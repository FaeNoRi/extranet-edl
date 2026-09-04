<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-edl-marron">Mon espace</h2>
    </x-slot>

    <x-stagiaire.shell active="dashboard">
        {{-- Bandeau de bienvenue --}}
        <div class="rounded-lg bg-white p-6 shadow-sm">
            <p class="text-sm text-gray-500">Bienvenue sur votre espace</p>
            <p class="mt-1 text-2xl font-semibold text-edl-marron">{{ $stagiaire->nom_complet }}</p>
            @if ($session)
                <p class="mt-2 font-medium text-edl-bleu">{{ $session->nom }}</p>
                @if ($session->formateur)
                    <div class="mt-2 text-sm text-gray-600">
                        <span class="text-gray-400">Formateur :</span> {{ $session->formateur->nom_complet }}
                        @if ($session->formateur->presentation)
                            <p class="mt-1 text-gray-500">{{ $session->formateur->presentation }}</p>
                        @endif
                    </div>
                @endif
            @else
                <p class="mt-2 text-sm text-gray-500">Aucune session n'est rattachée à votre compte.</p>
            @endif
        </div>

        @if ($session?->distanciel && $session->lien_teams)
            <div class="rounded-lg bg-white p-5 shadow-sm">
                <a href="{{ $session->lien_teams }}" target="_blank" rel="noopener"
                   class="inline-flex rounded-md bg-edl-bleu px-4 py-2 text-sm font-semibold text-white hover:bg-edl-vert-fonce">
                    Rejoindre la session Teams
                </a>
            </div>
        @endif

        {{-- Documents : 2 rectangles --}}
        <div class="grid gap-4 md:grid-cols-2">
            <x-admin.card titre="Présentation de la structure">
                @if ($documentsStructure->isEmpty())
                    <p class="text-sm text-gray-500">Aucun document.</p>
                @else
                    <ul class="divide-y divide-gray-100 text-sm">
                        @foreach ($documentsStructure as $doc)
                            <li class="py-2">
                                <a href="{{ route('stagiaire.documents.download', $doc) }}" class="text-edl-bleu hover:underline">
                                    {{ $doc->nom }}
                                </a>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </x-admin.card>

            <x-admin.card titre="Mes documents">
                @if ($mesDocuments->isEmpty())
                    <p class="text-sm text-gray-500">Aucun document pour votre session.</p>
                @else
                    <ul class="divide-y divide-gray-100 text-sm">
                        @foreach ($mesDocuments as $doc)
                            <li class="py-2">
                                <a href="{{ route('stagiaire.documents.download', $doc) }}" class="text-edl-bleu hover:underline">
                                    {{ $doc->nom }}
                                </a>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </x-admin.card>
        </div>

        {{-- Planning (surtout OP) --}}
        @if ($planning->isNotEmpty())
            <x-admin.card titre="Planning des séances">
                <div class="flex flex-wrap gap-2">
                    @foreach ($planning as $jour)
                        <span @class([
                            'rounded border px-2.5 py-1 text-sm tabular-nums',
                            'border-edl-vert-fonce/40 bg-edl-vert-fonce/5 font-medium' => $jour->date->isToday(),
                            'border-gray-200' => ! $jour->date->isToday(),
                            'text-gray-400' => $jour->date->isPast() && ! $jour->date->isToday(),
                        ])>
                            {{ $jour->date->translatedFormat('D d/m') }}
                        </span>
                    @endforeach
                </div>
            </x-admin.card>
        @endif

        <x-admin.card>
            <a href="{{ route('stagiaire.ressources.index') }}" class="text-sm font-medium text-edl-bleu hover:underline">
                Voir mes ressources pédagogiques →
            </a>
        </x-admin.card>
    </x-stagiaire.shell>
</x-app-layout>
