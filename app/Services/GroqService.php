<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

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
     * Chat dengan gambar (vision model) - Support Multiple Images
     * @param string $message Pesan user
     * @param array $images Array of ['base64' => string, 'mime' => string]
     * @param string|null $model Model ID
     */
    public function chatWithImage(string $message, array $images, ?string $model = null): string
    {
        // Use configured vision model (default: llama-4-scout)
        $model = $model ?? config('services.groq.vision_model', 'meta-llama/llama-4-scout-17b-16e-instruct');

        $contentPayload = [];

        // 1. Add all images first
        foreach ($images as $img) {
            $contentPayload[] = [
                'type' => 'image_url',
                'image_url' => [
                    'url' => "data:{$img['mime']};base64,{$img['base64']}",
                ],
            ];
        }

        // 2. Add text caption
        $contentPayload[] = [
            'type' => 'text',
            'text' => $message ?: 'Analisa gambar-gambar ini dan berikan penjelasan detail tentang kondisi tanaman padi.',
        ];

        $messages = [
            ['role' => 'system', 'content' => $this->systemPrompt],
            [
                'role' => 'user',
                'content' => $contentPayload,
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

        // Cache hasil scraping selama 60 menit agar tidak perlu request ulang
        $cacheKey = 'scraped_url_' . md5($url);

        $textContent = Cache::remember($cacheKey, 3600, function () use ($url) {
            return $this->scrapeUrl($url);
        });

        // SAFETY GATE: Pastikan konten tidak kosong sebelum dikirim ke AI
        if (strlen($textContent) < 200) {
            return "⚠️ **Gagal Membaca Artikel**: Sistem tidak dapat mengambil isi teks dari URL tersebut (Konten terlalu pendek/kosong). Mohon pastikan URL valid dan dapat diakses publik.\n\nTips: Coba copy-paste isi artikelnya langsung ke chat.";
        }

        $userMessage = "Berikut adalah konten teks dari artikel URL: {$url}\n\n[MULAI KONTEN]\n{$textContent}\n[AKHIR KONTEN]\n\nInstruksi User: {$message}";

        $messages = [
            ['role' => 'system', 'content' => $this->systemPrompt . "\n\nINSTRUKSI KHUSUS (RAG MODE):\nUser memberikan teks artikel dari URL. Tugas Anda adalah mengekstrak informasi dengan sangat teliti, seperti 'Detektif Data'.\n\nATURAN RAG:\n1. Jawab HANYA berdasarkan informasi yang ada di [MULAI KONTEN] sampai [AKHIR KONTEN].\n2. Cek setiap kalimat. Jangan lewatkan detail kecil seperti nama ilmiah (biasanya italic/kurung), persentase angka, atau dosis obat.\n3. Jika tertulis 'Rhizoctonia solani', 'Xanthomonas', atau angka '40%', '25%', WAJIB DISEBUTKAN.\n4. Jika informasi benar-benar tidak ada di teks, katakan jujur: 'Maaf, informasi spesifik tersebut tidak ditemukan dalam artikel ini, namun secara umum...'."],
            ['role' => 'user', 'content' => $userMessage],
        ];

        return $this->sendRequest($messages, $model);
    }

    /**
     * Scrape URL content (Jina Reader + DomCrawler Fallback)
     */
    public function scrapeUrl(string $url): string
    {
        $text = '';

        // 1. COBA JINA READER (Prioritas Utama - Markdown Bersih)
        try {
            // Timeout dipercepat (10s)
            // Tambahkan header X-With-Generated-Alt untuk deskripsi gambar
            $jinaUrl = "https://r.jina.ai/" . $url;
            $response = Http::withHeaders([
                'X-Target-Selector' => 'article, main, .post-content, .entry-content, #content', // Fokus ke konten utama
                'X-Return-Format' => 'markdown'
            ])->timeout(10)->get($jinaUrl);

            if ($response->successful()) {
                $text = $response->body();
                // Validasi panjang konten Jina
                if (strlen($text) > 100) {
                    return mb_substr($text, 0, 15000);
                }
            }
        } catch (\Exception $e) {
            Log::warning("Jina Reader failed for URL {$url}: " . $e->getMessage());
            // Lanjut ke fallback...
        }

        // 2. FALLBACK: DOM CRAWLER (Scraping Lokal)
        try {
            $response = Http::withHeaders([
                'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36',
            ])->timeout(15)->get($url);

            if ($response->failed()) {
                return "⚠️ Gagal mengakses URL (Status: {$response->status()}). Pastikan URL publik.";
            }

            $htmlContent = $response->body();

            if (class_exists(\Symfony\Component\DomCrawler\Crawler::class)) {
                $crawler = new \Symfony\Component\DomCrawler\Crawler($htmlContent);

                // Hapus elemen sampah
                $crawler->filter('script, style, nav, footer, header, aside, iframe, noscript, svg, .ad, .ads, .popup, .login, .signup, .menu, .sidebar, .widget, .comments')->each(function ($node) {
                    foreach ($node as $n) {
                        $n->parentNode->removeChild($n);
                    }
                });

                // Coba ambil dari selektor konten utama dulu
                $mainContent = $crawler->filter('article, main, .post-content, .entry-content, #content');
                if ($mainContent->count() > 0) {
                    $text = $mainContent->text();
                } else {
                    $text = $crawler->filter('body')->text();
                }
            } else {
                $text = strip_tags($htmlContent);
            }

            // Bersihkan whitespace
            $text = preg_replace('/\s+/', ' ', trim($text));

            // Batasi panjang (Naikkan ke 15,000 karakter agar tidak terpotong)
            // Llama 3 context window besar, manfaatkan.
            return mb_substr($text, 0, 15000);

        } catch (\Exception $e) {
            return "⚠️ Error saat scraping URL: " . $e->getMessage();
        }
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
        $maxRetries = 3;
        $attempt = 0;
        $backoff = 2; // Detik awal

        do {
            $attempt++;
            try {
                $http = Http::withHeaders([
                    'Authorization' => 'Bearer ' . $this->apiKey,
                    'Content-Type' => 'application/json',
                ])->timeout(60);

                if (config('app.env') === 'local') {
                    $http->withoutVerifying();
                }

                $response = $http->post("{$this->baseUrl}/chat/completions", [
                    'model' => $model,
                    'messages' => $messages,
                    'temperature' => 0.6,
                    'max_tokens' => 4096,
                ]);

                if ($response->successful()) {
                    $data = $response->json();
                    return $data['choices'][0]['message']['content'] ?? '⚠️ Respons kosong dari AI.';
                }

                // Handle 429 Specifically
                if ($response->status() === 429) {
                    Log::warning("Groq Rate Limit (429). Retrying attempt {$attempt}/{$maxRetries}...");
                    if ($attempt < $maxRetries) {
                        sleep($backoff);
                        $backoff *= 2; // Exponential backoff (2s, 4s, 8s)
                        continue;
                    }
                    return '⚠️ Terlalu banyak permintaan (Rate Limit). Silakan tunggu beberapa saat lagi.';
                }

                Log::error('Groq API Error: ' . $response->body());
                return '⚠️ Gagal mendapatkan respons dari AI. Status: ' . $response->status();

            } catch (\Exception $e) {
                Log::error('Groq API Exception: ' . $e->getMessage());
                return '⚠️ Koneksi ke AI gagal: ' . $e->getMessage();
            }
        } while ($attempt < $maxRetries);

        return '⚠️ Gagal menghubungi AI setelah beberapa percobaan.';
    }
}
