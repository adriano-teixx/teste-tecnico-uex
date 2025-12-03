<?php

use App\Http\Controllers\Api\AddressSearchController;
use App\Http\Controllers\Api\ContactController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Settings\GoogleMapsKeyController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', DashboardController::class)->name('dashboard');

    Route::prefix('contacts')->name('contacts.')->group(function () {
        Route::get('/', [ContactController::class, 'index']);
        Route::post('/', [ContactController::class, 'store']);
        Route::post('mock', [ContactController::class, 'mock'])->name('mock');
        Route::get('{contact}', [ContactController::class, 'show']);
        Route::put('{contact}', [ContactController::class, 'update']);
        Route::delete('{contact}', [ContactController::class, 'destroy']);
    });

    Route::get('/addresses', AddressSearchController::class)->name('addresses.search');
    Route::get('/addresses/geocode', [AddressSearchController::class, 'geocode'])
        ->name('addresses.geocode');
    Route::get('/addresses/cep', [AddressSearchController::class, 'byCep'])
        ->name('addresses.cep');

    Route::get('/settings/google-maps', [GoogleMapsKeyController::class, 'edit'])
        ->name('settings.google_maps.edit');

    Route::patch('/settings/google-maps', [GoogleMapsKeyController::class, 'update'])
        ->name('settings.google_maps.update');
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
