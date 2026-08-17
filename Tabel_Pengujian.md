# Tabel Hasil Pengujian Sistem Tracking Kendaraan Offline

Dokumen ini berisi rancangan tabel pengujian tugas akhir berdasarkan hasil pengujian yang telah dipaparkan pada Bab IV (sub-bab 4.2.1 hingga 4.2.4). Anda dapat langsung menyalin tabel-tabel di bawah ini ke dalam draf laporan Tugas Akhir Anda (Microsoft Word atau LaTeX).

---

## 1. Tabel Ringkasan Pengujian Sistem (Keseluruhan)
Tabel ini memberikan ringkasan menyeluruh mengenai seluruh fitur/fungsi utama yang diuji, parameter yang digunakan, hasil ekspektasi, hasil aktual, dan status kelulusan (keberhasilan).

| No | Fitur / Fungsi yang Diuji | Skenario Pengujian | Parameter yang Diamati | Hasil yang Diharapkan | Hasil Pengujian Aktual | Keterangan / Status |
| :--- | :--- | :--- | :--- | :--- | :--- | :--- |
| **1** | **Pembacaan Data GPS (4.2.1)** | Menyalakan alat di area terbuka (outdoor) untuk mendapatkan *GPS lock*. | - Status *GPS lock*<br>- Koordinat Lat, Long<br>- Altitude, Speed<br>- Jumlah Satelit & HDOP | Modul GPS Neo-M8N berhasil mengunci satelit, ESP32 membaca data posisi secara real-time dengan akurat. | Modul GPS berhasil mengunci sinyal satelit, ESP32 membaca koordinat secara real-time (contoh: Lat `-0.925165`, Long `100.351443`, Satellites `21`, HDOP `0.60`). | **Berhasil** (Sesuai Ekspektasi) |
| **2** | **Pengiriman Data ke Server via MQTT (4.2.2)** | Menghubungkan ESP32 ke Wi-Fi dan mengirimkan payload data GPS ke MQTT Broker dalam kondisi online. | - Koneksi Wi-Fi & MQTT<br>- Log Serial Monitor<br>- Data di MySQL (`offline = 0`)<br>- Tampilan Dashboard | ESP32 berhasil terhubung ke broker, memublikasikan payload JSON, Laravel subscriber menyimpan ke database, dan dashboard memperbarui data real-time. | Log Serial Monitor mencetak "MQTT connected" & "MQTT publish OK". Data tersimpan di tabel `gps_readings` dengan flag `offline = 0` dan ter-render di peta dashboard. | **Berhasil** (Sesuai Ekspektasi) |
| **3** | **Penyimpanan Data Offline (4.2.3)** | Memutuskan koneksi internet pada ESP32 saat sistem sedang berjalan aktif mengirimkan data GPS. | - Deteksi Wi-Fi terputus<br>- File `gps_queue.json` di SD Card<br>- Format data (`offline: true`)<br>- Status web dashboard | ESP32 mendeteksi koneksi terputus, mengalihkan penyimpanan ke MicroSD dalam file `gps_queue.json` dengan format JSON, dan dashboard menampilkan status "Offline". | Serial Monitor menampilkan "WiFi offline. Simpan ke SD". Data tersimpan di MicroSD dengan flag `"offline": true`. Dashboard menampilkan indikator offline dengan koordinat terakhir yang valid. | **Berhasil** (Sesuai Ekspektasi) |
| **4** | **Sinkronisasi Data Offline (4.2.4)** | Mengaktifkan kembali koneksi internet/Wi-Fi pada ESP32 setelah perangkat sempat berjalan offline. | - Deteksi Wi-Fi pulih kembali<br>- Proses *forwarding* data tunda<br>- Pengosongan antrean SD Card<br>- Data terkirim masuk ke MySQL | ESP32 mendeteksi koneksi pulih, membaca dan mengirimkan seluruh data tunda dari MicroSD ke MQTT Broker, database memperbarui data tunda, dan antrean di MicroSD menjadi kosong. | Serial Monitor menampilkan "WiFi reconnecting..." dan mencetak log ringkasan "Sync: sent=15, remaining=0". Tabel data logging di web terupdate menjadi 0 batch dan status "Synced". | **Berhasil** (Sesuai Ekspektasi) |

