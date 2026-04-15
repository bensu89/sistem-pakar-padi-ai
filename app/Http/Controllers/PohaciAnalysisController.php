<?php

namespace App\Http\Controllers;

use App\Models\PohaciConversation;
use App\Models\PohaciLocation;
use App\Models\PohaciMessage;
use App\Models\PohaciMonitoring;
use App\Models\PohaciRecommendation;
use App\Models\PohaciSatelliteObservation;
use App\Services\GroqService;
use Illuminate\Http\Request;
use Symfony\Component\Process\Process;

class PohaciAnalysisController extends Controller
{
    public function __construct(
        protected GroqService $groq,
        protected \App\Services\SupabaseStorageService $supabase
    )
    {
    }

    public function analyze(Request $request)
    {
        $validated = $request->validate([
            'conversation_id' => 'nullable|integer|exists:pohaci_conversations,id',
            'message' => 'nullable|string|max:5000',
            'file' => 'nullable|file|mimes:jpeg,png,jpg,webp|max:10240',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'location_hint' => 'nullable|string|max:255',
        ]);

        if (empty($validated['message']) && !$request->hasFile('file')) {
            return response()->json(['error' => 'Pesan atau foto harus diisi.'], 422);
        }

        $file = $request->file('file');
        $publicUrl = $file ? $this->storeImage($file, 'monitoring') : null;

        $conversation = isset($validated['conversation_id'])
            ? PohaciConversation::findOrFail($validated['conversation_id'])
            : PohaciConversation::create([
                'user_id' => $request->user()?->id,
                'source' => $request->hasFile('file') ? 'image' : 'chat',
                'status' => 'active',
                'metadata' => [
                    'location_hint' => $validated['location_hint'] ?? null,
                ],
            ]);

        $messagePayload = [
            'conversation_id' => $conversation->id,
            'user_id' => $request->user()?->id,
            'sender_type' => 'farmer',
            'content' => $validated['message'] ?? '',
            'has_attachment' => $request->hasFile('file'),
            'metadata' => [
                'location_hint' => $validated['location_hint'] ?? null,
            ],
        ];

        $userMessage = PohaciMessage::create($messagePayload);
        $coordinates = $this->resolveCoordinates($request, $file);
        $mode = 'standard';
        $spatialPayload = null;
        $location = null;
        $answer = null;
        $spatialError = null;

        if ($coordinates) {
            try {
                $spatialPayload = $this->fetchSpatialData($coordinates['latitude'], $coordinates['longitude']);
                $location = PohaciLocation::create([
                    'conversation_id' => $conversation->id,
                    'message_id' => $userMessage->id,
                    'latitude' => $coordinates['latitude'],
                    'longitude' => $coordinates['longitude'],
                    'source' => $coordinates['source'],
                    'confidence' => $coordinates['confidence'],
                    'label' => $validated['location_hint'] ?? null,
                    'raw_payload' => $coordinates['raw_payload'],
                ]);

                $satellite = PohaciSatelliteObservation::create([
                    'conversation_id' => $conversation->id,
                    'location_id' => $location->id,
                    'satellite_source' => $spatialPayload['satellite'] ?? 'COPERNICUS/S2_SR_HARMONIZED',
                    'ndvi_value' => data_get($spatialPayload, 'data.NDVI'),
                    'captured_from' => data_get($spatialPayload, 'window.start'),
                    'captured_to' => data_get($spatialPayload, 'window.end'),
                    'raw_payload' => $spatialPayload,
                ]);

                $answer = $this->runSpatialAnalysis($validated['message'] ?? '', $file, $spatialPayload);
                $mode = 'spatial';
            } catch (\Throwable $e) {
                $spatialError = $e->getMessage();
            }
        }

        if ($answer === null) {
            $answer = $this->runStandardAnalysis($validated['message'] ?? '', $file, $validated['location_hint'] ?? null);
            if ($coordinates && $spatialError) {
                $mode = 'spatial_fallback';
            }
        }

        $aiMessage = PohaciMessage::create([
            'conversation_id' => $conversation->id,
            'user_id' => $request->user()?->id,
            'sender_type' => 'ai',
            'content' => $answer,
            'has_attachment' => false,
            'metadata' => [
                'mode' => $mode,
                'spatial_error' => $spatialError,
            ],
        ]);

        $recommendation = PohaciRecommendation::create([
            'conversation_id' => $conversation->id,
            'message_id' => $aiMessage->id,
            'mode' => $mode,
            'risk_level' => $this->inferRiskLevel($answer),
            'result_text' => $answer,
            'fertilizer_suggestion' => null,
            'raw_response' => [
                'mode' => $mode,
                'spatial_error' => $spatialError,
                'spatial' => $spatialPayload,
            ],
        ]);

        PohaciMonitoring::create([
            'user_id' => $request->user()?->id,
            'reporter_name' => $request->user()?->name ?? 'Pengguna Umum',
            'reporter_email' => $request->user()?->email ?? null,
            'image_path' => $publicUrl,
            'latitude' => $location?->latitude,
            'longitude' => $location?->longitude,
            'coordinate_source' => $location?->source ?? 'none',
            'location_label' => $validated['location_hint'] ?? null,
            'disease_name' => data_get($spatialPayload, 'data.NDVI') !== null ? 'Analisa Spasial' : 'Analisa Umum',
            'confidence' => null,
            'solution' => $answer,
            'ndvi_value' => data_get($spatialPayload, 'data.NDVI'),
            'satellite_source' => data_get($spatialPayload, 'satellite'),
            'analysis_mode' => $mode,
            'recommendation' => $answer,
            'followup_status' => 'pending',
            'raw_payload' => [
                'conversation_id' => $conversation->id,
                'message_id' => $userMessage->id,
                'location' => $location,
                'spatial' => $spatialPayload,
                'spatial_error' => $spatialError,
            ],
        ]);

        return response()->json([
            'status' => 'success',
            'mode' => $mode,
            'conversation_id' => $conversation->id,
            'message_id' => $userMessage->id,
            'location_id' => $location?->id,
            'recommendation_id' => $recommendation->id,
            'spatial_error' => $spatialError,
            'answer' => $answer,
            'spatial' => $spatialPayload,
        ]);
    }

