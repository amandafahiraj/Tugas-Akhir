{{--
    Section: Live Map (Dashboard View)
    Ditampilkan sebagai halaman default saat dashboard dibuka.
    Berisi: peta Leaflet real-time, Vehicle Details card, dan bottom metrics.
--}}
<section id="dashboard-view" class="view active">
    <div class="hero-map">
        <div id="map">
            <div class="map-empty">Waiting for ESP32 coordinates...</div>
        </div>

        {{-- Map Layer Switcher --}}
        <div class="map-controls">
            <button class="map-control-btn active" type="button" data-dashboard-map-mode="standard">
                <i data-lucide="map"></i>Map
            </button>
            <button class="map-control-btn" type="button" data-dashboard-map-mode="satellite">
                <i data-lucide="satellite"></i>Satellite
            </button>
        </div>

        {{-- Vehicle Details Card --}}
        <div class="floating-card details-card">
            <div class="details-head">
                <i data-lucide="info"></i>
                <h2>Vehicle Details</h2>
                <span class="live-pill">LIVE</span>
            </div>
            <div class="details-body">
                <div class="coord-grid">
                    <div class="coord-box">
                        <span>Latitude</span>
                        <strong id="lat">-</strong>
                    </div>
                    <div class="coord-box">
                        <span>Longitude</span>
                        <strong id="lng">-</strong>
                    </div>
                </div>
                <div class="detail-list">
                    <div class="detail-row">
                        <i data-lucide="clock"></i>
                        <div><small>System Timestamp</small><strong id="timestamp">-</strong></div>
                    </div>
                    <div class="detail-row">
                        <i data-lucide="map-pinned"></i>
                        <div><small>Address</small><strong id="address">-</strong></div>
                    </div>
                    <div class="detail-row">
                        <i data-lucide="fingerprint"></i>
                        <div><small>Device ID</small><strong id="device-id">-</strong></div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Bottom Metric Cards --}}
        <div class="bottom-metrics">
            <div class="metric-card">
                <div class="metric-icon"><i data-lucide="gauge"></i></div>
                <div><span>Speed</span><strong id="speed">-</strong></div>
            </div>
            <div class="metric-card">
                <div class="metric-icon"><i data-lucide="satellite"></i></div>
                <div><span>Satellites</span><strong id="sat">-</strong></div>
            </div>
            <div class="metric-card">
                <div class="metric-icon"><i data-lucide="activity"></i></div>
                <div><span>HDOP</span><strong id="hdop">-</strong></div>
            </div>
            <div class="metric-card">
                <div class="metric-icon"><i data-lucide="database"></i></div>
                <div><span>Total Logs</span><strong id="total">0</strong></div>
            </div>
        </div>
    </div>
</section>
