<?php

namespace App\Traits;

use App\Models\PohaciConversation;
use App\Models\PohaciMessage;
use Illuminate\Http\Request;
use Symfony\Component\Process\Process;

trait HasSpatialHelpers
{
    protected function resolveCoordinates(Request $request, $file): ?array
    {
        if ($file && function_exists('exif_read_data')) {
            $exif = @exif_read_data($file->getRealPath(), 'GPS', true);
            if (is_array($exif)) {
                $latitude = $this->extractGpsCoordinate($exif, 'GPSLatitude', 'GPSLatitudeRef');
                $longitude = $this->extractGpsCoordinate($exif, 'GPSLongitude', 'GPSLongitudeRef');

                if ($latitude !== null && $longitude !== null) {
                    return [
                        'latitude' => $latitude,
                        'longitude' => $longitude,
                        'source' => 'exif',
                        'confidence' => 85,
                        'raw_payload' => $exif,
                    ];
                }
            }
        }

        if ($request->filled('latitude') && $request->filled('longitude')) {
            return [
                'latitude' => (float) $request->input('latitude'),
                'longitude' => (float) $request->input('longitude'),
                'source' => 'request',
                'confidence' => 100,
                'raw_payload' => ['source' => 'request'],
            ];
        }

        return null;
    }

    protected function extractGpsCoordinate(array $exif, string $key, string $refKey): ?float
    {
        if (empty($exif['GPS'][$key]) || empty($exif['GPS'][$refKey])) {
            return null;
        }

        $parts = $exif['GPS'][$key];
        if (!is_array($parts) || count($parts) < 3) {
            return null;
        }

        $degrees = $this->gpsValueToFloat($parts[0]);
        $minutes = $this->gpsValueToFloat($parts[1]);
        $seconds = $this->gpsValueToFloat($parts[2]);

        $decimal = $degrees + ($minutes / 60) + ($seconds / 3600);

        $ref = strtoupper((string) $exif['GPS'][$refKey]);
        if (in_array($ref, ['S', 'W'], true)) {
            $decimal *= -1;
        }

        return $decimal;
    }

    protected function gpsValueToFloat($value): float
    {
        if (is_string($value) && str_contains($value, '/')) {
            [$numerator, $denominator] = array_pad(explode('/', $value, 2), 2, 1);
            return (float) $numerator / max(1, (float) $denominator);
        }

        return (float) $value;
    }

    protected function fetchSpatialData(float $latitude, float $longitude): array
    {
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
        $env['GEE_LATITUDE'] = (string) $latitude;
        $env['GEE_LONGITUDE'] = (string) $longitude;
        $env['GEE_START_DATE'] = now()->subMonths(2)->format('Y-m-d');
        $env['GEE_END_DATE'] = now()->format('Y-m-d');

        $process->setEnv($env);
        $process->setTimeout(45);
        $process->run();

        $stdout = trim($process->getOutput());
        $decoded = $stdout !== '' ? json_decode($stdout, true) : null;

        if (!$process->isSuccessful()) {
            $message = trim($process->getErrorOutput());
            if (!$message && is_array($decoded)) {
                $message = (string) ($decoded['message'] ?? 'Gagal terhubung ke script satelit.');
            }
            throw new \RuntimeException($message ?: 'Gagal terhubung ke script satelit.');
        }

        if (!is_array($decoded)) {
            throw new \RuntimeException('Response satelit tidak valid.');
        }

        if (($decoded['status'] ?? null) === 'error') {
            throw new \RuntimeException((string) ($decoded['message'] ?? 'GEE error.'));
        }

        if (isset($decoded['data']) && is_array($decoded['data'])) {
            return $decoded['data'];
        }

        return $decoded;
    }

    protected function buildConversationContext(PohaciConversation $conversation, ?int $skipMessageId = null, int $limit = 10): ?string
    {
        $messages = PohaciMessage::query()
            ->where('conversation_id', $conversation->id)
            ->when($skipMessageId, function ($query) use ($skipMessageId) {
                $query->where('id', '<=', $skipMessageId);
            })
            ->orderByDesc('id')
            ->limit($limit)
            ->get()
            ->reverse()
            ->values();

        if ($messages->isEmpty()) {
            return null;
        }

        $lines = $messages->map(function (PohaciMessage $message) {
            $role = $message->sender_type === 'ai' ? 'AI' : 'User';
            $content = trim(preg_replace('/\s+/', ' ', strip_tags((string) $message->content)));
            $content = mb_substr($content, 0, 1000);

            return "{$role}: {$content}";
        })->all();

        return implode("\n", $lines);
    }
}
