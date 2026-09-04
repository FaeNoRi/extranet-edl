<?php

namespace App\Http\Controllers\Stagiaire;

use App\Enums\TypeQuestion;
use App\Http\Controllers\Controller;
use App\Models\Questionnaire;
use App\Models\QuestionnaireReponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class QuestionnaireController extends Controller
{
    public function index(): View
    {
        $stagiaire = auth()->user();
        $questionnaires = Questionnaire::pourSession($stagiaire->sessionStagiaire()?->id)
            ->withCount('questions')
            ->get()
            ->map(function (Questionnaire $q) use ($stagiaire) {
                $q->setAttribute('soumis', $q->estSoumisPar($stagiaire));

                return $q;
            });

        return view('stagiaire.questionnaires.index', compact('questionnaires'));
    }

    public function show(Questionnaire $questionnaire): View
    {
        $this->autoriser($questionnaire);

        abort_if($questionnaire->estSoumisPar(auth()->user()), 403, 'Questionnaire déjà rempli.');

        $questionnaire->load('questions');

        return view('stagiaire.questionnaires.show', compact('questionnaire'));
    }

    public function store(Request $request, Questionnaire $questionnaire): RedirectResponse
    {
        $this->autoriser($questionnaire);
        abort_if($questionnaire->estSoumisPar(auth()->user()), 403);

        $questionnaire->load('questions');
        $reponses = $request->input('reponses', []);

        $regles = [];
        foreach ($questionnaire->questions as $question) {
            $cle = "reponses.$question->id";
            $regles[$cle] = $question->obligatoire ? ['required'] : ['nullable'];
            if ($question->type === TypeQuestion::Echelle) {
                $regles[$cle][] = 'integer';
                $regles[$cle][] = 'between:1,5';
            }
            if ($question->type === TypeQuestion::ChoixMultiple) {
                $regles["$cle"] = ['array'];
                $regles["$cle.*"] = ['in:'.implode(',', $question->options ?? [])];
            }
        }
        $request->validate($regles);

        DB::transaction(function () use ($questionnaire, $reponses) {
            foreach ($questionnaire->questions as $question) {
                $valeur = $reponses[$question->id] ?? null;
                QuestionnaireReponse::updateOrCreate(
                    ['questionnaire_question_id' => $question->id, 'user_id' => auth()->id()],
                    [
                        'questionnaire_id' => $questionnaire->id,
                        'valeur' => is_array($valeur) ? implode(' ; ', $valeur) : $valeur,
                    ],
                );
            }

            $questionnaire->repondants()->syncWithoutDetaching([
                auth()->id() => ['soumis_at' => Carbon::now()],
            ]);
        });

        return redirect()->route('stagiaire.questionnaires.index')
            ->with('succes', 'Merci, votre questionnaire a été enregistré.');
    }

    private function autoriser(Questionnaire $questionnaire): void
    {
        $sessionId = auth()->user()->sessionStagiaire()?->id;

        abort_unless(
            $questionnaire->actif
            && (is_null($questionnaire->session_formation_id) || $questionnaire->session_formation_id === $sessionId),
            403,
        );
    }
}
