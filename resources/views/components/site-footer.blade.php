@php
    $edl = config('edl');
@endphp

<footer class="border-t border-gray-200 bg-white">
    <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
        <div class="grid gap-8 text-sm text-gray-600 sm:grid-cols-2">
            <div>
                <div class="flex items-center gap-2">
                    <img src="{{ asset($edl['logo']) }}" alt="" class="h-8 w-8 rounded-lg">
                    <p class="font-semibold text-edl-marron">{{ $edl['structure']['nom'] }}</p>
                </div>
                @if ($edl['structure']['adresse'])
                    <p class="mt-2">{{ $edl['structure']['adresse'] }}</p>
                @endif
                @if ($edl['structure']['telephone'])
                    <p class="mt-1">Tél. {{ $edl['structure']['telephone'] }}</p>
                @endif
                <p class="mt-1">
                    <a class="hover:text-edl-bleu" href="mailto:{{ $edl['structure']['email'] }}">{{ $edl['structure']['email'] }}</a>
                </p>
                <ul class="mt-3 flex gap-4">
                    <li><a class="hover:text-edl-bleu" href="{{ $edl['liens']['site'] }}" target="_blank" rel="noopener">Site internet</a></li>
                    <li><a class="hover:text-edl-bleu" href="{{ $edl['liens']['facebook'] }}" target="_blank" rel="noopener">Facebook</a></li>
                </ul>
            </div>

            <div>
                <p class="font-semibold text-edl-marron">Horaires d'ouverture</p>
                <table class="mt-2">
                    <tbody>
                        @foreach ($edl['horaires'] as $jour => $plage)
                            <tr>
                                <td class="pr-4 text-gray-500">{{ $jour }}</td>
                                <td class="tabular-nums">{{ $plage ?? 'Fermé' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <div class="mt-8 grid gap-8 border-t border-gray-100 pt-6 sm:grid-cols-[2fr_1fr]">
            <div>
                <p class="mb-3 text-xs font-semibold uppercase tracking-wide text-gray-400">Avec le soutien de</p>
                <x-logos-partenaires :logos="$edl['financeurs']" />
            </div>
            <div>
                <p class="mb-3 text-xs font-semibold uppercase tracking-wide text-gray-400">Certifications</p>
                <x-logos-partenaires :logos="$edl['certifications']" />
            </div>
        </div>

        <div class="mt-8 flex flex-col gap-2 border-t border-gray-100 pt-4 text-xs text-gray-400 sm:flex-row sm:items-center sm:justify-between">
            <p>&copy; {{ date('Y') }} {{ $edl['structure']['nom'] }}. Tous droits réservés.</p>
            <nav class="flex flex-wrap gap-x-4 gap-y-1">
                <a class="hover:text-edl-bleu" href="{{ route('legal.mentions') }}">Mentions légales</a>
                <a class="hover:text-edl-bleu" href="{{ route('legal.confidentialite') }}">Confidentialité</a>
                <a class="hover:text-edl-bleu" href="{{ route('legal.accessibilite') }}">Accessibilité</a>
            </nav>
        </div>
    </div>
</footer>
