<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('gps_readings', function (Blueprint $table) {
            $table->increments('id'); 
            $table->unsignedInteger('user_id')->nullable(); 
            $table->string('device_id', 20)->default('esp32-gps-01'); 
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->decimal('altitude_m', 10, 2)->nullable();
            $table->decimal('speed_kmph', 10, 2)->nullable();
            $table->unsignedSmallInteger('satellites')->nullable();
            $table->decimal('hdop', 8, 2)->nullable();
            $table->text('raw_nmea')->nullable();
            $table->boolean('offline')->default(false); 
            $table->timestamp('recorded_at')->useCurrent();
            $table->timestamps();


            $table->index(['device_id', 'recorded_at']);

            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('gps_readings');
    }
};
