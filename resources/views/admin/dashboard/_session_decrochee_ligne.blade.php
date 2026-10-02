<li>
    <a href="{{ route('admin.sessions.show', $session) }}" class="hover:underline">
        {{ $session->nom }}@if ($session->formateur) · {{ $session->formateur->nom_complet }} @endif
    </a>
</li>
