<?php

namespace App\Http\Controllers;

use App\Models\GpsReading;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class SystemStatusController extends Controller
{
    public function index(Request $request): View
    {
        $gpsController = app(GpsReadingController::class);
        $data = $gpsController->dashboardData($request);
        $data['activeView'] = 'system';
        return view('dashboard.index', $data);
    }

    public function health(): JsonResponse
    {
        $dbConnected = false;
        try {
            DB::connection()->getPdo();
            $dbConnected = true;
        } catch (\Exception $e) {
            // failed
        }

        $latestReading = GpsReading::latest('recorded_at')->first();
        $isTrackingActive = false;
        if ($latestReading && $latestReading->recorded_at->gt(now()->subMinutes(5))) {
            $isTrackingActive = true;
        }

        return response()->json([
            'status' => 'ok',
            'database' => $dbConnected ? 'connected' : 'disconnected',
            'gps_receiver' => $isTrackingActive ? 'active' : 'idle',
            'total_logs' => GpsReading::count(),
            'latest_entry' => $latestReading ? $latestReading->recorded_at->toDateTimeString() : null,
            'php_version' => PHP_VERSION,
            'laravel_version' => app()->version(),
        ]);
    }
}
