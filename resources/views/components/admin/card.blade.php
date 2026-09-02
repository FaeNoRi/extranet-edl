@props(['titre' => null, 'actions' => null])

<section {{ $attributes->merge(['class' => 'rounded-lg bg-white shadow-sm']) }}>
    @if ($titre || $actions)
        <header class="flex items-center justify-between border-b border-gray-100 px-5 py-3">
            <h2 class="font-semibold text-gray-800">{{ $titre }}</h2>
            @if ($actions)
                <div class="flex items-center gap-2">{{ $actions }}</div>
            @endif
        </header>
    @endif

    <div class="p-5">
        {{ $slot }}
    </div>
</section>
