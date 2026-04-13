<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DiagnosisController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\ChatController;

// 1. Landing Page (Halaman Depan)
Route::get('/', function () {
    return view('welcome');
})->name('landing');

// 2. Halaman Utama Aplikasi (Scan & Chat)
Route::get('/app', [DiagnosisController::class, 'index'])->name('home');

// 2. Proses Analisa (Upload ke AI Groq Vision)
Route::post('/analyze', [DiagnosisController::class, 'analyze'])->name('analyze');

// 3. Chat AI (Text, File, URL)
Route::post('/chat', [ChatController::class, 'sendMessage'])->name('chat.send');

// Test Route untuk Verifikasi Scraping (Bisa dihapus nanti)
Route::get('/test-scrape', function (Illuminate\Http\Request $request) {
    $url = $request->query('url');
    if (!$url)
        return "Silakan berikan parameter ?url=...";

    $groq = new App\Services\GroqService();
    $text = $groq->scrapeUrl($url);

    return "<pre>" . htmlspecialchars($text) . "</pre>";
});

// Authentication Routes (Login, Register, Logout, dll)
Auth::routes();

// --- GOOGLE OAUTH ROUTES ---
Route::get('auth/google', [\App\Http\Controllers\Auth\GoogleController::class, 'redirectToGoogle'])->name('google.login');
Route::get('auth/google/callback', [\App\Http\Controllers\Auth\GoogleController::class, 'handleGoogleCallback'])->name('google.callback');

// --- ADMIN ROUTES (Dilindungi Auth) ---
Route::middleware('auth')->group(function () {

    // --- TENTATIVE: ROUTE UNTUK SET ADMIN ---
    Route::get('/setup-admin', function () {
        try {
            $user = \App\Models\User::where('email', 'bebensutara@gmail.com')->first();
            if ($user) {
                $user->update(['is_admin' => true]);
                return "SUKSES! Akun bebensutara@gmail.com sekarang adalah Administrator.<br><a href='/monitoring-penelitian'>Ke Panel Monitoring</a>";
            }
            return "Gagal: Akun bebensutara@gmail.com belum login/terdaftar via Google. Silakan login terlebih dahulu.";
        } catch (\Exception $e) {
            return "Gagal: " . $e->getMessage();
        }
    });

    // Rute yang butuh akses penuh (Admin)
    Route::middleware('admin')->group(function () {
        // 3. Halaman Dashboard (Monitoring)
        Route::get('/monitoring-penelitian', [AdminController::class, 'index'])->name('admin.index');

        // 4. Hapus Data Diagnosa Valid
        Route::delete('/monitoring-penelitian/{id}', [AdminController::class, 'destroy'])->name('admin.destroy');

        // 5. Export Excel/CSV
        Route::get('/export-laporan', [AdminController::class, 'export'])->name('admin.export');

        // 6. Hapus Data Sampah (Salah Upload)
        Route::delete('/hapus-sampah/{id}', [AdminController::class, 'destroyFailed'])->name('admin.destroyFailed');
    });

}); // Akhir group auth
