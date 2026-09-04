<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-edl-marron">Espace formateur</h2>
    </x-slot>

    <x-formateur.shell active="dashboard" titre="Vue d'ensemble">
        <p class="text-sm text-gray-600">Bonjour {{ auth()->user()->prenom }}.</p>

        <x-admin.card titre="Mes sessions">
            @if ($sessions->isEmpty())
                <p class="text-sm text-gray-500">Aucune session ne vous est rattachée pour le moment.</p>
            @else
                <div class="grid gap-3 sm:grid-cols-2">
                    @foreach ($sessions as $session)
                        <a href="{{ route('formateur.sessions.show', $session) }}"
                           class="rounded-lg border border-gray-100 p-4 transition hover:border-edl-orange/40 hover:shadow-sm">
                            <div class="flex items-center gap-2">
                                <span @class([
                                    'rounded px-1.5 py-0.5 text-xs',
                                    'bg-edl-violet/15 text-edl-violet' => $session->isFpc(),
                                    'bg-edl-bleu/15 text-edl-bleu' => $session->isOp(),
                                ])>{{ $session->code_produit->value }}</span>
                                @if ($session->distanciel)<span class="rounded bg-edl-orange/15 px-1.5 py-0.5 text-xs text-edl-orange">distanciel</span>@endif
                            </div>
                            <p class="mt-1 font-medium text-gray-800">{{ $session->nom }}</p>
                            <p class="text-xs text-gray-400">
                                {{ $session->num_GESCOF }} · {{ $session->stagiaires_count }} stagiaire(s) · {{ $session->seances_count }} séance(s)
                            </p>
                        </a>
                    @endforeach
                </div>
            @endif
        </x-admin.card>

        <x-admin.card titre="Séances récentes et à venir">
            @if ($prochainesSeances->isEmpty())
                <p class="text-sm text-gray-500">Aucune séance.</p>
            @else
                <ul class="divide-y divide-gray-100 text-sm">
                    @foreach ($prochainesSeances as $seance)
                        <li class="flex items-center justify-between py-2">
                            <span>
                                <span class="font-medium tabular-nums">{{ $seance->date->format('d/m/Y') }}</span>
                                — {{ $seance->sessionFormation->nom }}
                                @if ($seance->stagiaire)<span class="text-gray-400">· {{ $seance->stagiaire->nom_complet }}</span>@endif
                            </span>
                            <a href="{{ route('formateur.seances.show', $seance) }}" class="text-edl-bleu hover:underline">Ouvrir</a>
                        </li>
                    @endforeach
                </ul>
            @endif
        </x-admin.card>
    </x-formateur.shell>
</x-app-layout>
