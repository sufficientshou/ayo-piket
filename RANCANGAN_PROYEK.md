Rancangan Sistem & Desain Arsitektur: Ayo Piket

Dokumen spesifikasi teknis, rancangan sistem, pembagian fitur, serta matriks hak akses pengguna (User) dan Administrator pada aplikasi Ayo Piket (Sistem Manajemen & Presensi Piket HIMTIKA UNSIKA).

1.  Ringkasan Eksekutif

Ayo Piket adalah aplikasi web berbasis PHP Native dan Tailwind CSS yang dirancang untuk mengelola siklus operasional piket rutin sekretariat himpunan. Aplikasi menangani:

- Penentuan jadwal khusus hari Senin dan Kamis.
- Pengacakan jadwal otomatis berbasis algoritma pemerataan divisi (_anti-jomplang round-robin_).
- Publikasi jadwal terkontrol (_draft_ vs _published_).
- Pelaporan presensi mandiri oleh pengurus disertai bukti foto dokumentasi kegiatan.
- Verifikasi dan monitoring presensi harian oleh administrator secara terpusat.
- Export rekapitulasi data ke format cetak PDF (Landscape A4) dan spreadsheet CSV.

2.  Arsitektur & Tumpukan Teknologi (Tech Stack)

| Komponen         | Teknologi                                                            | Deskripsi                                                                                |
| :--------------- | :------------------------------------------------------------------- | :--------------------------------------------------------------------------------------- |
| Backend          | PHP 8.x (Native Procedural)                                          | Logika bisnis, manipulasi sesi, hashing password, dan pemrosesan upload.                 |
| Database         | MySQL / MariaDB (PDO Engine)                                         | Penyimpanan relasional dengan PDO Prepared Statements untuk keamanan query.              |
| Frontend Styling | Tailwind CSS (CDN)                                                   | Desain UI bergaya Neo-Brutalism (border tebal hitam, drop-shadow tajam, kontras tinggi). |
| Font Family      | Space Mono & Plus Jakarta Sans                                       | Tipografi monospaced untuk identitas teknis dan sans-serif untuk readability.            |
| Client Script    | Vanilla JavaScript                                                   | Interaktivitas dropdown custom, validasi file, hitung durasi, dan auto-fill divisi.      |
| Export Engine    | Native CSS Paged Media (`@page { size: A4 landscape }`) & CSV Stream | Export dokumen tanpa ketergantungan library pihak ketiga yang berat.                     |

3.  Skema Basis Data (Database Schema)

Sistem menggunakan 5 tabel relasional:

```
[divisions] 1 ────< N [members] 1 ────< N [schedules]
                           │
                           └──────────< N [attendances]

[admins] (Independen)
```

3.1. Tabel `admins`

Menyimpan kredensial administrator himpunan.

- `id` (INT, Primary Key, Auto Increment)
- `username` (VARCHAR 50, Unique)
- `password` (VARCHAR 255, Bcrypt Hash)
- `nama` (VARCHAR 100)
- `created_at` (TIMESTAMP)

  3.2. Tabel `divisions`

Master data departemen / divisi dalam struktur kepengurusan.

- `id` (INT, Primary Key, Auto Increment)
- `nama_divisi` (VARCHAR 100)
- `created_at` (TIMESTAMP)

  3.3. Tabel `members`

Daftar seluruh anggota pengurus himpunan.

- `id` (INT, Primary Key, Auto Increment)
- `nim` (VARCHAR 20, Unique, Nullable)
- `nama` (VARCHAR 100)
- `division_id` (INT, Foreign Key ke `divisions.id` ON DELETE CASCADE)
- `no_wa` (VARCHAR 20, Nullable)
- `is_active` (TINYINT 1, Default 1 - Menandakan keaktifan piket)
- `created_at` (TIMESTAMP)

  3.4. Tabel `schedules`

Alokasi jadwal penugasan piket per hari Senin dan Kamis.

- `id` (INT, Primary Key, Auto Increment)
- `member_id` (INT, Foreign Key ke `members.id` ON DELETE CASCADE)
- `tanggal` (DATE)
- `hari` (ENUM: 'Senin', 'Kamis')
- `bulan` (TINYINT, 1-12)
- `tahun` (SMALLINT)
- `status` (ENUM: 'draft', 'published', Default 'draft')
- `created_at` (TIMESTAMP)

  3.5. Tabel `attendances`

