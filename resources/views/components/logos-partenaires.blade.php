@props(['compact' => false])

@php
    $logos = array_merge(config('edl.certifications', []), config('edl.financeurs', []));
@endphp

@if (! empty($logos))
    <div {{ $attributes->merge(['class' => 'flex flex-wrap items-center justify-center gap-3']) }}>
        @foreach ($logos as $logo)
            <span class="flex items-center justify-center rounded-lg border border-gray-200 bg-white shadow-sm
                         {{ $compact ? 'h-11 w-20 p-1.5' : 'h-16 w-28 p-2.5' }}"
                  title="{{ $logo['nom'] }}">
                <img src="{{ asset($logo['logo']) }}" alt="{{ $logo['nom'] }}"
                     class="max-h-full max-w-full object-contain" loading="lazy">
            </span>
        @endforeach
    </div>
@endif
