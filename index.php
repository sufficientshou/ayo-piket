<?php
require_once __DIR__ . '/config/database.php';
$judul_halaman = "Jadwal & Presensi Piket";
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

$daftar_nama_bulan = [
    1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
    5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
    9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
];

function dapatkan_inisial($nama) {
    $nama = trim($nama);
    $pecah = preg_split('/\s+/', $nama);
    if (count($pecah) >= 2) {
        return strtoupper(substr($pecah[0], 0, 1) . substr($pecah[1], 0, 1));
    }
    if (stripos($nama, 'farjar') !== false) {
        return 'FJ';
    }
    return strtoupper(substr($nama, 0, 2));
}
?>

<div class="space-y-8 sm:space-y-10">
    <div class="bg-[#164E33] border-2 border-black neo-shadow-lg relative overflow-hidden min-h-[460px] sm:min-h-[520px] lg:min-h-[580px] p-8 sm:p-14 lg:p-20 flex flex-col justify-center">
        <svg class="absolute bottom-0 right-0 w-48 sm:w-80 lg:w-[420px] h-72 sm:h-[440px] lg:h-[580px] pointer-events-none z-0" viewBox="0 0 200 250" fill="none" preserveAspectRatio="none">
            <polygon points="45,250 200,250 200,45 70,0" fill="#E84125" stroke="#000000" stroke-width="3" />
        </svg>

        <div class="max-w-4xl relative z-20 space-y-5 sm:space-y-6">
            <div class="flex flex-wrap items-center gap-2.5 sm:gap-3">
                <span class="bg-white text-black border-2 border-black px-3.5 py-1.5 text-xs sm:text-sm font-mono font-bold uppercase tracking-wider shadow-[2px_2px_0_#000]">
                    PIKET HIMTIKA UNSIKA
                </span>
                <span class="bg-[#E84125] text-white border-2 border-black px-3.5 py-1.5 text-xs sm:text-sm font-mono font-bold uppercase tracking-wider shadow-[2px_2px_0_#000]">
                    KABINET SELARAS
                </span>
            </div>

            <h1 class="text-4xl sm:text-6xl lg:text-7xl xl:text-[80px] font-black text-white uppercase tracking-tight leading-[1.05]">
                JADWAL &amp; PRESENSI PIKET
            </h1>

            <div class="border-l-4 border-white pl-5 sm:pl-6">
                <p class="text-white text-sm sm:text-lg lg:text-xl leading-relaxed max-w-2xl sm:max-w-3xl">
                    Jadwal piket rutin dilaksanakan setiap hari <span class="underline decoration-[#E84125] decoration-2 underline-offset-4 font-bold text-white">Senin &amp; Kamis</span>. Jangan lupa untuk mengisi laporan presensi serta melampirkan dokumentasi setelah selesai bertugas.
                </p>
            </div>

            <div class="pt-3 sm:pt-4 flex flex-wrap items-center gap-4 sm:gap-5">
                <a href="presensi.php" class="inline-flex items-stretch bg-white border-2 border-black shadow-[3px_3px_0_#000] hover:translate-x-[1px] hover:translate-y-[1px] transition">
                    <span class="px-5 sm:px-6 py-3 sm:py-3.5 font-mono font-bold text-xs sm:text-sm uppercase tracking-wider text-black flex items-center">
                        LAPOR PIKET SEKARANG
                    </span>
                    <span class="bg-black text-white px-3 sm:px-3.5 flex items-center justify-center border-l-2 border-black">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
                    </span>
                </a>
                <a href="export_jadwal.php?bulan=<?= $bulan_terpilih ?>&tahun=<?= $tahun_terpilih ?>" target="_blank"
                   class="inline-flex items-center gap-2.5 px-6 sm:px-7 py-3 sm:py-3.5 bg-[#0A2619] text-white border-2 border-black font-mono font-bold text-xs sm:text-sm uppercase tracking-wider shadow-[3px_3px_0_#000] hover:translate-x-[1px] hover:translate-y-[1px] transition">
                    <svg class="w-4 h-4 sm:w-5 sm:h-5 shrink-0" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                    <span>DOWNLOAD PDF</span>
                </a>
            </div>
        </div>
    </div>

    <div class="py-4 sm:py-8 lg:py-10">
        <div class="bg-[#FAF8F5] border-2 border-black p-5 sm:p-7 neo-shadow transition-all">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 cursor-pointer select-none" onclick="toggleRules()">
                <div>
                    <div class="flex items-center gap-2.5">
                        <span class="bg-[#E84125] text-white border-2 border-black px-2.5 py-0.5 text-xs font-mono font-bold uppercase tracking-wider">
                            RULES PIKET
                        </span>
                        <span class="text-xs font-mono font-bold text-slate-700 bg-white border border-black px-2.5 py-0.5">
                            15.00 – 20.00 WIB
                        </span>
                    </div>
                    <h3 class="text-xl sm:text-2xl font-black uppercase text-black mt-2">
                        RULES PIKET SEKRE HIMTIKA
                    </h3>
                    <p class="text-xs sm:text-sm text-slate-600 font-mono mt-1">
                        Piket rutin wajib dilaksanakan sesuai jadwal dan tata tertib. Klik untuk melihat detail aturan.
                    </p>
                </div>
                <button type="button" class="flex items-center gap-2 px-4 py-2 bg-white border-2 border-black text-black font-mono font-bold text-xs uppercase tracking-wider neo-shadow-sm neo-btn transition shrink-0 self-start sm:self-center">
                    <span id="rulesToggleText">SELENGKAPNYA</span>
                    <svg id="rulesToggleIcon" class="w-4 h-4 transform transition-transform duration-200" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                    </svg>
                </button>
            </div>

            <div id="rulesContent" class="hidden border-t-2 border-black pt-6 mt-5">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-3.5 sm:gap-4">
                    <div class="p-3.5 sm:p-4 bg-white border-2 border-black flex gap-3 neo-shadow-sm">
                        <span class="w-7 h-7 bg-black text-white font-mono font-bold text-xs flex items-center justify-center shrink-0 border border-black">01</span>
                        <p class="text-xs sm:text-sm font-mono text-slate-800 leading-relaxed">
                            Piket dilaksanakan sesuai jadwal yang telah ditentukan, dengan waktu pelaksanaan <strong class="text-black">15.00–20.00 WIB</strong>.
                        </p>
                    </div>
                    <div class="p-3.5 sm:p-4 bg-white border-2 border-black flex gap-3 neo-shadow-sm">
                        <span class="w-7 h-7 bg-black text-white font-mono font-bold text-xs flex items-center justify-center shrink-0 border border-black">02</span>
                        <p class="text-xs sm:text-sm font-mono text-slate-800 leading-relaxed">
                            Pengurus yang terjadwal bertanggung jawab untuk melaksanakan piket pada hari tersebut.
                        </p>
                    </div>
                    <div class="p-3.5 sm:p-4 bg-white border-2 border-black flex gap-3 neo-shadow-sm">
                        <span class="w-7 h-7 bg-black text-white font-mono font-bold text-xs flex items-center justify-center shrink-0 border border-black">03</span>
                        <p class="text-xs sm:text-sm font-mono text-slate-800 leading-relaxed">
                            Pelaksanaan dan pembagian tugas bersifat fleksibel dan disepakati bersama oleh pengurus yang bertugas.
                        </p>
                    </div>
                    <div class="p-3.5 sm:p-4 bg-white border-2 border-black flex gap-3 neo-shadow-sm">
                        <span class="w-7 h-7 bg-black text-white font-mono font-bold text-xs flex items-center justify-center shrink-0 border border-black">04</span>
                        <p class="text-xs sm:text-sm font-mono text-slate-800 leading-relaxed">
                            Tugas piket meliputi menyapu, mengepel, dan membersihkan Sekre HIMTIKA.
                        </p>
                    </div>
                    <div class="p-3.5 sm:p-4 bg-white border-2 border-black flex gap-3 neo-shadow-sm">
                        <span class="w-7 h-7 bg-black text-white font-mono font-bold text-xs flex items-center justify-center shrink-0 border border-black">05</span>
                        <p class="text-xs sm:text-sm font-mono text-slate-800 leading-relaxed">
                            Setelah piket selesai, pastikan Sekre HIMTIKA dalam keadaan bersih dan rapi.
                        </p>
                    </div>
                    <div class="p-3.5 sm:p-4 bg-white border-2 border-black flex gap-3 neo-shadow-sm">
                        <span class="w-7 h-7 bg-black text-white font-mono font-bold text-xs flex items-center justify-center shrink-0 border border-black">06</span>
                        <p class="text-xs sm:text-sm font-mono text-slate-800 leading-relaxed">
                            Setiap pengurus yang melaksanakan piket wajib melakukan pendataan melalui Google Form dan mengunggah bukti dokumentasi.
                        </p>
                    </div>
                    <div class="p-3.5 sm:p-4 bg-white border-2 border-black flex gap-3 neo-shadow-sm md:col-span-2">
                        <span class="w-7 h-7 bg-[#E84125] text-white font-mono font-bold text-xs flex items-center justify-center shrink-0 border border-black">07</span>
                        <p class="text-xs sm:text-sm font-mono text-slate-800 leading-relaxed">
                            Jika berhalangan hadir, pengurus wajib mengonfirmasi terlebih dahulu kepada <strong class="text-black">HIMTIKA Care</strong> dan mencari pengurus yang bersedia untuk menggantikan pada jadwal piket lain yang telah ditentukan. Untuk kendala yang sudah diketahui sebelumnya, konfirmasi dilakukan paling lambat <strong class="text-black">H-1</strong>. Pergantian jadwal hanya dilakukan jika memang ada keperluan yang tidak dapat ditinggalkan.
                        </p>
                    </div>
                    <div class="p-3.5 sm:p-4 bg-rose-50 border-2 border-black flex gap-3 neo-shadow-sm md:col-span-2">
                        <span class="w-7 h-7 bg-black text-white font-mono font-bold text-xs flex items-center justify-center shrink-0 border border-black">08</span>
                        <p class="text-xs sm:text-sm font-mono text-rose-950 font-bold leading-relaxed">
                            Bagi pengurus yang tidak melaksanakan piket dan tidak melakukan konfirmasi kepada HIMTIKA Care akan dikenakan punishment.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="space-y-4 sm:space-y-6">
        <div class="bg-[#FAF8F5] border-2 border-black p-5 sm:p-7 neo-shadow flex flex-col md:flex-row md:items-center justify-between gap-5">
        <div>
            <div class="flex items-center gap-3">
                <span class="w-4 h-4 bg-[#E84125] border-2 border-black inline-block shrink-0"></span>
                <h2 class="text-lg sm:text-2xl font-black uppercase tracking-tight text-black">
                    DAFTAR JADWAL BULAN <?= strtoupper($daftar_nama_bulan[$bulan_terpilih]) ?> <?= $tahun_terpilih ?>
                </h2>
            </div>
            <p class="text-xs sm:text-sm text-slate-700 font-mono mt-1">
                Menampilkan seluruh jadwal piket rutin hari <span class="font-bold text-black">Senin</span> &amp; <span class="font-bold text-black">Kamis</span>
            </p>
        </div>

        <form action="index.php" method="GET" class="flex flex-wrap items-center gap-3">
            <div class="relative" id="wrapper_bulan">
                <input type="hidden" name="bulan" id="input_bulan" value="<?= $bulan_terpilih ?>">
                <button type="button" onclick="toggleDropdownIndex('bulan')" id="trigger_bulan"
                        class="bg-white border-2 border-black px-4 py-2 sm:py-2.5 pr-9 font-mono font-bold text-xs sm:text-sm uppercase tracking-wider text-black neo-shadow-sm flex items-center justify-between gap-2 cursor-pointer focus:outline-none text-left">
                    <span id="label_bulan"><?= strtoupper($daftar_nama_bulan[$bulan_terpilih] ?? 'PILIH BULAN') ?></span>
                    <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2.5 text-black">
                        <svg id="arrow_bulan" class="w-3.5 h-3.5 fill-current transition-transform duration-150" viewBox="0 0 20 20"><path d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"/></svg>
                    </div>
                </button>
                <div id="menu_bulan" class="neo-dropdown-menu absolute left-0 min-w-full top-full mt-1.5 bg-white border-2 border-black neo-shadow-lg max-h-60 overflow-y-auto z-50 hidden py-1">
                    <?php foreach ($daftar_nama_bulan as $b_num => $b_nama): ?>
                        <?php $is_b_sel = ($bulan_terpilih == $b_num); ?>
                        <div class="neo-dropdown-item px-4 py-2 text-xs sm:text-sm font-mono font-bold uppercase tracking-wider cursor-pointer flex items-center justify-between border-b border-slate-100 last:border-b-0"
                             data-selected="<?= $is_b_sel ? 'true' : 'false' ?>"
                             onclick="pilihOpsiIndex('bulan', '<?= $b_num ?>', '<?= strtoupper($b_nama) ?>')">
                            <span><?= strtoupper($b_nama) ?></span>
                            <?php if ($is_b_sel): ?><span class="text-xs font-mono ml-2">✓</span><?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="relative" id="wrapper_tahun">
                <input type="hidden" name="tahun" id="input_tahun" value="<?= $tahun_terpilih ?>">
                <button type="button" onclick="toggleDropdownIndex('tahun')" id="trigger_tahun"
                        class="bg-white border-2 border-black px-4 py-2 sm:py-2.5 pr-9 font-mono font-bold text-xs sm:text-sm uppercase tracking-wider text-black neo-shadow-sm flex items-center justify-between gap-2 cursor-pointer focus:outline-none text-left">
                    <span id="label_tahun"><?= $tahun_terpilih ?></span>
                    <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2.5 text-black">
                        <svg id="arrow_tahun" class="w-3.5 h-3.5 fill-current transition-transform duration-150" viewBox="0 0 20 20"><path d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"/></svg>
                    </div>
                </button>
                <div id="menu_tahun" class="neo-dropdown-menu absolute left-0 min-w-full top-full mt-1.5 bg-white border-2 border-black neo-shadow-lg max-h-60 overflow-y-auto z-50 hidden py-1">
                    <?php for ($th = 2025; $th <= 2028; $th++): ?>
                        <?php $is_th_sel = ($tahun_terpilih == $th); ?>
                        <div class="neo-dropdown-item px-4 py-2 text-xs sm:text-sm font-mono font-bold uppercase tracking-wider cursor-pointer flex items-center justify-between border-b border-slate-100 last:border-b-0"
                             data-selected="<?= $is_th_sel ? 'true' : 'false' ?>"
                             onclick="pilihOpsiIndex('tahun', '<?= $th ?>', '<?= $th ?>')">
                            <span><?= $th ?></span>
                            <?php if ($is_th_sel): ?><span class="text-xs font-mono ml-2">✓</span><?php endif; ?>
                        </div>
                    <?php endfor; ?>
                </div>
            </div>

            <button type="submit" class="px-5 sm:px-6 py-2 sm:py-2.5 bg-[#164E33] border-2 border-black text-white font-mono font-bold text-xs sm:text-sm uppercase tracking-wider neo-shadow-sm neo-btn transition">
                PILIH
            </button>
        </form>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
        <?php foreach ($jadwal_per_tanggal as $tgl => $info): ?>
            <?php 
                $ada_anggota = !empty($info['anggota']);
                $is_kamis = ($info['hari'] === 'Kamis');
            ?>
            <div class="bg-[#FAF8F5] border-2 border-black p-5 sm:p-6 neo-shadow flex flex-col justify-between min-h-[240px] sm:min-h-[270px]">
                <div>
                    <div class="flex items-center justify-between">
                        <?php if ($is_kamis): ?>
                            <span class="bg-[#E84125] text-white border-2 border-black px-3 py-1 text-xs font-mono font-bold uppercase tracking-wider">
                                KAMIS
                            </span>
                        <?php else: ?>
                            <span class="bg-black text-white border-2 border-black px-3 py-1 text-xs font-mono font-bold uppercase tracking-wider">
                                SENIN
                            </span>
                        <?php endif; ?>

                        <?php if ($ada_anggota): ?>
                            <span class="bg-[#164E33] text-white border-2 border-black px-3 py-1 text-xs font-mono font-bold">
                                <?= count($info['anggota']) ?> Orang
                            </span>
                        <?php else: ?>
                            <span class="bg-white text-black border-2 border-black px-3 py-1 text-xs font-mono font-bold">
                                0 Orang
                            </span>
                        <?php endif; ?>
                    </div>

                    <h3 class="text-base sm:text-lg font-bold text-black mt-4 mb-4">
                        <?= format_tanggal_indo($tgl) ?>
                    </h3>
                </div>

                <div class="flex-grow flex flex-col justify-center">
                    <?php if ($ada_anggota): ?>
                        <div class="space-y-3">
                            <?php foreach ($info['anggota'] as $pj): ?>
                                <div class="border-2 border-black bg-white p-3 sm:p-3.5">
                                    <div class="flex items-center gap-3">
                                        <div class="w-9 h-9 sm:w-10 sm:h-10 bg-black text-white border-2 border-black flex items-center justify-center font-mono font-bold text-xs sm:text-sm shrink-0">
                                            <?= dapatkan_inisial($pj['nama']) ?>
                                        </div>
                                        <div class="overflow-hidden min-w-0">
                                            <div class="font-bold text-xs sm:text-sm text-black truncate"><?= sanitize($pj['nama']) ?></div>
                                            <div class="text-[11px] sm:text-xs text-slate-600 truncate"><?= sanitize($pj['nama_divisi']) ?></div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="border-2 border-dashed border-slate-400 p-5 sm:p-6 text-center my-auto flex flex-col items-center justify-center min-h-[110px]">
                            <div class="text-xs sm:text-sm font-mono italic text-slate-500">
                                Jadwal belum dipublikasi
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
    </div>
