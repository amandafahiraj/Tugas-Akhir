<?php

namespace App\Http\Controllers;

use App\Models\GpsReading;
use App\Services\GpsReadingIngestor;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class GpsReadingController extends Controller
{
    public function dashboard(Request $request): View
    {
        $data = $this->dashboardData($request);
        $data['activeView'] = 'dashboard';
        return view('dashboard.index', $data);
    }

    public function index(Request $request): JsonResponse
    {
        return response()->json($this->dashboardData($request));
    }

    public function stream(): StreamedResponse
    {
        return response()->stream(function (): void {
            set_time_limit(0);
            $lastReadingId = -1;

            while (! connection_aborted()) {
                $latest = GpsReading::latest('recorded_at')->latest()->first();

                if ($latest?->id !== $lastReadingId) {
                    $lastReadingId = $latest?->id;

                    echo 'event: gps-update'."\n";
                    echo 'data: '.json_encode($this->dashboardData())."\n\n";
                    if (ob_get_level() > 0) {
                        ob_flush();
                    }
                    flush();
                }

                sleep(1);
            }
        }, 200, [
            'Access-Control-Allow-Origin' => '*',
            'Content-Type' => 'text/event-stream',
            'Cache-Control' => 'no-cache',
            'Connection' => 'keep-alive',
            'X-Accel-Buffering' => 'no',
        ]);
    }

    public function store(Request $request, GpsReadingIngestor $ingestor): JsonResponse
    {
        $reading = $ingestor->ingest($request->all());

        return response()->json([
            'message' => 'GPS reading received',
            'data' => $reading,
        ], 201);
    }

    /**
     * @return array<string, mixed>
     */
    public function dashboardData(?Request $request = null): array
    {
        $readingsQuery = GpsReading::query();
        $trackingPathQuery = GpsReading::whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->latest('recorded_at');

        if ($request?->filled('start_date')) {
            $startDate = Carbon::parse((string) $request->string('start_date'))->startOfDay();
            $readingsQuery->where('recorded_at', '>=', $startDate);
            $trackingPathQuery->where('recorded_at', '>=', $startDate);
        }

        if ($request?->filled('end_date')) {
            $endDate = Carbon::parse((string) $request->string('end_date'))->endOfDay();
            $readingsQuery->where('recorded_at', '<=', $endDate);
            $trackingPathQuery->where('recorded_at', '<=', $endDate);
        }

        $latest = (clone $readingsQuery)->latest('recorded_at')->latest()->first()
            ?? GpsReading::latest('recorded_at')->latest()->first();

        // Ambil data heartbeat dari cache untuk mendeteksi update ketika GPS belum lock (koordinat null)
        $cachedHeartbeat = cache('last_heartbeat_esp32-gps-01');
        if ($cachedHeartbeat) {
            $cachedReading = new GpsReading($cachedHeartbeat);
            // Gunakan data cache jika ia lebih baru daripada data di database
            if (!$latest || Carbon::parse($cachedReading->recorded_at)->gt($latest->recorded_at)) {
                $latest = $cachedReading;
            }
        }

        $readings = $readingsQuery->latest('recorded_at')->latest()->take(10000)->get();
        $trackingPath = $trackingPathQuery
            ->latest()
            ->take(10000)
            ->get(['latitude', 'longitude', 'recorded_at'])
            ->reverse()
            ->map(fn (GpsReading $reading): array => [
                'latitude' => (float) $reading->latitude,
                'longitude' => (float) $reading->longitude,
                'recorded_at' => $reading->recorded_at,
            ])
            ->values();

        return [
            'latest' => $latest,
            'coordinates' => $this->coordinates($latest),
            'readings' => $readings,
            'tracking_path' => $trackingPath,
            'total' => GpsReading::count(),
            'tracked_devices' => GpsReading::distinct('device_id')->count('device_id'),
            'server_time' => now()->toIso8601String(),
        ];
    }

    /**
     * @return array{0: float, 1: float}|null
     */
    private function coordinates(?GpsReading $reading): ?array
    {
        if (! $reading || $reading->latitude === null || $reading->longitude === null) {
            return null;
        }

        return [(float) $reading->latitude, (float) $reading->longitude];
    }
}
