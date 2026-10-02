@php use App\Enums\TypeQuestion; @endphp

<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-edl-marron">Résultats</h2>
    </x-slot>

    <x-admin.shell active="questionnaires">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-semibold text-edl-marron">{{ $questionnaire->titre }}</h1>
                <p class="text-sm text-gray-500">
                    {{ $questionnaire->type->label() }} ·
                    {{ $questionnaire->repondants->count() }} réponse(s)
                </p>
            </div>
            <a href="{{ route('admin.questionnaires.index') }}" class="text-sm text-edl-bleu hover:underline">← Questionnaires</a>
        </div>

        @forelse ($questionnaire->questions as $question)
            <x-admin.card :titre="$question->libelle">
                @php $valeurs = $question->reponses->pluck('valeur')->filter(fn ($v) => $v !== null && $v !== ''); @endphp

                @if ($valeurs->isEmpty())
                    <p class="text-sm text-gray-400">Aucune réponse.</p>
                @elseif ($question->type === TypeQuestion::Echelle)
                    @php $moyenne = round($valeurs->map(fn ($v) => (int) $v)->avg(), 2); @endphp
                    <p class="text-sm">Moyenne : <strong>{{ $moyenne }}</strong> / 5</p>
                    <div class="mt-2 space-y-1">
                        @for ($n = 5; $n >= 1; $n--)
                            @php $c = $valeurs->filter(fn ($v) => (int) $v === $n)->count(); @endphp
                            <div class="flex items-center gap-2 text-xs">
                                <span class="w-4 text-gray-500">{{ $n }}</span>
                                <div class="h-3 flex-1 rounded bg-gray-100">
                                    <div class="h-3 rounded bg-edl-bleu" style="width: {{ $valeurs->count() ? round($c / $valeurs->count() * 100) : 0 }}%"></div>
                                </div>
                                <span class="w-6 text-right text-gray-500">{{ $c }}</span>
                            </div>
                        @endfor
                    </div>
                @elseif (in_array($question->type, [TypeQuestion::ChoixUnique, TypeQuestion::ChoixMultiple]))
                    <ul class="space-y-1 text-sm">
                        @foreach ($question->options ?? [] as $option)
                            @php $c = $valeurs->filter(fn ($v) => str_contains($v, $option))->count(); @endphp
                            <li class="flex justify-between">
                                <span>{{ $option }}</span>
                                <span class="text-gray-500">{{ $c }}</span>
                            </li>
                        @endforeach
                    </ul>
                @else
                    <ul class="space-y-2 text-sm">
                        @foreach ($valeurs as $v)
                            <li class="rounded bg-gray-50 px-3 py-2 text-gray-700">{{ $v }}</li>
                        @endforeach
                    </ul>
                @endif
            </x-admin.card>
        @empty
            <x-admin.card><p class="text-sm text-gray-500">Ce questionnaire n'a pas de question.</p></x-admin.card>
        @endforelse
    </x-admin.shell>
</x-app-layout>
