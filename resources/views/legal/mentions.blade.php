@php $s = config('edl.structure'); $l = config('edl.legal'); @endphp

<x-legal.page titre="Mentions légales">
    <h2>Éditeur</h2>
    <p>
        {{ $s['nom'] }}{{ $s['forme_juridique'] ? ' — '.$s['forme_juridique'] : '' }}<br>
        @if ($s['adresse']){{ $s['adresse'] }}<br>@endif
        @if ($s['telephone'])Tél. {{ $s['telephone'] }} — @endif
        <a href="mailto:{{ $s['email'] }}">{{ $s['email'] }}</a><br>
        @if ($s['siret'])SIRET : {{ $s['siret'] }}<br>@endif
        @if ($s['nda'])Numéro de déclaration d'activité de formation : {{ $s['nda'] }}<br>@endif
        @if ($s['directeur_publication'])Directeur de la publication : {{ $s['directeur_publication'] }}@endif
    </p>
    @php $manquants = collect(['numéro de déclaration d\'activité' => $s['nda'], 'directeur de la publication' => $s['directeur_publication']])->filter(fn ($v) => ! $v)->keys(); @endphp
    @if ($manquants->isNotEmpty())
        <p class="text-edl-rose">⚠ Reste à renseigner : {{ $manquants->implode(', ') }}.</p>
    @endif

    <h2>Hébergement</h2>
    <p>{{ $l['hebergeur'] ?: 'Hébergeur à préciser.' }}</p>

    <h2>Propriété intellectuelle</h2>
    <p>
        L'ensemble des contenus de cet extranet (textes, documents pédagogiques, ressources)
        est la propriété de {{ $s['nom'] }} ou de ses partenaires. Toute reproduction ou
        diffusion hors du cadre pédagogique de la formation est interdite.
    </p>

    <h2>Données personnelles</h2>
    <p>
        Le traitement des données personnelles est décrit dans la
        <a href="{{ route('legal.confidentialite') }}">politique de confidentialité</a>.
    </p>
</x-legal.page>
