<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;

class ImageWatermarkService
{
    protected string $endpoint;

    public function __construct()
    {
        $this->endpoint = config('services.imagewm.url', 'http://localhost:8001');
    }

    public function embed(UploadedFile $image, array $payload): array
    {       
        $response = Http::attach(
            'image', file_get_contents($image->getRealPath()), $image->getClientOriginalName()
        )->post("{$this->endpoint}/embed", [
            'payload' => json_encode($payload),
        ]);

        return $response->json();
    }

    public function extract(UploadedFile $image): array
    {
        dd("extract");
        $response = Http::attach(
            'image', file_get_contents($image->getRealPath()), $image->getClientOriginalName()
        )->post("{$this->endpoint}/extract");
dd("extract");
        return $response->json();
    }
}
