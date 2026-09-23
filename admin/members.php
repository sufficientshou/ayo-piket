<?php
require_once __DIR__ . '/../config/database.php';
$judul_halaman = "Kelola Data Pengurus";
require_once __DIR__ . '/header.php';

$aksi = $_GET['aksi'] ?? '';
$id_edit = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$data_edit = null;

$stmt_div = $koneksi->query("SELECT * FROM divisions ORDER BY nama_divisi ASC");
$daftar_divisi = $stmt_div->fetchAll();

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

if ($aksi === 'hapus' && $id_edit > 0) {
    $stmt = $koneksi->prepare("DELETE FROM members WHERE id = ?");
    $stmt->execute([$id_edit]);
    set_flash_message('sukses', 'Data pengurus berhasil dihapus!');
    header("Location: members.php");
    exit();
}

if ($aksi === 'edit' && $id_edit > 0) {
    $stmt = $koneksi->prepare("SELECT * FROM members WHERE id = ? LIMIT 1");
    $stmt->execute([$id_edit]);
    $data_edit = $stmt->fetch();
}

$filter_divisi = isset($_GET['filter_divisi']) ? (int)$_GET['filter_divisi'] : 0;
$query_str = "
    SELECT m.*, d.nama_divisi 
    FROM members m
    JOIN divisions d ON m.division_id = d.id
";
$params = [];

if ($filter_divisi > 0) {
    $query_str .= " WHERE m.division_id = ?";
    $params[] = $filter_divisi;
}

$query_str .= " ORDER BY d.nama_divisi ASC, m.nama ASC";
$stmt_members = $koneksi->prepare($query_str);
$stmt_members->execute($params);
$daftar_pengurus = $stmt_members->fetchAll();
?>

