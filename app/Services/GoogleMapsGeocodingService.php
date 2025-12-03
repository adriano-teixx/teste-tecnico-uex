<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use RuntimeException;

class GoogleMapsGeocodingService
{
    /**
     * Resolve latitude and longitude for the given address.
     *
     * @param  array<string, string>  $address
     * @return array<string, float>
     */
    public function geocode(array $address): array
    {
        $apiKey = config('services.google_maps.key');

        if (! $apiKey) {
            throw new RuntimeException('Chave do Google Maps não está configurada.');
        }

        $components = array_filter([
            $address['street'] ?? '',
            $address['number'] ?? '',
            $address['district'] ?? '',
            $address['city'] ?? '',
            $address['state'] ?? '',
            'Brasil',
        ]);

        $response = Http::retry(2, 150)->get('https://maps.googleapis.com/maps/api/geocode/json', [
            'key' => $apiKey,
            'address' => implode(', ', $components),
            'region' => 'br',
            'language' => 'pt-BR',
        ]);

        if ($response->failed()) {
            throw new RuntimeException('Não foi possível obter as coordenadas do endereço.');
        }

        $payload = $response->json();

        if (empty($payload['results'])) {
            throw new RuntimeException('Não existe coordenada disponível para o endereço cadastrado.');
        }

        $location = $payload['results'][0]['geometry']['location'] ?? null;

        if (! is_array($location) || ! isset($location['lat'], $location['lng'])) {
            throw new RuntimeException('A resposta do Google Maps está incompleta.');
        }

        return [
            'latitude' => (float) $location['lat'],
            'longitude' => (float) $location['lng'],
        ];
    }
}
