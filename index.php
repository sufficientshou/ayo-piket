<?php
require_once __DIR__ . '/config/database.php';
$judul_halaman = "Jadwal Piket Pengurus";
require_once __DIR__ . '/includes/header.php';

$bulan_terpilih = isset($_GET['bulan']) ? (int)$_GET['bulan'] : (int)date('n');
$tahun_terpilih = isset($_GET['tahun']) ? (int)$_GET['tahun'] : (int)date('Y');

$daftar_tanggal = ambil_tanggal_senin_kamis($tahun_terpilih, $bulan_terpilih);

$stmt_jadwal = $koneksi->prepare("
    SELECT s.id, s.tanggal, s.hari, m.nama, m.nim, d.nama_divisi
    FROM schedules s
    JOIN members m ON s.member_id = m.id
    JOIN divisions d ON m.division_id = d.id
    WHERE s.bulan = ? AND s.tahun = ? AND s.status = 'published'
    ORDER BY s.tanggal ASC, m.nama ASC
");
$stmt_jadwal->execute([$bulan_terpilih, $tahun_terpilih]);
$jadwal_raw = $stmt_jadwal->fetchAll();

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

$hari_ini = date('Y-m-d');
$piket_hari_ini = $jadwal_per_tanggal[$hari_ini]['anggota'] ?? [];

$daftar_nama_bulan = [
    1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
    5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
    9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
];
?>

<div class="space-y-8">
    <div class="bg-gradient-to-r from-indigo-600 to-indigo-800 rounded-3xl p-6 sm:p-8 text-white shadow-lg relative overflow-hidden">
        <div class="max-w-2xl relative z-10 space-y-3">
            <span class="inline-block px-3 py-1 bg-white/20 backdrop-blur-md rounded-full text-xs font-semibold tracking-wide">
                Sistem Piket Himpunan Mahasiswa
            </span>
            <h1 class="text-3xl sm:text-4xl font-extrabold tracking-tight">
                Jadwal & Presensi Piket
            </h1>
            <p class="text-indigo-100 text-sm sm:text-base leading-relaxed">
                Jadwal piket rutin dilaksanakan setiap hari <strong>Senin & Kamis</strong>. Jangan lupa untuk mengisi laporan dan upload dokumentasi setelah selesai bertugas.
            </p>
            <div class="pt-2 flex flex-wrap gap-3">
                <a href="presensi.php" class="px-5 py-2.5 bg-white text-indigo-700 hover:bg-indigo-50 font-bold text-sm rounded-xl shadow transition">
                    Lapor Piket Sekarang &rarr;
                </a>
                <a href="export_jadwal.php?bulan=<?= $bulan_terpilih ?>&tahun=<?= $tahun_terpilih ?>" target="_blank"
                   class="px-5 py-2.5 bg-indigo-700/60 hover:bg-indigo-700 text-white font-semibold text-sm rounded-xl border border-indigo-400/30 transition flex items-center space-x-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                    <span>Download PDF</span>
                </a>
            </div>
        </div>
    </div>

    <?php if (!empty($piket_hari_ini)): ?>
        <div class="bg-amber-50 border border-amber-200 rounded-2xl p-5 shadow-sm">
            <div class="flex items-center space-x-2 text-amber-800 font-bold text-sm mb-3">
                <span class="w-2.5 h-2.5 rounded-full bg-amber-500 animate-pulse"></span>
                <span>Petugas Piket Hari Ini (<?= format_tanggal_indo($hari_ini) ?>)</span>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-3">
                <?php foreach ($piket_hari_ini as $pj): ?>
                    <div class="bg-white p-3 rounded-xl border border-amber-200/60 shadow-xs flex items-center space-x-3">
                        <div class="w-8 h-8 rounded-full bg-amber-100 text-amber-700 flex items-center justify-center font-bold text-xs shrink-0">
                            <?= strtoupper(substr($pj['nama'], 0, 1)) ?>
                        </div>
                        <div class="overflow-hidden">
                            <div class="font-bold text-xs text-slate-800 truncate"><?= sanitize($pj['nama']) ?></div>
                            <div class="text-[10px] text-slate-500 truncate"><?= sanitize($pj['nama_divisi']) ?></div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>

    <div class="space-y-4">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h2 class="text-xl font-bold text-slate-800">Daftar Jadwal Bulan <?= $daftar_nama_bulan[$bulan_terpilih] ?> <?= $tahun_terpilih ?></h2>
                <p class="text-xs text-slate-500">Menampilkan seluruh jadwal piket hari Senin & Kamis</p>
            </div>

            <form action="index.php" method="GET" class="flex items-center space-x-2 bg-white p-1.5 rounded-xl border border-slate-200 shadow-xs">
                <select name="bulan" class="text-xs font-semibold text-slate-700 bg-transparent px-2 py-1 focus:outline-none">
                    <?php foreach ($daftar_nama_bulan as $b_num => $b_nama): ?>
                        <option value="<?= $b_num ?>" <?= $bulan_terpilih == $b_num ? 'selected' : '' ?>><?= $b_nama ?></option>
                    <?php endforeach; ?>
                </select>
                <select name="tahun" class="text-xs font-semibold text-slate-700 bg-transparent px-2 py-1 focus:outline-none">
                    <?php for ($th = date('Y') - 1; $th <= date('Y') + 2; $th++): ?>
                        <option value="<?= $th ?>" <?= $tahun_terpilih == $th ? 'selected' : '' ?>><?= $th ?></option>
                    <?php endfor; ?>
                </select>
                <button type="submit" class="px-3 py-1 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg text-xs font-semibold transition">
                    Pilih
                </button>
            </form>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
            <?php foreach ($jadwal_per_tanggal as $tgl => $info): ?>
                <?php $is_today = ($tgl === $hari_ini); ?>
                <div class="bg-white rounded-2xl border <?= $is_today ? 'border-indigo-400 ring-2 ring-indigo-200' : 'border-slate-200' ?> shadow-sm flex flex-col overflow-hidden">
                    <div class="p-4 <?= $is_today ? 'bg-indigo-50' : 'bg-slate-50' ?> border-b border-slate-200 flex justify-between items-center">
                        <div>
                            <span class="inline-block px-2 py-0.5 rounded text-xs font-bold uppercase tracking-wider <?= $info['hari'] === 'Senin' ? 'bg-sky-100 text-sky-800' : 'bg-purple-100 text-purple-800' ?>">
                                <?= $info['hari'] ?>
                            </span>
                            <div class="text-sm font-bold text-slate-800 mt-1"><?= format_tanggal_indo($tgl) ?></div>
                        </div>
                        <span class="text-xs font-semibold px-2 py-0.5 rounded-full <?= $is_today ? 'bg-indigo-600 text-white' : 'bg-slate-200 text-slate-700' ?>">
                            <?= count($info['anggota']) ?> Orang
                        </span>
                    </div>

                    <div class="p-4 flex-grow space-y-2">
                        <?php if (empty($info['anggota'])): ?>
                            <div class="py-6 text-center text-xs text-slate-400 italic">Jadwal belum dipublikasi</div>
                        <?php else: ?>
                            <?php foreach ($info['anggota'] as $piket): ?>
                                <div class="p-2.5 rounded-xl bg-slate-50 border border-slate-100">
                                    <div class="font-semibold text-xs text-slate-800 truncate"><?= sanitize($piket['nama']) ?></div>
                                    <div class="text-[10px] text-slate-500 truncate"><?= sanitize($piket['nama_divisi']) ?></div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
