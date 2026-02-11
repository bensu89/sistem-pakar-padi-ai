<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DiagnosisController; 
use App\Http\Controllers\AdminController;

// 1. Halaman Utama (Scan & Chat)
Route::get('/', [DiagnosisController::class, 'index'])->name('home');

// 2. Proses Analisa (Upload ke AI)
Route::post('/analyze', [DiagnosisController::class, 'analyze'])->name('analyze');

// 3. Halaman Dashboard (Monitoring)
// Pastikan nama controllernya AdminController, methodnya index
Route::get('/monitoring-penelitian', [AdminController::class, 'index'])->name('admin.index');

// 4. Hapus Data
Route::delete('/monitoring-penelitian/{id}', [AdminController::class, 'destroy'])->name('admin.destroy');

// 5. Export Excel/CSV (Cukup satu baris saja)
Route::get('/export-laporan', [AdminController::class, 'export'])->name('admin.export');
// Rute Hapus Data Sampah (Salah Upload)
Route::delete('/hapus-sampah/{id}', [App\Http\Controllers\AdminController::class, 'destroyFailed'])->name('admin.destroyFailed');