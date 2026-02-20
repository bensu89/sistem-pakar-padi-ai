<?php

namespace App\Services;

interface AIServiceInterface
{
    /**
     * Chat text biasa (tanpa gambar)
     */
    public function chat(string $message, ?string $model = null, ?string $diseaseContext = null): string;

    /**
     * Chat dengan gambar (vision model) - Support Multiple Images
     */
    public function chatWithImage(string $message, array $images, ?string $model = null): string;

    /**
     * Chat dengan konten URL
     */
    public function chatWithUrl(string $message, string $url, ?string $model = null): string;

    /**
     * Diagnosa daun padi (return JSON)
     */
    public function diagnosisImage(string $base64Image, string $mimeType = 'image/jpeg'): array;
}
