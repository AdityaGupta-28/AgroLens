<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <a href="{{ route('surveys.index') }}" class="text-slate-500 hover:text-slate-700 dark:text-slate-400 dark:hover:text-slate-300">
                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
            </a>
            <div>
                <h1 class="text-lg font-semibold text-slate-900 dark:text-white sm:text-xl">{{ __('Collect Data') }}</h1>
                <p class="text-sm text-slate-500 dark:text-slate-400">{{ $survey->title }} — Campaign code: <span class="font-mono font-semibold">{{ $survey->code }}</span></p>
            </div>
        </div>
    </x-slot>

    <div class="max-w-3xl mx-auto space-y-6">
        @if ($survey->description)
            <div class="rounded-xl border border-blue-200/60 bg-blue-50/50 p-4 dark:border-blue-900/40 dark:bg-blue-950/20">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <span class="text-lg">ℹ️</span>
                    </div>
                    <div class="ml-3">
                        <h3 class="text-sm font-semibold text-blue-800 dark:text-blue-300">Enumerator Campaign Instructions</h3>
                        <div class="mt-1 text-sm text-blue-700 dark:text-blue-400">
                            <p>{{ $survey->description }}</p>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        <x-ui.card title="Survey Enumeration Form" description="Ensure all details entered match active field inspections.">
            <form method="POST" action="{{ route('surveys.submit', $survey) }}" class="space-y-6">
                @csrf

                <!-- Core Metadata: Farmer and Region Linkages -->
                <div class="grid gap-6 md:grid-cols-3">
                    <div>
                        <label class="agro-label" for="farmer_name">Farmer Name</label>
                        <input name="farmer_name" id="farmer_name" type="text" class="agro-input" value="{{ old('farmer_name') }}" placeholder="e.g. Rajesh Kumar" required />
                        <x-input-error :messages="$errors->get('farmer_name')" class="mt-2" />
                    </div>

                    <div>
                        <label class="agro-label" for="state_select">State</label>
                        <select id="state_select" class="agro-select" required>
                            <option value="">-- Select State --</option>
                            @foreach ($regions->pluck('state')->unique()->sort() as $state)
                                <option value="{{ $state }}">{{ $state }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="agro-label" for="region_id">Reporting District</label>
                        <select name="region_id" id="region_id" class="agro-select" required>
                            <option value="">-- Select District --</option>
                            @foreach ($regions as $region)
                                <option value="{{ $region->id }}" data-state="{{ $region->state }}" {{ old('region_id') == $region->id ? 'selected' : '' }}>
                                    {{ $region->name }}
                                </option>
                            @endforeach
                        </select>
                        <x-input-error :messages="$errors->get('region_id')" class="mt-2" />
                    </div>
                </div>

                <hr class="border-slate-200 dark:border-slate-700" />

                <!-- Dynamic Schema Inputs -->
                <div class="grid gap-6 md:grid-cols-2">
                    @foreach ($survey->schema['fields'] ?? [] as $field)
                        @if ($field === 'crop_id')
                            <div>
                                <label class="agro-label" for="crop_id">Cultivated Crop</label>
                                <select name="responses[crop_id]" id="crop_id" class="agro-select" required>
                                    <option value="">-- Choose Crop --</option>
                                    @foreach ($crops as $crop)
                                        <option value="{{ $crop->id }}" {{ old('responses.crop_id') == $crop->id ? 'selected' : '' }}>
                                            {{ $crop->name }}
                                        </option>
                                    @endforeach
                                </select>
                                <x-input-error :messages="$errors->get('responses.crop_id')" class="mt-2" />
                            </div>
                        @endif

                        @if ($field === 'area_hectares')
                            <div>
                                <label class="agro-label" for="area_hectares">Cultivated Area (Hectares)</label>
                                <input name="responses[area_hectares]" id="area_hectares" type="number" step="0.01" min="0.01" class="agro-input" value="{{ old('responses.area_hectares') }}" placeholder="e.g. 2.45" required />
                                <x-input-error :messages="$errors->get('responses.area_hectares')" class="mt-2" />
                            </div>
                        @endif

                        @if ($field === 'season')
                            <div>
                                <label class="agro-label" for="season">Sowing Season</label>
                                <select name="responses[season]" id="season" class="agro-select" required>
                                    <option value="">-- Select Season --</option>
                                    <option value="Kharif" {{ old('responses.season') === 'Kharif' ? 'selected' : '' }}>Kharif</option>
                                    <option value="Rabi" {{ old('responses.season') === 'Rabi' ? 'selected' : '' }}>Rabi</option>
                                    <option value="Zaid" {{ old('responses.season') === 'Zaid' ? 'selected' : '' }}>Zaid</option>
                                </select>
                                <x-input-error :messages="$errors->get('responses.season')" class="mt-2" />
                            </div>
                        @endif

                        @if ($field === 'irrigation_source')
                            <div>
                                <label class="agro-label" for="irrigation_source">Irrigation Source</label>
                                <select name="responses[irrigation_source]" id="irrigation_source" class="agro-select" required>
                                    <option value="">-- Select Water Source --</option>
                                    <option value="bore_well" {{ old('responses.irrigation_source') === 'bore_well' ? 'selected' : '' }}>Borewell</option>
                                    <option value="canal" {{ old('responses.irrigation_source') === 'canal' ? 'selected' : '' }}>Canal Network</option>
                                    <option value="dug_well" {{ old('responses.irrigation_source') === 'dug_well' ? 'selected' : '' }}>Open Dugwell</option>
                                    <option value="rain_fed" {{ old('responses.irrigation_source') === 'rain_fed' ? 'selected' : '' }}>Rainfed (Monsoon)</option>
                                    <option value="river" {{ old('responses.irrigation_source') === 'river' ? 'selected' : '' }}>River / Stream</option>
                                    <option value="tank_pond" {{ old('responses.irrigation_source') === 'tank_pond' ? 'selected' : '' }}>Tank / Pond</option>
                                </select>
                                <x-input-error :messages="$errors->get('responses.irrigation_source')" class="mt-2" />
                            </div>
                        @endif

                        @if ($field === 'well_type')
                            <div>
                                <label class="agro-label" for="well_type">Groundwater Well Category</label>
                                <select name="responses[well_type]" id="well_type" class="agro-select" required>
                                    <option value="">-- Choose Well Type --</option>
                                    <option value="bore_well" {{ old('responses.well_type') === 'bore_well' ? 'selected' : '' }}>Borewell (Deep)</option>
                                    <option value="tube_well" {{ old('responses.well_type') === 'tube_well' ? 'selected' : '' }}>Tubewell</option>
                                    <option value="dug_well" {{ old('responses.well_type') === 'dug_well' ? 'selected' : '' }}>Dug Well (Open)</option>
                                </select>
                                <x-input-error :messages="$errors->get('responses.well_type')" class="mt-2" />
                            </div>
                        @endif

                        @if ($field === 'depth_feet')
                            <div>
                                <label class="agro-label" for="depth_feet">Well Depth (Feet)</label>
                                <input name="responses[depth_feet]" id="depth_feet" type="number" min="1" class="agro-input" value="{{ old('responses.depth_feet') }}" placeholder="e.g. 350" required />
                                <x-input-error :messages="$errors->get('responses.depth_feet')" class="mt-2" />
                            </div>
                        @endif

                        @if ($field === 'water_table_level_m')
                            <div>
                                <label class="agro-label" for="water_table_level_m">Water Table depth (Meters)</label>
                                <input name="responses[water_table_level_m]" id="water_table_level_m" type="number" step="0.1" min="0" class="agro-input" value="{{ old('responses.water_table_level_m') }}" placeholder="e.g. 24.5" required />
                                <x-input-error :messages="$errors->get('responses.water_table_level_m')" class="mt-2" />
                            </div>
                        @endif

                        @if ($field === 'recharge_status')
                            <div>
                                <label class="agro-label" for="recharge_status">Groundwater Recharge Status</label>
                                <select name="responses[recharge_status]" id="recharge_status" class="agro-select" required>
                                    <option value="">-- Choose Recharge Level --</option>
                                    <option value="good" {{ old('responses.recharge_status') === 'good' ? 'selected' : '' }}>Good (Steady flow)</option>
                                    <option value="moderate" {{ old('responses.recharge_status') === 'moderate' ? 'selected' : '' }}>Moderate</option>
                                    <option value="poor" {{ old('responses.recharge_status') === 'poor' ? 'selected' : '' }}>Poor / Critical stress</option>
                                </select>
                                <x-input-error :messages="$errors->get('responses.recharge_status')" class="mt-2" />
                            </div>
                        @endif
                    @endforeach

                    <!-- Standard Spatial Location Metadata -->
                    <div class="md:col-span-2 space-y-4">
                        <div class="flex items-center justify-between">
                            <label class="agro-label mb-0">GPS Coordinates (Geo-Tracking)</label>
                            <button type="button" id="get-location-btn" class="inline-flex items-center gap-1.5 text-xs font-semibold text-brand-600 hover:text-brand-700 dark:text-brand-400 cursor-pointer">
                                📡 Fetch Current GPS Location
                            </button>
                        </div>
                        
                        <div class="grid gap-4 sm:grid-cols-2">
                            <div>
                                <input name="latitude" id="latitude" type="number" step="0.0000001" min="-90" max="90" class="agro-input" value="{{ old('latitude') }}" placeholder="Latitude (e.g. 31.6342)" />
                                <span class="text-[10px] text-slate-400 dark:text-slate-500">Latitude (degrees decimal)</span>
                                <x-input-error :messages="$errors->get('latitude')" class="mt-1" />
                            </div>
                            <div>
                                <input name="longitude" id="longitude" type="number" step="0.0000001" min="-180" max="180" class="agro-input" value="{{ old('longitude') }}" placeholder="Longitude (e.g. 74.8723)" />
                                <span class="text-[10px] text-slate-400 dark:text-slate-500">Longitude (degrees decimal)</span>
                                <x-input-error :messages="$errors->get('longitude')" class="mt-1" />
                            </div>
                        </div>
                        <div id="location-success-msg" class="hidden text-xs font-semibold text-emerald-600 dark:text-emerald-400"></div>
                        <div id="location-error-msg" class="hidden text-xs font-semibold text-red-600 dark:text-red-400"></div>
                    </div>
                </div>

                <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-200 dark:border-slate-700">
                    <a href="{{ route('surveys.index') }}" class="agro-btn-secondary">
                        {{ __('Cancel') }}
                    </a>
                    <button type="submit" class="agro-btn-primary">
                        {{ __('Submit Response') }}
                    </button>
                </div>
            </form>
        </x-ui.card>
    </div>

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                // Dynamic State & District Filtering
                const stateSelect = document.getElementById('state_select');
                const districtSelect = document.getElementById('region_id');
                const districtOptions = Array.from(districtSelect.options).filter(opt => opt.value !== "");

                // Keep track of the initially selected value (e.g. from old input)
                const initialSelectedValue = districtSelect.value;

                // Pre-select state if a district was selected (e.g. on validation reload or edit)
                if (initialSelectedValue) {
                    const matchedOption = districtOptions.find(opt => opt.value === initialSelectedValue);
                    if (matchedOption) {
                        const matchedState = matchedOption.getAttribute('data-state');
                        stateSelect.value = matchedState;
                    }
                }

                function updateDistricts() {
                    const selectedState = stateSelect.value;
                    
                    // Clear district selection options completely
                    districtSelect.innerHTML = '';

                    if (selectedState) {
                        // Create "-- Select District --" placeholder
                        const placeholder = document.createElement('option');
                        placeholder.value = "";
                        placeholder.textContent = "-- Select District --";
                        districtSelect.appendChild(placeholder);

                        // Filter and append districts for the selected state
                        districtOptions.forEach(function (option) {
                            if (option.getAttribute('data-state') === selectedState) {
                                const clonedOption = option.cloneNode(true);
                                // Preserve old selected value if applicable
                                if (clonedOption.value === initialSelectedValue) {
                                    clonedOption.selected = true;
                                }
                                districtSelect.appendChild(clonedOption);
                            }
                        });
                        
                        districtSelect.disabled = false;
                    } else {
                        // Create "-- Select State First --" placeholder
                        const placeholder = document.createElement('option');
                        placeholder.value = "";
                        placeholder.textContent = "-- Select State First --";
                        districtSelect.appendChild(placeholder);
                        
                        districtSelect.disabled = true;
                    }
                }

                // Attach change listener
                stateSelect.addEventListener('change', updateDistricts);

                // Run once on load to establish correct initial state
                updateDistricts();

                const getLocBtn = document.getElementById('get-location-btn');
                const latInput = document.getElementById('latitude');
                const lngInput = document.getElementById('longitude');
                const errorMsg = document.getElementById('location-error-msg');
                const successMsg = document.getElementById('location-success-msg');

                getLocBtn.addEventListener('click', function () {
                    errorMsg.classList.add('hidden');
                    successMsg.classList.add('hidden');
                    getLocBtn.disabled = true;
                    getLocBtn.innerText = '📡 Connecting GPS...';

                    if (!navigator.geolocation) {
                        errorMsg.innerText = '❌ Geolocation is not supported by your browser.';
                        errorMsg.classList.remove('hidden');
                        getLocBtn.disabled = false;
                        getLocBtn.innerText = '📡 Fetch Current GPS Location';
                        return;
                    }

                    navigator.geolocation.getCurrentPosition(
                        function (position) {
                            latInput.value = position.coords.latitude.toFixed(7);
                            lngInput.value = position.coords.longitude.toFixed(7);
                            successMsg.innerText = '✅ Coordinates successfully synchronized!';
                            successMsg.classList.remove('hidden');
                            getLocBtn.disabled = false;
                            getLocBtn.innerText = '📡 Fetch Current GPS Location';
                        },
                        function (error) {
                            let text = '❌ Failed to fetch coordinates.';
                            switch(error.code) {
                                case error.PERMISSION_DENIED:
                                    text = '❌ Geolocation request was denied by the user.';
                                    break;
                                case error.POSITION_UNAVAILABLE:
                                    text = '❌ Location information is unavailable.';
                                    break;
                                case error.TIMEOUT:
                                    text = '❌ The location request timed out.';
                                    break;
                            }
                            errorMsg.innerText = text;
                            errorMsg.classList.remove('hidden');
                            getLocBtn.disabled = false;
                            getLocBtn.innerText = '📡 Fetch Current GPS Location';
                        },
                        {
                            enableHighAccuracy: true,
                            timeout: 8000,
                            maximumAge: 0
                        }
                    );
                });
            });
        </script>
    @endpush
</x-app-layout>
