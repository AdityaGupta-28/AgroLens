<?php

namespace App\Console\Commands;

use App\Services\ExternalAgriApiClient;
use App\Services\RealtimeAnalyticsService;
use Illuminate\Console\Command;

class TestAgroLensApiCommand extends Command
{
    protected $signature = 'agrolens:test-api {--state=Punjab} {--year=}';

    protected $description = 'Test the data.gov.in API connection using AGROLENS_API_KEY';

    public function handle(ExternalAgriApiClient $client, RealtimeAnalyticsService $realtime): int
    {
        if (! $client->isConfigured()) {
            $this->error('AGROLENS_API_KEY is not set in .env');

            return self::FAILURE;
        }

        $filters = array_filter([
            'state' => $this->option('state'),
            'year' => $this->option('year') ?: (int) date('Y'),
        ]);

        $this->info('Fetching crop records from data.gov.in…');

        $payload = $client->fetchCropRecords($filters);

        if ($payload === null) {
            $this->error('API request failed or returned no records. Check your key, resource ID, and filters.');
            $this->line('Resource: '.config('agrolens.api_resource_id'));

            return self::FAILURE;
        }

        $crops = ($payload['mode'] ?? '') === 'commodity_prices'
            ? $realtime->transformCommodityPriceDistribution($payload['records'])
            : $realtime->transformCropDistribution($payload['records']);

        $this->info('Success! Mode: '.$payload['mode'].', '.count($payload['records']).' records, '.count($crops).' crops parsed.');
        $valueLabel = ($payload['mode'] ?? '') === 'commodity_prices' ? 'Price (₹)' : 'Area (Ha)';
        $this->table(['Crop', $valueLabel, 'Share %'], collect($crops)->map(fn ($c) => [
            $c['crop'], $c['area'], $c['percentage'],
        ])->all());

        return self::SUCCESS;
    }
}
