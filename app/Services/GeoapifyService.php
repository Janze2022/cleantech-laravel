<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class GeoapifyService
{
    public function configured(): bool
    {
        return $this->apiKey() !== '';
    }

    public function autocomplete(string $text, int $limit = 6, array $extra = []): array
    {
        if (!$this->configured() || trim($text) === '') {
            return [];
        }

        $response = $this->request('/geocode/autocomplete', array_merge([
            'text' => $text,
            'limit' => $limit,
            'format' => 'json',
        ], $extra));

        return collect($response['results'] ?? [])
            ->map(function ($result) {
                return [
                    'formatted' => $result['formatted'] ?? null,
                    'latitude' => $result['lat'] ?? null,
                    'longitude' => $result['lon'] ?? null,
                    'city' => $result['city'] ?? null,
                    'state' => $result['state'] ?? null,
                    'county' => $result['county'] ?? null,
                    'suburb' => $result['suburb'] ?? null,
                    'district' => $result['district'] ?? null,
                    'neighbourhood' => $result['neighbourhood'] ?? null,
                    'quarter' => $result['quarter'] ?? null,
                    'village' => $result['village'] ?? null,
                    'hamlet' => $result['hamlet'] ?? null,
                    'country' => $result['country'] ?? null,
                    'postcode' => $result['postcode'] ?? null,
                    'street' => $result['street'] ?? null,
                    'housenumber' => $result['housenumber'] ?? null,
                    'result_type' => $result['result_type'] ?? null,
                ];
            })
            ->filter(fn ($result) => isset($result['latitude'], $result['longitude']))
            ->values()
            ->all();
    }

    public function reverseGeocode(float $latitude, float $longitude): ?array
    {
        if (!$this->configured()) {
            return null;
        }

        $response = $this->request('/geocode/reverse', [
            'lat' => $latitude,
            'lon' => $longitude,
            'format' => 'json',
        ]);

        $result = collect($response['results'] ?? [])->first();

        if (!$result) {
            return null;
        }

        return [
            'formatted' => $result['formatted'] ?? null,
            'latitude' => $result['lat'] ?? $latitude,
            'longitude' => $result['lon'] ?? $longitude,
            'city' => $result['city'] ?? null,
            'state' => $result['state'] ?? null,
            'county' => $result['county'] ?? null,
            'country' => $result['country'] ?? null,
            'postcode' => $result['postcode'] ?? null,
            'street' => $result['street'] ?? null,
            'housenumber' => $result['housenumber'] ?? null,
            'suburb' => $result['suburb'] ?? null,
            'district' => $result['district'] ?? null,
        ];
    }

    public function route(float $fromLatitude, float $fromLongitude, float $toLatitude, float $toLongitude, string $mode = 'drive'): ?array
    {
        if (!$this->configured()) {
            return null;
        }

        $response = $this->request('/routing', [
            // Geoapify routing expects waypoint pairs in longitude,latitude order.
            'waypoints' => sprintf('%s,%s|%s,%s', $fromLongitude, $fromLatitude, $toLongitude, $toLatitude),
            'mode' => $mode,
            'details' => 'route_details',
            'format' => 'json',
        ]);

        $feature = collect($response['features'] ?? [])->first();

        if (!$feature) {
            return null;
        }

        return [
            'geometry' => $feature['geometry'] ?? null,
            'properties' => $feature['properties'] ?? [],
        ];
    }

    protected function request(string $path, array $query = []): array
    {
        $response = Http::timeout(12)
            ->retry(1, 200)
            ->acceptJson()
            ->get(rtrim($this->baseUrl(), '/') . $path, array_merge($query, [
                'apiKey' => $this->apiKey(),
            ]))
            ->throw();

        return $response->json() ?: [];
    }

    protected function apiKey(): string
    {
        return trim((string) config('services.geoapify.key'));
    }

    protected function baseUrl(): string
    {
        return trim((string) config('services.geoapify.base_url', 'https://api.geoapify.com/v1'));
    }
}
