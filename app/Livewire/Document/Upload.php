<?php

namespace App\Livewire\Document;

use Livewire\Component;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use App\Models\Document;
use App\Services\WatermarkManager;
use Illuminate\Support\Facades\Http;

class Upload extends Component
{
    use WithFileUploads;

    public $document;
    public $successMessage;
    public $verificationResult;

    public function save()
    {
        $this->validate([
            'document' => 'required|file|mimes:pdf,png,jpg,jpeg,bmp,mp4,avi,mov',
        ]);

        $path = $this->document->store('documents');
        $storedFilePath = Storage::path( $path );
        #$contents = file_get_contents( $storedFilePath );
        #$hash = hash('sha256', $contents);
        $hash = hash_file('sha256', $storedFilePath);
        $mime = $this->document->getMimeType();

        $type = match (true) {
            str_starts_with($mime, 'application/pdf') => 'pdf',
            str_starts_with($mime, 'image/') => 'image',
            str_starts_with($mime, 'video/') => 'video',
            default => 'unsupported',
        };

        if ($type === 'unsupported') {
            session()->flash('upload_error', 'Unsupported file type.');
            return;
        }

        $document = Document::create([
            'user_id' => auth()->id(),
            'uuid' => Str::uuid(),
            'filename' => $this->document->getClientOriginalName(),
            'file_path' => $path,
            'type' => $type,
            'document_hash' => $hash,
            'size' => $this->document->getSize(),
            'signed_at' => now(),
        ]);

        // Call Python PQC service

        $endpoint = config('services.pqc.endpoint').'/sign';
        $encoded = base64_encode(json_encode($document->document_hash));
   
        $response = Http::post($endpoint, [
            'data' => $encoded,
        ]);

        $signature = $response->json('signature');
        $keyId     = $response->json('key_id');

        // Save hybrid signature
        $document->update([
            'signature'      => $signature,
            'public_key_id'  => $keyId,
        ]);

        // Embed watermark using WatermarkManager
        $payload = $document->getPayloadForWatermark();
        
        app(WatermarkManager::class)->embed($document, $payload);

        session()->flash('success', 'Document uploaded and watermarked.');
        $this->reset('document');
    }    
    /*
    public function save()
    {
        
        $this->validate([
            'document' => 'required|file|max:8192', // 8MB max TODO: place in a variable so can be toggled at a granular level
        ]);

        $mime = $this->document->getMimeType();

        $type = match (true) {
            str_starts_with($mime, 'application/pdf') => 'pdf',
            str_starts_with($mime, 'image/') => 'image',
            str_starts_with($mime, 'video/') => 'video',
            default => 'unsupported',
        };

        if ($type === 'unsupported') {
            session()->flash('upload_error', 'Unsupported file type.');
            throw new \Exception('Unsupported file type: ' . $mime);
        }

        $uuid = Str::uuid()->toString();
        $timestamp = now()->toIso8601String();

        $payload = [ 'uuid' => $uuid, 'timestamp' => $timestamp, ];

        // Sign the payload
        $signatureService = app(PqcSignatureService::class);
        $canonical = "{$uuid}|{$timestamp}";
        $signature = $signatureService->sign($canonical);
        $payload['signature'] = $signature;

        try {
            // Determine strategy based on MIME type
            $manager = app(WatermarkManager::class);
            $strategy = $manager->getStrategy($mime);

            // Embed the watermark
            $outputPath = $strategy->embed($this->file, $payload);

            // Save original file for reference (optional)
            $storedPath = $this->file->storeAs('public/uploads', $uuid . '.' . $this->file->getClientOriginalExtension());

            $this->successMessage = "File watermarked successfully and saved at: {$outputPath}";
        } catch (\Exception $e) {
            $this->successMessage = "Error: " . $e->getMessage();
        }

        // Store the uploaded file
        $filename = Str::uuid() . '.' . $this->document->getClientOriginalExtension();
        $path = $this->document->storeAs('documents', $filename);

        // Compute file hash

        $storedFile = Storage::path( $path );
        $docHash = hash_file('sha256', $storedFile);

        // Create DB record
        $document = Document::create([
            'user_id' => auth()->id(),
            'uuid' => Str::uuid(),
            'filename' => $this->document->getClientOriginalName(),
            'file_path' => $path,
            'sha256_hash' => $docHash,
            'type' => $type,
            'size' => $this->document->getSize(),
        ]);

        // Generate watermark
        app(WatermarkService::class)->generate($document);

        // Trigger verification immediately
        $result = app(WatermarkService::class)->verify(
            $path,
            now()->toIso8601String(), // or pull the actual timestamp saved
            $document->uuid
        );

        $this->verificationResult = $result;
        $this->reset('document');
        
        $this->successMessage = "Document uploaded and watermarked successfully!";
        
    }
*/
    /*
    public function save()
    {
        
        $this->validate([
            'document' => 'required|file|mimes:pdf,jpg,jpeg,png|max:5120', // 5MB max
        ]);

        // Store the uploaded file
        $filename = Str::uuid() . '.' . $this->document->getClientOriginalExtension();
        $path = $this->document->storeAs('documents', $filename);

        // Compute file hash

        $storedFile = Storage::path( $path );
        $docHash = hash_file('sha256', $storedFile);
        //$docHash = hash_file('sha256', storage_path("app/{$path}"));

        // Create DB record
        $document = Document::create([
            'user_id' => auth()->id(),
            'uuid' => Str::uuid(),
            'file_path' => $path,
            'sha256_hash' => $docHash,
        ]);

        // Generate watermark
        app(WatermarkService::class)->generate($document);

        // Trigger verification immediately
        $result = app(WatermarkService::class)->verify(
            $path,
            now()->toIso8601String(), // or pull the actual timestamp saved
            $document->uuid
        );

        $this->verificationResult = $result;
        $this->reset('document');
        
        $this->successMessage = "Document uploaded and watermarked successfully!";
        
    }
    */
    public function render()
    {
        return view('livewire.documents.upload');
    }
}
