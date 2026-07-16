<?php

namespace App\Http\Controllers;

use App\Models\GpsReading;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DataLoggingController extends Controller
{
    public function index(Request $request): View
    {
        $gpsController = app(GpsReadingController::class);
        $data = $gpsController->dashboardData($request);
        $data['activeView'] = 'logging';
        return view('dashboard.index', $data);
    }

    public function export(): StreamedResponse
    {
        $response = new StreamedResponse(function () {
            $handle = fopen('php://output', 'w');
            
            // Add CSV Headers
            fputcsv($handle, [
                'ID', 'Device ID', 'Latitude', 'Longitude', 'Altitude (m)', 
                'Speed (km/h)', 'Satellites', 'HDOP', 'Offline', 'Recorded At'
            ]);

            GpsReading::chunk(200, function ($readings) use ($handle) {
                foreach ($readings as $reading) {
                    fputcsv($handle, [
                        $reading->id,
                        $reading->device_id,
                        $reading->latitude,
                        $reading->longitude,
                        $reading->altitude_m,
                        $reading->speed_kmph,
                        $reading->satellites,
                        $reading->hdop,
                        $reading->offline ? 'Yes' : 'No',
                        $reading->recorded_at->toDateTimeString()
                    ]);
                }
            });

            fclose($handle);
        }, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="gps_readings_export_' . now()->format('Ymd_His') . '.csv"',
        ]);

        return $response;
    }

    public function clear(): JsonResponse
    {
        GpsReading::truncate();

        return response()->json([
            'message' => 'All GPS records cleared successfully'
        ]);
    }
}
