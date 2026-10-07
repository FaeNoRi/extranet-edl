@props(['restant' => 0])

@if ($restant > 0)
    <div x-data="{ ouvert: false }">
        <button type="button" x-show="!ouvert" @click="ouvert = true" aria-expanded="false"
                class="mt-2 text-xs font-medium text-edl-bleu hover:underline">
            Voir {{ $restant }} de plus
        </button>

        <div x-show="ouvert">
            {{ $plus }}
            <button type="button" @click="ouvert = false" aria-expanded="true"
                    class="mt-2 text-xs font-medium text-edl-bleu hover:underline">
                Réduire
            </button>
        </div>
    </div>
@endif
