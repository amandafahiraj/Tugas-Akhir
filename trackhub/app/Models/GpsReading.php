<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GpsReading extends Model
{
    protected $fillable = [
        'user_id',
        'device_id',
        'latitude',
        'longitude',
        'altitude_m',
        'speed_kmph',
        'satellites',
        'hdop',
        'raw_nmea',
        'recorded_at',
        'offline',
    ];

    protected function casts(): array
    {
        return [
            'latitude' => 'float',
            'longitude' => 'float',
            'altitude_m' => 'float',
            'speed_kmph' => 'float',
            'satellites' => 'integer',
            'hdop' => 'float',
            'recorded_at' => 'datetime',
            'offline' => 'boolean',
        ];
    }

    public function user(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function device(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Device::class, 'device_id', 'device_id');
    }
}

