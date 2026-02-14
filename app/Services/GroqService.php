<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GroqService
{
    protected string $apiKey;
    protected string $baseUrl;
    protected string $defaultModel;

    // System prompt khusus pakar padi
    protected string $systemPrompt = 'Kamu adalah "Pohaci AI", asisten pakar pertanian padi Indonesia. 
Kamu ahli dalam: penyakit tanaman padi, hama, pupuk, teknik budidaya, pengendalian hama terpadu, dan solusi pertanian modern.
Jawab dalam Bahasa Indonesia yang mudah dipahami petani. Gunakan poin-poin jika perlu.
Jika ada gambar, analisa kondisi tanaman secara detail.
Jika pertanyaan di luar topik pertanian, arahkan kembali ke topik pertanian padi.';

    // System prompt khusus untuk diagnosa daun
    protected string $diagnosisPrompt = 'Kamu adalah ahli patologi tanaman padi. Analisa gambar daun padi ini dengan teliti.

PENTING: Jawab dalam format JSON PERSIS seperti ini (tanpa markdown, tanpa backticks, hanya JSON murni):
{"disease_name":"nama penyakit","confidence":85,"solution":"penjelasan solusi lengkap"}

Aturan:
- Jika gambar BUKAN daun padi, jawab: {"disease_name":"Bukan Daun Padi","confidence":0,"solution":"Gambar yang dikirim bukan merupakan daun padi. Silakan upload foto daun padi untuk diagnosa."}
- confidence adalah angka 0-100 (tanpa simbol %)
- solution harus lengkap: gejala, penyebab, dan cara penanganan
- Nama penyakit dalam Bahasa Indonesia (misal: Hawar Daun Bakteri, Blas, Tungro, Bercak Coklat, Busuk Batang, dll)
- Jika daun sehat, jawab: {"disease_name":"Sehat","confidence":95,"solution":"Daun padi dalam kondisi sehat. Tetap lakukan pemeliharaan rutin."}';

    public function __construct()
    {
        $this->apiKey = config('services.groq.api_key');
        $this->baseUrl = config('services.groq.base_url');
        $this->defaultModel = config('services.groq.default_model');
    }

    /**
     * Chat text biasa (tanpa gambar)
     */
    public function chat(string $message, ?string $model = null, ?string $diseaseContext = null): string
    {
        $model = $model ?? $this->defaultModel;

        $systemContent = $this->systemPrompt;
        if ($diseaseContext && $diseaseContext !== 'Konsultasi Umum') {
            $systemContent .= "\n\nKonteks saat ini: User sedang mendiskusikan penyakit padi '{$diseaseContext}'. Berikan informasi yang relevan.";
        }

        $messages = [
            ['role' => 'system', 'content' => $systemContent],
            ['role' => 'user', 'content' => $message],
        ];

        return $this->sendRequest($messages, $model);
    }

    /**
     * Chat dengan gambar (vision model)
     */
    public function chatWithImage(string $message, string $base64Image, string $mimeType = 'image/jpeg', ?string $model = null): string
    {
        // Use configured vision model (default: llama-4-scout)
        $model = $model ?? config('services.groq.vision_model', 'meta-llama/llama-4-scout-17b-16e-instruct');

        $messages = [
            ['role' => 'system', 'content' => $this->systemPrompt],
            [
                'role' => 'user',
                'content' => [
                    [
                        'type' => 'image_url',
                        'image_url' => [
                            'url' => "data:{$mimeType};base64,{$base64Image}",
                        ],
                    ],
                    [
                        'type' => 'text',
                        'text' => $message ?: 'Analisa gambar ini dan berikan penjelasan detail tentang kondisi tanaman padi.',
                    ],
                ],
            ],
        ];

        return $this->sendRequest($messages, $model);
    }

    /**
     * Chat dengan konten URL
     */
    /**
     * Chat dengan konten URL (RAG Simple)
     */
    public function chatWithUrl(string $message, string $url, ?string $model = null): string
    {
        $model = $model ?? $this->defaultModel;

        $textContent = ''; // Initialize $textContent

        // Fetch URL content
        try {
            // Gunakan Jina Reader untuk mendapatkan konten bersih (Markdown)
            // Format: https://r.jina.ai/URL
            $jinaUrl = "https://r.jina.ai/" . $url;

            $response = Http::timeout(30)->get($jinaUrl);

            if ($response->failed()) {
                // Fallback ke metode lama jika Jina gagal
                return "⚠️ Gagal mengakses URL via Jina Reader (Status: {$response->status()}).";
            }

            $text = $response->body();

            // Batasi panjang konten
            $textContent = mb_substr($text, 0, 10000); // Jina output lebih bersih, bisa ambil lebih banyak

            if (strlen($textContent) < 50) {
                return "⚠️ Gagal mengambil konten artikel. Konten terlalu pendek.";
            }

        } catch (\Exception $e) {
            return "⚠️ Error saat scraping URL: " . $e->getMessage();
        }

        $userMessage = "Berikut adalah konten teks dari artikel URL: {$url}\n\n[MULAI KONTEN]\n{$textContent}\n[AKHIR KONTEN]\n\nInstruksi User: {$message}";

        $messages = [
            ['role' => 'system', 'content' => $this->systemPrompt . "\n\nPENTING: User memberikan konten artikel dari URL. Jawab pertanyaan user HANYA berdasarkan informasi yang ada di [MULAI KONTEN] sampai [AKHIR KONTEN]. Jika informasi tidak ada di artikel, katakan 'Maaf, informasi tersebut tidak ditemukan dalam artikel yang Anda berikan'." . (strpos($url, 'dgwfertilizer') !== false ? " (Artikel ini mungkin tentang pupuk/obat dgwfertilizer)." : "")],
            ['role' => 'user', 'content' => $userMessage],
        ];

        return $this->sendRequest($messages, $model);
    }

    /**
     * Diagnosa daun padi (return JSON)
     */
    public function diagnosisImage(string $base64Image, string $mimeType = 'image/jpeg'): array
    {
        $model = config('services.groq.vision_model', 'meta-llama/llama-4-scout-17b-16e-instruct');

        $messages = [
            ['role' => 'system', 'content' => $this->diagnosisPrompt],
            [
                'role' => 'user',
                'content' => [
                    [
                        'type' => 'image_url',
                        'image_url' => [
                            'url' => "data:{$mimeType};base64,{$base64Image}",
                        ],
                    ],
                    [
                        'type' => 'text',
                        'text' => 'Diagnosa penyakit pada daun padi di gambar ini. Jawab dalam format JSON.',
                    ],
                ],
            ],
        ];

        $response = $this->sendRequest($messages, $model);

        // Parse JSON dari response
        // Coba extract JSON dari response (kadang ada text tambahan)
        if (preg_match('/\{[^{}]*"disease_name"[^{}]*\}/s', $response, $matches)) {
            $parsed = json_decode($matches[0], true);
            if ($parsed) {
                return [
                    'disease_name' => $parsed['disease_name'] ?? 'Tidak Diketahui',
                    'confidence' => (float) ($parsed['confidence'] ?? 0),
                    'solution' => $parsed['solution'] ?? 'Tidak ada solusi.',
                ];
            }
        }

        // Fallback jika parsing gagal
        return [
            'disease_name' => 'Tidak Diketahui',
            'confidence' => 0,
            'solution' => $response,
        ];
    }

    /**
     * Kirim request ke Groq API
     */
    protected function sendRequest(array $messages, string $model): string
    {
        try {
            $http = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Content-Type' => 'application/json',
            ])->timeout(60);

            // Bypass SSL verification in local environment to fix common Windows/XAMPP issues
            if (config('app.env') === 'local') {
                $http->withoutVerifying();
            }

            $response = $http->post("{$this->baseUrl}/chat/completions", [
                'model' => $model,
                'messages' => $messages,
                'temperature' => 0.7,
                'max_tokens' => 2048,
            ]);

            if ($response->failed()) {
                Log::error('Groq API Error: ' . $response->body());
                return '⚠️ Gagal mendapatkan respons dari AI. Status: ' . $response->status();
            }

            $data = $response->json();
            return $data['choices'][0]['message']['content'] ?? '⚠️ Respons kosong dari AI.';

        } catch (\Exception $e) {
            Log::error('Groq API Exception: ' . $e->getMessage());
            return '⚠️ Koneksi ke AI gagal: ' . $e->getMessage();
        }
    }
}
