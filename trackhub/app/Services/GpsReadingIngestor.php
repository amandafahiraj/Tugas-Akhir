<?php

namespace App\Services;

use App\Models\GpsReading;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class GpsReadingIngestor
{
    /**
     * @param  array<string, mixed>  $payload
     *
     * @throws ValidationException
     */
    public function ingest(array $payload): GpsReading
    {
        $validated = Validator::make($payload, [
            'device_id' => ['nullable', 'string', 'max:64'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'altitude_m' => ['nullable', 'numeric'],
            'altitude' => ['nullable', 'numeric'],
            'speed_kmph' => ['nullable', 'numeric', 'min:0'],
            'speed' => ['nullable', 'numeric', 'min:0'],
            'satellites' => ['nullable', 'integer', 'min:0'],
            'hdop' => ['nullable', 'numeric', 'min:0'],
            'raw_nmea' => ['nullable', 'string', 'max:2000'],
            'recorded_at' => ['nullable', 'date'],
            'offline' => ['nullable', 'boolean'],
        ])->validate();

        return GpsReading::create([
            'device_id' => $validated['device_id'] ?? 'esp32-gps-01',
            'latitude' => $validated['latitude'] ?? null,
            'longitude' => $validated['longitude'] ?? null,
            'altitude_m' => $validated['altitude_m'] ?? $validated['altitude'] ?? null,
            'speed_kmph' => $validated['speed_kmph'] ?? $validated['speed'] ?? null,
            'satellites' => $validated['satellites'] ?? null,
            'hdop' => $validated['hdop'] ?? null,
            'raw_nmea' => $validated['raw_nmea'] ?? null,
            'recorded_at' => $validated['recorded_at'] ?? now(),
            'offline' => $validated['offline'] ?? false,
        ]);
    }
}
