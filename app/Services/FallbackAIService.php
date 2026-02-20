<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;

class FallbackAIService implements AIServiceInterface
{
    protected $primary;
    protected $fallback;
    protected $activeProvider;
    protected $primaryName;
    protected $fallbackName;

    public function __construct(AIServiceInterface $primary, AIServiceInterface $fallback, string $primaryName, string $fallbackName)
    {
        $this->primary = $primary;
        $this->fallback = $fallback;
        $this->primaryName = $primaryName;
        $this->fallbackName = $fallbackName;
        $this->activeProvider = $primaryName;
    }

    /**
     * Mendapatkan nama provider yang terakhir berhasil merespons
     */
    public function getActiveProvider(): string
    {
        return $this->activeProvider;
    }

    /**
     * Mendapatkan nama model yang aktif berdasarkan provider yang merespons
     */
    public function getActiveModelName(string $type = 'default'): string
    {
        if ($this->activeProvider === 'gemini') {
            return $type === 'vision'
                ? config('services.gemini.vision_model', 'gemini-2.5-pro')
                : config('services.gemini.default_model', 'gemini-2.5-flash');
        }
        return $type === 'vision'
            ? config('services.groq.vision_model', 'meta-llama/llama-4-scout-17b-16e-instruct')
            : config('services.groq.default_model', 'llama-3.3-70b-versatile');
    }

    public function chat(string $message, ?string $model = null, ?string $diseaseContext = null): string
    {
        try {
            $result = $this->primary->chat($message, $model, $diseaseContext);
            if ($this->isErrorResponse($result)) {
                throw new \RuntimeException('Primary AI returned error: ' . $result);
            }
            $this->activeProvider = $this->primaryName;
            return $result;
        } catch (\Exception $e) {
            Log::warning('Primary AI (' . $this->primaryName . ') failed for chat: ' . $e->getMessage() . '. Falling back to ' . $this->fallbackName);
            try {
                $result = $this->fallback->chat($message, null, $diseaseContext);
                $this->activeProvider = $this->fallbackName;
                return $result;
            } catch (\Exception $e2) {
                Log::error('Fallback AI (' . $this->fallbackName . ') also failed: ' . $e2->getMessage());
                return 'Kedua AI provider gagal merespons. Silakan coba lagi nanti.';
            }
        }
    }

    public function chatWithImage(string $message, array $images, ?string $model = null): string
    {
        try {
            $result = $this->primary->chatWithImage($message, $images, $model);
            if ($this->isErrorResponse($result)) {
                throw new \RuntimeException('Primary AI returned error: ' . $result);
            }
            $this->activeProvider = $this->primaryName;
            return $result;
        } catch (\Exception $e) {
            Log::warning('Primary AI (' . $this->primaryName . ') failed for chatWithImage: ' . $e->getMessage() . '. Falling back to ' . $this->fallbackName);
            try {
                $result = $this->fallback->chatWithImage($message, $images, null);
                $this->activeProvider = $this->fallbackName;
                return $result;
            } catch (\Exception $e2) {
                Log::error('Fallback AI (' . $this->fallbackName . ') also failed: ' . $e2->getMessage());
                return 'Kedua AI provider gagal merespons untuk analisa gambar. Silakan coba lagi nanti.';
            }
        }
    }

    public function chatWithUrl(string $message, string $url, ?string $model = null): string
    {
        try {
            $result = $this->primary->chatWithUrl($message, $url, $model);
            if ($this->isErrorResponse($result)) {
                throw new \RuntimeException('Primary AI returned error: ' . $result);
            }
            $this->activeProvider = $this->primaryName;
            return $result;
        } catch (\Exception $e) {
            Log::warning('Primary AI (' . $this->primaryName . ') failed for chatWithUrl: ' . $e->getMessage() . '. Falling back to ' . $this->fallbackName);
            try {
                $result = $this->fallback->chatWithUrl($message, $url, null);
                $this->activeProvider = $this->fallbackName;
                return $result;
            } catch (\Exception $e2) {
                Log::error('Fallback AI (' . $this->fallbackName . ') also failed: ' . $e2->getMessage());
                return 'Kedua AI provider gagal merespons untuk analisa URL. Silakan coba lagi nanti.';
            }
        }
    }

    public function diagnosisImage(string $base64Image, string $mimeType = 'image/jpeg'): array
    {
        try {
            $result = $this->primary->diagnosisImage($base64Image, $mimeType);
            if (isset($result['disease_name']) && $result['disease_name'] === 'Tidak Diketahui' && $result['confidence'] == 0) {
                // Cek apakah solution mengandung indikator error (rate limit, gagal, dll)
                if ($this->isErrorResponse($result['solution'])) {
                    throw new \RuntimeException('Primary AI diagnosis error: ' . $result['solution']);
                }
            }
            $this->activeProvider = $this->primaryName;
            return $result;
        } catch (\Exception $e) {
            Log::warning('Primary AI (' . $this->primaryName . ') failed for diagnosisImage: ' . $e->getMessage() . '. Falling back to ' . $this->fallbackName);
            try {
                $result = $this->fallback->diagnosisImage($base64Image, $mimeType);
                $this->activeProvider = $this->fallbackName;
                return $result;
            } catch (\Exception $e2) {
                Log::error('Fallback AI (' . $this->fallbackName . ') also failed: ' . $e2->getMessage());
                return [
                    'disease_name' => 'Tidak Diketahui',
                    'confidence' => 0,
                    'solution' => 'Kedua AI provider gagal melakukan diagnosa. Silakan coba lagi nanti.',
                ];
            }
        }
    }

    /**
     * Cek apakah respons AI mengindikasikan error (rate limit, koneksi gagal, dll)
     */
    protected function isErrorResponse(string $response): bool
    {
        $errorIndicators = [
            'Rate Limit',
            'rate limit',
            'Koneksi ke',
            'gagal',
            'Gagal mendapatkan respons',
            'cURL error',
            'Terlalu banyak permintaan',
            'Respons kosong',
        ];
        foreach ($errorIndicators as $indicator) {
            if (strpos($response, $indicator) !== false) {
                return true;
            }
        }
        return false;
    }
}