<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\ExportDonneesUtilisateurService;
use App\Support\RegistreTraitements;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;

class RgpdController extends Controller
{
    public function registre(): View
    {
        return view('admin.rgpd.registre', ['registre' => RegistreTraitements::donnees()]);
    }

    public function registrePdf(): Response
    {
        return Pdf::loadView('pdf.registre-traitements', ['registre' => RegistreTraitements::donnees()])
            ->setPaper('a4', 'landscape')
            ->download('registre-des-traitements.pdf');
    }

    public function export(Request $request, User $utilisateur, ExportDonneesUtilisateurService $export): Response
    {
        $donnees = $export->exporter($utilisateur, $request->user());

        activity('RGPD')
            ->performedOn($utilisateur)
            ->causedBy($request->user())
            ->log('Export des données personnelles');

        $nom = 'export-rgpd-'.Str::slug($utilisateur->login).'-'.now()->format('Ymd').'.json';

        return response()->streamDownload(
            fn () => print json_encode($donnees, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            $nom,
            ['Content-Type' => 'application/json; charset=utf-8'],
        );
    }
}
