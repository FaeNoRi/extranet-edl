<x-app-layout>
    <x-slot name="header">
        <h1 class="text-2xl font-semibold leading-tight sm:text-3xl">Administrateurs</h1>
    </x-slot>

    <x-admin.shell active="administrateurs">
        <x-admin.card>
            <x-slot name="titre">Administrateurs ({{ $administrateurs->count() }})</x-slot>
            <x-slot name="actions">
                <a href="{{ route('admin.administrateurs.create') }}"
                   class="rounded-md bg-edl-bleu px-3 py-2 text-sm font-semibold text-white hover:bg-edl-vert-fonce">
                    Nouvel administrateur
                </a>
            </x-slot>

            <p class="mb-4 text-sm text-gray-500">
                Un compte nominatif par personne, jamais de compte partagé. Le compte créé à l'installation est à archiver
                dès que les comptes nominatifs fonctionnent.
            </p>

            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="border-b border-gray-200 text-left text-xs uppercase tracking-wide text-gray-500">
                        <tr>
                            <th class="py-2 pr-3">Nom</th>
                            <th class="py-2 pr-3">Identifiant</th>
                            <th class="py-2 pr-3">Adresse e-mail</th>
                            <th class="py-2"><span class="sr-only">Actions</span></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach ($administrateurs as $administrateur)
                            <tr>
                                <td class="py-2 pr-3 font-medium text-gray-800">
                                    {{ $administrateur->nom_complet }}
                                    @if ($administrateur->is(auth()->user()))
                                        <span class="ml-1 text-xs font-normal text-gray-500">(vous)</span>
                                    @endif
                                </td>
                                <td class="py-2 pr-3 text-gray-500">{{ $administrateur->login }}</td>
                                <td class="py-2 pr-3 text-gray-500">{{ $administrateur->email }}</td>
                                <td class="whitespace-nowrap py-2 text-right">
                                    <a href="{{ route('admin.administrateurs.edit', $administrateur) }}" class="text-edl-bleu hover:underline">Modifier</a>
                                    <x-admin.bouton-lien-acces :utilisateur="$administrateur" class="ml-3" />
                                    @unless ($administrateur->is(auth()->user()))
                                        <form method="POST" action="{{ route('admin.administrateurs.destroy', $administrateur) }}" class="ml-3 inline"
                                              x-data @submit="if (! confirm('Archiver ce compte administrateur ?')) $event.preventDefault()">
                                            @csrf @method('DELETE')
                                            <button class="text-edl-rose hover:underline">Archiver</button>
                                        </form>
                                    @endunless
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </x-admin.card>
    </x-admin.shell>
</x-app-layout>