</div>

<script>
    function toggleRules() {
        const content = document.getElementById('rulesContent');
        const icon = document.getElementById('rulesToggleIcon');
        const text = document.getElementById('rulesToggleText');
        if (content.classList.contains('hidden')) {
            content.classList.remove('hidden');
            icon.classList.add('rotate-180');
            text.textContent = 'TUTUP';
        } else {
            content.classList.add('hidden');
            icon.classList.remove('rotate-180');
            text.textContent = 'SELENGKAPNYA';
        }
    }

    function toggleDropdownIndex(tipe) {
        var menu = document.getElementById('menu_' + tipe);
        var arrow = document.getElementById('arrow_' + tipe);
        var otherTipe = tipe === 'bulan' ? 'tahun' : 'bulan';
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

    function pilihOpsiIndex(tipe, val, label) {
        document.getElementById('input_' + tipe).value = val;
        document.getElementById('label_' + tipe).textContent = label;
        var menu = document.getElementById('menu_' + tipe);
        var arrow = document.getElementById('arrow_' + tipe);
        if (menu) menu.classList.add('hidden');
        if (arrow) arrow.classList.remove('rotate-180');

        document.querySelectorAll('#menu_' + tipe + ' .neo-dropdown-item').forEach(function(item) {
            item.setAttribute('data-selected', 'false');
            var chk = item.querySelector('.text-xs');
            if (chk) chk.remove();
        });
        if (event && event.currentTarget) {
            event.currentTarget.setAttribute('data-selected', 'true');
            var chk = document.createElement('span');
            chk.className = 'text-xs font-mono ml-2';
            chk.textContent = '✓';
            event.currentTarget.appendChild(chk);
        }
    }

    document.addEventListener('click', function(e) {
        var wBulan = document.getElementById('wrapper_bulan');
        var wTahun = document.getElementById('wrapper_tahun');
        if (wBulan && !wBulan.contains(e.target)) {
            var mB = document.getElementById('menu_bulan');
            var aB = document.getElementById('arrow_bulan');
            if (mB) mB.classList.add('hidden');
            if (aB) aB.classList.remove('rotate-180');
        }
        if (wTahun && !wTahun.contains(e.target)) {
            var mT = document.getElementById('menu_tahun');
            var aT = document.getElementById('arrow_tahun');
            if (mT) mT.classList.add('hidden');
            if (aT) aT.classList.remove('rotate-180');
        }
    });
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
