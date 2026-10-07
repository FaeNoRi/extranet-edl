<?php

namespace Tests\Feature;

use App\Models\Referentiel;
use App\Models\User;
use App\Notifications\PasswordSetupLink;
use App\Services\PreflightService;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

/**
 * Préparation de la mise en production : contrôle préalable, premier administrateur, jeu de
 * données, pages d'erreur.
 */
class DeploiementTest extends TestCase
{
    use RefreshDatabase;

    /** @return array<string, string> nom du contrôle => statut */
    private function statuts(): array
    {
        return collect(app(PreflightService::class)->controles())->pluck('statut', 'nom')->all();
    }

    public function test_le_controle_prealable_bloque_une_configuration_de_developpement(): void
    {
        config(['app.debug' => true, 'app.url' => 'http://localhost', 'session.secure' => false, 'mail.default' => 'log', 'backup.backup.password' => null]);

        $statuts = $this->statuts();

        foreach (['Mode debug désactivé', 'Adresse en HTTPS', 'Cookie de session sécurisé', 'Envoi d\'e-mails réel', 'Chiffrement des sauvegardes', 'Environnement'] as $nom) {
            $this->assertSame(PreflightService::BLOQUANT, $statuts[$nom], $nom);
        }

        $this->artisan('edl:preflight')->assertFailed();
    }

    public function test_le_controle_prealable_accepte_une_configuration_de_production(): void
    {
        $this->app['env'] = 'production';
        config([
            'app.debug' => false, 'app.url' => 'https://extranet.example.test', 'session.secure' => true,
            'mail.default' => 'smtp', 'backup.backup.password' => 'secret',
        ]);
        User::factory()->admin()->create(['password' => Hash::make('Un-vrai-mot2passe')]);

        $statuts = $this->statuts();

        foreach (['Mode debug désactivé', 'Adresse en HTTPS', 'Cookie de session sécurisé', 'Envoi d\'e-mails réel', 'Chiffrement des sauvegardes', 'Environnement', 'Base de données', 'Migrations à jour', 'Aucun compte de démonstration', 'Au moins un administrateur'] as $nom) {
            $this->assertSame(PreflightService::OK, $statuts[$nom], $nom);
        }
    }

    public function test_un_compte_administrateur_de_demonstration_est_signale(): void
    {
        // La fabrique crée les comptes avec le mot de passe « password » : celui des données de démonstration.
        User::factory()->admin()->create();

        $this->assertSame(PreflightService::BLOQUANT, $this->statuts()['Aucun compte de démonstration']);
    }

    public function test_l_absence_de_planificateur_est_signalee(): void
    {
        Cache::forget('edl:planificateur:dernier-passage');
        $this->assertSame(PreflightService::AVERTISSEMENT, $this->statuts()['Planificateur actif']);

        Cache::put('edl:planificateur:dernier-passage', now());
        $this->assertSame(PreflightService::OK, $this->statuts()['Planificateur actif']);

        Cache::put('edl:planificateur:dernier-passage', now()->subMinutes(30));
        $this->assertSame(PreflightService::AVERTISSEMENT, $this->statuts()['Planificateur actif']);
    }

    public function test_les_informations_legales_manquantes_sont_signalees(): void
    {
        config(['edl.legal.dpo_nom' => '', 'edl.structure.nda' => '', 'edl.structure.directeur_publication' => '', 'edl.legal.registre_valide_le' => '']);

        $statuts = $this->statuts();

        foreach (['Référent RGPD renseigné', 'Directeur de publication', 'N° de déclaration d\'activité', 'Registre RGPD validé'] as $nom) {
            $this->assertSame(PreflightService::AVERTISSEMENT, $statuts[$nom], $nom);
        }
    }

    public function test_le_premier_administrateur_se_cree_sans_mot_de_passe_connu(): void
    {
        Notification::fake();

        $this->artisan('edl:creer-admin', ['login' => 'responsable', 'email' => 'responsable@example.test', 'nom' => 'durand', 'prenom' => 'Claire'])
            ->assertSuccessful();

        $admin = User::where('login', 'responsable')->firstOrFail();
        $this->assertTrue($admin->isAdmin());
        $this->assertSame('DURAND', $admin->nom);
        $this->assertFalse(Hash::check('password', $admin->password));
        Notification::assertSentTo($admin, PasswordSetupLink::class);
    }

    public function test_la_creation_d_administrateur_refuse_un_identifiant_existant_ou_un_email_invalide(): void
    {
        User::factory()->create(['login' => 'pris']);

        $this->artisan('edl:creer-admin', ['login' => 'pris', 'email' => 'a@example.test', 'nom' => 'X', 'prenom' => 'Y'])->assertFailed();
        $this->artisan('edl:creer-admin', ['login' => 'nouveau', 'email' => 'pas-un-email', 'nom' => 'X', 'prenom' => 'Y'])->assertFailed();
        $this->assertDatabaseMissing('users', ['login' => 'nouveau']);
    }

    public function test_en_production_le_jeu_de_donnees_ne_charge_que_le_referentiel(): void
    {
        $this->app['env'] = 'production';

        $this->artisan('db:seed', ['--class' => DatabaseSeeder::class, '--force' => true])->assertSuccessful();

        $this->assertGreaterThan(0, Referentiel::count());
        $this->assertSame(0, User::count(), 'Aucun compte (ni « admin » de démonstration) ne doit être créé en production.');
    }

    public function test_les_pages_d_erreur_sont_en_francais(): void
    {
        $this->get('/cette-page-n-existe-pas')
            ->assertNotFound()
            ->assertSee('Page introuvable')
            ->assertSee("Retour à l'accueil", false);

        $this->actingAs(User::factory()->stagiaireOp()->create())
            ->get(route('admin.dashboard'))
            ->assertForbidden()
            ->assertSee('Accès refusé');
    }

    public function test_les_moteurs_de_recherche_n_indexent_rien(): void
    {
        $robots = file_get_contents(public_path('robots.txt'));

        $this->assertStringContainsString('Disallow: /', $robots);
        $this->assertDoesNotMatchRegularExpression('/^Disallow:\s*$/m', $robots);
    }

    public function test_le_planificateur_enregistre_son_temoin_de_vie(): void
    {
        $evenements = collect(app(Schedule::class)->events())->map->description;

        $this->assertTrue($evenements->contains('edl-temoin-planificateur'));
    }
}
