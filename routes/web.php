<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DiagnosisController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\ChatController;
use Illuminate\Support\Facades\DB;

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

Route::get('/health', function () {
    $status = 'ok';
    $checks = [
        'backend' => 'ok',
        'database' => 'unknown',
        'groq' => 'unknown',
        'gee' => 'unknown',
    ];

    try {
        DB::select('select 1');
        $checks['database'] = 'ok';
    } catch (\Throwable $e) {
        $checks['database'] = 'error';
        $status = 'degraded';
    }

    $groqReady = filled(config('services.groq.api_key'));
    $geeReady = filled(config('services.gee.client_email')) && filled(config('services.gee.private_key'));

    $checks['groq'] = $groqReady ? 'ok' : 'missing_config';
    $checks['gee'] = $geeReady ? 'ok' : 'missing_config';

    // Deep check (optional): /health?deep=1
    if ($geeReady && request()->boolean('deep')) {
        $checks['gee'] = \Illuminate\Support\Facades\Cache::remember('pohaci_gee_health', 300, function () {
            try {
                $process = new \Symfony\Component\Process\Process(['node', base_path('scripts/gee_fetch.js')]);
                $env = getenv();
                if (!is_array($env)) {
                    $env = $_SERVER;
                }

                $env['PATH'] = getenv('PATH') ?: ($_SERVER['PATH'] ?? '');
                $env['SystemRoot'] = getenv('SystemRoot') ?: ($_SERVER['SystemRoot'] ?? 'C:\\WINDOWS');
                $env['SystemDrive'] = getenv('SystemDrive') ?: ($_SERVER['SystemDrive'] ?? 'C:');
                $env['GEE_CLIENT_EMAIL'] = config('services.gee.client_email');
                $env['GEE_PRIVATE_KEY'] = config('services.gee.private_key');
                $env['GEE_LATITUDE'] = (string) (-6.8403);
                $env['GEE_LONGITUDE'] = (string) (108.0886);
                $env['GEE_START_DATE'] = now()->subDays(14)->format('Y-m-d');
                $env['GEE_END_DATE'] = now()->format('Y-m-d');

                $process->setEnv($env);
                $process->setTimeout(20);
                $process->run();

                $out = trim($process->getOutput());
                $json = $out !== '' ? json_decode($out, true) : null;
                if (!$process->isSuccessful()) {
                    return 'error';
                }
                if (!is_array($json)) {
                    return 'error';
                }

                $ndvi = data_get($json, 'data.data.NDVI');
                return $ndvi !== null ? 'ok' : 'error';
            } catch (\Throwable $e) {
                return 'error';
            }
        });

        if ($checks['gee'] !== 'ok') {
            $status = 'degraded';
        }
    }

    if (!$groqReady || !$geeReady) {
        $status = 'degraded';
    }

    return response()->json([
        'status' => $status,
        'checks' => $checks,
        'timestamp' => now()->toIso8601String(),
    ]);
});

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
