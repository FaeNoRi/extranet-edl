<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-edl-marron">Mes sessions</h2>
    </x-slot>

    <x-formateur.shell active="sessions" titre="Mes sessions">
        @if ($sessions->isEmpty())
            <x-admin.card><p class="text-sm text-gray-500">Aucune session.</p></x-admin.card>
        @else
            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($sessions as $session)
                    <a href="{{ route('formateur.sessions.show', $session) }}"
                       class="rounded-lg bg-white p-5 shadow-sm transition hover:shadow">
                        <div class="flex items-center gap-2">
                            <span @class([
                                'rounded px-1.5 py-0.5 text-xs',
                                'bg-edl-violet/15 text-edl-violet' => $session->isFpc(),
                                'bg-edl-bleu/15 text-edl-bleu' => $session->isOp(),
                            ])>{{ $session->code_produit->value }}</span>
                            <span class="text-xs text-gray-400">{{ $session->langue }}</span>
                        </div>
                        <p class="mt-2 font-medium text-gray-800">{{ $session->nom }}</p>
                        <p class="mt-1 text-xs text-gray-400">
                            {{ $session->num_GESCOF }}<br>
                            {{ $session->stagiaires_count }} stagiaire(s) · {{ $session->seances_count }} séance(s)
                        </p>
                    </a>
                @endforeach
            </div>
        @endif
    </x-formateur.shell>
</x-app-layout>
