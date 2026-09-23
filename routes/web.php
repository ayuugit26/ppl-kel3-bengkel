<?php

use App\Http\Controllers\BengkelController;
use Illuminate\Support\Facades\Route;

// Pelanggan (Lacak Antrean & Daftar)
Route::get('/', [BengkelController::class, 'index'])->name('bengkel.index');
Route::post('/antrean', [BengkelController::class, 'storeAntrean'])->name('antrean.store');

// Admin / Mekanik
Route::get('/admin', [BengkelController::class, 'admin'])->name('bengkel.admin');
Route::post('/admin/update/{id}', [BengkelController::class, 'updateStatus'])->name('antrean.update');

// Kasir
Route::get('/kasir', [BengkelController::class, 'kasir'])->name('bengkel.kasir');
Route::post('/kasir/bayar/{id}', [BengkelController::class, 'bayar'])->name('kasir.bayar');
