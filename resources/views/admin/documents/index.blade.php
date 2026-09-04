@use('App\Http\Controllers\Admin\DocumentController')

<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-edl-marron">Documents de la structure</h2>
    </x-slot>

    <x-admin.shell active="documents" titre="Documents de la structure">
        <p class="text-sm text-gray-600">
            Documents communs à tous les stagiaires (bloc « Présentation de la structure »).
            Les documents personnalisés d'une session se gèrent depuis la fiche de la session.
        </p>

        <x-admin.card titre="Ajouter un document">
            <form method="POST" action="{{ route('admin.documents.store') }}" enctype="multipart/form-data"
                  class="flex flex-wrap items-end gap-3">
                @csrf
                <input type="hidden" name="categorie" value="presentation_structure">
                <div>
                    <x-input-label for="nom" :value="__('Intitulé')" />
                    <select id="nom" name="nom"
                            class="mt-1 block rounded-md border-gray-300 text-sm focus:border-edl-bleu focus:ring-edl-bleu">
                        @foreach (DocumentController::typesStructure() as $type)
                            <option value="{{ $type }}">{{ $type }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <x-input-label for="fichier" :value="__('Fichier')" />
                    <input id="fichier" name="fichier" type="file" required
                           class="mt-1 block text-sm text-gray-700 file:mr-3 file:rounded-md file:border-0 file:bg-edl-bleu file:px-3 file:py-2 file:text-sm file:font-semibold file:text-white">
                </div>
                <x-primary-button>Ajouter</x-primary-button>
            </form>
            <x-input-error :messages="$errors->get('fichier')" class="mt-2" />
        </x-admin.card>

        <x-admin.card titre="Documents ({{ $documents->count() }})">
            @if ($documents->isEmpty())
                <p class="text-sm text-gray-500">Aucun document.</p>
            @else
                <ul class="divide-y divide-gray-100 text-sm">
                    @foreach ($documents as $document)
                        <li class="flex items-center justify-between py-2">
                            <span>{{ $document->nom }}
                                <span class="text-xs text-gray-400">· {{ number_format($document->taille / 1024, 0, ',', ' ') }} Ko</span>
                            </span>
                            <span class="flex gap-3">
                                <a href="{{ route('admin.documents.download', $document) }}" class="text-edl-bleu hover:underline">Télécharger</a>
                                <form method="POST" action="{{ route('admin.documents.destroy', $document) }}"
                                      onsubmit="return confirm('Supprimer ce document ?')">
                                    @csrf @method('DELETE')
                                    <button class="text-edl-rose hover:underline">Suppr.</button>
                                </form>
                            </span>
                        </li>
                    @endforeach
                </ul>
            @endif
        </x-admin.card>
    </x-admin.shell>
</x-app-layout>