<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-800">Master Data Pengurus</h1>
            <p class="text-sm text-slate-500">Kelola daftar seluruh anggota pengurus himpunan yang bertugas piket</p>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-200 h-fit">
            <h2 class="text-lg font-bold text-slate-800 mb-4">
                <?= $data_edit ? 'Edit Data Pengurus' : 'Tambah Pengurus Baru' ?>
            </h2>

            <form action="members.php" method="POST" class="space-y-4">
                <?php if ($data_edit): ?>
                    <input type="hidden" name="id_member" value="<?= $data_edit['id'] ?>">
                <?php endif; ?>

                <div>
                    <label for="nim" class="block text-xs font-semibold uppercase tracking-wider text-slate-700 mb-1">NIM (Opsional)</label>
                    <input type="text" id="nim" name="nim"
                           value="<?= $data_edit ? sanitize($data_edit['nim'] ?? '') : '' ?>"
                           placeholder="Contoh: 2110511001"
                           class="w-full px-4 py-2 rounded-xl border border-slate-300 focus:ring-2 focus:ring-indigo-500 focus:outline-none text-sm transition">
                </div>

                <div>
                    <label for="nama" class="block text-xs font-semibold uppercase tracking-wider text-slate-700 mb-1">Nama Lengkap *</label>
                    <input type="text" id="nama" name="nama" required
                           value="<?= $data_edit ? sanitize($data_edit['nama']) : '' ?>"
                           placeholder="Masukkan nama pengurus"
                           class="w-full px-4 py-2 rounded-xl border border-slate-300 focus:ring-2 focus:ring-indigo-500 focus:outline-none text-sm transition">
                </div>

                <div>
                    <label for="division_id" class="block text-xs font-semibold uppercase tracking-wider text-slate-700 mb-1">Divisi *</label>
                    <select id="division_id" name="division_id" required
                            class="w-full px-4 py-2 rounded-xl border border-slate-300 focus:ring-2 focus:ring-indigo-500 focus:outline-none text-sm transition">
                        <option value="">-- Pilih Divisi --</option>
                        <?php foreach ($daftar_divisi as $div): ?>
                            <option value="<?= $div['id'] ?>" <?= ($data_edit && $data_edit['division_id'] == $div['id']) ? 'selected' : '' ?>>
                                <?= sanitize($div['nama_divisi']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div>
                    <label for="no_wa" class="block text-xs font-semibold uppercase tracking-wider text-slate-700 mb-1">No. WhatsApp</label>
                    <input type="text" id="no_wa" name="no_wa"
                           value="<?= $data_edit ? sanitize($data_edit['no_wa'] ?? '') : '' ?>"
                           placeholder="Contoh: 081234567890"
                           class="w-full px-4 py-2 rounded-xl border border-slate-300 focus:ring-2 focus:ring-indigo-500 focus:outline-none text-sm transition">
                </div>

                <div class="flex items-center space-x-2 pt-2">
                    <input type="checkbox" id="is_active" name="is_active" value="1"
                           <?= (!$data_edit || $data_edit['is_active'] == 1) ? 'checked' : '' ?>
                           class="w-4 h-4 text-indigo-600 rounded border-slate-300 focus:ring-indigo-500">
                    <label for="is_active" class="text-sm font-medium text-slate-700">Status Anggota Aktif</label>
                </div>

                <div class="flex items-center space-x-2 pt-2">
                    <button type="submit" class="flex-1 py-2 px-4 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold text-sm rounded-xl transition shadow-sm">
                        <?= $data_edit ? 'Simpan Perubahan' : 'Tambah Pengurus' ?>
                    </button>
                    <?php if ($data_edit): ?>
                        <a href="members.php" class="py-2 px-3 bg-slate-100 hover:bg-slate-200 text-slate-600 font-semibold text-sm rounded-xl transition">
                            Batal
                        </a>
                    <?php endif; ?>
                </div>
            </form>
        </div>

        <div class="lg:col-span-2 bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
            <div class="p-5 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                <h2 class="font-bold text-slate-800">Daftar Pengurus (<?= count($daftar_pengurus) ?>)</h2>
                
                <form action="members.php" method="GET" class="flex items-center space-x-2">
                    <select name="filter_divisi" onchange="this.form.submit()" 
                            class="text-xs px-3 py-1.5 rounded-lg border border-slate-300 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        <option value="0">Semua Divisi</option>
                        <?php foreach ($daftar_divisi as $d): ?>
                            <option value="<?= $d['id'] ?>" <?= $filter_divisi == $d['id'] ? 'selected' : '' ?>>
                                <?= sanitize($d['nama_divisi']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <?php if ($filter_divisi > 0): ?>
                        <a href="members.php" class="text-xs text-rose-600 hover:underline">Reset</a>
                    <?php endif; ?>
                </form>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm text-slate-600">
                    <thead class="bg-slate-50 text-xs uppercase tracking-wider text-slate-500 border-b border-slate-200">
                        <tr>
                            <th class="px-6 py-3">Nama Pengurus</th>
                            <th class="px-6 py-3">Divisi</th>
                            <th class="px-6 py-3">WhatsApp</th>
                            <th class="px-6 py-3">Status</th>
                            <th class="px-6 py-3 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <?php if (empty($daftar_pengurus)): ?>
                            <tr>
                                <td colspan="5" class="px-6 py-8 text-center text-slate-400">Belum ada pengurus terdaftar.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($daftar_pengurus as $m): ?>
                                <tr class="hover:bg-slate-50 transition">
                                    <td class="px-6 py-4">
                                        <div class="font-semibold text-slate-800"><?= sanitize($m['nama']) ?></div>
                                        <div class="text-xs text-slate-400"><?= $m['nim'] ? sanitize($m['nim']) : 'NIM -' ?></div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-indigo-50 text-indigo-700">
                                            <?= sanitize($m['nama_divisi']) ?>
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-xs font-mono text-slate-600">
                                        <?= $m['no_wa'] ? sanitize($m['no_wa']) : '-' ?>
                                    </td>
                                    <td class="px-6 py-4">
                                        <?php if ($m['is_active']): ?>
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-emerald-50 text-emerald-700 border border-emerald-200">Aktif</span>
                                        <?php else: ?>
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-slate-100 text-slate-500 border border-slate-200">Nonaktif</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="px-6 py-4 text-right space-x-2">
                                        <a href="members.php?aksi=edit&id=<?= $m['id'] ?>" class="text-indigo-600 hover:text-indigo-900 font-medium text-xs">Edit</a>
                                        <a href="members.php?aksi=hapus&id=<?= $m['id'] ?>" 
                                           onclick="return confirm('Hapus data pengurus <?= sanitize($m['nama']) ?>?')" 
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
</div>

<?php require_once __DIR__ . '/footer.php'; ?>
