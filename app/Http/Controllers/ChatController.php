<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\GroqService;

class ChatController extends Controller
{
    protected GroqService $groq;

    public function __construct(GroqService $groq)
    {
        $this->groq = $groq;
    }

    /**
     * Handle semua jenis chat: text, file, dan URL
     */
    public function sendMessage(Request $request)
    {
        $request->validate([
            'message' => 'nullable|string|max:5000',
            'file' => 'nullable|file|mimes:jpeg,png,jpg,gif,webp|max:10240',
            'url' => 'nullable|url|max:2000',
            'disease_context' => 'nullable|string|max:200',
        ]);

        $message = $request->input('message', '');
        $diseaseContext = $request->input('disease_context', 'Konsultasi Umum');

        try {
            // --- MODE 1: Chat dengan File (Gambar) ---
            if ($request->hasFile('file')) {
                $file = $request->file('file');
                $base64 = base64_encode(file_get_contents($file->getRealPath()));
                $mimeType = $file->getMimeType();

                // Jika tidak ada pesan, berikan default
                if (empty($message)) {
                    $message = 'Analisa gambar ini dan jelaskan kondisi tanaman padi yang terlihat.';
                }

                $answer = $this->groq->chatWithImage($message, $base64, $mimeType);

                return response()->json([
                    'answer' => $answer,
                    'model_used' => 'meta-llama/llama-4-scout-17b-16e-instruct',
                    'type' => 'vision',
                ]);
            }

            // --- MODE 2: Chat dengan URL ---
            if ($request->filled('url')) {
                $url = $request->input('url');

                if (empty($message)) {
                    $message = 'Rangkum dan analisa konten dari URL ini dalam konteks pertanian padi.';
                }

                $answer = $this->groq->chatWithUrl($message, $url);

                return response()->json([
                    'answer' => $answer,
                    'model_used' => config('services.groq.default_model'),
                    'type' => 'url',
                ]);
            }

            // --- MODE 3: Chat Text Biasa ---
            if (empty($message)) {
                return response()->json(['error' => 'Pesan tidak boleh kosong.'], 422);
            }

            $answer = $this->groq->chat($message, null, $diseaseContext);

            return response()->json([
                'answer' => $answer,
                'model_used' => config('services.groq.default_model'),
                'type' => 'text',
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Gagal memproses pesan: ' . $e->getMessage(),
            ], 500);
        }
    }
}
