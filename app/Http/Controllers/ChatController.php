<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\GroqService;
use App\Models\PohaciLog;
use Illuminate\Support\Facades\Cache;

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

                $this->saveLog(
                    $message,
                    $answer,
                    'meta-llama/llama-4-scout-17b-16e-instruct',
                    null,
                    null
                );

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

                // Ambil context dari cache yang disimpan di GroqService
                $scrapedContext = Cache::get('scraped_url_' . md5($url));

                $this->saveLog(
                    $message,
                    $answer,
                    config('services.groq.default_model'),
                    $url,
                    $scrapedContext
                );

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

            $this->saveLog(
                $message,
                $answer,
                config('services.groq.default_model'),
                null,
                null
            );

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
    /**
     * Helper untuk menyimpan log chat ke database
     */
    private function saveLog(string $question, string $answer, string $model, ?string $url = null, ?string $context = null)
    {
        try {
            PohaciLog::create([
                'user_id' => auth()->id() ?? null, // Ambil ID kalau login, null kalau tamu
                'user_question' => $question,
                'target_url' => $url,
                'raw_context' => $context,
                'ai_answer' => $answer,
                'status' => 'success',
                'meta_data' => [
                    'model' => $model,
                    'timestamp' => now()->toDateTimeString(),
                    'ip_address' => request()->ip()
                ]
            ]);
        } catch (\Exception $e) {
            \Log::error("Gagal menyimpan Pohaci Log: " . $e->getMessage());
        }
    }
}
