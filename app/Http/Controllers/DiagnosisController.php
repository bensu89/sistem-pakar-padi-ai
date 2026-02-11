<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http; 
use Illuminate\Support\Facades\Storage;
use App\Models\Diagnosis;      // Model Data Valid
use App\Models\FailedUpload;   // Model Data Sampah/Ditolak

class DiagnosisController extends Controller
{
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
            // 2. Simpan Gambar Sementara di Laravel
            if ($request->hasFile('file')) {
                $image = $request->file('file');
                $filename = 'scan_' . time() . '.' . $image->getClientOriginalExtension();
                // Simpan ke storage/app/public/uploads
                $path = $image->storeAs('uploads', $filename, 'public'); 
            }

            // 3. Kirim Gambar ke Python API (Flask)
            $imagePath = storage_path('app/public/' . $path);
            
            // Post ke Python
            $response = Http::attach(
                'file', file_get_contents($imagePath), $filename
            )->post('http://127.0.0.1:5000/predict');

            // Cek jika Python Mati/Error
            if ($response->failed()) {
                return response()->json(['error' => 'Gagal koneksi ke AI Server'], 500);
            }

            $result = $response->json();

            // --- LOGIKA PENENTUAN (FILTERING) ---

            // KASUS A: Python bilang ini "Bukan Daun Padi"
            if (isset($result['disease_name']) && $result['disease_name'] == 'Bukan Daun Padi') {
                
                // Masukkan ke Tabel Sampah (FailedUpload)
                FailedUpload::create([
                    'image_path' => 'storage/' . $path,
                    'reason'     => 'Terdeteksi Objek Non-Padi'
                ]);

                // Kembalikan JSON ke Home (agar muncul peringatan), tapi STOP disini.
                return response()->json($result);
            }

            // KASUS B: Python bilang ini Penyakit Padi (Valid)
            // Masukkan ke Dashboard Utama (Diagnosis)
            Diagnosis::create([
                'image_path'   => 'storage/' . $path,
                'disease_name' => $result['disease_name'],
                'confidence'   => $result['confidence'],
                'solution'     => $result['solution']
            ]);

            // Kembalikan JSON Hasil Diagnosa
            return response()->json($result);

        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}