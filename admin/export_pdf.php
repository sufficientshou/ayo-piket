<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

$bulan = isset($_GET['bulan']) ? (int)$_GET['bulan'] : (int)date('n');
$tahun = isset($_GET['tahun']) ? (int)$_GET['tahun'] : (int)date('Y');

$daftar_nama_bulan = [
    1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
    5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
    9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
];

$daftar_tanggal = ambil_tanggal_senin_kamis($tahun, $bulan);

$stmt = $koneksi->prepare("
    SELECT s.tanggal, s.hari, m.nama, m.nim, d.nama_divisi
    FROM schedules s
    JOIN members m ON s.member_id = m.id
    JOIN divisions d ON m.division_id = d.id
    WHERE s.bulan = ? AND s.tahun = ?
    ORDER BY s.tanggal ASC, m.nama ASC
");
$stmt->execute([$bulan, $tahun]);
$jadwal_raw = $stmt->fetchAll();

$jadwal_per_tanggal = [];
foreach ($daftar_tanggal as $dt) {
    $jadwal_per_tanggal[$dt['tanggal']] = [
        'hari' => $dt['hari'],
        'anggota' => []
    ];
}
foreach ($jadwal_raw as $row) {
    $jadwal_per_tanggal[$row['tanggal']]['anggota'][] = $row;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Jadwal Piket <?= $daftar_nama_bulan[$bulan] ?> <?= $tahun ?></title>
    <style>
        @page {
            size: A4 landscape;
            margin: 15mm;
        }
        body {
            font-family: Arial, sans-serif;
            color: #333;
            margin: 0;
            padding: 20px;
            font-size: 12px;
        }
        .header {
            text-align: center;
            border-bottom: 2px solid #333;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }
        .header h1 {
            margin: 0;
            font-size: 18px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        .header h2 {
            margin: 5px 0 0 0;
            font-size: 14px;
            font-weight: normal;
        }
        .grid-container {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 12px;
        }
        .card {
            border: 1px solid #ccc;
            border-radius: 6px;
            overflow: hidden;
            background: #fafafa;
        }
        .card-header {
            background: #2b3a4a;
            color: white;
            padding: 6px 10px;
            font-weight: bold;
            display: flex;
            justify-content: space-between;
        }
        .card-body {
            padding: 8px;
            min-height: 100px;
            background: #fff;
        }
        .member-item {
            padding: 4px 0;
            border-bottom: 1px dashed #eee;
        }
        .member-item:last-child {
            border-bottom: none;
        }
        .member-name {
            font-weight: bold;
            color: #111;
        }
        .member-div {
            font-size: 10px;
            color: #666;
        }
        .footer-note {
            margin-top: 25px;
            display: flex;
            justify-content: space-between;
            font-size: 11px;
        }
        .ttd-box {
            text-align: center;
            margin-top: 20px;
        }
        .no-print {
            margin-bottom: 20px;
            padding: 10px;
            background: #eef2ff;
            border: 1px solid #c7d2fe;
            border-radius: 6px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .btn-print {
            background: #4f46e5;
            color: white;
            border: none;
            padding: 8px 16px;
            font-weight: bold;
            border-radius: 4px;
            cursor: pointer;
        }
        @media print {
            .no-print {
                display: none;
            }
            body {
                padding: 0;
            }
        }
    </style>
</head>
<body>
    <div class="no-print">
        <span>Tekan tombol di samping atau gunakan shortcut <strong>Ctrl + P</strong> lalu pilih <em>Save as PDF</em> untuk mendownload file PDF.</span>
        <button onclick="window.print()" class="btn-print">Cetak / Simpan PDF</button>
    </div>

    <div class="header">
        <h1>Jadwal Piket Pengurus Himpunan Mahasiswa</h1>
        <h2>Periode: <?= $daftar_nama_bulan[$bulan] ?> <?= $tahun ?> (Senin & Kamis)</h2>
    </div>

    <div class="grid-container">
        <?php foreach ($jadwal_per_tanggal as $tgl => $info): ?>
            <div class="card">
                <div class="card-header">
                    <span><?= $info['hari'] ?></span>
                    <span><?= date('d/m/Y', strtotime($tgl)) ?></span>
                </div>
                <div class="card-body">
                    <?php if (empty($info['anggota'])): ?>
                        <div style="color: #999; font-style: italic; text-align: center; padding-top: 20px;">Tidak ada jadwal</div>
                    <?php else: ?>
                        <?php $no = 1; foreach ($info['anggota'] as $ang): ?>
                            <div class="member-item">
                                <div class="member-name"><?= $no++ ?>. <?= sanitize($ang['nama']) ?></div>
                                <div class="member-div"><?= sanitize($ang['nama_divisi']) ?></div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <div class="footer-note">
        <div>
            Dicetak pada: <?= date('d/m/Y H:i') ?> WIB<br>
            * Wajib hadir sesuai jadwal dan mengisi form laporan piket.
        </div>
        <div class="ttd-box">
            Mengetahui,<br>
            Koordinator Piket Himpunan<br><br><br><br>
            ( _______________________ )
        </div>
    </div>
</body>
</html>
