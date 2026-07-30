// dashboard.js — All GPS Tracker Dashboard Logic
// NOTE: `initialData` is injected by Blade before this script runs.

let appData = initialData;
let map = null;
let marker = null;
let mapLayer = null;
let dashboardMapMode = 'standard';
let historyMap = null;
let historyLine = null;
let historyLayer = null;
let historyMarkerLayer = null;
let historyMapMode = 'standard';
let lastAddressKey = null;
let lastHistoryRouteKey = null;
let historyCurrentPage = 1;
let logCurrentPage = 1;
const itemsPerPage = 8;
let activeView = 'dashboard';
let isRefreshing = false;
const addressCache = new Map();
const routeCache = new Map();

const tileSources = {
    standard: {
        url: 'https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png',
        options: {
            attribution: '&copy; OpenStreetMap contributors',
            maxZoom: 19,
        },
    },
    satellite: {
        url: 'https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}',
        options: {
            attribution: 'Tiles &copy; Esri',
            maxZoom: 19,
        },
    },
};

const createTileLayer = (mode) => {
    const source = tileSources[mode] ?? tileSources.standard;
    return L.tileLayer(source.url, source.options);
};

const set = (id, val) => {
    const element = document.getElementById(id);
    if (element) {
        element.textContent = val ?? '-';
    }
};

const setWidth = (id, percent) => {
    const element = document.getElementById(id);
    if (element) {
        element.style.width = `${Math.max(0, Math.min(100, percent))}%`;
    }
};

const escapeHtml = (value) => String(value ?? '-').replace(/[&<>"']/g, (char) => ({
    '&': '&amp;',
    '<': '&lt;',
    '>': '&gt;',
    '"': '&quot;',
    "'": '&#039;',
}[char]));

const formatNumber = (value, decimals = 2) => {
    if (value === null || value === undefined || value === '' || Number.isNaN(Number(value))) {
        return '-';
    }

    return Number(value).toFixed(decimals);
};

const formatSpeed = (reading) => {
    const speed = reading?.speed_kmph ?? reading?.speed;
    return speed === null || speed === undefined ? '-' : `${formatNumber(speed)} km/h`;
};

const formatTime = (value) => {
    if (!value) {
        return '-';
    }

    return new Date(value).toLocaleString('id-ID', {
        year: 'numeric',
        month: 'short',
        day: '2-digit',
        hour: '2-digit',
        minute: '2-digit',
        second: '2-digit',
    });
};

const hasFix = (reading) => reading && reading.latitude !== null && reading.longitude !== null;

const statusBadge = (reading) => hasFix(reading)
    ? '<span class="badge sent"><span class="dot"></span>GPS Terkunci</span>'
    : '<span class="badge pending"><span class="dot"></span>GPS Pending</span>';

const signalBars = (reading) => {
    const good = Number(reading?.satellites ?? 0) >= 4 && Number(reading?.hdop ?? 99) <= 10;
    return `<span class="signal ${good ? 'good' : 'mid'}"><i></i><i></i><i></i><i></i></span>`;
};

const distanceKm = (path) => {
    const toRad = (value) => value * Math.PI / 180;
    let total = 0;

    for (let i = 1; i < path.length; i += 1) {
        const prev = path[i - 1];
        const next = path[i];
        const dLat = toRad(next.latitude - prev.latitude);
        const dLng = toRad(next.longitude - prev.longitude);
        const lat1 = toRad(prev.latitude);
        const lat2 = toRad(next.latitude);
        const a = Math.sin(dLat / 2) ** 2 + Math.cos(lat1) * Math.cos(lat2) * Math.sin(dLng / 2) ** 2;
        total += 6371 * 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
    }

    return total;
};

const showWaitingForFix = () => {
    const mapElement = document.getElementById('map');

    if (map) {
        map.remove();
        map = null;
        marker = null;
        mapLayer = null;
    }

    mapElement.innerHTML = '<div class="map-empty">Receiving ESP32 data, waiting for valid GPS fix...</div>';
    lastAddressKey = null;
    set('address', '-');
};

const syncDashboardMapControls = () => {
    document.querySelectorAll('[data-dashboard-map-mode]').forEach((button) => {
        button.classList.toggle('active', button.dataset.dashboardMapMode === dashboardMapMode);
    });
};

