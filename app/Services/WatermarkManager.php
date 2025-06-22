<?php

namespace App\Services;

use App\Models\Document;
use App\Services\Strategies\PdfWatermarkStrategy;
use App\Services\Strategies\ImageWatermarkStrategy;
use App\Services\Strategies\VideoWatermarkStrategy;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class WatermarkManager
{
    public function embed(Document $document, array $payload): string
    {
        $strategy = $this->resolveStrategy($document->type);
        $result =  $strategy->embed($document, $payload);

        return $result;
    }

    public function extract(Document $document): array
    {
        $strategy = $this->resolveStrategy($document->type);
        return $strategy->extract($document);
    }

    protected function resolveStrategy(string $mime): object
    {
        return match (true) {
            ($mime == 'pdf') => new PdfWatermarkStrategy(),
            ($mime == 'image') => new ImageWatermarkStrategy(),
            ($mime == 'video') => new VideoWatermarkStrategy(),
            default => throw new \Exception("Unsupported file type for watermarking: $mime"),
            // Str::startsWith($mime, 'application/pdf') => new PdfWatermarkStrategy(),
            // Str::startsWith($mime, 'image/') => new ImageWatermarkStrategy(),
            // Str::startsWith($mime, 'video/') => new VideoWatermarkStrategy(),
            // default => throw new \Exception("Unsupported file type for watermarking: $mime"),
        };
    }

    protected function getStrategy(string $type)
    {
        return match ($type) {
            'pdf'   => app(PdfWatermarkStrategy::class),
            'image' => app(ImageWatermarkStrategy::class),
            'video' => app(VideoWatermarkStrategy::class),
            default => throw new \Exception("Unsupported document type: {$type}"),
        };
    }

    public function getVerificationPayload(Document $document): array
    {
        return match ($document->type) {
            'pdf' => $this->extractFromPdf($document),
            'image' => $this->extractFromImage($document),
            'video' => $this->extractFromVideo($document),
            default => throw new \Exception("Unsupported document type: " . $document->type),
        };
    }

    protected function extractFromPdf(Document $document): array
    {
        //dd($document);
        $path = Storage::path( $document->file_path );
    $response = Http::attach(
            'file', file_get_contents($path), 'testfile.pdf'
        )
        ->post(config('services.qmark_pdf.extract'));

    if (!$response->successful()) {
        logger()->error('Failed to extract watermark from PDF', [
            'status' => $response->status(),
            'body' => $response->body(),
        ]);
        throw new \Exception('Failed to extract pdf watermark');
    }
    $payload = json_decode($response['payload']);

    //return $response->json('payload');
        return [
            'data' => $response['payload'],
            'signature' => $payload->signature,
            'key_id' => $payload->publicKeyId,
        ];
    }

    protected function extractFromImage(Document $document): array
    {

        $storedFilePath = Storage::path( $document->file_path );

        $response = Http::attach(
            'file',
            file_get_contents( $storedFilePath ),
            basename($storedFilePath)
        )->post(env('IMAGEWM_SERVICE_ENDPOINT') . '/extract');
dd( $response['payload'] );
        if ($response->failed()) {
            throw new \Exception('Failed to extract image watermark');
        }

        return [
            'data' => $response['payload'],
            'signature' => $response['signature'],
            'key_id' => $response['key_id'],
        ];
    }

    protected function extractFromVideo(Document $document): array
    {
        $response = Http::attach(
            'file',
            file_get_contents(storage_path('app/' . $document->path)),
            basename($document->path)
        )->post(env('VIDEO_WATERMARK_API_URL') . '/extract');

        if ($response->failed()) {
            throw new \Exception('Failed to extract video watermark');
        }

        return [
            'data' => $response['data'],
            'signature' => $response['signature'],
            'key_id' => $response['key_id'],
        ];
    }

    public function verifyHybrid(string $data, string $signature, string $keyId): bool
    {
        $response = Http::post(env('PQC_SERVICE_ENDPOINT') . '/verify', [
            'data' => $data,
            'signature' => $signature,
            'key_id' => $keyId,
        ]);
dd($response);
        return $response->ok() && $response['valid'] === true;
    }
}
