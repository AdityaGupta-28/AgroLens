import Chart from 'chart.js/auto';

const chartInstances = {};

function chartTheme() {
    const dark = document.documentElement.classList.contains('dark');

    return {
        text: dark ? '#94a3b8' : '#64748b',
        grid: dark ? '#334155' : '#e2e8f0',
    };
}

function baseOptions() {
    const { text, grid } = chartTheme();

    return {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: { labels: { color: text } },
        },
        scales: {
            x: { ticks: { color: text }, grid: { color: grid } },
            y: { ticks: { color: text }, grid: { color: grid } },
        },
    };
}

function destroyChart(key) {
    if (chartInstances[key]) {
        chartInstances[key].destroy();
        delete chartInstances[key];
    }
}

function renderEmpty(canvas, message) {
    const ctx = canvas.getContext('2d');
    if (!ctx) {
        return;
    }

    destroyChart(canvas.id);
    const { text } = chartTheme();
    ctx.clearRect(0, 0, canvas.width, canvas.height);
    ctx.fillStyle = text;
    ctx.font = '14px system-ui, sans-serif';
    ctx.textAlign = 'center';
    ctx.fillText(message, canvas.width / 2 || 120, canvas.height / 2 || 80);
}

function renderCharts(data) {
    const holding = data?.holding ?? [];
    const irrigation = data?.irrigation ?? [];
    const crops = data?.crops ?? [];
    const wells = data?.wells ?? [];
    const { text } = chartTheme();
    const borderColor = document.documentElement.classList.contains('dark') ? '#1e293b' : '#ffffff';

    const holdingEl = document.getElementById('holdingChart');
    if (holdingEl) {
        destroyChart('holdingChart');
        if (holding.length === 0) {
            renderEmpty(holdingEl, 'No holding data');
        } else {
            chartInstances.holdingChart = new Chart(holdingEl, {
                type: 'bar',
                data: {
                    labels: holding.map((r) => r.label ?? r.category),
                    datasets: [{
                        label: 'Area (Ha)',
                        data: holding.map((r) => r.total_area),
                        backgroundColor: holding.map((r) => r.color ?? '#10b981'),
                        borderRadius: 4,
                    }],
                },
                options: {
                    ...baseOptions(),
                    plugins: { legend: { display: false } },
                },
            });
        }
    }

    const irrigationEl = document.getElementById('irrigationChart');
    if (irrigationEl) {
        destroyChart('irrigationChart');
        if (irrigation.length === 0) {
            renderEmpty(irrigationEl, 'No irrigation data');
        } else {
            chartInstances.irrigationChart = new Chart(irrigationEl, {
                type: 'doughnut',
                data: {
                    labels: irrigation.map((r) => r.label ?? r.source),
                    datasets: [{
                        data: irrigation.map((r) => r.count),
                        backgroundColor: irrigation.map((r) => r.color ?? '#10b981'),
                        borderWidth: 2,
                        borderColor,
                    }],
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { position: 'right', labels: { color: text, boxWidth: 12 } } },
                },
            });
        }
    }

    const cropEl = document.getElementById('cropChart');
    if (cropEl) {
        destroyChart('cropChart');
        if (crops.length === 0) {
            renderEmpty(cropEl, 'No crop data — check API key or filters');
        } else {
            chartInstances.cropChart = new Chart(cropEl, {
                type: 'pie',
                data: {
                    labels: crops.map((r) => r.crop),
                    datasets: [{
                        data: crops.map((r) => r.area),
                        backgroundColor: crops.map((r) => r.color),
                        borderColor,
                        borderWidth: 2,
                        hoverOffset: 14,
                    }],
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    layout: { padding: 12 },
                    interaction: { mode: 'nearest', intersect: true },
                    plugins: {
                        legend: { position: 'bottom', labels: { color: text, boxWidth: 12, padding: 14 } },
                        tooltip: {
                            enabled: true,
                            callbacks: {
                                label: (ctx) => {
                                    const row = crops[ctx.dataIndex];
                                    return `${row.crop}: ${row.area} ha • ${row.percentage}%`;
                                },
                                title: (items) => items.map((item) => item.label).join(', '),
                            },
                        },
                    },
                },
            });
        }
    }

    const wellEl = document.getElementById('wellChart');
    if (wellEl) {
        destroyChart('wellChart');
        if (wells.length === 0) {
            renderEmpty(wellEl, 'No well data');
        } else {
            chartInstances.wellChart = new Chart(wellEl, {
                type: 'line',
                data: {
                    labels: wells.map((r) => r.region),
                    datasets: [{
                        label: 'Avg Depth (ft)',
                        data: wells.map((r) => r.avg_depth),
                        borderColor: '#0ea5e9',
                        backgroundColor: 'rgba(14, 165, 233, 0.15)',
                        fill: true,
                        tension: 0.3,
                    }],
                },
                options: baseOptions(),
            });
        }
    }
}

window.AgroLensCharts = { render: renderCharts };

document.addEventListener('livewire:init', () => {
    Livewire.on('charts-updated', (event) => {
        const payload = event?.payload ?? event?.[0]?.payload ?? event;
        renderCharts(payload);
    });
});

window.addEventListener('theme-changed', () => {
    if (window.agrolensLastChartPayload) {
        renderCharts(window.agrolensLastChartPayload);
    }
});

document.addEventListener('DOMContentLoaded', () => {
    if (window.agrolensLastChartPayload) {
        renderCharts(window.agrolensLastChartPayload);
    }
});

if (document.readyState !== 'loading' && window.agrolensLastChartPayload) {
    renderCharts(window.agrolensLastChartPayload);
}

document.addEventListener('agrolens-charts-render', (event) => {
    renderCharts(event.detail ?? {});
});
