<?php

namespace Tests\Feature;

use App\Models\PasswordResetToken;
use App\Models\Ressource;
use App\Models\Seance;
use App\Models\SessionFormation;
use App\Models\User;
use App\Notifications\PasswordSetupLink;
use App\Support\OptionsSeance;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * Garde-fous issus de la revue de sécurité (octobre 2026).
 */
class SecuriteTest extends TestCase
{
    use RefreshDatabase;

    private function payload(SessionFormation $session, array $extra = []): array
    {
        return array_merge([
            'session_formation_id' => $session->id,
            'date' => '2026-03-10',
            'objectifs' => [OptionsSeance::OBJECTIFS[0]],
            'contenu' => 'Contenu.',
            'outils' => ['Livres'],
            'sources' => 'Sources.',
            'analyse_seance' => 'Analyse.',
        ], $extra);
    }

    // --- Fiches pédagogiques : aucune référence à des données d'une autre session -----------------

    public function test_une_ressource_d_une_autre_session_ne_peut_pas_etre_rattachee_a_une_fiche(): void
    {
        Storage::fake('local');
        $formateur = User::factory()->formateur()->create();
        $session = SessionFormation::factory()->op()->create(['formateur_id' => $formateur->id]);
        $etrangere = Ressource::factory()->create(['session_formation_id' => SessionFormation::factory()->op()->create()->id]);

        $this->actingAs($formateur)
            ->post(route('formateur.seances.store'), $this->payload($session, ['ressources' => [$etrangere->id]]))
            ->assertSessionHasErrors('ressources.0');

        $this->assertSame(0, Seance::count());
    }

    public function test_modifier_une_fiche_ne_rend_pas_transmis_un_document_de_travail(): void
    {
        Storage::fake('local');
        $formateur = User::factory()->formateur()->create();
        $session = SessionFormation::factory()->op()->create(['formateur_id' => $formateur->id]);

        $this->actingAs($formateur)->post(route('formateur.seances.store'), $this->payload($session, [
            'fichiers_internes' => [UploadedFile::fake()->create('corrige.pdf', 10, 'application/pdf')],
        ]))->assertSessionHasNoErrors();

        $seance = Seance::firstOrFail();
        $interne = $seance->ressources()->firstOrFail();
        $this->assertFalse((bool) $interne->pivot->transmis);

        // Le formulaire d'édition coche tous les fichiers déjà rattachés : on le renvoie tel quel.
        $this->actingAs($formateur)
            ->put(route('formateur.seances.update', $seance), $this->payload($session, ['ressources' => [$interne->id]]))
            ->assertSessionHasNoErrors();

        $this->assertFalse((bool) $seance->ressources()->firstOrFail()->pivot->transmis, 'Un document de travail est devenu visible des stagiaires.');
    }

    public function test_le_stagiaire_d_une_fiche_fpc_doit_etre_inscrit_a_la_session(): void
    {
        $formateur = User::factory()->formateur()->create();
        $session = SessionFormation::factory()->fpc()->create(['formateur_id' => $formateur->id]);
        $inscrit = User::factory()->stagiaireFpc()->create();
        $session->stagiaires()->attach($inscrit->id);
        $etranger = User::factory()->stagiaireFpc()->create();

        $this->actingAs($formateur)
            ->post(route('formateur.seances.store'), $this->payload($session, ['user_id' => $etranger->id]))
            ->assertSessionHasErrors('user_id');

        $this->actingAs($formateur)
            ->post(route('formateur.seances.store'), $this->payload($session, ['user_id' => $inscrit->id]))
            ->assertSessionHasNoErrors();
    }

    // --- Fichiers servis à l'écran ---------------------------------------------------------------

