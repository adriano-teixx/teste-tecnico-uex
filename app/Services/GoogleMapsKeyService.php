<?php

namespace App\Services;

use App\Models\Setting;

class GoogleMapsKeyService
{
    public function getKey(): ?string
    {
        return Setting::getValue('google_maps_api_key');
    }

    public function storeKey(?string $key, ?int $userId = null): Setting
    {
        return Setting::setValue('google_maps_api_key', $key, $userId);
    }

    public function hasKey(): bool
    {
        $key = $this->getKey();

        return is_string($key) && trim($key) !== '';
    }
}
