@php
    $edition = $questionnaire->exists;
    $questionsInitiales = old('questions', $edition
        ? $questionnaire->questions->map(fn ($q) => [
            'id' => $q->id,
            'libelle' => $q->libelle,
            'type' => $q->type->value,
            'obligatoire' => $q->obligatoire,
            'options' => implode("\n", $q->options ?? []),
        ])->all()
        : [['id' => null, 'libelle' => '', 'type' => 'echelle', 'obligatoire' => true, 'options' => '']]);
@endphp

<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-edl-marron">
            {{ $edition ? 'Modifier le questionnaire' : 'Nouveau questionnaire' }}
        </h2>
    </x-slot>

    <x-admin.shell active="questionnaires">
        <x-admin.card>
            <form method="POST"
                  action="{{ $edition ? route('admin.questionnaires.update', $questionnaire) : route('admin.questionnaires.store') }}"
                  class="space-y-5"
                  x-data="{
                      questions: {{ Illuminate\Support\Js::from($questionsInitiales) }},
                      ajouter() { this.questions.push({ id: null, libelle: '', type: 'echelle', obligatoire: true, options: '' }); },
                      retirer(i) { this.questions.splice(i, 1); },
                      attendOptions(t) { return t === 'choix_unique' || t === 'choix_multiple'; },
                  }">
                @csrf
                @if ($edition) @method('PUT') @endif

                <div class="grid gap-5 sm:grid-cols-2">
                    <div>
                        <x-input-label for="titre" :value="__('Titre')" />
                        <x-text-input id="titre" name="titre" class="mt-1 block w-full" :value="old('titre', $questionnaire->titre)" required />
                        <x-input-error :messages="$errors->get('titre')" class="mt-1" />
                    </div>
                    <div>
                        <x-input-label for="type" :value="__('Type')" />
                        <select id="type" name="type" class="mt-1 block w-full rounded-md border-gray-300 text-sm focus:border-edl-bleu focus:ring-edl-bleu">
                            @foreach ($typesQuestionnaire as $t)
                                <option value="{{ $t->value }}" @selected(old('type', $questionnaire->type?->value) === $t->value)>{{ $t->label() }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <x-input-label for="session_formation_id" :value="__('Portée')" />
                        <select id="session_formation_id" name="session_formation_id"
                                class="mt-1 block w-full rounded-md border-gray-300 text-sm focus:border-edl-bleu focus:ring-edl-bleu">
                            <option value="">Toutes les sessions</option>
                            @foreach ($sessions as $s)
                                <option value="{{ $s->id }}" @selected(old('session_formation_id', $questionnaire->session_formation_id) == $s->id)>{{ $s->num_GESCOF }} — {{ $s->nom }}</option>
                            @endforeach
                        </select>
                    </div>
                    <label class="flex items-center gap-2 self-end text-sm text-gray-700">
                        <input type="checkbox" name="actif" value="1" @checked(old('actif', $questionnaire->actif))
                               class="rounded border-gray-300 text-edl-bleu focus:ring-edl-bleu">
                        Actif (visible par les stagiaires)
                    </label>
                </div>

                <div>
                    <x-input-label for="description" :value="__('Introduction (facultative)')" />
                    <textarea id="description" name="description" rows="2"
                              class="mt-1 block w-full rounded-md border-gray-300 text-sm focus:border-edl-bleu focus:ring-edl-bleu">{{ old('description', $questionnaire->description) }}</textarea>
                </div>

                <div>
                    <div class="flex items-center justify-between">
                        <h3 class="text-sm font-semibold text-edl-marron">Questions</h3>
                        <button type="button" @click="ajouter()"
                                class="rounded-md border border-edl-bleu px-2 py-1 text-xs font-semibold text-edl-bleu hover:bg-edl-bleu/10">
                            + Ajouter une question
                        </button>
                    </div>

                    <div class="mt-3 space-y-3">
                        <template x-for="(question, i) in questions" :key="i">
                            <div class="rounded-md border border-gray-200 p-4">
                                <div class="flex gap-3">
                                    <input type="hidden" :name="`questions[${i}][id]`" :value="question.id">
                                    <div class="flex-1">
                                        <input type="text" :name="`questions[${i}][libelle]`" x-model="question.libelle"
                                               placeholder="Libellé de la question" required
                                               class="block w-full rounded-md border-gray-300 text-sm focus:border-edl-bleu focus:ring-edl-bleu">
                                    </div>
                                    <select :name="`questions[${i}][type]`" x-model="question.type"
                                            class="rounded-md border-gray-300 text-sm focus:border-edl-bleu focus:ring-edl-bleu">
                                        @foreach ($typesQuestion as $t)
                                            <option value="{{ $t->value }}">{{ $t->label() }}</option>
                                        @endforeach
                                    </select>
                                    <button type="button" @click="retirer(i)" class="text-edl-rose hover:underline" title="Retirer">✕</button>
                                </div>

                                <div class="mt-2" x-show="attendOptions(question.type)">
                                    <textarea :name="`questions[${i}][options]`" x-model="question.options" rows="3"
                                              placeholder="Une option par ligne"
                                              class="block w-full rounded-md border-gray-300 text-xs focus:border-edl-bleu focus:ring-edl-bleu"></textarea>
                                </div>

                                <label class="mt-2 flex items-center gap-2 text-xs text-gray-600">
                                    <input type="checkbox" :name="`questions[${i}][obligatoire]`" value="1" x-model="question.obligatoire"
                                           class="rounded border-gray-300 text-edl-bleu focus:ring-edl-bleu">
                                    Réponse obligatoire
                                </label>
                            </div>
                        </template>
                    </div>
                    <x-input-error :messages="$errors->get('questions')" class="mt-1" />
                    @foreach ($errors->get('questions.*') as $messages)
                        @foreach ($messages as $message)
                            <p class="mt-1 text-sm text-edl-rose">{{ $message }}</p>
                        @endforeach
                    @endforeach
                </div>

                <div class="flex items-center gap-3">
                    <x-primary-button>{{ $edition ? 'Enregistrer' : 'Créer' }}</x-primary-button>
                    <a href="{{ route('admin.questionnaires.index') }}" class="text-sm text-gray-500 hover:underline">Retour</a>
                </div>
            </form>
        </x-admin.card>
    </x-admin.shell>
</x-app-layout>