    public function test_un_fichier_deguise_n_est_jamais_servi_en_ligne_comme_une_page(): void
    {
        Storage::fake('local');
        $session = SessionFormation::factory()->fpc()->create();
        $stagiaire = User::factory()->stagiaireFpc()->create();
        $session->stagiaires()->attach($stagiaire->id);
        $seance = Seance::factory()->create(['session_formation_id' => $session->id, 'date' => Carbon::yesterday(), 'user_id' => null]);

        Storage::put('seances/piege.jpg', '<html><body><script>alert(document.cookie)</script></body></html>');
        $piege = Ressource::factory()->create(['chemin_fichier' => 'seances/piege.jpg', 'nom_fichier_original' => 'piege.jpg']);
        Storage::put('seances/vrai.pdf', "%PDF-1.4\n1 0 obj<<>>endobj\ntrailer<<>>\n%%EOF");
        $vrai = Ressource::factory()->create(['chemin_fichier' => 'seances/vrai.pdf', 'nom_fichier_original' => 'vrai.pdf']);
        $seance->ressources()->attach([$piege->id => ['transmis' => true], $vrai->id => ['transmis' => true]]);

        $this->actingAs($stagiaire)
            ->get(route('stagiaire.ressources.download', [$piege, 'apercu' => 1]))
            ->assertStatus(415);

        $reponse = $this->actingAs($stagiaire)
            ->get(route('stagiaire.ressources.download', [$vrai, 'apercu' => 1]))
            ->assertOk();
        $this->assertStringContainsString('application/pdf', $reponse->headers->get('Content-Type'));
        $this->assertSame('nosniff', $reponse->headers->get('X-Content-Type-Options'));

        // Image : affichable, mais enfermée dans un bac à sable si on l'ouvre directement.
        Storage::put('seances/image.png', base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mP8z8BQDwAEhQGAhKmMIQAAAABJRU5ErkJggg=='));
        $image = Ressource::factory()->create(['chemin_fichier' => 'seances/image.png', 'nom_fichier_original' => 'image.png']);
        $seance->ressources()->attach($image->id, ['transmis' => true]);

        $reponseImage = $this->actingAs($stagiaire)
            ->get(route('stagiaire.ressources.download', [$image, 'apercu' => 1]))
            ->assertOk();
        $this->assertStringContainsString('sandbox', $reponseImage->headers->get('Content-Security-Policy'));
    }

    // --- Authentification ------------------------------------------------------------------------

    public function test_les_demandes_de_lien_sont_limitees_par_compte_meme_depuis_plusieurs_adresses(): void
    {
        Notification::fake();
        $stagiaire = User::factory()->stagiaireOp()->create();

        foreach (range(1, 6) as $i) {
            $this->withServerVariables(['REMOTE_ADDR' => "203.0.113.{$i}"])
                ->post(route('password.email'), ['login' => $stagiaire->login]);
        }

        Notification::assertSentToTimes($stagiaire, PasswordSetupLink::class, 3);
    }

    public function test_un_nouveau_lien_invalide_le_precedent(): void
    {
        $stagiaire = User::factory()->stagiaireOp()->create();
        $ancien = PasswordResetToken::issueFor($stagiaire);
        $recent = PasswordResetToken::issueFor($stagiaire);

        $this->get(route('password.setup', $ancien->token))->assertRedirect(route('password.request'));
        $this->get(route('password.setup', $recent->token))->assertOk();
    }

    public function test_les_mots_de_passe_trop_faibles_sont_refuses(): void
    {
        $stagiaire = User::factory()->stagiaireOp()->create();
        $jeton = PasswordResetToken::issueFor($stagiaire);

        foreach (['court1A', 'toutenminuscules', '1234567890123'] as $faible) {
            $this->post(route('password.store', $jeton->token), ['password' => $faible, 'password_confirmation' => $faible])
                ->assertSessionHasErrors('password');
        }

        $this->post(route('password.store', $jeton->token), ['password' => 'Un-bon-mot2passe', 'password_confirmation' => 'Un-bon-mot2passe'])
            ->assertSessionHasNoErrors();
    }

    // --- En-têtes et cookies ---------------------------------------------------------------------

    public function test_la_politique_de_securite_du_contenu_interdit_les_ressources_tierces(): void
    {
        $csp = $this->get(route('login'))->headers->get('Content-Security-Policy');

        $this->assertNotNull($csp);
        foreach (["default-src 'self'", "object-src 'none'", "base-uri 'self'", "frame-ancestors 'self'", "form-action 'self'"] as $directive) {
            $this->assertStringContainsString($directive, $csp);
        }
        $this->assertStringNotContainsString('http', $csp, 'Aucun hôte tiers ne doit être autorisé.');
    }

    public function test_aucune_vue_n_utilise_de_script_en_ligne_bloque_par_la_csp(): void
    {
        // La CSP refuse les gestionnaires en ligne (onclick, onsubmit…) : ils seraient ignorés en
        // silence (déconnexion en GET, suppressions sans confirmation). Utiliser Alpine (@click, @submit).
        $fautifs = collect(File::allFiles(resource_path('views')))
            ->filter(fn ($f) => preg_match('/\son[a-z]+\s*=\s*["\']|<script(?![^>]*\bsrc=)[^>]*>|javascript:/i', $f->getContents()))
            ->map(fn ($f) => $f->getRelativePathname())
            ->values()
            ->all();

        $this->assertSame([], $fautifs, 'Script en ligne dans : '.implode(', ', $fautifs));
    }

    public function test_les_pages_connectees_ne_sont_pas_conservees_par_le_navigateur(): void
    {
        $reponse = $this->actingAs(User::factory()->admin()->create())->get(route('admin.dashboard'));

        $this->assertStringContainsString('no-store', $reponse->headers->get('Cache-Control'));
    }

    public function test_le_cookie_de_session_est_securise_en_production(): void
    {
        $config = $this->app['config'];
        $this->assertTrue($config->get('session.http_only'));
        $this->assertSame('lax', $config->get('session.same_site'));

        $source = file_get_contents(config_path('session.php'));
        $this->assertMatchesRegularExpression("/'secure' => env\('SESSION_SECURE_COOKIE', .*production/s", $source);
    }
}
