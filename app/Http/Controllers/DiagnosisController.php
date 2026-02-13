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

    public function __construct(GroqService $groq)
    {
        $this->groq = $groq;
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
            // 2. Simpan Gambar di Laravel
            if ($request->hasFile('file')) {
                $image = $request->file('file');
                $filename = 'padi_' . time() . '.' . $image->getClientOriginalExtension();
                $path = $image->storeAs('uploads', $filename, 'public');
            }

            // 3. Encode gambar ke base64 dan kirim ke Groq Vision
            $imagePath = storage_path('app/public/' . $path);
            $base64Image = base64_encode(file_get_contents($imagePath));
            $mimeType = $image->getMimeType();

            // 4. Kirim ke Groq Vision untuk diagnosa
            $result = $this->groq->diagnosisImage($base64Image, $mimeType);

            // --- LOGIKA PENENTUAN (FILTERING) ---

            // KASUS A: Bukan Daun Padi
            if (isset($result['disease_name']) && $result['disease_name'] == 'Bukan Daun Padi') {
                FailedUpload::create([
                    'image_path' => 'storage/' . $path,
                    'reason' => 'Terdeteksi Objek Non-Padi',
                ]);

                return response()->json($result);
            }

            // KASUS B: Penyakit Padi (Valid)
            Diagnosis::create([
                'image_path' => 'storage/' . $path,
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