<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../includes/auth_check.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

$aksi = $_GET['aksi'] ?? '';
$id_edit = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$data_edit = null;

$stmt_div = $koneksi->query("SELECT * FROM divisions ORDER BY nama_divisi ASC");
$daftar_divisi = $stmt_div->fetchAll();

// Handle Form Submit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nim = sanitize($_POST['nim'] ?? '');
    $nama = sanitize($_POST['nama'] ?? '');
    $division_id = (int)($_POST['division_id'] ?? 0);
    $no_wa = sanitize($_POST['no_wa'] ?? '');
    $is_active = isset($_POST['is_active']) ? 1 : 0;

    if (empty($nama) || $division_id <= 0) {
        set_flash_message('gagal', 'Nama pengurus dan divisi wajib diisi!');
    } else {
        if (!empty($_POST['id_member'])) {
            $id = (int)$_POST['id_member'];
            $stmt = $koneksi->prepare("UPDATE members SET nim = ?, nama = ?, division_id = ?, no_wa = ?, is_active = ? WHERE id = ?");
            $stmt->execute([$nim, $nama, $division_id, $no_wa, $is_active, $id]);
            set_flash_message('sukses', 'Data pengurus berhasil diperbarui!');
        } else {
            $stmt = $koneksi->prepare("INSERT INTO members (nim, nama, division_id, no_wa, is_active) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$nim, $nama, $division_id, $no_wa, $is_active]);
            set_flash_message('sukses', 'Pengurus baru berhasil ditambahkan!');
        }
        header("Location: members.php");
        exit();
    }
}

// Handle Hapus
if ($aksi === 'hapus' && $id_edit > 0) {
    $stmt = $koneksi->prepare("DELETE FROM members WHERE id = ?");
    $stmt->execute([$id_edit]);
    set_flash_message('sukses', 'Data pengurus berhasil dihapus!');
    header("Location: members.php");
    exit();
}

// Handle Edit
if ($aksi === 'edit' && $id_edit > 0) {
    $stmt = $koneksi->prepare("SELECT * FROM members WHERE id = ? LIMIT 1");
    $stmt->execute([$id_edit]);
    $data_edit = $stmt->fetch();
}

// Search and Filter
$search = sanitize($_GET['search'] ?? '');
$filter_divisi = (int)($_GET['filter_divisi'] ?? 0);

$query_str = "
    SELECT m.*, d.nama_divisi 
    FROM members m
    JOIN divisions d ON m.division_id = d.id
";
$kondisi = [];
$params = [];

