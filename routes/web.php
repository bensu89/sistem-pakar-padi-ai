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

// 2. Proses Analisa (Upload ke AI Groq Vision) — max 10 req/menit per IP
Route::post('/analyze', [DiagnosisController::class, 'analyze'])->middleware('throttle:10,1')->name('analyze');

// 3. Chat AI (Text, File, URL) — max 30 req/menit per IP
Route::post('/chat', [ChatController::class, 'sendMessage'])->middleware('throttle:30,1')->name('chat.send');

Route::get('/health', [\App\Http\Controllers\HealthController::class, 'check']);

// Test Route untuk Verifikasi Scraping (Bisa dihapus nanti)
Route::get('/test-scrape', function (Illuminate\Http\Request $request) {
    abort_unless(app()->environment('local'), 404);

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
        abort_unless(app()->environment('local'), 404);

        $adminEmail = config('app.admin_email');
        abort_if(empty($adminEmail), 500, 'ADMIN_EMAIL belum dikonfigurasi di .env');

        try {
            $user = \App\Models\User::where('email', $adminEmail)->first();
            if ($user) {
                $user->update(['is_admin' => true]);
                return "SUKSES! Akun {$adminEmail} sekarang adalah Administrator.<br><a href='/monitoring-penelitian'>Ke Panel Monitoring</a>";
            }
            return "Gagal: Akun {$adminEmail} belum login/terdaftar via Google. Silakan login terlebih dahulu.";
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
