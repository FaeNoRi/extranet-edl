<li class="py-2">
    <div class="flex items-center justify-between gap-2">
        <a href="{{ route('admin.sessions.show', $ligne['session']) }}" class="font-medium text-edl-bleu hover:underline">
            {{ $ligne['session']->nom }}
        </a>
        <span class="whitespace-nowrap text-xs text-gray-500">
            {{ $ligne['realises'] }}/{{ $ligne['planifies'] }} jour(s)
            @if ($ligne['taux'] !== null) · {{ $ligne['taux'] }}% @endif
        </span>
    </div>
    @if ($ligne['taux'] !== null)
        <div class="mt-1 h-1.5 w-full rounded-full bg-gray-100">
            <div class="h-1.5 rounded-full bg-edl-vert-fonce" style="width: {{ min(100, $ligne['taux']) }}%"></div>
        </div>
    @endif
</li>
