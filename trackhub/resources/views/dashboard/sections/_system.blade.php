{{--
    Section: System Status
    Berisi: 4 kartu status sistem (connection, total logs, satellites, last update).
--}}
<section id="system-view" class="view">
    <div class="page-head">
        <div>
            <h1>System Status</h1>
            <p>Health snapshot for ESP32 GPS tracker and Laravel receiver.</p>
        </div>
    </div>

    <div class="system-grid">
        <div class="system-card">
            <i data-lucide="wifi"></i>
            <small>Device Status</small>
            <strong id="system-device-status">-</strong>
        </div>
        <div class="system-card">
            <i data-lucide="navigation"></i>
            <small>GPS Lock</small>
            <strong id="system-gps-lock">-</strong>
        </div>
        <div class="system-card">
            <i data-lucide="satellite"></i>
            <small>Satellites</small>
            <strong id="system-sat">-</strong>
        </div>
        <div class="system-card">
            <i data-lucide="database"></i>
            <small>Total Logs</small>
            <strong id="system-total">0</strong>
        </div>
        <div class="system-card">
            <i data-lucide="clock"></i>
            <small>Last Update</small>
            <strong id="system-update">-</strong>
        </div>
    </div>
</section>
