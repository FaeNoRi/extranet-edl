<?php

namespace Tests\Feature;

use App\Models\Questionnaire;
use App\Models\Seance;
use App\Models\SessionFormation;
use App\Models\SessionJour;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Garde-fous contre les requêtes N+1 : le nombre de requêtes d'une page ne doit
 * pas croître avec le nombre de sessions / stagiaires affichés. (Le chargement
 * paresseux de relations est de plus interdit hors production, cf. AppServiceProvider.)
 */
class PerformanceTest extends TestCase
{
    use RefreshDatabase;

    private User $formateur;

    protected function setUp(): void
    {
        parent::setUp();

        $this->formateur = User::factory()->formateur()->create();
    }

    public function test_le_nombre_de_requetes_ne_depend_pas_du_nombre_de_sessions(): void
    {
        $admin = User::factory()->admin()->create();
        $stagiaire = null;

        $this->peupler(2);
        $stagiaire = SessionFormation::first()->stagiaires()->first();

        $pages = [
            ['admin', $admin, route('admin.dashboard')],
            ['admin sessions', $admin, route('admin.sessions.index')],
            ['admin stagiaires', $admin, route('admin.stagiaires.index')],
            ['admin purges', $admin, route('admin.purges.index')],
            ['admin questionnaires', $admin, route('admin.questionnaires.index')],
            ['formateur', $this->formateur, route('formateur.dashboard')],
            ['formateur sessions', $this->formateur, route('formateur.sessions.index')],
            ['stagiaire', $stagiaire, route('stagiaire.dashboard')],
        ];

        $avant = array_map(fn ($p) => $this->requetes($p[1], $p[2]), $pages);

        $this->peupler(8);

        foreach ($pages as $i => [$nom, $utilisateur, $url]) {
            $apres = $this->requetes($utilisateur, $url);
            $this->assertLessThanOrEqual(
                $avant[$i] + 2,
                $apres,
                "{$nom} : {$avant[$i]} requêtes avec 2 sessions, {$apres} avec 10 (N+1 probable)"
            );
        }
    }

    private function requetes(User $utilisateur, string $url): int
    {
        DB::flushQueryLog();
        DB::enableQueryLog();
        $this->actingAs($utilisateur)->get($url)->assertOk();
        $n = count(DB::getQueryLog());
        DB::disableQueryLog();

        return $n;
    }

    private function peupler(int $sessions): void
    {
        for ($i = 0; $i < $sessions; $i++) {
            $session = SessionFormation::factory()->fpc()->create(['formateur_id' => $this->formateur->id]);
            $session->formateurs()->syncWithoutDetaching([$this->formateur->id => ['principal' => true]]);
            $session->stagiaires()->attach(User::factory()->stagiaireFpc()->count(3)->create()->pluck('id'));

            foreach ([0, 1] as $decalage) {
                SessionJour::create(['session_formation_id' => $session->id, 'date' => Carbon::today()->addDays(10 + $decalage), 'actif' => true]);
            }
            Seance::factory()->count(2)->sequence(
                ['date' => Carbon::today()->subDays(3)->toDateString()],
                ['date' => Carbon::today()->subDays(4)->toDateString()],
            )->create(['session_formation_id' => $session->id, 'formateur_id' => $this->formateur->id]);

            Questionnaire::create(['titre' => 'Satisfaction '.$i, 'type' => 'satisfaction_chaud', 'session_formation_id' => $session->id, 'actif' => true]);
        }
    }
}
