<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\UploadedFile;

class SupabaseStorageService
{
    protected string $url;
    protected string $key;
    protected string $bucket;

    public function __construct()
    {
        $this->url = config('services.supabase.url');
        $this->key = config('services.supabase.key');
        $this->bucket = config('services.supabase.bucket', 'uploads');
    }

    /**
     * Upload a file to Supabase Storage.
     * Returns the public URL or null on failure.
     */
    public function upload(UploadedFile $file, string $folder = 'diagnosa'): ?string
    {
        $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
        $path = $folder . '/' . $filename;
        $endpoint = $this->url . '/storage/v1/object/' . $this->bucket . '/' . $path;

        try {
            $content = file_get_contents($file->getRealPath());

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->key,
                'Content-Type' => $file->getMimeType(),
                'x-upsert' => 'false',
            ])->send('POST', $endpoint, ['body' => $content]);

            if ($response->successful()) {
                return $this->url . '/storage/v1/object/public/' . $this->bucket . '/' . $path;
            }

            Log::warning('Supabase upload failed', [
                'status' => $response->status(),
                'body' => $response->body(),
                'path' => $path,
            ]);
            return null;

        } catch (\Exception $e) {
            Log::warning('Supabase upload error', ['message' => $e->getMessage(), 'path' => $path]);
            return null;
        }
    }

    /**
     * Delete a file from Supabase Storage by its public URL.
     * Returns true on success, false otherwise.
     */
    public function delete(string $publicUrl): bool
    {
        // Extract storage path from public URL
        // URL format: {url}/storage/v1/object/public/{bucket}/{path}
        $prefix = $this->url . '/storage/v1/object/public/' . $this->bucket . '/';

        if (!str_starts_with($publicUrl, $prefix)) {
            return false;
        }

        $path = substr($publicUrl, strlen($prefix));
        $endpoint = $this->url . '/storage/v1/object/' . $this->bucket . '/' . $path;

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->key,
            ])->delete($endpoint);

            if ($response->successful()) {
                return true;
            }

            Log::warning('Supabase delete failed', [
                'status' => $response->status(),
                'body' => $response->body(),
                'path' => $path,
            ]);
            return false;

        } catch (\Exception $e) {
            Log::warning('Supabase delete error', ['message' => $e->getMessage(), 'path' => $path]);
            return false;
        }
    }

    /**
     * Check if a URL points to this Supabase bucket.
     */
    public function isSupabaseUrl(string $url): bool
    {
        return str_starts_with($url, $this->url . '/storage/v1/object/public/' . $this->bucket . '/');
    }
}
