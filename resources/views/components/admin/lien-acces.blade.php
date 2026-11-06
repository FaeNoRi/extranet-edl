{{-- Lien de création du mot de passe venant d'être généré (Admin\LienAccesController), à transmettre
     par l'administrateur à la personne concernée. --}}
@if ($lien = session('lien_acces'))
    <div role="status" class="rounded-xl border border-edl-bleu/30 bg-edl-bleu/10 p-4 text-sm text-edl-bleu-fonce"
         x-data="{ copie: false }">
        <p class="font-semibold">Lien d'accès de {{ $lien['nom'] }} (identifiant {{ $lien['login'] }})</p>
        <p class="mt-1">
            Valable 48 heures, pour une seule utilisation ; les liens précédents de ce compte ne fonctionnent plus.
            Transmettez-le à la personne seule (message individuel), jamais sur un canal partagé.
        </p>
        <div class="mt-3 flex flex-wrap items-center gap-2">
            <label for="lien-acces" class="sr-only">Lien d'accès</label>
            <input id="lien-acces" type="text" readonly value="{{ $lien['url'] }}" x-ref="lien"
                   @focus="$event.target.select()"
                   class="min-w-0 flex-1 rounded-md border-gray-300 bg-white text-sm text-gray-800">
            <button type="button"
                    @click="navigator.clipboard.writeText($refs.lien.value).then(() => copie = true)"
                    class="rounded-md bg-edl-bleu px-3 py-2 text-sm font-semibold text-white hover:bg-edl-vert-fonce">
                <span x-show="! copie">Copier le lien</span>
                <span x-show="copie" x-cloak>Lien copié</span>
            </button>
        </div>
    </div>
@endif
