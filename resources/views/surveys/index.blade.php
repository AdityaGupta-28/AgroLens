<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="text-lg font-semibold text-slate-900 dark:text-white sm:text-xl">{{ __('Survey Data Collection') }}</h1>
                <p class="text-sm text-slate-500 dark:text-slate-400">{{ __('Active enumerator programmes') }} — {{ number_format($totalResponses) }} {{ __('responses on record') }}</p>
            </div>
            @if (auth()->user()?->hasPermission(\App\Enums\Permission::ManageSurveys))
                <a href="{{ route('surveys.create') }}" class="agro-btn-primary flex items-center gap-1.5 self-start text-xs sm:text-sm">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    {{ __('Create Campaign') }}
                </a>
            @endif
        </div>
    </x-slot>

    <div class="mb-6 grid grid-cols-2 gap-4 lg:grid-cols-3">
        <x-ui.stat-card label="Active Surveys" :value="$surveys->where('is_active', true)->count()" icon="📋" />
        <x-ui.stat-card label="Total Responses" :value="number_format($totalResponses)" icon="✅" />
        <x-ui.stat-card label="Programmes" :value="$surveys->count()" icon="🗂️" />
    </div>

    <x-ui.card title="Enumerator programmes" description="Schemas align with farmers, holdings, crops, and wells in the operational database." :padding="false">
        <div class="overflow-x-auto">
            <table class="agro-table">
                <thead>
                    <tr>
                        <th>Code</th>
                        <th>Title</th>
                        <th>Status</th>
                        <th>Period</th>
                        <th>Responses</th>
                        <th class="text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 dark:divide-slate-700">
                    @forelse ($surveys as $survey)
                        <tr class="hover:bg-slate-50/80 dark:hover:bg-slate-800/40">
                            <td class="font-mono text-sm font-semibold text-slate-700 dark:text-slate-300">{{ $survey->code }}</td>
                            <td>
                                <p class="font-medium text-slate-900 dark:text-white">{{ $survey->title }}</p>
                                <p class="mt-0.5 text-xs text-slate-500 dark:text-slate-400">{{ $survey->description }}</p>
                            </td>
                            <td>
                                <x-ui.badge :variant="$survey->is_active ? 'success' : 'warning'">
                                    {{ $survey->is_active ? 'Active' : 'Inactive' }}
                                </x-ui.badge>
                            </td>
                            <td class="text-sm text-slate-500 dark:text-slate-400">
                                {{ $survey->starts_at?->format('M Y') }} – {{ $survey->ends_at?->format('M Y') }}
                            </td>
                            <td class="font-semibold">{{ number_format($survey->responses_count) }}</td>
                            <td class="text-right">
                                <div class="flex items-center justify-end gap-3">
                                    @if ($survey->is_active)
                                        <a href="{{ route('surveys.collect', $survey) }}" class="inline-flex items-center gap-1 text-sm font-semibold text-brand-600 hover:text-brand-750 dark:text-brand-400 dark:hover:text-brand-300">
                                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/>
                                            </svg>
                                            Collect
                                        </a>
                                    @endif
                                    
                                    <a href="{{ route('surveys.responses', $survey) }}" class="inline-flex items-center gap-1 text-sm font-semibold text-slate-600 hover:text-slate-900 dark:text-slate-400 dark:hover:text-white">
                                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                                        </svg>
                                        Analytics
                                    </a>

                                    @if(auth()->user()?->hasPermission(\App\Enums\Permission::ManageSurveys))
                                        <form action="{{ route('surveys.destroy', $survey) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this survey campaign? All collected responses will be permanently removed.');" class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="inline-flex items-center gap-1 text-sm font-semibold text-red-650 hover:text-red-750 dark:text-red-400 dark:hover:text-red-300 cursor-pointer">
                                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                </svg>
                                                Delete
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6">
                                <x-ui.empty-state title="No surveys configured" description="Create a custom survey or run database seeders to load enumerator programmes." />
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </x-ui.card>
</x-app-layout>