const applyDashboardMapMode = (mode) => {
    dashboardMapMode = mode;

    if (map) {
        if (mapLayer) {
            mapLayer.remove();
        }

        mapLayer = createTileLayer(dashboardMapMode).addTo(map);
        mapLayer.bringToBack();
    }

    syncDashboardMapControls();
};

const syncHistoryMapControls = () => {
    document.querySelectorAll('[data-history-map-mode]').forEach((button) => {
        button.classList.toggle('active', button.dataset.historyMapMode === historyMapMode);
    });
};

const applyHistoryMapMode = (mode) => {
    historyMapMode = mode;

    if (historyMap) {
        if (historyLayer) {
            historyLayer.remove();
        }

        historyLayer = createTileLayer(historyMapMode).addTo(historyMap);
        historyLayer.bringToBack();
    }

    syncHistoryMapControls();
};

const reverseGeocode = async (position) => {
    const key = position.map((value) => value.toFixed(5)).join(',');

    if (key === lastAddressKey) {
        return;
    }

    lastAddressKey = key;

    if (addressCache.has(key)) {
        set('address', addressCache.get(key));
        marker?.bindPopup(addressCache.get(key));
        return;
    }

    set('address', 'Loading address...');

    try {
        const url = new URL('https://nominatim.openstreetmap.org/reverse');
        url.searchParams.set('format', 'jsonv2');
        url.searchParams.set('lat', position[0]);
        url.searchParams.set('lon', position[1]);
        url.searchParams.set('zoom', '18');
        url.searchParams.set('addressdetails', '1');

        const response = await fetch(url, { headers: { 'Accept': 'application/json' } });

        if (!response.ok) {
            throw new Error('Reverse geocoding failed');
        }

        const data = await response.json();
        const address = data.display_name || 'Address not found';

        addressCache.set(key, address);
        set('address', address);
        marker?.bindPopup(address);
    } catch (error) {
        set('address', 'Address unavailable');
    }
};

const ensureMap = (coordinates) => {
    if (!Array.isArray(coordinates) || coordinates.length !== 2) {
        showWaitingForFix();
        return;
    }

    const position = [Number(coordinates[0]), Number(coordinates[1])];

    if (!Number.isFinite(position[0]) || !Number.isFinite(position[1])) {
        showWaitingForFix();
        return;
    }

    if (!map) {
        document.getElementById('map').innerHTML = '';

        map = L.map('map', { zoomControl: false }).setView(position, 16);
        mapLayer = createTileLayer(dashboardMapMode).addTo(map);
        L.control.zoom({ position: 'bottomright' }).addTo(map);
        marker = L.marker(position).addTo(map);
        setTimeout(() => map.invalidateSize(), 0);
    }

    marker.setLatLng(position);
    map.panTo(position, { animate: true, duration: 0.5 });
    syncDashboardMapControls();
    reverseGeocode(position);
};

const routeKey = (points) => points
    .map((point) => `${point[0].toFixed(5)},${point[1].toFixed(5)}`)
    .join('|');

const simplifyRoutePoints = (points, maxPoints = 24) => {
    if (points.length <= maxPoints) {
        return points;
    }

    const result = [];
    const lastIndex = points.length - 1;

    for (let i = 0; i < maxPoints; i += 1) {
        result.push(points[Math.round((i / (maxPoints - 1)) * lastIndex)]);
    }

    return result;
};

const fetchRoadRoute = async (points) => {
    const routePoints = simplifyRoutePoints(points);
    const key = routeKey(routePoints);

    if (routeCache.has(key)) {
        return routeCache.get(key);
    }

    if (routePoints.length < 2) {
        routeCache.set(key, routePoints);
        return routePoints;
    }

    try {
        const coordinates = routePoints
            .map((point) => `${point[1]},${point[0]}`)
            .join(';');
        const response = await fetch(`https://router.project-osrm.org/route/v1/driving/${coordinates}?overview=full&geometries=geojson`, {
            headers: { 'Accept': 'application/json' },
        });

        if (!response.ok) {
            throw new Error('Road routing failed');
        }

        const payload = await response.json();
        const routed = payload.routes?.[0]?.geometry?.coordinates
            ?.map((point) => [point[1], point[0]])
            ?.filter((point) => Number.isFinite(point[0]) && Number.isFinite(point[1]));

        if (!routed?.length) {
            throw new Error('Road route not found');
        }

        routeCache.set(key, routed);
        return routed;
    } catch (error) {
        routeCache.set(key, points);
        return points;
    }
};

