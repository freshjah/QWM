<div class="max-w-md mx-auto p-4 sm:p-6 bg-white shadow rounded-lg space-y-6">

    <h2 class="text-lg sm:text-xl font-semibold text-gray-800 text-center">Verify Document Authenticity</h2>
    <form wire:submit.prevent="verify" class="space-y-4">
        <input type="file" wire:model="document" accept=".pdf,.png,.jpg,.jpeg,.bmp" class="block w-full">
        @error('document') <span class="text-red-600 text-xs">{{ $message }}</span> @enderror

        <button type="submit"
            class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">
            Verify Document
        </button>
    </form>

    <script>
        window.addEventListener('verification-complete', event => {
            alert(event.detail.message);
        });
    </script>
</div>

{{--
<div class="max-w-md mx-auto p-4 sm:p-6 bg-white shadow rounded-lg space-y-6">

    <h2 class="text-lg sm:text-xl font-semibold text-gray-800 text-center">Verify Document Authenticity</h2>

    <form wire:submit.prevent="verify" class="space-y-5">
        <!-- Upload -->
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Document</label>
            <input type="file" wire:model="document" class="block w-full text-sm text-gray-700 border rounded p-2">
            @error('document') <span class="text-red-600 text-xs">{{ $message }}</span> @enderror
        </div>

        <!-- Timestamp -->
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Timestamp</label>
            <input type="text" wire:model.lazy="timestamp" placeholder="e.g. 2025-06-16T13:23:00Z"
                class="block w-full border text-sm p-2 rounded">
            @error('timestamp') <span class="text-red-600 text-xs">{{ $message }}</span> @enderror
        </div>

        <!-- UUID -->
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Document UUID</label>
            <input type="text" wire:model.lazy="documentUuid"
                class="block w-full border text-sm p-2 rounded">
            @error('documentUuid') <span class="text-red-600 text-xs">{{ $message }}</span> @enderror
        </div>

        <!-- Button -->
        <div class="flex justify-center">
            <button type="submit"
                class="w-full sm:w-auto px-5 py-2 text-sm font-semibold text-white bg-blue-600 rounded hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-300">
                Verify
            </button>
        </div>
    </form>

    <!-- Result -->
    @if($verificationResult)
        <div class="p-4 text-sm rounded border 
            {{ $verificationResult['status'] === 'valid' 
                ? 'border-green-600 bg-green-50 text-green-800' 
                : 'border-red-600 bg-red-50 text-red-800' }}">
            <p class="font-semibold">
                {{ strtoupper($verificationResult['status']) }}
            </p>
            <p>{{ $verificationResult['message'] }}</p>
            @if(isset($verificationResult['verified_at']))
                <p class="mt-1 text-xs text-gray-500">Verified at: {{ $verificationResult['verified_at'] }}</p>
            @endif
        </div>
    @endif
</div>
--}}