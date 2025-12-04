<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use RuntimeException;

class OpenStreetMapGeocodingService
{
    /**
     * Resolve latitude and longitude using the OpenStreetMap Nominatim API.
     *
     * @param  array<string, string>  $address
     * @return array<string, float>
     */
    public function geocode(array $address): array
    {
        $query = $this->buildQuery($address);

        if ($query === '') {
            throw new RuntimeException('Não foi possível montar uma consulta válida para o OpenStreetMap.');
        }

        $results = $this->callApi($query);

        if (empty($results)) {
            throw new RuntimeException('Não foi possível obter as coordenadas do endereço.');
        }

        $first = $results[0];

        if (!isset($first['lat'], $first['lon'])) {
            throw new RuntimeException('A resposta do OpenStreetMap está incompleta.');
        }

        return [
            'latitude' => (float) $first['lat'],
            'longitude' => (float) $first['lon'],
        ];
    }

    private function buildQuery(array $address): string
    {
        $parts = array_filter([
            trim((string) ($address['street'] ?? '')),
            trim((string) ($address['number'] ?? '')),
            trim((string) ($address['district'] ?? '')),
            trim((string) ($address['city'] ?? '')),
            trim((string) ($address['state'] ?? '')),
            'Brasil',
        ]);

        return implode(', ', $parts);
    }

    private function callApi(string $query): array
    {
        $response = Http::acceptJson()
            ->retry(2, 150)
            ->timeout(5)
            ->withHeaders([
                'User-Agent' => config('app.name', 'Laravel'),
            ])
            ->get($this->getBaseUrl(), [
                'q' => $query,
                'format' => 'json',
                'addressdetails' => 1,
                'limit' => 1,
            ]);

        if ($response->failed()) {
            throw new RuntimeException('Não foi possível obter as coordenadas do endereço.');
        }

        return $response->json();
    }

    private function getBaseUrl(): string
    {
        return config('services.openstreet.base_url');
    }
}
