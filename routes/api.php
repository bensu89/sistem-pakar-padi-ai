<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\PohaciAnalysisController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::get('/gee', function () {
    abort_unless(app()->environment('local'), 404);

    // 1. Eksekusi script Node.js menggunakan Symfony Process
    $process = new \Symfony\Component\Process\Process(['node', base_path('scripts/gee_fetch.js')]);
    
    // Pastikan seluruh environment Windows/OS terbawa (PATH, SystemRoot) 
    // agar Node dapat ditemukan dan tidak "crash" karena modul Crypto.
    $env = getenv(); 
    if (!is_array($env)) {
        $env = $_SERVER;
    }
    
    $env['PATH'] = getenv('PATH') ?: $_SERVER['PATH'] ?? '';
    $env['SystemRoot'] = getenv('SystemRoot') ?: $_SERVER['SystemRoot'] ?? 'C:\\WINDOWS';
    $env['SystemDrive'] = getenv('SystemDrive') ?: $_SERVER['SystemDrive'] ?? 'C:';
    
    // Tambahkan konfigurasi kredensial GEE.
    $env['GEE_CLIENT_EMAIL'] = env('GEE_CLIENT_EMAIL');
    $env['GEE_PRIVATE_KEY'] = env('GEE_PRIVATE_KEY');

    $process->setEnv($env);
    $process->run();

    // 2. Tangkap error jika Node gagal berjalan
    if (!$process->isSuccessful()) {
        return response()->json([
            'status' => 'error',
            'message' => 'Gagal terhubung ke script satelit.',
            'debug_error' => $process->getErrorOutput()
        ], 500);
    }

    // 3. Ambil Output (yang dikembalikan console.log dalam format JSON) dari Node
    $output = $process->getOutput();
    
    // Parsing ke array lalu kembalikan sbg response JSON Laravel
    $data = json_decode($output, true);
    
    return response()->json($data ?: [
        'status' => 'error', 
        'message' => 'Response tidak valid dari satelit.'
    ]);
});

Route::post('/pohaci/analyze', [PohaciAnalysisController::class, 'analyze']);

Route::get('/pohaci/health', function () {
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

    // Deep check (optional): /api/pohaci/health?deep=1
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
