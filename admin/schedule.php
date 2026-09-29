<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../includes/auth_check.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

$bulan_terpilih = isset($_GET['bulan']) ? (int)$_GET['bulan'] : (int)date('n');
$tahun_terpilih = isset($_GET['tahun']) ? (int)$_GET['tahun'] : (int)date('Y');

$daftar_tanggal = ambil_tanggal_senin_kamis($tahun_terpilih, $bulan_terpilih);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $aksi_form = $_POST['aksi'] ?? '';

    if ($aksi_form === 'gacha') {
        $stmt_active = $koneksi->query("SELECT id, division_id FROM members WHERE is_active = 1");
        $semua_pengurus = $stmt_active->fetchAll();

        if (empty($semua_pengurus)) {
            set_flash_message('gagal', 'Tidak ada pengurus aktif untuk diacak.');
            header("Location: schedule.php?bulan=$bulan_terpilih&tahun=$tahun_terpilih");
            exit();
        }

        if (empty($daftar_tanggal)) {
            set_flash_message('gagal', 'Tidak ditemukan hari Senin atau Kamis pada bulan dan tahun ini.');
            header("Location: schedule.php?bulan=$bulan_terpilih&tahun=$tahun_terpilih");
            exit();
        }

        $stmt_hapus = $koneksi->prepare("DELETE FROM schedules WHERE bulan = ? AND tahun = ?");
        $stmt_hapus->execute([$bulan_terpilih, $tahun_terpilih]);

        $kelompok_divisi = [];
        foreach ($semua_pengurus as $p) {
            $kelompok_divisi[$p['division_id']][] = $p['id'];
        }

        foreach ($kelompok_divisi as $div_id => $anggota) {
            shuffle($kelompok_divisi[$div_id]);
        }

        $urutan_pengurus = [];
        $ada_sisa = true;
        while ($ada_sisa) {
            $ada_sisa = false;
            foreach ($kelompok_divisi as $div_id => &$list_id) {
                if (!empty($list_id)) {
                    $urutan_pengurus[] = array_pop($list_id);
                    $ada_sisa = true;
                }
            }
        }

        $total_tanggal = count($daftar_tanggal);
        $indeks_tanggal = 0;

        $stmt_insert = $koneksi->prepare("
            INSERT INTO schedules (member_id, tanggal, hari, bulan, tahun, status) 
            VALUES (?, ?, ?, ?, ?, 'draft')
        ");

        foreach ($urutan_pengurus as $id_member) {
            $info_slot = $daftar_tanggal[$indeks_tanggal % $total_tanggal];
            $stmt_insert->execute([
                $id_member,
                $info_slot['tanggal'],
                $info_slot['hari'],
                $bulan_terpilih,
                $tahun_terpilih
            ]);
            $indeks_tanggal++;
        }

        set_flash_message('sukses', 'Gacha jadwal berhasil! Jadwal telah dibagikan secara merata dalam status DRAFT.');
        header("Location: schedule.php?bulan=$bulan_terpilih&tahun=$tahun_terpilih");
        exit();
    }

    if ($aksi_form === 'tambah_manual') {
        $member_id = (int)($_POST['member_id'] ?? 0);
        $tanggal = sanitize($_POST['tanggal'] ?? '');

        if ($member_id > 0 && !empty($tanggal)) {
            $hari_indeks = date('N', strtotime($tanggal));
            $hari_nama = ($hari_indeks == 1) ? 'Senin' : (($hari_indeks == 4) ? 'Kamis' : '');

            if (empty($hari_nama)) {
                set_flash_message('gagal', 'Piket hanya diperbolehkan pada hari Senin atau Kamis.');
            } else {
                $stmt_cek = $koneksi->prepare("SELECT id FROM schedules WHERE member_id = ? AND tanggal = ?");
                $stmt_cek->execute([$member_id, $tanggal]);
                if ($stmt_cek->fetch()) {
                    set_flash_message('gagal', 'Pengurus ini sudah memiliki jadwal pada tanggal tersebut.');
                } else {
                    $stmt_ins = $koneksi->prepare("
                        INSERT INTO schedules (member_id, tanggal, hari, bulan, tahun, status) 
                        VALUES (?, ?, ?, ?, ?, 'draft')
                    ");
                    $stmt_ins->execute([$member_id, $tanggal, $hari_nama, $bulan_terpilih, $tahun_terpilih]);
                    set_flash_message('sukses', 'Pengurus berhasil ditambahkan! Jangan lupa klik tombol PUBLISH KE PUBLIK agar tampil di halaman publik.');
                }
            }
        }
        header("Location: schedule.php?bulan=$bulan_terpilih&tahun=$tahun_terpilih");
        exit();
    }

    if ($aksi_form === 'ubah_status') {
        $status_baru = sanitize($_POST['status'] ?? 'draft');
        $stmt_stat = $koneksi->prepare("UPDATE schedules SET status = ? WHERE bulan = ? AND tahun = ?");
        $stmt_stat->execute([$status_baru, $bulan_terpilih, $tahun_terpilih]);
        set_flash_message('sukses', 'Status jadwal berhasil diubah menjadi ' . strtoupper($status_baru));
        header("Location: schedule.php?bulan=$bulan_terpilih&tahun=$tahun_terpilih");
        exit();
    }

    if ($aksi_form === 'reset') {
        $stmt_reset = $koneksi->prepare("DELETE FROM schedules WHERE bulan = ? AND tahun = ?");
        $stmt_reset->execute([$bulan_terpilih, $tahun_terpilih]);
        set_flash_message('sukses', 'Semua jadwal untuk bulan ini berhasil direset/dikosongkan.');
        header("Location: schedule.php?bulan=$bulan_terpilih&tahun=$tahun_terpilih");
        exit();
    }
}

if (isset($_GET['hapus_id'])) {
    $id_hapus = (int)$_GET['hapus_id'];
    $stmt_del = $koneksi->prepare("DELETE FROM schedules WHERE id = ?");
    $stmt_del->execute([$id_hapus]);
    set_flash_message('sukses', 'Anggota berhasil dihapus dari jadwal tanggal terkait.');
    header("Location: schedule.php?bulan=$bulan_terpilih&tahun=$tahun_terpilih");
    exit();
}

$stmt_jadwal = $koneksi->prepare("
    SELECT s.id AS schedule_id, s.tanggal, s.hari, s.status, m.id AS member_id, m.nama, m.nim, d.nama_divisi
    FROM schedules s
    JOIN members m ON s.member_id = m.id
    JOIN divisions d ON m.division_id = d.id
    WHERE s.bulan = ? AND s.tahun = ?
    ORDER BY s.tanggal ASC, m.nama ASC
");
$stmt_jadwal->execute([$bulan_terpilih, $tahun_terpilih]);
$daftar_jadwal_raw = $stmt_jadwal->fetchAll();

$jadwal_per_tanggal = [];
$status_periode = 'draft';
$total_terjadwal = 0;

foreach ($daftar_tanggal as $dt) {
    $jadwal_per_tanggal[$dt['tanggal']] = [
        'hari' => $dt['hari'],
        'anggota' => []
    ];
}

foreach ($daftar_jadwal_raw as $row) {
    $status_periode = $row['status'];
    $total_terjadwal++;
    $jadwal_per_tanggal[$row['tanggal']]['anggota'][] = $row;
}

if (isset($_GET['export']) && $_GET['export'] === 'excel') {
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename=jadwal_piket_' . $bulan_terpilih . '_' . $tahun_terpilih . '.csv');
    $output = fopen('php://output', 'w');
    fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
    fputcsv($output, ['No', 'Tanggal', 'Hari', 'NIM', 'Nama Pengurus', 'Divisi', 'Status']);
    $no = 1;
    foreach ($daftar_tanggal as $dt) {
        $tgl_key = $dt['tanggal'];
        $list_anggota = $jadwal_per_tanggal[$tgl_key]['anggota'] ?? [];
        if (empty($list_anggota)) {
            fputcsv($output, [$no++, $tgl_key, $dt['hari'], '-', '(Belum ada pengurus)', '-', strtoupper($status_periode)]);
        } else {
            foreach ($list_anggota as $piket) {
                fputcsv($output, [$no++, $tgl_key, $dt['hari'], $piket['nim'], $piket['nama'], $piket['nama_divisi'], strtoupper($status_periode)]);
            }
        }
    }
    fclose($output);
    exit();
}

$stmt_all_members = $koneksi->query("
    SELECT m.id, m.nama, d.nama_divisi 
    FROM members m 
    JOIN divisions d ON m.division_id = d.id 
    WHERE m.is_active = 1 
    ORDER BY m.nama ASC
");
$semua_pengurus_pilihan = $stmt_all_members->fetchAll();

$daftar_nama_bulan = [
    1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
    5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
    9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
];

$total_sesi = count($daftar_tanggal);
$total_kapasitas = $total_sesi * 2;
$slot_terisi = min($total_terjadwal, $total_kapasitas);
$slot_kosong = max(0, $total_kapasitas - $total_terjadwal);
$persen_terisi = $total_kapasitas > 0 ? round(($slot_terisi / $total_kapasitas) * 100) : 0;
$distinct_pengurus = count(array_unique(array_column($daftar_jadwal_raw, 'member_id')));

$judul_halaman = "Jadwal Piket & Gacha";
require_once __DIR__ . '/header.php';
?>

<div class="space-y-6 sm:space-y-7">

    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
        <div>
            <h1 class="text-2xl sm:text-3xl lg:text-4xl font-black uppercase tracking-tight text-black">
                Jadwal Piket &amp; Gacha
            </h1>
        </div>

        <div class="flex flex-wrap items-center gap-3 shrink-0">
            <form action="schedule.php" method="GET" class="flex items-center border-2 border-black bg-white shadow-[3px_3px_0px_#000]">
                <select name="bulan" class="px-3 py-2 bg-transparent font-mono font-bold text-xs uppercase tracking-wider text-black focus:outline-none border-r-2 border-black cursor-pointer">
                    <?php foreach ($daftar_nama_bulan as $b_num => $b_nama): ?>
                        <option value="<?= $b_num ?>" <?= $bulan_terpilih == $b_num ? 'selected' : '' ?>><?= strtoupper($b_nama) ?></option>
                    <?php endforeach; ?>
                </select>
                <select name="tahun" class="px-3 py-2 bg-transparent font-mono font-bold text-xs uppercase tracking-wider text-black focus:outline-none border-r-2 border-black cursor-pointer">
                    <?php for ($th = 2025; $th <= 2028; $th++): ?>
                        <option value="<?= $th ?>" <?= $tahun_terpilih == $th ? 'selected' : '' ?>><?= $th ?></option>
                    <?php endfor; ?>
                </select>
                <button type="submit" class="px-4 py-2 bg-black text-white hover:bg-zinc-800 font-mono font-bold text-xs uppercase tracking-wider transition cursor-pointer">
                    CARI
                </button>
            </form>

            <a href="export_pdf.php?bulan=<?= $bulan_terpilih ?>&tahun=<?= $tahun_terpilih ?>" target="_blank"
               class="px-4 py-2 bg-[#B8E926] border-2 border-black text-black font-mono font-bold text-xs uppercase tracking-wider shadow-[3px_3px_0px_#000] hover:translate-x-[1px] hover:translate-y-[1px] transition flex items-center gap-2 cursor-pointer">
                <svg class="w-4 h-4 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                </svg>
                <span>CETAK REKAP</span>
            </a>

            <?php if ($status_periode === 'published'): ?>
                <form action="schedule.php?bulan=<?= $bulan_terpilih ?>&tahun=<?= $tahun_terpilih ?>" method="POST">
                    <input type="hidden" name="aksi" value="ubah_status">
                    <input type="hidden" name="status" value="draft">
                    <button type="submit" class="px-4 py-2 bg-white hover:bg-zinc-100 text-black border-2 border-black font-mono font-bold text-xs uppercase tracking-wider shadow-[3px_3px_0px_#000] hover:translate-x-[1px] hover:translate-y-[1px] transition flex items-center gap-2 cursor-pointer whitespace-nowrap">
                        <span class="w-2.5 h-2.5 rounded-full bg-[#164E33] shrink-0"></span>
                        <span>TERPUBLIKASI (TARIK KE DRAFT)</span>
                    </button>
                </form>
            <?php else: ?>
                <form action="schedule.php?bulan=<?= $bulan_terpilih ?>&tahun=<?= $tahun_terpilih ?>" method="POST">
                    <input type="hidden" name="aksi" value="ubah_status">
                    <input type="hidden" name="status" value="published">
                    <button type="submit" class="px-4 py-2 bg-[#164E33] hover:bg-[#123e29] text-white border-2 border-black font-mono font-bold text-xs uppercase tracking-wider shadow-[3px_3px_0px_#000] hover:translate-x-[1px] hover:translate-y-[1px] transition flex items-center gap-2 cursor-pointer whitespace-nowrap">
                        <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 10l7-7m0 0l7 7m-7-7v18"/></svg>
                        <span>PUBLISH KE PUBLIK</span>
                    </button>
                </form>
            <?php endif; ?>
        </div>
    </div>

    <div class="bg-white border-2 border-black shadow-[4px_4px_0px_#000] p-5 sm:p-6 flex flex-col xl:flex-row xl:items-center justify-between gap-5 sm:gap-6">
        <div>
            <div class="flex items-center gap-2.5">
                <svg class="w-5 h-5 text-black shrink-0" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4" />
                </svg>
                <h2 class="text-sm sm:text-base font-mono font-bold uppercase tracking-wider text-black">
                    AKSI PENJADWALAN &amp; GACHA OTOMATIS
                </h2>
            </div>
        </div>

        <div class="flex flex-wrap items-center gap-2.5 shrink-0">
            <form action="schedule.php?bulan=<?= $bulan_terpilih ?>&tahun=<?= $tahun_terpilih ?>" method="POST"
                  onsubmit="return confirm('Jalankan gacha acak? Jadwal lama di bulan ini akan digantikan secara adil.')">
                <input type="hidden" name="aksi" value="gacha">
                <button type="submit" class="px-4 py-2.5 bg-[#164E33] hover:bg-[#123e29] text-white border-2 border-black font-mono font-bold text-xs uppercase tracking-wider shadow-[3px_3px_0px_#000] hover:translate-x-[1px] hover:translate-y-[1px] transition flex items-center gap-2 cursor-pointer whitespace-nowrap">
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                    <span>GACHA JADWAL (ACAK RATA DIVISI)</span>
                </button>
            </form>

            <button type="button" onclick="bukaModalManual()"
                    class="px-4 py-2.5 bg-white hover:bg-zinc-50 text-black border-2 border-black font-mono font-bold text-xs uppercase tracking-wider shadow-[3px_3px_0px_#000] hover:translate-x-[1px] hover:translate-y-[1px] transition flex items-center gap-2 cursor-pointer whitespace-nowrap">
                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/></svg>
                <span>+ TAMBAH PETUGAS MANUAL</span>
            </button>

            <form action="schedule.php?bulan=<?= $bulan_terpilih ?>&tahun=<?= $tahun_terpilih ?>" method="POST"
                  onsubmit="return confirm('Kosongkan semua jadwal bulan ini?')">
                <input type="hidden" name="aksi" value="reset">
                <button type="submit" class="px-4 py-2.5 bg-white hover:bg-red-50 text-[#dc2626] border-2 border-black font-mono font-bold text-xs uppercase tracking-wider shadow-[3px_3px_0px_#000] hover:translate-x-[1px] hover:translate-y-[1px] transition flex items-center gap-2 cursor-pointer whitespace-nowrap">
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                    <span>RESET JADWAL</span>
                </button>
            </form>
        </div>
    </div>

    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 font-mono text-xs">
        <div class="flex items-center gap-2">
            <span class="font-bold text-zinc-500 uppercase tracking-wider">STATUS JADWAL:</span>
            <?php if ($status_periode === 'published'): ?>
                <span class="bg-[#164E33] text-white border-2 border-black px-2.5 py-0.5 font-bold uppercase tracking-wider text-[11px]">
                    PUBLIK (AKTIF)
                </span>
            <?php else: ?>
                <span class="bg-[#eab308] text-black border-2 border-black px-2.5 py-0.5 font-bold uppercase tracking-wider text-[11px]">
                    DRAFT (BELUM TAMPIL DI PUBLIK)
                </span>
            <?php endif; ?>
        </div>

        <div class="font-bold tracking-wider text-black">
            TOTAL: <?= $total_sesi ?> TANGGAL OPERASIONAL
        </div>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
        <?php foreach ($jadwal_per_tanggal as $tgl => $info): ?>
            <?php 
                $jml_anggota = count($info['anggota']);
                $ada_anggota = ($jml_anggota > 0);
                $is_kamis = ($info['hari'] === 'Kamis');
            ?>
            <div class="bg-[#FAF8F5] border-2 border-black p-5 sm:p-6 shadow-[4px_4px_0px_#000] flex flex-col justify-between min-h-[240px] sm:min-h-[270px]">
                <div>
                    <div class="flex items-center justify-between">
                        <?php if ($is_kamis): ?>
                            <span class="bg-[#E84125] text-white border-2 border-black px-3 py-1 text-xs font-mono font-bold uppercase tracking-wider">
                                KAMIS
                            </span>
                        <?php else: ?>
                            <span class="bg-black text-white border-2 border-black px-3 py-1 text-xs font-mono font-bold uppercase tracking-wider">
                                SENIN
                            </span>
                        <?php endif; ?>

                        <?php if ($ada_anggota): ?>
                            <span class="bg-[#164E33] text-white border-2 border-black px-3 py-1 text-xs font-mono font-bold">
                                <?= $jml_anggota ?> Orang
                            </span>
                        <?php else: ?>
                            <span class="bg-white text-black border-2 border-black px-3 py-1 text-xs font-mono font-bold">
                                0 Orang
                            </span>
                        <?php endif; ?>
                    </div>

                    <h3 class="text-base sm:text-lg font-bold text-black mt-4 mb-4">
                        <?= format_tanggal_indo($tgl) ?>
                    </h3>

                    <div class="space-y-2.5">
                        <?php if ($ada_anggota): ?>
                            <?php foreach ($info['anggota'] as $piket): ?>
                                <div class="border-2 border-black bg-white p-2.5 flex items-center justify-between shadow-[2px_2px_0px_#000]">
                                    <div class="flex items-center gap-2.5 min-w-0 pr-2">
                                        <div class="w-8 h-8 bg-black text-white border-2 border-black flex items-center justify-center font-mono font-bold text-xs shrink-0">
                                            <?= dapatkan_inisial($piket['nama']) ?>
                                        </div>
                                        <div class="overflow-hidden min-w-0">
                                            <div class="font-bold text-xs sm:text-sm text-black truncate"><?= htmlspecialchars($piket['nama']) ?></div>
                                            <div class="font-mono text-[10px] sm:text-xs text-slate-600 truncate"><?= htmlspecialchars($piket['nama_divisi']) ?></div>
                                        </div>
                                    </div>
                                    <a href="schedule.php?bulan=<?= $bulan_terpilih ?>&tahun=<?= $tahun_terpilih ?>&hapus_id=<?= $piket['schedule_id'] ?>"
                                       onclick="return confirm('Hapus <?= htmlspecialchars($piket['nama']) ?> dari jadwal tanggal ini?')"
                                       class="w-7 h-7 border-2 border-black bg-white hover:bg-[#E84125] hover:text-white flex items-center justify-center font-mono font-bold text-sm transition shrink-0"
                                       title="Hapus">
                                        &times;
                                    </a>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>

                        <?php if ($jml_anggota < 2): ?>
                            <button type="button" onclick="bukaModalManual('<?= $tgl ?>')"
                                    class="w-full border-2 border-dashed border-black py-2.5 bg-white font-mono font-bold text-xs hover:bg-zinc-100 flex items-center justify-center gap-1.5 transition cursor-pointer">
                                <span>&oplus;</span>
                                <span>+ TAMBAH PENGURUS</span>
                            </button>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

</div>

<div id="modal_manual" class="fixed inset-0 bg-black/60 z-50 flex items-center justify-center p-4 hidden" onclick="tutupModalManual()">
    <div class="bg-[#FAF8F5] border-2 border-black shadow-[6px_6px_0px_#000] max-w-md w-full p-6 relative" onclick="event.stopPropagation()">
        <div class="flex justify-between items-center border-b-2 border-black pb-3 mb-4">
            <div class="flex items-center gap-2">
                <span class="bg-[#B8E926] border border-black px-2 py-0.5 font-mono text-[10px] font-bold uppercase text-black">MANUAL</span>
                <h3 class="font-black text-black text-sm uppercase">Tambah Jadwal Manual</h3>
            </div>
            <button type="button" onclick="tutupModalManual()" class="w-7 h-7 border-2 border-black bg-white flex items-center justify-center font-bold text-sm hover:bg-black hover:text-white transition cursor-pointer">&times;</button>
        </div>

        <form action="schedule.php?bulan=<?= $bulan_terpilih ?>&tahun=<?= $tahun_terpilih ?>" method="POST" class="space-y-4">
            <input type="hidden" name="aksi" value="tambah_manual">

            <div>
                <label for="tanggal_manual" class="block font-mono text-xs font-bold uppercase text-black mb-1.5">
                    PILIH TANGGAL PIKET (SENIN / KAMIS)
                </label>
                <select name="tanggal" id="tanggal_manual" required class="w-full px-3.5 py-2.5 bg-white border-2 border-black font-mono text-xs font-bold text-black focus:outline-none">
                    <?php foreach ($daftar_tanggal as $dt): ?>
                        <option value="<?= $dt['tanggal'] ?>"><?= strtoupper($dt['hari']) ?>, <?= date('j', strtotime($dt['tanggal'])) ?> <?= strtoupper($daftar_nama_bulan[(int)date('n', strtotime($dt['tanggal']))]) ?> <?= date('Y', strtotime($dt['tanggal'])) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div>
                <label for="member_id_manual" class="block font-mono text-xs font-bold uppercase text-black mb-1.5">
                    PILIH PENGURUS
                </label>
                <select name="member_id" id="member_id_manual" required class="w-full px-3.5 py-2.5 bg-white border-2 border-black font-mono text-xs font-bold text-black focus:outline-none">
                    <option value="">-- PILIH PENGURUS --</option>
                    <?php foreach ($semua_pengurus_pilihan as $png): ?>
                        <option value="<?= $png['id'] ?>"><?= htmlspecialchars(strtoupper($png['nama'])) ?> (<?= htmlspecialchars(strtoupper($png['nama_divisi'])) ?>)</option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="flex items-center justify-end gap-2.5 pt-3 border-t-2 border-black">
                <button type="button" onclick="tutupModalManual()" class="px-4 py-2 bg-white hover:bg-zinc-100 text-black border-2 border-black font-mono font-bold text-xs uppercase tracking-wider shadow-[2px_2px_0px_#000] transition cursor-pointer">
                    BATAL
                </button>
                <button type="submit" class="px-5 py-2 bg-[#164E33] hover:bg-[#123e29] text-white border-2 border-black font-mono font-bold text-xs uppercase tracking-wider shadow-[2px_2px_0px_#000] hover:translate-x-[1px] hover:translate-y-[1px] transition cursor-pointer">
                    SIMPAN JADWAL
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function bukaModalManual(tanggal = '') {
    if (tanggal) {
        document.getElementById('tanggal_manual').value = tanggal;
    }
    document.getElementById('modal_manual').classList.remove('hidden');
}
function tutupModalManual() {
    document.getElementById('modal_manual').classList.add('hidden');
}
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') tutupModalManual();
});
</script>

<?php require_once __DIR__ . '/footer.php'; ?>