const drawHistoryRoute = (points, forceFit = false) => {
    try {
        if (historyLine) {
            historyLine.remove();
            historyLine = null;
        }

        if (historyMarkerLayer) {
            historyMarkerLayer.remove();
            historyMarkerLayer = null;
        }

        if (!points || points.length === 0) {
            return;
        }

        historyMarkerLayer = L.layerGroup().addTo(historyMap);

        if (points.length === 1) {
            L.circleMarker(points[0], {
                radius: 8,
                color: '#ffffff',
                fillColor: '#26d9ff',
                fillOpacity: 1,
                weight: 3,
            }).addTo(historyMarkerLayer);

            historyMap.setView(points[0], 16);
            return;
        }

        historyLine = L.polyline(points, {
            color: '#17c9ee',
            weight: 5,
            opacity: 0.88,
            lineJoin: 'round',
            lineCap: 'round',
        }).addTo(historyMap);

        L.circleMarker(points[0], {
            radius: 6,
            color: '#ffffff',
            fillColor: '#12b76a',
            fillOpacity: 1,
            weight: 3,
        }).addTo(historyMarkerLayer);

        L.circleMarker(points[points.length - 1], {
            radius: 8,
            color: '#ffffff',
            fillColor: '#26d9ff',
            fillOpacity: 1,
            weight: 3,
        }).addTo(historyMarkerLayer);

        const bounds = historyLine.getBounds();
        if (bounds.isValid() && forceFit) {
            historyMap.fitBounds(bounds, { padding: [40, 40] });
        }
    } catch (error) {
        console.error('Error drawing history route:', error);
    }
};

const ensureHistoryMap = async (forceFit = false) => {
    try {
        // Get dynamically filtered readings that have coordinates
        const readings = filteredReadings()
            .filter((r) => r.latitude !== null && r.longitude !== null);

        // Reversing coordinates because readings list is ordered descending (newest first)
        const points = readings
            .map((point) => [Number(point.latitude), Number(point.longitude)])
            .reverse()
            .filter((point) => Number.isFinite(point[0]) && Number.isFinite(point[1]));

        if (!historyMap) {
            historyMap = L.map('history-map', { zoomControl: false });
            historyLayer = createTileLayer(historyMapMode).addTo(historyMap);
            L.control.zoom({ position: 'bottomright' }).addTo(historyMap);

            // Initialize default view to prevent Leaflet view errors
            if (points.length > 0) {
                historyMap.setView(points[0], 12);
            } else {
                historyMap.setView([-6.2, 106.82], 12);
            }
            forceFit = true;
        }

        historyMap.invalidateSize();

        if (!points.length) {
            if (historyLine) {
                historyLine.remove();
                historyLine = null;
            }
            if (historyMarkerLayer) {
                historyMarkerLayer.remove();
                historyMarkerLayer = null;
            }
            historyMap.setView([-6.2, 106.82], 12);
            lastHistoryRouteKey = null;
            return;
        }

        const currentRouteKey = points.map(p => `${p[0].toFixed(5)},${p[1].toFixed(5)}`).join('|');
        const routeChanged = (currentRouteKey !== lastHistoryRouteKey);
        if (routeChanged) {
            lastHistoryRouteKey = currentRouteKey;
        }

        drawHistoryRoute(points, forceFit || routeChanged);
        const routedPoints = await fetchRoadRoute(points);

        if (activeView === 'history') {
            drawHistoryRoute(routedPoints, forceFit || routeChanged);
        }

        syncHistoryMapControls();
    } catch (error) {
        console.error('Error in ensureHistoryMap:', error);
    }
};

const filteredReadings = () => {
    const status = document.getElementById('history-status')?.value ?? 'all';
    const start = document.getElementById('start-date')?.value;
    const end = document.getElementById('end-date')?.value;

    return (appData.readings || []).filter((reading) => {
        const readingDate = reading.recorded_at ? new Date(reading.recorded_at) : null;
        const statusOk = status === 'all' || (status === 'sent' ? hasFix(reading) : !hasFix(reading));
        const startOk = !start || (readingDate && readingDate >= new Date(`${start}T00:00:00`));
        const endOk = !end || (readingDate && readingDate <= new Date(`${end}T23:59:59`));

        return statusOk && startOk && endOk;
    });
};