Laporan presensi riil yang dikirim oleh pengurus setelah piket.

- `id` (INT, Primary Key, Auto Increment)
- `member_id` (INT, Foreign Key ke `members.id` ON DELETE CASCADE)
- `tanggal` (DATE)
- `jam_mulai` (TIME)
- `jam_selesai` (TIME)
- `foto_bukti` (VARCHAR 255, Nama file di folder `uploads/dokumentasi/`)
- `status_verifikasi` (ENUM: 'pending', 'valid', 'invalid', Default 'valid')
- `catatan` (TEXT, Nullable)
- `created_at` (TIMESTAMP)

4.  Matriks Peran & Hak Akses Pengguna

| Fitur / Modul                        |    User (Pengurus / Publik)     |        Admin (Pengurus Inti)         |
| :----------------------------------- | :-----------------------------: | :----------------------------------: |
| Akses Akun                           |  Tanpa Login (Portal Terbuka)   |    Wajib Login (Bcrypt + Session)    |
| Melihat Jadwal Bulanan               | Hanya jadwal status `published` | Semua jadwal (`draft` & `published`) |
| Filter Bulan & Tahun Jadwal          |               Ya                |                  Ya                  |
| Generate Jadwal Otomatis (Gacha)     |              Tidak              |                  Ya                  |
| Plotting Jadwal Manual               |              Tidak              |                  Ya                  |
| Hapus / Reset Penugasan Jadwal       |              Tidak              |                  Ya                  |
| Publish / Unpublish Jadwal           |              Tidak              |                  Ya                  |
| Export Jadwal ke PDF Landscape       |         Ya (Published)          |        Ya (Draft & Published)        |
| Export Jadwal ke CSV                 |              Tidak              |                  Ya                  |
| Mengisi Form Presensi Harian         |               Ya                |                  Ya                  |
| Upload Foto Bukti Dokumentasi        |         Ya (Maks. 2 MB)         |                  Ya                  |
| Melihat Live Feed Presensi Hari Ini  |    Ya (Status ACC / Pending)    |                  Ya                  |
| Verifikasi Presensi (ACC / Batalkan) |              Tidak              |                  Ya                  |
| Hapus Presensi & Hapus File Fisik    |              Tidak              |                  Ya                  |
| Export Rekap Presensi ke CSV         |              Tidak              |                  Ya                  |
| CRUD Master Data Divisi              |              Tidak              |                  Ya                  |
| CRUD Master Data Pengurus            |              Tidak              |                  Ya                  |
| Aktivasi / Nonaktifkan Pengurus      |              Tidak              |                  Ya                  |

5.  Rincian Fitur Berdasarkan Peran

5.1. Fitur Peran: User (Pengurus / Publik)

1. Dashboard Kalender Piket Publik (`index.php`):
   - Menampilkan slot penugasan hari Senin & Kamis pada bulan dan tahun aktif.
   - Anggota dikelompokkan berdasarkan tanggal dengan kartu nama, badge inisial, dan label divisi.
   - Jadwal berstatus `draft` disembunyikan otomatis dari pandangan publik.
   - Navigasi dropdown bulan (Januari - Desember) dan tahun dinamis.

2. Aturan & Tata Tertib Piket (Rules Modal/Accordion):
   - Menampilkan 8 poin regulasi piket sekre (jadwal 15.00 - 20.00 WIB, SOP kebersihan, izin via HIMTIKA Care paling lambat H-1, ketentuan punishment).

3. Live Presensi Feed Harian:
   - Sidebar/widget daftar pengurus yang telah melapor pada hari berjalan (`CURRENT_DATE`).
   - Dilengkapi status verifikasi: badge hijau `TERVERIFIKASI` atau badge kuning `MENUNGGU REVIEW`.
   - Toggle view responsif untuk perangkat mobile dan desktop.

