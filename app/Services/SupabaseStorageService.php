<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Http\UploadedFile;

class SupabaseStorageService
{
    protected $url;
    protected $key;
    protected $bucket;

    public function __construct()
    {
        $this->url = env('SUPABASE_URL');
        $this->key = env('SUPABASE_KEY'); // Service Role or Anon Key (must have write policy)
        $this->bucket = env('SUPABASE_BUCKET', 'uploads'); // Default bucket name
    }

    /**
     * Upload a file to Supabase Storage
     *
     * @param  UploadedFile  $file
     * @param  string  $folder
     * @return string|null Public URL of the uploaded file
     */
    public function upload(UploadedFile $file, $folder = 'diagnosa')
    {
        $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
        $path = $folder . '/' . $filename;

        // Endpoint: POST {supabase_url}/storage/v1/object/{bucket}/{path}
        $endpoint = $this->url . '/storage/v1/object/' . $this->bucket . '/' . $path;

        try {
            // Read file content
            $content = file_get_contents($file->getRealPath());

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->key,
                'Content-Type' => $file->getMimeType(),
                'x-upsert' => 'false',
            ])->send('POST', $endpoint, [
                        'body' => $content
                    ]);

            if ($response->successful()) {
                // Return Public URL
                // Format: {supabase_url}/storage/v1/object/public/{bucket}/{path}
                return $this->url . '/storage/v1/object/public/' . $this->bucket . '/' . $path;
            }

            // Log error if needed
            // \Log::error('Supabase Upload Failed: ' . $response->body());
            return null;

        } catch (\Exception $e) {
            // \Log::error('Supabase Upload Error: ' . $e->getMessage());
            return null;
        }
    }
}
