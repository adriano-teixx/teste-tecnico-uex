<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Http\Requests\Settings\StoreGoogleMapsKeyRequest;
use App\Services\GoogleMapsKeyService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class GoogleMapsKeyController extends Controller
{
    public function edit(GoogleMapsKeyService $service): View
    {
        return view('settings.google-maps-key', [
            'googleMapsKey' => $service->getKey(),
        ]);
    }

    public function update(StoreGoogleMapsKeyRequest $request, GoogleMapsKeyService $service): RedirectResponse
    {
        $service->storeKey($request->input('google_maps_key'), $request->user()->id);

        return redirect()
            ->route('settings.google_maps.edit')
            ->with('status', 'Chave do Google Maps atualizada.');
    }
}
