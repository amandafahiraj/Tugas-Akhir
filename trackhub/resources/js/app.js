import './bootstrap';
// import L from 'leaflet';
// import 'leaflet/dist/leaflet.css';

// const dashboard = document.querySelector('#gps-dashboard');

// if (dashboard) {
//     const dashboardData = document.querySelector('#gps-dashboard-data');
//     let initialData = {};

//     try {
//         initialData = dashboardData ? JSON.parse(dashboardData.textContent) : {};
//     } catch (error) {
//         initialData = {};
//     }

//     const state = {
//         latest: initialData.latest || null,
//         readings: initialData.readings || [],
//         streamUrl: dashboard.dataset.streamUrl,
//         stream: null,
//     };

//     const elements = {
//         connectionState: document.querySelector('#connection-state'),
//         deviceId: document.querySelector('#device-id'),
//         speed: document.querySelector('#speed'),
//         satellites: document.querySelector('#satellites'),
//         lastUpdate: document.querySelector('#last-update'),
//         latitude: document.querySelector('#latitude'),
//         longitude: document.querySelector('#longitude'),
//         altitude: document.querySelector('#altitude'),
//         hdop: document.querySelector('#hdop'),
//         rawNmea: document.querySelector('#raw-nmea'),
//         mapEmpty: document.querySelector('#map-empty'),
//         map: document.querySelector('#map'),
//         readingsTable: document.querySelector('#readings-table'),
//     };

//     const mapState = {
//         instance: null,
//         marker: null,
//     };

//     const formatNumber = (value, digits = 2) => {
//         if (value === null || value === undefined || value === '') {
//             return '-';
//         }

//         return Number(value).toFixed(digits);
//     };

//     const formatTime = (value) => {
//         if (!value) {
//             return '-';
//         }

//         return new Intl.DateTimeFormat(undefined, {
//             hour: '2-digit',
//             minute: '2-digit',
//             second: '2-digit',
//             day: '2-digit',
//             month: 'short',
//         }).format(new Date(value));
//     };

//     const hasCoordinates = (reading) => reading?.latitude !== null
//         && reading?.latitude !== undefined
//         && reading?.longitude !== null
//         && reading?.longitude !== undefined;

//     const updateMap = (reading) => {
//         if (!elements.map || !elements.mapEmpty) {
//             return;
//         }

//         try {
//             if (!hasCoordinates(reading)) {
//                 elements.map.classList.add('hidden');
//                 elements.mapEmpty.classList.remove('hidden');
//                 return;
//             }

//             const latitude = Number(reading.latitude);
//             const longitude = Number(reading.longitude);
//             const nextPosition = [latitude, longitude];

//             elements.mapEmpty.classList.add('hidden');
//             elements.map.classList.remove('hidden');

//             if (!mapState.instance) {
//                 mapState.instance = L.map(elements.map, {
//                     zoomControl: true,
//                 }).setView(nextPosition, 16);

//                 L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
//                     attribution: '&copy; OpenStreetMap contributors',
//                     maxZoom: 19,
//                 }).addTo(mapState.instance);

//                 mapState.marker = L.marker(nextPosition).addTo(mapState.instance);
//             } else {
//                 mapState.marker.setLatLng(nextPosition);
//                 mapState.instance.panTo(nextPosition, {
//                     animate: true,
//                     duration: 0.5,
//                 });
//             }

//             window.setTimeout(() => mapState.instance.invalidateSize(), 0);
//         } catch (error) {
//             elements.mapEmpty.textContent = 'Map failed to initialize.';
//             elements.mapEmpty.classList.remove('hidden');
//             elements.map.classList.add('hidden');
//         }
//     };

//     const renderLatest = () => {
//         const reading = state.latest;

//         if (!reading) {
//             elements.connectionState.textContent = 'Waiting for data';
//             updateMap(null);
//             return;
//         }

