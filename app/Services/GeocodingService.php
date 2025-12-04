<?php

namespace App\Services;

class GeocodingService
{
    public function __construct(
        private GoogleMapsGeocodingService $googleMapsService,
        private OpenStreetMapGeocodingService $openStreetService,
    ) {
    }

    /**
     * Resolve latitude and longitude for the given address by delegating to the configured provider.
     *
     * @param  array<string, string>  $address
     * @return array<string, float>
     */
    public function geocode(array $address): array
    {
        $provider = config('services.geocoding.provider', 'google');

        return match ($provider) {
            'openstreet' => $this->openStreetService->geocode($address),
            default => $this->googleMapsService->geocode($address),
        };
    }
}
