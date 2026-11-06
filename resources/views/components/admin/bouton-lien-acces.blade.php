{{-- Bouton « Lien d'accès » d'une ligne de liste (formateurs, stagiaires, administrateurs). --}}
@props(['utilisateur'])

<form method="POST" action="{{ route('admin.lien-acces', $utilisateur) }}" {{ $attributes->merge(['class' => 'inline']) }}
      x-data @submit="if (! confirm('Générer un lien de création du mot de passe pour ce compte ? Les liens précédents ne fonctionneront plus.')) $event.preventDefault()">
    @csrf
    <button class="text-edl-bleu hover:underline" title="Lien de création du mot de passe, à transmettre à la personne">Lien d'accès</button>
</form>
