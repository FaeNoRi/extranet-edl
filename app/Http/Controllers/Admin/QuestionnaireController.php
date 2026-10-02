<?php

namespace App\Http\Controllers\Admin;

use App\Enums\TypeQuestion;
use App\Enums\TypeQuestionnaire;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\QuestionnaireRequest;
use App\Models\Questionnaire;
use App\Models\SessionFormation;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class QuestionnaireController extends Controller
{
    public function index(): View
    {
        return view('admin.questionnaires.index', [
            'questionnaires' => Questionnaire::with('sessionFormation')
                ->withCount(['questions', 'repondants'])
                ->latest()
                ->get(),
        ]);
    }

    public function create(): View
    {
        return view('admin.questionnaires.form', $this->options(new Questionnaire(['actif' => true])));
    }

    public function store(QuestionnaireRequest $request): RedirectResponse
    {
        $questionnaire = DB::transaction(function () use ($request) {
            $questionnaire = Questionnaire::create($request->safe()->only('titre', 'type', 'session_formation_id', 'description', 'actif'));
            $this->syncQuestions($questionnaire, $request);

            return $questionnaire;
        });

        return redirect()->route('admin.questionnaires.edit', $questionnaire)
            ->with('succes', 'Questionnaire créé.');
    }

    public function edit(Questionnaire $questionnaire): View
    {
        return view('admin.questionnaires.form', $this->options($questionnaire->load('questions')));
    }

    public function update(QuestionnaireRequest $request, Questionnaire $questionnaire): RedirectResponse
    {
        DB::transaction(function () use ($request, $questionnaire) {
            $questionnaire->update($request->safe()->only('titre', 'type', 'session_formation_id', 'description', 'actif'));
            $this->syncQuestions($questionnaire, $request);
        });

        return redirect()->route('admin.questionnaires.edit', $questionnaire)
            ->with('succes', 'Questionnaire enregistré.');
    }

    public function destroy(Questionnaire $questionnaire): RedirectResponse
    {
        $questionnaire->delete();

        return redirect()->route('admin.questionnaires.index')->with('succes', 'Questionnaire supprimé.');
    }

    public function resultats(Questionnaire $questionnaire): View
    {
        $questionnaire->load('questions.reponses', 'repondants');

        return view('admin.questionnaires.resultats', compact('questionnaire'));
    }

    private function syncQuestions(Questionnaire $questionnaire, QuestionnaireRequest $request): void
    {
        $questions = $request->questionsNormalisees();
        $conserves = collect($questions)->pluck('id')->filter()->all();

        $questionnaire->questions()->whereNotIn('id', $conserves ?: [0])->delete();

        foreach ($questions as $data) {
            $questionnaire->questions()->updateOrCreate(
                ['id' => $data['id']],
                collect($data)->except('id')->all(),
            );
        }
    }

    /**
     * @return array<string, mixed>
     */
    private function options(Questionnaire $questionnaire): array
    {
        return [
            'questionnaire' => $questionnaire,
            'sessions' => SessionFormation::orderBy('nom')->get(['id', 'nom', 'num_GESCOF']),
            'typesQuestionnaire' => TypeQuestionnaire::cases(),
            'typesQuestion' => TypeQuestion::cases(),
        ];
    }
}
