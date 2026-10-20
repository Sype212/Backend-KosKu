<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\PemilikKosController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes - KosKu Backend
|--------------------------------------------------------------------------
*/

// ========================
// AUTH ROUTES (public)
// ========================
Route::prefix('auth')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
});

// ========================
// PROTECTED ROUTES (butuh token)
// ========================
Route::middleware('auth:sanctum')->group(function () {

    // Auth
    Route::prefix('auth')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::get('/profile', [AuthController::class, 'profile']);
        Route::put('/profile', [AuthController::class, 'updateProfile']);
    });

    Route::middleware('role:pemilik')->prefix('pemilik')->name('pemilik.')->group(function () {
        Route::get('/dashboard', [PemilikKosController::class, 'dashboard'])->name('dashboard');
        Route::get('/kos', [PemilikKosController::class, 'index'])->name('kos.index');
        Route::post('/kos', [PemilikKosController::class, 'store'])->name('kos.store');
        Route::get('/kos/{id}', [PemilikKosController::class, 'show'])->name('kos.show');
        Route::put('/kos/{id}', [PemilikKosController::class, 'update'])->name('kos.update');
        Route::delete('/kos/{id}', [PemilikKosController::class, 'destroy'])->name('kos.destroy');
        Route::patch('/kos/{id}/ketersediaan', [PemilikKosController::class, 'updateKetersediaan'])->name('kos.ketersediaan');
    });

    // ========================
    // Tambahkan route anggota lain di bawah sini
    // ========================

    // Route khusus ADMIN (pengelola platform):
    // Route::middleware('role:admin')->group(function () {
    //     Route::get('users', ...);           // lihat semua user
    // });

    // Route khusus PEMILIK kost:
    // Route::middleware('role:pemilik')->group(function () {
    //     Route::apiResource('kamar', KamarController::class);        // kelola kamar
    //     Route::apiResource('pembayaran', PembayaranController::class); // kelola pembayaran
    //     Route::get('penyewa', ...);         // lihat daftar penyewanya
    // });

    // Route khusus PENYEWA:
    // Route::middleware('role:penyewa')->group(function () {
    //     Route::get('kamar/saya', ...);      // lihat kamar yang disewa
    //     Route::get('pembayaran/saya', ...); // lihat tagihan sendiri
    // });

    // Route PEMILIK & PENYEWA (semua yang login bisa):
    // Route::middleware('role:pemilik,penyewa')->group(function () {
    //     Route::get('kost', [KostController::class, 'index']);
    // });

});
