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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama_divisi = sanitize($_POST['nama_divisi'] ?? '');

    if (empty($nama_divisi)) {
        set_flash_message('gagal', 'Nama divisi tidak boleh kosong!');
    } else {
        if (!empty($_POST['id_divisi'])) {
            $id = (int)$_POST['id_divisi'];
            $stmt = $koneksi->prepare("UPDATE divisions SET nama_divisi = ? WHERE id = ?");
            $stmt->execute([$nama_divisi, $id]);
            set_flash_message('sukses', 'Data divisi berhasil diperbarui!');
        } else {
            $stmt = $koneksi->prepare("INSERT INTO divisions (nama_divisi) VALUES (?)");
            $stmt->execute([$nama_divisi]);
            set_flash_message('sukses', 'Divisi baru berhasil ditambahkan!');
        }
        header("Location: divisions.php");
        exit();
    }
}

if ($aksi === 'hapus' && $id_edit > 0) {
    $stmt = $koneksi->prepare("DELETE FROM divisions WHERE id = ?");
    $stmt->execute([$id_edit]);
    set_flash_message('sukses', 'Divisi berhasil dihapus!');
    header("Location: divisions.php");
    exit();
}

if ($aksi === 'edit' && $id_edit > 0) {
    $stmt = $koneksi->prepare("SELECT * FROM divisions WHERE id = ? LIMIT 1");
    $stmt->execute([$id_edit]);
    $data_edit = $stmt->fetch();
}

$search = sanitize($_GET['search'] ?? '');

$query_str = "
    SELECT d.id, d.nama_divisi, d.created_at, COUNT(m.id) AS total_anggota
    FROM divisions d
    LEFT JOIN members m ON d.id = m.division_id
";
$params = [];

if (!empty($search)) {
    $query_str .= " WHERE d.nama_divisi LIKE ?";
    $params[] = "%$search%";
}

$query_str .= " GROUP BY d.id ORDER BY d.nama_divisi ASC";
$stmt = $koneksi->prepare($query_str);
$stmt->execute($params);
$daftar_divisi = $stmt->fetchAll();

$stmt_total = $koneksi->query("SELECT id FROM divisions");
$total_divisi = $stmt_total->rowCount();

$judul_halaman = "Kelola Data Divisi";
require_once __DIR__ . '/header.php';
?>

