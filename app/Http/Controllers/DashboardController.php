<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Services\GoogleMapsKeyService;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(GoogleMapsKeyService $googleMapsKeyService): View
    {
        return view('dashboard', [
            'needsGoogleMapsSetup' => ! $googleMapsKeyService->hasKey(),
        ]);
    }
}
