<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Monitoring Tracking Kendaraan</title>

    <link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css"/>
    <script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>
    <script src="https://unpkg.com/lucide@latest"></script>

    <style>
        :root {
            font-family: Inter, ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
            --blue: #003f91;
            --blue-strong: #003280;
            --blue-soft: #eaf2ff;
            --ink: #0f1f38;
            --muted: #61708a;
            --line: #c7d1e2;
            --panel: #ffffff;
            --bg: #f5f7fc;
            --green: #12b76a;
            --amber: #f59e0b;
            --red: #d92d20;
        }

        * { box-sizing: border-box; }

        body {
            margin: 0;
            background: var(--bg);
            color: var(--ink);
        }

        button, input, select {
            font: inherit;
        }

        .topbar {
            position: sticky;
            top: 0;
            z-index: 40;
            height: 72px;
            display: flex;
            align-items: center;
            gap: 28px;
            padding: 0 28px;
            background: #fff;
            border-bottom: 1px solid #dce3ef;
            box-shadow: 0 1px 4px rgba(16, 32, 64, 0.06);
        }

        .brand {
            min-width: 205px;
            font-size: 20px;
            font-weight: 800;
            color: #003aa5;
        }

        .top-nav {
            display: flex;
            height: 100%;
            align-items: center;
            gap: 26px;
        }

        .top-link {
            height: 100%;
            display: grid;
            place-items: center;
            border: 0;
            border-bottom: 3px solid transparent;
            background: transparent;
            color: #52617a;
            cursor: pointer;
        }

        .top-link.active {
            color: #0040d8;
            border-bottom-color: #0040ff;
            font-weight: 700;
        }

        .shell {
            display: grid;
            grid-template-columns: 295px minmax(0, 1fr);
            min-height: calc(100vh - 72px);
        }

        .sidebar {
            display: flex;
            flex-direction: column;
            background: #f8fbff;
            border-right: 1px solid #dce3ef;
        }

        .manager {
            display: flex;
            align-items: center;
            gap: 14px;
            padding: 28px;
            border-bottom: 1px solid #e3e9f3;
        }

        .manager-icon {
            width: 48px;
            height: 48px;
            display: grid;
            place-items: center;
            color: #fff;
            background: #00479c;
            border-radius: 8px;
        }

        .manager strong {
            display: block;
            color: #003aa5;
        }

        .manager span {
            display: block;
            margin-top: 4px;
            color: #60708c;
            font-size: 12px;
            letter-spacing: 0.14em;
        }

        .side-nav {
            padding: 18px 0;
        }

        .side-link {
            width: 100%;
            display: flex;
            align-items: center;
            gap: 14px;
            padding: 16px 24px;
            border: 0;
            border-right: 4px solid transparent;
            background: transparent;
            color: #1f334f;
            text-align: left;
            cursor: pointer;
        }

        .side-link.active {
            border-right-color: #0040ff;
            background: #eaf2ff;
            color: #0040ff;
        }

        main {
            min-width: 0;
            padding: 28px;
        }

        .view {
            display: none;
        }

        .view.active {
            display: block;
        }

        .hero-map {
            position: relative;
            min-height: 680px;
            overflow: hidden;
            border: 1px solid #1f3a5e;
            border-radius: 12px;
            background: #dfe8f3;
            box-shadow: 0 18px 50px rgba(10, 28, 52, 0.22);
        }

        #map {
            position: absolute;
            inset: 0;
            z-index: 1;
            min-height: 680px;
            background: #dfe8f3;
        }

        .hero-map::after {
            content: "";
            position: absolute;
            inset: 0;
            z-index: 2;
            pointer-events: none;
            background: linear-gradient(90deg, rgba(255, 255, 255, 0.12), transparent 45%, rgba(255, 255, 255, 0.08));
        }

        .map-empty {
            position: absolute;
            inset: 0;
            z-index: 5;
            display: grid;
            place-items: center;
            padding: 28px;
            color: #52617a;
            text-align: center;
            background: #eef4fb;
        }

        .map-controls {
            position: absolute;
            left: 30px;
            bottom: 132px;
            z-index: 9;
            display: inline-flex;
            gap: 8px;
            padding: 8px;
            border: 1px solid rgba(255, 255, 255, 0.72);
            border-radius: 10px;
            background: rgba(255, 255, 255, 0.9);
            box-shadow: 0 10px 26px rgba(15, 31, 56, 0.18);
        }

        .map-control-btn {
            min-width: 44px;
            height: 36px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            padding: 0 12px;
            border: 1px solid transparent;
            border-radius: 7px;
            background: transparent;
            color: #1f334f;
            font-size: 13px;
            font-weight: 800;
            cursor: pointer;
        }

        .map-control-btn.active {
            border-color: #0f9f63;
            background: #e7f8ef;
            color: #057a47;
        }

        .floating-card {
            position: absolute;
            z-index: 8;
            background: rgba(255, 255, 255, 0.92);
            border: 1px solid rgba(255, 255, 255, 0.75);
            border-radius: 12px;
            box-shadow: 0 14px 36px rgba(0, 0, 0, 0.22);
        }

        .status-card {
            top: 30px;
            left: 30px;
            min-width: 290px;
            padding: 20px;
            display: flex;
            align-items: center;
            gap: 14px;
        }

        .pulse {
            width: 18px;
            height: 18px;
            border-radius: 50%;
            background: var(--green);
            box-shadow: 0 0 0 8px rgba(18, 183, 106, 0.14);
        }

        .status-card strong {
            display: block;
            font-size: 24px;
        }

        .status-card span {
            color: var(--muted);
        }

        .active-pill {
            margin-left: auto;
            padding: 7px 10px;
            border-radius: 6px;
            background: #e8faee;
            color: #087a35;
            font-size: 12px;
            font-weight: 800;
        }

        .details-card {
            top: 30px;
            right: 30px;
            width: 395px;
            overflow: hidden;
        }

        .details-head {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 18px 24px;
            background: #064692;
            color: #fff;
        }

        .details-head h2 {
            margin: 0;
            font-size: 26px;
        }

        .live-pill {
            margin-left: auto;
            padding: 6px 10px;
            border-radius: 5px;
            background: rgba(255, 255, 255, 0.18);
            font-size: 12px;
            font-weight: 800;
            letter-spacing: 0.08em;
        }

        .details-body {
            padding: 24px;
            background: #fff;
        }

        .coord-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 18px;
        }

        .coord-box {
            min-height: 118px;
            padding: 16px;
            border-radius: 8px;
            background: #f0f3fb;
        }

        .coord-box span {
            color: #64748b;
            font-size: 13px;
            font-weight: 800;
            letter-spacing: 0.08em;
        }

        .coord-box strong {
            display: block;
            margin-top: 8px;
            color: #002f86;
            font-size: 26px;
        }

        .detail-list {
            display: grid;
            gap: 14px;
            margin-top: 22px;
            padding-top: 20px;
            border-top: 1px solid #e4e9f2;
        }

        .detail-row {
            display: grid;
            grid-template-columns: 28px 1fr;
            gap: 14px;
            color: #1f334f;
        }

        .detail-row small {
            display: block;
            color: #8190aa;
            font-weight: 800;
            letter-spacing: 0.06em;
            text-transform: uppercase;
        }

        .bottom-metrics {
            position: absolute;
            left: 310px;
            right: 310px;
            bottom: 24px;
            z-index: 8;
            display: grid;
            grid-template-columns: repeat(4, minmax(130px, 1fr));
            gap: 14px;
        }

        .metric-card {
            min-height: 92px;
            display: flex;
            align-items: center;
            gap: 14px;
            padding: 16px;
            background: #fff;
            border: 1px solid #d7dfeb;
            border-radius: 12px;
            box-shadow: 0 10px 24px rgba(0, 0, 0, 0.18);
        }

        .metric-icon {
            width: 48px;
            height: 48px;
            display: grid;
            place-items: center;
            border-radius: 8px;
            background: #eef4ff;
            color: #00479c;
        }

        .metric-card span {
            display: block;
            color: #8390a8;
            font-size: 12px;
            font-weight: 800;
            letter-spacing: 0.06em;
            text-transform: uppercase;
        }

        .metric-card strong {
            display: block;
            margin-top: 4px;
            font-size: 18px;
        }

        .page-head {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 18px;
            margin-bottom: 20px;
        }

        .page-head h1 {
            margin: 0;
            font-size: 38px;
            letter-spacing: 0;
        }

        .page-head p {
            margin: 8px 0 0;
            color: #4b5565;
        }

        .summary-cards {
            display: flex;
            gap: 14px;
        }

        .summary-card {
            min-width: 190px;
            display: flex;
            align-items: center;
            gap: 14px;
            padding: 16px;
            border: 1px solid var(--line);
            border-radius: 8px;
            background: #f6f8fe;
        }

        .toolbar {
            display: grid;
            grid-template-columns: 1fr 1fr 1fr auto;
            gap: 18px;
            margin-bottom: 18px;
            padding: 18px;
            border: 1px solid var(--line);
            border-radius: 10px;
            background: #fff;
        }

        .field label {
            display: block;
            margin-bottom: 6px;
            color: #1f2937;
            font-size: 13px;
            font-weight: 800;
            letter-spacing: 0.08em;
            text-transform: uppercase;
        }

        .field input, .field select {
            width: 100%;
            height: 44px;
            padding: 0 14px;
            border: 1px solid var(--line);
            border-radius: 8px;
            background: #fbfcff;
        }

        .primary-btn {
            align-self: end;
            height: 48px;
            display: inline-flex;
            align-items: center;
            gap: 10px;
            padding: 0 28px;
            border: 1px solid #00347d;
            border-radius: 8px;
            background: #064692;
            color: #fff;
            font-weight: 800;
            cursor: pointer;
        }

        .outline-btn {
            height: 48px;
            display: inline-flex;
            align-items: center;
            gap: 10px;
            padding: 0 20px;
            border: 1px solid #063b91;
            border-radius: 8px;
            background: #fff;
            color: #063b91;
            font-weight: 700;
            cursor: pointer;
        }

        .panel {
            border: 1px solid var(--line);
            border-radius: 12px;
            background: #fff;
            overflow: hidden;
        }

        .panel-head {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
            padding: 20px 28px;
            border-bottom: 1px solid var(--line);
        }

        .panel-head h2 {
            margin: 0;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th, td {
            padding: 18px 28px;
            border-bottom: 1px solid #e6ebf4;
            text-align: left;
            vertical-align: middle;
        }

        th {
            background: #e9f2ff;
            color: #053174;
            font-size: 12px;
            font-weight: 900;
            letter-spacing: 0.08em;
            text-transform: uppercase;
        }

        .badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 5px 10px;
            border-radius: 999px;
            font-size: 12px;
            font-weight: 900;
            text-transform: uppercase;
        }

        .badge.sent {
            background: #e8faee;
            color: #087a35;
        }

        .badge.pending {
            background: #fff5df;
            color: #b54708;
        }

        .dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: currentColor;
        }

        .signal {
            display: inline-flex;
            align-items: end;
            gap: 3px;
            height: 22px;
        }

        .signal i {
            width: 4px;
            border-radius: 4px;
            background: #dbe2ec;
        }

        .signal i:nth-child(1) { height: 8px; }
        .signal i:nth-child(2) { height: 12px; }
        .signal i:nth-child(3) { height: 16px; }
        .signal i:nth-child(4) { height: 21px; }
        .signal.good i { background: #003f91; }
        .signal.mid i:nth-child(-n+2) { background: #f59e0b; }

        .split {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 20px;
            margin-top: 20px;
        }

        .history-map-card {
            min-height: 560px;
            position: relative;
            overflow: hidden;
            border: 1px solid var(--line);
            border-radius: 12px;
            background: #dfe8f3;
        }

        .history-map-card .map-controls {
            left: 14px;
            bottom: 14px;
        }

        #history-map {
            min-height: 560px;
            height: 560px;
            background: #dfe8f3;
        }

        .insight-card {
            min-height: 560px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            padding: 28px;
            border-radius: 12px;
            background: #064692;
            color: #fff;
            overflow: hidden;
            position: relative;
        }

        .insight-card::after {
            content: "";
            position: absolute;
            width: 220px;
            height: 220px;
            right: -70px;
            bottom: -55px;
            border: 22px solid rgba(255, 255, 255, 0.08);
            border-radius: 40px;
            transform: rotate(45deg);
        }

        .logging-layout {
            display: grid;
            grid-template-columns: 0.95fr 1.95fr;
            gap: 20px;
            margin-top: 20px;
        }

        .pipeline {
            margin-top: 18px;
            border: 1px solid var(--line);
            border-radius: 12px;
            background: #fff;
            overflow: hidden;
        }

        .pipeline-head {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 20px 28px;
            background: #e4f0ff;
            border-bottom: 1px solid var(--line);
        }

        .pipeline-body {
            display: grid;
            grid-template-columns: 1fr 1fr 1fr;
            align-items: center;
            gap: 28px;
            padding: 40px;
        }

        .stage {
            text-align: center;
        }

        .stage-icon {
            width: 78px;
            height: 78px;
            display: grid;
            place-items: center;
            margin: 0 auto 16px;
            border-radius: 16px;
            background: #064692;
            color: #fff;
        }

        .stage.muted .stage-icon {
            background: #eef2f7;
            color: #9aa8ba;
        }

        .storage-card {
            min-height: 520px;
            padding: 28px;
            border: 1px solid var(--line);
            border-radius: 12px;
            background: #fff;
        }

        .progress {
            height: 12px;
            border-radius: 999px;
            overflow: hidden;
            background: #edf2f7;
        }

        .progress span {
            display: block;
            height: 100%;
            width: 0%;
            background: #064692;
        }

        .mini-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 18px;
            margin-top: 26px;
        }

        .mini-stat {
            padding: 18px;
            border: 1px solid var(--line);
            border-radius: 8px;
            background: #f5f7fc;
        }

        .mini-stat strong {
            display: block;
            margin: 8px 0 4px;
            font-size: 28px;
        }

        .system-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 18px;
        }

        .system-card {
            padding: 22px;
            border: 1px solid var(--line);
            border-radius: 12px;
            background: #fff;
        }

        .system-card strong {
            display: block;
            margin-top: 14px;
            font-size: 26px;
        }

        @media (max-width: 1180px) {
            .shell { grid-template-columns: 86px minmax(0, 1fr); }
            .brand { min-width: auto; }
            .top-nav { display: none; }
            .manager { justify-content: center; padding: 18px; }
            .manager div:not(.manager-icon), .side-link span { display: none; }
            .side-link { justify-content: center; padding: 18px; }
            .bottom-metrics { left: 24px; right: 24px; grid-template-columns: repeat(2, 1fr); }
            .details-card { position: relative; top: auto; right: auto; width: auto; margin: 20px; }
            .status-card { left: 20px; top: 20px; }
            .hero-map { min-height: 860px; }
            #map { min-height: 860px; }
            .toolbar, .logging-layout, .split { grid-template-columns: 1fr; }
            .system-grid { grid-template-columns: repeat(2, 1fr); }
        }

        @media (max-width: 760px) {
            .topbar { padding: 0 16px; gap: 14px; }
            .shell { grid-template-columns: 1fr; }
            .sidebar { display: none; }
            main { padding: 16px; }
            .hero-map { min-height: 980px; }
            #map { min-height: 980px; }
            .status-card { position: relative; inset: auto; margin: 16px; min-width: 0; }
            .bottom-metrics { position: relative; left: auto; right: auto; bottom: auto; margin: 16px; grid-template-columns: 1fr; }
            .coord-grid, .summary-cards, .system-grid, .pipeline-body { grid-template-columns: 1fr; }
            .page-head { display: block; }
            th, td { padding: 14px; }
        }
    </style>
