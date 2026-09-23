<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function base_url($path = "") {
    $protokol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http";
    $host = $_SERVER['HTTP_HOST'];
    $script = dirname($_SERVER['SCRIPT_NAME']);
    $folder = trim(str_replace('\\', '/', $script), '/');
    $folder_root = explode('/', $folder)[0];
    $root = $folder_root ? "/" . $folder_root : "";
    return $protokol . "://" . $host . $root . "/" . ltrim($path, '/');
}

function sanitize($data) {
    return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
}

function set_flash_message($tipe, $pesan) {
    $_SESSION['flash_message'] = [
        'tipe' => $tipe,
        'pesan' => $pesan
    ];
}

function get_flash_message() {
    if (isset($_SESSION['flash_message'])) {
        $flash = $_SESSION['flash_message'];
        unset($_SESSION['flash_message']);
        return $flash;
    }
    return null;
}

function format_tanggal_indo($tanggal) {
    $daftar_bulan = [
        1 => 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
        'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
    ];
    $pecah = explode('-', $tanggal);
    $hari = (int)$pecah[2];
    $bulan = (int)$pecah[1];
    $tahun = $pecah[0];
    return $hari . ' ' . $daftar_bulan[$bulan] . ' ' . $tahun;
}

function ambil_tanggal_senin_kamis($tahun, $bulan) {
    $hasil = [];
    $jumlah_hari = cal_days_in_month(CAL_GREGORIAN, $bulan, $tahun);
    
    for ($hari = 1; $hari <= $jumlah_hari; $hari++) {
        $tanggal_teks = sprintf('%04d-%02d-%02d', $tahun, $bulan, $hari);
        $hari_indeks = date('N', strtotime($tanggal_teks));
        
        if ($hari_indeks == 1) {
            $hasil[] = [
                'tanggal' => $tanggal_teks,
                'hari' => 'Senin'
            ];
        } else if ($hari_indeks == 4) {
            $hasil[] = [
                'tanggal' => $tanggal_teks,
                'hari' => 'Kamis'
            ];
        }
    }
    
    return $hasil;
}

function upload_foto_dokumentasi($file) {
    $folder_tujuan = __DIR__ . '/../uploads/dokumentasi/';
    
    if (!is_dir($folder_tujuan)) {
        mkdir($folder_tujuan, 0777, true);
    }
    
    $nama_asli = $file['name'];
    $ukuran = $file['size'];
    $error = $file['error'];
    $tmp_name = $file['tmp_name'];
    
    if ($error !== UPLOAD_ERR_OK) {
        return ['sukses' => false, 'pesan' => 'Terjadi kesalahan saat upload file'];
    }
    
    if ($ukuran > 2 * 1024 * 1024) {
        return ['sukses' => false, 'pesan' => 'Ukuran file foto maksimal 2 MB'];
    }
    
    $ekstensi = strtolower(pathinfo($nama_asli, PATHINFO_EXTENSION));
    $ekstensi_diizinkan = ['jpg', 'jpeg', 'png', 'webp'];
    
    if (!in_array($ekstensi, $ekstensi_diizinkan)) {
        return ['sukses' => false, 'pesan' => 'Format file harus JPG, JPEG, PNG, atau WEBP'];
    }
    
    $nama_baru = 'piket_' . date('Ymd_His') . '_' . bin2hex(random_bytes(4)) . '.' . $ekstensi;
    $target_file = $folder_tujuan . $nama_baru;
    
    if (move_uploaded_file($tmp_name, $target_file)) {
        return ['sukses' => true, 'nama_file' => $nama_baru];
    }
    
    return ['sukses' => false, 'pesan' => 'Gagal memindahkan file ke direktori tujuan'];
}

function dapatkan_inisial($nama) {
    $nama = trim($nama);
    $pecah = preg_split('/\s+/', $nama);
    if (count($pecah) >= 2) {
        return strtoupper(substr($pecah[0], 0, 1) . substr($pecah[1], 0, 1));
    }
    if (stripos($nama, 'farjar') !== false) {
        return 'FJ';
    }
    return strtoupper(substr($nama, 0, 2));
}
