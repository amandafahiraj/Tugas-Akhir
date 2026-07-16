<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Dashboard monitoring dan tracking kendaraan secara real-time menggunakan GPS ESP32.">
    <title>@yield('title', 'Monitoring Tracking Kendaraan')</title>

    {{-- Leaflet Maps --}}
    <link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css"/>
    <script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>

    {{-- Lucide Icons --}}
    <script src="https://unpkg.com/lucide@latest"></script>

    {{-- Dashboard Stylesheet --}}
    <link rel="stylesheet" href="{{ asset('css/dashboard.css') }}">

    @stack('styles')
</head>
<body>

    {{-- Top Navigation Bar --}}
    @include('partials._topbar')

    <div class="shell">
        {{-- Sidebar Navigation --}}
        @include('partials._sidebar')

        <main>
            @yield('content')
        </main>
    </div>

    {{-- Inject PHP data as JS variable BEFORE dashboard.js loads --}}
    @stack('scripts')

    {{-- Dashboard JavaScript --}}
    <script src="{{ asset('js/dashboard.js') }}"></script>

</body>
</html>
