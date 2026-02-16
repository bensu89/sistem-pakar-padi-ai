<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DiagnosisController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\PohaciController;

// 1. Landing Page (Halaman Depan)
Route::get('/', function () {
    return view('welcome');
})->name('landing');

// 2. Halaman Utama Aplikasi (Scan & Chat)
Route::get('/app', [DiagnosisController::class, 'index'])->name('home');

// 2. Proses Analisa (Upload ke AI Groq Vision)
Route::post('/analyze', [DiagnosisController::class, 'analyze'])->name('analyze');

// 3. Chat AI (Text, File, URL)
// 3. Chat AI (Text, File, URL)
Route::post('/chat', [ChatController::class, 'sendMessage'])->name('chat.send');

// 3b. Chat Scan (Pohaci Controller - Image Analysis / Scan & Chat)
Route::post('/chat-scan', [PohaciController::class, 'chat'])->name('pohaci.chat');

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

// --- ADMIN ROUTES (Dilindungi Auth) ---
Route::middleware('auth')->group(function () {

    // 3. Halaman Dashboard (Monitoring)
    Route::get('/monitoring-penelitian', [AdminController::class, 'index'])->name('admin.index');

    // --- TENTATIVE: ROUTE UNTUK RESET/BUAT USER ADMIN (HAPUS NANTI SETELAH DIPAKAI) ---
    Route::get('/setup-admin', function () {
        try {
            $user = \App\Models\User::updateOrCreate(
                ['email' => 'admin@padi.com'],
                [
                    'name' => 'Admin Padi',
                    'password' => bcrypt('password'), // Password default
                    'email_verified_at' => now(),
                ]
            );
            return "User Admin Berhasil Dibuat!<br>Email: admin@padi.com<br>Password: password<br><a href='/login'>Login Disini</a>";
        } catch (\Exception $e) {
            return "Gagal: " . $e->getMessage();
        }
    });

    // 4. Hapus Data Diagnosa Valid
    Route::delete('/monitoring-penelitian/{id}', [AdminController::class, 'destroy'])->name('admin.destroy');

    // 5. Export Excel/CSV
    Route::get('/export-laporan', [AdminController::class, 'export'])->name('admin.export');

    // 6. Hapus Data Sampah (Salah Upload)
    Route::delete('/hapus-sampah/{id}', [AdminController::class, 'destroyFailed'])->name('admin.destroyFailed');

}); // Akhir group auth
