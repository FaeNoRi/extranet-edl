<?php

namespace Tests\Feature\Admin;

use App\Enums\Role;
use App\Models\PasswordResetToken;
use App\Models\User;
use App\Services\TachesPlanifieesService;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Spatie\Activitylog\Models\Activity;
use Tests\TestCase;

/**
 * Hébergement mutualisé OVH sans SSH : cron horaire, comptes et liens d'accès créés dans
 * l'administration, état du serveur et mise à jour de la base depuis le navigateur, photos
 * servies sans lien public/storage.
 */
class HebergementSansSshTest extends TestCase
{
    use RefreshDatabase;

    // --- Tâches planifiées (cron horaire) -----------------------------------------------------

    private function taches(Schedule $schedule): TachesPlanifieesService
    {
        return new TachesPlanifieesService($schedule);
    }

    public function test_une_tache_echue_est_rattrapee_une_seule_fois_par_echeance(): void
    {
        $schedule = new Schedule;
        $schedule->command('list --raw')->dailyAt('02:30');
        $taches = $this->taches($schedule);

        // Premier passage : la tâche n'a jamais tourné, elle part (rattrapage de l'échéance de la veille).
        $this->travelTo(Carbon::parse('2026-11-10 01:36'));
        $this->assertSame(['list --raw'], array_column($taches->rattraper(), 'commande'));
        $this->assertSame([], $taches->rattraper());

        // Passage suivant à xx:36 (minute imposée par OVH) : l'échéance de 02:30 est rattrapée.
        $this->travelTo(Carbon::parse('2026-11-10 02:36'));
        $executees = $taches->rattraper();
        $this->assertSame(['list --raw'], array_column($executees, 'commande'));
        $this->assertTrue($executees[0]['ok']);

        $this->travelTo(Carbon::parse('2026-11-10 03:36'));
        $this->assertSame([], $taches->rattraper());

        $this->assertSame('list --raw', $taches->dernierRapport()['taches'][0]['commande']);
    }

    public function test_une_tache_en_echec_est_signalee_sans_bloquer_les_suivantes(): void
    {
        $schedule = new Schedule;
        $schedule->command('commande:inexistante')->dailyAt('02:30');
        $schedule->command('list --raw')->dailyAt('03:00');

        $this->travelTo(Carbon::parse('2026-11-10 03:36'));
        $executees = $this->taches($schedule)->rattraper();

        $this->assertSame([false, true], array_column($executees, 'ok'));
    }

    public function test_le_passage_horaire_ecrit_le_temoin_et_ignore_les_fonctions(): void
    {
        $schedule = new Schedule;
        $schedule->call(fn () => throw new \RuntimeException('ne doit pas être appelée'))->everyMinute();
        Cache::forget(TachesPlanifieesService::TEMOIN);

        $this->assertSame([], $this->taches($schedule)->rattraper());
        $this->assertNotNull(Cache::get(TachesPlanifieesService::TEMOIN));
    }

    public function test_les_taches_de_l_application_sont_reconnues_dans_l_ordre_chronologique(): void
    {
        $commandes = collect(app(Schedule::class)->events())
            ->filter(fn ($e) => $e->command)
            ->map(fn ($e) => preg_replace('/^.*artisan["\']?\s+/', '', $e->command))
            ->values()->all();

        $this->assertSame(['backup:run', 'backup:clean', 'edl:purge-comptes', 'edl:purge-imports', 'activitylog:clean', 'backup:monitor'], $commandes);
        $this->assertFileExists(base_path('cron.php'));
        $this->assertStringContainsString('edl:taches', file_get_contents(base_path('cron.php')));
    }

    // --- Lien d'accès affiché -----------------------------------------------------------------

