<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Models\Diagnosis;
use App\Models\FailedUpload;
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
            Diagnosis::create([
                'image_path' => $publicUrl,
                'disease_name' => $result['disease_name'],
                'confidence' => $result['confidence'],
                'solution' => $result['solution'],
            ]);

            return response()->json($result);

        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}