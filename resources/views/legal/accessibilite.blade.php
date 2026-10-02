<x-legal.page titre="Déclaration d'accessibilité">
    <p>
        {{ config('edl.structure.nom') }} s'engage à rendre son extranet accessible
        conformément au référentiel <strong>RGAA</strong> et aux critères
        <strong>WCAG 2.1 niveau AA</strong>.
    </p>

    <h2>État de conformité</h2>
    <p>
        L'extranet est <strong>partiellement conforme</strong>. Un audit complet est
        programmé avant la mise en service. Les points déjà pris en compte :
    </p>
    <ul>
        <li>structure sémantique (titres, listes, régions), langue de la page déclarée ;</li>
        <li>navigation au clavier et indicateur de focus visible ;</li>
        <li>contrastes de la charte graphique vérifiés ;</li>
        <li>libellés explicites sur les champs de formulaire et messages d'erreur associés ;</li>
        <li>interface responsive (mobile, tablette, ordinateur).</li>
    </ul>

    <h2>Points restant à traiter</h2>
    <ul>
        <li>alternatives textuelles systématiques sur les documents et ressources ;</li>
        <li>audit des composants interactifs (menus, aperçu de documents) ;</li>
        <li>sous-titrage des ressources vidéo fournies par les formateurs.</li>
    </ul>

    <h2>Retour d'information</h2>
    <p>
        Si vous rencontrez un défaut d'accessibilité, contactez l'administration à
        <a href="mailto:{{ config('edl.structure.email') }}">{{ config('edl.structure.email') }}</a>.
    </p>
</x-legal.page>
