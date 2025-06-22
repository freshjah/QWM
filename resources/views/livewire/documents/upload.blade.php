<x-layouts.page :title="$title ?? null">
    <div class="max-w-xl mx-auto p-6 bg-white dark:bg-gray-800 rounded shadow space-y-4">

        <h2 class="text-xl font-bold text-gray-800 dark:text-white">Upload Document</h2>

        @if ($successMessage)
            <div class="p-3 bg-green-100 text-green-800 rounded text-sm">{{ $successMessage }}</div>
        @endif

        @if ($verificationResult)
            <div class="p-4 rounded text-sm mt-4 
                {{ $verificationResult['status'] === 'valid' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                <p class="font-semibold">{{ strtoupper($verificationResult['status']) }}</p>
                <p>{{ $verificationResult['message'] }}</p>
                @if (isset($verificationResult['verified_at']))
                    <p class="text-xs text-gray-500 mt-1">Verified at: {{ $verificationResult['verified_at'] }}</p>
                @endif
            </div>
        @endif

        @if (session()->has('upload_error'))
            <div class="bg-red-100 text-red-700 px-4 py-2 rounded mb-3">
                {{ session('upload_error') }}
            </div>
        @endif
        <form wire:submit.prevent="save" class="space-y-4">
            <div>
                <input type="file" wire:model="document" class="w-full border p-2 rounded text-sm dark:bg-gray-900 dark:text-white">
                @error('file') <span class="text-red-600 text-xs">{{ $message }}</span> @enderror
            </div>

            <button type="submit"
                class="w-full px-4 py-2 bg-indigo-600 text-white font-semibold rounded hover:bg-indigo-700">
                Upload & Watermark
            </button>
        </form>

        <div wire:loading wire:target="save" class="text-sm text-gray-500 mt-2">Processing document...</div>
    </div>
</x-layouts.page >