    public function test_l_administrateur_obtient_un_lien_d_acces_journalise(): void
    {
        $admin = User::factory()->admin()->create();
        $stagiaire = User::factory()->stagiaireOp()->create();

        $reponse = $this->actingAs($admin)->from(route('admin.stagiaires.index'))
            ->post(route('admin.lien-acces', $stagiaire))
            ->assertRedirect(route('admin.stagiaires.index'));

        $jeton = PasswordResetToken::where('user_id', $stagiaire->id)->sole();
        $reponse->assertSessionHas('lien_acces.url', route('password.setup', ['token' => $jeton->token]));
        $this->assertTrue(Activity::where('log_name', 'Accès')->where('subject_id', $stagiaire->id)->exists());

        $this->get(route('admin.stagiaires.index'))->assertSee('Copier le lien');

        auth()->logout();
        $this->get(route('password.setup', ['token' => $jeton->token]))->assertOk();
    }

    public function test_seul_l_administrateur_genere_des_liens(): void
    {
        $formateur = User::factory()->formateur()->create();

        $this->actingAs($formateur)->post(route('admin.lien-acces', User::factory()->stagiaireOp()->create()))->assertForbidden();
    }

    // --- Comptes administrateurs --------------------------------------------------------------

    public function test_creation_d_un_administrateur_sans_mot_de_passe_connu(): void
    {
        $this->actingAs(User::factory()->admin()->create())
            ->post(route('admin.administrateurs.store'), ['prenom' => 'Claire', 'nom' => 'durand', 'login' => 'claire.durand', 'email' => 'claire@example.test'])
            ->assertRedirect(route('admin.administrateurs.index'));

        $admin = User::where('login', 'claire.durand')->sole();
        $this->assertSame(Role::Admin, $admin->role);
        $this->assertSame('DURAND', $admin->nom);
        $this->assertFalse(password_verify('password', $admin->password));

        $this->get(route('admin.administrateurs.index'))->assertOk()->assertSee('claire.durand');
    }

    public function test_un_administrateur_ne_peut_pas_archiver_son_propre_compte(): void
    {
        $moi = User::factory()->admin()->create();
        $installation = User::factory()->admin()->create();

        $this->actingAs($moi)->delete(route('admin.administrateurs.destroy', $moi))->assertSessionHas('erreur');
        $this->assertNotSoftDeleted($moi);

        $this->delete(route('admin.administrateurs.destroy', $installation))->assertSessionHas('succes');
        $this->assertSoftDeleted($installation);
    }

    public function test_la_page_administrateurs_ne_gere_que_des_administrateurs(): void
    {
        $formateur = User::factory()->formateur()->create();

        $this->actingAs(User::factory()->admin()->create())
            ->get(route('admin.administrateurs.edit', $formateur))->assertNotFound();
        $this->actingAs($formateur)->get(route('admin.administrateurs.index'))->assertForbidden();
    }

    // --- Page Serveur ---------------------------------------------------------------------------

    public function test_la_page_serveur_affiche_les_controles_et_la_base_a_jour(): void
    {
        $this->actingAs(User::factory()->admin()->create())
            ->get(route('admin.serveur.index'))
            ->assertOk()
            ->assertSee('Contrôles de mise en service')
            ->assertSee('La base est à jour.');

        $this->post(route('admin.serveur.migrer'))->assertSessionHas('succes', 'La base est déjà à jour.');
    }

    public function test_la_page_serveur_est_reservee_a_l_administrateur(): void
    {
        $this->actingAs(User::factory()->formateur()->create())->get(route('admin.serveur.index'))->assertForbidden();
    }

    // --- Photos des formateurs ------------------------------------------------------------------

    public function test_la_photo_d_un_formateur_est_servie_aux_utilisateurs_connectes(): void
    {
        Storage::fake('public');
        $formateur = User::factory()->formateur()->create([
            'photo_path' => UploadedFile::fake()->image('photo.jpg')->store('formateurs', 'public'),
        ]);

        $this->get(route('photos.formateur', $formateur))->assertRedirect(route('login'));
        $this->actingAs(User::factory()->stagiaireOp()->create())
            ->get(route('photos.formateur', $formateur))->assertOk();

        $this->get(route('photos.formateur', User::factory()->formateur()->create()))->assertNotFound();
    }
}
