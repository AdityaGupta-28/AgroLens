<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Optional external API (data.gov.in) — not used by dashboard charts
    |--------------------------------------------------------------------------
    |
    | Land Insights analytics are driven from farmers, holdings, irrigation,
    | and crop pattern records in the database. API settings remain for
    | optional artisan diagnostics (agrolens:test-api).
    |
    */
    'api_key' => env('AGROLENS_API_KEY'),

    'api_base_url' => env('AGROLENS_API_BASE_URL', 'https://api.data.gov.in'),

    'api_resource_id' => env('AGROLENS_API_RESOURCE_ID'),

    'fallback_resource_ids' => [],

    'api_limit' => (int) env('AGROLENS_API_LIMIT', 100),

    'connect_timeout' => (int) env('AGROLENS_API_CONNECT_TIMEOUT', 10),

    'timeout' => (int) env('AGROLENS_API_TIMEOUT', 20),

    'retry_times' => (int) env('AGROLENS_API_RETRY_TIMES', 1),

    'retry_sleep_ms' => (int) env('AGROLENS_API_RETRY_SLEEP_MS', 500),

    'circuit_breaker_minutes' => (int) env('AGROLENS_API_CIRCUIT_MINUTES', 5),

    'cache_ttl' => (int) env('AGROLENS_API_CACHE_TTL', 300),

    'enabled' => env('AGROLENS_API_ENABLED', false),

    'fetch_on_poll' => env('AGROLENS_API_FETCH_ON_POLL', false),

    'data_sources' => [
        'districts' => 'District coordinates and crop-region mapping are curated from public Government of India and state agriculture references for demo seeding.',
        'operations' => 'Dashboard metrics are calculated from MySQL farmers, holdings, wells, irrigation records, crop patterns, and surveys.',
        'privacy' => 'Seeder farmer names and phone numbers are synthetic so demo environments do not expose personal data.',
    ],

];
