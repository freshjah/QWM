<?php

namespace App\Services\Strategies;

use App\Models\Document;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

class ImageWatermarkStrategy
{
    public function embed(Document $document, array $payload): string
    {
        $path = Storage::path( $document->file_path );

        $response = Http::attach(
            'file', file_get_contents($path), basename($path)
        )->post(config('services.qmark_image.embed'), [
            'payload' => json_encode($payload)
        ]);

        if ($response->successful()) {
            $filename = 'watermarked_' . now()->timestamp . '.png';
            $twpath = "watermarked/{$filename}";
            Storage::put($twpath, $response->body());
            $wpath = Storage::path( $twpath );
                //return array("paths"=>$path);
            return $wpath;
        }

        throw new \Exception('Image watermarking failed: ' . $response->body());
    }

    public function extract(Document $document): array
    {
        $path = Storage::path( $document->file_path );

        $response = Http::attach(
            'file', file_get_contents($path), basename($path)
        )->post(config('services.qmark_image.extract'));

        if ($response->successful()) {
            return $response->json();
        }

        throw new \Exception('Image extraction failed: ' . $response->body());
    }
}
