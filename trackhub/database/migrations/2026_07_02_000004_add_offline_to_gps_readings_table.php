<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('gps_readings', function (Blueprint $table) {
            $table->boolean('offline')->default(false)->after('raw_nmea');
        });
    }

    public function down(): void
    {
        Schema::table('gps_readings', function (Blueprint $table) {
            $table->dropColumn('offline');
        });
    }
};
