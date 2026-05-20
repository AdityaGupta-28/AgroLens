<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <a href="{{ route('surveys.index') }}" class="text-slate-500 hover:text-slate-700 dark:text-slate-400 dark:hover:text-slate-300">
                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
            </a>
            <div>
                <h1 class="text-lg font-semibold text-slate-900 dark:text-white sm:text-xl">{{ __('Create Survey Campaign') }}</h1>
                <p class="text-sm text-slate-500 dark:text-slate-400">{{ __('Define a new enumerator programme and custom schema') }}</p>
            </div>
        </div>
    </x-slot>

    <div class="max-w-4xl mx-auto space-y-6">
        <x-ui.card title="Campaign Specifications" description="Define the parameters and the digital data collection schema.">
            <form method="POST" action="{{ route('surveys.store') }}" class="space-y-6">
                @csrf

                <div class="grid gap-6 md:grid-cols-2">
                    <div class="md:col-span-2">
                        <label class="agro-label" for="title">Survey Campaign Title</label>
                        <input name="title" id="title" type="text" class="agro-input" value="{{ old('title') }}" required placeholder="e.g. Rabi 2026 Soil Health Assessment" />
                        <x-input-error :messages="$errors->get('title')" class="mt-2" />
                    </div>

                    <div>
                        <label class="agro-label" for="code">Unique Campaign Code</label>
                        <input name="code" id="code" type="text" class="agro-input font-mono uppercase" value="{{ old('code') }}" required placeholder="e.g. RABI-SOIL-26" />
                        <span class="text-xs text-slate-400 dark:text-slate-500">Only letters, numbers, and dashes. Must be unique.</span>
                        <x-input-error :messages="$errors->get('code')" class="mt-2" />
                    </div>

                    <div>
                        <label class="agro-label" for="is_active">Campaign Status</label>
                        <select name="is_active" id="is_active" class="agro-select">
                            <option value="1" {{ old('is_active') === '0' ? '' : 'selected' }}>Active (Accepting Data)</option>
                            <option value="0" {{ old('is_active') === '0' ? 'selected' : '' }}>Inactive / Closed</option>
                        </select>
                        <x-input-error :messages="$errors->get('is_active')" class="mt-2" />
                    </div>

                    <div class="md:col-span-2">
                        <label class="agro-label" for="description">Campaign Description</label>
                        <textarea name="description" id="description" rows="3" class="agro-input" placeholder="Outline the purpose of this data collection drive. Mention enumerator instructions or geographical target areas.">{{ old('description') }}</textarea>
                        <x-input-error :messages="$errors->get('description')" class="mt-2" />
                    </div>

                    <div>
                        <label class="agro-label" for="starts_at">Starts At</label>
                        <input name="starts_at" id="starts_at" type="date" class="agro-input" value="{{ old('starts_at', date('Y-m-d')) }}" />
                        <x-input-error :messages="$errors->get('starts_at')" class="mt-2" />
                    </div>

                    <div>
                        <label class="agro-label" for="ends_at">Ends At</label>
                        <input name="ends_at" id="ends_at" type="date" class="agro-input" value="{{ old('ends_at', date('Y-12-31')) }}" />
                        <x-input-error :messages="$errors->get('ends_at')" class="mt-2" />
                    </div>
                </div>

                <hr class="border-slate-200 dark:border-slate-700" />

                <div>
                    <h3 class="text-md font-semibold text-slate-900 dark:text-white mb-1">Data Collection Schema Configuration</h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mb-4">Select the custom fields that will be generated inside the enumerator collection form for this campaign.</p>
                    
                    <x-input-error :messages="$errors->get('fields')" class="mb-3" />

                    <div class="grid gap-4 sm:grid-cols-2 md:grid-cols-3">
                        @foreach ($fields as $key => $label)
                            <label class="relative flex flex-col p-4 rounded-xl border border-slate-200 dark:border-slate-700 hover:border-brand-500 dark:hover:border-brand-500 bg-white dark:bg-slate-900/50 cursor-pointer select-none transition shadow-sm">
                                <div class="flex items-center justify-between mb-2">
                                    <span class="font-semibold text-sm text-slate-800 dark:text-slate-200">{{ $label }}</span>
                                    <input type="checkbox" name="fields[]" value="{{ $key }}" class="h-4 w-4 rounded border-slate-300 dark:border-slate-700 text-brand-600 focus:ring-brand-500 cursor-pointer" {{ is_array(old('fields')) && in_array($key, old('fields')) ? 'checked' : '' }} />
                                </div>
                                <span class="text-xs text-slate-500 dark:text-slate-400">
                                    @if ($key === 'crop_id')
                                        Dropdown catalog to select crop.
                                    @elseif ($key === 'area_hectares')
                                        Cultivated land sizing in hectares.
                                    @elseif ($key === 'season')
                                        Dropdown for crop seasons (Kharif, Rabi, Zaid).
                                    @elseif ($key === 'irrigation_source')
                                        Dropdown choices for irrigation method.
                                    @elseif ($key === 'gps_coordinates')
                                        Capture geo-location bounds for spatial mapping.
                                    @elseif ($key === 'well_type')
                                        Categorized wells (bore_well, tube_well, etc.).
                                    @elseif ($key === 'depth_feet')
                                        Numerical depth in feet.
                                    @elseif ($key === 'water_table_level_m')
                                        Numerical water level in meters.
                                    @elseif ($key === 'recharge_status')
                                        Rate scale (good, moderate, poor).
                                    @endif
                                </span>
                            </label>
                        @endforeach
                    </div>
                </div>

                <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-200 dark:border-slate-700">
                    <a href="{{ route('surveys.index') }}" class="agro-btn-secondary">
                        {{ __('Cancel') }}
                    </a>
                    <button type="submit" class="agro-btn-primary">
                        {{ __('Create Campaign') }}
                    </button>
                </div>
            </form>
        </x-ui.card>
    </div>
</x-app-layout>