<div class="space-y-6 sm:space-y-7">

    <!-- Top Header & Badges -->
    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
        <div>
            <h1 class="text-2xl sm:text-3xl lg:text-4xl font-black uppercase tracking-tight text-black">
                Data Divisi
            </h1>
        </div>

        <div class="flex flex-wrap items-center gap-3 shrink-0">
            <div class="bg-white border-2 border-black font-mono font-bold text-xs uppercase tracking-wider px-3.5 py-2 shadow-[3px_3px_0px_#000]">
                TOTAL: <?= $total_divisi ?> DIVISI
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
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                        </svg>
                        <h2 class="text-base sm:text-lg font-black uppercase tracking-tight text-black">
                            <?= $data_edit ? 'Edit Divisi' : 'Tambah Divisi Baru' ?>
                        </h2>
                    </div>
                    <span class="bg-[#B8E926] border border-black px-2 py-0.5 font-mono text-[10px] font-bold uppercase tracking-wider text-black">
                        FORM INPUT
                    </span>
                </div>

                <form action="divisions.php" method="POST" class="space-y-4">
                    <?php if ($data_edit): ?>
                        <input type="hidden" name="id_divisi" value="<?= $data_edit['id'] ?>">
                    <?php endif; ?>

                    <div>
                        <label for="nama_divisi" class="block font-mono font-bold text-xs uppercase tracking-wider text-black mb-1.5">
                            NAMA DIVISI <span class="text-[#E84125]">*</span>
                        </label>
                        <input type="text" id="nama_divisi" name="nama_divisi" required
                               value="<?= $data_edit ? sanitize($data_edit['nama_divisi']) : '' ?>"
                               placeholder="Contoh: Infokom"
                               class="w-full px-3.5 py-2.5 bg-white border-2 border-black font-mono text-xs text-black placeholder:text-zinc-400 focus:outline-none focus:bg-zinc-50 transition">
                    </div>

                    <div class="pt-2 flex items-center gap-2">
                        <button type="submit"
                                class="flex-1 py-3 px-4 bg-[#164E33] hover:bg-[#123e29] text-white border-2 border-black shadow-[3px_3px_0px_#000] font-mono font-bold text-xs uppercase tracking-wider flex items-center justify-center gap-2 hover:translate-x-[1px] hover:translate-y-[1px] transition cursor-pointer">
                            <svg class="w-4 h-4 text-white shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                            </svg>
                            <span><?= $data_edit ? 'SIMPAN PERUBAHAN' : 'TAMBAH DIVISI' ?></span>
                        </button>
                        <?php if ($data_edit): ?>
                            <a href="divisions.php" class="py-3 px-4 bg-white hover:bg-zinc-100 text-black border-2 border-black shadow-[3px_3px_0px_#000] font-mono font-bold text-xs uppercase tracking-wider transition">
                                BATAL
                            </a>
                        <?php endif; ?>
                    </div>
                </form>
            </div>

        </div>

        <!-- Right Column: Table -->
        <div class="lg:col-span-8 space-y-6">

            <!-- Card Table Daftar Divisi -->
            <div class="bg-white border-2 border-black shadow-[4px_4px_0px_#000] overflow-hidden">
                <!-- Table Header Controls -->
                <div class="p-4 sm:p-5 border-b-2 border-black flex flex-col md:flex-row md:items-center md:justify-between gap-3">
                    <div class="flex items-center gap-2">
                        <h2 class="text-base sm:text-lg font-black uppercase tracking-tight text-black">
                            Daftar Divisi
                        </h2>
                        <span class="border-2 border-black px-2 py-0.5 font-mono text-xs font-bold bg-[#FAF8F5]">
                            (<?= count($daftar_divisi) ?>)
                        </span>
                    </div>

                    <form action="divisions.php" method="GET" class="flex flex-wrap items-center border-2 border-black bg-white shadow-[3px_3px_0px_#000]">
                        <div class="relative flex items-center">
                            <svg class="w-3.5 h-3.5 text-zinc-400 absolute left-2.5 pointer-events-none" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                            </svg>
                            <input type="text" name="search" value="<?= htmlspecialchars($search) ?>"
                                   placeholder="Cari divisi..."
                                   class="pl-8 pr-3 py-2 bg-transparent font-mono text-xs text-black placeholder:text-zinc-400 focus:outline-none w-36 sm:w-44">
                        </div>

                        <button type="submit" class="px-3.5 py-2 bg-black text-white hover:bg-zinc-800 font-mono font-bold text-xs uppercase tracking-wider transition cursor-pointer">
                            CARI
                        </button>

                        <?php if (!empty($search)): ?>
                            <a href="divisions.php" class="px-3 py-2 bg-zinc-100 hover:bg-zinc-200 border-l-2 border-black font-mono text-xs font-bold text-zinc-700">
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
                                <th class="px-5 py-3.5 w-16">NO</th>
                                <th class="px-5 py-3.5">NAMA DIVISI</th>
                                <th class="px-5 py-3.5">JUMLAH PENGURUS</th>
                                <th class="px-5 py-3.5 text-right">AKSI</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-zinc-200 bg-white">
                            <?php if (empty($daftar_divisi)): ?>
                                <tr>
                                    <td colspan="4" class="px-5 py-10 text-center font-mono text-xs text-zinc-500">
                                        Belum ada data divisi yang sesuai.
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php $nomor = 1; foreach ($daftar_divisi as $div): ?>
                                    <tr class="hover:bg-[#FAF8F5] transition">
                                        <td class="px-5 py-3.5 font-mono text-xs text-zinc-500 font-bold">
                                            <?= $nomor++ ?>
                                        </td>
                                        <td class="px-5 py-3.5">
                                            <span class="inline-block px-3 py-1 bg-[#FAF8F5] border-2 border-black text-black font-mono font-bold text-xs shadow-[2px_2px_0px_#000]">
                                                <?= sanitize($div['nama_divisi']) ?>
                                            </span>
                                        </td>
                                        <td class="px-5 py-3.5">
                                            <span class="inline-block px-2.5 py-0.5 bg-[#164E33] text-white border-2 border-black font-mono font-bold text-[11px] uppercase tracking-wider shadow-[1px_1px_0px_#000]">
                                                <?= $div['total_anggota'] ?> Pengurus
                                            </span>
                                        </td>
                                        <td class="px-5 py-3.5 text-right font-mono text-xs font-bold space-x-2">
                                            <a href="divisions.php?aksi=edit&id=<?= $div['id'] ?>"
                                               class="px-2.5 py-1 bg-white hover:bg-zinc-100 text-black border-2 border-black shadow-[2px_2px_0px_#000] inline-block hover:translate-x-[1px] hover:translate-y-[1px] transition">
                                                Edit
                                            </a>
                                            <a href="divisions.php?aksi=hapus&id=<?= $div['id'] ?>"
                                               onclick="return confirm('Hapus divisi <?= sanitize($div['nama_divisi']) ?>? Pengurus di divisi ini juga akan terhapus.');"
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

                <!-- Table Footer -->
                <div class="p-4 sm:px-5 sm:py-3.5 border-t-2 border-black flex items-center justify-between bg-[#FAF8F5]">
                    <span class="font-mono text-xs text-zinc-700 font-bold">
                        Menampilkan <?= count($daftar_divisi) ?> dari <?= $total_divisi ?> Divisi Terdaftar
                    </span>
                </div>
            </div>

        </div>

    </div>

</div>

<?php require_once __DIR__ . '/footer.php'; ?>
