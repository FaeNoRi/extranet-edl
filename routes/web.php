<?php

use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\DocumentController;
use App\Http\Controllers\Admin\FormateurController;
use App\Http\Controllers\Admin\GescofImportController;
use App\Http\Controllers\Admin\JournalController;
use App\Http\Controllers\Admin\PurgeController;
use App\Http\Controllers\Admin\SessionFormationController;
use App\Http\Controllers\Admin\SessionJourController;
use App\Http\Controllers\Admin\StagiaireController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Formateur\DashboardController as FormateurDashboardController;
use App\Http\Controllers\Formateur\RessourceController as FormateurRessourceController;
use App\Http\Controllers\Formateur\SeanceController as FormateurSeanceController;
use App\Http\Controllers\Formateur\SessionController as FormateurSessionController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Stagiaire\DashboardController as StagiaireDashboardController;
use App\Http\Controllers\Stagiaire\EmargementController as StagiaireEmargementController;
use App\Http\Controllers\Stagiaire\RessourcePedagogiqueController;
use App\Http\Controllers\Stagiaire\TelechargementController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
})->name('accueil');

Route::middleware('auth')->group(function () {
    // Point d'entrée unique : redirige vers le tableau de bord du rôle.
    Route::get('/tableau-de-bord', DashboardController::class)->name('dashboard');

    Route::get('/profil', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profil', [ProfileController::class, 'update'])->name('profile.update');
});

/*
|--------------------------------------------------------------------------
| Espaces réservés (scaffolding des phases suivantes)
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'role:admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        Route::get('/', [AdminDashboardController::class, 'index'])->name('dashboard');

        Route::get('imports', [GescofImportController::class, 'index'])->name('imports.index');
        Route::post('imports/simuler', [GescofImportController::class, 'simuler'])->name('imports.simuler');
        Route::post('imports/{import}/appliquer', [GescofImportController::class, 'appliquer'])->name('imports.appliquer');
        Route::get('imports/{import}', [GescofImportController::class, 'show'])->name('imports.show');

        Route::resource('formateurs', FormateurController::class)->except('show')
            ->parameters(['formateurs' => 'formateur']);

        Route::resource('sessions', SessionFormationController::class)
            ->parameters(['sessions' => 'session']);
        Route::post('sessions/{session}/planning', [SessionJourController::class, 'sync'])->name('sessions.planning.sync');
        Route::get('sessions/{session}/archive', [SessionFormationController::class, 'archive'])->name('sessions.archive');

        Route::get('stagiaires', [StagiaireController::class, 'index'])->name('stagiaires.index');
        Route::delete('stagiaires/{stagiaire}', [StagiaireController::class, 'destroy'])->name('stagiaires.destroy');

        Route::get('purges', [PurgeController::class, 'index'])->name('purges.index');
        Route::post('purges', [PurgeController::class, 'executer'])->name('purges.executer');

        Route::get('documents', [DocumentController::class, 'index'])->name('documents.index');
        Route::post('documents', [DocumentController::class, 'store'])->name('documents.store');
        Route::get('documents/{document}', [DocumentController::class, 'download'])->name('documents.download');
        Route::delete('documents/{document}', [DocumentController::class, 'destroy'])->name('documents.destroy');

        Route::get('journal', [JournalController::class, 'index'])->name('journal.index');
    });

Route::middleware(['auth', 'role:formateur'])
    ->prefix('formateur')
    ->name('formateur.')
    ->group(function () {
        Route::get('/', FormateurDashboardController::class)->name('dashboard');

        Route::get('sessions', [FormateurSessionController::class, 'index'])->name('sessions.index');
        Route::get('sessions/{session}', [FormateurSessionController::class, 'show'])->name('sessions.show');
        Route::post('sessions/{session}/ressources', [FormateurRessourceController::class, 'store'])->name('sessions.ressources.store');

        Route::get('sessions/{session}/seances/creer', [FormateurSeanceController::class, 'create'])->name('seances.create');
        Route::post('seances', [FormateurSeanceController::class, 'store'])->name('seances.store');
        Route::get('seances/{seance}', [FormateurSeanceController::class, 'show'])->name('seances.show');
        Route::get('seances/{seance}/modifier', [FormateurSeanceController::class, 'edit'])->name('seances.edit');
        Route::put('seances/{seance}', [FormateurSeanceController::class, 'update'])->name('seances.update');
        Route::delete('seances/{seance}', [FormateurSeanceController::class, 'destroy'])->name('seances.destroy');
        Route::get('seances/{seance}/fiche', [FormateurSeanceController::class, 'fiche'])->name('seances.fiche');

        Route::get('ressources/{ressource}', [FormateurRessourceController::class, 'download'])->name('ressources.download');
        Route::delete('ressources/{ressource}', [FormateurRessourceController::class, 'destroy'])->name('ressources.destroy');
    });

Route::middleware(['auth', 'role:stagiaire_op,stagiaire_fpc'])
    ->prefix('espace')
    ->name('stagiaire.')
    ->group(function () {
        Route::get('/', StagiaireDashboardController::class)->name('dashboard');

        Route::get('ressources', [RessourcePedagogiqueController::class, 'index'])->name('ressources.index');
        Route::get('ressources/{seance}', [RessourcePedagogiqueController::class, 'show'])->name('ressources.show');

        Route::get('documents/{document}', [TelechargementController::class, 'document'])->name('documents.download');
        Route::get('fichiers/{ressource}', [TelechargementController::class, 'ressource'])->name('ressources.download');

        Route::post('seances/{seance}/emargement', [StagiaireEmargementController::class, 'sign'])->name('emargement');
    });

require __DIR__.'/auth.php';
