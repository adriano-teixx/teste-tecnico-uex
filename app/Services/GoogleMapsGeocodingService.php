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

        foreach ($this->buildAddressVariants($address) as $components) {
            $payload = $this->callApi($components, $apiKey);
            $status = $payload['status'] ?? '';

            if ($status === 'OK' && !empty($payload['results'])) {
                $location = $payload['results'][0]['geometry']['location'] ?? null;

                if (is_array($location) && isset($location['lat'], $location['lng'])) {
                    return [
                        'latitude' => (float) $location['lat'],
                        'longitude' => (float) $location['lng'],
                    ];
                }

                throw new RuntimeException('A resposta do Google Maps está incompleta.');
            }

            if (in_array($status, ['ZERO_RESULTS', 'OK'], true)) {
                continue;
            }

            $message = match ($status) {
                'OVER_DAILY_LIMIT', 'OVER_QUERY_LIMIT' => 'Limite da API do Google Maps atingido.',
                'REQUEST_DENIED' => 'Requisição ao Google Maps negada.',
                'INVALID_REQUEST' => 'Parâmetros inválidos para o Google Maps.',
                default => 'Não foi possível obter as coordenadas do endereço.',
            };

            throw new RuntimeException($message);
        }

        throw new RuntimeException('Não existe coordenada disponível para o endereço cadastrado.');
    }

    /**
     * Build a list of address variants that progressively omit optional components.
     */
    private function buildAddressVariants(array $address): array
    {
        $street = trim((string) ($address['street'] ?? ''));
        $number = trim((string) ($address['number'] ?? ''));
        $district = trim((string) ($address['district'] ?? ''));
        $city = trim((string) ($address['city'] ?? ''));
        $state = trim((string) ($address['state'] ?? ''));

        $base = array_filter([$street, $number, $district, $city, $state, 'Brasil']);

        $variants = [implode(', ', $base)];

        if ($district) {
            $variants[] = implode(', ', array_filter([$street, $number, $city, $state, 'Brasil']));
        }

        if ($number) {
            $variants[] = implode(', ', array_filter([$street, $district, $city, $state, 'Brasil']));
        }

        $variants[] = implode(', ', array_filter([$street, $city, $state, 'Brasil']));
        $variants[] = implode(', ', array_filter([$city, $state, 'Brasil']));

        return array_unique(array_filter($variants));
    }

    private function callApi(string $address, string $apiKey): array
    {
        $response = Http::retry(2, 150)->get('https://maps.googleapis.com/maps/api/geocode/json', [
            'key' => $apiKey,
            'address' => $address,
            'region' => 'br',
            'language' => 'pt-BR',
        ]);

        if ($response->failed()) {
            throw new RuntimeException('Não foi possível obter as coordenadas do endereço.');
        }

        return $response->json();
    }
}
