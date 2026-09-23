<?php
require_once __DIR__ . '/../config/database.php';
$judul_halaman = "Rekap Presensi & Dashboard";
require_once __DIR__ . '/header.php';

$total_pengurus = $koneksi->query("SELECT COUNT(*) FROM members WHERE is_active = 1")->fetchColumn();
$total_jadwal_bulan_ini = $koneksi->query("SELECT COUNT(*) FROM schedules WHERE bulan = MONTH(CURRENT_DATE()) AND tahun = YEAR(CURRENT_DATE())")->fetchColumn();
$total_presensi = $koneksi->query("SELECT COUNT(*) FROM attendances")->fetchColumn();

$filter_tanggal = sanitize($_GET['filter_tanggal'] ?? '');
$filter_divisi = (int)($_GET['filter_divisi'] ?? 0);

if (isset($_GET['hapus_presensi_id'])) {
    $id_hps = (int)$_GET['hapus_presensi_id'];
    $stmt_c = $koneksi->prepare("SELECT foto_bukti FROM attendances WHERE id = ?");
    $stmt_c->execute([$id_hps]);
    $foto = $stmt_c->fetchColumn();

    if ($foto && file_exists(__DIR__ . '/../uploads/dokumentasi/' . $foto)) {
        unlink(__DIR__ . '/../uploads/dokumentasi/' . $foto);
    }

    $stmt_d = $koneksi->prepare("DELETE FROM attendances WHERE id = ?");
    $stmt_d->execute([$id_hps]);
    set_flash_message('sukses', 'Laporan presensi berhasil dihapus.');
    header("Location: index.php");
    exit();
}

$query_str = "
    SELECT a.*, m.nama, m.nim, d.nama_divisi
    FROM attendances a
    JOIN members m ON a.member_id = m.id
    JOIN divisions d ON m.division_id = d.id
";
$kondisi = [];
$params = [];

if (!empty($filter_tanggal)) {
    $kondisi[] = "a.tanggal = ?";
    $params[] = $filter_tanggal;
}

if ($filter_divisi > 0) {
    $kondisi[] = "m.division_id = ?";
    $params[] = $filter_divisi;
}

if (!empty($kondisi)) {
    $query_str .= " WHERE " . implode(" AND ", $kondisi);
}

$query_str .= " ORDER BY a.tanggal DESC, a.created_at DESC";
$stmt_att = $koneksi->prepare($query_str);
$stmt_att->execute($params);
$daftar_presensi = $stmt_att->fetchAll();

$stmt_divs = $koneksi->query("SELECT * FROM divisions ORDER BY nama_divisi ASC");
$daftar_divisi = $stmt_divs->fetchAll();
?>

