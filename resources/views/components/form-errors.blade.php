{{-- Récapitulatif des erreurs de validation, annoncé par les lecteurs d'écran (WCAG 3.3.1 / 4.1.3). --}}
@if ($errors->any())
    <div role="alert" class="rounded-md border border-edl-rose/30 bg-edl-rose/10 px-4 py-3 text-sm text-edl-rose">
        <p class="font-medium">Le formulaire contient des erreurs :</p>
        <ul class="mt-1 list-disc pl-5">
            @foreach ($errors->all() as $erreur)<li>{{ $erreur }}</li>@endforeach
        </ul>
    </div>
@endif
