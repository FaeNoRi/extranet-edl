<x-app-layout>
    <x-slot name="sousTitre">Bonjour {{ auth()->user()->prenom }}.</x-slot>
    <x-slot name="header">
        <h1 class="text-2xl font-semibold leading-tight sm:text-3xl">Espace formateur</h1>
    </x-slot>

    <x-formateur.shell active="dashboard">
        <section>
            <div class="mb-3 flex items-center justify-between gap-3">
                <h2 class="text-2xl font-semibold">Mes sessions</h2>
                @if ($sessions->isNotEmpty())
                    <a href="{{ route('formateur.sessions.index') }}" class="text-edl-bleu hover:underline">Tout voir <span aria-hidden="true">→</span></a>
                @endif
            </div>

            @if ($sessions->isEmpty())
                <p class="rounded-xl border border-edl-sable bg-white p-5 text-sm text-edl-texte-doux">Aucune session ne vous est rattachée pour le moment.</p>
            @else
                <div class="grid gap-4 sm:grid-cols-2">
                    @foreach ($sessions as $session)
                        @php
                            $taux = $session->jours_prevus_count > 0
                                ? (int) round(100 * $session->jours_realises_count / $session->jours_prevus_count)
                                : null;
                        @endphp
                        <a href="{{ route('formateur.sessions.show', $session) }}"
                           class="block rounded-xl border border-edl-sable bg-white p-4 transition hover:border-edl-orange hover:shadow-sm">
                            <div class="flex flex-wrap items-center gap-2 text-xs">
                                <span @class([
                                    'rounded-full px-2 py-0.5 font-semibold',
                                    'bg-edl-violet/10 text-edl-violet-fonce' => $session->isFpc(),
                                    'bg-edl-bleu/10 text-edl-bleu-fonce' => $session->isOp(),
                                ])>{{ $session->code_produit->value }}</span>
                                @if ($session->langue)
                                    <span class="rounded-full bg-edl-marron/10 px-2 py-0.5">{{ $session->langue }}</span>
                                @endif
                                @if ($session->distanciel)
                                    <span class="rounded-full bg-edl-bleu-vert/15 px-2 py-0.5 text-edl-bleu-vert-fonce">distanciel</span>
                                @endif
                            </div>
                            <p class="mt-2 text-lg font-semibold leading-snug">{{ $session->nom }}</p>
                            <p class="text-sm text-edl-texte-doux">
                                {{ $session->num_GESCOF }} · {{ $session->stagiaires_count }} stagiaire(s) · {{ $session->seances_count }} séance(s)
                            </p>
                            @if ($taux !== null)
                                <div class="mt-3 flex items-center gap-3">
                                    <div class="h-2 flex-1 overflow-hidden rounded-full bg-edl-sable/60" aria-hidden="true">
                                        <div @class([
                                            'h-2 rounded-full',
                                            'bg-edl-violet' => $session->isFpc(),
                                            'bg-edl-bleu' => $session->isOp(),
                                        ]) style="width: {{ min(100, $taux) }}%"></div>
                                    </div>
                                    <span class="text-xs text-edl-texte-doux">{{ $taux }} %<span class="sr-only"> du planning réalisé</span></span>
                                </div>
                            @endif
                        </a>
                    @endforeach
                </div>
            @endif
        </section>

        <x-admin.card titre="Séances récentes et à venir" icone="calendrier" couleur="orange">
            @if ($prochainesSeances->isEmpty())
                <p class="text-sm text-edl-texte-doux">Aucune séance.</p>
            @else
                <ul class="space-y-2.5">
                    @foreach ($prochainesSeances as $seance)
                        @php
                            $etat = $seance->date->isToday() ? 'jour' : ($seance->date->isPast() ? 'passee' : 'avenir');
                        @endphp
                        <li class="flex flex-wrap items-center gap-4 rounded-xl border border-edl-sable/70 bg-edl-creme/60 p-2.5 sm:flex-nowrap">
                            <span @class([
                                'w-14 flex-none rounded-lg py-1.5 text-center',
                                'bg-edl-orange text-gray-900' => $etat === 'jour',
                                'bg-edl-vert/15 text-edl-vert-fonce' => $etat === 'passee',
                                'border-2 border-edl-bleu-vert bg-white' => $etat === 'avenir',
                            ])>
                                <span class="block text-xs uppercase">{{ $seance->date->translatedFormat('M') }}</span>
                                <span class="block text-xl font-semibold leading-none tabular-nums">{{ $seance->date->format('d') }}</span>
                                <span class="sr-only">{{ $seance->date->format('Y') }}</span>
                            </span>
                            <span class="min-w-0 flex-1">
                                <span class="block font-semibold">{{ $seance->sessionFormation->nom }}</span>
                                @if ($seance->stagiaire)
                                    <span class="block text-sm text-edl-texte-doux">{{ $seance->stagiaire->nom_complet }}</span>
                                @endif
                            </span>
                            <span @class([
                                'rounded-full px-2.5 py-0.5 text-xs',
                                'bg-edl-orange/15 text-edl-orange-fonce' => $etat === 'jour',
                                'bg-edl-vert/15 text-edl-vert-fonce' => $etat === 'passee',
                                'bg-edl-bleu-vert/15 text-edl-bleu-vert-fonce' => $etat === 'avenir',
                            ])>{{ ['jour' => 'Aujourd\'hui', 'passee' => 'Réalisée', 'avenir' => 'À venir'][$etat] }}</span>
                            <a href="{{ route('formateur.seances.show', $seance) }}" class="inline-flex min-h-[40px] items-center rounded-lg border border-edl-bleu/20 bg-white px-3 text-sm text-edl-bleu hover:bg-edl-bleu/10">
                                Ouvrir<span class="sr-only"> la séance du {{ $seance->date->format('d/m/Y') }}</span>
                            </a>
                        </li>
                    @endforeach
                </ul>
            @endif
        </x-admin.card>
    </x-formateur.shell>
</x-app-layout>
