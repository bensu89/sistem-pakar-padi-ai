<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Models\Diagnosis;
use App\Models\FailedUpload;
use App\Services\AIServiceInterface;

class DiagnosisController extends Controller
{
    protected AIServiceInterface $ai;
    protected \App\Services\SupabaseStorageService $supabase;

    public function __construct(AIServiceInterface $ai, \App\Services\SupabaseStorageService $supabase)
    {
        $this->ai = $ai;
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

            // 3. Kirim ke AI Vision untuk diagnosa
            $result = $this->ai->diagnosisImage($base64Image, $mimeType);

            // --- LOGIKA PENENTUAN (FILTERING) ---

            // KASUS A: Bukan Daun Padi
            if (isset($result['disease_name']) && $result['disease_name'] == 'Bukan Daun Padi') {
                try {
                    FailedUpload::create([
                        'image_path' => $publicUrl,
                        'reason' => 'Terdeteksi Objek Non-Padi',
                    ]);
                }
                catch (\Exception $dbEx) {
                    \Illuminate\Support\Facades\Log::warning('DB save failed (FailedUpload): ' . $dbEx->getMessage());
                }

                return response()->json($result);
            }

            // KASUS B: Penyakit Padi (Valid)
            try {
                Diagnosis::create([
                    'image_path' => $publicUrl,
                    'disease_name' => $result['disease_name'],
                    'confidence' => $result['confidence'],
                    'solution' => $result['solution'],
                ]);
            }
            catch (\Exception $dbEx) {
                \Illuminate\Support\Facades\Log::warning('DB save failed (Diagnosis): ' . $dbEx->getMessage());
            }

            return response()->json($result);

        }
        catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}