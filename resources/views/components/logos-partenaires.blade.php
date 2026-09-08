@props(['logos' => [], 'compact' => false])

@if (! empty($logos))
    <ul {{ $attributes->merge(['class' => 'flex flex-wrap items-center gap-3']) }}>
        @foreach ($logos as $logo)
            <li>
                @php
                    $cadre = 'flex items-center justify-center rounded-lg border border-gray-200 bg-white shadow-sm '
                        . ($compact ? 'h-12 w-24 p-2' : 'h-20 w-36 p-3');
                @endphp
                @if (! empty($logo['url']))
                    <a href="{{ $logo['url'] }}" target="_blank" rel="noopener"
                       class="{{ $cadre }} transition hover:border-edl-bleu/40 hover:shadow"
                       title="{{ $logo['nom'] }}">
                        <img src="{{ asset($logo['logo']) }}" alt="{{ $logo['nom'] }}"
                             class="max-h-full max-w-full object-contain" loading="lazy">
                    </a>
                @else
                    <span class="{{ $cadre }}" title="{{ $logo['nom'] }}">
                        <img src="{{ asset($logo['logo']) }}" alt="{{ $logo['nom'] }}"
                             class="max-h-full max-w-full object-contain" loading="lazy">
                    </span>
                @endif
            </li>
        @endforeach
    </ul>
@endif
