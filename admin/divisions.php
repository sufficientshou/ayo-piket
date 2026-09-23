<?php
require_once __DIR__ . '/../config/database.php';
$judul_halaman = "Kelola Data Divisi";
require_once __DIR__ . '/header.php';

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

$stmt = $koneksi->query("
    SELECT d.id, d.nama_divisi, d.created_at, COUNT(m.id) AS total_anggota
    FROM divisions d
    LEFT JOIN members m ON d.id = m.division_id
    GROUP BY d.id
    ORDER BY d.nama_divisi ASC
");
$daftar_divisi = $stmt->fetchAll();
?>

<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-800">Master Data Divisi</h1>
            <p class="text-sm text-slate-500">Kelola daftar divisi kepengurusan himpunan</p>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-200 h-fit">
            <h2 class="text-lg font-bold text-slate-800 mb-4">
                <?= $data_edit ? 'Edit Divisi' : 'Tambah Divisi Baru' ?>
            </h2>

            <form action="divisions.php" method="POST" class="space-y-4">
                <?php if ($data_edit): ?>
                    <input type="hidden" name="id_divisi" value="<?= $data_edit['id'] ?>">
                <?php endif; ?>

                <div>
                    <label for="nama_divisi" class="block text-xs font-semibold uppercase tracking-wider text-slate-700 mb-1">Nama Divisi</label>
                    <input type="text" id="nama_divisi" name="nama_divisi" required
                           value="<?= $data_edit ? sanitize($data_edit['nama_divisi']) : '' ?>"
                           placeholder="Contoh: Kaderisasi"
                           class="w-full px-4 py-2 rounded-xl border border-slate-300 focus:ring-2 focus:ring-indigo-500 focus:outline-none text-sm transition">
                </div>

                <div class="flex items-center space-x-2 pt-2">
                    <button type="submit" class="flex-1 py-2 px-4 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold text-sm rounded-xl transition shadow-sm">
                        <?= $data_edit ? 'Simpan Perubahan' : 'Tambah Divisi' ?>
                    </button>
                    <?php if ($data_edit): ?>
                        <a href="divisions.php" class="py-2 px-3 bg-slate-100 hover:bg-slate-200 text-slate-600 font-semibold text-sm rounded-xl transition">
                            Batal
                        </a>
                    <?php endif; ?>
                </div>
            </form>
        </div>

        <div class="lg:col-span-2 bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
            <div class="p-5 border-b border-slate-100 flex justify-between items-center">
                <h2 class="font-bold text-slate-800">Daftar Divisi (<?= count($daftar_divisi) ?>)</h2>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm text-slate-600">
                    <thead class="bg-slate-50 text-xs uppercase tracking-wider text-slate-500 border-b border-slate-200">
                        <tr>
                            <th class="px-6 py-3">No</th>
                            <th class="px-6 py-3">Nama Divisi</th>
                            <th class="px-6 py-3">Jumlah Anggota</th>
                            <th class="px-6 py-3 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <?php if (empty($daftar_divisi)): ?>
                            <tr>
                                <td colspan="4" class="px-6 py-8 text-center text-slate-400">Belum ada data divisi.</td>
                            </tr>
                        <?php else: ?>
                            <?php $nomor = 1; foreach ($daftar_divisi as $div): ?>
                                <tr class="hover:bg-slate-50 transition">
                                    <td class="px-6 py-4 font-medium text-slate-500"><?= $nomor++ ?></td>
                                    <td class="px-6 py-4 font-semibold text-slate-800"><?= sanitize($div['nama_divisi']) ?></td>
                                    <td class="px-6 py-4">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-50 text-blue-700">
                                            <?= $div['total_anggota'] ?> Pengurus
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-right space-x-2">
                                        <a href="divisions.php?aksi=edit&id=<?= $div['id'] ?>" class="text-indigo-600 hover:text-indigo-900 font-medium text-xs">Edit</a>
                                        <a href="divisions.php?aksi=hapus&id=<?= $div['id'] ?>" 
                                           onclick="return confirm('Hapus divisi ini? Pengurus di divisi ini juga akan terhapus.')" 
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
