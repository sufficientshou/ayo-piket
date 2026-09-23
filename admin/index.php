<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../includes/auth_check.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

$filter_tanggal = sanitize($_GET['filter_tanggal'] ?? '');
$filter_divisi = (int)($_GET['filter_divisi'] ?? 0);
$filter_status = sanitize($_GET['filter_status'] ?? '');

if (isset($_GET['verifikasi_id'])) {
    $id_v = (int)$_GET['verifikasi_id'];
    $stmt_v = $koneksi->prepare("UPDATE attendances SET status_verifikasi = 'valid' WHERE id = ?");
    $stmt_v->execute([$id_v]);
    set_flash_message('sukses', 'Laporan presensi berhasil di-ACC / diverifikasi!');
    header("Location: index.php");
    exit();
}

if (isset($_GET['batal_verifikasi_id'])) {
    $id_bv = (int)$_GET['batal_verifikasi_id'];
    $stmt_bv = $koneksi->prepare("UPDATE attendances SET status_verifikasi = 'pending' WHERE id = ?");
    $stmt_bv->execute([$id_bv]);
    set_flash_message('sukses', 'Status verifikasi presensi dibatalkan menjadi menunggu.');
    header("Location: index.php");
    exit();
}

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

if (!empty($filter_status)) {
    $kondisi[] = "a.status_verifikasi = ?";
    $params[] = $filter_status;
}

if (!empty($kondisi)) {
    $query_str .= " WHERE " . implode(" AND ", $kondisi);
}

$query_str .= " ORDER BY a.tanggal DESC, a.created_at DESC";
$stmt_att = $koneksi->prepare($query_str);
$stmt_att->execute($params);
$daftar_presensi = $stmt_att->fetchAll();

if (isset($_GET['export']) && $_GET['export'] === 'excel') {
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename=rekap_presensi_' . date('Ymd_His') . '.csv');
    $output = fopen('php://output', 'w');
    fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
    fputcsv($output, ['ID', 'Tanggal', 'Hari', 'Jam Mulai', 'Jam Selesai', 'NIM', 'Nama Pengurus', 'Divisi', 'Status Verifikasi', 'Catatan', 'Waktu Kirim']);
    foreach ($daftar_presensi as $p) {
        fputcsv($output, [
            $p['id'],
            $p['tanggal'],
            date('l', strtotime($p['tanggal'])),
            $p['jam_mulai'],
            $p['jam_selesai'],
            $p['nim'],
            $p['nama'],
            $p['nama_divisi'],
            $p['status_verifikasi'],
            $p['catatan'],
            $p['created_at']
        ]);
    }
    fclose($output);
    exit();
}

$stmt_divs = $koneksi->query("SELECT * FROM divisions ORDER BY nama_divisi ASC");
$daftar_divisi = $stmt_divs->fetchAll();

$label_divisi_terpilih = 'SEMUA DIVISI';
foreach ($daftar_divisi as $d) {
    if ($filter_divisi == $d['id']) {
        $label_divisi_terpilih = strtoupper($d['nama_divisi']);
        break;
    }
}

$label_status_terpilih = 'SEMUA STATUS';
if ($filter_status === 'valid') {
    $label_status_terpilih = 'TERVERIFIKASI';
} elseif ($filter_status === 'pending') {
    $label_status_terpilih = 'MENUNGGU REVIEW';
}

$judul_halaman = "Rekap Presensi & Dashboard";
require_once __DIR__ . '/header.php';
?>

