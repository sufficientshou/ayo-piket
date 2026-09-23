<?php
require_once __DIR__ . '/../config/database.php';
$judul_halaman = "Kelola Jadwal & Gacha Piket";
require_once __DIR__ . '/header.php';

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
                    set_flash_message('sukses', 'Pengurus berhasil ditambahkan ke jadwal manual.');
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
?>

<div class="space-y-6">
    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
        <div>
            <div class="flex items-center space-x-3">
                <h1 class="text-2xl font-bold text-slate-800">Jadwal Piket</h1>
                <span class="text-xs uppercase font-bold tracking-wider px-2.5 py-1 rounded-full <?= $status_periode === 'published' ? 'bg-emerald-100 text-emerald-800 border border-emerald-300' : 'bg-amber-100 text-amber-800 border border-amber-300' ?>">
                    Status: <?= $status_periode ?>
                </span>
            </div>
            <p class="text-sm text-slate-500 mt-1">Pengaturan jadwal piket khusus hari Senin & Kamis (<?= count($daftar_tanggal) ?> hari dalam bulan ini)</p>
        </div>

        <div class="flex flex-wrap items-center gap-2">
            <form action="schedule.php" method="GET" class="flex items-center space-x-2 bg-white p-1.5 rounded-xl border border-slate-200">
                <select name="bulan" class="text-xs font-medium text-slate-700 bg-transparent px-2 py-1 focus:outline-none">
                    <?php foreach ($daftar_nama_bulan as $b_num => $b_nama): ?>
                        <option value="<?= $b_num ?>" <?= $bulan_terpilih == $b_num ? 'selected' : '' ?>><?= $b_nama ?></option>
                    <?php endforeach; ?>
                </select>
                <select name="tahun" class="text-xs font-medium text-slate-700 bg-transparent px-2 py-1 focus:outline-none">
                    <?php for ($th = date('Y') - 1; $th <= date('Y') + 2; $th++): ?>
                        <option value="<?= $th ?>" <?= $tahun_terpilih == $th ? 'selected' : '' ?>><?= $th ?></option>
                    <?php endfor; ?>
                </select>
                <button type="submit" class="px-2.5 py-1 bg-slate-800 hover:bg-slate-700 text-white rounded-lg text-xs font-semibold">Cari</button>
            </form>

            <a href="export_pdf.php?bulan=<?= $bulan_terpilih ?>&tahun=<?= $tahun_terpilih ?>" target="_blank"
               class="px-3 py-2 bg-rose-600 hover:bg-rose-700 text-white rounded-xl text-xs font-semibold shadow-sm transition flex items-center space-x-1">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                <span>Export PDF</span>
            </a>
        </div>
    </div>

    <div class="bg-white p-5 rounded-2xl shadow-sm border border-slate-200 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div class="space-y-1">
            <h2 class="text-sm font-bold text-slate-800 uppercase tracking-wider">Aksi Penjadwalan</h2>
            <p class="text-xs text-slate-500">Gunakan Gacha otomatis untuk meratakan pengurus antar divisi, atau tambahkan secara manual</p>
        </div>

        <div class="flex flex-wrap items-center gap-2">
            <form action="schedule.php?bulan=<?= $bulan_terpilih ?>&tahun=<?= $tahun_terpilih ?>" method="POST"
                  onsubmit="return confirm('Jalankan gacha acak? Jadwal lama di bulan ini akan digantikan secara adil.')">
                <input type="hidden" name="aksi" value="gacha">
                <button type="submit" class="px-3.5 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold text-xs rounded-xl shadow-sm transition flex items-center space-x-1.5">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
                    <span>Gacha Jadwal (Acak Rata)</span>
                </button>
            </form>

            <button onclick="document.getElementById('modal_manual').classList.remove('hidden')" 
                    class="px-3.5 py-2 bg-slate-800 hover:bg-slate-700 text-white font-semibold text-xs rounded-xl shadow-sm transition">
                + Tambah Manual
            </button>

            <?php if ($total_terjadwal > 0): ?>
                <?php if ($status_periode === 'draft'): ?>
                    <form action="schedule.php?bulan=<?= $bulan_terpilih ?>&tahun=<?= $tahun_terpilih ?>" method="POST">
                        <input type="hidden" name="aksi" value="ubah_status">
                        <input type="hidden" name="status" value="published">
                        <button type="submit" class="px-3.5 py-2 bg-emerald-600 hover:bg-emerald-700 text-white font-semibold text-xs rounded-xl shadow-sm transition">
                            Publish ke Publik
                        </button>
                    </form>
                <?php else: ?>
                    <form action="schedule.php?bulan=<?= $bulan_terpilih ?>&tahun=<?= $tahun_terpilih ?>" method="POST">
                        <input type="hidden" name="aksi" value="ubah_status">
                        <input type="hidden" name="status" value="draft">
                        <button type="submit" class="px-3.5 py-2 bg-amber-600 hover:bg-amber-700 text-white font-semibold text-xs rounded-xl shadow-sm transition">
                            Tarik ke Draft
                        </button>
                    </form>
                <?php endif; ?>

                <form action="schedule.php?bulan=<?= $bulan_terpilih ?>&tahun=<?= $tahun_terpilih ?>" method="POST"
                      onsubmit="return confirm('Kosongkan semua jadwal bulan ini?')">
                    <input type="hidden" name="aksi" value="reset">
                    <button type="submit" class="px-3 py-2 bg-rose-50 text-rose-700 hover:bg-rose-100 font-semibold text-xs rounded-xl border border-rose-200 transition">
                        Reset Jadwal
                    </button>
                </form>
            <?php endif; ?>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
        <?php foreach ($jadwal_per_tanggal as $tgl => $info): ?>
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm flex flex-col overflow-hidden">
                <div class="p-4 bg-slate-50 border-b border-slate-200 flex justify-between items-center">
                    <div>
                        <span class="inline-block px-2 py-0.5 rounded text-xs font-bold uppercase tracking-wider <?= $info['hari'] === 'Senin' ? 'bg-sky-100 text-sky-800' : 'bg-purple-100 text-purple-800' ?>">
                            <?= $info['hari'] ?>
                        </span>
                        <div class="text-sm font-bold text-slate-800 mt-1"><?= format_tanggal_indo($tgl) ?></div>
                    </div>
                    <span class="text-xs bg-slate-200 text-slate-700 font-semibold px-2 py-0.5 rounded-full">
                        <?= count($info['anggota']) ?> Orang
                    </span>
                </div>

                <div class="p-4 flex-grow space-y-2">
                    <?php if (empty($info['anggota'])): ?>
                        <div class="py-6 text-center text-xs text-slate-400 italic">Belum ada pengurus piket</div>
                    <?php else: ?>
                        <?php foreach ($info['anggota'] as $piket): ?>
                            <div class="flex items-center justify-between p-2 rounded-xl bg-slate-50 border border-slate-100 text-xs hover:border-slate-300 transition">
                                <div class="overflow-hidden pr-2">
                                    <div class="font-semibold text-slate-800 truncate"><?= sanitize($piket['nama']) ?></div>
                                    <div class="text-[10px] text-slate-500 truncate"><?= sanitize($piket['nama_divisi']) ?></div>
                                </div>
                                <a href="schedule.php?bulan=<?= $bulan_terpilih ?>&tahun=<?= $tahun_terpilih ?>&hapus_id=<?= $piket['schedule_id'] ?>"
                                   onclick="return confirm('Hapus <?= sanitize($piket['nama']) ?> dari jadwal tanggal ini?')"
                                   class="text-rose-500 hover:text-rose-700 p-1" title="Hapus dari hari ini">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                                </a>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<div id="modal_manual" class="fixed inset-0 bg-slate-900/60 z-50 flex items-center justify-center p-4 hidden">
    <div class="bg-white rounded-2xl max-w-md w-full p-6 shadow-xl space-y-4">
        <div class="flex justify-between items-center border-b border-slate-100 pb-3">
            <h3 class="font-bold text-slate-800 text-base">Tambah Jadwal Manual</h3>
            <button onclick="document.getElementById('modal_manual').classList.add('hidden')" class="text-slate-400 hover:text-slate-600 text-sm font-bold">&times;</button>
        </div>

        <form action="schedule.php?bulan=<?= $bulan_terpilih ?>&tahun=<?= $tahun_terpilih ?>" method="POST" class="space-y-4">
            <input type="hidden" name="aksi" value="tambah_manual">

            <div>
                <label for="tanggal_manual" class="block text-xs font-semibold uppercase text-slate-700 mb-1">Pilih Tanggal Piket (Senin/Kamis)</label>
                <select name="tanggal" id="tanggal_manual" required class="w-full px-3 py-2 border border-slate-300 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                    <?php foreach ($daftar_tanggal as $dt): ?>
                        <option value="<?= $dt['tanggal'] ?>"><?= $dt['hari'] ?>, <?= format_tanggal_indo($dt['tanggal']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div>
                <label for="member_id_manual" class="block text-xs font-semibold uppercase text-slate-700 mb-1">Pilih Pengurus</label>
                <select name="member_id" id="member_id_manual" required class="w-full px-3 py-2 border border-slate-300 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                    <option value="">-- Pilih Pengurus --</option>
                    <?php foreach ($semua_pengurus_pilihan as $png): ?>
                        <option value="<?= $png['id'] ?>"><?= sanitize($png['nama']) ?> (<?= sanitize($png['nama_divisi']) ?>)</option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="flex items-center justify-end space-x-2 pt-2">
                <button type="button" onclick="document.getElementById('modal_manual').classList.add('hidden')" class="px-4 py-2 bg-slate-100 text-slate-600 text-xs font-semibold rounded-xl">Batal</button>
                <button type="submit" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-semibold rounded-xl shadow-sm">Simpan Jadwal</button>
            </div>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/footer.php'; ?>
