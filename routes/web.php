<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\BengkelController;
use App\Http\Controllers\MekanikController;
use Illuminate\Support\Facades\Route;

Route::get('/', [BengkelController::class, 'index'])->name('home');
Route::get('/antrean', [BengkelController::class, 'index'])->name('bengkel.index');

Route::get('/login', [AuthController::class, 'showLoginForm'])->middleware('guest')->name('login');
Route::post('/login', [AuthController::class, 'login'])->middleware('guest')->name('login.store');
Route::get('/register', [AuthController::class, 'showRegistrationForm'])->middleware('guest')->name('register');
Route::post('/register', [AuthController::class, 'register'])->middleware('guest')->name('register.store');
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth')->name('logout');

Route::middleware(['auth', 'role:admin,pelanggan'])->group(function () {
    Route::post('/antrean', [BengkelController::class, 'storeAntrean'])->name('antrean.store');
});

Route::middleware(['auth', 'role:pelanggan'])->group(function () {
    Route::get('/pelanggan', [BengkelController::class, 'pelanggan'])->name('pelanggan.dashboard');
});

Route::middleware(['auth', 'role:admin'])->group(function () {
    Route::get('/admin', [BengkelController::class, 'admin'])->name('bengkel.admin');
    Route::post('/admin/update/{id}', [BengkelController::class, 'updateStatus'])->name('antrean.update');
    Route::post('/admin/mekanik', [BengkelController::class, 'storeMekanik'])->name('admin.mekanik.store');
    Route::put('/admin/mekanik/{mekanik}', [BengkelController::class, 'updateMekanik'])->name('admin.mekanik.update');
    Route::delete('/admin/mekanik/{mekanik}', [BengkelController::class, 'destroyMekanik'])->name('admin.mekanik.destroy');
    Route::post('/admin/users', [BengkelController::class, 'storeUser'])->name('admin.users.store');
    Route::put('/admin/users/{user}', [BengkelController::class, 'updateUser'])->name('admin.users.update');
    Route::delete('/admin/users/{user}', [BengkelController::class, 'destroyUser'])->name('admin.users.destroy');
    Route::get('/kasir', [BengkelController::class, 'kasir'])->name('bengkel.kasir');
    Route::post('/kasir/bayar/{transaksi}', [BengkelController::class, 'bayar'])->name('kasir.bayar');
    Route::get('/kasir/struk/{transaksi}', [BengkelController::class, 'struk'])->name('kasir.struk');
});

Route::middleware(['auth', 'role:mekanik'])->prefix('mekanik')->name('mekanik.')->group(function () {
    Route::get('/', [MekanikController::class, 'index'])->name('index');
    Route::post('/antrean/{antrean}/ambil', [MekanikController::class, 'take'])->name('antrean.take');
    Route::put('/antrean/{antrean}', [MekanikController::class, 'update'])->name('antrean.update');
});
