<li class="flex items-center justify-between py-2">
    <a href="{{ route('admin.questionnaires.resultats', $ligne['questionnaire']) }}" class="text-edl-bleu hover:underline">
        {{ $ligne['questionnaire']->titre }}
        <span class="text-xs text-gray-400">· {{ $ligne['questionnaire']->sessionFormation?->nom ?? 'commun' }}</span>
    </a>
    <span class="whitespace-nowrap text-xs text-gray-500">
        {{ $ligne['reponses'] }}/{{ $ligne['eligibles'] }}
        @if ($ligne['taux'] !== null) · {{ $ligne['taux'] }}% @endif
    </span>
</li>
