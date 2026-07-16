{{--
    Section: Tracking History
    Berisi: toolbar filter tanggal/status, tabel history, peta rute, dan insight card.
--}}
<section id="history-view" class="view">
    <div class="page-head">
        <div>
            <h1>Tracking History</h1>
            <p>Operational log for Vehicle ID: <strong id="history-device">-</strong></p>
        </div>

    </div>

    {{-- Filter Toolbar --}}
    <div class="toolbar">
        <div class="field">
            <label for="start-date">Start Date</label>
            <input id="start-date" type="date">
        </div>
        <div class="field">
            <label for="end-date">End Date</label>
            <input id="end-date" type="date">
        </div>
        <div class="field">
            <label for="history-status">Status Filter</label>
            <select id="history-status">
                <option value="all">Semua Status GPS</option>
                <option value="sent">GPS Terkunci (Fixed)</option>
                <option value="pending">GPS Belum Terkunci</option>
            </select>
        </div>
        <button class="primary-btn" id="history-search" type="button">
            <i data-lucide="search"></i>Search
        </button>
    </div>



    {{-- History Table --}}
    <div class="panel">
        <table>
            <thead>
            <tr>
                <th>Timestamp</th>
                <th>Latitude</th>
                <th>Longitude</th>
                <th>Status</th>
            </tr>
            </thead>
            <tbody id="history-table"></tbody>
        </table>
    </div>

    {{-- Route Map (full width) --}}
    <div class="history-map-card" style="margin-top: 20px;">
        <div id="history-map"></div>
        <div class="map-controls">
            <button class="map-control-btn active" type="button" data-history-map-mode="standard">
                <i data-lucide="map"></i>Map
            </button>
            <button class="map-control-btn" type="button" data-history-map-mode="satellite">
                <i data-lucide="satellite"></i>Satellite
            </button>
        </div>
    </div>
</section>
