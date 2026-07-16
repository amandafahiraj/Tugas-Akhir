{{--
    Dashboard Index — Entry point utama untuk GPS Tracker dashboard.
    Extends layout app, meng-include semua section sebagai partials,
    dan inject data PHP ke JavaScript via @push('scripts').
--}}
@extends('layouts.app')

@section('title', 'Monitoring Tracking Kendaraan')

@section('content')
    @include('dashboard.sections._live_map')
    @include('dashboard.sections._history')
    @include('dashboard.sections._logging')
    @include('dashboard.sections._system')
@endsection

@push('scripts')
<script>
    /**
     * Data awal dari server (PHP → JavaScript).
     * Diinjeksikan sebelum dashboard.js dijalankan,
     * sehingga dashboard.js bisa langsung menggunakan `initialData`.
     */
    const initialData = {
        latest: @json($latest),
        coordinates: @json($coordinates),
        readings: @json($readings),
        tracking_path: @json($tracking_path),
        total: @json($total),
        tracked_devices: @json($tracked_devices),
        activeView: @json($activeView ?? 'dashboard'),
    };
</script>
@endpush
