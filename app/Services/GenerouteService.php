<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class GenerouteService
{
    public function configured(): bool
    {
        return $this->apiKey() !== '';
    }

    public function route(float $fromLatitude, float $fromLongitude, float $toLatitude, float $toLongitude): ?array
    {
        if (!$this->configured()) {
            return null;
        }

        $response = Http::timeout(15)
            ->retry(1, 200)
            ->acceptJson()
            ->withToken($this->apiKey())
            ->post(rtrim($this->baseUrl(), '/') . '/v1/trip', [
                'region' => $this->region(),
                'locations' => [
                    [
                        'coordinates' => [$fromLongitude, $fromLatitude],
                        'title' => 'Provider current location',
                        'data' => ['id' => 1],
                    ],
                    [
                        'coordinates' => [$toLongitude, $toLatitude],
                        'title' => 'Customer pinned location',
                        'data' => ['id' => 2],
                    ],
                ],
                'return_to_start' => false,
                'start_location' => 'any',
                'end_location' => 'any',
            ])
            ->throw()
            ->json();

        $trip = collect($response['trips'] ?? [])->first();

        if (!$trip || empty($trip['geometry'])) {
            return null;
        }

        return [
            'geometry' => $trip['geometry'],
            'properties' => [
                'source' => 'generoute',
                'trip_index' => $trip['trip_index'] ?? 0,
                'total_distance' => $trip['total_distance'] ?? null,
                'total_duration' => $trip['total_duration'] ?? null,
            ],
        ];
    }

    protected function apiKey(): string
    {
        return trim((string) config('services.generoute.key'));
    }

    protected function baseUrl(): string
    {
        return trim((string) config('services.generoute.base_url', 'https://api.generoute.io'));
    }

    protected function region(): string
    {
        return trim((string) config('services.generoute.region', 'ph'));
    }
}