    protected function runSpatialAnalysis(string $message, $file, array $spatialPayload): string
    {
        $context = $this->buildSpatialContext($spatialPayload);

        if ($file) {
            $base64 = base64_encode(file_get_contents($file->getRealPath()));
            $mimeType = $file->getMimeType();
            $prompt = trim(($message ?: 'Analisa foto ini.') . "\n\n" . $context);

            return $this->groq->chatWithImage($prompt, $base64, $mimeType);
        }

        $prompt = trim(($message ?: 'Analisa kondisi lahan ini.') . "\n\n" . $context);

        return $this->groq->chat($prompt);
    }

    protected function runStandardAnalysis(string $message, $file, ?string $locationHint = null): string
    {
        if ($file) {
            $base64 = base64_encode(file_get_contents($file->getRealPath()));
            $mimeType = $file->getMimeType();
            $prompt = $message ?: 'Analisa foto ini dan berikan saran agronomi umum.';

            if ($locationHint) {
                $prompt .= "\nLokasi yang disebutkan: {$locationHint}.";
            }

            return $this->groq->chatWithImage($prompt, $base64, $mimeType);
        }

        $prompt = $message ?: 'Bantu analisa keluhan petani dan berikan saran agronomi umum.';

        if ($locationHint) {
            $prompt .= "\nLokasi yang disebutkan: {$locationHint}.";
        }

        return $this->groq->chat($prompt);
    }

    protected function resolveCoordinates(Request $request, $file): ?array
    {
        if ($request->filled('latitude') && $request->filled('longitude')) {
            return [
                'latitude' => (float) $request->input('latitude'),
                'longitude' => (float) $request->input('longitude'),
                'source' => 'request',
                'confidence' => 100,
                'raw_payload' => [
                    'source' => 'request',
                ],
            ];
        }

        if (!$file || !function_exists('exif_read_data')) {
            return null;
        }

        $exif = @exif_read_data($file->getRealPath(), 'GPS', true);
        if (!is_array($exif)) {
            return null;
        }

        $latitude = $this->extractGpsCoordinate($exif, 'GPSLatitude', 'GPSLatitudeRef');
        $longitude = $this->extractGpsCoordinate($exif, 'GPSLongitude', 'GPSLongitudeRef');

        if ($latitude === null || $longitude === null) {
            return null;
        }

        return [
            'latitude' => $latitude,
            'longitude' => $longitude,
            'source' => 'exif',
            'confidence' => 85,
            'raw_payload' => $exif,
        ];
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

    protected function storeImage($file, string $folder): ?string
    {
        $publicUrl = $this->supabase->upload($file, $folder);

        if ($publicUrl) {
            return $publicUrl;
        }

        $filename = 'temp_' . time() . '.' . $file->getClientOriginalExtension();
        $path = $file->storeAs('uploads', $filename, 'public');

        return 'storage/' . $path;
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
        $process->setTimeout(120);
        $process->run();

        if (!$process->isSuccessful()) {
            throw new \RuntimeException(trim($process->getErrorOutput()) ?: 'Gagal terhubung ke script satelit.');
        }

        $decoded = json_decode($process->getOutput(), true);
        if (!is_array($decoded)) {
            throw new \RuntimeException('Response satelit tidak valid.');
        }

        return $decoded;
    }

    protected function buildSpatialContext(array $spatialPayload): string
    {
        $ndvi = data_get($spatialPayload, 'data.NDVI');
        $lat = data_get($spatialPayload, 'coordinates.latitude');
        $lng = data_get($spatialPayload, 'coordinates.longitude');

        return "KONTEKS SPASIAL:\n"
            . "- Mode: Jalur spasial high-precision\n"
            . "- Koordinat: {$lat}, {$lng}\n"
            . "- NDVI: " . ($ndvi ?? 'tidak tersedia') . "\n"
            . "- Sumber satelit: " . data_get($spatialPayload, 'satellite', 'COPERNICUS/S2_SR_HARMONIZED') . "\n"
            . "Gunakan data ini bersama keluhan user untuk memberi rekomendasi pupuk yang lebih presisi.";
    }

    protected function inferRiskLevel(string $answer): ?string
    {
        $text = strtolower($answer);

        if (str_contains($text, 'tinggi') || str_contains($text, 'parah')) {
            return 'high';
        }

        if (str_contains($text, 'sedang')) {
            return 'medium';
        }

        if (str_contains($text, 'rendah') || str_contains($text, 'aman')) {
            return 'low';
        }

        return null;
    }
}
