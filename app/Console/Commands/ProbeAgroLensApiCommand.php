<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class ProbeAgroLensApiCommand extends Command
{
    protected $signature = 'agrolens:probe-api';

    protected $description = 'Probe data.gov.in resources to find one that returns records';

    public function handle(): int
    {
        $key = config('agrolens.api_key');
        $resources = [
            'ead44f5f-6471-48ec-a488-4b5894302aaa' => 'APY crop (current default)',
            '9ef84268-d588-465a-a308-a864a43d0070' => 'Commodity / mandi prices',
            '9ef84268-d588-465a-a308-a864a144a585' => 'Commodity alt id',
        ];

        foreach ($resources as $id => $label) {
            $response = Http::timeout(30)->get("https://api.data.gov.in/resource/{$id}", [
                'api-key' => $key,
                'format' => 'json',
                'limit' => 2,
            ]);

            $json = $response->json();
            $count = $json['count'] ?? 0;
            $status = $json['status'] ?? 'unknown';
            $keys = array_keys($json['records'][0] ?? []);

            $this->line("{$label}");
            $this->line("  HTTP: {$response->status()} | API status: {$status} | count: {$count}");
            if ($keys !== []) {
                $this->line('  Fields: '.implode(', ', $keys));
            }
            $this->newLine();
        }

        $workingId = '9ef84268-d588-465a-a308-a864a43d0070';
        $punjab = Http::timeout(30)->get("https://api.data.gov.in/resource/{$workingId}", [
            'api-key' => $key,
            'format' => 'json',
            'limit' => 100,
            'filters[state]' => 'Punjab',
        ]);
        $this->info('Punjab sample from working commodity API:');
        $this->line('  count: '.($punjab->json('count') ?? 0));

        return self::SUCCESS;
    }
}
