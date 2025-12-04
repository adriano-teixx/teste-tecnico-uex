<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\AddressCepRequest;
use App\Http\Requests\AddressGeocodeRequest;
use App\Http\Requests\AddressSearchRequest;
use App\Services\GeocodingService;
use App\Services\ViaCepService;
use Illuminate\Http\JsonResponse;
use RuntimeException;

class AddressSearchController extends Controller
{
    /**
     * Handle the incoming request to search addresses.
     */
    public function __invoke(AddressSearchRequest $request, ViaCepService $viaCepService): JsonResponse
    {
        $results = $viaCepService->search(
            $request->input('uf'),
            $request->input('city'),
            $request->input('street'),
        );

        return response()->json(['data' => $results]);
    }

    public function geocode(AddressGeocodeRequest $request, GeocodingService $geocoder): JsonResponse
    {
        try {
            $coordinates = $geocoder->geocode([
                'street' => $request->input('street'),
                'number' => $request->input('number'),
                'district' => $request->input('district'),
                'city' => $request->input('city'),
                'state' => $request->input('state'),
            ]);
        } catch (RuntimeException $exception) {
            return response()->json(['message' => $exception->getMessage()], 422);
        }

        return response()->json(['data' => $coordinates]);
    }

    public function byCep(AddressCepRequest $request, ViaCepService $viaCepService): JsonResponse
    {
        try {
            $address = $viaCepService->findByCep($request->input('cep'));
        } catch (RuntimeException $exception) {
            return response()->json(['message' => $exception->getMessage()], 422);
        }

        return response()->json(['data' => $address]);
    }
}
