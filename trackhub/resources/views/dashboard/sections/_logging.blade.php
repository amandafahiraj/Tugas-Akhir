{{--
    Section: Data Logging Monitoring
    Berisi: storage card MicroSD, data log queue table.
--}}
<section id="logging-view" class="view">
    <div class="page-head">
        <div>
            <h1>Data Logging Monitoring</h1>
            <p>Real-time store-and-forward status for vehicle ID: <strong id="logging-device">-</strong></p>
        </div>
        <div style="display: flex; gap: 14px;">
            <button class="outline-btn" id="force-sync" type="button">
                <i data-lucide="refresh-cw"></i>Force Sync
            </button>
            <button class="primary-btn" id="export-logs" type="button">
                <i data-lucide="download"></i>Export Logs
            </button>
        </div>
    </div>

    <div class="logging-layout">
        {{-- MicroSD Storage Card --}}
        <div class="storage-card">
            <h2>MicroSD Storage</h2>
            <p>Current storage status for offline logging during network outage.</p>
            <div style="display: flex; justify-content: space-between; margin: 28px 0 10px;">
                <strong>Storage Capacity</strong>
                <span id="storage-caption">0 MB / 32 MB</span>
            </div>
            <div class="progress"><span id="storage-bar"></span></div>
            <div class="mini-grid">
                <div class="mini-stat">
                    <small>Log Offline</small>
                    <strong id="mini-pending" style="color: var(--amber);">0</strong>
                    <span>Tersinkronisasi (SD)</span>
                </div>
                <div class="mini-stat">
                    <small>Log Real-time</small>
                    <strong id="mini-sent" style="color: #079455;">0</strong>
                    <span>Terkirim Langsung</span>
                </div>
            </div>
        </div>

        {{-- Data Log Queue Table --}}
        <div class="panel">
            <div class="panel-head">
                <h2>Data Log Queue</h2>
                <select id="log-filter">
                    <option value="all">Tipe: Semua</option>
                    <option value="realtime">Log Real-time</option>
                    <option value="offline" selected>Log Offline (SD)</option>
                </select>
            </div>
            <table>
                <thead>
                <tr>
                    <th>Log ID</th>
                    <th>Capture Time</th>
                    <th>Status</th>
                    <th>Sync Timestamp</th>
                    <th>Size</th>
                </tr>
                </thead>
                <tbody id="log-table"></tbody>
            </table>
            {{-- Pagination Controls --}}
            <div class="pagination" id="log-pagination" style="display: flex; justify-content: space-between; align-items: center; padding: 14px 28px; border-top: 1px solid #e6ebf4;">
                <button class="outline-btn" id="log-prev-btn" style="height: 36px; padding: 0 14px; font-size: 13px;">Sebelumnya</button>
                <span id="log-page-info" style="font-size: 14px; color: var(--muted); font-weight: 500;">Halaman 1 dari 1</span>
                <button class="outline-btn" id="log-next-btn" style="height: 36px; padding: 0 14px; font-size: 13px;">Selanjutnya</button>
            </div>
        </div>
    </div>
</section>
