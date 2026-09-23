# Ayo Piket - Sistem Manajemen & Presensi Piket Himpunan

Sistem berbasis Web PHP Native dan Tailwind CSS untuk penjadwalan piket himpunan mahasiswa (khusus hari Senin & Kamis), fitur gacha pembagian hari yang adil antar divisi, export jadwal PDF, serta form presensi pengurus dengan upload bukti foto dokumentasi.

---

## Cara Instalasi & Menjalankan

1. **Jalankan Apache & MySQL** di XAMPP Control Panel.
2. Pastikan folder proyek berada di:
   ```text
   C:/xampp/htdocs/ayo-piket
   ```
   *(Atau buat Virtual Host / Symlink ke folder proyek ini).*
3. **Import Database**:
   - Buka browser dan kunjungi `http://localhost/phpmyadmin`.
   - Buat database baru bernama `ayo_piket`.
   - Pilih tab **Import**, klik **Choose File**, pilih file `database.sql` dari folder proyek ini.
   - Klik tombol **Import** / **Go** di bagian bawah.
4. **Buka Aplikasi di Browser**:
   - Halaman Publik (Jadwal & Presensi): `http://localhost/ayo-piket/index.php`
   - Form Presensi Pengurus: `http://localhost/ayo-piket/presensi.php`
   - Panel Admin: `http://localhost/ayo-piket/admin/login.php`

---

## Kredensial Login Administrator Default

- **Username**: `admin`
- **Password**: `admin123`

---

## Fitur yang Tersedia

1. **Jadwal Khusus Senin & Kamis**:
   - Sistem otomatis menghitung seluruh tanggal hari Senin dan Kamis dalam bulan yang dipilih.
2. **Gacha Pembagian Jadwal Otomatis**:
   - Algoritma pemerataan divisi (*anti-jomplang*): Anggota diacak per divisi dan didistribusikan secara round-robin ke seluruh tanggal Senin & Kamis agar setiap hari memiliki komposisi divisi yang seimbang.
3. **Pengaturan Jadwal Manual**:
   - Admin dapat menambahkan pengurus ke tanggal tertentu secara manual atau menghapus penugasan jika berhalangan.
   - Fitur publish & draft: Pengurus di halaman publik hanya melihat jadwal yang sudah berstatus `published`.
4. **Export Jadwal PDF**:
   - Layout siap cetak Landscape A4 dengan tombol simpan ke PDF untuk admin dan pengurus publik.
5. **Form Presensi & Upload Dokumentasi**:
   - Dropdown pengurus aktif dengan deteksi otomatis divisi via JavaScript.
   - Input jam mulai dan jam selesai dengan validasi jam selesai > jam mulai.
   - Upload foto dokumentasi dengan validasi ekstensi (JPG, PNG, WEBP) dan batas ukuran maksimal 2 MB.
6. **Dashboard & Rekap Presensi Admin**:
   - Rekap seluruh presensi masuk dengan modal preview foto resolusi penuh.
   - Filter presensi berdasarkan tanggal dan divisi.
   - CRUD Master Data Divisi dan Pengurus.
