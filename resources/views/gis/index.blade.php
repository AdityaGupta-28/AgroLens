<x-app-layout>
    <x-slot name="header">
        <div>
            <h1 class="text-lg font-semibold text-slate-900 dark:text-white sm:text-xl">{{ __('GIS & Regional Analytics') }}</h1>
            <p class="text-sm text-slate-500 dark:text-slate-400">{{ __('Filter English state and district markers from the same MySQL records used by the dashboard') }}</p>
        </div>
    </x-slot>

    <livewire:gis-map />
</x-app-layout>
