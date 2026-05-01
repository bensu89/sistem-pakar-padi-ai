<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Diagnosis;
use App\Models\FailedUpload;
use App\Models\PohaciMonitoring;
use App\Services\GroqService;
use App\Traits\HasSpatialHelpers;
use Illuminate\Support\Facades\Schema;

class DiagnosisController extends Controller
{
    use HasSpatialHelpers;

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
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
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
            $coordinates = $this->resolveCoordinates($request, $image);
            $spatialPayload = null;
            $analysisMode = 'standard';
            $ndviValue = null;
            $satelliteSource = null;

            if ($coordinates) {
                try {
                    $spatialPayload = $this->fetchSpatialData($coordinates['latitude'], $coordinates['longitude']);
                    $ndviValue = data_get($spatialPayload, 'data.NDVI');
                    $satelliteSource = data_get($spatialPayload, 'satellite');
                    $analysisMode = 'spatial';
                } catch (\Throwable $e) {
                    $analysisMode = 'spatial_fallback';
                }
            }

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

            $user = auth()->user();

            $monitoringData = [
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
                'ndvi_value' => $ndviValue,
                'satellite_source' => $satelliteSource,
                'analysis_mode' => $analysisMode,
                'recommendation' => $diagnosis->solution,
                'followup_status' => 'pending',
                'raw_payload' => [
                    'diagnosis' => $result,
                    'coordinates' => $coordinates,
                    'spatial' => $spatialPayload,
                ],
            ];

            if (Schema::hasColumn('pohaci_monitorings', 'model_used')) {
                $monitoringData['model_used'] = $result['model_used'] ?? null;
            }

            PohaciMonitoring::create($monitoringData);

            return response()->json(array_merge($result, [
                'model_used' => $result['model_used'] ?? null,
                'ndvi_value' => $ndviValue,
                'satellite_source' => $satelliteSource,
                'analysis_mode' => $analysisMode,
                'coordinates' => $coordinates,
            ]));

        } catch (\RuntimeException $e) {
            $status = in_array($e->getCode(), [502, 503], true) ? $e->getCode() : 500;
            return response()->json(['error' => $e->getMessage()], $status);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Terjadi kesalahan saat memproses diagnosa.'], 500);
        }
    }
}
