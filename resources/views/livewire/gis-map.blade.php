<div class="space-y-4">
    <div class="grid gap-4 lg:grid-cols-[1fr_auto] lg:items-end">
        <div class="grid gap-4 md:grid-cols-3">
            <div>
                <label class="agro-label" for="gis-state">State</label>
                <select wire:model.live="state" id="gis-state" class="agro-select">
                    <option value="">All States</option>
                    @foreach ($states as $s)
                        <option value="{{ $s }}">{{ $s }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="agro-label" for="gis-district">District</label>
                <select wire:model.live="regionId" id="gis-district" class="agro-select">
                    <option value="">All Districts</option>
                    @foreach ($districts as $district)
                        <option value="{{ $district->id }}">{{ $district->name }} ({{ $district->state }})</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="agro-label" for="gis-year">Crop Year</label>
                <select wire:model.live="year" id="gis-year" class="agro-select">
                    @foreach ($years as $y)
                        <option value="{{ $y }}">{{ $y }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="flex flex-wrap items-center gap-2 text-xs text-slate-600 dark:text-slate-300">
            <span class="rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 dark:border-slate-700 dark:bg-slate-900">
                <span wire:loading.remove wire:target="state,regionId,year">{{ count($markers) }} district markers</span>
                <span wire:loading wire:target="state,regionId,year">Updating map...</span>
            </span>
            <span class="rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 dark:border-slate-700 dark:bg-slate-900">English state and district labels</span>
        </div>
    </div>

    <div class="grid gap-4 xl:grid-cols-[1fr_17rem]">
        <div wire:ignore id="agrolens-map" class="z-0 h-[580px] overflow-hidden rounded-lg border border-slate-200 bg-slate-100 dark:border-slate-700 dark:bg-slate-900"></div>

        <aside class="rounded-lg border border-slate-200 bg-white p-4 dark:border-slate-700 dark:bg-slate-900">
            <h3 class="text-sm font-semibold text-slate-900 dark:text-white">Map Legend</h3>
            <p class="mt-1 text-xs leading-5 text-slate-500 dark:text-slate-400">Marker color indicates relative farmer density in the current filter.</p>

            <div class="mt-4 space-y-3 text-sm text-slate-600 dark:text-slate-300">
                <div class="flex items-center justify-between gap-3">
                    <span class="flex items-center gap-2"><span class="inline-block h-3 w-3 rounded-full bg-amber-200"></span> Low</span>
                    <span class="text-xs text-slate-400">fewer records</span>
                </div>
                <div class="flex items-center justify-between gap-3">
                    <span class="flex items-center gap-2"><span class="inline-block h-3 w-3 rounded-full bg-amber-500"></span> Medium</span>
                    <span class="text-xs text-slate-400">typical</span>
                </div>
                <div class="flex items-center justify-between gap-3">
                    <span class="flex items-center gap-2"><span class="inline-block h-3 w-3 rounded-full bg-red-600"></span> High</span>
                    <span class="text-xs text-slate-400">dense</span>
                </div>
            </div>

            <div class="mt-5 border-t border-slate-200 pt-4 dark:border-slate-700">
                <p class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">Current scope</p>
                <p class="mt-2 text-sm text-slate-700 dark:text-slate-300">
                    {{ $state ?: 'All Indian states' }}
                    @if ($regionId)
                        @php($selectedDistrict = $districts->firstWhere('id', $regionId))
                        / {{ $selectedDistrict?->name }}
                    @endif
                </p>
            </div>
        </aside>
    </div>
</div>

@assets
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
@endassets

@once
<script>
(function () {
    let map, markerLayer, tileLayer;
    let listenersAttached = false;

    function safeText(value) {
        return String(value ?? '')
            .replaceAll('&', '&amp;')
            .replaceAll('<', '&lt;')
            .replaceAll('>', '&gt;')
            .replaceAll('"', '&quot;')
            .replaceAll("'", '&#039;');
    }

    function popupHtml(m) {
        return `
            <div class="min-w-[220px] text-sm text-slate-700">
                <div class="mb-2">
                    <strong class="block text-base text-slate-900">${safeText(m.name)}</strong>
                    <span class="text-xs font-medium uppercase tracking-wide text-slate-500">${safeText(m.state)}</span>
                    ${m.zone ? `<p class="mt-1 text-xs text-slate-500">${safeText(m.zone)}</p>` : ''}
                </div>
                <table class="w-full border-t border-slate-200 pt-2">
                    <tr><td class="py-1">Farmers</td><td class="py-1 text-right font-semibold">${Number(m.farmers || 0).toLocaleString('en-IN')}</td></tr>
                    <tr><td class="py-1">Wells</td><td class="py-1 text-right font-semibold">${Number(m.wells || 0).toLocaleString('en-IN')}</td></tr>
                    <tr><td class="py-1">Cultivated land</td><td class="py-1 text-right font-semibold">${m.cultivated_land} ha</td></tr>
                    <tr><td class="py-1">Irrigation</td><td class="py-1 text-right font-semibold">${m.irrigation_pct}%</td></tr>
                    <tr><td class="py-1">Top crop</td><td class="py-1 text-right font-semibold">${safeText(m.top_crop || '-')}</td></tr>
                </table>
            </div>
        `;
    }

    function markerRadius(m, list) {
        const farmers = Number(m.farmers || 0);
        const max = Math.max(...list.map((item) => Number(item.farmers || 0)), 1);
        const min = Math.min(...list.map((item) => Number(item.farmers || 0)), max);

        if (max === min) {
            return 7;
        }

        const ratio = (farmers - min) / (max - min);
        return Math.max(5, Math.min(13, 5 + ratio * 8));
    }

    function initMap(markers) {
        if (typeof L === 'undefined') {
            return;
        }

        const indiaBounds = L.latLngBounds([
            [6.5546, 68.1113],
            [35.6745, 97.3954],
        ]);

        if (!map) {
            map = L.map('agrolens-map', {
                center: [22.5937, 78.9629],
                zoom: 5,
                minZoom: 4,
                maxZoom: 10,
                zoomSnap: 0.25,
                zoomDelta: 0.25,
                maxBounds: indiaBounds.pad(0.08),
                maxBoundsViscosity: 1,
                worldCopyJump: false,
                zoomControl: true,
            });

            const lightTilesUrl = 'https://{s}.basemaps.cartocdn.com/light_all/{z}/{x}/{y}{r}.png';
            const darkTilesUrl = 'https://{s}.basemaps.cartocdn.com/dark_all/{z}/{x}/{y}{r}.png';
            const isDark = document.documentElement.classList.contains('dark');

            tileLayer = L.tileLayer(isDark ? darkTilesUrl : lightTilesUrl, {
                attribution: '&copy; OpenStreetMap contributors &copy; CARTO',
                subdomains: 'abcd',
                maxZoom: 20,
            }).addTo(map);

            window.addEventListener('theme-changed', (e) => {
                if (tileLayer && map) {
                    map.removeLayer(tileLayer);
                    const newUrl = e.detail.theme === 'dark' ? darkTilesUrl : lightTilesUrl;
                    tileLayer = L.tileLayer(newUrl, {
                        attribution: '&copy; OpenStreetMap contributors &copy; CARTO',
                        subdomains: 'abcd',
                        maxZoom: 20,
                    }).addTo(map);
                }
            });

            markerLayer = L.layerGroup().addTo(map);

            map.on('dragend', () => {
                map.panInsideBounds(indiaBounds, { animate: true });
            });
        }

        markerLayer.clearLayers();
        const list = markers || [];

        list.forEach((m) => {
            L.circleMarker([m.lat, m.lng], {
                radius: markerRadius(m, list),
                fillColor: m.color || '#f59e0b',
                color: '#ffffff',
                weight: 1.5,
                fillOpacity: 0.78,
                opacity: 0.95,
            })
                .bindPopup(popupHtml(m), { maxWidth: 300, className: 'agrolens-map-popup' })
                .addTo(markerLayer);
        });

        if (list.length > 0) {
            const bounds = L.latLngBounds(list.map((m) => [m.lat, m.lng]));
            map.fitBounds(bounds.pad(list.length === 1 ? 2.5 : 0.2), { maxZoom: list.length === 1 ? 8 : 7 });
        } else {
            map.fitBounds(indiaBounds, { padding: [48, 48] });
        }

        setTimeout(() => map?.invalidateSize(), 150);
    }

    function attachMapListeners() {
        if (listenersAttached || typeof Livewire === 'undefined') {
            return;
        }

        listenersAttached = true;
        Livewire.on('map-markers-updated', (event) => {
            const markers = event?.markers ?? event?.[0]?.markers ?? [];
            initMap(markers);
        });
    }

    const initialMarkers = @js($markers);

    document.addEventListener('livewire:init', attachMapListeners);
    document.addEventListener('livewire:load', attachMapListeners);
    document.addEventListener('DOMContentLoaded', () => {
        attachMapListeners();
        if (document.getElementById('agrolens-map')) {
            initMap(initialMarkers);
        }
    });

    if (document.readyState !== 'loading' && document.getElementById('agrolens-map')) {
        attachMapListeners();
        initMap(initialMarkers);
    }
})();
</script>
@endonce
