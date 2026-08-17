# Panduan Langkah demi Langkah Memasukkan Tabel Pengujian ke Dokumen Tugas Akhir

Panduan ini membantu Anda menerapkan revisi penambahan tabel pengujian ke dalam draf Tugas Akhir (Word/LaTeX) Anda secara rapi dan akademis.

---

## Langkah 1: Menyalin dan Menata Tabel ke Microsoft Word
Karena sebagian besar mahasiswa menggunakan Microsoft Word, berikut cara terbaik agar tabel dari format Markdown tidak berantakan saat di-paste:
1. **Salin Tabel**: Blok seluruh tabel dari berkas [Tabel_Pengujian.md](file:///d:/gpstracker/Tabel_Pengujian.md) (mulai dari header tabel `| No | Fitur / Fungsi...` sampai baris terakhir). Klik kanan dan pilih **Copy**.
2. **Tempel ke Word**: Buka dokumen draf Tugas Akhir Anda di Microsoft Word pada Bab IV bagian **4.2 Pengujian**. Tempel dengan menekan tombol **Ctrl + V** (atau klik kanan -> *Keep Source Formatting* / *Use Destination Styles*).
3. **Merapikan Lebar Kolom (Autofit)**:
   - Klik ikon tanda plus kecil (+) di sudut kiri atas tabel untuk menyeleksi seluruh tabel.
   - Pergi ke tab **Layout** di bagian atas Word (di bawah *Table Tools*).
   - Klik tombol **AutoFit**, lalu pilih **AutoFit Window** agar lebar kolom otomatis menyesuaikan batas margin kertas laporan Anda.
4. **Desain Tabel Standar Akademik (APA / IEEE Style)**:
   - Kebanyakan kampus mensyaratkan tabel bersih tanpa garis vertikal (hanya garis horizontal di bagian atas, bawah, dan pembatas header).
   - Caranya: Seleksi tabel -> masuk ke tab **Table Design** -> klik **Borders** -> hilangkan garis vertikal kiri dan kanan (*No Border* pada bagian vertikal, sisakan garis horizontal atas/bawah).
   - Ubah teks header menjadi **Tebal (Bold)** dan rata tengah (*center*).

---

## Langkah 2: Memberi Nama dan Penomoran Tabel
Sesuaikan nomor tabel dengan aturan penomoran bab Anda.
* **Format Umum**: `Tabel [Nomor Bab].[Nomor Urut Tabel] [Nama Tabel]`
* **Contoh Penamaan**:
  - `Tabel 4.3 Ringkasan Hasil Pengujian Fungsional Sistem` (diletakkan di bagian awal sub-bab 4.2 sebelum penjelasan detil).
  - atau `Tabel 4.3 Matriks Hasil Pengujian Sistem Tracking`

---

## Langkah 3: Menulis Narasi Analisis / Penjelasan Tabel
Penguji tidak hanya ingin melihat tabel kosong, tetapi ingin membaca analisis yang merujuk pada tabel tersebut. Anda wajib menuliskan narasi pengantar sebelum tabel dan narasi analisis setelah tabel.

### A. Contoh Narasi Pengantar (Sebelum Tabel)
> *"Untuk mempermudah pemahaman mengenai seluruh pengujian yang telah dilakukan pada sistem tracking kendaraan offline ini, berikut disajikan rangkuman matriks pengujian pada Tabel 4.3."*

### B. Contoh Narasi Analisis (Setelah Tabel)
> *"Berdasarkan Tabel 4.3, dapat dianalisis bahwa seluruh komponen perangkat keras (ESP32, GPS Neo-M8N, dan MicroSD) serta perangkat lunak (Laravel, MQTT, dan MySQL) telah berfungsi sesuai dengan rancangan sistem. Fitur penyimpanan data offline (sub-bab 4.2.3) berhasil mengamankan data posisi kendaraan saat kehilangan jaringan internet dengan menyimpannya ke kartu MicroSD. Ketika jaringan internet pulih kembali, fitur sinkronisasi offline (sub-bab 4.2.4) berhasil melakukan pengiriman ulang (forwarding) seluruh data tunda ke server tanpa terjadi kehilangan data (data loss), dibuktikan dengan berkurangnya kapasitas data tunda menjadi 0 batch pada Gambar 4.40."*

---

## Langkah 4: Menghubungkan Tabel dengan Gambar Bukti (Cross-Reference)
Pastikan setiap baris di tabel pengujian memiliki kaitan dengan gambar/screenshot yang ada di laporan Anda. Ini membuktikan bahwa hasil pengujian di tabel Anda valid dan bukan sekadar karangan:
- **Pengujian 1 (GPS)** -> Rujuk ke **Gambar 4.32** (Tampilan dashboard mendapatkan koordinat).
- **Pengujian 2 (MQTT Online)** -> Rujuk ke **Gambar 4.33** (Serial Monitor MQTT OK) dan **Gambar 4.34** (Tabel database phpMyAdmin).
- **Pengujian 3 (Offline SD Card)** -> Rujuk ke **Gambar 4.36** (Serial Monitor simpan ke SD) dan **Gambar 4.37** (Isi file JSON di MicroSD).
- **Pengujian 4 (Sinkronisasi)** -> Rujuk ke **Gambar 4.40** (Dashboard logging menjadi 0 batch) dan **Gambar 4.41** (Serial monitor `Sync: sent=15, remaining=0`).

---

## Langkah 5: Mempersiapkan Jawaban untuk Dosen Penguji saat Cek Revisi
Saat menyerahkan revisi ini, dosen biasanya akan bertanya singkat untuk memastikan Anda paham. Berikut draf jawaban yang bisa Anda siapkan:

* **Pertanyaan Dosen**: *"Mengapa Anda menyimpulkan pengujian sinkronisasi data offline ini berhasil?"*
* **Jawaban Anda**: *"Saya menyimpulkan berhasil karena berdasarkan hasil pengujian di Tabel 4.3 (dan detil sub-bab 4.2.4), saat koneksi pulih, ESP32 berhasil membaca file `gps_queue.json` dari MicroSD dan mengirimkannya kembali ke MQTT broker hingga selesai. Hal ini dibuktikan dengan log Serial Monitor yang mencetak `Sync: sent=15, remaining=0` (artinya semua 15 data terkirim tanpa sisa) dan status di halaman Data Logging web berubah menjadi 'Synced' dengan kapasitas antrean kembali ke 0."*
