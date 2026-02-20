<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\AIServiceInterface;
use App\Models\PohaciLog;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Facades\Image; // Intervention Image V2
use Illuminate\Support\Facades\Log;

class PohaciController extends Controller
{
    protected AIServiceInterface $ai;

    public function __construct(AIServiceInterface $ai)
    {
        $this->ai = $ai;
    }

    /**
     * Handle Image Scan & Chat
     * Fitur: Kompresi Gambar (Intervention V2) -> Upload Supabase -> Analisa AI (Groq)
     */
    public function chat(Request $request)
    {
        // 1. VALIDASI INPUT
        $request->validate([
            'image' => 'required|file|mimes:jpg,jpeg,png,webp|max:20480', // Max 20MB
            'message' => 'nullable|string|max:1000',
        ]);

        try {
            $file = $request->file('image');
            $userMessage = $request->input('message', 'Analisa gambar ini dan jelaskan kondisi tanaman padi.');
            $originalName = $file->getClientOriginalName();

            // 2. KOMPRESI GAMBAR (Intervention Image V2)
            // Resize lebar max 1024px, aspect ratio maintained, prevent upsizing
            $image = Image::make($file)->resize(1024, null, function ($constraint) {
                $constraint->aspectRatio();
                $constraint->upsize();
            })->encode('jpg', 75); // Encode ke JPG kualitas 75%

            // 3. UPLOAD KE SUPABASE STORAGE
            // Generate nama file unik
            $filename = 'scan_' . time() . '_' . uniqid() . '.jpg';
            $path = 'padi-uploads/' . $filename;

            // Upload stream terkompresi ke disk 'supabase'
            Storage::disk('supabase')->put($path, (string)$image, 'public');

            // Ambil URL Publik
            $publicUrl = Storage::disk('supabase')->url($path);

            // 4. AI PROCESSING (GROQ)
            // Konversi image terkompresi ke base64 untuk dikirim ke LLM
            $base64Image = base64_encode((string)$image);

            // Panggil Service Groq (Llama-3 Vision)
            // Kita bungkus dalam array untuk support kompatibilitas method chatWithImage yang baru
            $imagesPayload = [
                [
                    'base64' => $base64Image,
                    'mime' => 'image/jpeg'
                ]
            ];

            $aiResponse = $this->ai->chatWithImage($userMessage, $imagesPayload);

            // 5. LOGGING KE DATABASE
            PohaciLog::create([
                'user_id' => auth()->id() ?? null,
                'user_question' => $userMessage,
                'target_url' => $publicUrl, // URL Supabase
                'raw_context' => 'Image Scan',
                'ai_answer' => $aiResponse,
                'status' => 'success',
                'meta_data' => [
                    'original_size' => $file->getSize(),
                    'compressed_size' => strlen((string)$image),
                    'model' => 'llama-3-vision',
                    'timestamp' => now()->toDateTimeString()
                ]
            ]);

            return response()->json([
                'success' => true,
                'data' => [
                    'image_url' => $publicUrl,
                    'response' => $aiResponse,
                    'scan_meta' => [
                        'compressed' => true,
                        'size_kb' => round(strlen((string)$image) / 1024, 2)
                    ]
                ]
            ]);

        }
        catch (\Exception $e) {
            Log::error("Pohaci Error: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => 'Gagal memproses permintaan: ' . $e->getMessage()
            ], 500);
        }
    }
}
