<?php

namespace App\Services\Strategies;

use App\Models\Document;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

class VideoWatermarkStrategy
{
    public function embed(Document $document, array $payload): array
    {
        $path = Storage::path( $document->file_path );

        $response = Http::attach(
            'file', file_get_contents($path), basename($path)
        )->post(config('services.qmark_video.embed'), [
            'payload' => json_encode($payload)
        ]);

        if ($response->successful()) {
            return $response->json();
        }

        throw new \Exception('Video watermarking failed: ' . $response->body());
    }

    public function extract(Document $document): array
    {
        $path = Storage::path( $document->file_path );

        $response = Http::attach(
            'file', file_get_contents($path), basename($path)
        )->post(config('services.qmark_video.extract'));

        if ($response->successful()) {
            return $response->json();
        }

        throw new \Exception('Video extraction failed: ' . $response->body());
    }
}
