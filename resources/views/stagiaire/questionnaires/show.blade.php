@php use App\Enums\TypeQuestion; @endphp

<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-edl-marron">{{ $questionnaire->titre }}</h2>
    </x-slot>

    <x-stagiaire.shell active="questionnaires">
        <x-admin.card>
            @if ($questionnaire->description)
                <p class="mb-4 text-sm text-gray-600">{{ $questionnaire->description }}</p>
            @endif

            <form method="POST" action="{{ route('stagiaire.questionnaires.store', $questionnaire) }}" class="space-y-6">
                @csrf

                @foreach ($questionnaire->questions as $question)
                    <fieldset>
                        <legend class="text-sm font-medium text-gray-800">
                            {{ $question->libelle }}
                            @if ($question->obligatoire)<span class="text-edl-rose">*</span>@endif
                        </legend>
                        <x-input-error :messages="$errors->get('reponses.'.$question->id)" class="mt-1" />

                        <div class="mt-2">
                            @switch($question->type)
                                @case(TypeQuestion::Echelle)
                                    <div class="flex gap-2">
                                        @for ($n = 1; $n <= 5; $n++)
                                            <label class="flex cursor-pointer flex-col items-center gap-1 text-xs">
                                                <input type="radio" name="reponses[{{ $question->id }}]" value="{{ $n }}"
                                                       @checked(old("reponses.$question->id") == $n)
                                                       class="border-gray-300 text-edl-bleu focus:ring-edl-bleu">
                                                {{ $n }}
                                            </label>
                                        @endfor
                                    </div>
                                    @break

                                @case(TypeQuestion::ChoixUnique)
                                    <div class="space-y-1">
                                        @foreach ($question->options ?? [] as $option)
                                            <label class="flex items-center gap-2 text-sm">
                                                <input type="radio" name="reponses[{{ $question->id }}]" value="{{ $option }}"
                                                       @checked(old("reponses.$question->id") === $option)
                                                       class="border-gray-300 text-edl-bleu focus:ring-edl-bleu">
                                                {{ $option }}
                                            </label>
                                        @endforeach
                                    </div>
                                    @break

                                @case(TypeQuestion::ChoixMultiple)
                                    <div class="space-y-1">
                                        @foreach ($question->options ?? [] as $option)
                                            <label class="flex items-center gap-2 text-sm">
                                                <input type="checkbox" name="reponses[{{ $question->id }}][]" value="{{ $option }}"
                                                       @checked(in_array($option, (array) old("reponses.$question->id", [])))
                                                       class="rounded border-gray-300 text-edl-bleu focus:ring-edl-bleu">
                                                {{ $option }}
                                            </label>
                                        @endforeach
                                    </div>
                                    @break

                                @default
                                    <textarea name="reponses[{{ $question->id }}]" rows="3"
                                              class="block w-full rounded-md border-gray-300 text-sm focus:border-edl-bleu focus:ring-edl-bleu">{{ old("reponses.$question->id") }}</textarea>
                            @endswitch
                        </div>
                    </fieldset>
                @endforeach

                <div class="flex items-center gap-3">
                    <x-primary-button>Envoyer mes réponses</x-primary-button>
                    <a href="{{ route('stagiaire.questionnaires.index') }}" class="text-sm text-gray-500 hover:underline">Annuler</a>
                </div>
            </form>
        </x-admin.card>
    </x-stagiaire.shell>
</x-app-layout>
