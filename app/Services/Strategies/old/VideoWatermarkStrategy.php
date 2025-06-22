<?php

namespace App\Services\Strategies;

use App\Services\Watermark\WatermarkStrategyInterface;
use Illuminate\Http\UploadedFile;

class VideoWatermarkStrategy implements WatermarkStrategyInterface
{
    public function embed(UploadedFile $file, array $payload): string
    {
        // Placeholder for video watermark embedding
        return 'video_watermarked_path.mp4';
    }

    public function extract(UploadedFile $file): array
    {
        // Placeholder for video watermark extraction
        return ['uuid' => 'video1234', 'signature' => '...'];
    }
}