if (!empty($search)) {
    $kondisi[] = "(m.nama LIKE ? OR m.nim LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

if ($filter_divisi > 0) {
    $kondisi[] = "m.division_id = ?";
    $params[] = $filter_divisi;
}

if (!empty($kondisi)) {
    $query_str .= " WHERE " . implode(" AND ", $kondisi);
}

$query_str .= " ORDER BY m.nama ASC";
$stmt_members = $koneksi->prepare($query_str);
$stmt_members->execute($params);
$daftar_pengurus = $stmt_members->fetchAll();

// Statistics
$stmt_all_members = $koneksi->query("SELECT id FROM members");
$total_semua = $stmt_all_members->rowCount();

// Label for Dropdowns
$label_filter_divisi = 'Semua Divisi';
if ($filter_divisi > 0) {
    foreach ($daftar_divisi as $d) {
        if ($d['id'] == $filter_divisi) {
            $label_filter_divisi = $d['nama_divisi'];
            break;
        }
    }
}

$label_form_divisi = '';
if ($data_edit && !empty($data_edit['division_id'])) {
    foreach ($daftar_divisi as $d) {
        if ($d['id'] == $data_edit['division_id']) {
            $label_form_divisi = $d['nama_divisi'];
            break;
        }
    }
}

// Pagination
$halaman = max(1, (int)($_GET['page'] ?? 1));
$per_page = 10;
$total_hasil = count($daftar_pengurus);
$total_halaman = max(1, (int)ceil($total_hasil / $per_page));
$offset = ($halaman - 1) * $per_page;
$daftar_pengurus_tampil = array_slice($daftar_pengurus, $offset, $per_page);
$offset_start = $total_hasil > 0 ? $offset + 1 : 0;
$offset_end = min($offset + $per_page, $total_hasil);

$judul_halaman = "Kelola Data Pengurus";
require_once __DIR__ . '/header.php';
?>

<div class="space-y-6 sm:space-y-7">

    <!-- Top Header & Badges -->
    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
        <div>
            <h1 class="text-2xl sm:text-3xl lg:text-4xl font-black uppercase tracking-tight text-black">
                Data Pengurus
            </h1>
        </div>

        <div class="flex flex-wrap items-center gap-3 shrink-0">
            <div class="bg-white border-2 border-black font-mono font-bold text-xs uppercase tracking-wider px-3.5 py-2 shadow-[3px_3px_0px_#000]">
                TOTAL: <?= $total_semua ?> PENGURUS
            </div>
        </div>
    </div>

    <!-- Main Content Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">

        <!-- Left Column: Form Input -->
        <div class="lg:col-span-4 space-y-6">

            <!-- Card Form Input -->
            <div class="bg-white border-2 border-black shadow-[4px_4px_0px_#000] p-5 sm:p-6">
                <div class="flex items-center justify-between gap-2 mb-5">
                    <div class="flex items-center gap-2">
                        <svg class="w-5 h-5 text-black shrink-0" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/>
                        </svg>
                        <h2 class="text-base sm:text-lg font-black uppercase tracking-tight text-black">
                            <?= $data_edit ? 'Edit Data Pengurus' : 'Tambah Pengurus Baru' ?>
                        </h2>
                    </div>
                    <span class="bg-[#B8E926] border border-black px-2 py-0.5 font-mono text-[10px] font-bold uppercase tracking-wider text-black">
                        FORM INPUT
                    </span>
                </div>

                <form id="form_member" action="members.php" method="POST" class="space-y-4">
                    <?php if ($data_edit): ?>
                        <input type="hidden" name="id_member" value="<?= $data_edit['id'] ?>">
                    <?php endif; ?>

                    <div>
                        <label for="nim" class="block font-mono font-bold text-xs uppercase tracking-wider text-black mb-1.5">
                            NIM (OPSIONAL)
                        </label>
                        <input type="text" id="nim" name="nim"
                               value="<?= $data_edit ? sanitize($data_edit['nim'] ?? '') : '' ?>"
                               placeholder="Contoh: 2110511001"
                               class="w-full px-3.5 py-2.5 bg-white border-2 border-black font-mono text-xs text-black placeholder:text-zinc-400 focus:outline-none focus:bg-zinc-50 transition">
                    </div>

                    <div>
                        <label for="nama" class="block font-mono font-bold text-xs uppercase tracking-wider text-black mb-1.5">
                            NAMA LENGKAP <span class="text-[#E84125]">*</span>
                        </label>
                        <input type="text" id="nama" name="nama" required
                               value="<?= $data_edit ? sanitize($data_edit['nama']) : '' ?>"
                               placeholder="Masukkan nama pengurus"
                               class="w-full px-3.5 py-2.5 bg-white border-2 border-black text-xs font-semibold text-black placeholder:text-zinc-400 focus:outline-none focus:bg-zinc-50 transition">
                    </div>

                    <div>
                        <label class="block font-mono font-bold text-xs uppercase tracking-wider text-black mb-1.5">
                            DIVISI <span class="text-[#E84125]">*</span>
                        </label>
                        <div class="relative" id="wrapper_form_divisi">
                            <input type="hidden" name="division_id" id="input_form_divisi" value="<?= $data_edit ? (int)$data_edit['division_id'] : '' ?>">
                            <button type="button" onclick="toggleDropdownFormDivisi()" id="trigger_form_divisi"
                                    class="w-full pl-3.5 pr-10 py-2.5 bg-white border-2 border-black text-xs font-semibold text-black focus:outline-none focus:bg-zinc-50 cursor-pointer transition flex items-center justify-between text-left">
                                <span id="label_form_divisi" class="<?= $label_form_divisi ? 'text-black font-semibold' : 'text-zinc-400 font-normal' ?>">
                                    <?= $label_form_divisi ?: '-- Pilih Divisi --' ?>
                                </span>
                                <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-3 text-black">
                                    <svg id="arrow_form_divisi" class="w-4 h-4 shrink-0 transition-transform duration-150" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
                                    </svg>
                                </div>
                            </button>

                            <div id="menu_form_divisi" class="neo-dropdown-menu absolute left-0 w-full top-full mt-1 bg-white border-2 border-black shadow-[3px_3px_0px_#000] max-h-60 overflow-y-auto z-50 hidden py-1">
                                <div class="neo-dropdown-item px-3.5 py-2 text-xs font-semibold cursor-pointer border-b border-zinc-100 text-zinc-500"
                                     onclick="pilihFormDivisi('', '-- Pilih Divisi --')">
                                    -- Pilih Divisi --
                                </div>
                                <?php foreach ($daftar_divisi as $div): ?>
                                    <?php $is_f_sel = ($data_edit && $data_edit['division_id'] == $div['id']); ?>
                                    <div class="neo-dropdown-item px-3.5 py-2 text-xs font-semibold cursor-pointer border-b border-zinc-100 last:border-b-0"
                                         data-selected="<?= $is_f_sel ? 'true' : 'false' ?>"
                                         onclick="pilihFormDivisi(<?= $div['id'] ?>, '<?= htmlspecialchars($div['nama_divisi'], ENT_QUOTES) ?>')">
                                        <?= sanitize($div['nama_divisi']) ?>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>

                    <div>
                        <label for="no_wa" class="block font-mono font-bold text-xs uppercase tracking-wider text-black mb-1.5">
                            NO. WHATSAPP
                        </label>
                        <input type="text" id="no_wa" name="no_wa"
                               value="<?= $data_edit ? sanitize($data_edit['no_wa'] ?? '') : '' ?>"
                               placeholder="Contoh: 081234567890"
                               class="w-full px-3.5 py-2.5 bg-white border-2 border-black font-mono text-xs text-black placeholder:text-zinc-400 focus:outline-none focus:bg-zinc-50 transition">
                        <p class="font-mono text-[10px] text-zinc-500 mt-1">
                            *Digunakan otomatis untuk bot reminder piket H-1
                        </p>
                    </div>

                    <div class="pt-1">
                        <label class="flex items-center gap-2.5 cursor-pointer select-none">
                            <input type="checkbox" id="is_active" name="is_active" value="1"
                                   <?= (!$data_edit || $data_edit['is_active'] == 1) ? 'checked' : '' ?>
                                   class="w-4 h-4 accent-[#164E33] border-2 border-black rounded cursor-pointer">
                            <span class="font-bold text-sm text-black">Status Anggota Aktif</span>
                        </label>
                    </div>

                    <div class="pt-2 flex items-center gap-2">
                        <button type="submit"
                                class="flex-1 py-3 px-4 bg-[#164E33] hover:bg-[#123e29] text-white border-2 border-black shadow-[3px_3px_0px_#000] font-mono font-bold text-xs uppercase tracking-wider flex items-center justify-center gap-2 hover:translate-x-[1px] hover:translate-y-[1px] transition cursor-pointer">
                            <svg class="w-4 h-4 text-white shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0m-5 8a2 2 0 100-4 2 2 0 000 4zm0 0c1.306 0 2.417.835 2.83 2M9 14a3.001 3.001 0 00-2.83 2M15 11h3m-3 4h2"/>
                            </svg>
                            <span><?= $data_edit ? 'SIMPAN PERUBAHAN' : 'TAMBAH PENGURUS' ?></span>
                        </button>
                        <?php if ($data_edit): ?>
                            <a href="members.php" class="py-3 px-4 bg-white hover:bg-zinc-100 text-black border-2 border-black shadow-[3px_3px_0px_#000] font-mono font-bold text-xs uppercase tracking-wider transition">
                                BATAL
                            </a>
                        <?php endif; ?>
                    </div>
                </form>
            </div>

        </div>

        <!-- Right Column: Table -->
        <div class="lg:col-span-8 space-y-6">

            <!-- Card Table Daftar Pengurus -->
            <div class="bg-white border-2 border-black shadow-[4px_4px_0px_#000] overflow-hidden">
                <!-- Table Header Controls -->
                <div class="p-4 sm:p-5 border-b-2 border-black flex flex-col md:flex-row md:items-center md:justify-between gap-3">
                    <div class="flex items-center gap-2">
                        <h2 class="text-base sm:text-lg font-black uppercase tracking-tight text-black">
                            Daftar Pengurus
                        </h2>
                        <span class="border-2 border-black px-2 py-0.5 font-mono text-xs font-bold bg-[#FAF8F5]">
                            (<?= count($daftar_pengurus) ?>)
                        </span>
                    </div>

                    <form id="form_filter" action="members.php" method="GET" class="flex flex-wrap items-center border-2 border-black bg-white shadow-[3px_3px_0px_#000]">
                        <div class="relative flex items-center border-r-2 border-black">
                            <svg class="w-3.5 h-3.5 text-zinc-400 absolute left-2.5 pointer-events-none" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                            </svg>
                            <input type="text" name="search" value="<?= htmlspecialchars($search) ?>"
                                   placeholder="Cari nama / NIM..."
                                   class="pl-8 pr-3 py-2 bg-transparent font-mono text-xs text-black placeholder:text-zinc-400 focus:outline-none w-36 sm:w-44">
                        </div>

                        <!-- Custom Dropdown Divisi Filter -->
                        <div class="relative border-r-2 border-black" id="wrapper_filter_divisi">
                            <input type="hidden" name="filter_divisi" id="input_filter_divisi" value="<?= $filter_divisi ?>">
                            <button type="button" onclick="toggleDropdownFilter()" id="trigger_filter_divisi"
                                    class="pl-3 pr-8 py-2 bg-transparent font-mono font-bold text-xs uppercase tracking-wider text-black focus:outline-none cursor-pointer flex items-center justify-between gap-2 whitespace-nowrap">
                                <span id="label_filter_divisi"><?= htmlspecialchars(strtoupper($label_filter_divisi)) ?></span>
                                <svg id="arrow_filter_divisi" class="w-3.5 h-3.5 text-black absolute right-2 pointer-events-none transition-transform duration-150" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
                                </svg>
                            </button>

                            <div id="menu_filter_divisi" class="neo-dropdown-menu absolute left-0 min-w-[220px] top-full mt-1 bg-white border-2 border-black shadow-[3px_3px_0px_#000] max-h-60 overflow-y-auto z-50 hidden py-1">
                                <div class="neo-dropdown-item px-3.5 py-2 text-xs font-mono font-bold uppercase tracking-wider cursor-pointer border-b border-zinc-100 last:border-b-0 whitespace-nowrap"
                                     data-selected="<?= $filter_divisi === 0 ? 'true' : 'false' ?>"
                                     onclick="pilihFilterDivisi(0, 'SEMUA DIVISI')">
                                    <span>SEMUA DIVISI</span>
                                </div>
                                <?php foreach ($daftar_divisi as $d): ?>
                                    <?php $is_sel = ($filter_divisi == $d['id']); ?>
                                    <div class="neo-dropdown-item px-3.5 py-2 text-xs font-mono font-bold uppercase tracking-wider cursor-pointer border-b border-zinc-100 last:border-b-0 whitespace-nowrap"
                                         data-selected="<?= $is_sel ? 'true' : 'false' ?>"
                                         onclick="pilihFilterDivisi(<?= $d['id'] ?>, '<?= htmlspecialchars(strtoupper($d['nama_divisi']), ENT_QUOTES) ?>')">
                                        <span><?= htmlspecialchars(strtoupper($d['nama_divisi'])) ?></span>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>

                        <button type="submit" class="px-3.5 py-2 bg-black text-white hover:bg-zinc-800 font-mono font-bold text-xs uppercase tracking-wider transition cursor-pointer">
                            CARI
                        </button>

                        <?php if (!empty($search) || $filter_divisi > 0): ?>
                            <a href="members.php" class="px-3 py-2 bg-zinc-100 hover:bg-zinc-200 border-l-2 border-black font-mono text-xs font-bold text-zinc-700">
                                RESET
                            </a>
                        <?php endif; ?>
                    </form>
                </div>

                <!-- Table Content -->
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead class="border-b-2 border-black bg-[#FAF8F5] font-mono text-[11px] font-bold uppercase tracking-wider text-zinc-600">
                            <tr>
                                <th class="px-5 py-3.5">NAMA PENGURUS</th>
                                <th class="px-5 py-3.5">DIVISI</th>
                                <th class="px-5 py-3.5">WHATSAPP</th>
                                <th class="px-5 py-3.5">STATUS</th>
                                <th class="px-5 py-3.5 text-right">AKSI</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-zinc-200 bg-white">
                            <?php if (empty($daftar_pengurus_tampil)): ?>
                                <tr>
                                    <td colspan="5" class="px-5 py-10 text-center font-mono text-xs text-zinc-500">
                                        Belum ada data pengurus yang sesuai.
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($daftar_pengurus_tampil as $m): ?>
                                    <tr class="hover:bg-[#FAF8F5] transition">
                                        <td class="px-5 py-3.5">
                                            <div class="font-bold text-sm text-black leading-tight">
                                                <?= sanitize($m['nama']) ?>
                                            </div>
                                            <div class="font-mono text-xs text-zinc-500 mt-0.5">
                                                <?= $m['nim'] ? sanitize($m['nim']) : '-' ?>
                                            </div>
                                        </td>
                                        <td class="px-5 py-3.5">
                                            <span class="inline-block px-3 py-1 bg-[#FAF8F5] border-2 border-black text-black font-mono font-bold text-xs shadow-[2px_2px_0px_#000]">
                                                <?= sanitize($m['nama_divisi']) ?>
                                            </span>
                                        </td>
                                        <td class="px-5 py-3.5 font-mono text-xs text-black">
                                            <?= $m['no_wa'] ? sanitize($m['no_wa']) : '-' ?>
                                        </td>
                                        <td class="px-5 py-3.5">
                                            <?php if ($m['is_active']): ?>
                                                <span class="inline-block px-2.5 py-0.5 bg-[#164E33] text-white border-2 border-black font-mono font-bold text-[11px] uppercase tracking-wider shadow-[1px_1px_0px_#000]">
                                                    Aktif
                                                </span>
                                            <?php else: ?>
                                                <span class="inline-block px-2.5 py-0.5 bg-zinc-100 text-zinc-500 border-2 border-black font-mono font-bold text-[11px] uppercase tracking-wider">
                                                    Nonaktif
                                                </span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="px-5 py-3.5 text-right font-mono text-xs font-bold space-x-2">
                                            <a href="members.php?aksi=edit&id=<?= $m['id'] ?>"
                                               class="px-2.5 py-1 bg-white hover:bg-zinc-100 text-black border-2 border-black shadow-[2px_2px_0px_#000] inline-block hover:translate-x-[1px] hover:translate-y-[1px] transition">
                                                Edit
                                            </a>
                                            <a href="members.php?aksi=hapus&id=<?= $m['id'] ?>"
                                               onclick="return confirm('Hapus data pengurus <?= sanitize($m['nama']) ?>?');"
                                               class="px-2.5 py-1 bg-[#E84125] hover:bg-red-700 text-white border-2 border-black shadow-[2px_2px_0px_#000] inline-block hover:translate-x-[1px] hover:translate-y-[1px] transition">
                                                Hapus
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Table Footer & Pagination -->
                <div class="p-4 sm:px-5 sm:py-3.5 border-t-2 border-black flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 bg-[#FAF8F5]">
                    <span class="font-mono text-xs text-zinc-700 font-bold">
                        Menampilkan <?= $offset_start ?> - <?= $offset_end ?> dari <?= $total_hasil ?> Pengurus Terdaftar
                    </span>

                    <div class="flex items-center gap-1.5 font-mono text-xs">
                        <?php if ($halaman > 1): ?>
                            <a href="members.php?page=<?= $halaman - 1 ?>&search=<?= urlencode($search) ?>&filter_divisi=<?= $filter_divisi ?>"
                               class="px-2.5 py-1 border-2 border-black bg-white text-black font-bold shadow-[2px_2px_0px_#000] hover:bg-zinc-100 transition">
                                &larr; Prev
                            </a>
                        <?php else: ?>
                            <span class="px-2.5 py-1 border-2 border-zinc-300 bg-white text-zinc-400 font-bold cursor-not-allowed">
                                &larr; Prev
                            </span>
                        <?php endif; ?>

                        <?php for ($p = 1; $p <= $total_halaman; $p++): ?>
                            <?php if ($p == $halaman): ?>
                                <span class="px-3 py-1 border-2 border-black bg-[#164E33] text-white font-bold shadow-[2px_2px_0px_#000]">
                                    <?= $p ?>
                                </span>
                            <?php else: ?>
                                <a href="members.php?page=<?= $p ?>&search=<?= urlencode($search) ?>&filter_divisi=<?= $filter_divisi ?>"
                                   class="px-3 py-1 border-2 border-black bg-white text-black font-bold shadow-[2px_2px_0px_#000] hover:bg-zinc-100 transition">
                                    <?= $p ?>
                                </a>
                            <?php endif; ?>
                        <?php endfor; ?>

                        <?php if ($halaman < $total_halaman): ?>
                            <a href="members.php?page=<?= $halaman + 1 ?>&search=<?= urlencode($search) ?>&filter_divisi=<?= $filter_divisi ?>"
                               class="px-2.5 py-1 border-2 border-black bg-white text-black font-bold shadow-[2px_2px_0px_#000] hover:bg-zinc-100 transition">
                                Next &rarr;
                            </a>
                        <?php else: ?>
                            <span class="px-2.5 py-1 border-2 border-zinc-300 bg-white text-zinc-400 font-bold cursor-not-allowed">
                                Next &rarr;
                            </span>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

        </div>

    </div>

</div>

<script>
function toggleDropdownFilter() {
    var menu = document.getElementById('menu_filter_divisi');
    var arrow = document.getElementById('arrow_filter_divisi');
    var isHidden = menu.classList.contains('hidden');

    var mForm = document.getElementById('menu_form_divisi');
    var aForm = document.getElementById('arrow_form_divisi');
    if (mForm) mForm.classList.add('hidden');
    if (aForm) aForm.classList.remove('rotate-180');

    if (isHidden) {
        menu.classList.remove('hidden');
        arrow.classList.add('rotate-180');
    } else {
        menu.classList.add('hidden');
        arrow.classList.remove('rotate-180');
    }
}

function pilihFilterDivisi(val, label) {
    document.getElementById('input_filter_divisi').value = val;
    document.getElementById('label_filter_divisi').textContent = label;
    var menu = document.getElementById('menu_filter_divisi');
    var arrow = document.getElementById('arrow_filter_divisi');
    if (menu) menu.classList.add('hidden');
    if (arrow) arrow.classList.remove('rotate-180');

    document.getElementById('form_filter').submit();
}

function toggleDropdownFormDivisi() {
    var menu = document.getElementById('menu_form_divisi');
    var arrow = document.getElementById('arrow_form_divisi');
    var isHidden = menu.classList.contains('hidden');

    var mFilter = document.getElementById('menu_filter_divisi');
    var aFilter = document.getElementById('arrow_filter_divisi');
    if (mFilter) mFilter.classList.add('hidden');
    if (aFilter) aFilter.classList.remove('rotate-180');

    if (isHidden) {
        menu.classList.remove('hidden');
        arrow.classList.add('rotate-180');
    } else {
        menu.classList.add('hidden');
        arrow.classList.remove('rotate-180');
    }
}

function pilihFormDivisi(val, label) {
    document.getElementById('input_form_divisi').value = val;
    var lbl = document.getElementById('label_form_divisi');
    lbl.textContent = label;
    if (val === '') {
        lbl.className = 'text-zinc-400 font-normal';
    } else {
        lbl.className = 'text-black font-semibold';
    }
    var menu = document.getElementById('menu_form_divisi');
    var arrow = document.getElementById('arrow_form_divisi');
    if (menu) menu.classList.add('hidden');
    if (arrow) arrow.classList.remove('rotate-180');

    document.querySelectorAll('#menu_form_divisi .neo-dropdown-item').forEach(function(item) {
        item.setAttribute('data-selected', 'false');
    });
    if (window.event && window.event.currentTarget) {
        window.event.currentTarget.setAttribute('data-selected', 'true');
    }
}

document.addEventListener('click', function(e) {
    var wFilter = document.getElementById('wrapper_filter_divisi');
    if (wFilter && !wFilter.contains(e.target)) {
        var mF = document.getElementById('menu_filter_divisi');
        var aF = document.getElementById('arrow_filter_divisi');
        if (mF) mF.classList.add('hidden');
        if (aF) aF.classList.remove('rotate-180');
    }

    var wForm = document.getElementById('wrapper_form_divisi');
    if (wForm && !wForm.contains(e.target)) {
        var mForm = document.getElementById('menu_form_divisi');
        var aForm = document.getElementById('arrow_form_divisi');
        if (mForm) mForm.classList.add('hidden');
        if (aForm) aForm.classList.remove('rotate-180');
    }
});

// Form submit validation
var formMember = document.getElementById('form_member');
if (formMember) {
    formMember.addEventListener('submit', function(e) {
        var divVal = document.getElementById('input_form_divisi').value;
        if (!divVal || parseInt(divVal) <= 0) {
            alert('Silakan pilih divisi terlebih dahulu!');
            e.preventDefault();
        }
    });
}
</script>

<?php require_once __DIR__ . '/footer.php'; ?>
