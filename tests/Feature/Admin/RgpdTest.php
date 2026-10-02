<?php

namespace Tests\Feature\Admin;

use App\Models\Document;
use App\Models\Emargement;
use App\Models\Questionnaire;
use App\Models\QuestionnaireQuestion;
use App\Models\QuestionnaireReponse;
use App\Models\Referentiel;
use App\Models\Seance;
use App\Models\SessionFormation;
use App\Models\User;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Spatie\Activitylog\Models\Activity;
use Tests\TestCase;

class RgpdTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->admin()->create(['login' => 'admin-test']);
    }

    private function exporter(User $utilisateur): array
    {
        $reponse = $this->actingAs($this->admin)->get(route('admin.rgpd.export', $utilisateur));
        $reponse->assertOk();

        return json_decode($reponse->streamedContent(), true, flags: JSON_THROW_ON_ERROR);
    }

    public function test_le_registre_presente_chaque_traitement_et_signale_le_projet(): void
    {
        config(['edl.legal.registre_valide_le' => '']);

        $this->actingAs($this->admin)->get(route('admin.rgpd.registre'))
            ->assertOk()
            ->assertSee('Projet à valider')
            ->assertSee('Gestion des comptes et authentification')
            ->assertSee('Émargement')
            ->assertSee('Journal des actions')
            ->assertSee('Connexions techniques')
            ->assertSee('Bunny Fonts');
    }

    public function test_le_registre_valide_affiche_sa_date(): void
    {
        config(['edl.legal.registre_valide_le' => '2026-11-05']);

        $this->actingAs($this->admin)->get(route('admin.rgpd.registre'))
            ->assertOk()
            ->assertSee('05/11/2026')
            ->assertDontSee('Projet à valider');
    }

    public function test_le_registre_se_telecharge_en_pdf(): void
    {
        $reponse = $this->actingAs($this->admin)->get(route('admin.rgpd.registre.pdf'));

        $reponse->assertOk();
        $this->assertSame('application/pdf', $reponse->headers->get('content-type'));
    }

    public function test_seul_un_admin_accede_au_registre_et_aux_exports(): void
    {
        $stagiaire = User::factory()->stagiaireFpc()->create();

        foreach ([User::factory()->formateur()->create(), User::factory()->stagiaireOp()->create()] as $autre) {
            $this->actingAs($autre)->get(route('admin.rgpd.registre'))->assertForbidden();
            $this->actingAs($autre)->get(route('admin.rgpd.registre.pdf'))->assertForbidden();
            $this->actingAs($autre)->get(route('admin.rgpd.export', $stagiaire))->assertForbidden();
        }
    }

    public function test_l_export_d_un_stagiaire_reunit_ses_donnees_sans_secrets_ni_tiers(): void
    {
        $formateur = User::factory()->formateur()->create(['nom' => 'MARTEAU', 'prenom' => 'Paul']);
        $session = SessionFormation::factory()->fpc()->create(['formateur_id' => $formateur->id, 'num_GESCOF' => 'GESCOF-12345']);
        $stagiaire = User::factory()->stagiaireFpc()->create(['nom' => 'ROYER', 'prenom' => 'Daniel', 'email' => 'daniel@example.test']);
        $autre = User::factory()->stagiaireFpc()->create(['nom' => 'AUTRENOM', 'prenom' => 'Zoe']);
        $session->stagiaires()->attach([$stagiaire->id, $autre->id]);

        $fiche = Seance::factory()->pourStagiaire($stagiaire)->create([
            'session_formation_id' => $session->id,
            'formateur_id' => $formateur->id,
            'contenu' => 'Révision du passé composé.',
        ]);
        Seance::factory()->pourStagiaire($autre)->create(['session_formation_id' => $session->id, 'contenu' => 'Contenu de AUTRENOM']);
        Emargement::factory()->create(['seance_id' => $fiche->id, 'user_id' => $stagiaire->id, 'present' => true, 'commentaire' => 'RAS']);

        $questionnaire = Questionnaire::create(['titre' => 'Satisfaction', 'type' => 'satisfaction_chaud', 'session_formation_id' => $session->id, 'actif' => true]);
        $question = QuestionnaireQuestion::create(['questionnaire_id' => $questionnaire->id, 'libelle' => 'Note ?', 'type' => 'echelle', 'obligatoire' => true, 'ordre' => 1]);
        QuestionnaireReponse::create(['questionnaire_id' => $questionnaire->id, 'questionnaire_question_id' => $question->id, 'user_id' => $stagiaire->id, 'valeur' => '4']);
        $questionnaire->repondants()->attach($stagiaire->id, ['soumis_at' => now()]);

        $module = Referentiel::factory()->create();
        $stagiaire->referentiels()->attach($module->id, ['consulte_at' => now()]);
        $stagiaire->documents()->attach(Document::factory()->mesDocuments()->create(['nom' => 'Convention de Daniel'])->id);

        $donnees = $this->exporter($stagiaire);

        $this->assertSame('ROYER', $donnees['compte']['nom']);
        $this->assertSame('daniel@example.test', $donnees['compte']['email']);
        $this->assertSame('GESCOF-12345', $donnees['sessions_de_formation'][0]['numero_gescof']);
        $this->assertSame('Révision du passé composé.', $donnees['fiches_pedagogiques_individuelles'][0]['contenu']);
        $this->assertTrue($donnees['emargements'][0]['present']);
        $this->assertSame('RAS', $donnees['emargements'][0]['commentaire']);
        $this->assertSame('Note ?', $donnees['questionnaires'][0]['reponses'][0]['question']);
        $this->assertSame('4', $donnees['questionnaires'][0]['reponses'][0]['reponse']);
        $this->assertSame($module->module, $donnees['referentiel_consulte'][0]['module']);
        $this->assertSame('Convention de Daniel', $donnees['documents_attribues'][0]['nom']);

        // Ni secrets techniques, ni données d'un autre stagiaire.
        $brut = json_encode($donnees, JSON_UNESCAPED_UNICODE);
        $this->assertStringNotContainsString('password', $brut);
        $this->assertStringNotContainsString('remember_token', $brut);
        $this->assertStringNotContainsString('AUTRENOM', $brut);
    }

    public function test_l_export_d_un_formateur_liste_ses_formations_animees(): void
    {
        $formateur = User::factory()->formateur()->create();
        $session = SessionFormation::factory()->op()->create(['formateur_id' => $formateur->id, 'num_GESCOF' => 'GESCOF-777']);
        Seance::factory()->create(['session_formation_id' => $session->id, 'formateur_id' => $formateur->id, 'date' => '2026-03-10']);

        $donnees = $this->exporter($formateur);

        $this->assertSame('GESCOF-777', $donnees['formations_animees'][0]['numero_gescof']);
        $this->assertTrue($donnees['formations_animees'][0]['referent']);
        $this->assertSame(['2026-03-10'], $donnees['formations_animees'][0]['seances_animees']);
        $this->assertArrayHasKey('presentation', $donnees['compte']);
    }

    public function test_le_journal_exporte_ne_reprend_les_modifications_que_pour_l_objet_de_l_action(): void
    {
        $formateur = User::factory()->formateur()->create();
        $stagiaire = User::factory()->stagiaireOp()->create(['nom' => 'DUPONT']);

        // L'admin modifie le stagiaire : pour l'admin, l'entrée ne doit pas révéler les données du stagiaire.
        activity('User')->causedBy($this->admin)->performedOn($stagiaire)
            ->withProperties(['attributes' => ['nom' => 'SECRET-STAGIAIRE']])->event('updated')->log('updated');
        activity('User')->causedBy($this->admin)->performedOn($formateur)
            ->withProperties(['attributes' => ['nom' => 'SECRET-FORMATEUR']])->event('updated')->log('updated');

        $pourAdmin = $this->exporter($this->admin);
        // 2 actions dont il est l'auteur + la création de son propre compte.
        $this->assertCount(3, $pourAdmin['journal']);
        $this->assertSame(2, collect($pourAdmin['journal'])->where('role_dans_l_action', 'auteur')->count());
        $this->assertStringNotContainsString('SECRET', json_encode($pourAdmin['journal']));

        $pourStagiaire = $this->exporter($stagiaire);
        $modification = collect($pourStagiaire['journal'])->firstWhere('evenement', 'updated');
        $this->assertSame('SECRET-STAGIAIRE', $modification['modifications']['attributes']['nom']);
    }

    public function test_les_connexions_techniques_sont_exportees(): void
    {
        $stagiaire = User::factory()->stagiaireOp()->create();
        DB::table('sessions')->insert([
            'id' => 'abc', 'user_id' => $stagiaire->id, 'ip_address' => '203.0.113.9',
            'user_agent' => 'TestBrowser/1.0', 'payload' => '', 'last_activity' => now()->timestamp,
        ]);

        $donnees = $this->exporter($stagiaire);

        $this->assertSame('203.0.113.9', $donnees['connexions_techniques'][0]['adresse_ip']);
    }

    public function test_un_compte_supprime_logiquement_reste_exportable(): void
    {
        $stagiaire = User::factory()->stagiaireOp()->create(['nom' => 'ARCHIVE']);
        $stagiaire->delete();

        $donnees = $this->exporter($stagiaire);

        $this->assertSame('ARCHIVE', $donnees['compte']['nom']);
        $this->assertNotNull($donnees['compte']['supprime_le']);
    }

    public function test_un_export_est_journalise_et_se_telecharge_en_json(): void
    {
        $stagiaire = User::factory()->stagiaireOp()->create(['login' => 'jdupont']);

        $reponse = $this->actingAs($this->admin)->get(route('admin.rgpd.export', $stagiaire));

        $reponse->assertOk();
        $this->assertStringContainsString('application/json', $reponse->headers->get('content-type'));
        $this->assertStringContainsString('attachment; filename=export-rgpd-jdupont-', $reponse->headers->get('content-disposition'));

        $entree = Activity::where('log_name', 'RGPD')->firstOrFail();
        $this->assertSame($this->admin->id, $entree->causer_id);
        $this->assertSame($stagiaire->id, $entree->subject_id);
    }

    public function test_le_journal_est_purge_apres_trois_ans(): void
    {
        $this->assertSame(1095, config('activitylog.clean_after_days'));

        $commandes = collect(app(Schedule::class)->events())->pluck('command')->implode(' ');
        $this->assertStringContainsString('activitylog:clean', $commandes);
    }
}
