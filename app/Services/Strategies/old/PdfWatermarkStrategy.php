<?php

namespace App\Services\Strategies;

use App\Services\Watermark\WatermarkStrategyInterface;
use Illuminate\Http\UploadedFile;

class PdfWatermarkStrategy implements WatermarkStrategyInterface
{
    public function embed(UploadedFile $file, array $payload): string
    {
        // TODO: integrate with PDF embedding microservice
        return 'pdf_watermarked_path.pdf';
    }

    public function extract(UploadedFile $file): array
    {
        // TODO: integrate with PDF extraction logic
        return ['uuid' => '1234', 'signature' => '...'];
    }
}
