<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
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
