<div class="space-y-6" wire:poll.visible.120s="pollData">
    <x-ui.card title="Filters" description="Refine land holding, irrigation, and crop analytics by region, season, and year.">
        <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-6">
            <div>
                <label class="agro-label" for="filter-state">State</label>
                <select wire:model.live="state" id="filter-state" class="agro-select">
                    <option value="">All States</option>
                    @foreach ($states as $s)
                        <option value="{{ $s }}">{{ $s }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="agro-label" for="filter-district">District</label>
                <select wire:model.live="regionId" id="filter-district" class="agro-select">
                    <option value="">All Districts</option>
                    @foreach ($districts as $district)
                        <option value="{{ $district->id }}">{{ $district->name }} ({{ $district->state }})</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="agro-label" for="filter-season">Season</label>
                <select wire:model.live="season" id="filter-season" class="agro-select">
                    <option value="">All Seasons</option>
                    @foreach ($seasons as $s)
                        <option value="{{ $s }}">{{ ucfirst($s) }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="agro-label" for="filter-year">Year</label>
                <input type="number" wire:model.live="year" id="filter-year" class="agro-input" min="2018" max="2030" />
            </div>
            <div>
                <label class="agro-label" for="filter-irrigation">Irrigation Source</label>
                <select wire:model.live="irrigationSource" id="filter-irrigation" class="agro-select">
                    <option value="">All Sources</option>
                    @foreach ($irrigationSources as $source)
                        <option value="{{ $source->value }}">{{ $source->label() }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex items-end">
                <button wire:click="refresh" type="button" class="agro-btn-primary w-full">
                    <span wire:loading.remove wire:target="refresh,pollData">Refresh Data</span>
                    <span wire:loading wire:target="refresh,pollData">Updating…</span>
                </button>
            </div>
        </div>
    </x-ui.card>

    <div x-data="{ showForm: false }" class="space-y-4">
        <div class="flex justify-end">
            <button @click="showForm = !showForm" type="button" class="agro-btn-secondary flex items-center gap-2">
                <span x-show="!showForm" class="flex items-center gap-1.5">
                    <span>➕</span> Add Record Details
                </span>
                <span x-show="showForm" class="flex items-center gap-1.5" x-cloak>
                    <span>➖</span> Hide Form
                </span>
            </button>
        </div>

        <div x-show="showForm" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 transform scale-95" x-transition:enter-end="opacity-100 transform scale-100" x-transition:leave="transition ease-in duration-75" x-transition:leave-start="opacity-100 transform scale-100" x-transition:leave-end="opacity-0 transform scale-95" class="origin-top" x-cloak>
            <x-ui.card title="Quick Add Agricultural Details" description="Manually register a new agricultural record. The submitted details are processed and immediately reflected in all dashboard charts and KPI statistics.">
                <form wire:submit.prevent="saveRecord" class="space-y-4">
                    @if ($formSuccess)
                        <div class="mb-4">
                            <x-ui.alert type="success">{{ $formSuccess }}</x-ui.alert>
                        </div>
                    @endif

                    <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
                        <div>
                            <label class="agro-label" for="add-farmer">Farmer Name</label>
                            <input type="text" wire:model="farmerName" id="add-farmer" class="agro-input" placeholder="e.g. Anand Gowda" required />
                            <x-input-error :messages="$errors->get('farmerName')" class="mt-1" />
                        </div>
                        <div>
                            <label class="agro-label" for="add-state">State</label>
                            <select wire:model.live="formState" id="add-state" class="agro-select" required>
                                <option value="">-- Select State --</option>
                                @foreach ($states as $s)
                                    <option value="{{ $s }}">{{ $s }}</option>
                                @endforeach
                            </select>
                            <x-input-error :messages="$errors->get('formState')" class="mt-1" />
                        </div>
                        <div>
                            <label class="agro-label" for="add-district">District</label>
                            <select wire:model="formRegionId" id="add-district" class="agro-select" required {{ !$formState ? 'disabled' : '' }}>
                                <option value="">-- Select District --</option>
                                @foreach ($formDistricts as $dist)
                                    <option value="{{ $dist->id }}">{{ $dist->name }}</option>
                                @endforeach
                            </select>
                            <x-input-error :messages="$errors->get('formRegionId')" class="mt-1" />
                        </div>
                    </div>

                    <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
                        <div>
                            <label class="agro-label" for="add-crop">Crop Sown</label>
                            <select wire:model="cropId" id="add-crop" class="agro-select" required>
                                <option value="">-- Select Crop --</option>
                                @foreach ($crops as $c)
                                    <option value="{{ $c->id }}">{{ $c->name }}</option>
                                @endforeach
                            </select>
                            <x-input-error :messages="$errors->get('cropId')" class="mt-1" />
                        </div>
                        <div>
                            <label class="agro-label" for="add-season">Sowing Season</label>
                            <select wire:model="formSeason" id="add-season" class="agro-select" required>
                                <option value="kharif">Kharif</option>
                                <option value="rabi">Rabi</option>
                                <option value="zaid">Zaid</option>
                            </select>
                            <x-input-error :messages="$errors->get('formSeason')" class="mt-1" />
                        </div>
                        <div>
                            <label class="agro-label" for="add-area">Cultivated Area (Hectares)</label>
                            <input type="number" step="0.01" wire:model="area" id="add-area" class="agro-input" min="0.01" required />
                            <x-input-error :messages="$errors->get('area')" class="mt-1" />
                        </div>
                    </div>

                    <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
                        <div>
                            <label class="agro-label" for="add-irrigation">Irrigation Source</label>
                            <select wire:model="irrigation" id="add-irrigation" class="agro-select" required>
                                <option value="bore_well">Borewell</option>
                                <option value="canal">Canal Network</option>
                                <option value="dug_well">Open Dugwell</option>
                                <option value="rain_fed">Rainfed (Monsoon)</option>
                                <option value="river">River / Stream</option>
                                <option value="tank_pond">Tank / Pond</option>
                            </select>
                            <x-input-error :messages="$errors->get('irrigation')" class="mt-1" />
                        </div>
                        <div>
                            <label class="agro-label" for="add-well-depth">Well Depth (Feet)</label>
                            <input type="number" wire:model="wellDepth" id="add-well-depth" class="agro-input" min="1" required />
                            <x-input-error :messages="$errors->get('wellDepth')" class="mt-1" />
                        </div>
                        <div>
                            <label class="agro-label" for="add-water-table">Water Table Level (Meters)</label>
                            <input type="number" step="0.1" wire:model="waterTable" id="add-water-table" class="agro-input" min="0" required />
                            <x-input-error :messages="$errors->get('waterTable')" class="mt-1" />
                        </div>
                    </div>

                    <div class="flex justify-end pt-2">
                        <button type="submit" class="agro-btn-primary px-6">
                            Submit Record
                        </button>
                    </div>
                </form>
            </x-ui.card>
        </div>
    </div>

    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-6" wire:loading.class="opacity-60">
        @php
            $kpiCards = [
                ['label' => 'Total Farmers', 'value' => number_format($kpis['total_farmers'] ?? 0), 'icon' => '👨‍🌾'],
                ['label' => 'Cultivated Land (Ha)', 'value' => number_format($kpis['total_cultivated_land'] ?? 0, 1), 'icon' => '🌾'],
                ['label' => 'Irrigated Land (Ha)', 'value' => number_format($kpis['irrigated_land'] ?? 0, 1), 'icon' => '💧'],
                ['label' => 'Irrigation Coverage', 'value' => ($kpis['irrigation_ratio'] ?? 0).'%', 'icon' => '📊'],
                ['label' => 'Avg Well Depth (ft)', 'value' => $kpis['avg_well_depth'] ?? '—', 'icon' => '🕳️'],
                ['label' => 'Crop Diversity', 'value' => $kpis['crop_diversity_index'] ?? 0, 'icon' => '🌱'],
            ];
        @endphp
        @foreach ($kpiCards as $card)
            <x-ui.stat-card :label="$card['label']" :value="$card['value']" :icon="$card['icon']" />
        @endforeach
    </div>

    <div class="grid gap-6 lg:grid-cols-2" id="agrolens-charts">
        <x-ui.card title="Land Holding Distribution" description="Parcels by category (ha)" :padding="true">
            <div class="relative h-72 w-full min-h-[288px]">
                <canvas id="holdingChart"></canvas>
            </div>
        </x-ui.card>
        <x-ui.card title="Irrigation Sources" description="Active irrigation records" :padding="true">
            <div class="relative h-72 w-full min-h-[288px]">
                <canvas id="irrigationChart"></canvas>
            </div>
        </x-ui.card>
        <x-ui.card title="Cropping Patterns" description="Cultivated area (ha) by crop" :padding="true">
            <div class="relative h-72 w-full min-h-[288px]">
                <canvas id="cropChart"></canvas>
            </div>
        </x-ui.card>
        <x-ui.card title="Average Well Depth by District" description="Groundwater depth (ft)" :padding="true">
            <div class="relative h-72 w-full min-h-[288px]">
                <canvas id="wellChart"></canvas>
            </div>
        </x-ui.card>
    </div>

    @if (count($cropDistribution) > 0)
        <x-ui.card title="Crop Summary" :padding="true">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-slate-200 text-left dark:border-slate-700">
                            <th class="pb-2 font-medium text-slate-600 dark:text-slate-400">Crop</th>
                            <th class="pb-2 font-medium text-slate-600 dark:text-slate-400">Area (Ha)</th>
                            <th class="pb-2 font-medium text-slate-600 dark:text-slate-400">Share</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($cropDistribution as $row)
                            <tr class="border-b border-slate-100 dark:border-slate-800">
                                <td class="py-2">
                                    <span class="mr-2 inline-block h-3 w-3 rounded-full" style="background-color: {{ $row['color'] }}"></span>
                                    {{ $row['crop'] }}
                                </td>
                                <td class="py-2">{{ number_format($row['area'], 2) }}</td>
                                <td class="py-2">{{ $row['percentage'] }}%</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </x-ui.card>
    @endif
</div>

@script
<script>
    window.agrolensLastChartPayload = @js($chartPayload);

    if (window.AgroLensCharts) {
        window.AgroLensCharts.render(window.agrolensLastChartPayload);
    }
</script>
@endscript
