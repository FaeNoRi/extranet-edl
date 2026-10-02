<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-edl-marron">Administration</h2>
    </x-slot>

    <x-admin.shell active="rgpd" titre="RGPD — Registre des traitements">
        @if ($registre['valide_le'])
            <div class="rounded-md border border-edl-vert-fonce/30 bg-edl-vert-fonce/10 px-4 py-3 text-sm text-edl-vert-fonce">
                Registre validé par le responsable de traitement le {{ \Illuminate\Support\Carbon::parse($registre['valide_le'])->translatedFormat('d/m/Y') }}.
            </div>
        @else
            <div class="rounded-md border border-edl-jaune/50 bg-edl-jaune/20 px-4 py-3 text-sm text-edl-marron">
                <strong>Projet à valider.</strong> Ce registre est établi à partir du fonctionnement réel de l'application.
                Les bases légales et les durées de conservation sont des propositions à faire valider par le
                responsable de traitement (variable <code>EDL_REGISTRE_VALIDE_LE</code> une fois validé).
            </div>
        @endif

        <div class="flex flex-wrap items-center justify-between gap-3">
            <p class="max-w-2xl text-sm text-gray-600">
                Registre des activités de traitement (art. 30 du RGPD). Pour répondre à une demande d'accès d'une
                personne, utilisez le bouton « Exporter » des listes
                <a href="{{ route('admin.stagiaires.index') }}" class="text-edl-bleu hover:underline">Stagiaires</a> et
                <a href="{{ route('admin.formateurs.index') }}" class="text-edl-bleu hover:underline">Formateurs</a>.
            </p>
            <a href="{{ route('admin.rgpd.registre.pdf') }}"
               class="rounded-md bg-edl-bleu px-4 py-2 text-sm font-semibold text-white hover:bg-edl-vert-fonce">Télécharger en PDF</a>
        </div>

        <x-admin.card titre="Responsable de traitement et cadre général">
            <dl class="grid gap-x-6 gap-y-3 text-sm md:grid-cols-2">
                <div>
                    <dt class="text-gray-500">Responsable de traitement</dt>
                    <dd>{{ $registre['responsable']['nom'] }} — {{ $registre['responsable']['forme'] }}<br>
                        {{ $registre['responsable']['adresse'] }}<br>
                        SIRET {{ $registre['responsable']['siret'] }} · {{ $registre['responsable']['email'] }}</dd>
                </div>
                <div>
                    <dt class="text-gray-500">Délégué / référent à la protection des données</dt>
                    <dd>{{ $registre['responsable']['dpo'] ?: 'Non renseigné (variables EDL_DPO_NOM et EDL_DPO_EMAIL)' }}</dd>
                </div>
                <div>
                    <dt class="text-gray-500">Hébergeur (sous-traitant)</dt>
                    <dd>{{ $registre['hebergeur'] }}</dd>
                </div>
                <div>
                    <dt class="text-gray-500">Transferts hors Union européenne</dt>
                    <dd>{{ $registre['transferts'] }}</dd>
                </div>
                <div class="md:col-span-2">
                    <dt class="text-gray-500">Autres destinataires techniques</dt>
                    <dd><ul class="list-disc pl-5">@foreach ($registre['destinataires_techniques'] as $d)<li>{{ $d }}</li>@endforeach</ul></dd>
                </div>
                <div class="md:col-span-2">
                    <dt class="text-gray-500">Mesures de sécurité générales</dt>
                    <dd><ul class="list-disc pl-5">@foreach ($registre['securite'] as $m)<li>{{ $m }}</li>@endforeach</ul></dd>
                </div>
                <div class="md:col-span-2">
                    <dt class="text-edl-rose">Points d'attention généraux</dt>
                    <dd><ul class="list-disc pl-5">@foreach ($registre['points_generaux'] as $p)<li>{{ $p }}</li>@endforeach</ul></dd>
                </div>
            </dl>
        </x-admin.card>

        @foreach ($registre['traitements'] as $i => $t)
            <x-admin.card :titre="($i + 1).'. '.$t['nom']">
                <dl class="grid gap-x-6 gap-y-3 text-sm md:grid-cols-2">
                    <div><dt class="text-gray-500">Finalité</dt><dd>{{ $t['finalite'] }}</dd></div>
                    <div><dt class="text-gray-500">Base légale</dt><dd>{{ $t['base_legale'] }}</dd></div>
                    <div><dt class="text-gray-500">Personnes concernées</dt><dd>{{ $t['personnes'] }}</dd></div>
                    <div><dt class="text-gray-500">Catégories de données</dt><dd>{{ $t['donnees'] }}</dd></div>
                    <div><dt class="text-gray-500">Origine des données</dt><dd>{{ $t['origine'] }}</dd></div>
                    <div><dt class="text-gray-500">Destinataires</dt><dd>{{ $t['destinataires'] }}</dd></div>
                    <div class="md:col-span-2">
                        <dt class="text-gray-500">Durée de conservation</dt>
                        <dd><ul class="list-disc pl-5">@foreach ($t['conservation'] as $c)<li>{{ $c }}</li>@endforeach</ul></dd>
                    </div>
                    @if ($t['points_attention'])
                        <div class="md:col-span-2">
                            <dt class="text-edl-rose">Points d'attention</dt>
                            <dd><ul class="list-disc pl-5">@foreach ($t['points_attention'] as $p)<li>{{ $p }}</li>@endforeach</ul></dd>
                        </div>
                    @endif
                </dl>
            </x-admin.card>
        @endforeach
    </x-admin.shell>
</x-app-layout>