4. Form Presensi & Laporan Piket (`presensi.php`):
   - Pencarian Nama Pengurus: Dropdown interaktif hanya menampilkan pengurus dengan status `is_active = 1`.
   - Auto-Fill Divisi: Nama divisi terisi otomatis secara real-time via JavaScript saat nama pengurus dipilih.
   - Validasi Waktu: Input jam mulai dan jam selesai dengan proteksi client-side dan server-side (`jam_selesai > jam_mulai`).
   - Upload Dokumentasi Bukti: Drag-and-drop dropzone foto dengan validasi format file (`jpg`, `jpeg`, `png`, `webp`) dan batasan ukuran maksimal 2 MB.
   - Checkbox Keabsahan: Pernyataan legalitas laporan sebelum pengiriman.
   - Flash Alert: Feedback instan via modal bila terjadi kesalahan input atau sukses terkirim.

5. Export PDF Jadwal Publik (`export_jadwal.php`):
   - Tata letak cetak siap pakai (Landscape A4) dengan grid terstruktur per tanggal, nama petugas, dan divisi.
   - Dilengkapi fungsi otomatis pemicu dialog cetak browser (`window.print()`).

5.2. Fitur Peran: Admin (Administrator)

1. Autentikasi & Keamanan Sesi (`admin/login.php`, `includes/auth_check.php`):
   - Login berbasis sesi aman dengan `session_regenerate_id(true)`.
   - Verifikasi kata sandi menggunakan fungsi native `password_verify()`.
   - Proteksi middleware di seluruh rute panel admin melalui `auth_check.php`.
   - Logout terproteksi yang menghancurkan seluruh data sesi (`admin/logout.php`).

2. Dashboard & Monitoring Presensi (`admin/index.php`):
   - Metrik statistik: Total presensi masuk, presensi terverifikasi, dan presensi menunggu review.
   - Filter presensi multi-parameter: berdasarkan tanggal spesifik, divisi, dan status verifikasi (`pending` / `valid`).
   - Verifikasi 1-Klik: Tombol ACC untuk mengubah status presensi menjadi `valid`.
   - Batal Verifikasi: Tombol pembatalan untuk mengembalikan status presensi ke `pending`.
   - Preview Foto Resolusi Penuh: Modal interaktif untuk memeriksa foto bukti piket sebelum verifikasi.
   - Hapus Data Presensi Bersih: Menghapus baris rekaman presensi sekaligus menghapus berkas foto fisik dari direktori `uploads/dokumentasi/` via `unlink()`.
   - Export CSV Rekapitulasi: Mengunduh seluruh atau hasil filter presensi dalam format CSV (dilengkapi header UTF-8 BOM untuk kompatibilitas Microsoft Excel).

3. Manajemen Jadwal & Algoritma Gacha (`admin/schedule.php`):
   - Algoritma Gacha Round-Robin Berimbang:
     1. Sistem mengambil seluruh pengurus aktif (`is_active = 1`).
     2. Mengelompokkan pengurus ke dalam array divisi masing-masing.
     3. Mengacak anggota di dalam tiap grup divisi (`shuffle()`).
     4. Mengambil 1 anggota bergantian dari setiap divisi secara melingkar (round-robin interleaving).
     5. Memetakan urutan hasil acak ke daftar tanggal hari Senin & Kamis pada bulan yang dipilih.
     6. Menghasilkan komposisi divisi yang seimbang pada setiap sesi piket tanpa dominasi satu divisi.
   - Status Publikasi: Admin dapat menyetel status periode jadwal menjadi `draft` (tahap penyusunan) atau `published` (terbuka untuk publik).
   - Plotting Penugasan Manual: Menambahkan pengurus secara spesifik ke tanggal tertentu (tervalidasi hanya hari Senin & Kamis, serta pencegahan duplikasi anggota pada hari yang sama).
   - Hapus Slot Penugasan: Menghapus satu pengurus tertentu dari tanggal piket.
   - Reset Jadwal Bulanan: Mengosongkan seluruh slot jadwal dalam satu bulan sekaligus.
   - Export Jadwal CSV & PDF: Rekap jadwal format lembar kerja CSV dan layout pracetak PDF (`admin/export_pdf.php`).

