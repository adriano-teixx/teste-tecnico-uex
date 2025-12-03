<?php

namespace App\Providers;

use App\Services\GoogleMapsKeyService;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(GoogleMapsKeyService $service): void
    {
        if (Schema::hasTable('settings')) {
            $googleMapsKey = $service->getKey() ?? config('services.google_maps.key');

            if ($googleMapsKey !== null) {
                config(['services.google_maps.key' => $googleMapsKey]);
            }
        }
    }
}
