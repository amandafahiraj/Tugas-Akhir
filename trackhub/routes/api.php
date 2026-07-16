<?php

use App\Http\Controllers\GpsReadingController;
use Illuminate\Support\Facades\Route;

// Stateful API route protected by session auth
Route::middleware(['web', 'auth'])->get('/gps-readings', [GpsReadingController::class, 'index'])->name('api.gps-readings.index');

// Public API route for physical GPS tracker devices to post data
Route::post('/gps-readings', [GpsReadingController::class, 'store'])->name('api.gps-readings.store');

