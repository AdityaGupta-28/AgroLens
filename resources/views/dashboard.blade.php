<x-app-layout>
    <x-slot name="header">
        <div>
            <h1 class="text-lg font-semibold text-slate-900 dark:text-white sm:text-xl">{{ __('Land Insights Analytics') }}</h1>
            <p class="text-sm text-slate-500 dark:text-slate-400">{{ __('Analysis of land holding, irrigation, and cropping patterns across regions') }}</p>
        </div>
    </x-slot>

    <livewire:analytics-dashboard />
</x-app-layout>
