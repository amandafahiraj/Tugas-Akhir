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
