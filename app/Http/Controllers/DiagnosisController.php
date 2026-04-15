<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Models\Diagnosis;
use App\Models\FailedUpload;
use App\Models\PohaciMonitoring;
use App\Services\GroqService;

class DiagnosisController extends Controller
{
    protected GroqService $groq;
    protected \App\Services\SupabaseStorageService $supabase;

    public function __construct(GroqService $groq, \App\Services\SupabaseStorageService $supabase)
    {
        $this->groq = $groq;
        $this->supabase = $supabase;
    }

    public function index()
    {
        return view('home');
    }

    public function analyze(Request $request)
    {
        // 1. Validasi Input
        $request->validate([
            'file' => 'required|image|mimes:jpeg,png,jpg,gif|max:5120',
        ]);

        try {
            $image = $request->file('file');
            $mimeType = $image->getMimeType();
            $base64Image = base64_encode(file_get_contents($image->getRealPath()));

            // 2. Upload ke Supabase Storage (Cloud)
            $publicUrl = $this->supabase->upload($image, 'diagnosa');

            // Fallback jika upload gagal (misal config belum set), pakai local temporary link 
            // (Agar app tidak crash, meski gambar broken nanti)
            if (!$publicUrl) {
                // Simpan lokal sementara
                $filename = 'temp_' . time() . '.' . $image->getClientOriginalExtension();
                $path = $image->storeAs('uploads', $filename, 'public');
                $publicUrl = 'storage/' . $path;
            }

            // 3. Kirim ke Groq Vision untuk diagnosa
            $result = $this->groq->diagnosisImage($base64Image, $mimeType);

            // --- LOGIKA PENENTUAN (FILTERING) ---

            // KASUS A: Bukan Daun Padi
            if (isset($result['disease_name']) && $result['disease_name'] == 'Bukan Daun Padi') {
                FailedUpload::create([
                    'image_path' => $publicUrl,
                    'reason' => 'Terdeteksi Objek Non-Padi',
                ]);

                return response()->json($result);
            }

            // KASUS B: Penyakit Padi (Valid)
            $diagnosis = Diagnosis::create([
                'image_path' => $publicUrl,
                'disease_name' => $result['disease_name'],
                'confidence' => $result['confidence'],
                'solution' => $result['solution'],
            ]);

            $coordinates = $this->resolveCoordinates($request, $image);
            $user = auth()->user();

            PohaciMonitoring::create([
                'user_id' => $user?->id,
                'reporter_name' => $user?->name ?? 'Pengguna Umum',
                'reporter_email' => $user?->email ?? null,
                'image_path' => $publicUrl,
                'latitude' => $coordinates['latitude'] ?? null,
                'longitude' => $coordinates['longitude'] ?? null,
                'coordinate_source' => $coordinates['source'] ?? 'none',
                'location_label' => $request->input('location_hint'),
                'disease_name' => $diagnosis->disease_name,
                'confidence' => $diagnosis->confidence,
                'solution' => $diagnosis->solution,
                'analysis_mode' => 'standard',
                'recommendation' => $diagnosis->solution,
                'followup_status' => 'pending',
                'raw_payload' => [
                    'diagnosis' => $result,
                    'coordinates' => $coordinates,
                ],
            ]);

            return response()->json($result);

        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    protected function resolveCoordinates(Request $request, $file): ?array
    {
        if ($request->filled('latitude') && $request->filled('longitude')) {
            return [
                'latitude' => (float) $request->input('latitude'),
                'longitude' => (float) $request->input('longitude'),
                'source' => 'request',
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

    protected function gpsValueToFloat($value): float
    {
        if (is_string($value) && str_contains($value, '/')) {
            [$numerator, $denominator] = array_pad(explode('/', $value, 2), 2, 1);
            return (float) $numerator / max(1, (float) $denominator);
        }

        return (float) $value;
    }
}
