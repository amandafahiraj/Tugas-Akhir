<?php

namespace App\Http\Controllers;

use App\Models\GpsReading;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\JsonResponse;

class TrackingHistoryController extends Controller
{
    public function index(Request $request): View
    {
        $gpsController = app(GpsReadingController::class);
        $data = $gpsController->dashboardData($request);
        $data['activeView'] = 'history';
        return view('dashboard.index', $data);
    }

    public function show($id): JsonResponse
    {
        $reading = GpsReading::findOrFail($id);
        return response()->json($reading);
    }

    public function destroy($id): JsonResponse
    {
        $reading = GpsReading::findOrFail($id);
        $reading->delete();

        return response()->json([
            'message' => 'GPS reading deleted successfully'
        ]);
    }
}
