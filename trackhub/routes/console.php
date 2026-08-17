<?php

use App\Services\GpsReadingIngestor;
use App\Services\SimpleMqttClient;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Validation\ValidationException;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

//command yang membaca konfigurasi di .env untuk terhubung secara terus-menerus ke broker dan mensubscribe topic trackhub/gps
Artisan::command('mqtt:listen', function (GpsReadingIngestor $ingestor) {
    $topic = config('mqtt.topic');
    $this->info(sprintf(
        'Listening MQTT %s:%s topic [%s]',
        config('mqtt.host'),
        config('mqtt.port'),
        $topic
    ));

    while (true) {
        $client = new SimpleMqttClient(
            config('mqtt.host'),
            config('mqtt.port'),
            config('mqtt.client_id'),
            config('mqtt.username'),
            config('mqtt.password'),
            config('mqtt.keepalive'),
            config('mqtt.timeout'),
        );

        try {
            $client->connect(); //server laravel melakukan SUBSCRIBE ke topik yang sama untuk memantau dan menarik data secara real-time begitu ada kiriman baru dari ESP32
            $client->subscribe($topic);
            $this->info('MQTT connected and subscribed.');

            while (true) {
                $message = $client->readMessage();
                if ($message === null) {
                    continue;
                }
                // jika payload JSON yang diterima Laravel tidak valid atau formatnya salah
                $payload = json_decode($message['payload'], true);
                if (! is_array($payload)) {
                    $this->warn('Ignored MQTT message with invalid JSON.');
                    continue;
                }

                // ============================
                // CEK PAYLOAD YANG MASUK
                // ============================
                $this->info("PAYLOAD MQTT:");
                $this->line(json_encode($payload, JSON_PRETTY_PRINT));
                // ============================

                try {
                    $reading = $ingestor->ingest($payload);
                    $this->line(sprintf(
                        '[%s] %s lat=%s lng=%s',
                        now()->toDateTimeString(),
                        $reading->device_id,
                        $reading->latitude ?? 'null',
                        $reading->longitude ?? 'null',
                    ));
                } catch (ValidationException $exception) {
                    $this->warn('Ignored invalid GPS payload: '.json_encode($exception->errors()));
                }
            }
        } catch (Throwable $exception) {
            $this->error('MQTT listener error: '.$exception->getMessage());
            $client->disconnect();
            sleep(config('mqtt.reconnect_delay'));
        }
    }
})->purpose('Subscribe to Mosquitto MQTT GPS topic and store readings');
