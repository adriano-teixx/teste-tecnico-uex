<?php

namespace App\Services;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class ViaCepService
{
    /**
     * Search for addresses using the Via CEP API.
     *
     * @return Collection<int, array<string, string|null>>
     */
    public function search(string $uf, string $city, string $street): Collection
    {
        $baseUrl = rtrim(config('services.viacep.base_url', 'https://viacep.com.br/ws'), '/');
        $url = sprintf(
            '%s/%s/%s/%s/json/',
            $baseUrl,
            rawurlencode(strtoupper($uf)),
            rawurlencode($city),
            rawurlencode($street),
        );

        $response = Http::retry(2, 100)->get($url);

        if ($response->failed()) {
            throw new RuntimeException('Não foi possível consultar os endereços no momento.');
        }

        $payload = $response->json();

        if (isset($payload['erro'])) {
            return collect();
        }

        return collect($payload)
            ->filter(fn ($item) => is_array($item) && empty($item['erro'] ?? false))
            ->map(function (array $item) {
                return [
                    'cep' => $item['cep'] ?? null,
                    'street' => $item['logradouro'] ?? null,
                    'district' => $item['bairro'] ?? null,
                    'city' => $item['localidade'] ?? null,
                    'state' => $item['uf'] ?? null,
                ];
            })
            ->values();
    }

    public function findByCep(string $cep): array
    {
        $baseUrl = rtrim(config('services.viacep.base_url', 'https://viacep.com.br/ws'), '/');
        $url = sprintf('%s/%s/json/', $baseUrl, rawurlencode($cep));

        $response = Http::retry(2, 100)->get($url);

        if ($response->failed()) {
            throw new RuntimeException('Não foi possível consultar o CEP no momento.');
        }

        $payload = $response->json();

        if (empty($payload) || isset($payload['erro'])) {
            throw new RuntimeException('CEP não encontrado.');
        }

        return [
            'cep' => $payload['cep'] ?? null,
            'street' => $payload['logradouro'] ?? null,
            'district' => $payload['bairro'] ?? null,
            'city' => $payload['localidade'] ?? null,
            'state' => $payload['uf'] ?? null,
        ];
    }
}
