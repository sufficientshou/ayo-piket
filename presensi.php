<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/functions.php';

if (isset($_GET['action']) && $_GET['action'] === 'get_feed') {
    header('Content-Type: application/json');
    $stmt_feed = $koneksi->query("
        SELECT a.id, a.jam_mulai, a.status_verifikasi, a.created_at, m.nama, d.nama_divisi
        FROM attendances a
        JOIN members m ON a.member_id = m.id
        JOIN divisions d ON m.division_id = d.id
        WHERE a.tanggal = CURRENT_DATE()
        ORDER BY a.created_at DESC, a.id DESC
    ");
    $data_feed = $stmt_feed->fetchAll();
    echo json_encode($data_feed);
    exit();
}

$pesan_error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $member_id = (int)($_POST['member_id'] ?? 0);
    $tanggal = sanitize($_POST['tanggal'] ?? '');
    $jam_mulai = sanitize($_POST['jam_mulai'] ?? '');
    $jam_selesai = sanitize($_POST['jam_selesai'] ?? '');
    $catatan = sanitize($_POST['catatan'] ?? '');
    $konfirmasi = isset($_POST['konfirmasi']) ? true : false;

    if ($member_id <= 0 || empty($tanggal) || empty($jam_mulai) || empty($jam_selesai)) {
        $pesan_error = "Harap lengkapi semua data wajib pada form!";
    } else if (!$konfirmasi) {
        $pesan_error = "Harap centang pernyataan keabsahan laporan sebelum mengirim!";
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
                VALUES (?, ?, ?, ?, ?, 'pending', ?)
            ");
            $stmt_ins->execute([$member_id, $tanggal, $jam_mulai, $jam_selesai, $nama_file, $catatan]);

            set_flash_message('sukses', '');
            header("Location: index.php");
            exit();
        }
    }
}

