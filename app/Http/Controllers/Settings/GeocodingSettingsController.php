<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Http\Requests\Settings\StoreGeocodingSettingsRequest;
use App\Services\GeocodingSettingsService;
use App\Services\GoogleMapsKeyService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class GeocodingSettingsController extends Controller
{
    public function edit(GoogleMapsKeyService $googleMapsKeyService, GeocodingSettingsService $settingsService): View
    {
        return view('settings.geocoding', [
            'googleMapsKey' => $googleMapsKeyService->getKey(),
            'geocodingProvider' => $settingsService->getProvider(),
            'needsOnboarding' => !$googleMapsKeyService->hasKey() || !$settingsService->hasStoredProvider(),
        ]);
    }

    public function update(StoreGeocodingSettingsRequest $request, GoogleMapsKeyService $googleMapsKeyService, GeocodingSettingsService $settingsService): RedirectResponse
    {
        $googleMapsKeyService->storeKey($request->input('google_maps_key'), $request->user()->id);
        $settingsService->storeProvider($request->input('geocoding_provider'), $request->user()->id);

        return redirect()
            ->route('settings.google_maps.edit')
            ->with('status', 'Configurações de geocodificação atualizadas.');
    }
}