<div class="space-y-6 sm:space-y-8">

    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl sm:text-3xl lg:text-4xl font-black uppercase tracking-tight text-black">
                DASHBOARD &amp; REKAP PRESENSI
            </h1>
        </div>
    </div>

    <div class="bg-white border-2 border-black neo-shadow-lg p-5 sm:p-7 flex flex-col lg:flex-row lg:items-center justify-between gap-5 sm:gap-6">
        <div>
            <h2 class="text-lg sm:text-2xl font-black uppercase tracking-tight text-black">Riwayat Laporan Piket</h2>
            <p class="font-mono text-xs sm:text-sm text-slate-600 mt-1.5">
                Klik pada foto dokumentasi untuk memperbesar tampilan &amp; cek detail kegiatan.
            </p>
        </div>

        <form action="index.php" method="GET" class="flex flex-wrap items-center gap-2.5 sm:gap-3">
            <input type="date" name="filter_tanggal" value="<?= htmlspecialchars($filter_tanggal) ?>"
                   class="px-4 py-2.5 sm:py-3 bg-white border-2 border-black font-mono text-xs sm:text-sm font-bold text-black neo-shadow-sm focus:outline-none">

            <div class="relative" id="wrapper_divisi">
                <input type="hidden" name="filter_divisi" id="input_divisi" value="<?= $filter_divisi ?>">
                <button type="button" onclick="toggleDropdownAdmin('divisi')" id="trigger_divisi"
                        class="bg-white border-2 border-black px-4 py-2.5 sm:py-3 pr-9 font-mono font-bold text-xs sm:text-sm uppercase tracking-wider text-black neo-shadow-sm flex items-center justify-between gap-2 cursor-pointer focus:outline-none text-left">
                    <span id="label_divisi"><?= $label_divisi_terpilih ?></span>
                    <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2.5 text-black">
                        <svg id="arrow_divisi" class="w-3.5 h-3.5 fill-current transition-transform duration-150" viewBox="0 0 20 20"><path d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"/></svg>
                    </div>
                </button>
                <div id="menu_divisi" class="neo-dropdown-menu absolute left-0 min-w-full top-full mt-1.5 bg-white border-2 border-black neo-shadow-lg max-h-60 overflow-y-auto z-50 hidden py-1">
                    <div class="neo-dropdown-item px-4 py-2 text-xs sm:text-sm font-mono font-bold uppercase tracking-wider cursor-pointer flex items-center justify-between border-b border-slate-100 last:border-b-0"
                         data-selected="<?= $filter_divisi === 0 ? 'true' : 'false' ?>"
                         onclick="pilihOpsiAdmin('divisi', 0, 'SEMUA DIVISI')">
                        <span>SEMUA DIVISI</span>
                    </div>
                    <?php foreach ($daftar_divisi as $d): ?>
                        <?php $is_d_sel = ($filter_divisi == $d['id']); ?>
                        <div class="neo-dropdown-item px-4 py-2 text-xs sm:text-sm font-mono font-bold uppercase tracking-wider cursor-pointer flex items-center justify-between border-b border-slate-100 last:border-b-0"
                             data-selected="<?= $is_d_sel ? 'true' : 'false' ?>"
                             onclick="pilihOpsiAdmin('divisi', <?= $d['id'] ?>, '<?= htmlspecialchars(strtoupper($d['nama_divisi']), ENT_QUOTES) ?>')">
                            <span><?= htmlspecialchars(strtoupper($d['nama_divisi'])) ?></span>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="relative" id="wrapper_status">
                <input type="hidden" name="filter_status" id="input_status" value="<?= htmlspecialchars($filter_status) ?>">
                <button type="button" onclick="toggleDropdownAdmin('status')" id="trigger_status"
                        class="bg-white border-2 border-black px-4 py-2.5 sm:py-3 pr-9 font-mono font-bold text-xs sm:text-sm uppercase tracking-wider text-black neo-shadow-sm flex items-center justify-between gap-2 cursor-pointer focus:outline-none text-left">
                    <span id="label_status"><?= $label_status_terpilih ?></span>
                    <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2.5 text-black">
                        <svg id="arrow_status" class="w-3.5 h-3.5 fill-current transition-transform duration-150" viewBox="0 0 20 20"><path d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"/></svg>
                    </div>
                </button>
                <div id="menu_status" class="neo-dropdown-menu absolute left-0 min-w-full top-full mt-1.5 bg-white border-2 border-black neo-shadow-lg max-h-60 overflow-y-auto z-50 hidden py-1">
                    <div class="neo-dropdown-item px-4 py-2 text-xs sm:text-sm font-mono font-bold uppercase tracking-wider cursor-pointer flex items-center justify-between border-b border-slate-100 last:border-b-0"
                         data-selected="<?= $filter_status === '' ? 'true' : 'false' ?>"
                         onclick="pilihOpsiAdmin('status', '', 'SEMUA STATUS')">
                        <span>SEMUA STATUS</span>
                    </div>
                    <div class="neo-dropdown-item px-4 py-2 text-xs sm:text-sm font-mono font-bold uppercase tracking-wider cursor-pointer flex items-center justify-between border-b border-slate-100 last:border-b-0"
                         data-selected="<?= $filter_status === 'valid' ? 'true' : 'false' ?>"
                         onclick="pilihOpsiAdmin('status', 'valid', 'TERVERIFIKASI')">
                        <span>TERVERIFIKASI</span>
                    </div>
                    <div class="neo-dropdown-item px-4 py-2 text-xs sm:text-sm font-mono font-bold uppercase tracking-wider cursor-pointer flex items-center justify-between border-b border-slate-100 last:border-b-0"
                         data-selected="<?= $filter_status === 'pending' ? 'true' : 'false' ?>"
                         onclick="pilihOpsiAdmin('status', 'pending', 'MENUNGGU REVIEW')">
                        <span>MENUNGGU REVIEW</span>
                    </div>
                </div>
            </div>

            <button type="submit" class="px-5 py-2.5 sm:py-3 bg-[#164E33] hover:bg-[#123e29] text-white border-2 border-black neo-shadow-sm neo-btn font-mono font-bold text-xs sm:text-sm uppercase tracking-wider flex items-center gap-2 transition cursor-pointer">
                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
                </svg>
                <span>FILTER</span>
            </button>
        </form>
    </div>

    <div class="bg-white border-2 border-black neo-shadow-lg overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead class="bg-[#F4EFE6] border-b-2 border-black">
                    <tr>
                        <th class="px-5 sm:px-6 py-4 font-mono text-xs sm:text-sm font-black text-black uppercase tracking-wider">TANGGAL &amp; HARI</th>
                        <th class="px-5 sm:px-6 py-4 font-mono text-xs sm:text-sm font-black text-black uppercase tracking-wider">NAMA PENGURUS</th>
                        <th class="px-5 sm:px-6 py-4 font-mono text-xs sm:text-sm font-black text-black uppercase tracking-wider">JAM PIKET</th>
                        <th class="px-5 sm:px-6 py-4 font-mono text-xs sm:text-sm font-black text-black uppercase tracking-wider text-center">DOKUMENTASI</th>
                        <th class="px-5 sm:px-6 py-4 font-mono text-xs sm:text-sm font-black text-black uppercase tracking-wider">STATUS</th>
                        <th class="px-5 sm:px-6 py-4 font-mono text-xs sm:text-sm font-black text-black uppercase tracking-wider">CATATAN</th>
                        <th class="px-5 sm:px-6 py-4 font-mono text-xs sm:text-sm font-black text-black uppercase tracking-wider text-right">AKSI</th>
                    </tr>
                </thead>
                <tbody class="divide-y-2 divide-black bg-white">
                    <?php if (empty($daftar_presensi)): ?>
                        <tr>
                            <td colspan="7" class="px-6 py-14 text-center font-mono text-sm text-zinc-500 bg-white">
                                Tidak ada data presensi yang sesuai dengan filter yang dipilih.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($daftar_presensi as $pres): ?>
                            <?php 
                                $foto_url = '../uploads/dokumentasi/' . htmlspecialchars($pres['foto_bukti']);
                            ?>
                            <tr class="hover:bg-zinc-50 transition">
                                <td class="px-5 sm:px-6 py-4 sm:py-5 align-middle">
                                    <div class="font-black text-black text-base sm:text-lg leading-snug">
                                        <?= date('l, d F', strtotime($pres['tanggal'])) ?><br>
                                        <?= date('Y', strtotime($pres['tanggal'])) ?>
                                    </div>
                                    <div class="font-mono text-xs sm:text-sm text-zinc-600 mt-1">
                                        <?= date('H:i', strtotime($pres['created_at'])) ?> WIB
                                    </div>
                                </td>
                                <td class="px-5 sm:px-6 py-4 sm:py-5 align-middle">
                                    <div class="font-black text-black text-base sm:text-lg">
                                        <?= htmlspecialchars($pres['nama']) ?>
                                    </div>
                                    <div class="mt-2">
                                        <span class="inline-block border-2 border-black bg-[#EEF2FF] text-[#312E81] font-mono text-xs sm:text-sm font-bold px-2.5 py-1 leading-tight neo-shadow-sm">
                                            <?= htmlspecialchars($pres['nama_divisi']) ?>
                                        </span>
                                    </div>
                                </td>
                                <td class="px-5 sm:px-6 py-4 sm:py-5 align-middle whitespace-nowrap">
                                    <div class="inline-block border-2 border-black bg-zinc-50 px-3 py-1.5 font-mono text-xs sm:text-sm font-bold text-black neo-shadow-sm">
                                        <?= substr($pres['jam_mulai'], 0, 5) ?> - <?= substr($pres['jam_selesai'], 0, 5) ?>
                                    </div>
                                </td>
                                <td class="px-5 sm:px-6 py-4 sm:py-5 align-middle text-center">
                                    <?php if (!empty($pres['foto_bukti'])): ?>
                                        <button type="button" onclick="bukaModalFoto('<?= $foto_url ?>', '<?= htmlspecialchars($pres['nama']) ?>')" class="group relative inline-block overflow-hidden w-16 h-16 sm:w-20 sm:h-20 border-2 border-black neo-shadow-sm neo-btn transition cursor-pointer bg-zinc-100">
                                            <img src="<?= $foto_url ?>" alt="Foto Bukti" class="w-full h-full object-cover group-hover:scale-110 transition duration-150">
                                        </button>
                                    <?php else: ?>
                                        <div class="w-16 h-16 sm:w-20 sm:h-20 border-2 border-black bg-zinc-100 flex items-center justify-center font-mono text-xs text-zinc-400 mx-auto">
                                            No Foto
                                        </div>
                                    <?php endif; ?>
                                </td>
                                <td class="px-5 sm:px-6 py-4 sm:py-5 align-middle">
                                    <?php if ($pres['status_verifikasi'] === 'valid'): ?>
                                        <div class="border-2 border-black bg-[#dcfce7] text-[#065f46] neo-shadow-sm font-mono font-bold text-xs sm:text-sm px-3.5 py-2 inline-flex">
                                            <span>Terverifikasi</span>
                                        </div>
                                    <?php else: ?>
                                        <div class="border-2 border-black bg-[#fef9c3] text-[#78350f] neo-shadow-sm font-mono font-bold text-xs sm:text-sm px-3.5 py-2 inline-flex">
                                            <span>Menunggu Review</span>
                                        </div>
                                    <?php endif; ?>
                                </td>
                                <td class="px-5 sm:px-6 py-4 sm:py-5 align-middle">
                                    <div class="border-2 border-black bg-[#fcfcfc] px-3.5 py-2.5 font-mono text-xs sm:text-sm text-zinc-800 neo-shadow-sm w-44 break-words">
                                        <?= htmlspecialchars($pres['catatan'] ?: '-') ?>
                                    </div>
                                </td>
                                <td class="px-5 sm:px-6 py-4 sm:py-5 align-middle text-right">
                                    <div class="flex items-center justify-end gap-2.5 sm:gap-3">
                                        <?php if ($pres['status_verifikasi'] === 'valid'): ?>
                                            <a href="index.php?batal_verifikasi_id=<?= $pres['id'] ?>" class="w-24 sm:w-28 py-2 sm:py-2.5 border-2 border-black bg-white hover:bg-zinc-100 text-black font-mono font-bold text-xs sm:text-sm leading-tight text-center uppercase neo-shadow-sm neo-btn transition inline-block">
                                                Batal ACC
                                            </a>
                                        <?php else: ?>
                                            <a href="index.php?verifikasi_id=<?= $pres['id'] ?>" class="w-24 sm:w-28 py-2 sm:py-2.5 border-2 border-black bg-[#B8E926] text-black font-mono font-bold text-xs sm:text-sm uppercase neo-shadow-sm neo-btn transition inline-flex items-center justify-center">
                                                ACC
                                            </a>
                                        <?php endif; ?>
                                        <a href="index.php?hapus_presensi_id=<?= $pres['id'] ?>"
                                           onclick="return confirm('Hapus bukti presensi milik <?= htmlspecialchars($pres['nama']) ?>?')"
                                           class="w-24 sm:w-28 py-2 sm:py-2.5 border-2 border-[#E84125] bg-white hover:bg-red-50 text-[#E84125] font-mono font-bold text-xs sm:text-sm uppercase neo-shadow-sm neo-btn transition inline-flex items-center justify-center">
                                            Hapus
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <div class="border-t-2 border-black p-5 bg-white flex items-center justify-end gap-2.5 font-mono text-xs sm:text-sm">
            <button type="button" class="px-4 py-2 sm:py-2.5 border-2 border-zinc-300 text-zinc-400 font-bold bg-[#f8fafc] cursor-not-allowed">
                &larr; Prev
            </button>
            <button type="button" class="px-4 py-2 sm:py-2.5 border-2 border-black font-bold bg-[#B8E926] text-black neo-shadow-sm neo-btn">
                1
            </button>
            <button type="button" class="px-4 py-2 sm:py-2.5 border-2 border-black font-bold bg-white text-black neo-shadow-sm neo-btn hover:bg-zinc-50">
                2
            </button>
            <button type="button" class="px-4 py-2 sm:py-2.5 border-2 border-black font-bold bg-white text-black neo-shadow-sm neo-btn hover:bg-zinc-50">
                Next &rarr;
            </button>
        </div>
    </div>

</div>

<div id="modal_foto" class="fixed inset-0 bg-black/60 z-50 flex items-center justify-center p-4 hidden" onclick="tutupModalFoto()"><div class="bg-white border-2 border-black neo-shadow-lg max-w-2xl w-full p-6 relative max-h-[90vh] overflow-hidden" onclick="event.stopPropagation()"><div class="flex justify-between items-center pb-3 mb-4 border-b-2 border-black"><div class="flex items-center gap-2"><span class="bg-[#B8E926] border border-black px-2 py-0.5 font-mono text-[10px] font-bold uppercase text-black">PREVIEW</span><h4 id="judul_modal_foto" class="font-black text-black text-sm uppercase">Dokumentasi Piket</h4></div><button onclick="tutupModalFoto()" class="w-8 h-8 border-2 border-black bg-white flex items-center justify-center font-bold text-base hover:bg-black hover:text-white transition cursor-pointer">&times;</button></div><div class="max-h-[70vh] overflow-auto flex items-center justify-center bg-[#FAF8F5] border-2 border-black p-2"><img id="gambar_modal_foto" src="" alt="Bukti Full" class="max-h-[65vh] object-contain"></div></div></div>

<script>
function toggleDropdownAdmin(tipe) {
    var menu = document.getElementById('menu_' + tipe);
    var arrow = document.getElementById('arrow_' + tipe);
    var otherTipe = tipe === 'divisi' ? 'status' : 'divisi';
    var otherMenu = document.getElementById('menu_' + otherTipe);
    var otherArrow = document.getElementById('arrow_' + otherTipe);
    if (otherMenu) otherMenu.classList.add('hidden');
    if (otherArrow) otherArrow.classList.remove('rotate-180');

    if (menu.classList.contains('hidden')) {
        menu.classList.remove('hidden');
        arrow.classList.add('rotate-180');
    } else {
        menu.classList.add('hidden');
        arrow.classList.remove('rotate-180');
    }
}

function pilihOpsiAdmin(tipe, val, label) {
    document.getElementById('input_' + tipe).value = val;
    document.getElementById('label_' + tipe).textContent = label;
    var menu = document.getElementById('menu_' + tipe);
    var arrow = document.getElementById('arrow_' + tipe);
    if (menu) menu.classList.add('hidden');
    if (arrow) arrow.classList.remove('rotate-180');

    document.querySelectorAll('#menu_' + tipe + ' .neo-dropdown-item').forEach(function(item) {
        item.setAttribute('data-selected', 'false');
    });
    if (event && event.currentTarget) {
        event.currentTarget.setAttribute('data-selected', 'true');
    }
}

document.addEventListener('click', function(e) {
    var wDiv = document.getElementById('wrapper_divisi');
    var wStat = document.getElementById('wrapper_status');
    if (wDiv && !wDiv.contains(e.target)) {
        var mD = document.getElementById('menu_divisi');
        var aD = document.getElementById('arrow_divisi');
        if (mD) mD.classList.add('hidden');
        if (aD) aD.classList.remove('rotate-180');
    }
    if (wStat && !wStat.contains(e.target)) {
        var mS = document.getElementById('menu_status');
        var aS = document.getElementById('arrow_status');
        if (mS) mS.classList.add('hidden');
        if (aS) aS.classList.remove('rotate-180');
    }
});

function bukaModalFoto(url, nama) {
    document.getElementById('gambar_modal_foto').src = url;
    document.getElementById('judul_modal_foto').innerText = 'Dokumentasi Piket: ' + nama;
    document.getElementById('modal_foto').classList.remove('hidden');
}
function tutupModalFoto() {
    document.getElementById('modal_foto').classList.add('hidden');
    document.getElementById('gambar_modal_foto').src = '';
}
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') tutupModalFoto();
});
</script>

<?php require_once __DIR__ . '/footer.php'; ?>
