<?php

namespace App\Livewire\Document;

use Livewire\Component;
use Livewire\WithFileUploads;
use App\Models\Document;
use App\Services\WatermarkManager;
use Illuminate\Support\Facades\Storage;

class Verification extends Component
{
    use WithFileUploads;

    public $document;
    public $timestamp;
    public $documentUuid;
    public $verificationResult;

    public function verify()
    {
        $this->validate([
            'document' => 'required|file|mimes:pdf,png,jpg,jpeg,bmp',
        ]);

        $path = $this->document->store('temp_verifications');
        $mime = $this->document->getMimeType();

        $tempDoc = new Document([
            'file_path' => $path,
            'type' => str_contains($mime, 'pdf') ? 'pdf' : 'image',
        ]);

        try {
           
            $payload = app(WatermarkManager::class)->getVerificationPayload($tempDoc);
//dd($payload);
            $isValid = app(WatermarkManager::class)->verifyHybrid(
                $payload['data'],
                $payload['signature'],
                $payload['key_id']
            );
dump('check');            
dd($isValid);
            Storage::delete($path);

            $this->dispatchBrowserEvent('verification-complete', [
                'status' => $isValid ? 'success' : 'error',
                'message' => $isValid ? '✅ Document is valid.' : '❌ Document verification failed.',
            ]);
        } catch (\Throwable $e) {
            dd($e->getMessage());
            $this->dispatchBrowserEvent('verification-complete', [
                'status' => 'error',
                'message' => 'Error during verification: ' . $e->getMessage(),
            ]);
        }
    }

    /*
    public function verify()
    {
        $this->validate([
            'document' => 'required|file',
        ]);

        // Save temporarily
        $tempPath = $this->document->storeAs('temp-verification', Str::uuid());

        // Verify
        $result = app(WatermarkService::class)->verifyEmbeddedWatermark( $tempPath );

        $this->verificationResult = $result;

        //Cleanup
        Storage::delete($tempPath);
    }
    */
    /*
    public function verify()
    {
        $this->validate([
            'document' => 'required|file',
            'timestamp' => 'required|string',
            'documentUuid' => 'required|string',
        ]);

        // Save temporarily
        $tempPath = $this->document->storeAs('temp-verification', Str::uuid());

        // Verify
        $result = app(WatermarkService::class)->verify(
            $tempPath,
            $this->timestamp,
            $this->documentUuid
        );

        $this->verificationResult = $result;
    }    
    */
    public function render()
    {
        return view('livewire.documents.verification');
    }
}
