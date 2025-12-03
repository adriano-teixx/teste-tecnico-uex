<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\AddressSearchRequest;
use App\Services\ViaCepService;
use Illuminate\Http\JsonResponse;

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
}
