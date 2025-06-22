<?php
namespace App\Services;

use Illuminate\Support\Facades\Http;

class PqcSignatureService
{
    protected string $endpoint;

    public function __construct()
    {
        $this->endpoint = config('services.pqc.url', 'http://localhost:8002');
    }

    public function sign(string $canonical): string
    {
        $response = Http::post("{$this->endpoint}/sign", [
            'data' => $canonical,
        ]);

        if ($response->failed()) {
            throw new \Exception("Signing failed: " . $response->body());
        }

        return $response->json()['signature'] ?? '';
    }

    public function verify(string $canonical, string $signature): bool
    {
        $response = Http::post("{$this->endpoint}/verify", [
            'data' => $canonical,
            'signature' => $signature,
        ]);

        if ($response->failed()) {
            throw new \Exception("Verification failed: " . $response->body());
        }

        return $response->json()['valid'] ?? false;
    }
}
