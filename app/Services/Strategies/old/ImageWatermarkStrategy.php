<?php

namespace App\Services\Strategies;

use App\Services\Watermark\WatermarkStrategyInterface;
use App\Services\ImageWatermarkService;
use Illuminate\Http\UploadedFile;

class ImageWatermarkStrategy implements WatermarkStrategyInterface
{
    public function embed(UploadedFile $file, array $payload): string
    {
        $service = app(ImageWatermarkService::class);
        $service->embed($file, $payload);
        return 'image_watermarked_path.jpg';
    }

    public function extract(UploadedFile $file): array
    {
        $service = app(ImageWatermarkService::class);
        return $service->extract($file);
    }
}
