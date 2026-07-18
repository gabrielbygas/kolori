<?php

use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

// modify by claude
// Pas de page d'accueil publique : "/" renvoie vers le login (redirige vers
// le dashboard si déjà connecté, via RedirectIfAuthenticated sur /login).
Route::redirect('/', '/login');

Route::get('/dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// modify by claude
Route::middleware(['auth', 'verified', 'role:admin|logisticien'])->group(function () {
    Route::resource('products', ProductController::class)->only(['index', 'create', 'store', 'edit', 'update']);
});

// modify by claude
Route::middleware(['auth', 'verified', 'role:admin'])->group(function () {
    Route::resource('users', UserController::class)->only(['index', 'create', 'store']);
});

require __DIR__.'/auth.php';
