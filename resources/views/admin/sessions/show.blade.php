<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-edl-marron">Session</h2>
    </x-slot>

    <x-admin.shell active="sessions">
        <div class="flex flex-wrap items-start justify-between gap-3">
            <div>
                <h1 class="text-2xl font-semibold text-edl-marron">{{ $session->nom }}</h1>
                <p class="text-sm text-gray-500">
                    <span class="font-mono">{{ $session->num_GESCOF }}</span> ·
                    {{ $session->code_produit->value }} · {{ $session->langue }}
                    @if ($session->distanciel) · <span class="text-edl-orange">distanciel</span> @endif
                </p>
            </div>
            <div class="flex gap-2">
                @if ($session->isFpc())
                    <a href="{{ route('admin.sessions.archive', $session) }}"
                       class="rounded-md border border-edl-bleu px-3 py-2 text-sm font-semibold text-edl-bleu hover:bg-edl-bleu/10">
                        Télécharger (ZIP)
                    </a>
                @endif
                <a href="{{ route('admin.sessions.edit', $session) }}"
                   class="rounded-md bg-edl-bleu px-3 py-2 text-sm font-semibold text-white hover:bg-edl-vert-fonce">Modifier</a>
                <form method="POST" action="{{ route('admin.sessions.destroy', $session) }}"
                      onsubmit="return confirm('Supprimer cette session et ses séances ?')">
                    @csrf @method('DELETE')
                    <button class="rounded-md border border-edl-rose px-3 py-2 text-sm font-semibold text-edl-rose hover:bg-edl-rose/10">Supprimer</button>
                </form>
            </div>
        </div>

        <div class="grid gap-6 lg:grid-cols-3">
            <x-admin.card titre="Informations" class="lg:col-span-1">
                <dl class="space-y-2 text-sm">
                    <div><dt class="text-gray-500">Client</dt><dd>{{ $session->client?->nom ?? '—' }} @if($session->client?->email)<span class="text-gray-400">· {{ $session->client->email }}</span>@endif</dd></div>
                    <div><dt class="text-gray-500">Formateur référent</dt><dd>{{ $session->formateur?->nom_complet ?? '— à affecter —' }}</dd></div>
                    <div><dt class="text-gray-500">Équipe</dt><dd>{{ $session->formateurs->map->nom_complet->join(', ') ?: '—' }}</dd></div>
                    @if ($session->isOp())
                        <div><dt class="text-gray-500">Rythme</dt><dd>{{ $session->rythme_op === 'annee' ? "À l'année" : ($session->rythme_op ? 'Au trimestre' : '—') }}</dd></div>
                    @endif
                    @if ($session->isFpc() && $session->lien_teams)
                        <div><dt class="text-gray-500">Teams</dt><dd><a href="{{ $session->lien_teams }}" class="text-edl-bleu hover:underline" target="_blank" rel="noopener">Lien</a></dd></div>
                    @endif
                    @if ($session->objectifs)
                        <div><dt class="text-gray-500">Objectifs</dt><dd class="whitespace-pre-line">{{ $session->objectifs }}</dd></div>
                    @endif
                </dl>
            </x-admin.card>

            <x-admin.card titre="Planning" class="lg:col-span-2">
                <form method="POST" action="{{ route('admin.sessions.planning.sync', $session) }}" class="space-y-4">
                    @csrf
                    @if ($session->jours->isEmpty())
                        <p class="text-sm text-gray-500">Aucun jour. Ajoutez des dates ci-dessous.</p>
                    @else
                        <div class="flex flex-wrap gap-2">
                            @foreach ($session->jours as $jour)
                                <label @class([
                                    'flex items-center gap-1.5 rounded border px-2 py-1 text-sm',
                                    'border-edl-vert-fonce/40 bg-edl-vert-fonce/5' => $jour->actif,
                                    'border-gray-200 bg-gray-50 text-gray-400 line-through' => ! $jour->actif,
                                ])>
                                    <input type="checkbox" name="actifs[]" value="{{ $jour->id }}" @checked($jour->actif)
                                           class="rounded border-gray-300 text-edl-vert-fonce focus:ring-edl-vert-fonce">
                                    {{ $jour->date->format('d/m/Y') }}
                                </label>
                            @endforeach
                        </div>
                        <p class="text-xs text-gray-400">Décochez les jours sans séance (fériés, vacances).</p>
                    @endif

                    <div>
                        <x-input-label for="nouvelles_dates" :value="__('Ajouter des dates (jj/mm/aaaa, séparées par des espaces ou virgules)')" />
                        <textarea id="nouvelles_dates" name="nouvelles_dates" rows="2"
                                  class="mt-1 block w-full rounded-md border-gray-300 text-sm focus:border-edl-bleu focus:ring-edl-bleu"></textarea>
                    </div>

                    <x-primary-button>Mettre à jour le planning</x-primary-button>
                </form>
            </x-admin.card>
        </div>

        <x-admin.card>
            <x-slot name="titre">Stagiaires ({{ $session->stagiaires->count() }})</x-slot>
            @if ($session->stagiaires->isEmpty())
                <p class="text-sm text-gray-500">Aucun stagiaire inscrit.</p>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="border-b border-gray-200 text-left text-xs uppercase tracking-wide text-gray-500">
                            <tr><th class="py-2 pr-3">Nom</th><th class="py-2 pr-3">Identifiant</th><th class="py-2 pr-3">E-mail</th><th class="py-2 pr-3">Statut</th><th class="py-2"></th></tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach ($session->stagiaires as $stagiaire)
                                <tr @class(['text-gray-400' => $stagiaire->pivot->disparu_import_at])>
                                    <td class="py-2 pr-3 font-medium">{{ $stagiaire->nom_complet }}</td>
                                    <td class="py-2 pr-3">{{ $stagiaire->login }}</td>
                                    <td class="py-2 pr-3">{{ $stagiaire->email ?: '—' }}</td>
                                    <td class="py-2 pr-3">
                                        @if ($stagiaire->pivot->disparu_import_at)
                                            <span class="rounded bg-edl-jaune/20 px-1.5 py-0.5 text-xs text-edl-marron">absent du dernier import</span>
                                        @else
                                            <span class="text-xs text-edl-vert-fonce">actif</span>
                                        @endif
                                    </td>
                                    <td class="py-2 text-right">
                                        <form method="POST" action="{{ route('admin.stagiaires.destroy', $stagiaire) }}"
                                              onsubmit="return confirm('Supprimer ce compte stagiaire ?')">
                                            @csrf @method('DELETE')
                                            <button class="text-edl-rose hover:underline">Supprimer</button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </x-admin.card>
    </x-admin.shell>
</x-app-layout>
