<?php

return [
    'host' => env('MQTT_HOST', '127.0.0.1'),
    'port' => (int) env('MQTT_PORT', 1883),
    'topic' => env('MQTT_TOPIC', 'trackhub/gps'),
    'client_id' => env('MQTT_CLIENT_ID', 'trackhub-laravel-subscriber'),
    'username' => env('MQTT_USERNAME'),
    'password' => env('MQTT_PASSWORD'),
    'keepalive' => (int) env('MQTT_KEEPALIVE', 30),
    'timeout' => (int) env('MQTT_TIMEOUT', 10),
    'reconnect_delay' => (int) env('MQTT_RECONNECT_DELAY', 5),
];
