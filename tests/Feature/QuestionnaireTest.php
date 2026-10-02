<?php

namespace Tests\Feature;

use App\Models\Questionnaire;
use App\Models\QuestionnaireQuestion;
use App\Models\SessionFormation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class QuestionnaireTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_construit_un_questionnaire_avec_ses_questions(): void
    {
        $this->actingAs(User::factory()->admin()->create());

        $response = $this->post(route('admin.questionnaires.store'), [
            'titre' => 'Satisfaction à chaud',
            'type' => 'satisfaction_chaud',
            'actif' => '1',
            'questions' => [
                ['libelle' => 'Le contenu a répondu à vos attentes ?', 'type' => 'echelle', 'obligatoire' => '1'],
                ['libelle' => 'Un point à améliorer ?', 'type' => 'texte'],
                ['libelle' => 'Format préféré ?', 'type' => 'choix_unique', 'obligatoire' => '1', 'options' => "Présentiel\nDistanciel"],
            ],
        ]);

        $questionnaire = Questionnaire::firstOrFail();
        $response->assertRedirect(route('admin.questionnaires.edit', $questionnaire));
        $this->assertCount(3, $questionnaire->questions);
        $this->assertSame(['Présentiel', 'Distanciel'], $questionnaire->questions[2]->options);
    }

    public function test_option_obligatoire_pour_les_choix(): void
    {
        $this->actingAs(User::factory()->admin()->create());

        $this->post(route('admin.questionnaires.store'), [
            'titre' => 'Test', 'type' => 'satisfaction_chaud',
            'questions' => [['libelle' => 'Choix ?', 'type' => 'choix_unique']],
        ])->assertSessionHasErrors('questions.0.options');
    }

    private function questionnairePret(SessionFormation $session): Questionnaire
    {
        $q = Questionnaire::create([
            'titre' => 'Q', 'type' => 'satisfaction_chaud', 'session_formation_id' => $session->id, 'actif' => true,
        ]);
        QuestionnaireQuestion::create(['questionnaire_id' => $q->id, 'libelle' => 'Note ?', 'type' => 'echelle', 'obligatoire' => true, 'ordre' => 1]);
        QuestionnaireQuestion::create(['questionnaire_id' => $q->id, 'libelle' => 'Commentaire', 'type' => 'texte', 'obligatoire' => false, 'ordre' => 2]);

        return $q->load('questions');
    }

    public function test_stagiaire_repond_puis_ne_peut_plus_repondre(): void
    {
        $session = SessionFormation::factory()->fpc()->create();
        $stagiaire = User::factory()->stagiaireFpc()->create();
        $session->stagiaires()->attach($stagiaire->id);
        $q = $this->questionnairePret($session);

        $this->actingAs($stagiaire)->get(route('stagiaire.questionnaires.show', $q))->assertOk();

        $reponses = [
            $q->questions[0]->id => 4,
            $q->questions[1]->id => 'Très bien dans l\'ensemble.',
        ];
        $this->actingAs($stagiaire)->post(route('stagiaire.questionnaires.store', $q), ['reponses' => $reponses])
            ->assertRedirect(route('stagiaire.questionnaires.index'));

        $this->assertDatabaseHas('questionnaire_soumissions', ['questionnaire_id' => $q->id, 'user_id' => $stagiaire->id]);
        $this->assertSame('4', $q->reponses()->where('questionnaire_question_id', $q->questions[0]->id)->value('valeur'));

        // Deuxième tentative refusée.
        $this->actingAs($stagiaire)->get(route('stagiaire.questionnaires.show', $q))->assertForbidden();
        $this->actingAs($stagiaire)->post(route('stagiaire.questionnaires.store', $q), ['reponses' => $reponses])->assertForbidden();
    }

    public function test_question_obligatoire_bloque_l_envoi(): void
    {
        $session = SessionFormation::factory()->fpc()->create();
        $stagiaire = User::factory()->stagiaireFpc()->create();
        $session->stagiaires()->attach($stagiaire->id);
        $q = $this->questionnairePret($session);

        $this->actingAs($stagiaire)
            ->post(route('stagiaire.questionnaires.store', $q), ['reponses' => [$q->questions[1]->id => 'x']])
            ->assertSessionHasErrors('reponses.'.$q->questions[0]->id);
    }

    public function test_un_stagiaire_ne_voit_pas_le_questionnaire_d_une_autre_session(): void
    {
        $q = $this->questionnairePret(SessionFormation::factory()->fpc()->create());
        $stagiaire = User::factory()->stagiaireFpc()->create();
        SessionFormation::factory()->fpc()->create()->stagiaires()->attach($stagiaire->id);

        $this->actingAs($stagiaire)->get(route('stagiaire.questionnaires.show', $q))->assertForbidden();
    }

    public function test_resultats_agreges(): void
    {
        $session = SessionFormation::factory()->fpc()->create();
        $q = $this->questionnairePret($session);

        foreach ([3, 5, 4] as $note) {
            $s = User::factory()->stagiaireFpc()->create();
            $session->stagiaires()->attach($s->id);
            $this->actingAs($s)->post(route('stagiaire.questionnaires.store', $q), [
                'reponses' => [$q->questions[0]->id => $note, $q->questions[1]->id => 'ok'],
            ]);
        }

        $this->actingAs(User::factory()->admin()->create())
            ->get(route('admin.questionnaires.resultats', $q))
            ->assertOk()
            ->assertSee('4')       // moyenne (3+5+4)/3
            ->assertSee('3 réponse(s)');
    }
}
