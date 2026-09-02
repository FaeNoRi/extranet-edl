@php
    $edl = config('edl');
@endphp

<footer class="border-t border-gray-200 bg-white">
    <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
        <div class="grid gap-8 text-sm text-gray-600 sm:grid-cols-2 lg:grid-cols-4">
            <div>
                <p class="font-semibold text-edl-marron">{{ $edl['structure']['nom'] }}</p>
                @if ($edl['structure']['adresse'])
                    <p class="mt-2 whitespace-pre-line">{{ $edl['structure']['adresse'] }}</p>
                @endif
                @if ($edl['structure']['telephone'])
                    <p class="mt-1">Tél. {{ $edl['structure']['telephone'] }}</p>
                @endif
                <p class="mt-1">
                    <a class="hover:text-edl-bleu" href="mailto:{{ $edl['structure']['email'] }}">{{ $edl['structure']['email'] }}</a>
                </p>
            </div>

            <div>
                <p class="font-semibold text-edl-marron">Horaires d'ouverture</p>
                <p class="mt-2">{{ $edl['horaires'] }}</p>
            </div>

            <div>
                <p class="font-semibold text-edl-marron">Nous suivre</p>
                <ul class="mt-2 space-y-1">
                    <li><a class="hover:text-edl-bleu" href="{{ $edl['liens']['site'] }}" target="_blank" rel="noopener">Site internet</a></li>
                    <li><a class="hover:text-edl-bleu" href="{{ $edl['liens']['facebook'] }}" target="_blank" rel="noopener">Facebook</a></li>
                </ul>
            </div>

            <div>
                <p class="font-semibold text-edl-marron">Certifications &amp; financeurs</p>
                <div class="mt-2 flex flex-wrap gap-2">
                    @foreach ($edl['certifications'] as $label)
                        <span class="inline-flex items-center rounded border border-gray-300 px-2 py-1 text-xs font-medium text-gray-700">{{ $label }}</span>
                    @endforeach
                    @foreach ($edl['financeurs'] as $label)
                        <span class="inline-flex items-center rounded border border-gray-300 px-2 py-1 text-xs font-medium text-gray-700">{{ $label }}</span>
                    @endforeach
                </div>
                {{-- TODO : remplacer par les logos officiels dans public/img/partenaires/ --}}
            </div>
        </div>

        <p class="mt-8 text-xs text-gray-400">
            &copy; {{ date('Y') }} {{ $edl['structure']['nom'] }}. Tous droits réservés.
        </p>
    </div>
</footer>
