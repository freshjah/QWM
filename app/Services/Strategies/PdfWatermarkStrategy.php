<?php

namespace App\Services\Strategies;

use App\Models\Document;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class PdfWatermarkStrategy
{
    public function embed(Document $document, array $payload): string
    {
        $path = Storage::path( $document->file_path );

        $response = Http::attach(
            'file', file_get_contents($path), basename($path)
        )->post(config('services.qmark_pdf.embed'), [
            'payload' => json_encode($payload)
        ]);

        if (!$response->successful()) {
            throw new \Exception('PDF watermarking failed: ' . $response->body());
        }

        // Store the returned PDF file
        $filename = 'documents/' . Str::uuid() . '.pdf';
        Storage::put($filename, $response->body());
        $wpath = Storage::path( $filename );
        return $wpath;    
    }

    public function extract(Document $document): ?string
    {
        $path = Storage::path( $document->file_path );

        $response = Http::attach(
            'file', file_get_contents($path), basename($path)
        )->post(config('services.qmark_pdf.extract'));

        if ($response->successful()) {
            return $response->json('payload');
            //return $response->json();
        }

        throw new \Exception('PDF extraction failed: ' . $response->body());
    }
}
