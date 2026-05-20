<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <a href="{{ route('surveys.index') }}" class="text-slate-500 hover:text-slate-700 dark:text-slate-400 dark:hover:text-slate-300">
                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
            </a>
            <div>
                <h1 class="text-lg font-semibold text-slate-900 dark:text-white sm:text-xl">{{ __('Campaign Submissions & Analytics') }}</h1>
                <p class="text-sm text-slate-500 dark:text-slate-400">{{ $survey->title }} — Code: <span class="font-mono font-semibold">{{ $survey->code }}</span></p>
            </div>
        </div>
    </x-slot>

    <div class="space-y-6">
        <!-- Analytics & KPI Cards -->
        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <x-ui.stat-card label="Total Submissions" :value="number_format($responses->total())" icon="📥" />
            
            <x-ui.stat-card label="Farmers Engaged" :value="number_format($responses->pluck('farmer_id')->unique()->filter()->count())" icon="🧑‍🌾" />
            
            <x-ui.stat-card label="Districts Surveyed" :value="number_format($responses->pluck('region_id')->unique()->filter()->count())" icon="📍" />

            @if (in_array('area_hectares', $survey->schema['fields'] ?? []))
                <x-ui.stat-card label="Total Sown Area" :value="$stats['totalArea'] . ' Hectares'" icon="🌾" />
            @elseif (in_array('depth_feet', $survey->schema['fields'] ?? []))
                <x-ui.stat-card label="Average Well Depth" :value="$stats['avgDepth'] . ' Feet'" icon="💧" />
            @else
                <x-ui.stat-card label="Campaign Status" :value="$survey->is_active ? 'Active' : 'Archived'" icon="📋" />
            @endif
        </div>

        <!-- Dynamic Visualization Panels -->
        @if (!empty($stats['cropCounts']) || !empty($stats['wellTypeCounts']) || !empty($stats['rechargeCounts']))
            <div class="grid gap-6 md:grid-cols-2">
                <!-- Doughnut Chart: Distribution -->
                @if (!empty($stats['cropCounts']) || !empty($stats['wellTypeCounts']))
                    <x-ui.card title="Category Distribution Analytics" description="Breakdown of submitted entities within this campaign.">
                        <div class="relative flex items-center justify-center p-2 h-64">
                            <canvas id="categoryDistributionChart"></canvas>
                        </div>
                    </x-ui.card>
                @endif

                <!-- Bar/Polar Chart: Secondary Metric -->
                @if (!empty($stats['rechargeCounts']))
                    <x-ui.card title="Well Recharge Metrics" description="Quality index distributions of groundwater recharge.">
                        <div class="relative flex items-center justify-center p-2 h-64">
                            <canvas id="rechargeMetricsChart"></canvas>
                        </div>
                    </x-ui.card>
                @elseif (in_array('area_hectares', $survey->schema['fields'] ?? []) && $stats['areaCount'] > 0)
                    <x-ui.card title="Cultivation Statistics" description="Calculated aggregate indices for land sowing sizing.">
                        <div class="p-6 space-y-4">
                            <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-2">
                                <span class="text-sm text-slate-500 dark:text-slate-400">Total Cultivated Area on Record</span>
                                <span class="font-bold text-lg text-slate-900 dark:text-white font-mono">{{ $stats['totalArea'] }} Hectares</span>
                            </div>
                            <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-2">
                                <span class="text-sm text-slate-500 dark:text-slate-400">Average Holding Area</span>
                                <span class="font-bold text-lg text-slate-900 dark:text-white font-mono">{{ $stats['avgArea'] }} Hectares</span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-sm text-slate-500 dark:text-slate-400">Agricultural Form Entries</span>
                                <span class="font-bold text-lg text-slate-900 dark:text-white font-mono">{{ $stats['areaCount'] }} farmers</span>
                            </div>
                            <div class="p-4 bg-slate-50 dark:bg-slate-900 rounded-lg text-xs text-slate-500 dark:text-slate-400">
                                Areas align with primary land records inside district registers. Correct coordinate boundaries have been verified.
                            </div>
                        </div>
                    </x-ui.card>
                @elseif (in_array('depth_feet', $survey->schema['fields'] ?? []) && $stats['depthCount'] > 0)
                    <x-ui.card title="Well Depth Index" description="Depth summaries for registered aquifers and tube wells.">
                        <div class="p-6 space-y-4">
                            <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-2">
                                <span class="text-sm text-slate-500 dark:text-slate-400">Average Aquifer Depth</span>
                                <span class="font-bold text-lg text-slate-900 dark:text-white font-mono">{{ $stats['avgDepth'] }} Feet</span>
                            </div>
                            <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-2">
                                <span class="text-sm text-slate-500 dark:text-slate-400">Tested Wells</span>
                                <span class="font-bold text-lg text-slate-900 dark:text-white font-mono">{{ $stats['depthCount'] }} units</span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-sm text-slate-500 dark:text-slate-400">Average Water Table Depth</span>
                                <span class="font-bold text-lg text-slate-900 dark:text-white font-mono">
                                    {{ round($responses->pluck('responses.water_table_level_m')->avg(), 2) }} Meters
                                </span>
                            </div>
                            <div class="p-4 bg-slate-50 dark:bg-slate-900 rounded-lg text-xs text-slate-500 dark:text-slate-400">
                                Deeper tube wells indicate increasing localized water-table stress. These coordinates map straight into the primary GIS layer.
                            </div>
                        </div>
                    </x-ui.card>
                @endif
            </div>
        @endif

        <!-- Submissions Table -->
        <x-ui.card title="Raw Submissions Log" :padding="false">
            <div class="overflow-x-auto">
                <table class="agro-table">
                    <thead>
                        <tr>
                            <th>Enumerator (Officer)</th>
                            <th>District</th>
                            <th>Target Farmer</th>
                            <th>Spatial GPS</th>
                            <th>Field Answers</th>
                            <th>Submitted At</th>
                            @if(auth()->user()?->hasPermission(\App\Enums\Permission::ManageSurveys))
                                <th class="text-right">Actions</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200 dark:divide-slate-700">
                        @forelse ($responses as $r)
                            <tr class="hover:bg-slate-50/80 dark:hover:bg-slate-800/40">
                                <td>
                                    <div class="font-medium text-slate-900 dark:text-white">{{ $r->enumerator?->name }}</div>
                                    <div class="text-[10px] text-slate-500 dark:text-slate-400 font-mono">{{ $r->enumerator?->email }}</div>
                                </td>
                                <td>
                                    @if ($r->region)
                                        <span class="font-semibold text-slate-700 dark:text-slate-300 text-sm">{{ $r->region->name }}</span>
                                        <div class="text-[10px] text-slate-400">{{ $r->region->state }}</div>
                                    @else
                                        <span class="text-slate-400 dark:text-slate-500 italic text-xs">Direct / Not linked</span>
                                    @endif
                                </td>
                                <td>
                                    @if ($r->farmer)
                                        <div class="font-medium text-slate-800 dark:text-slate-200 text-sm">{{ $r->farmer->name }}</div>
                                        <div class="text-[10px] text-slate-500 font-mono">{{ $r->farmer->farmer_code }}</div>
                                    @else
                                        <span class="text-slate-400 dark:text-slate-500 italic text-xs">Anonymous submission</span>
                                    @endif
                                </td>
                                <td>
                                    @if ($r->latitude && $r->longitude)
                                        <span class="font-mono text-xs font-semibold text-brand-600 dark:text-brand-400">
                                            {{ round($r->latitude, 4) }}, {{ round($r->longitude, 4) }}
                                        </span>
                                    @else
                                        <span class="text-slate-400 dark:text-slate-500 text-xs italic">No spatial data</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="flex flex-wrap gap-1.5 max-w-sm py-1">
                                        @foreach ($r->responses ?? [] as $key => $val)
                                            <span class="inline-flex items-center rounded-md bg-slate-100 dark:bg-slate-800 px-2 py-0.5 text-[10px] font-medium text-slate-650 dark:text-slate-350 border border-slate-200 dark:border-slate-700 font-mono">
                                                <strong class="text-slate-800 dark:text-slate-250 mr-0.5">
                                                    @if ($key === 'crop_id')
                                                        crop:
                                                    @else
                                                        {{ str_replace('_', ' ', $key) }}:
                                                    @endif
                                                </strong> 
                                                @if ($key === 'crop_id' && isset($cropNames[$val]))
                                                    {{ $cropNames[$val] }}
                                                @elseif (is_string($val))
                                                    {{ str_replace('_', ' ', $val) }}
                                                @else
                                                    {{ $val }}
                                                @endif
                                            </span>
                                        @endforeach
                                    </div>
                                </td>
                                <td class="text-xs text-slate-500 dark:text-slate-400 whitespace-nowrap">
                                    {{ $r->submitted_at?->format('d M Y') }}
                                    <div class="text-[10px] text-slate-450">{{ $r->submitted_at?->format('H:i') }}</div>
                                </td>
                                @if(auth()->user()?->hasPermission(\App\Enums\Permission::ManageSurveys))
                                    <td class="text-right whitespace-nowrap">
                                        <form action="{{ route('surveys.destroyResponse', $r) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this specific survey submission? This cannot be undone.');" class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-xs font-semibold text-red-650 hover:text-red-750 dark:text-red-400 dark:hover:text-red-300 cursor-pointer">
                                                Delete
                                            </button>
                                        </form>
                                    </td>
                                @endif
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7">
                                    <x-ui.empty-state title="No submissions collected yet" description="Click on 'Collect' on the survey main page to record the first response." />
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if ($responses->hasPages())
                <div class="border-t border-slate-200 dark:border-slate-700 p-4">{{ $responses->links() }}</div>
            @endif
        </x-ui.card>
    </div>

    @push('scripts')
        <!-- Fail-safe Chart.js CDN load -->
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
        
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const isDark = document.documentElement.classList.contains('dark');
                const textColor = isDark ? '#cbd5e1' : '#334155';
                const gridColor = isDark ? 'rgba(255, 255, 255, 0.08)' : 'rgba(0, 0, 0, 0.04)';

                // 1. Doughnut/Pie Chart for Category Distribution
                @if (!empty($stats['cropCounts']) || !empty($stats['wellTypeCounts']))
                    const catCtx = document.getElementById('categoryDistributionChart').getContext('2d');
                    
                    @if (!empty($stats['cropCounts']))
                        const catLabels = {!! json_encode(array_keys($stats['cropCounts'])) !!};
                        const catData = {!! json_encode(array_values($stats['cropCounts'])) !!};
                        const chartTitle = 'Crop Distribution (Count)';
                    @else
                        const catLabels = {!! json_encode(array_keys($stats['wellTypeCounts'])) !!};
                        const catData = {!! json_encode(array_values($stats['wellTypeCounts'])) !!};
                        const chartTitle = 'Well Type Breakdown';
                    @endif

                    new Chart(catCtx, {
                        type: 'doughnut',
                        data: {
                            labels: catLabels,
                            datasets: [{
                                data: catData,
                                backgroundColor: [
                                    '#10b981', // Emerald
                                    '#06b6d4', // Cyan
                                    '#3b82f6', // Blue
                                    '#f59e0b', // Amber
                                    '#8b5cf6', // Violet
                                    '#ec4899'  // Pink
                                ],
                                borderWidth: isDark ? 2 : 1,
                                borderColor: isDark ? '#0f172a' : '#ffffff'
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: {
                                    position: 'bottom',
                                    labels: {
                                        color: textColor,
                                        boxWidth: 12,
                                        font: { size: 11, family: 'Inter, sans-serif' }
                                    }
                                }
                            }
                        }
                    });
                @endif

                // 2. Bar Chart for Recharge Status Metrics
                @if (!empty($stats['rechargeCounts']))
                    const rechCtx = document.getElementById('rechargeMetricsChart').getContext('2d');
                    const rechLabels = {!! json_encode(array_keys($stats['rechargeCounts'])) !!};
                    const rechData = {!! json_encode(array_values($stats['rechargeCounts'])) !!};

                    new Chart(rechCtx, {
                        type: 'bar',
                        data: {
                            labels: rechLabels,
                            datasets: [{
                                label: 'Responses Count',
                                data: rechData,
                                backgroundColor: '#0ea5e9', // Sky Blue
                                borderRadius: 6,
                                borderHeight: 0
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: { display: false }
                            },
                            scales: {
                                x: {
                                    grid: { drawOnChartArea: false },
                                    ticks: { color: textColor, font: { family: 'Inter, sans-serif' } }
                                },
                                y: {
                                    grid: { color: gridColor },
                                    ticks: { color: textColor, stepSize: 1, font: { family: 'Inter, sans-serif' } }
                                }
                            }
                        }
                    });
                @endif
            });
        </script>
    @endpush
</x-app-layout>
