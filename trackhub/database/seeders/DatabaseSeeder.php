<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Pastikan User manju (ID 2) ada
        $user = User::where('email', 'manju@gmail.com')->first();
        if (!$user) {
            $user = User::where('id', 2)->first();
        }
        if (!$user) {
            $user = User::updateOrCreate(
                ['email' => 'manju@gmail.com'],
                [
                    'id' => 2,
                    'name' => 'manju',
                    'password' => \Illuminate\Support\Facades\Hash::make('password'),
                ]
            );
        }

        // 2. Petakan device "esp32-gps-01" ke User ID 2
        \App\Models\Device::updateOrCreate(
            ['device_id' => 'esp32-gps-01'],
            [
                'user_id' => $user->id,
                'name' => 'ESP32 GPS Tracker 01',
            ]
        );

        // 3. Perbarui data koordinat lama agar terhubung ke user ini (ID 2)
        \App\Models\GpsReading::where('device_id', 'esp32-gps-01')
            ->whereNull('user_id')
            ->update(['user_id' => $user->id]);
    }

}