const renderTables = () => {
    // 1. History Table Pagination
    const historyReadings = filteredReadings();
    const historyTotal = historyReadings.length;
    const historyTotalPages = Math.max(1, Math.ceil(historyTotal / itemsPerPage));
    
    if (historyCurrentPage > historyTotalPages) {
        historyCurrentPage = historyTotalPages;
    }
    
    const historyStart = (historyCurrentPage - 1) * itemsPerPage;
    const historyRows = historyReadings.slice(historyStart, historyStart + itemsPerPage);
    
    document.getElementById('history-table').innerHTML = historyRows.length
        ? historyRows.map((reading) => `
            <tr>
                <td>${escapeHtml(formatTime(reading.recorded_at))}</td>
                <td>${escapeHtml(formatNumber(reading.latitude, 6))}</td>
                <td>${escapeHtml(formatNumber(reading.longitude, 6))}</td>
                <td>${statusBadge(reading)}</td>
            </tr>
        `).join('')
        : '<tr><td colspan="4">No tracking history available.</td></tr>';

    set('history-page-info', `Halaman ${historyCurrentPage} dari ${historyTotalPages} (${historyTotal} data)`);
    
    const historyPrevBtn = document.getElementById('history-prev-btn');
    if (historyPrevBtn) historyPrevBtn.disabled = historyCurrentPage === 1;
    const historyNextBtn = document.getElementById('history-next-btn');
    if (historyNextBtn) historyNextBtn.disabled = historyCurrentPage === historyTotalPages;

    // 2. Log Table Pagination
    const logFilter = document.getElementById('log-filter')?.value ?? 'all';
    const allLogs = (appData.readings || [])
        .filter((reading) => {
            if (logFilter === 'all') return true;
            const isOffline = reading.offline === true || reading.offline === 1 || reading.offline === '1';
            return logFilter === 'offline' ? isOffline : !isOffline;
        });
        
    const logTotal = allLogs.length;
    const logTotalPages = Math.max(1, Math.ceil(logTotal / itemsPerPage));
    
    if (logCurrentPage > logTotalPages) {
        logCurrentPage = logTotalPages;
    }
    
    const logStart = (logCurrentPage - 1) * itemsPerPage;
    const logRows = allLogs.slice(logStart, logStart + itemsPerPage);

    document.getElementById('log-table').innerHTML = logRows.length
        ? logRows.map((reading) => {
            const isOffline = reading.offline === true || reading.offline === 1 || reading.offline === '1';
            const hasGps = hasFix(reading);
            const size = Math.max(0.8, JSON.stringify(reading).length / 1024).toFixed(1);

            let statusText = isOffline ? 'Offline (Synced)' : 'Real-time';
            let statusClass = isOffline ? 'sent' : 'sent';

            let statusHtml = `<span class="badge ${statusClass}"><span class="dot"></span>${statusText}</span>`;
            if (!hasGps) {
                statusHtml += ` <span class="badge pending" style="margin-left: 4px;"><span class="dot"></span>No GPS Fix</span>`;
            }

            return `
                <tr>
                    <td><strong>#LOG-${String(reading.id ?? 0).padStart(4, '0')}</strong></td>
                    <td>${escapeHtml(formatTime(reading.recorded_at))}</td>
                    <td>${statusHtml}</td>
                    <td>${escapeHtml(formatTime(reading.updated_at ?? reading.recorded_at))}</td>
                    <td>${size} KB</td>
                </tr>
            `;
        }).join('')
        : '<tr><td colspan="5">No data log queue available.</td></tr>';

    set('log-page-info', `Halaman ${logCurrentPage} dari ${logTotalPages} (${logTotal} data)`);
    
    const logPrevBtn = document.getElementById('log-prev-btn');
    if (logPrevBtn) logPrevBtn.disabled = logCurrentPage === 1;
    const logNextBtn = document.getElementById('log-next-btn');
    if (logNextBtn) logNextBtn.disabled = logCurrentPage === logTotalPages;

    if (window.lucide) {
        lucide.createIcons();
    }
};

