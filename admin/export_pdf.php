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
$total_sesi = count($daftar_tanggal);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Jadwal Piket <?= $daftar_nama_bulan[$bulan] ?> <?= $tahun ?></title>
    <style>
        @page {
            size: A4 landscape;
            margin: 0;
        }
        * {
            box-sizing: border-box;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
            color: #000000;
            margin: 0;
            padding: 16px;
            background: #ffffff;
            font-size: 12px;
        }
        .no-print {
            margin-bottom: 20px;
            padding: 12px 16px;
            background: #FAF8F5;
            border: 2px solid #000000;
            box-shadow: 4px 4px 0px #000000;
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-family: monospace;
            font-size: 12px;
            font-weight: bold;
        }
        .no-print .btn-group {
            display: flex;
            gap: 10px;
        }
        .btn-print {
            background: #B8E926;
            color: #000000;
            border: 2px solid #000000;
            padding: 8px 16px;
            font-family: monospace;
            font-size: 12px;
            font-weight: bold;
            text-transform: uppercase;
            box-shadow: 2px 2px 0px #000000;
            cursor: pointer;
        }
        .btn-back {
            background: #ffffff;
            color: #000000;
            border: 2px solid #000000;
            padding: 8px 16px;
            font-family: monospace;
            font-size: 12px;
            font-weight: bold;
            text-decoration: none;
            text-transform: uppercase;
            box-shadow: 2px 2px 0px #000000;
            display: inline-block;
        }
        @media print {
            .no-print {
                display: none !important;
            }
            body {
                padding: 10mm;
                margin: 0;
            }
        }
        .document-header {
            margin-bottom: 16px;
            padding-bottom: 12px;
            border-bottom: 2px solid #000000;
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
        }
        .doc-title-box h1 {
            margin: 0;
            font-size: 20px;
            font-weight: 900;
            text-transform: uppercase;
            letter-spacing: -0.5px;
        }
        .doc-title-box p {
            margin: 4px 0 0 0;
            font-family: monospace;
            font-size: 12px;
            font-weight: bold;
            color: #475569;
            text-transform: uppercase;
        }
        .doc-badge {
            background: #164E33;
            color: #ffffff;
            border: 2px solid #000000;
            padding: 4px 12px;
            font-family: monospace;
            font-weight: bold;
            font-size: 11px;
            box-shadow: 2px 2px 0px #000000;
            text-transform: uppercase;
        }
        .grid-container {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            grid-auto-rows: 1fr;
            gap: 12px;
        }
        .neo-card {
            background: #FAF8F5;
            border: 2px solid #000000;
            box-shadow: 3px 3px 0px #000000;
            padding: 16px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            min-width: 0;
            min-height: 250px;
            height: 100%;
            break-inside: avoid;
            page-break-inside: avoid;
        }
        .card-top-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .badge-day {
            border: 2px solid #000000;
            padding: 3px 10px;
            font-family: monospace;
            font-weight: bold;
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .badge-kamis {
            background: #E84125;
            color: #ffffff;
        }
        .badge-senin {
            background: #000000;
            color: #ffffff;
        }
        .badge-count {
            border: 2px solid #000000;
            padding: 3px 10px;
            font-family: monospace;
            font-weight: bold;
            font-size: 11px;
        }
        .badge-count-filled {
            background: #164E33;
            color: #ffffff;
        }
        .badge-count-empty {
            background: #ffffff;
            color: #000000;
        }
        .card-date {
            font-size: 15px;
            font-weight: bold;
            color: #000000;
            margin-top: 10px;
            margin-bottom: 12px;
        }
        .members-container {
            display: flex;
            flex-direction: column;
            gap: 8px;
            flex-grow: 1;
        }
        .member-card {
            background: #ffffff;
            border: 2px solid #000000;
            box-shadow: 2px 2px 0px #000000;
            padding: 6px 8px;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .member-avatar {
            width: 28px;
            height: 28px;
            background: #000000;
            color: #ffffff;
            border: 2px solid #000000;
            font-family: monospace;
            font-weight: bold;
            font-size: 11px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }
        .member-details {
            overflow: hidden;
            min-width: 0;
        }
        .member-name {
            font-weight: bold;
            font-size: 12px;
            color: #000000;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            line-height: 1.2;
        }
        .member-divisi {
            font-family: monospace;
            font-size: 10px;
            color: #475569;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            margin-top: 1px;
        }
        .empty-state {
            border: 2px dashed #94a3b8;
            background: #ffffff;
            padding: 16px 8px;
            text-align: center;
            font-family: monospace;
            font-size: 11px;
            font-style: italic;
            color: #64748b;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-grow: 1;
            min-height: 110px;
            height: 100%;
        }
    </style>
</head>
<body>
    <div class="no-print">
        <span>Tekan tombol di samping atau gunakan shortcut <strong>Ctrl + P</strong> lalu pilih <em>Save as PDF</em> untuk mencetak / menyimpan PDF (hilangkan centang <em>Headers and footers</em> jika masih muncul di print preview).</span>
        <div class="btn-group">
            <a href="schedule.php?bulan=<?= $bulan ?>&tahun=<?= $tahun ?>" class="btn-back">&larr; Kembali</a>
            <button onclick="window.print()" class="btn-print">Cetak / Simpan PDF</button>
        </div>
    </div>

    <div class="document-header">
        <div class="doc-title-box">
            <h1>Jadwal Piket Pengurus Himpunan Mahasiswa</h1>
            <p>Periode: <?= $daftar_nama_bulan[$bulan] ?> <?= $tahun ?> (Senin &amp; Kamis)</p>
        </div>
        <div class="doc-badge">
            TOTAL: <?= $total_sesi ?> Sesi Piket
        </div>
    </div>

    <div class="grid-container">
        <?php foreach ($jadwal_per_tanggal as $tgl => $info): ?>
            <?php 
                $jml_anggota = count($info['anggota']);
                $ada_anggota = ($jml_anggota > 0);
                $is_kamis = ($info['hari'] === 'Kamis');
            ?>
            <div class="neo-card">
                <div>
                    <div class="card-top-row">
                        <?php if ($is_kamis): ?>
                            <span class="badge-day badge-kamis">KAMIS</span>
                        <?php else: ?>
                            <span class="badge-day badge-senin">SENIN</span>
                        <?php endif; ?>

                        <?php if ($ada_anggota): ?>
                            <span class="badge-count badge-count-filled"><?= $jml_anggota ?> Orang</span>
                        <?php else: ?>
                            <span class="badge-count badge-count-empty">0 Orang</span>
                        <?php endif; ?>
                    </div>

                    <div class="card-date">
                        <?= format_tanggal_indo($tgl) ?>
                    </div>
                </div>

                <div class="members-container">
                    <?php if ($ada_anggota): ?>
                        <?php foreach ($info['anggota'] as $piket): ?>
                            <div class="member-card">
                                <div class="member-avatar">
                                    <?= dapatkan_inisial($piket['nama']) ?>
                                </div>
                                <div class="member-details">
                                    <div class="member-name"><?= htmlspecialchars($piket['nama']) ?></div>
                                    <div class="member-divisi"><?= htmlspecialchars($piket['nama_divisi']) ?></div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="empty-state">
                            <span>Belum ada pengurus piket</span>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</body>
</html>
