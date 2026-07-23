<?php

use App\Http\Controllers\PosController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SaleController;
use App\Http\Controllers\StockMovementController;
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

    // Mouvements de stock : journal immuable (pas d'edit/delete, une erreur
    // se corrige par un mouvement inverse, jamais en réécrivant l'historique).
    Route::get('/stock', [StockMovementController::class, 'index'])->name('stock.index');
    Route::post('/stock', [StockMovementController::class, 'store'])->name('stock.store');
});

// modify by claude
Route::middleware(['auth', 'verified', 'role:admin'])->group(function () {
    Route::resource('users', UserController::class)->only(['index', 'create', 'store']);
});

// modify by claude
Route::middleware(['auth', 'verified', 'role:admin|vendeur'])->group(function () {
    Route::get('/pos', [PosController::class, 'index'])->name('pos.index');
    Route::post('/pos', [PosController::class, 'store'])->name('pos.store');

    Route::get('/sales/{sale}/receipt', [SaleController::class, 'receipt'])->name('sales.receipt');
    Route::get('/sales/{sale}/receipt.pdf', [SaleController::class, 'receiptPdf'])->name('sales.receipt.pdf');
});

require __DIR__.'/auth.php';