4. Master Data Divisi (`admin/divisions.php`):
   - Tambah nama divisi baru.
   - Ubah nama divisi yang sudah ada.
   - Hapus divisi (didukung relasi `ON DELETE CASCADE` ke tabel pengurus).
   - Indikator jumlah total pengurus terdaftar per divisi.

5. Master Data Pengurus (`admin/members.php`):
   - Tambah data anggota: Nama lengkap, NIM, relasi divisi, dan Nomor WhatsApp.
   - Ubah data anggota.
   - Filter daftar anggota berdasarkan divisi.
   - Switch status keaktifan (`is_active`): Anggota nonaktif tidak akan dimasukkan ke dalam algoritma gacha piket dan form presensi publik.
   - Hapus data anggota.

6. Alur Kerja Sistem (Business Process Workflows)

6.1. Alur Pembuatan & Publikasi Jadwal

```
[Admin Masuk Menu Jadwal]
       │
       ▼
[Pilih Bulan & Tahun] ───> [Sistem Hitung Tanggal Senin & Kamis]
       │
       ├─────────────────────────────────┬─────────────────────────────────┐
       ▼                                 ▼                                 ▼
[Klik Tombol Gacha]              [Tambah Manual]                    [Reset Jadwal]
       │                                 │                                 │
  Acak anggota per divisi         Pilih anggota & tanggal          Kosongkan alokasi bulan
  & petakan ke tanggal slot       (Validasi Senin/Kamis)
       │                                 │
       └────────────────┬────────────────┘
                        ▼
                [Status: DRAFT] (Hanya terlihat di panel admin)
                        │
                        ▼
            [Klik PUBLISH KE PUBLIK]
                        │
                        ▼
              [Status: PUBLISHED] (Tampil di portal publik & siap presensi)
```

6.2. Alur Pengisian & Verifikasi Presensi

```
[Pengurus Piket di Sekre]
       │
       ▼
[Buka presensi.php] ───> [Pilih Nama] ───> [Divisi Otomatis Terisi]
       │
       ▼
[Input Tanggal, Jam Mulai, Jam Selesai] (Validasi jam_selesai > jam_mulai)
       │
       ▼
[Unggah Foto Bukti] ───> [Validasi Ekstensi & Ukuran <= 2MB]
       │
       ▼
[Centang Konfirmasi & Kirim]
       │
       ▼
[Tersimpan di DB attendances (status: pending/valid)]
       │
       ├─────────────────────────────────────────┐
       ▼                                         ▼
[Muncul di Live Feed Harian]             [Muncul di Panel Dashboard Admin]
                                                 │
                                                 ▼
                                        [Admin Tinjau Bukti Foto]
                                                 │
                                                 ├──> [Valid] -> Klik ACC
                                                 └──> [Tidak Sesuai] -> Batalkan / Hapus
```

7. Desain Keamanan & Validasi Input

1. Prepared Statements (Anti SQL Injection): Seluruh transaksi basis data menggunakan PDO parameterized queries (`$stmt->prepare()` dan `$stmt->execute()`).
1. Sanitasi Output (Anti XSS): Penggunaan fungsi pembungkus `sanitize()` dengan `htmlspecialchars(..., ENT_QUOTES, 'UTF-8')` pada seluruh data dinamis sebelum dirender ke HTML.
1. Validasi File Upload Ketat:
   - Verifikasi kode status error `UPLOAD_ERR_OK`.
   - Pembatasan ukuran maksimum berkas sebesar `2 * 1024 * 1024` bytes (2 MB).
   - Validasi whitelist ekstensi berkas (`jpg`, `jpeg`, `png`, `webp`).
   - Penamaan ulang file acak dengan timestamp dan byte acak (`piket_YYYYmmdd_His_xxxx.ext`) guna mencegah eksekusi skrip berbahaya dan tabrakan nama file.
1. Logika Tanggal & Jam:
   - Jadwal dibatasi secara prosedural hanya pada hari Senin (`date('N') == 1`) dan Kamis (`date('N') == 4`).
   - Pengecekan interval waktu `strtotime($jam_selesai) > strtotime($jam_mulai)`.
1. Autentikasi Sesi:
   - Pengecekan session pada rute tertutup via `includes/auth_check.php`.
   - Regenerasi ID sesi saat login untuk mitigasi ancaman _Session Fixation_.
