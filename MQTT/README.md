# TrackHub MQTT Setup

Alur baru:

1. ESP32 publish JSON GPS ke Mosquitto topic `trackhub/gps`.
2. Laravel menjalankan `php artisan mqtt:listen`.
3. Subscriber Laravel menyimpan payload ke tabel `gps_readings`.
4. Dashboard tetap membaca data lewat endpoint Laravel yang sudah ada.

## Mosquitto

Jalankan broker di mesin yang bisa diakses ESP32:

```powershell
D:\gpstracker\MQTT\mosquitto\mosquitto.exe -c D:\gpstracker\MQTT\mosquitto-local.conf -v
```

Jika memakai Git Bash:

```bash
/d/gpstracker/MQTT/mosquitto/mosquitto.exe -c /d/gpstracker/MQTT/mosquitto-local.conf -v
```

Installer lokal ada di `D:\gpstracker\MQTT\mosquitto`, jadi command `mosquitto -v` belum tentu tersedia global kecuali folder itu ditambahkan ke `PATH`.

Default konfigurasi project:

```env
MQTT_HOST=192.168.167.167
MQTT_PORT=1883
MQTT_TOPIC=trackhub/gps
```

Jika broker ada di laptop dengan IP `192.168.167.167`, samakan:

- `kodeArduino/kode/kode.ino`: `mqttBroker = "192.168.167.167"`
- `trackhub/.env`: `MQTT_HOST=192.168.167.167` supaya Laravel subscribe ke broker yang sama dengan ESP32.

## Laravel

Di folder `trackhub`:

```powershell
php artisan migrate
php artisan mqtt:listen
```

Untuk menjalankan server, Vite, log, queue, dan subscriber sekaligus:

```powershell
composer run dev
```

## Test Publish Manual

Jika `mosquitto_pub` tersedia:

```powershell
D:\gpstracker\MQTT\mosquitto\mosquitto_pub.exe -h 127.0.0.1 -t trackhub/gps -f D:\gpstracker\MQTT\test-payload.json
```

Subscriber Laravel harus menampilkan baris seperti:

```text
[2026-05-21 15:00:00] test-01 lat=-6.2 lng=106.8166667
```

## Arduino

Install library berikut di Arduino IDE:

- WiFiManager
- TinyGPSPlus
- PubSubClient

Upload `kodeArduino/kode/kode.ino` ke ESP32. Saat broker belum tersambung, data tetap masuk ke `gps_queue.csv` di SD dan akan dikirim ulang saat MQTT berhasil publish.
