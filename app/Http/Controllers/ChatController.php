<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\AIServiceInterface;
use App\Models\PohaciLog;
use Illuminate\Support\Facades\Cache;

class ChatController extends Controller
{
    protected AIServiceInterface $ai;

    public function __construct(AIServiceInterface $ai)
    {
        $this->ai = $ai;
    }

    /**
     * Handle semua jenis chat: text, file, dan URL
     */
    public function sendMessage(Request $request)
    {
        $request->validate([
            'message' => 'nullable|string|max:5000',
            'file' => 'nullable|file|mimes:jpeg,png,jpg,gif,webp|max:10240',
            'files.*' => 'nullable|file|mimes:jpeg,png,jpg,gif,webp|max:10240', // Support multiple files
            'url' => 'nullable|url|max:2000',
            'disease_context' => 'nullable|string|max:200',
        ]);

        $message = $request->input('message', '');
        $diseaseContext = $request->input('disease_context', 'Konsultasi Umum');

        try {
            // --- MODE 1: Chat dengan File (Gambar) ---
            // Support single 'file' or multiple 'files[]'
            $files = [];
            if ($request->hasFile('files')) {
                $files = $request->file('files');
            }
            elseif ($request->hasFile('file')) {
                $files = [$request->file('file')];
            }

            if (!empty($files)) {
                $imagesPayload = [];

                foreach ($files as $file) {
                    $imagesPayload[] = [
                        'base64' => base64_encode(file_get_contents($file->getRealPath())),
                        'mime' => $file->getMimeType(),
                    ];
                }

                // Jika tidak ada pesan, berikan default
                if (empty($message)) {
                    $message = 'Analisa gambar-gambar ini dan jelaskan kondisi tanaman padi yang terlihat.';
                }

                $answer = $this->ai->chatWithImage($message, $imagesPayload);

                $visionModel = $this->ai instanceof \App\Services\FallbackAIService
                    ? $this->ai->getActiveModelName('vision')
                    : $this->getActiveModelName('vision');
                $this->saveLog(
                    $message,
                    $answer,
                    $visionModel,
                    null,
                    null
                );

                return response()->json([
                    'answer' => $answer,
                    'model_used' => $visionModel,
                    'type' => 'vision',
                ]);
            }

            // --- MODE 2: Chat dengan URL ---
            if ($request->filled('url')) {
                $url = $request->input('url');

                if (empty($message)) {
                    $message = 'Rangkum dan analisa konten dari URL ini dalam konteks pertanian padi.';
                }

                $answer = $this->ai->chatWithUrl($message, $url);

                // Ambil context dari cache yang disimpan di GroqService
                $scrapedContext = Cache::get('scraped_url_' . md5($url));

                $defaultModel = $this->ai instanceof \App\Services\FallbackAIService
                    ? $this->ai->getActiveModelName('default')
                    : $this->getActiveModelName('default');
                $this->saveLog(
                    $message,
                    $answer,
                    $defaultModel,
                    $url,
                    $scrapedContext
                );

                return response()->json([
                    'answer' => $answer,
                    'model_used' => $defaultModel,
                    'type' => 'url',
                ]);
            }

            // --- MODE 3: Chat Text Biasa ---
            if (empty($message)) {
                return response()->json(['error' => 'Pesan tidak boleh kosong.'], 422);
            }

            $answer = $this->ai->chat($message, null, $diseaseContext);

            $defaultModel = $this->ai instanceof \App\Services\FallbackAIService
                ? $this->ai->getActiveModelName('default')
                : $this->getActiveModelName('default');
            $this->saveLog(
                $message,
                $answer,
                $defaultModel,
                null,
                null
            );

            return response()->json([
                'answer' => $answer,
                'model_used' => $defaultModel,
                'type' => 'text',
            ]);

        }
        catch (\Exception $e) {
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
        }
        catch (\Exception $e) {
            \Log::error("Gagal menyimpan Pohaci Log: " . $e->getMessage());
        }
    }

    /**
     * Helper untuk mendapatkan nama model AI yang aktif
     */
    private function getActiveModelName(string $type = 'default'): string
    {
        $provider = config('services.ai.provider', 'groq');

        if ($provider === 'gemini') {
            return $type === 'vision'
                ? config('services.gemini.vision_model', 'gemini-2.5-pro')
                : config('services.gemini.default_model', 'gemini-2.5-flash');
        }

        return $type === 'vision'
            ? config('services.groq.vision_model', 'meta-llama/llama-4-scout-17b-16e-instruct')
            : config('services.groq.default_model', 'llama-3.3-70b-versatile');
    }
}
