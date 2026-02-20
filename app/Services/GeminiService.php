<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class GeminiService implements AIServiceInterface
{
    protected $apiKey;
    protected $baseUrl;
    protected $defaultModel;

    protected $systemPrompt = 'Kamu adalah "Pohaci AI", asisten pakar pertanian padi Indonesia.
Kamu ahli dalam: penyakit tanaman padi, hama, pupuk, teknik budidaya, pengendalian hama terpadu, dan solusi pertanian modern.
Jawab dalam Bahasa Indonesia yang mudah dipahami petani. Gunakan poin-poin jika perlu.
Jika ada gambar, analisa kondisi tanaman secara detail.
Jika pertanyaan di luar topik pertanian, arahkan kembali ke topik pertanian padi.';

    protected $diagnosisPrompt = 'Kamu adalah ahli patologi tanaman padi. Analisa gambar daun padi ini dengan teliti.

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
        $this->apiKey = config('services.gemini.api_key');
        $this->baseUrl = 'https://generativelanguage.googleapis.com/v1beta/models';
        $this->defaultModel = config('services.gemini.default_model', 'gemini-2.5-flash');
    }

    public function chat(string $message, ?string $model = null, ?string $diseaseContext = null): string
    {
        $model = $model ?? $this->defaultModel;
        $systemContent = $this->systemPrompt;
        if ($diseaseContext && $diseaseContext !== 'Konsultasi Umum') {
            $systemContent .= "\n\nKonteks saat ini: User sedang mendiskusikan penyakit padi '" . $diseaseContext . "'. Berikan informasi yang relevan.";
        }
        $payload = [
            'system_instruction' => ['parts' => [['text' => $systemContent]]],
            'contents' => [['role' => 'user', 'parts' => [['text' => $message]]]],
            'generationConfig' => ['temperature' => 0.6, 'maxOutputTokens' => 4096]
        ];
        return $this->sendRequest($payload, $model);
    }

    public function chatWithImage(string $message, array $images, ?string $model = null): string
    {
        $model = $model ?? config('services.gemini.vision_model', 'gemini-2.5-pro');
        $parts = [];
        $messageText = $message ?: 'Analisa gambar-gambar ini dan berikan penjelasan detail tentang kondisi tanaman padi.';
        $parts[] = ['text' => $messageText];
        foreach ($images as $img) {
            $parts[] = ['inline_data' => ['mime_type' => $img['mime'], 'data' => $img['base64']]];
        }
        $payload = [
            'system_instruction' => ['parts' => [['text' => $this->systemPrompt]]],
            'contents' => [['role' => 'user', 'parts' => $parts]],
            'generationConfig' => ['temperature' => 0.6, 'maxOutputTokens' => 4096]
        ];
        return $this->sendRequest($payload, $model);
    }

    public function chatWithUrl(string $message, string $url, ?string $model = null): string
    {
        $model = $model ?? $this->defaultModel;
        $cacheKey = 'scraped_url_' . md5($url);
        $textContent = Cache::remember($cacheKey, 3600, function () use ($url) {
            return app(GroqService::class)->scrapeUrl($url);
        });
        if (strlen($textContent) < 200) {
            return 'Gagal Membaca Artikel. Mohon pastikan URL valid dan dapat diakses publik.';
        }
        $userMessage = 'Berikut adalah konten teks dari artikel URL: ' . $url . "\n\n[MULAI KONTEN]\n" . $textContent . "\n[AKHIR KONTEN]\n\nInstruksi User: " . $message;
        $sysInstr = $this->systemPrompt . "\n\nINSTRUKSI KHUSUS (RAG MODE):\nUser memberikan teks artikel dari URL. Tugas Anda adalah mengekstrak informasi dengan sangat teliti.\n\nATURAN RAG:\n1. Jawab HANYA berdasarkan informasi yang ada di konten.\n2. Cek setiap kalimat. Jangan lewatkan detail kecil.\n3. Jika informasi tidak ada di teks, katakan jujur.";
        $payload = [
            'system_instruction' => ['parts' => [['text' => $sysInstr]]],
            'contents' => [['role' => 'user', 'parts' => [['text' => $userMessage]]]],
            'generationConfig' => ['temperature' => 0.6, 'maxOutputTokens' => 4096]
        ];
        return $this->sendRequest($payload, $model);
    }

    public function diagnosisImage(string $base64Image, string $mimeType = 'image/jpeg'): array
    {
        $model = config('services.gemini.vision_model', 'gemini-2.5-pro');
        $payload = [
            'system_instruction' => ['parts' => [['text' => $this->diagnosisPrompt]]],
            'contents' => [['role' => 'user', 'parts' => [
                ['text' => 'Diagnosa penyakit pada daun padi di gambar ini. Jawab dalam format JSON.'],
                ['inline_data' => ['mime_type' => $mimeType, 'data' => $base64Image]]
            ]]],
            'generationConfig' => ['temperature' => 0.6, 'maxOutputTokens' => 1024]
        ];
        $response = $this->sendRequest($payload, $model);
        $cleaned = str_replace(array('```json', '```'), '', $response);
        $cleaned = trim($cleaned);
        if (preg_match('/\{[^{}]*"disease_name"[^{}]*\}/s', $cleaned, $matches)) {
            $parsed = json_decode($matches[0], true);
            if ($parsed) {
                return [
                    'disease_name' => $parsed['disease_name'] ?? 'Tidak Diketahui',
                    'confidence' => (float) ($parsed['confidence'] ?? 0),
                    'solution' => $parsed['solution'] ?? 'Tidak ada solusi.',
                ];
            }
        }
        return ['disease_name' => 'Tidak Diketahui', 'confidence' => 0, 'solution' => $response];
    }

    protected function sendRequest(array $payload, string $model): string
    {
        $maxRetries = 3;
        $attempt = 0;
        $backoff = 2;
        do {
            $attempt++;
            try {
                $endpoint = $this->baseUrl . '/' . $model . ':generateContent?key=' . $this->apiKey;
                $http = Http::withHeaders(['Content-Type' => 'application/json'])->timeout(60);
                if (config('app.env') === 'local') {
                    $http->withoutVerifying();
                }
                $response = $http->post($endpoint, $payload);
                if ($response->successful()) {
                    $data = $response->json();
                    if (isset($data['candidates'][0]['content']['parts'][0]['text'])) {
                        return $data['candidates'][0]['content']['parts'][0]['text'];
                    }
                    if (isset($data['promptFeedback']['blockReason'])) {
                        return 'Prompt terblokir oleh filter keamanan (' . $data['promptFeedback']['blockReason'] . ').';
                    }
                    return 'Respons kosong dari AI Gemini.';
                }
                if ($response->status() === 429) {
                    Log::warning('Gemini Rate Limit 429. Retrying attempt ' . $attempt . '/' . $maxRetries);
                    if ($attempt < $maxRetries) {
                        sleep($backoff);
                        $backoff *= 2;
                        continue;
                    }
                    return 'Terlalu banyak permintaan (Rate Limit). Silakan tunggu.';
                }
                Log::error('Gemini API Error: ' . $response->body());
                return 'Gagal mendapatkan respons dari Gemini. Status: ' . $response->status();
            } catch (\Exception $e) {
                Log::error('Gemini API Exception: ' . $e->getMessage());
                return 'Koneksi ke Gemini AI gagal: ' . $e->getMessage();
            }
        } while ($attempt < $maxRetries);
        return 'Gagal menghubungi Gemini AI setelah beberapa percobaan.';
    }
}