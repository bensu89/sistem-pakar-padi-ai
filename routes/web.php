<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DiagnosisController;
use App\Http\Controllers\AdminController;

// 1. Halaman Utama (Scan & Chat)
Route::get('/', [DiagnosisController::class, 'index'])->name('home');

// 2. Proses Analisa (Upload ke AI)
Route::post('/analyze', [DiagnosisController::class, 'analyze'])->name('analyze');

// Authentication Routes (Login, Register, Logout, dll)
Auth::routes();

// --- ADMIN ROUTES (Dilindungi Auth) ---
Route::middleware('auth')->group(function () {

    // 3. Halaman Dashboard (Monitoring)
    Route::get('/monitoring-penelitian', [AdminController::class, 'index'])->name('admin.index');

    // 4. Hapus Data Diagnosa Valid
    Route::delete('/monitoring-penelitian/{id}', [AdminController::class, 'destroy'])->name('admin.destroy');

    // 5. Export Excel/CSV
    Route::get('/export-laporan', [AdminController::class, 'export'])->name('admin.export');

    // 6. Hapus Data Sampah (Salah Upload)
    Route::delete('/hapus-sampah/{id}', [AdminController::class, 'destroyFailed'])->name('admin.destroyFailed');

}); // Akhir group auth
