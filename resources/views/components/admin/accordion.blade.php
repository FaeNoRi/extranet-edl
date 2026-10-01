@props(['titre'])

<div x-data="{ ouvert: false }">
    <button type="button" @click="ouvert = !ouvert" class="flex w-full items-center justify-between text-left">
        <span class="font-semibold text-gray-800">{{ $titre }}</span>
        <span class="text-gray-400" x-text="ouvert ? '−' : '+'"></span>
    </button>

    <div x-show="ouvert" class="mt-3">
        {{ $slot }}
    </div>
</div>
