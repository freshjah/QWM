<?php

use App\Livewire\Document\History;
use App\Livewire\Document\Upload;
use App\Livewire\Document\Verification;
use App\Livewire\Settings\Appearance;
use App\Livewire\Settings\Password;
use App\Livewire\Settings\Profile;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('home');
})->name('home');

Route::view('dashboard', 'dashboard')
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware(['auth'])->group(function () {
    Route::redirect('settings', 'settings/profile');

    Route::get('settings/profile', Profile::class)->name('settings.profile');
    Route::get('settings/password', Password::class)->name('settings.password');
    Route::get('settings/appearance', Appearance::class)->name('settings.appearance');

    Route::get('documents/upload', Upload::class)->name('documents.upload');
    Route::get('documents/verification', Verification::class)->name('documents.verification');
    Route::get('documents/history', History::class)->name('documents.history');
});

require __DIR__.'/auth.php';
