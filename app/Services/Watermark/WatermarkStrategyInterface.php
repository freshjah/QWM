<?php

namespace App\Services\Watermark;

use Illuminate\Http\UploadedFile;

interface WatermarkStrategyInterface
{
    public function embed(UploadedFile $file, array $payload): string;
    public function extract(UploadedFile $file): array;
}