//         elements.connectionState.textContent = hasCoordinates(reading) ? 'Live coordinates' : 'Receiving GPS stream';
//         elements.deviceId.textContent = reading.device_id || '-';
//         elements.speed.textContent = formatNumber(reading.speed_kmph);
//         elements.satellites.textContent = reading.satellites ?? '-';
//         elements.lastUpdate.textContent = formatTime(reading.recorded_at || reading.created_at);
//         elements.latitude.textContent = formatNumber(reading.latitude, 7);
//         elements.longitude.textContent = formatNumber(reading.longitude, 7);
//         elements.altitude.textContent = formatNumber(reading.altitude_m);
//         elements.hdop.textContent = formatNumber(reading.hdop);
//         elements.rawNmea.textContent = reading.raw_nmea || '-';
//         updateMap(reading);
//     };

//     const renderTable = () => {
//         if (!state.readings.length) {
//             elements.readingsTable.innerHTML = '<tr><td class="px-5 py-6 text-zinc-400" colspan="6">No readings stored yet.</td></tr>';
//             return;
//         }

//         elements.readingsTable.innerHTML = state.readings.map((reading) => `
//             <tr>
//                 <td class="whitespace-nowrap px-5 py-3">${formatTime(reading.recorded_at || reading.created_at)}</td>
//                 <td class="whitespace-nowrap px-5 py-3">${reading.device_id || '-'}</td>
//                 <td class="whitespace-nowrap px-5 py-3">${formatNumber(reading.latitude, 7)}</td>
//                 <td class="whitespace-nowrap px-5 py-3">${formatNumber(reading.longitude, 7)}</td>
//                 <td class="whitespace-nowrap px-5 py-3">${formatNumber(reading.speed_kmph)}</td>
//                 <td class="whitespace-nowrap px-5 py-3">${reading.satellites ?? '-'}</td>
//             </tr>
//         `).join('');
//     };

//     const receiveReading = (reading) => {
//         state.latest = reading;
//         state.readings = [
//             reading,
//             ...state.readings.filter((item) => item.id !== reading.id),
//         ].slice(0, 50);

//         renderLatest();
//         renderTable();
//     };

//     const connectStream = () => {
//         if (!window.EventSource) {
//             elements.connectionState.textContent = 'Live stream unsupported';
//             return;
//         }

//         const lastId = state.latest?.id || 0;
//         const streamUrl = new URL(state.streamUrl, window.location.href);
//         streamUrl.searchParams.set('last_id', lastId);

//         state.stream = new EventSource(streamUrl);

//         state.stream.addEventListener('open', () => {
//             elements.connectionState.textContent = state.latest ? 'Live stream connected' : 'Waiting for data';
//         });

//         state.stream.addEventListener('gps-reading', (event) => {
//             receiveReading(JSON.parse(event.data));
//         });

//         state.stream.addEventListener('error', () => {
//             elements.connectionState.textContent = 'Live stream reconnecting';
//         });

//         window.addEventListener('beforeunload', () => {
//             state.stream?.close();
//         }, { once: true });
//     };

//     const loadInitialData = async () => {
//         try {
//             const response = await fetch('/api/gps-readings', {
//                 headers: { Accept: 'application/json' },
//             });

//             if (!response.ok) {
//                 throw new Error(`HTTP ${response.status}`);
//             }

//             const payload = await response.json();
//             state.readings = payload.data || [];
//             state.latest = state.readings[0] || null;
//         } catch (error) {
//             elements.connectionState.textContent = 'Initial GPS data unavailable';
//         }
//     };

//     const bootFromInitialState = async () => {
//         if (!state.latest && !state.readings.length) {
//             await loadInitialData();
//         }

//         if (state.latest) {
//             renderLatest();
//         } else {
//             elements.connectionState.textContent = 'Waiting for data';
//             updateMap(null);
//         }

//         renderTable();
//         connectStream();
//     };

//     bootFromInitialState();
// }
