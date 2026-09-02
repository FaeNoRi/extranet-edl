<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-edl-marron">Journal des actions</h2>
    </x-slot>

    <x-admin.shell active="journal">
        <x-admin.card>
            <form method="GET" class="mb-4 flex flex-wrap gap-3">
                <select name="type" class="rounded-md border-gray-300 text-sm focus:border-edl-bleu focus:ring-edl-bleu">
                    <option value="">Tous les objets</option>
                    @foreach ($types as $t)
                        <option value="{{ $t }}" @selected(request('type') === $t)>{{ $t }}</option>
                    @endforeach
                </select>
                <select name="evenement" class="rounded-md border-gray-300 text-sm focus:border-edl-bleu focus:ring-edl-bleu">
                    <option value="">Tous les événements</option>
                    @foreach (['created' => 'Création', 'updated' => 'Modification', 'deleted' => 'Suppression'] as $val => $lib)
                        <option value="{{ $val }}" @selected(request('evenement') === $val)>{{ $lib }}</option>
                    @endforeach
                </select>
                <x-secondary-button>Filtrer</x-secondary-button>
            </form>

            <div class="space-y-2">
                @forelse ($activites as $activite)
                    <details class="rounded-md border border-gray-100 bg-gray-50 px-4 py-2 text-sm">
                        <summary class="cursor-pointer list-none">
                            <span class="text-gray-400">{{ $activite->created_at->format('d/m/Y H:i') }}</span>
                            <span class="mx-1 font-medium text-gray-800">{{ $activite->causer?->nom_complet ?? 'Système' }}</span>
                            @php $e = ['created' => 'a créé', 'updated' => 'a modifié', 'deleted' => 'a supprimé'][$activite->event] ?? $activite->event; @endphp
                            {{ $e }}
                            <span class="text-gray-600">{{ $activite->log_name }}</span>
                            @if ($activite->subject_id) <span class="text-gray-400">#{{ $activite->subject_id }}</span> @endif
                        </summary>
                        @php
                            $changes = $activite->attribute_changes;
                            $fmt = fn ($v) => \Illuminate\Support\Str::limit(is_array($v) ? json_encode($v, JSON_UNESCAPED_UNICODE) : (string) ($v ?? '∅'), 80);
                        @endphp
                        @if ($changes && $changes->isNotEmpty() && ! empty($changes['attributes']))
                            <div class="mt-2 overflow-x-auto">
                                <table class="text-xs">
                                    <tbody>
                                        @foreach ($changes['attributes'] as $champ => $nouvelle)
                                            <tr>
                                                <td class="pr-3 font-medium text-gray-500">{{ $champ }}</td>
                                                <td class="pr-3 text-edl-rose line-through">{{ $fmt($changes['old'][$champ] ?? null) }}</td>
                                                <td class="text-edl-vert-fonce">{{ $fmt($nouvelle) }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @endif
                    </details>
                @empty
                    <p class="text-sm text-gray-500">Aucune activité enregistrée.</p>
                @endforelse
            </div>

            <div class="mt-4">{{ $activites->links() }}</div>
        </x-admin.card>
    </x-admin.shell>
</x-app-layout>
