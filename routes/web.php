<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\BengkelController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route(auth()->check() ? 'bengkel.admin' : 'login');
})->name('home');

// Pelanggan (Lacak Antrean & Daftar)
Route::get('/antrean', [BengkelController::class, 'index'])->name('bengkel.index');
Route::post('/antrean', [BengkelController::class, 'storeAntrean'])->name('antrean.store');

Route::get('/login', [AuthController::class, 'showLoginForm'])->middleware('guest')->name('login');
Route::post('/login', [AuthController::class, 'login'])->middleware('guest')->name('login.store');
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth')->name('logout');

// Menu internal hanya bisa diakses setelah login.
Route::middleware('auth')->group(function () {
    Route::get('/admin', [BengkelController::class, 'admin'])->name('bengkel.admin');
    Route::post('/admin/update/{id}', [BengkelController::class, 'updateStatus'])->name('antrean.update');
    Route::post('/admin/mekanik', [BengkelController::class, 'storeMekanik'])->name('admin.mekanik.store');
    Route::put('/admin/mekanik/{mekanik}', [BengkelController::class, 'updateMekanik'])->name('admin.mekanik.update');
    Route::delete('/admin/mekanik/{mekanik}', [BengkelController::class, 'destroyMekanik'])->name('admin.mekanik.destroy');

    Route::get('/kasir', [BengkelController::class, 'kasir'])->name('bengkel.kasir');
    Route::post('/kasir/bayar/{id}', [BengkelController::class, 'bayar'])->name('kasir.bayar');
    Route::get('/kasir/struk/{transaksi}', [BengkelController::class, 'struk'])->name('kasir.struk');
});
