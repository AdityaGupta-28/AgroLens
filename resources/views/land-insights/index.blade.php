<x-app-layout>
    <x-slot name="header">
        <div>
            <h1 class="text-lg font-semibold text-slate-900 dark:text-white sm:text-xl">{{ __('Land Insights') }}</h1>
            <p class="text-sm text-slate-500 dark:text-slate-400">{{ __('Land holding, irrigation, soil, crop, and market indicators by district') }}</p>
        </div>
    </x-slot>

    @php
        $totalArea = (float) $stateSummaries->sum('total_area');
        $totalIrrigated = (float) $stateSummaries->sum('irrigated_area');
        $avgIrrigation = $totalArea > 0 ? round(($totalIrrigated / $totalArea) * 100, 1) : 0;
        $topState = $stateSummaries->keys()->first();
        $stateOptions = $stateSummaries->keys()->values();
    @endphp

    <div
        x-data="{
            query: '',
            state: '',
            matches(name, selectedState) {
                const haystack = `${name} ${selectedState}`.toLowerCase();
                const queryOk = !this.query || haystack.includes(this.query.toLowerCase());
                const stateOk = !this.state || selectedState === this.state;
                return queryOk && stateOk;
            }
        }"
        class="space-y-6"
    >
        <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
            <div class="agro-card agro-card-body">
                <p class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">States tracked</p>
                <div class="mt-3 flex items-end justify-between gap-3">
                    <p class="text-3xl font-bold text-slate-900 dark:text-white">{{ number_format($stateSummaries->count()) }}</p>
                    <span class="text-xs text-slate-500 dark:text-slate-400">{{ number_format($insights->count()) }} districts</span>
                </div>
            </div>
            <div class="agro-card agro-card-body">
                <p class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">Cultivated area</p>
                <p class="mt-3 text-3xl font-bold text-slate-900 dark:text-white">{{ number_format($totalArea, 1) }} ha</p>
                <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">Across synced operational holdings</p>
            </div>
            <div class="agro-card agro-card-body">
                <p class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">Irrigation coverage</p>
                <p class="mt-3 text-3xl font-bold text-slate-900 dark:text-white">{{ $avgIrrigation }}%</p>
                <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">{{ number_format($totalIrrigated, 1) }} ha irrigated</p>
            </div>
            <div class="agro-card agro-card-body">
                <p class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">Largest tracked state</p>
                <p class="mt-3 truncate text-2xl font-bold text-slate-900 dark:text-white">{{ $topState ?? 'N/A' }}</p>
                <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">{{ $topState ? number_format($stateSummaries[$topState]['total_area'], 1).' ha' : 'No data available' }}</p>
            </div>
        </section>

        <section class="grid gap-6 xl:grid-cols-[1fr_0.75fr]">
            <div class="agro-card agro-card-body">
                <div class="flex flex-col gap-4 md:flex-row md:items-start md:justify-between">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wide text-brand-600 dark:text-brand-400">State summary</p>
                        <h2 class="mt-1 text-base font-semibold text-slate-900 dark:text-white">Top states by cultivated land</h2>
                    </div>
                    <div class="flex flex-wrap gap-2 text-xs text-slate-500 dark:text-slate-400">
                        <span>{{ number_format($stateSummaries->sum('holdings_count')) }} holdings</span>
                        <span>{{ number_format($seasonSummary->sum('pattern_count')) }} crop patterns</span>
                    </div>
                </div>

                <div class="mt-5 overflow-hidden rounded-lg border border-slate-200 dark:border-slate-700">
                    <table class="agro-table">
                        <thead>
                            <tr>
                                <th>State</th>
                                <th>Districts</th>
                                <th>Holdings</th>
                                <th>Area</th>
                                <th>Irrigation</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200 dark:divide-slate-700">
                            @foreach ($stateSummaries->take(8) as $stateName => $summary)
                                @php
                                    $irrigation = (float) $summary['irrigation_pct'];
                                @endphp
                                <tr>
                                    <td class="font-semibold text-slate-900 dark:text-white">{{ $stateName }}</td>
                                    <td>{{ number_format($summary['district_count']) }}</td>
                                    <td>{{ number_format($summary['holdings_count']) }}</td>
                                    <td>{{ number_format($summary['total_area'], 1) }} ha</td>
                                    <td class="min-w-36">
                                        <div class="flex items-center gap-2">
                                            <div class="h-2 flex-1 overflow-hidden rounded-full bg-slate-200 dark:bg-slate-700">
                                                <div class="h-full rounded-full bg-brand-600" style="width: {{ min(100, $irrigation) }}%"></div>
                                            </div>
                                            <span class="w-12 text-right text-xs font-semibold">{{ number_format($irrigation, 1) }}%</span>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="grid gap-4">
                <div class="agro-card agro-card-body">
                    <p class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">Season footprint</p>
                    <div class="mt-4 space-y-4">
                        @foreach ($seasonSummary as $season)
                            @php
                                $seasonTotalArea = $seasonSummary->sum('total_area');
                                $share = $seasonTotalArea > 0 ? round(($season['total_area'] / $seasonTotalArea) * 100, 1) : 0;
                            @endphp
                            <div>
                                <div class="flex items-center justify-between gap-3 text-sm">
                                    <span class="font-semibold text-slate-900 dark:text-white">{{ $season['season'] }}</span>
                                    <span class="text-slate-500 dark:text-slate-400">{{ number_format($season['total_area'], 1) }} ha</span>
                                </div>
                                <div class="mt-2 h-2 overflow-hidden rounded-full bg-slate-200 dark:bg-slate-700">
                                    <div class="h-full rounded-full bg-sky-500" style="width: {{ min(100, $share) }}%"></div>
                                </div>
                                <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">{{ number_format($season['pattern_count']) }} crop patterns</p>
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="agro-card agro-card-body">
                    <p class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">Data readiness</p>
                    <div class="mt-3 space-y-3">
                        @foreach ($dataSources as $label => $description)
                            <div>
                                <p class="text-sm font-semibold capitalize text-slate-900 dark:text-white">{{ str_replace('_', ' ', $label) }}</p>
                                <p class="mt-1 text-xs leading-5 text-slate-500 dark:text-slate-400">{{ $description }}</p>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </section>

        <section class="agro-card">
            <div class="border-b border-slate-200/80 px-5 py-4 dark:border-slate-700/80 sm:px-6">
                <div class="grid gap-4 lg:grid-cols-[1fr_auto] lg:items-end">
                    <div>
                        <h2 class="text-base font-semibold text-slate-900 dark:text-white">District insight records</h2>
                        <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Search by district or filter by state to inspect operational indicators.</p>
                    </div>
                    <div class="grid gap-3 sm:grid-cols-2">
                        <div>
                            <label class="agro-label" for="land-insight-state">State</label>
                            <select id="land-insight-state" x-model="state" class="agro-select min-w-52">
                                <option value="">All States</option>
                                @foreach ($stateOptions as $stateName)
                                    <option value="{{ $stateName }}">{{ $stateName }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="agro-label" for="land-insight-search">Search</label>
                            <input id="land-insight-search" x-model.debounce.200ms="query" type="text" class="agro-input min-w-56" placeholder="District or state">
                        </div>
                    </div>
                </div>
            </div>

            <div class="grid gap-4 p-5 sm:p-6 xl:grid-cols-2">
                @forelse ($insights as $insight)
                    @php
                        $stats = $regionStats[$insight->region_id] ?? null;
                        $farmers = $farmerCounts[$insight->region_id] ?? 0;
                        $wells = $wellCounts[$insight->region_id] ?? 0;
                        $holdings = $stats->holdings_count ?? 0;
                        $areaTotal = (float) ($stats->area_total ?? 0);
                        $irrigatedArea = (float) ($stats->irrigated_area ?? 0);
                        $irrigationCoverage = $areaTotal > 0 ? round(($irrigatedArea / $areaTotal) * 100, 1) : 0;
                        $avgFarmSize = $farmers > 0 ? round($areaTotal / $farmers, 2) : 0;
                        $soilScore = $insight->nitrogen_level + $insight->phosphorus_level + $insight->potassium_level;
                        $soilHealth = $soilScore >= 500 ? 'Very fertile' : ($soilScore >= 420 ? 'Fertile' : ($soilScore >= 320 ? 'Moderate' : 'Needs improvement'));
                        $rainfallBand = $insight->avg_rainfall >= 1400 ? 'High rainfall' : ($insight->avg_rainfall >= 900 ? 'Moderate rainfall' : 'Dry or semi-arid');
                    @endphp

                    <article
                        data-name="{{ $insight->region->name }}"
                        data-state="{{ $insight->region->state }}"
                        x-show="matches($el.dataset.name, $el.dataset.state)"
                        x-transition.opacity
                        class="rounded-lg border border-slate-200 bg-white p-4 shadow-sm dark:border-slate-700 dark:bg-slate-900"
                    >
                        <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                            <div class="min-w-0">
                                <div class="flex flex-wrap items-center gap-2">
                                    <h3 class="truncate text-base font-semibold text-slate-900 dark:text-white">{{ $insight->region->name }}</h3>
                                    <x-ui.badge>{{ $insight->region->state }}</x-ui.badge>
                                </div>
                                <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">{{ $insight->region->agricultural_zone ?? 'Zone unknown' }}</p>
                            </div>
                            <div class="text-left sm:text-right">
                                <p class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">Market rate</p>
                                <p class="mt-1 text-sm font-semibold text-slate-900 dark:text-white">
                                    {{ $insight->current_market_price > 0 ? 'Rs '.number_format($insight->current_market_price).'/qtl' : 'N/A' }}
                                </p>
                            </div>
                        </div>

                        <div class="mt-4 grid grid-cols-2 gap-x-4 gap-y-3 border-y border-slate-200 py-4 text-sm dark:border-slate-700 md:grid-cols-4">
                            <div>
                                <p class="text-xs text-slate-500 dark:text-slate-400">Farmers</p>
                                <p class="mt-1 font-semibold text-slate-900 dark:text-white">{{ number_format($farmers) }}</p>
                            </div>
                            <div>
                                <p class="text-xs text-slate-500 dark:text-slate-400">Area</p>
                                <p class="mt-1 font-semibold text-slate-900 dark:text-white">{{ number_format($areaTotal, 1) }} ha</p>
                            </div>
                            <div>
                                <p class="text-xs text-slate-500 dark:text-slate-400">Irrigation</p>
                                <p class="mt-1 font-semibold text-slate-900 dark:text-white">{{ $irrigationCoverage }}%</p>
                            </div>
                            <div>
                                <p class="text-xs text-slate-500 dark:text-slate-400">Wells</p>
                                <p class="mt-1 font-semibold text-slate-900 dark:text-white">{{ number_format($wells) }}</p>
                            </div>
                        </div>

                        <div class="mt-4 grid gap-4 text-sm md:grid-cols-2">
                            <div class="space-y-2">
                                <div class="flex justify-between gap-4">
                                    <span class="text-slate-500 dark:text-slate-400">Holdings</span>
                                    <span class="font-medium text-slate-900 dark:text-white">{{ number_format($holdings) }}</span>
                                </div>
                                <div class="flex justify-between gap-4">
                                    <span class="text-slate-500 dark:text-slate-400">Avg farm size</span>
                                    <span class="font-medium text-slate-900 dark:text-white">{{ $avgFarmSize }} ha</span>
                                </div>
                                <div class="flex justify-between gap-4">
                                    <span class="text-slate-500 dark:text-slate-400">Avg well depth</span>
                                    <span class="font-medium text-slate-900 dark:text-white">{{ $insight->avg_well_depth }} ft</span>
                                </div>
                                <div class="flex justify-between gap-4">
                                    <span class="text-slate-500 dark:text-slate-400">Primary irrigation</span>
                                    <span class="text-right font-medium text-slate-900 dark:text-white">{{ $insight->primary_irrigation_source }}</span>
                                </div>
                            </div>

                            <div class="space-y-2">
                                <div class="flex justify-between gap-4">
                                    <span class="text-slate-500 dark:text-slate-400">Soil health</span>
                                    <span class="font-medium text-slate-900 dark:text-white">{{ $soilHealth }}</span>
                                </div>
                                <div class="flex justify-between gap-4">
                                    <span class="text-slate-500 dark:text-slate-400">Rainfall</span>
                                    <span class="font-medium text-slate-900 dark:text-white">{{ $rainfallBand }}</span>
                                </div>
                                <div class="flex justify-between gap-4">
                                    <span class="text-slate-500 dark:text-slate-400">Cropping style</span>
                                    <span class="text-right font-medium text-slate-900 dark:text-white">{{ $insight->cropping_pattern_type }}</span>
                                </div>
                                <div class="flex justify-between gap-4">
                                    <span class="text-slate-500 dark:text-slate-400">Soil profile</span>
                                    <span class="text-right font-medium text-slate-900 dark:text-white">pH {{ $insight->soil_ph }} | N {{ $insight->nitrogen_level }} | P {{ $insight->phosphorus_level }} | K {{ $insight->potassium_level }}</span>
                                </div>
                            </div>
                        </div>

                        <div class="mt-4 flex flex-wrap gap-2">
                            @foreach ($insight->major_crops as $crop)
                                <x-ui.badge variant="info">{{ $crop }}</x-ui.badge>
                            @endforeach
                        </div>
                    </article>
                @empty
                    <div class="xl:col-span-2">
                        <x-ui.empty-state
                            title="No regional insights yet"
                            description="Run: php artisan agrolens:sync-land-insights after seeding agricultural data."
                        />
                    </div>
                @endforelse
            </div>
        </section>
    </div>
</x-app-layout>