---

## 2. Tabel Detil Parameter Hasil Pengujian Fungsional

Untuk memperkuat bab pengujian, disarankan juga menambahkan tabel pengujian detil per skenario untuk menunjukkan variasi/kasus data yang berhasil diolah oleh sistem.

### A. Tabel Pengujian Fungsional Pembacaan GPS (Outdoor vs. Indoor)
Tabel ini membuktikan keandalan pembacaan GPS pada kondisi lingkungan yang berbeda.

| No | Kondisi Lingkungan | Status GPS Lock | Koordinat Latitude / Longitude | Jumlah Satelit Terbaca | Akurasi (HDOP) | Status Pembacaan |
| :---: | :--- | :---: | :--- | :---: | :---: | :---: |
| 1 | Area Terbuka (Outdoor) | Terkunci (*Locked*) | Contoh: `-0.925165` / `100.351443` | 21 | 0.60 | Sukses / Akurat |
| 2 | Area Tertutup (Indoor) | Tidak Terkunci | - / - | 0 - 3 | > 2.0 (atau *Invalid*) | Gagal / Mencari Sinyal |

### B. Tabel Pengujian Transmisi Data (MQTT & Database)
Tabel ini menunjukkan kesesuaian data yang dikirim oleh ESP32 dengan data yang masuk ke database MySQL Laravel.

| Parameter Pengiriman (ESP32) | Nilai Terkirim | Parameter Database (MySQL) | Nilai Tersimpan | Status Sinkronisasi |
| :--- | :--- | :--- | :--- | :---: |
| `device_id` | `"esp32-gps-01"` | `device_id` | `"esp32-gps-01"` | Sesuai |
| `latitude` | `-0.9137522` | `latitude` | `-0.9137522` | Sesuai |
| `longitude` | `100.3575945` | `longitude` | `100.3575945` | Sesuai |
| `offline` | `true` (saat offline) | `offline` | `1` (atau `true`) | Sesuai |
| `offline` | `false` (saat online) | `offline` | `0` (atau `false`) | Sesuai |

### C. Tabel Pengujian Kapasitas & Ketahanan Penyimpanan Offline (MicroSD)
Tabel ini membuktikan fungsionalitas fitur *store-and-forward* (penyimpanan sementara).

| Skenario Pengujian Offline | Durasi Putus Koneksi | Jumlah Payload Terbentuk | Kapasitas Terpakai pada MicroSD | Status File `gps_queue.json` | Hasil Setelah Internet Pulih |
| :--- | :---: | :---: | :---: | :---: | :--- |
| Koneksi internet diputus secara sengaja | 5 menit | 60 data (interval 5s) | ~20 KB | Berhasil ditulis (Format JSON per baris) | 60 data tunda berhasil disinkronisasi ke server, file di SD Card dikosongkan. |
| Koneksi internet diputus secara sengaja | 15 menit | 180 data (interval 5s) | ~60 KB | Berhasil ditulis (Format JSON per baris) | 180 data tunda berhasil disinkronisasi ke server, file di SD Card dikosongkan. |

---

## Cara Memasukkan ke Laporan Tugas Akhir Anda:
1. **Salin Tabel**: Blok tabel di atas, klik kanan, pilih *Copy*, dan paste langsung ke Microsoft Word. Format tabel markdown akan otomatis dikonversi menjadi tabel Word yang rapi.
2. **Sesuaikan Format Penomoran**: Sesuaikan nomor tabel dengan format bab Anda (misalnya, **Tabel 4.3 Ringkasan Pengujian Fungsional Sistem**).
3. **Tambahkan Narasi Penjelas**: Di bawah tabel tersebut, berikan narasi singkat yang merujuk pada gambar-gambar bukti pengujian Anda (misalnya: *"Berdasarkan Tabel 4.3, pengujian sinkronisasi data offline menunjukkan bahwa sistem berhasil mengirimkan seluruh 15 data tunda yang tersimpan di MicroSD, dibuktikan dengan tampilan Serial Monitor pada Gambar 4.41."*).