const render = (data) => {
    appData = data;
    const latest = data.latest;
    const readings = data.readings || [];
    const gpsFixCount = readings.filter(hasFix).length;
    const offlineCount = readings.filter(r => r.offline === true || r.offline === 1 || r.offline === '1').length;
    const realtimeCount = readings.length - offlineCount;
    const reliability = readings.length ? Math.round((gpsFixCount / readings.length) * 100) : 0;
    const path = data.tracking_path || [];
    const distance = distanceKm(path);
    const hasCoordinates = Array.isArray(data.coordinates) && data.coordinates.length === 2;

    // Hitung status keaktifan perangkat secara real-time
    const serverTime = data.server_time ? new Date(data.server_time) : new Date();
    const recordedAt = latest ? new Date(latest.recorded_at) : null;
    const isDeviceActive = latest && recordedAt && (serverTime - recordedAt) < 60 * 1000;
    const hasGpsFix = isDeviceActive && latest && latest.latitude !== null && latest.longitude !== null;

    set('total', data.total ?? 0);
    set('system-total', data.total ?? 0);
    set('mini-pending', offlineCount);
    set('mini-sent', realtimeCount);
    set('distance-total', `${distance.toFixed(2)} km`);
    set('active-time', readings.length ? `${Math.max(1, Math.round(readings.length * 2 / 60))} min` : '0 min');
    set('network-reliability', reliability ? `${reliability}%` : '-');
    set('storage-caption', `${Math.max(1, Math.round(readings.length * 1.2))} KB / 32 MB`);
    setWidth('storage-bar', Math.min(100, readings.length));
    setWidth('reliability-bar', reliability);

    // Update status badge di Vehicle Details Card (Dashboard View)
    const devBadge = document.getElementById('device-status-badge');
    if (devBadge) {
        const label = devBadge.querySelector('.label');
        if (isDeviceActive) {
            devBadge.className = 'status-badge device-online';
            label.textContent = 'Alat: Aktif';
        } else {
            devBadge.className = 'status-badge device-offline';
            label.textContent = 'Alat: Tidak Aktif';
        }
    }

    const gpsBadge = document.getElementById('gps-lock-badge');
    if (gpsBadge) {
        const label = gpsBadge.querySelector('.label');
        if (hasGpsFix) {
            gpsBadge.className = 'status-badge gps-fixed';
            label.textContent = 'GPS: Terkunci';
        } else {
            gpsBadge.className = 'status-badge gps-no-fix';
            label.textContent = 'GPS: Belum Terkunci';
        }
    }

    // Update status di System View
    set('system-device-status', isDeviceActive ? 'Aktif' : 'Tidak Aktif');
    set('system-gps-lock', hasGpsFix ? 'Terkunci' : 'Belum Terkunci');

    if (!latest) {
        set('connection-label', 'Waiting');
        set('stream-status', 'Waiting for GPS data from ESP32');
        set('system-connection', 'Waiting');
        set('system-device-status', 'Tidak Aktif');
        set('system-gps-lock', 'Belum Terkunci');
        renderTables();
        return;
    }

    set('connection-label', 'Online');
    set('stream-status', hasCoordinates ? 'Connectivity stable' : 'Receiving data, waiting for GPS fix');
    set('lat', hasCoordinates ? `${formatNumber(latest.latitude, 6)}` : '-');
    set('lng', hasCoordinates ? `${formatNumber(latest.longitude, 6)}` : '-');
    set('timestamp', formatTime(latest.recorded_at));
    set('device-id', latest.device_id ?? '-');
    set('history-device', latest.device_id ?? '-');
    set('logging-device', latest.device_id ?? '-');
    set('speed', formatSpeed(latest));
    set('sat', latest.satellites ?? '-');
    set('hdop', formatNumber(latest.hdop));
    set('system-connection', hasCoordinates ? 'GPS Fixed' : 'No Fix');
    set('system-sat', latest.satellites ?? '-');
    set('system-update', formatTime(latest.recorded_at));
    set('operational-insight', readings.length
        ? `This tracker has ${reliability}% valid coordinate reports across the latest ${readings.length} logs. Pending rows indicate GPS data received before a satellite fix.`
        : 'Waiting for GPS readings to calculate route reliability and reporting quality.'
    );

    ensureMap(data.coordinates);
    renderTables();

    if (activeView === 'history') {
        ensureHistoryMap();
    }
};

const viewUrls = {
    dashboard: '/',
    history: '/tracking-history',
    logging: '/data-logging',
    system: '/system-status'
};

