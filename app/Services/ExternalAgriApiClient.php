<?php

namespace App\Services;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

class ExternalAgriApiClient
{
    private const CIRCUIT_CACHE_KEY = 'agrolens.api.circuit_open';

    public function isConfigured(): bool
    {
        return config('agrolens.enabled', true)
            && filled(config('agrolens.api_key'));
    }

    public function isCircuitOpen(): bool
    {
        return Cache::has(self::CIRCUIT_CACHE_KEY);
    }

    /**
     * @param  array<string, mixed>  $filters
     * @return array{records: array<int, array<string, mixed>>, fetched_at: string, source: string, mode: string}|null
     */
    public function fetchCropRecords(array $filters = [], bool $allowNetwork = true): ?array
    {
        if (! $this->isConfigured()) {
            return null;
        }

        $cacheKey = 'agrolens.external.'.md5(json_encode($filters));

        $cached = Cache::get($cacheKey);
        if ($cached !== null) {
            return $cached;
        }

        if (! $allowNetwork || $this->isCircuitOpen()) {
            return null;
        }

        $result = $this->requestCropRecords($filters);

        if ($result !== null) {
            Cache::put($cacheKey, $result, config('agrolens.cache_ttl', 300));
            Cache::forget(self::CIRCUIT_CACHE_KEY);

            return $result;
        }

        return null;
    }

    public function clearCache(array $filters = []): void
    {
        Cache::forget('agrolens.external.'.md5(json_encode($filters)));
        Cache::forget(self::CIRCUIT_CACHE_KEY);
    }

    /**
     * @param  array<string, mixed>  $filters
     * @return array{records: array<int, array<string, mixed>>, fetched_at: string, source: string, mode: string}|null
     */
    private function requestCropRecords(array $filters): ?array
    {
        $resourceIds = array_unique(array_filter([
            config('agrolens.api_resource_id'),
            ...config('agrolens.fallback_resource_ids', []),
        ]));

        foreach ($resourceIds as $resourceId) {
            try {
                $payload = $this->requestResource($resourceId, $filters);

                if ($payload !== null) {
                    return $payload;
                }
            } catch (Throwable $e) {
                $this->openCircuit($e);
            }
        }

        return null;
    }

    /**
     * @param  array<string, mixed>  $filters
     * @return array{records: array<int, array<string, mixed>>, fetched_at: string, source: string, mode: string}|null
     */
    private function requestResource(string $resourceId, array $filters): ?array
    {
        $url = rtrim((string) config('agrolens.api_base_url'), '/')."/resource/{$resourceId}";

        $query = [
            'api-key' => config('agrolens.api_key'),
            'format' => 'json',
            'limit' => (int) config('agrolens.api_limit', 100),
            'offset' => 0,
        ];

        if (! empty($filters['state'])) {
            $query['filters[state]'] = $filters['state'];
        }

        if (! empty($filters['year'])) {
            $query['filters[year]'] = (string) $filters['year'];
        }

        if (! empty($filters['season'])) {
            $query['filters[season]'] = ucfirst((string) $filters['season']);
        }

        $response = $this->get($url, $query);

        if (! $response->successful()) {
            Log::warning('AgroLens API HTTP error', [
                'resource' => $resourceId,
                'status' => $response->status(),
            ]);

            return null;
        }

        $json = $response->json();

        if (($json['status'] ?? '') === 'error') {
            Log::warning('AgroLens API returned error', [
                'resource' => $resourceId,
                'message' => $json['message'] ?? 'unknown',
            ]);

            return null;
        }

        $records = $json['records'] ?? [];

        if ((! is_array($records) || $records === []) && (isset($query['filters[year]']) || isset($query['filters[season]']))) {
            unset($query['filters[year]'], $query['filters[season]']);
            $retryResponse = $this->get($url, $query);
            $records = $retryResponse->json('records') ?? [];
        }

        if (! is_array($records) || $records === []) {
            return null;
        }

        return [
            'records' => $records,
            'fetched_at' => now()->toIso8601String(),
            'source' => 'data.gov.in',
            'mode' => $this->detectMode($records[0]),
        ];
    }

    /**
     * @param  array<string, mixed>  $record
     */
    private function detectMode(array $record): string
    {
        if (isset($record['modal_price']) || isset($record['commodity'])) {
            return 'commodity_prices';
        }

        return 'crop_area';
    }

    /**
     * @param  array<string, mixed>  $query
     */
    private function get(string $url, array $query): Response
    {
        try {
            return Http::connectTimeout((int) config('agrolens.connect_timeout', 10))
                ->timeout((int) config('agrolens.timeout', 20))
                ->retry(
                    (int) config('agrolens.retry_times', 1),
                    (int) config('agrolens.retry_sleep_ms', 500),
                    fn (Throwable $e) => $e instanceof ConnectionException
                )
                ->get($url, $query);
        } catch (ConnectionException|RequestException $e) {
            throw $e;
        }
    }

    private function openCircuit(Throwable $e): void
    {
        $minutes = (int) config('agrolens.circuit_breaker_minutes', 5);

        Cache::put(self::CIRCUIT_CACHE_KEY, true, now()->addMinutes($minutes));

        Log::warning('AgroLens API unavailable — using database fallback', [
            'message' => $e->getMessage(),
            'retry_after_minutes' => $minutes,
        ]);
    }
}
