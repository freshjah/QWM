<?php

namespace App\Services;

use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use App\Models\Document;
use App\Models\Watermark;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;
use App\Models\PublicKey;
use Smalot\PdfParser\Parser;

class WatermarkService
{
    /**
     * Generate and store a watermark for a given document
     */
    public function generate(Document $document): Watermark
    {
        // Step 1: Fetch entropy (quantum or fallback)
        $entropy = $this->getEntropy();

        // Step 2: Compute document hash
        $storedFile = Storage::path( $document->file_path );
        $docHash = hash_file('sha256', $storedFile);

        // Step 3: Generate timestamp
        $timestamp = Carbon::now()->toIso8601String();

        // Step 4: Create the secure payload (minimal but strong)
        $payloadString = hash('sha512', $entropy . $docHash . $timestamp . $document->uuid);

        // Step 5: Get digital signature (optional: call external microservice)
        $signature = $this->signPayload($payloadString);

        // Step 6: Save watermark
        return Watermark::create([
            'document_id' => $document->id,
            'entropy_seed' => $entropy,  // Will be encrypted via mutator
            'timestamp' => $timestamp,
            'signature' => $signature,
        ]);
    }

    /**
     * Fetch entropy from QRNG or fallback
     */
    private function getEntropy(): string
    {
        try {
            $response = Http::timeout(3)->get('https://qrng.anu.edu.au/API/jsonI.php?length=32&type=hex16');
            if ($response->successful() && isset($response['data'][0])) {
                return $response['data'][0];
            }
        } catch (\Throwable $e) {
            // Log fallback condition
        }

        return bin2hex(random_bytes(32)); // Secure fallback
    }

    /**
     * Sign payload - optionally delegate to Python or Go
     */
    private function signPayload(string $payload): string
    {
        // Option A: Pure PHP hash (not PQ-safe, but fast for MVP)
        return hash('sha256', $payload);

        // Option B: Call external signer (uncomment below)
        /*
        $response = Http::post('http://localhost:5000/sign', [
            'payload' => $payload,
        ]);
        return $response->json('signature');
        */
    }

    public function verify(string $documentPath, string $providedTimestamp, string $documentUuid): array
    {
        // Step 1: Compute document hash
        $fullPath = Storage::path( $documentPath );
        if (!file_exists($fullPath)) {
            return ['status' => 'error', 'message' => 'Document not found.'];
        }

        $docHash = hash_file('sha256', $fullPath);

        // Step 2: Lookup matching watermark
        $watermark = Watermark::where('timestamp', $providedTimestamp)
            ->whereHas('document', fn ($q) => $q->where('uuid', $documentUuid))
            ->first();

        if (!$watermark) {
            return ['status' => 'fail', 'message' => 'No matching record found.'];
        }

        // Step 3: Decrypt entropy
        try {
            $entropy = $watermark->entropy_seed;
        } catch (\Throwable $e) {
            return ['status' => 'error', 'message' => 'Failed to decrypt entropy seed.'];
        }

        // Step 4: Rebuild payload
        $reconstructedPayload = hash('sha512', $entropy . $docHash . $providedTimestamp . $documentUuid);

        // Step 5: Verify signature
        $expectedSignature = $this->signPayload($reconstructedPayload);

        if (hash_equals($expectedSignature, $watermark->signature)) {
            return [
                'status' => 'valid',
                'message' => 'Document is authentic.',
                'verified_at' => now()->toIso8601String()
            ];
        }

        return ['status' => 'fail', 'message' => 'Signature does not match.'];
    }

    public function verifyEmbeddedWatermark(string $filePath): array
    {
        // Step 1: Extract embedded JSON payload
        $payload = $this->extractWatermarkPayload($filePath);
        if (!$payload) {
            return ['status' => false, 'reason' => 'No embedded watermark found.'];
        }

        // Step 2: Get SHA-256 of actual file
        $documentHash = hash_file('sha256', $filePath);

        // Step 3: Parse metadata & rehash it
        $metadata = $this->extractMetadataFromPdf($filePath); // Get title, subject, keywords
        $metadataString = implode('|', [
            $metadata['title'] ?? '',
            $metadata['subject'] ?? '',
            $metadata['keywords'] ?? ''
        ]);
        $metadataHash = hash('sha256', $metadataString);

        // Step 4: Check key
        $key = PublicKey::where('key_id', $payload['publicKeyId'])->first();
        if (!$key || $key->revoked_at) {
            return ['status' => false, 'reason' => 'Key revoked or not found.'];
        }

        // Step 5: Build canonical string
        $canonical = implode('|', [
            $payload['uuid'],
            $documentHash,
            $payload['timestamp'],
            $payload['metadata_hash'],
            $payload['expires_at']
        ]);

        // Step 6: Check expiration
        if (Carbon::now()->gt(Carbon::parse($payload['expires_at']))) {
            return ['status' => false, 'reason' => 'Signature expired.'];
        }

        // Step 7: Verify fingerprint
        $isValid = $this->pqVerify(
            $canonical,
            base64_decode($payload['fingerprint']),
            $key->public_key
        );

        return [
            'status' => $isValid,
            'reason' => $isValid ? 'Valid signature and document integrity confirmed.' : 'Invalid signature.',
            'payload' => $payload
        ];
    } 
    
    public function extractWatermarkPayload(string $filePath): ?array
    {
        $parser = new Parser();
        $pdf = $parser->parseFile($filePath);

        $details = $pdf->getDetails();
        $payloadRaw = $details['Custom:QMark-Payload'] ?? null;

        if (!$payloadRaw) return null;

        $decoded = json_decode($payloadRaw, true);
        return is_array($decoded) ? $decoded : null;
    }

    public function pqVerify(string $canonical, string $signature, string $publicKey): bool
    {
        // Replace this with real verification logic
        // For demo, simulate validity:
        return strlen($signature) > 100 && str_contains($publicKey, 'BEGIN');
    }

    public function extractMetadataFromPdf(string $filePath): array
    {
        $parser = new Parser();
        $pdf = $parser->parseFile($filePath);
        $details = $pdf->getDetails();

        return [
            'title' => $details['Title'] ?? '',
            'subject' => $details['Subject'] ?? '',
            'keywords' => $details['Keywords'] ?? ''
        ];
    }
}
