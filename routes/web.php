<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProfileController;
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
        Route::view('/', 'dashboard.admin')->name('dashboard');
    });

Route::middleware(['auth', 'role:formateur'])
    ->prefix('formateur')
    ->name('formateur.')
    ->group(function () {
        Route::view('/', 'dashboard.formateur')->name('dashboard');
    });

Route::middleware(['auth', 'role:stagiaire_op,stagiaire_fpc'])
    ->prefix('espace')
    ->name('stagiaire.')
    ->group(function () {
        Route::view('/', 'dashboard.stagiaire')->name('dashboard');
    });

require __DIR__.'/auth.php';
