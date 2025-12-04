<?php

namespace App\Services;

use App\Models\Setting;

class GeocodingSettingsService
{
    public function getProvider(): string
    {
        return Setting::getValue('geocoding_provider', config('services.geocoding.provider'));
    }

    public function storeProvider(string $provider, ?int $userId = null): Setting
    {
        return Setting::setValue('geocoding_provider', $provider, $userId);
    }
}
