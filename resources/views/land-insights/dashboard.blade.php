@extends('layouts.app')

@section('title', 'Agricultural Dashboard')

@section('styles')
<style>
    .header-section { margin-bottom: 2rem; }
    .header-section h1 { font-size: 2rem; margin-bottom: 0.5rem; }
    .header-section p { color: var(--text-muted); }

    .grid { display: grid; gap: 1.5rem; margin-bottom: 2rem; }
    .grid-3 { grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); }
    .grid-2 { grid-template-columns: repeat(auto-fit, minmax(450px, 1fr)); }

    .stat-card .label { color: var(--text-muted); font-size: 0.85rem; text-transform: uppercase; margin-bottom: 0.5rem; }
    .stat-card .value { font-size: 1.75rem; font-weight: 700; }
    .stat-card .trend { font-size: 0.85rem; color: var(--primary); margin-left: 0.5rem; }

    .chart-container { height: 300px; position: relative; }

    table { width: 100%; border-collapse: collapse; margin-top: 1rem; }
    th { text-align: left; padding: 1rem; color: var(--text-muted); font-size: 0.8rem; border-bottom: 1px solid var(--border); }
    td { padding: 1rem; border-bottom: 1px solid var(--border); font-size: 0.9rem; }
    
    .badge { padding: 0.25rem 0.6rem; border-radius: 0.5rem; font-size: 0.75rem; font-weight: 600; }
    .badge-soil { background: rgba(56, 189, 248, 0.1); color: var(--secondary); }
    .badge-rain { background: rgba(16, 185, 129, 0.1); color: var(--primary); }

    .progress-bar { height: 6px; background: rgba(255,255,255,0.05); border-radius: 3px; overflow: hidden; margin-top: 5px; }
    .progress-fill { height: 100%; background: var(--primary); }
</style>
@endsection

@section('content')
<div class="header-section">
    <h1>Agricultural Intelligence Dashboard</h1>
    <p>Regional analysis of land holding, soil health, and water resources.</p>
</div>

<div class="grid grid-3">
    <div class="glass-card stat-card">
        <div class="label">Avg Land Holding</div>
        <div class="value">{{ number_format($avgHolding, 2) }} <small style="font-size: 0.5em; color: var(--text-muted)">Ha</small></div>
        <div class="trend">↑ 2.4% from 2023</div>
    </div>
    <div class="glass-card stat-card">
        <div class="label">Soil Nutrient Index</div>
        <div class="value">Optimal</div>
        <div class="trend" style="color: var(--secondary)">Avg pH 7.2</div>
    </div>
    <div class="glass-card stat-card">
        <div class="label">Water Scarcity Risk</div>
        <div class="value">Moderate</div>
        <div class="trend" style="color: #fbbf24">Avg Well Depth: {{ $maxDepth }}ft</div>
    </div>
</div>

<div class="grid grid-2">
    <div class="glass-card">
        <h3>Holding Size Distribution (Hectares)</h3>
        <div class="chart-container">
            <canvas id="holdingChart"></canvas>
        </div>
    </div>
    <div class="glass-card">
        <h3>Well Depth by Region (Feet)</h3>
        <div class="chart-container">
            <canvas id="depthChart"></canvas>
        </div>
    </div>
</div>

<div class="glass-card">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
        <h3>Detailed Regional Insights</h3>
        <button class="btn btn-primary">Export Report</button>
    </div>
    <table>
        <thead>
            <tr>
                <th>Region</th>
                <th>Holding</th>
                <th>Soil Health (pH)</th>
                <th>Irrigation</th>
                <th>Rainfall</th>
                <th>Crops</th>
            </tr>
        </thead>
        <tbody>
            @foreach($insights as $insight)
            <tr>
                <td>
                    <div style="font-weight: 600;">{{ $insight->region->name }}</div>
                    <div style="font-size: 0.75rem; color: var(--text-muted);">{{ $insight->region->state }}</div>
                </td>
                <td>{{ $insight->holding_size_avg }} Ha</td>
                <td>
                    <span class="badge badge-soil">pH {{ $insight->soil_ph }}</span>
                    <div class="progress-bar">
                        <div class="progress-fill" style="width: {{ ($insight->soil_ph / 14) * 100 }}%; background: {{ $insight->soil_ph > 7.5 ? '#f87171' : ($insight->soil_ph < 6 ? '#fbbf24' : '#10b981') }}"></div>
                    </div>
                </td>
                <td>{{ $insight->primary_irrigation_source }}</td>
                <td>
                    <span class="badge badge-rain">{{ $insight->avg_rainfall }} mm</span>
                </td>
                <td>
                    @foreach($insight->major_crops as $crop)
                        <span style="font-size: 0.8rem; background: rgba(255,255,255,0.05); padding: 2px 6px; border-radius: 4px; margin-right: 3px;">{{ $crop }}</span>
                    @endforeach
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection

@section('scripts')
<script>
    const regions = {!! json_encode($insights->map(fn($i) => $i->region->name)) !!};
    const holdings = {!! json_encode($insights->map(fn($i) => $i->holding_size_avg)) !!};
    const depths = {!! json_encode($insights->map(fn($i) => $i->avg_well_depth)) !!};

    const commonOptions = {
        responsive: true,
        maintainAspectRatio: false,
        plugins: { legend: { display: false } },
        scales: {
            y: { grid: { color: 'rgba(255,255,255,0.05)' }, ticks: { color: '#94a3b8' } },
            x: { grid: { display: false }, ticks: { color: '#94a3b8' } }
        }
    };

    new Chart(document.getElementById('holdingChart'), {
        type: 'bar',
        data: {
            labels: regions,
            datasets: [{
                data: holdings,
                backgroundColor: '#10b981',
                borderRadius: 8
            }]
        },
        options: commonOptions
    });

    new Chart(document.getElementById('depthChart'), {
        type: 'line',
        data: {
            labels: regions,
            datasets: [{
                data: depths,
                borderColor: '#38bdf8',
                backgroundColor: 'rgba(56, 189, 248, 0.1)',
                fill: true,
                tension: 0.4,
                borderWidth: 3,
                pointBackgroundColor: '#38bdf8'
            }]
        },
        options: commonOptions
    });
</script>
@endsection

