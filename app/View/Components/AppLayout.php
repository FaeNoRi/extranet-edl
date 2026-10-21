<?php

namespace App\View\Components;

use Illuminate\View\Component;
use Illuminate\View\View;

class AppLayout extends Component
{
    /**
     * Couleur du bandeau de titre selon l'espace : bleu pour l'administration, orange pour les
     * formateurs, rose pour les stagiaires (pages communes comme le profil : d'après le rôle).
     */
    public function espace(): string
    {
        $route = request()->route()?->getName() ?? '';
        $user = auth()->user();

        return match (true) {
            str_starts_with($route, 'admin.') => 'admin',
            str_starts_with($route, 'formateur.') => 'formateur',
            str_starts_with($route, 'stagiaire.') => 'stagiaire',
            (bool) $user?->isFormateur() => 'formateur',
            (bool) $user?->isAdmin() => 'admin',
            default => 'stagiaire',
        };
    }

    /**
     * Get the view / contents that represents the component.
     */
    public function render(): View
    {
        return view('layouts.app');
    }
}