</head>

<body>
<header class="topbar">
    <div class="brand">Monitoring Tracking Kendaraan</div>
    <nav class="top-nav">
        <button class="top-link active" data-view-link="dashboard">Dashboard</button>
        <button class="top-link" data-view-link="history">Tracking History</button>
        <button class="top-link" data-view-link="logging">Reports</button>
    </nav>
    <div style="margin-left: auto; display: flex; align-items: center; gap: 16px;">
        <span style="font-size: 14px; color: var(--muted); font-weight: 500;">
            Hi, <strong style="color: var(--ink);">{{ auth()->user()->name }}</strong>
        </span>
        <form action="{{ route('logout') }}" method="POST" style="margin: 0;">
            @csrf
            <button type="submit" class="outline-btn" style="height: 38px; padding: 0 16px; font-size: 13px; display: inline-flex; align-items: center; gap: 8px;">
                <i data-lucide="log-out" style="width: 16px; height: 16px;"></i>
                <span>Keluar</span>
            </button>
        </form>
    </div>
</header>

<div class="shell">
    <aside class="sidebar">
        {{-- <div class="manager">
            <div class="manager-icon"><i data-lucide="radio-tower"></i></div>
            <div>
                <strong>Fleet</strong>
                <span>Technical Operations</span>
            </div>
        </div> --}}

        <nav class="side-nav">
            <button class="side-link active" data-view-link="dashboard"><i data-lucide="layout-dashboard"></i><span>Dashboard</span></button>
            <button class="side-link" data-view-link="history"><i data-lucide="history"></i><span>Tracking History</span></button>
            <button class="side-link" data-view-link="logging"><i data-lucide="database"></i><span>Data Logging</span></button>
            <button class="side-link" data-view-link="system"><i data-lucide="broadcast"></i><span>System Status</span></button>
        </nav>

    </aside>

    <main>
        <section id="dashboard-view" class="view active">
            <div class="hero-map">
                <div id="map">
                    <div class="map-empty">Waiting for ESP32 coordinates...</div>
                </div>

                <div class="map-controls">
                    <button class="map-control-btn active" type="button" data-dashboard-map-mode="standard"><i data-lucide="map"></i>Map</button>
                    <button class="map-control-btn" type="button" data-dashboard-map-mode="satellite"><i data-lucide="satellite"></i>Satellite</button>
                </div>

                <div class="floating-card status-card">
                    <div class="pulse"></div>
                    <div>
                        <strong id="connection-label">Online</strong>
                        <span id="stream-status">Connecting live data...</span>
                    </div>
                    <div class="active-pill">ACTIVE</div>
                </div>

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

        <section id="history-view" class="view">
            <div class="page-head">
                <div>
                    <h1>Tracking History</h1>
                    <p>Operational log for Vehicle ID: <strong id="history-device">-</strong></p>
                </div>
                <div class="summary-cards">
                    <div class="summary-card"><i data-lucide="route"></i><div><small>Total Distance</small><strong id="distance-total">0 km</strong></div></div>
                    <div class="summary-card"><i data-lucide="timer"></i><div><small>Active Time</small><strong id="active-time">0 min</strong></div></div>
                </div>
            </div>

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
                <button class="primary-btn" id="history-search" type="button"><i data-lucide="search"></i>Search</button>
            </div>

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

            <div class="split">
                <div class="history-map-card">
                    <div id="history-map"></div>
                    <div class="map-controls">
                        <button class="map-control-btn active" type="button" data-history-map-mode="standard"><i data-lucide="map"></i>Map</button>
                        <button class="map-control-btn" type="button" data-history-map-mode="satellite"><i data-lucide="satellite"></i>Satellite</button>
                    </div>
                </div>
                <div class="insight-card">
                    <div>
                        <i data-lucide="chart-no-axes-combined"></i>
                        <h2>Operational Insight</h2>
                        <p id="operational-insight">Waiting for GPS readings to calculate route reliability and reporting quality.</p>
                    </div>
                    <div>
                        <small>NETWORK RELIABILITY</small>
                        <strong id="network-reliability">-</strong>
                        <div class="progress" style="margin-top: 10px;"><span id="reliability-bar"></span></div>
                    </div>
                </div>
            </div>
        </section>

        <section id="logging-view" class="view">
            <div class="page-head">
                <div>
                    <h1>Data Logging Monitoring</h1>
                    <p>Real-time store-and-forward status for vehicle ID: <strong id="logging-device">-</strong></p>
                </div>
                <div style="display: flex; gap: 14px;">
                    <button class="outline-btn" id="force-sync" type="button"><i data-lucide="refresh-cw"></i>Force Sync</button>
                    <button class="primary-btn" id="export-logs" type="button"><i data-lucide="download"></i>Export Logs</button>
                </div>
            </div>



            <div class="logging-layout">
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

                <div class="panel">
                    <div class="panel-head">
                        <h2>Data Log Queue</h2>
                        <select id="log-filter">
                            <option value="all">Tipe: Semua</option>
                            <option value="realtime">Log Real-time</option>
                            <option value="offline">Log Offline (SD)</option>
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
                </div>
            </div>
        </section>

        <section id="system-view" class="view">
            <div class="page-head">
                <div>
                    <h1>System Status</h1>
                    <p>Health snapshot for ESP32 GPS tracker and Laravel receiver.</p>
                </div>
            </div>
            <div class="system-grid">
                <div class="system-card"><i data-lucide="wifi"></i><small>Connection</small><strong id="system-connection">-</strong></div>
                <div class="system-card"><i data-lucide="database"></i><small>Total Logs</small><strong id="system-total">0</strong></div>
                <div class="system-card"><i data-lucide="satellite"></i><small>Satellites</small><strong id="system-sat">-</strong></div>
                <div class="system-card"><i data-lucide="clock"></i><small>Last Update</small><strong id="system-update">-</strong></div>
            </div>
        </section>
    </main>
