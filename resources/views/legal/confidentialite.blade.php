@php $s = config('edl.structure'); $l = config('edl.legal'); @endphp

<x-legal.page titre="Politique de confidentialité">
    <p>
        {{ $s['nom'] }}, responsable de traitement, met en œuvre cet extranet pour assurer
        le suivi pédagogique des stagiaires. La présente politique décrit les traitements de
        données personnelles réalisés dans ce cadre, conformément au RGPD.
    </p>

    <h2>Données traitées</h2>
    <ul>
        <li><strong>Identité</strong> : nom, prénom, identifiant de connexion, adresse e-mail de contact.</li>
        <li><strong>Formation</strong> : session, client, formateur, séances, émargements, réponses aux questionnaires.</li>
        <li><strong>Connexion</strong> : journal des actions (qui a modifié quoi et quand), adresses IP techniques de session.</li>
    </ul>

    <h2>Origine des données</h2>
    <p>
        Les comptes stagiaires sont créés à partir de l'export GESCOF de l'École des Langues.
        Les comptes formateurs et administrateurs sont créés par l'administration.
    </p>

    <h2>Finalités &amp; base légale</h2>
    <ul>
        <li>Suivi pédagogique et mise à disposition des ressources — exécution de la convention de formation.</li>
        <li>Émargement et évaluation — obligation légale (formation professionnelle) et intérêt légitime.</li>
        <li>Journalisation des actions — intérêt légitime (sécurité, traçabilité).</li>
    </ul>

    <h2>Destinataires</h2>
    <p>
        Les données sont accessibles au stagiaire concerné, à son ou ses formateurs, et à
        l'administration de {{ $s['nom'] }}. Aucune donnée n'est cédée à des tiers ni
        utilisée à des fins commerciales.
    </p>

    <h2>Durées de conservation</h2>
    <ul>
        <li>Comptes stagiaires OP : {{ $l['conservation_op'] }}</li>
        <li>Comptes stagiaires FPC : {{ $l['conservation_fpc'] }}</li>
        <li>{{ $l['conservation_journal'] }}</li>
    </ul>

    <h2>Cookies</h2>
    <p>
        Cet extranet n'utilise que des cookies strictement nécessaires à l'authentification
        et à la sécurité (session, protection CSRF). Aucun cookie de mesure d'audience ou
        publicitaire n'est déposé.
    </p>

    <h2>Vos droits</h2>
    <p>
        Vous disposez d'un droit d'accès, de rectification, d'effacement, de limitation et
        d'opposition. Pour les exercer, contactez
        {{ $l['dpo_contact'] ?: 'l\'administration de l\'École des Langues' }}
        (<a href="mailto:{{ $s['email'] }}">{{ $s['email'] }}</a>). Vous pouvez également
        introduire une réclamation auprès de la CNIL (<a href="https://www.cnil.fr">www.cnil.fr</a>).
    </p>
</x-legal.page>
