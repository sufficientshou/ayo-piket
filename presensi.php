<?php
require_once __DIR__ . '/config/database.php';
$judul_halaman = "Form Presensi Piket";
require_once __DIR__ . '/includes/header.php';

$stmt_members = $koneksi->query("
    SELECT m.id, m.nama, m.nim, d.nama_divisi 
    FROM members m
    JOIN divisions d ON m.division_id = d.id
    WHERE m.is_active = 1
    ORDER BY m.nama ASC
");
$daftar_pengurus = $stmt_members->fetchAll();

$pesan_error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $member_id = (int)($_POST['member_id'] ?? 0);
    $tanggal = sanitize($_POST['tanggal'] ?? '');
    $jam_mulai = sanitize($_POST['jam_mulai'] ?? '');
    $jam_selesai = sanitize($_POST['jam_selesai'] ?? '');
    $catatan = sanitize($_POST['catatan'] ?? '');

    if ($member_id <= 0 || empty($tanggal) || empty($jam_mulai) || empty($jam_selesai)) {
        $pesan_error = "Harap lengkapi semua data wajib pada form!";
    } else if (strtotime($jam_selesai) <= strtotime($jam_mulai)) {
        $pesan_error = "Jam selesai piket harus lebih besar dari jam mulai!";
    } else if (!isset($_FILES['foto_bukti']) || $_FILES['foto_bukti']['error'] !== UPLOAD_ERR_OK) {
        $pesan_error = "Foto dokumentasi piket wajib diunggah!";
    } else {
        $upload_hasil = upload_foto_dokumentasi($_FILES['foto_bukti']);

        if (!$upload_hasil['sukses']) {
            $pesan_error = $upload_hasil['pesan'];
        } else {
            $nama_file = $upload_hasil['nama_file'];

            $stmt_ins = $koneksi->prepare("
                INSERT INTO attendances (member_id, tanggal, jam_mulai, jam_selesai, foto_bukti, status_verifikasi, catatan)
                VALUES (?, ?, ?, ?, ?, 'valid', ?)
            ");
            $stmt_ins->execute([$member_id, $tanggal, $jam_mulai, $jam_selesai, $nama_file, $catatan]);

            set_flash_message('sukses', 'Presensi dan laporan dokumentasi piket berhasil dikirim. Terima kasih!');
            header("Location: index.php");
            exit();
        }
    }
}
?>

<div class="max-w-xl mx-auto py-4">
    <div class="bg-white rounded-3xl shadow-sm border border-slate-200 p-6 sm:p-8">
        <div class="text-center mb-6">
            <span class="inline-flex p-3 bg-indigo-50 text-indigo-600 rounded-2xl mb-2">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
            </span>
            <h1 class="text-2xl font-extrabold text-slate-800">Form Laporan Piket</h1>
            <p class="text-xs text-slate-500 mt-1">Silakan isi presensi kehadiran dan unggah foto dokumentasi piket</p>
        </div>

        <?php if (!empty($pesan_error)): ?>
            <div class="mb-5 p-3.5 bg-rose-50 border border-rose-200 text-rose-700 text-xs sm:text-sm rounded-xl font-medium">
                <?= $pesan_error ?>
            </div>
        <?php endif; ?>

        <form action="presensi.php" method="POST" enctype="multipart/form-data" class="space-y-4">
            <div>
                <label for="member_id" class="block text-xs font-semibold uppercase tracking-wider text-slate-700 mb-1">
                    Nama Pengurus *
                </label>
                <select name="member_id" id="member_id" required onchange="perbaruiDivisi()"
                        class="w-full px-4 py-2.5 rounded-xl border border-slate-300 focus:ring-2 focus:ring-indigo-500 focus:outline-none text-sm transition">
                    <option value="" data-divisi="">-- Pilih Nama Kamu --</option>
                    <?php foreach ($daftar_pengurus as $png): ?>
                        <option value="<?= $png['id'] ?>" data-divisi="<?= sanitize($png['nama_divisi']) ?>" <?= (isset($member_id) && $member_id == $png['id']) ? 'selected' : '' ?>>
                            <?= sanitize($png['nama']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div>
                <label class="block text-xs font-semibold uppercase tracking-wider text-slate-700 mb-1">
                    Divisi
                </label>
                <input type="text" id="tampilan_divisi" readonly placeholder="Divisi akan otomatis terisi"
                       class="w-full px-4 py-2.5 rounded-xl border border-slate-200 bg-slate-50 text-slate-500 text-sm focus:outline-none">
            </div>

            <div>
                <label for="tanggal" class="block text-xs font-semibold uppercase tracking-wider text-slate-700 mb-1">
                    Tanggal Piket *
                </label>
                <input type="date" name="tanggal" id="tanggal" required value="<?= isset($tanggal) ? $tanggal : date('Y-m-d') ?>"
                       class="w-full px-4 py-2.5 rounded-xl border border-slate-300 focus:ring-2 focus:ring-indigo-500 focus:outline-none text-sm transition">
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label for="jam_mulai" class="block text-xs font-semibold uppercase tracking-wider text-slate-700 mb-1">
                        Jam Mulai *
                    </label>
                    <input type="time" name="jam_mulai" id="jam_mulai" required value="<?= isset($jam_mulai) ? $jam_mulai : '09:00' ?>"
                           class="w-full px-4 py-2.5 rounded-xl border border-slate-300 focus:ring-2 focus:ring-indigo-500 focus:outline-none text-sm transition">
                </div>
                <div>
                    <label for="jam_selesai" class="block text-xs font-semibold uppercase tracking-wider text-slate-700 mb-1">
                        Jam Selesai *
                    </label>
                    <input type="time" name="jam_selesai" id="jam_selesai" required value="<?= isset($jam_selesai) ? $jam_selesai : '12:00' ?>"
                           class="w-full px-4 py-2.5 rounded-xl border border-slate-300 focus:ring-2 focus:ring-indigo-500 focus:outline-none text-sm transition">
                </div>
            </div>

            <div>
                <label for="foto_bukti" class="block text-xs font-semibold uppercase tracking-wider text-slate-700 mb-1">
                    Upload Foto Dokumentasi * (Maks. 2 MB)
                </label>
                <input type="file" name="foto_bukti" id="foto_bukti" required accept="image/*"
                       class="w-full px-3 py-2 text-xs text-slate-600 rounded-xl border border-slate-300 file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100">
                <p class="text-[11px] text-slate-400 mt-1">Format didukung: JPG, PNG, WEBP.</p>
            </div>

            <div>
                <label for="catatan" class="block text-xs font-semibold uppercase tracking-wider text-slate-700 mb-1">
                    Catatan Kegiatan (Opsional)
                </label>
                <textarea name="catatan" id="catatan" rows="3" placeholder="Contoh: Membersihkan sekretariat, merapikan meja & menyapu..."
                          class="w-full px-4 py-2.5 rounded-xl border border-slate-300 focus:ring-2 focus:ring-indigo-500 focus:outline-none text-sm transition"><?= isset($catatan) ? $catatan : '' ?></textarea>
            </div>

            <div class="pt-2">
                <button type="submit" class="w-full py-3 px-4 bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-sm rounded-xl shadow-md transition">
                    Kirim Laporan Piket
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function perbaruiDivisi() {
    var select = document.getElementById('member_id');
    var selectedOption = select.options[select.selectedIndex];
    var divisi = selectedOption.getAttribute('data-divisi') || '';
    document.getElementById('tampilan_divisi').value = divisi;
}
document.addEventListener('DOMContentLoaded', perbaruiDivisi);
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
