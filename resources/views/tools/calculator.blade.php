<x-app-layout>
    <x-slot name="header">
        <div>
            <h1 class="text-lg font-semibold text-slate-900 dark:text-white sm:text-xl">{{ __('Planning Tools') }}</h1>
            <p class="text-sm text-slate-500 dark:text-slate-400">{{ __('Estimate seed, fertilizer, irrigation period, and soil suitability from crop reference data') }}</p>
        </div>
    </x-slot>

    <script>
        window.plannerTool = function ({ crops }) {
            const seedRates = {
                'Wheat': 100,
                'Rice (Paddy)': 25,
                'Maize': 20,
                'Cotton': 15,
                'Sugarcane': 3500,
                'Soybean': 75,
                'Groundnut': 120,
                'Mustard': 5,
                'Potato': 2000,
                'Onion': 8,
            };

            return {
                crops,
                cropName: crops[0]?.name ?? '',
                area: 1,
                soilPh: 6.5,
                efficiency: 70,
                fertility: 'medium',
                result: {
                    seedKg: '0.0',
                    ureaKg: '0.0',
                    dapKg: '0.0',
                    waterDays: '0',
                    waterFactor: '1.00',
                    phOk: true,
                    phMessage: 'Within preferred range',
                },
                get selectedCrop() {
                    return this.crops.find((crop) => crop.name === this.cropName) ?? this.crops[0] ?? null;
                },
                calculate() {
                    const crop = this.selectedCrop;
                    if (!crop) return;

                    const area = Math.max(Number(this.area) || 0, 0);
                    const fertilityFactor = this.fertility === 'low' ? 1.18 : (this.fertility === 'high' ? 0.88 : 1);
                    const waterFactor = Math.max(0.75, Math.min(1.35, 70 / (Number(this.efficiency) || 70)));
                    const baseSeedRate = seedRates[crop.name] ?? 50;
                    const pH = Number(this.soilPh) || 0;
                    const phOk = pH >= Number(crop.optimal_ph_min) && pH <= Number(crop.optimal_ph_max);

                    this.result = {
                        seedKg: (baseSeedRate * area).toFixed(1),
                        ureaKg: (120 * area * fertilityFactor).toFixed(1),
                        dapKg: (60 * area * fertilityFactor).toFixed(1),
                        waterDays: Math.round(Number(crop.water_requirement_days || 0) * waterFactor).toString(),
                        waterFactor: waterFactor.toFixed(2),
                        phOk,
                        phMessage: phOk ? 'Within preferred range' : 'Needs soil amendment review',
                    };
                },
            };
        };
    </script>

    <div
        x-data="plannerTool({ crops: @js($crops->values()) })"
        x-init="calculate()"
        class="grid gap-6 lg:grid-cols-[0.9fr_1.1fr]"
    >
        <x-ui.card title="Resource Planner" description="Use district field values for final recommendations; this calculator gives a planning estimate.">
            <form class="grid gap-4" @submit.prevent="calculate">
                <div>
                    <label class="agro-label" for="crop">Crop</label>
                    <select id="crop" x-model="cropName" class="agro-select" @change="calculate">
                        <template x-for="crop in crops" :key="crop.id">
                            <option :value="crop.name" x-text="`${crop.name} (${crop.season})`"></option>
                        </template>
                    </select>
                </div>

                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <label class="agro-label" for="area">Land holding (ha)</label>
                        <input id="area" type="number" min="0.05" step="0.05" x-model.number="area" class="agro-input" @input.debounce.250ms="calculate">
                    </div>
                    <div>
                        <label class="agro-label" for="soil-ph">Soil pH</label>
                        <input id="soil-ph" type="number" min="3.5" max="10" step="0.1" x-model.number="soilPh" class="agro-input" @input.debounce.250ms="calculate">
                    </div>
                </div>

                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <label class="agro-label" for="irrigation-efficiency">Irrigation efficiency (%)</label>
                        <input id="irrigation-efficiency" type="number" min="30" max="95" step="1" x-model.number="efficiency" class="agro-input" @input.debounce.250ms="calculate">
                    </div>
                    <div>
                        <label class="agro-label" for="fertility">Soil fertility</label>
                        <select id="fertility" x-model="fertility" class="agro-select" @change="calculate">
                            <option value="low">Low</option>
                            <option value="medium">Medium</option>
                            <option value="high">High</option>
                        </select>
                    </div>
                </div>

                <button type="submit" class="agro-btn-primary w-full sm:w-auto">Update Estimate</button>
            </form>
        </x-ui.card>

        <div class="space-y-6">
            <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
                <div class="agro-card agro-card-body">
                    <p class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">Seed requirement</p>
                    <p class="mt-2 text-2xl font-bold text-slate-900 dark:text-white"><span x-text="result.seedKg"></span> kg</p>
                </div>
                <div class="agro-card agro-card-body">
                    <p class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">Urea</p>
                    <p class="mt-2 text-2xl font-bold text-slate-900 dark:text-white"><span x-text="result.ureaKg"></span> kg</p>
                </div>
                <div class="agro-card agro-card-body">
                    <p class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">DAP</p>
                    <p class="mt-2 text-2xl font-bold text-slate-900 dark:text-white"><span x-text="result.dapKg"></span> kg</p>
                </div>
                <div class="agro-card agro-card-body">
                    <p class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">Water period</p>
                    <p class="mt-2 text-2xl font-bold text-slate-900 dark:text-white"><span x-text="result.waterDays"></span> days</p>
                </div>
            </div>

            <x-ui.card title="Crop Suitability" :padding="true">
                <div class="grid gap-4 md:grid-cols-3">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">Crop profile</p>
                        <p class="mt-2 text-base font-semibold text-slate-900 dark:text-white" x-text="selectedCrop?.scientific_name ?? 'Reference crop'"></p>
                        <p class="mt-1 text-sm text-slate-500 dark:text-slate-400" x-text="`${selectedCrop?.type ?? ''} | ${selectedCrop?.season ?? ''}`"></p>
                    </div>
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">Optimal pH</p>
                        <p class="mt-2 text-base font-semibold text-slate-900 dark:text-white" x-text="`${selectedCrop?.optimal_ph_min ?? '-'} - ${selectedCrop?.optimal_ph_max ?? '-'}`"></p>
                        <p class="mt-1 text-sm" :class="result.phOk ? 'text-emerald-600 dark:text-emerald-400' : 'text-amber-600 dark:text-amber-400'" x-text="result.phMessage"></p>
                    </div>
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">Irrigation adjustment</p>
                        <p class="mt-2 text-base font-semibold text-slate-900 dark:text-white"><span x-text="result.waterFactor"></span>x baseline</p>
                        <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Adjusted for field efficiency.</p>
                    </div>
                </div>
            </x-ui.card>

            <x-ui.alert type="info">
                Planning estimates use crop reference values stored in MySQL. For production advisories, calibrate per district with soil tests, local package-of-practices, and current extension guidance.
            </x-ui.alert>
        </div>
    </div>

</x-app-layout>