const showView = (view, pushState = true) => {
    activeView = view;

    document.querySelectorAll('.view').forEach((element) => {
        element.classList.toggle('active', element.id === `${view}-view`);
    });

    document.querySelectorAll('[data-view-link]').forEach((element) => {
        element.classList.toggle('active', element.dataset.viewLink === view);
    });

    if (view === 'history') {
        setTimeout(() => ensureHistoryMap(true), 50);
    }

    if (view === 'dashboard' && map) {
        setTimeout(() => map.invalidateSize(), 50);
    }

    if (pushState && viewUrls[view] && window.location.pathname !== viewUrls[view]) {
        window.history.pushState({ view }, '', viewUrls[view]);
    }
};

const refreshData = async () => {
    if (isRefreshing) {
        return;
    }

    isRefreshing = true;

    try {
        const response = await fetch('/gps-readings', {
            headers: { 'Accept': 'application/json' },
        });

        if (!response.ok) {
            throw new Error('Failed to refresh GPS data');
        }

        render(await response.json());
    } catch (error) {
        set('stream-status', 'Unable to refresh GPS data');
        set('system-connection', 'Offline');
        set('system-device-status', 'Tidak Aktif');
        set('system-gps-lock', 'Belum Terkunci');
        const devBadge = document.getElementById('device-status-badge');
        if (devBadge) {
            devBadge.className = 'status-badge device-offline';
            devBadge.querySelector('.label').textContent = 'Server Offline';
        }
    } finally {
        isRefreshing = false;
    }
};

// --- Event Listeners ---

document.querySelectorAll('[data-view-link]').forEach((button) => {
    button.addEventListener('click', () => showView(button.dataset.viewLink));
});

document.querySelectorAll('[data-dashboard-map-mode]').forEach((button) => {
    button.addEventListener('click', () => applyDashboardMapMode(button.dataset.dashboardMapMode));
});

document.querySelectorAll('[data-history-map-mode]').forEach((button) => {
    button.addEventListener('click', () => applyHistoryMapMode(button.dataset.historyMapMode));
});

const handleHistoryFilterChange = () => {
    historyCurrentPage = 1;
    renderTables();
    ensureHistoryMap(true);
};

document.getElementById('history-search').addEventListener('click', handleHistoryFilterChange);
document.getElementById('history-status').addEventListener('change', handleHistoryFilterChange);
document.getElementById('log-filter').addEventListener('change', () => {
    logCurrentPage = 1;
    renderTables();
});

// Pagination Event Listeners
document.getElementById('history-prev-btn')?.addEventListener('click', () => {
    if (historyCurrentPage > 1) {
        historyCurrentPage--;
        renderTables();
    }
});

document.getElementById('history-next-btn')?.addEventListener('click', () => {
    const historyReadings = filteredReadings();
    const historyTotalPages = Math.max(1, Math.ceil(historyReadings.length / itemsPerPage));
    if (historyCurrentPage < historyTotalPages) {
        historyCurrentPage++;
        renderTables();
    }
});

document.getElementById('log-prev-btn')?.addEventListener('click', () => {
    if (logCurrentPage > 1) {
        logCurrentPage--;
        renderTables();
    }
});

document.getElementById('log-next-btn')?.addEventListener('click', () => {
    const logFilter = document.getElementById('log-filter')?.value ?? 'all';
    const allLogs = (appData.readings || [])
        .filter((reading) => {
            if (logFilter === 'all') return true;
            const isOffline = reading.offline === true || reading.offline === 1 || reading.offline === '1';
            return logFilter === 'offline' ? isOffline : !isOffline;
        });
    const logTotalPages = Math.max(1, Math.ceil(allLogs.length / itemsPerPage));
    if (logCurrentPage < logTotalPages) {
        logCurrentPage++;
        renderTables();
    }
});

document.getElementById('force-sync').addEventListener('click', refreshData);
document.getElementById('export-logs').addEventListener('click', () => {
    const offlineReadings = (appData.readings || []).filter(reading => reading.offline == true);
    const blob = new Blob([JSON.stringify(offlineReadings, null, 2)], { type: 'application/json' });
    const link = document.createElement('a');
    link.href = URL.createObjectURL(blob);
    link.download = 'gps-logs.json';
    link.click();
    URL.revokeObjectURL(link.href);
});

// --- Init ---
if (typeof initialData !== 'undefined' && initialData.activeView) {
    activeView = initialData.activeView;
}
render(initialData);
showView(activeView, false);
syncDashboardMapControls();
syncHistoryMapControls();
setInterval(refreshData, 2000);

window.addEventListener('popstate', (event) => {
    const view = event.state?.view || 'dashboard';
    showView(view, false);
});

if (window.lucide) {
    lucide.createIcons();
}
