<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\GpsReadingController;
use App\Http\Controllers\TrackingHistoryController;
use App\Http\Controllers\DataLoggingController;
use App\Http\Controllers\SystemStatusController;
use Illuminate\Support\Facades\Route;

// Guest Routes (Login & Register)
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
    Route::get('/register', [AuthController::class, 'showRegisterForm'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);
});

// Authenticated Routes
Route::middleware('auth')->group(function () {

    // Dashboard
    Route::get('/', [GpsReadingController::class, 'dashboard'])->name('dashboard');

    // GPS Readings
    Route::get('/gps-readings', [GpsReadingController::class, 'index'])->name('gps-readings.index');

    // Tracking History
    Route::prefix('tracking-history')->name('tracking-history.')->group(function () {
        Route::get('/', [TrackingHistoryController::class, 'index'])->name('index');
        Route::get('/{id}', [TrackingHistoryController::class, 'show'])->name('show');
        Route::delete('/{id}', [TrackingHistoryController::class, 'destroy'])->name('destroy');
    });

    // Data Logging
    Route::prefix('data-logging')->name('data-logging.')->group(function () {
        Route::get('/', [DataLoggingController::class, 'index'])->name('index');
        Route::get('/export', [DataLoggingController::class, 'export'])->name('export');
        Route::delete('/clear', [DataLoggingController::class, 'clear'])->name('clear');
    });

    // System Status
    Route::prefix('system-status')->name('system-status.')->group(function () {
        Route::get('/', [SystemStatusController::class, 'index'])->name('index');
        Route::get('/health', [SystemStatusController::class, 'health'])->name('health');
    });

    // Logout
    Route::match(['get', 'post'], '/logout', [AuthController::class, 'logout'])->name('logout');
});

// Public POST route for GPS data submission
Route::post('/gps-readings', [GpsReadingController::class, 'store'])->name('gps-readings.store');