</div>

<script>
    const initialData = {
        latest: @json($latest),
        coordinates: @json($coordinates),
        readings: @json($readings),
        tracking_path: @json($tracking_path),
        total: @json($total),
        tracked_devices: @json($tracked_devices),
    };

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
            buildingLayer = null;
            buildingRequestKey = null;
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

    const drawHistoryRoute = (points) => {
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
            if (bounds.isValid()) {
                historyMap.fitBounds(bounds, { padding: [40, 40] });
            }
        } catch (error) {
            console.error('Error drawing history route:', error);
        }
    };

    const ensureHistoryMap = async () => {
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
                return;
            }

            drawHistoryRoute(points);
            const routedPoints = await fetchRoadRoute(points);

            if (activeView === 'history') {
                drawHistoryRoute(routedPoints);
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
        const historyRows = filteredReadings().slice(0, 8);
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

        const logFilter = document.getElementById('log-filter')?.value ?? 'all';
        const logRows = (appData.readings || [])
            .filter((reading) => {
                if (logFilter === 'all') return true;
                const isOffline = reading.offline === true || reading.offline === 1 || reading.offline === '1';
                return logFilter === 'offline' ? isOffline : !isOffline;
            })
            .slice(0, 8);

        document.getElementById('log-table').innerHTML = logRows.length
            ? logRows.map((reading) => {
                const isOffline = reading.offline === true || reading.offline === 1 || reading.offline === '1';
                const hasGps = hasFix(reading);
                const size = Math.max(0.8, JSON.stringify(reading).length / 1024).toFixed(1);
                
                let statusText = isOffline ? 'Offline (Synced)' : 'Real-time';
                let statusClass = isOffline ? 'sent' : 'sent'; // both are sent (green badge)
                
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

        if (!latest) {
            set('connection-label', 'Waiting');
            set('stream-status', 'Waiting for GPS data from ESP32');
            set('system-connection', 'Waiting');
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

    const showView = (view) => {
        activeView = view;

        document.querySelectorAll('.view').forEach((element) => {
            element.classList.toggle('active', element.id === `${view}-view`);
        });

        document.querySelectorAll('[data-view-link]').forEach((element) => {
            element.classList.toggle('active', element.dataset.viewLink === view);
        });

        if (view === 'history') {
            setTimeout(ensureHistoryMap, 50);
        }

        if (view === 'dashboard' && map) {
            setTimeout(() => map.invalidateSize(), 50);
        }
    };

    const refreshData = async () => {
        if (isRefreshing) {
            return;
        }

        isRefreshing = true;

        try {
            const response = await fetch('/api/gps-readings', {
                headers: { 'Accept': 'application/json' },
            });

            if (!response.ok) {
                throw new Error('Failed to refresh GPS data');
            }

            render(await response.json());
        } catch (error) {
            set('stream-status', 'Unable to refresh GPS data');
            set('system-connection', 'Offline');
        } finally {
            isRefreshing = false;
        }
    };

    document.querySelectorAll('[data-view-link]').forEach((button) => {
        button.addEventListener('click', () => showView(button.dataset.viewLink));
    });

    document.querySelectorAll('[data-dashboard-map-mode]').forEach((button) => {
        button.addEventListener('click', () => applyDashboardMapMode(button.dataset.dashboardMapMode));
    });

    document.querySelectorAll('[data-history-map-mode]').forEach((button) => {
        button.addEventListener('click', () => applyHistoryMapMode(button.dataset.historyMapMode));
    });

    //document.getElementById('dashboard-3d-toggle').addEventListener('click', () => {
      //  if (dashboardMapMode === 'satellite') {
        //    applyDashboardMapMode('standard');
        //}

        //dashboard3d = !dashboard3d;
        //syncDashboardMapControls();
        //update3dBuildings();

        //if (map) {
          //  setTimeout(() => map.invalidateSize(), 200);
        //}
    //});

    const handleHistoryFilterChange = () => {
        renderTables();
        ensureHistoryMap();
    };

    document.getElementById('history-search').addEventListener('click', handleHistoryFilterChange);
    document.getElementById('history-status').addEventListener('change', handleHistoryFilterChange);
    document.getElementById('log-filter').addEventListener('change', renderTables);
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

    render(initialData);
    syncDashboardMapControls();
    syncHistoryMapControls();
    setInterval(refreshData, 2000);

    if (window.lucide) {
        lucide.createIcons();
    }
</script>
</body>
</html>