$stmt_members = $koneksi->query("
    SELECT m.id, m.nama, m.nim, d.nama_divisi 
    FROM members m
    JOIN divisions d ON m.division_id = d.id
    WHERE m.is_active = 1
    ORDER BY m.nama ASC
");
$daftar_pengurus = $stmt_members->fetchAll();

$stmt_hari_ini = $koneksi->query("
    SELECT a.id, a.jam_mulai, a.status_verifikasi, a.created_at, m.nama, d.nama_divisi
    FROM attendances a
    JOIN members m ON a.member_id = m.id
    JOIN divisions d ON m.division_id = d.id
    WHERE a.tanggal = CURRENT_DATE()
    ORDER BY a.created_at DESC, a.id DESC
");
$presensi_hari_ini = $stmt_hari_ini->fetchAll();

$judul_halaman = "Form Piket";
require_once __DIR__ . '/includes/header.php';
?>

<div class="max-w-[1600px] mx-auto space-y-6 sm:space-y-8">
    <div class="flex items-center text-xs sm:text-base font-mono font-bold">
        <div class="text-slate-600 uppercase tracking-wider">
            <a href="index.php" class="hover:text-black hover:underline">PORTAL HIMTIKA</a>
            <span class="mx-2 text-slate-400">/</span>
            <span class="text-black">PRESENSI HARIAN</span>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 lg:gap-8 items-start">
        <div class="lg:col-span-4 xl:col-span-4 space-y-6">
            <div class="bg-white border-2 border-black neo-shadow-lg overflow-hidden">
                <div id="feedHeader" class="bg-[#111111] text-white px-5 py-4 flex items-center justify-between cursor-pointer lg:cursor-default select-none border-b-0 lg:border-b-2 border-black" onclick="toggleFeedMobile()">
                    <span class="font-mono font-black text-xs sm:text-sm uppercase tracking-wider text-white">PRESENSI HARI INI</span>
                    <div class="lg:hidden flex items-center text-white">
                        <svg id="feedToggleArrow" class="w-4 h-4 transform transition-transform duration-200" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                        </svg>
                    </div>
                </div>
                <div id="feedListContainer" class="hidden lg:block divide-y divide-slate-100 max-h-[540px] overflow-y-auto">
                    <?php if (empty($presensi_hari_ini)): ?>
                        <div class="p-8 text-center text-xs font-mono text-slate-400">
                            Belum ada pengurus yang mengisi presensi piket hari ini.
                        </div>
                    <?php else: ?>
                        <?php foreach ($presensi_hari_ini as $feed): ?>
                            <?php 
                                $is_v = ($feed['status_verifikasi'] === 'valid');
                                $jam_str = date('H:i', strtotime($feed['jam_mulai'])) . ' WIB';
                            ?>
                            <div class="p-4 sm:p-5 flex flex-col gap-1.5 hover:bg-slate-50 transition border-b border-slate-100 last:border-b-0">
                                <div class="flex items-center justify-between gap-2">
                                    <span class="font-bold text-black text-xs sm:text-sm truncate"><?= sanitize($feed['nama']) ?></span>
                                    <span class="text-xs font-mono text-slate-500 shrink-0"><?= $jam_str ?></span>
                                </div>
                                <div class="flex items-center justify-between gap-2">
                                    <span class="text-xs font-mono text-slate-500 truncate"><?= sanitize($feed['nama_divisi']) ?></span>
                                    <?php if ($is_v): ?>
                                        <span class="border border-black px-2 py-0.5 text-[10px] sm:text-[11px] font-mono font-bold uppercase tracking-wider bg-[#DCFCE7] text-[#164E33] shrink-0">TERVERIFIKASI</span>
                                    <?php else: ?>
                                        <span class="border border-black px-2 py-0.5 text-[10px] sm:text-[11px] font-mono font-bold uppercase tracking-wider bg-[#FEF3C7] text-[#92400E] shrink-0">MENUNGGU REVIEW</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>

            <div class="hidden lg:block space-y-4">
                <div class="bg-white border-2 border-black p-5 neo-shadow flex items-start gap-3.5">
                    <div class="w-10 h-10 bg-[#B8E926] border-2 border-black font-mono font-black text-base flex items-center justify-center shrink-0">
                        ?
                    </div>
                    <div>
                        <h4 class="font-black text-sm uppercase text-black tracking-tight">LUPA JADWAL PIKET?</h4>
                        <p class="text-xs font-mono text-slate-600 mt-1 leading-relaxed">
                            Periksa kembali jadwal giliran kelompok piket Anda di lembar jadwal publik.
                        </p>
                        <a href="index.php" class="inline-flex items-center gap-1.5 font-mono font-bold text-xs text-black uppercase mt-2 hover:underline">
                            <span>Lihat Kalender Roster</span>
                            <span>&rarr;</span>
                        </a>
                    </div>
                </div>

                <div class="bg-white border-2 border-black p-5 neo-shadow flex items-start gap-3.5">
                    <div class="w-10 h-10 bg-[#E84125] text-white border-2 border-black font-mono font-black text-base flex items-center justify-center shrink-0">
                        !
                    </div>
                    <div>
                        <h4 class="font-black text-sm uppercase text-black tracking-tight">KENDALA UNGGAH FOTO?</h4>
                        <p class="text-xs font-mono text-slate-600 mt-1 leading-relaxed">
                            Ukuran foto di atas 2 MB dapat dikompres terlebih dahulu melalui tool kompresor resmi.
                        </p>
                        <a href="https://tinypng.com" target="_blank" class="inline-flex items-center gap-1.5 font-mono font-bold text-xs text-black uppercase mt-2 hover:underline">
                            <span>Buka Image Compressor</span>
                            <span>&rarr;</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <div class="lg:col-span-8 xl:col-span-8">
            <div class="bg-white border-2 border-black neo-shadow-lg overflow-hidden">
                <div class="h-3.5 bg-[#164E33] border-b-2 border-black"></div>

                <div class="bg-[#FAF8F5] p-5 sm:p-8 lg:p-10 border-b-2 border-black">
                    <div class="flex items-start gap-4 sm:gap-5">
                        <div class="w-14 h-14 sm:w-16 sm:h-16 bg-[#B8E926] border-2 border-black neo-shadow-sm flex items-center justify-center shrink-0">
                            <svg class="w-8 h-8 sm:w-9 sm:h-9 text-black stroke-[3]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <polyline points="20 6 9 17 4 12"></polyline>
                            </svg>
                        </div>
                        <div>
                            <div class="flex flex-wrap items-center gap-2.5">
                                <span class="bg-[#164E33] text-white px-3 py-1 text-xs font-mono font-bold uppercase tracking-wider">
                                    PRESENSI &amp; DOKUMENTASI
                                </span>
                            </div>
                            <h1 class="text-2xl sm:text-4xl lg:text-5xl font-black uppercase text-black tracking-tight mt-2 leading-tight">
                                FORM LAPORAN PIKET
                            </h1>
                            <p class="text-xs sm:text-sm lg:text-base text-slate-700 font-mono mt-1.5 leading-relaxed">
                                Silakan isi presensi kehadiran pengurus dan unggah foto dokumentasi kegiatan piket secara valid.
                            </p>
                        </div>
                    </div>

                    <div class="border-2 border-black p-4 sm:p-5 bg-white flex items-start gap-3.5 mt-6 neo-shadow-sm">
                        <div class="w-7 h-7 sm:w-8 sm:h-8 bg-[#E84125] text-white font-mono font-black text-sm flex items-center justify-center shrink-0 border border-black">
                            !
                        </div>
                        <p class="text-xs sm:text-sm font-mono text-slate-800 leading-relaxed">
                            <strong class="text-black">PERHATIAN:</strong> Pastikan nama dan jadwal yang Anda pilih telah sesuai dengan jadwal.
                        </p>
                    </div>

                    <?php if (!empty($pesan_error)): ?>
                        <div id="modalError" class="fixed inset-0 bg-black/60 z-50 flex items-center justify-center p-4">
                            <div class="bg-[#FAF8F5] border-2 border-black neo-shadow-lg max-w-lg w-full overflow-hidden relative">
                                <div class="h-3.5 bg-[#E84125] border-b-2 border-black"></div>
                                <div class="p-6 sm:p-7">
                                    <div class="flex items-center justify-between gap-2">
                                        <span class="bg-[#E84125] text-white px-2.5 py-0.5 text-[10px] sm:text-[11px] font-mono font-bold uppercase tracking-wider">
                                            PERINGATAN
                                        </span>
                                        <button type="button" onclick="document.getElementById('modalError').remove()" class="w-7 h-7 border-2 border-black bg-white flex items-center justify-center font-bold text-sm hover:bg-black hover:text-white transition cursor-pointer">&times;</button>
                                    </div>
                                    <h3 class="text-lg sm:text-xl font-black uppercase text-black tracking-tight mt-3 leading-tight">
                                        GAGAL MENGIRIM
                                    </h3>
                                    <p class="text-xs sm:text-sm font-mono text-slate-800 mt-2 leading-relaxed">
                                        <?= $pesan_error ?>
                                    </p>
                                </div>
                                <div class="px-6 py-4 bg-white border-t-2 border-black flex justify-end">
                                    <button type="button" onclick="document.getElementById('modalError').remove()" class="px-5 py-2 bg-black hover:bg-slate-800 text-white border-2 border-black font-mono font-bold text-xs uppercase tracking-wider neo-shadow-sm neo-btn transition cursor-pointer">
                                        TUTUP
                                    </button>
                                </div>
                            </div>
                        </div>
                        <script>
                            document.addEventListener('keydown', function(e) {
                                if (e.key === 'Escape') {
                                    var m = document.getElementById('modalError');
                                    if (m) m.remove();
                                }
                            });
                            document.addEventListener('click', function(e) {
                                var m = document.getElementById('modalError');
                                if (m && e.target === m) m.remove();
                            });
                        </script>
                    <?php endif; ?>
                </div>

                <div class="p-5 sm:p-8 lg:p-10 bg-white">
                    <form action="presensi.php" method="POST" enctype="multipart/form-data" class="space-y-6 sm:space-y-7" onsubmit="return validasiFormPresensi(event)">
                        <div>
                            <div class="flex items-center justify-between mb-2">
                                <label for="member_id" class="text-xs sm:text-base font-mono font-bold uppercase tracking-wider text-black">
                                    NAMA PENGURUS <span class="text-[#E84125]">*</span>
                                </label>
                                <span class="text-xs sm:text-sm font-mono uppercase text-slate-500 font-bold">WAJIB DIPILIH</span>
                            </div>
                            <div class="relative" id="wrapper_member_id">
                                <input type="hidden" name="member_id" id="member_id" value="<?= isset($member_id) ? (int)$member_id : '' ?>">
                                
                                <button type="button" id="trigger_member_id" onclick="toggleDropdownMember()"
                                        class="w-full bg-white border-2 border-black px-5 py-3.5 sm:py-4 pr-12 text-xs sm:text-base font-mono font-bold text-black neo-shadow-sm flex items-center justify-between cursor-pointer focus:outline-none text-left">
                                    <span id="label_member_id" class="truncate">
                                        <?php 
                                            $nama_terpilih = '-- Pilih Nama Kamu --';
                                            if (!empty($member_id)) {
                                                foreach ($daftar_pengurus as $png) {
                                                    if ($png['id'] == $member_id) {
                                                        $nama_terpilih = sanitize($png['nama']);
                                                        break;
                                                    }
                                                }
                                            }
                                            echo $nama_terpilih;
                                        ?>
                                    </span>
                                    <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-4 text-black">
                                        <svg id="arrow_member_id" class="w-4 h-4 sm:w-5 sm:h-5 fill-current transition-transform duration-150" viewBox="0 0 20 20">
                                            <path d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"/>
                                        </svg>
                                    </div>
                                </button>

                                <div id="menu_member_id" class="neo-dropdown-menu absolute left-0 right-0 top-full mt-1.5 bg-white border-2 border-black neo-shadow-lg max-h-60 overflow-y-auto z-50 hidden py-1">
                                    <div class="neo-dropdown-item px-5 py-3 text-xs sm:text-base font-mono font-bold text-slate-500 cursor-pointer border-b border-slate-100"
                                         onclick="pilihMember('', '', '-- Pilih Nama Kamu --')">
                                        -- Pilih Nama Kamu --
                                    </div>
                                    <?php foreach ($daftar_pengurus as $png): ?>
                                        <?php $is_sel = (isset($member_id) && $member_id == $png['id']); ?>
                                        <div class="neo-dropdown-item px-5 py-3 text-xs sm:text-base font-mono font-bold cursor-pointer flex items-center justify-between border-b border-slate-100 last:border-b-0"
                                             data-selected="<?= $is_sel ? 'true' : 'false' ?>"
                                             onclick="pilihMember('<?= $png['id'] ?>', '<?= sanitize(addslashes($png['nama_divisi'])) ?>', '<?= sanitize(addslashes($png['nama'])) ?>')">
                                            <span class="truncate"><?= sanitize($png['nama']) ?></span>
                                            <?php if ($is_sel): ?>
                                                <span class="text-xs font-mono shrink-0 ml-2">✓</span>
                                            <?php endif; ?>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>

                        <div>
                            <div class="flex items-center justify-between mb-2">
                                <div class="flex items-center gap-2.5">
                                    <label class="text-xs sm:text-base font-mono font-bold uppercase tracking-wider text-black">
                                        DIVISI
                                    </label>
                                    <span class="bg-white border border-black px-2 py-0.5 text-xs font-mono font-bold text-slate-700 uppercase">
                                        AUTO-FILL
                                    </span>
                                </div>
                                <span class="text-xs sm:text-sm font-mono text-slate-500 font-bold">Otomatis Terisi</span>
                            </div>
                            <?php 
                                $divisi_terpilih = '';
                                if (!empty($member_id)) {
                                    foreach ($daftar_pengurus as $png) {
                                        if ($png['id'] == $member_id) {
                                            $divisi_terpilih = sanitize($png['nama_divisi']);
                                            break;
                                        }
                                    }
                                }
                            ?>
                            <div class="relative border-2 border-black neo-shadow-sm flex items-center px-5 py-3.5 sm:py-4" style="background: repeating-linear-gradient(45deg, #FAF8F5, #FAF8F5 10px, #EFECE6 10px, #EFECE6 20px);">
                                <input type="text" id="tampilan_divisi" readonly placeholder="Pilih pengurus terlebih dahulu..." value="<?= $divisi_terpilih ?>"
                                       class="w-full bg-transparent text-xs sm:text-base font-mono font-bold text-black focus:outline-none placeholder-slate-400">
                                <div class="text-slate-600 pl-3">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                        <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
                                        <path d="M7 11V7a5 5 0 0110 0v4"></path>
                                    </svg>
                                </div>
                            </div>
                        </div>

                        <div>
                            <div class="flex items-center justify-between mb-2">
                                <label for="tanggal" class="text-xs sm:text-base font-mono font-bold uppercase tracking-wider text-black">
                                    TANGGAL PIKET <span class="text-[#E84125]">*</span>
                                </label>
                                <span class="text-xs sm:text-sm font-mono text-slate-500 font-bold">MM/DD/YYYY</span>
                            </div>
                            <input type="date" name="tanggal" id="tanggal" required value="<?= isset($tanggal) ? $tanggal : date('Y-m-d') ?>"
                                   class="w-full bg-white border-2 border-black px-5 py-3.5 sm:py-4 text-xs sm:text-base font-mono font-bold text-black neo-shadow-sm focus:outline-none">
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-5 sm:gap-6">
                            <div>
                                <div class="flex items-center justify-between mb-2">
                                    <label for="jam_mulai" class="text-xs sm:text-base font-mono font-bold uppercase tracking-wider text-black">
                                        JAM MULAI <span class="text-[#E84125]">*</span>
                                    </label>
                                    <span class="bg-white border border-black px-2 py-0.5 text-xs font-mono font-bold text-black uppercase">
                                        WIB
                                    </span>
                                </div>
                                <input type="time" name="jam_mulai" id="jam_mulai" required value="<?= isset($jam_mulai) ? $jam_mulai : '15:00' ?>"
                                       class="w-full bg-white border-2 border-black px-5 py-3.5 sm:py-4 text-xs sm:text-base font-mono font-bold text-black neo-shadow-sm focus:outline-none">
                            </div>
                            <div>
                                <div class="flex items-center justify-between mb-2">
                                    <label for="jam_selesai" class="text-xs sm:text-base font-mono font-bold uppercase tracking-wider text-black">
                                        JAM SELESAI <span class="text-[#E84125]">*</span>
                                    </label>
                                    <span class="bg-white border border-black px-2 py-0.5 text-xs font-mono font-bold text-black uppercase">
                                        WIB
                                    </span>
                                </div>
                                <input type="time" name="jam_selesai" id="jam_selesai" required value="<?= isset($jam_selesai) ? $jam_selesai : '20:00' ?>"
                                       class="w-full bg-white border-2 border-black px-5 py-3.5 sm:py-4 text-xs sm:text-base font-mono font-bold text-black neo-shadow-sm focus:outline-none">
                            </div>
                        </div>

                        <div>
                            <div class="flex items-center justify-between mb-2">
                                <label class="text-xs sm:text-base font-mono font-bold uppercase tracking-wider text-black">
                                    UPLOAD FOTO DOKUMENTASI <span class="text-[#E84125]">*</span>
                                </label>
                                <span class="text-xs sm:text-sm font-mono text-slate-500 font-bold">MAKS. 2 MB</span>
                            </div>

                            <input type="file" name="foto_bukti" id="foto_bukti" required accept="image/jpeg,image/png,image/webp" class="hidden" onchange="perbaruiPratinjauFile(this)">

                            <div id="dropzoneFoto" onclick="document.getElementById('foto_bukti').click()"
                                 class="border-2 border-dashed border-black bg-[#FAF8F5] p-6 sm:p-8 lg:p-10 text-center flex flex-col items-center justify-center cursor-pointer hover:bg-slate-100 transition">
                                <div class="w-12 h-12 sm:w-14 sm:h-14 bg-[#B8E926] border-2 border-black flex items-center justify-center mb-3 neo-shadow-sm">
                                    <svg class="w-6 h-6 sm:w-7 sm:h-7 text-black" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
                                        <rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect>
                                        <circle cx="8.5" cy="8.5" r="1.5"></circle>
                                        <polyline points="21 15 16 10 5 21"></polyline>
                                    </svg>
                                </div>
                                <div class="flex flex-wrap items-center justify-center gap-2.5 mb-2">
                                    <button type="button" class="px-4 py-2 bg-black text-white border-2 border-black font-mono font-bold text-xs sm:text-sm uppercase neo-shadow-sm pointer-events-none">
                                        PILIH FILE FOTO
                                    </button>
                                    <span class="font-mono text-xs sm:text-sm font-bold text-slate-700 uppercase">
                                        ATAU GESER KE SINI
                                    </span>
                                </div>
                                <p class="text-xs font-mono text-slate-500">
                                    Format: <strong class="text-black">JPG, PNG, WEBP</strong> (Pastikan foto jelas)
                                </p>
                            </div>

                            <div id="statusFileBox" class="border-2 border-black bg-white p-3.5 flex items-center justify-between mt-3 neo-shadow-sm">
                                <div class="flex items-center gap-2.5 min-w-0">
                                    <span id="dotStatusFile" class="w-3 h-3 rounded-full bg-slate-300 inline-block shrink-0"></span>
                                    <span id="namaFilePilihan" class="font-mono font-bold text-xs sm:text-sm text-slate-700 truncate">
                                        Belum ada file dipilih
                                    </span>
                                    <span id="ukuranFilePilihan" class="font-mono text-xs text-slate-500 shrink-0"></span>
                                </div>
                                <span id="badgeStatusFile" class="bg-slate-100 text-slate-600 border border-black px-2.5 py-0.5 text-xs font-mono font-bold uppercase shrink-0">
                                    MENUNGGU
                                </span>
                            </div>
                        </div>

                        <div>
                            <div class="flex items-center justify-between mb-2">
                                <div class="flex items-center gap-2.5">
                                    <label for="catatan" class="text-xs sm:text-base font-mono font-bold uppercase tracking-wider text-black">
                                        CATATAN KEGIATAN
                                    </label>
                                    <span class="bg-white border border-black px-2 py-0.5 text-xs font-mono font-bold text-slate-700 uppercase">
                                        OPSIONAL
                                    </span>
                                </div>
                                <span class="text-xs sm:text-sm font-mono text-slate-500 font-bold">Maks. 500 Karakter</span>
                            </div>
                            <textarea name="catatan" id="catatan" rows="3" maxlength="500" placeholder="Contoh: Merapikan meja &amp; menyapu lantai utama, membuang sampah..."
                                      class="w-full bg-white border-2 border-black p-4 text-xs sm:text-base font-mono font-bold text-black focus:outline-none neo-shadow-sm"><?= isset($catatan) ? $catatan : '' ?></textarea>
                        </div>

                        <label class="border-2 border-black p-4 sm:p-5 bg-white flex items-start gap-3.5 neo-shadow-sm cursor-pointer hover:bg-slate-50 transition">
                            <input type="checkbox" name="konfirmasi" id="konfirmasi" required
                                   class="w-5 h-5 border-2 border-black text-[#164E33] focus:ring-0 rounded-none shrink-0 mt-0.5 cursor-pointer accent-[#164E33]">
                            <span class="font-mono text-xs sm:text-sm text-slate-800 leading-relaxed select-none">
                                Saya menyatakan dengan sungguh-sungguh bahwa data presensi dan dokumentasi yang dilampirkan adalah benar adanya, sah, serta diambil pada hari dan jam pelaksanaan piket.
                            </span>
                        </label>

                        <div class="pt-1">
                            <button type="submit" class="w-full py-4 px-6 bg-[#164E33] hover:bg-[#0E3522] border-2 border-black text-white font-mono font-black text-sm sm:text-lg uppercase tracking-wider flex items-center justify-center gap-2.5 neo-shadow neo-btn transition">
                                <span>KIRIM LAPORAN PIKET</span>
                                <svg class="w-5 h-5 text-white stroke-[2.5]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <line x1="5" y1="12" x2="19" y2="12"></line>
                                    <polyline points="12 5 19 12 12 19"></polyline>
                                </svg>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function toggleDropdownMember() {
    var menu = document.getElementById('menu_member_id');
    var arrow = document.getElementById('arrow_member_id');
    if (menu.classList.contains('hidden')) {
        menu.classList.remove('hidden');
        arrow.classList.add('rotate-180');
    } else {
        menu.classList.add('hidden');
        arrow.classList.remove('rotate-180');
    }
}

function tutupDropdownMember() {
    var menu = document.getElementById('menu_member_id');
    var arrow = document.getElementById('arrow_member_id');
    if (menu) menu.classList.add('hidden');
    if (arrow) arrow.classList.remove('rotate-180');
}

function pilihMember(id, divisi, nama) {
    document.getElementById('member_id').value = id;
    document.getElementById('label_member_id').textContent = nama;
    document.getElementById('tampilan_divisi').value = divisi;
    
    var trigger = document.getElementById('trigger_member_id');
    trigger.classList.remove('border-[#E84125]', 'bg-rose-50');

    document.querySelectorAll('#menu_member_id .neo-dropdown-item').forEach(function(item) {
        item.setAttribute('data-selected', 'false');
        var chk = item.querySelector('.text-xs');
        if (chk) chk.remove();
    });

    if (event && event.currentTarget && id !== '') {
        event.currentTarget.setAttribute('data-selected', 'true');
        var chk = document.createElement('span');
        chk.className = 'text-xs font-mono shrink-0 ml-2';
        chk.textContent = '✓';
        event.currentTarget.appendChild(chk);
    }

    tutupDropdownMember();
}

function validasiFormPresensi(e) {
    var memberId = document.getElementById('member_id').value;
    if (!memberId || memberId === '0') {
        e.preventDefault();
        var trigger = document.getElementById('trigger_member_id');
        trigger.classList.add('border-[#E84125]', 'bg-rose-50');
        trigger.scrollIntoView({ behavior: 'smooth', block: 'center' });
        var menu = document.getElementById('menu_member_id');
        var arrow = document.getElementById('arrow_member_id');
        menu.classList.remove('hidden');
        arrow.classList.add('rotate-180');
        return false;
    }
    return true;
}

document.addEventListener('click', function(e) {
    var wrapper = document.getElementById('wrapper_member_id');
    if (wrapper && !wrapper.contains(e.target)) {
        tutupDropdownMember();
    }
});

function perbaruiPratinjauFile(input) {
    var dot = document.getElementById('dotStatusFile');
    var badge = document.getElementById('badgeStatusFile');
    var nama = document.getElementById('namaFilePilihan');
    var ukuran = document.getElementById('ukuranFilePilihan');

    if (input.files && input.files[0]) {
        var file = input.files[0];
        var sizeInMB = (file.size / (1024 * 1024)).toFixed(2);
        var sizeStr = sizeInMB >= 1 ? sizeInMB + ' MB' : (file.size / 1024).toFixed(0) + ' KB';

        if (nama) nama.textContent = file.name;
        if (ukuran) ukuran.textContent = '(' + sizeStr + ')';

        if (dot) dot.classList.add('hidden');
        if (badge) {
            badge.className = 'bg-[#B8E926] text-black border border-black px-2.5 py-0.5 text-xs font-mono font-bold uppercase shrink-0';
            badge.textContent = 'SIAP UNGGAH';
        }
    } else {
        if (nama) nama.textContent = 'Belum ada file dipilih';
        if (ukuran) ukuran.textContent = '';
        if (dot) {
            dot.className = 'w-3 h-3 rounded-full bg-slate-300 inline-block shrink-0';
            dot.classList.remove('hidden');
        }
        if (badge) {
            badge.className = 'bg-slate-100 text-slate-600 border border-black px-2.5 py-0.5 text-xs font-mono font-bold uppercase shrink-0';
            badge.textContent = 'MENUNGGU';
        }
    }
}

var dropzone = document.getElementById('dropzoneFoto');
var fileInput = document.getElementById('foto_bukti');

['dragenter', 'dragover'].forEach(function(eventName) {
    dropzone.addEventListener(eventName, function(e) {
        e.preventDefault();
        e.stopPropagation();
        dropzone.classList.add('bg-emerald-50');
    }, false);
});

['dragleave', 'drop'].forEach(function(eventName) {
    dropzone.addEventListener(eventName, function(e) {
        e.preventDefault();
        e.stopPropagation();
        dropzone.classList.remove('bg-emerald-50');
    }, false);
});

dropzone.addEventListener('drop', function(e) {
    var dt = e.dataTransfer;
    if (dt && dt.files && dt.files.length) {
        fileInput.files = dt.files;
        perbaruiPratinjauFile(fileInput);
    }
}, false);

function perbaruiLiveFeed() {
    fetch('presensi.php?action=get_feed')
        .then(function(res) { return res.json(); })
        .then(function(data) {
            var container = document.getElementById('feedListContainer');
            if (!container) return;
            if (!data || data.length === 0) {
                container.innerHTML = '<div class="p-8 text-center text-xs font-mono text-slate-400">Belum ada pengurus yang mengisi presensi piket hari ini.</div>';
                return;
            }
            var html = '';
            data.forEach(function(item) {
                var isV = (item.status_verifikasi === 'valid');
                var jam = item.jam_mulai ? item.jam_mulai.substring(0, 5) + ' WIB' : '';
                var badge = isV 
                    ? '<span class="border border-black px-2 py-0.5 text-[10px] sm:text-[11px] font-mono font-bold uppercase tracking-wider bg-[#DCFCE7] text-[#164E33] shrink-0">TERVERIFIKASI</span>'
                    : '<span class="border border-black px-2 py-0.5 text-[10px] sm:text-[11px] font-mono font-bold uppercase tracking-wider bg-[#FEF3C7] text-[#92400E] shrink-0">MENUNGGU REVIEW</span>';
                
                var namaSafe = document.createElement('div');
                namaSafe.textContent = item.nama || '';
                var divisiSafe = document.createElement('div');
                divisiSafe.textContent = item.nama_divisi || '';

                html += '<div class="p-4 sm:p-5 flex flex-col gap-1.5 hover:bg-slate-50 transition border-b border-slate-100 last:border-b-0">' +
                            '<div class="flex items-center justify-between gap-2">' +
                                '<span class="font-bold text-black text-xs sm:text-sm truncate">' + namaSafe.innerHTML + '</span>' +
                                '<span class="text-xs font-mono text-slate-500 shrink-0">' + jam + '</span>' +
                            '</div>' +
                            '<div class="flex items-center justify-between gap-2">' +
                                '<span class="text-xs font-mono text-slate-500 truncate">' + divisiSafe.innerHTML + '</span>' +
                                badge +
                            '</div>' +
                        '</div>';
            });
            container.innerHTML = html;
        })
        .catch(function(err) {});
}

function toggleFeedMobile() {
    if (window.innerWidth >= 1024) return;
    var container = document.getElementById('feedListContainer');
    var arrow = document.getElementById('feedToggleArrow');
    var header = document.getElementById('feedHeader');
    if (!container || !arrow) return;
    if (container.classList.contains('hidden')) {
        container.classList.remove('hidden');
        arrow.classList.add('rotate-180');
        if (header) {
            header.classList.remove('border-b-0');
            header.classList.add('border-b-2');
        }
    } else {
        container.classList.add('hidden');
        arrow.classList.remove('rotate-180');
        if (header) {
            header.classList.remove('border-b-2');
            header.classList.add('border-b-0');
        }
    }
}

setInterval(perbaruiLiveFeed, 4000);
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
