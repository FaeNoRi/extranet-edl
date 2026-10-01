@props(['restant' => 0])

@if ($restant > 0)
    <div x-data="{ ouvert: false }" class="mt-2">
        <button type="button" @click="ouvert = !ouvert" class="text-xs font-medium text-edl-bleu hover:underline">
            <span x-show="!ouvert">Voir {{ $restant }} de plus</span>
            <span x-show="ouvert">Réduire</span>
        </button>
        <div x-show="ouvert" class="mt-2">
            {{ $plus }}
        </div>
    </div>
@endif
