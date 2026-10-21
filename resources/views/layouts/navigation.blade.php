@php
    $user = Auth::user();
    $initiales = mb_strtoupper(mb_substr($user->prenom ?? '', 0, 1).mb_substr($user->nom ?? '', 0, 1)) ?: '?';
@endphp

{{-- Barre du haut : logo (retour au tableau de bord) et menu utilisateur. La navigation entre rubriques
     se fait par le menu latéral de chaque espace. --}}
<nav aria-label="Navigation principale" class="border-b border-edl-sable bg-white">
    <div class="mx-auto flex h-16 max-w-7xl items-center justify-between gap-4 px-4 sm:px-6 lg:px-8">
        <a href="{{ route('dashboard') }}" class="flex shrink-0 items-center gap-2 text-lg font-semibold text-edl-bleu">
            <img src="{{ asset(config('edl.logo')) }}" alt="" class="h-9 w-auto">
            <span>Extranet <span class="text-edl-rose">EDL+</span><span class="sr-only"> — tableau de bord</span></span>
        </a>

        <x-dropdown align="right" width="w-56">
            <x-slot name="trigger">
                <button aria-haspopup="true" :aria-expanded="open.toString()" class="inline-flex min-h-[44px] items-center gap-2 rounded-full border border-edl-sable bg-white py-1 pe-3 ps-1 text-sm font-medium leading-4 text-edl-marron transition duration-150 ease-in-out hover:bg-edl-creme focus:outline-none">
                    <span @class([
                        'flex h-8 w-8 items-center justify-center rounded-full text-sm font-semibold',
                        'bg-edl-bleu text-white' => $user->isAdmin(),
                        'bg-edl-orange text-gray-900' => $user->isFormateur(),
                        'bg-edl-rose text-white' => ! $user->isAdmin() && ! $user->isFormateur(),
                    ]) aria-hidden="true">{{ $initiales }}</span>
                    {{-- Sur petit écran, seules les initiales s'affichent : le nom reste lu par les lecteurs d'écran. --}}
                    <span class="sr-only sm:not-sr-only">
                        {{ $user->nom_complet }}
                        <span class="ms-1 text-xs text-edl-texte-doux">&middot; {{ $user->role->label() }}</span>
                    </span>

                    <svg class="h-4 w-4 fill-current" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" aria-hidden="true">
                        <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                    </svg>
                </button>
            </x-slot>

            <x-slot name="content">
                <div class="border-b border-edl-sable px-4 py-2 sm:hidden">
                    <p class="font-medium text-edl-marron">{{ $user->nom_complet }}</p>
                    <p class="text-sm text-edl-texte-doux">{{ $user->role->label() }}</p>
                </div>

                <x-dropdown-link :href="route('profile.edit')">
                    {{ __('Profil') }}
                </x-dropdown-link>

                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <x-dropdown-link :href="route('logout')"
                            x-on:click.prevent="$el.closest('form').submit()">
                        {{ __('Se déconnecter') }}
                    </x-dropdown-link>
                </form>
            </x-slot>
        </x-dropdown>
    </div>
</nav>