<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-800">Dashboard & Rekap Presensi</h1>
            <p class="text-sm text-slate-500">Pantau seluruh laporan dan dokumentasi piket pengurus yang masuk</p>
        </div>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm flex items-center space-x-4">
            <div class="p-3 bg-indigo-50 text-indigo-600 rounded-xl">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
            </div>
            <div>
                <div class="text-2xl font-bold text-slate-800"><?= $total_pengurus ?></div>
                <div class="text-xs text-slate-500 font-medium">Pengurus Aktif</div>
            </div>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm flex items-center space-x-4">
            <div class="p-3 bg-amber-50 text-amber-600 rounded-xl">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
            </div>
            <div>
                <div class="text-2xl font-bold text-slate-800"><?= $total_jadwal_bulan_ini ?></div>
                <div class="text-xs text-slate-500 font-medium">Slot Terjadwal Bulan Ini</div>
            </div>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm flex items-center space-x-4">
            <div class="p-3 bg-emerald-50 text-emerald-600 rounded-xl">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            </div>
            <div>
                <div class="text-2xl font-bold text-slate-800"><?= $total_presensi ?></div>
                <div class="text-xs text-slate-500 font-medium">Total Laporan Masuk</div>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
        <div class="p-5 border-b border-slate-100 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div>
                <h2 class="font-bold text-slate-800">Riwayat Laporan Piket (<?= count($daftar_presensi) ?>)</h2>
                <p class="text-xs text-slate-500">Klik pada foto dokumentasi untuk memperbesar tampilan</p>
            </div>

            <form action="index.php" method="GET" class="flex flex-wrap items-center gap-2">
                <input type="date" name="filter_tanggal" value="<?= $filter_tanggal ?>"
                       class="text-xs px-3 py-1.5 rounded-lg border border-slate-300 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                <select name="filter_divisi" class="text-xs px-3 py-1.5 rounded-lg border border-slate-300 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    <option value="0">Semua Divisi</option>
                    <?php foreach ($daftar_divisi as $d): ?>
                        <option value="<?= $d['id'] ?>" <?= $filter_divisi == $d['id'] ? 'selected' : '' ?>>
                            <?= sanitize($d['nama_divisi']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <button type="submit" class="px-3 py-1.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg text-xs font-semibold transition">
                    Filter
                </button>
                <?php if (!empty($filter_tanggal) || $filter_divisi > 0): ?>
                    <a href="index.php" class="text-xs text-rose-600 hover:underline px-1">Reset</a>
                <?php endif; ?>
            </form>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-slate-600">
                <thead class="bg-slate-50 text-xs uppercase tracking-wider text-slate-500 border-b border-slate-200">
                    <tr>
                        <th class="px-6 py-3">Tanggal & Hari</th>
                        <th class="px-6 py-3">Nama Pengurus</th>
                        <th class="px-6 py-3">Jam Piket</th>
                        <th class="px-6 py-3">Dokumentasi</th>
                        <th class="px-6 py-3">Catatan</th>
                        <th class="px-6 py-3 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <?php if (empty($daftar_presensi)): ?>
                        <tr>
                            <td colspan="6" class="px-6 py-10 text-center text-slate-400">Belum ada laporan presensi piket yang masuk.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($daftar_presensi as $pres): ?>
                            <?php 
                                $hari_indeks = date('N', strtotime($pres['tanggal']));
                                $hari_nama = ($hari_indeks == 1) ? 'Senin' : (($hari_indeks == 4) ? 'Kamis' : date('l', strtotime($pres['tanggal'])));
                            ?>
                            <tr class="hover:bg-slate-50 transition">
                                <td class="px-6 py-4">
                                    <div class="font-bold text-slate-800 text-xs"><?= $hari_nama ?>, <?= format_tanggal_indo($pres['tanggal']) ?></div>
                                    <div class="text-[10px] text-slate-400"><?= date('H:i', strtotime($pres['created_at'])) ?> WIB</div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="font-semibold text-slate-800"><?= sanitize($pres['nama']) ?></div>
                                    <span class="inline-block mt-0.5 text-[10px] font-semibold text-indigo-700 bg-indigo-50 px-2 py-0.5 rounded-full">
                                        <?= sanitize($pres['nama_divisi']) ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-xs font-mono text-slate-700">
                                    <?= substr($pres['jam_mulai'], 0, 5) ?> - <?= substr($pres['jam_selesai'], 0, 5) ?>
                                </td>
                                <td class="px-6 py-4">
                                    <?php $foto_url = '../uploads/dokumentasi/' . sanitize($pres['foto_bukti']); ?>
                                    <button type="button" onclick="bukaModalFoto('<?= $foto_url ?>', '<?= sanitize($pres['nama']) ?>')" class="group relative block overflow-hidden rounded-xl w-14 h-14 border border-slate-200 shadow-xs">
                                        <img src="<?= $foto_url ?>" alt="Foto Bukti" class="w-full h-full object-cover group-hover:scale-110 transition duration-200">
                                    </button>
                                </td>
                                <td class="px-6 py-4 text-xs text-slate-500 max-w-xs">
                                    <?= $pres['catatan'] ? sanitize($pres['catatan']) : '<span class="italic text-slate-400">Tidak ada catatan</span>' ?>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <a href="index.php?hapus_presensi_id=<?= $pres['id'] ?>"
                                       onclick="return confirm('Hapus bukti presensi milik <?= sanitize($pres['nama']) ?>?')"
                                       class="text-rose-600 hover:text-rose-900 font-medium text-xs">Hapus</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div id="modal_foto" class="fixed inset-0 bg-slate-900/80 z-50 flex items-center justify-center p-4 hidden" onclick="tutupModalFoto()">
    <div class="bg-white rounded-2xl max-w-2xl w-full p-4 shadow-2xl space-y-3" onclick="event.stopPropagation()">
        <div class="flex justify-between items-center pb-2 border-b border-slate-100">
            <h4 id="judul_modal_foto" class="font-bold text-slate-800 text-sm">Dokumentasi Piket</h4>
            <button onclick="tutupModalFoto()" class="text-slate-400 hover:text-slate-600 text-lg font-bold leading-none">&times;</button>
        </div>
        <div class="max-h-[75vh] overflow-auto flex items-center justify-center bg-slate-50 rounded-xl p-2">
            <img id="gambar_modal_foto" src="" alt="Bukti Full" class="max-h-[70vh] rounded-lg object-contain">
        </div>
    </div>
</div>

<script>
function bukaModalFoto(url, nama) {
    document.getElementById('gambar_modal_foto').src = url;
    document.getElementById('judul_modal_foto').innerText = 'Dokumentasi Piket: ' + nama;
    document.getElementById('modal_foto').classList.remove('hidden');
}
function tutupModalFoto() {
    document.getElementById('modal_foto').classList.add('hidden');
    document.getElementById('gambar_modal_foto').src = '';
}
</script>

<?php require_once __DIR__ . '/footer.php'; ?>
