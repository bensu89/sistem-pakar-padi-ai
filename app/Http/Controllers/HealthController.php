<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Symfony\Component\Process\Process;

class HealthController extends Controller
{
    public function check(Request $request)
    {
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

        // Deep check (optional): ?deep=1
        if ($geeReady && $request->boolean('deep')) {
            $checks['gee'] = Cache::remember('pohaci_gee_health', 300, function () {
                try {
                    $process = new Process(['node', base_path('scripts/gee_fetch.js')]);
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

                    if (!$process->isSuccessful() || !is_array($json)) {
                        return 'error';
                    }

                    return data_get($json, 'data.data.NDVI') !== null ? 'ok' : 'error';
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
    }
}
