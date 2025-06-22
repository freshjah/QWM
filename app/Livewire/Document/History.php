<?php

namespace App\Livewire\Document;

use Livewire\Component;
use App\Models\Document;
use App\Services\WatermarkManager;
use Livewire\WithPagination;

class History extends Component
{
    use WithPagination;

    public $documents;

    public function mount()
    {
        $this->documents = Document::where('user_id', auth()->id())->latest()->get();
    }

    public function verify(Document $document)
    {
        try {
            $payload = app(WatermarkManager::class)->getVerificationPayload($document);

            $isValid = app(WatermarkManager::class)->verifyHybrid(
                $payload['data'],
                $payload['signature'],
                $payload['key_id']
            );

            activity()->performedOn($document)
                ->withProperties(['valid' => $isValid])
                ->log('Document verification from history');

            $this->dispatchBrowserEvent('verification-complete', [
                'status' => $isValid ? 'success' : 'error',
                'message' => $isValid ? '✅ Stored document is valid.' : '❌ Verification failed.',
            ]);
        } catch (\Throwable $e) {
            $this->dispatchBrowserEvent('verification-complete', [
                'status' => 'error',
                'message' => 'Error during stored verification: ' . $e->getMessage(),
            ]);
        }
    }

    public function render()
    {
        return view('livewire.documents.history', ['documents' => $this->documents]);
    }

/*
    public function verify($documentPath)
    {
        $filePath = Storage::path($documentPath);
        $service = new WatermarkService();
        $result = $service->verifyEmbeddedWatermark( $filePath );

        dd($result);
    }

    public function render()
    {
        $documents = Document::with('watermark')
            ->where('user_id', auth()->id())
            ->latest()
            ->paginate(10);

        return view('livewire.documents.history', compact('documents'));
    }
*/        
}
