<x-legal.page titre="Déclaration d'accessibilité">
    <p>
        {{ config('edl.structure.nom') }} s'engage à rendre son extranet accessible
        conformément au <strong>RGAA</strong> et aux critères
        <strong>WCAG 2.1 niveau AA</strong>. Cette déclaration porte sur l'extranet EDL+
        (espaces stagiaire, formateur et administration).
    </p>

    <h2>État de conformité</h2>
    <p>
        L'extranet est <strong>partiellement conforme</strong>. Le dernier contrôle date du
        2 octobre 2026 ; les non-conformités connues sont listées ci-dessous.
    </p>

    <h2>Résultats du contrôle</h2>
    <ul>
        <li>
            Contrôle automatisé (outil axe-core, règles WCAG 2.1 A et AA) de 50 pages représentatives
            des quatre profils (stagiaire OP, stagiaire FPC, formateur, administration) et des pages
            publiques, sur écran d'ordinateur et à 375 px de large : aucune anomalie détectée.
        </li>
        <li>
            Vérification manuelle de la navigation au clavier (lien « Aller au contenu », ordre de
            tabulation, indicateur de focus visible), de l'affichage à 320 px de large sans défilement
            horizontal, et des contrastes de couleurs (texte au moins 4,5:1).
        </li>
        <li>
            Structure de chaque page : un titre de niveau 1, des zones identifiées (en-tête, navigation,
            contenu principal, pied de page), un titre de page descriptif, la langue déclarée, des champs
            de formulaire étiquetés et des messages de confirmation ou d'erreur annoncés aux lecteurs d'écran.
        </li>
    </ul>
    <p>
        Un contrôle automatisé ne détecte qu'une partie des défauts : la navigation a donc aussi été
        testée avec le lecteur d'écran NVDA (octobre 2026), sans blocage constaté. Un test avec
        VoiceOver reste à réaliser.
    </p>

    <h2>Contenus non accessibles</h2>
    <ul>
        <li>
            <strong>Lecteur de documents des stagiaires OP</strong> : pour protéger les contenus de
            la formation, les pages des documents sont affichées sous forme d'images. Un lecteur
            d'écran ne peut pas les lire. Le défilement, le zoom et les commandes restent utilisables
            au clavier. Pour obtenir un document dans une forme accessible, contactez l'administration.
        </li>
        <li>
            <strong>Documents, vidéos et sons déposés par les formateurs</strong> : leur accessibilité
            (sous-titres, transcriptions, balisage des PDF) dépend des fichiers fournis et n'est pas
            systématiquement vérifiée.
        </li>
    </ul>

    <h2>Retour d'information et contact</h2>
    <p>
        Si vous rencontrez un défaut d'accessibilité ou avez besoin d'un contenu sous une autre forme,
        contactez l'administration à
        <a href="mailto:{{ config('edl.structure.email') }}">{{ config('edl.structure.email') }}</a>.
        Nous vous répondrons dans les meilleurs délais.
    </p>

    <h2>Voies de recours</h2>
    <p>
        Si votre demande reste sans réponse satisfaisante, vous pouvez écrire au
        <strong>Défenseur des droits</strong> (<a href="https://formulaire.defenseurdesdroits.fr">formulaire en ligne</a>)
        ou lui adresser un courrier gratuit sans affranchissement : Défenseur des droits,
        Libre réponse 71120, 75342 Paris CEDEX 07.
    </p>
</x-legal.page>
