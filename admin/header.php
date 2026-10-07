<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../includes/auth_check.php';
require_once __DIR__ . '/../includes/functions.php';
$flash = get_flash_message();
$halaman_saat_ini = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($judul_halaman) ? $judul_halaman . ' - Himpunan Mahasiswa Informatika Unsika' : 'Atmint - Himpunan Mahasiswa Informatika Unsika' ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800;900&family=Space+Mono:ital,wght@0,400;0,700;1,400&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: #F4F0EA;
        }
        .font-mono {
            font-family: 'Space Mono', monospace;
        }
        .neo-shadow {
            box-shadow: 3px 3px 0px #000000;
        }
        .neo-shadow-sm {
            box-shadow: 2px 2px 0px #000000;
        }
        .neo-shadow-lg {
            box-shadow: 4px 4px 0px #000000;
        }
        .neo-btn:hover {
            transform: translate(1px, 1px);
            box-shadow: 1px 1px 0px #000000;
        }
        .neo-btn:active {
            transform: translate(2px, 2px);
            box-shadow: 0px 0px 0px #000000;
        }
        .neo-dropdown-item {
            background-color: #ffffff;
            color: #000000;
        }
        .neo-dropdown-item:hover,
        .neo-dropdown-item.is-hovered {
            background-color: #164E33 !important;
            color: #ffffff !important;
        }
        .neo-dropdown-item[data-selected="true"] {
            background-color: #F4F0EA;
            color: #000000;
        }
        .neo-dropdown-item[data-selected="true"]:hover {
            background-color: #164E33 !important;
            color: #ffffff !important;
        }
        select option {
            background-color: #ffffff;
            color: #000000;
        }
        select option:checked,
        select option:hover,
        select option:focus {
            background-color: #164E33 !important;
            color: #ffffff !important;
        }
    </style>
</head>
<body class="bg-[#F4F0EA] text-black min-h-screen flex flex-col selection:bg-[#ccff00] selection:text-black">
    <header class="bg-white border-b-2 border-black sticky top-0 z-50 relative">
        <div class="max-w-[1720px] mx-auto px-4 sm:px-8 lg:px-12">
            <div class="flex justify-between items-center h-20">
                <a href="index.php" class="flex items-center gap-3.5 group">
                    <div class="w-12 h-12 bg-[#FAF8F5] border-2 border-black flex items-center justify-center shrink-0 p-1 neo-shadow-sm group-hover:translate-x-[1px] group-hover:translate-y-[1px] transition">
                        <img src="../assets/images/logo.png" alt="Logo Ayo Piket" class="w-full h-full object-contain">
                    </div>
                    <div class="flex flex-col">
                        <div class="flex items-center gap-2">
                            <span class="font-black text-xl sm:text-2xl tracking-tight text-black leading-none">AYO PIKET</span>
                            <span class="bg-[#164E33] text-white text-[10px] font-mono font-bold px-1.5 py-0.5 border border-black uppercase tracking-wider">
                                ADMIN
                            </span>
                        </div>
                        <span class="font-mono text-[10px] sm:text-xs tracking-widest text-slate-700 font-bold uppercase mt-1 leading-none">KHUSUS ATMIN</span>
                    </div>
                </a>

                <div class="hidden xl:flex items-center gap-2.5 sm:gap-3">
                    <a href="index.php" class="px-3.5 py-2 <?= $halaman_saat_ini === 'index.php' ? 'bg-[#164E33] text-white' : 'bg-white text-black' ?> border-2 border-black font-mono font-bold text-xs uppercase tracking-wider neo-shadow-sm neo-btn transition">
                        REKAP PRESENSI
                    </a>
                    <a href="schedule.php" class="px-3.5 py-2 <?= $halaman_saat_ini === 'schedule.php' ? 'bg-[#164E33] text-white' : 'bg-white text-black' ?> border-2 border-black font-mono font-bold text-xs uppercase tracking-wider neo-shadow-sm neo-btn transition">
                        JADWAL &amp; GACHA
                    </a>
                    <a href="members.php" class="px-3.5 py-2 <?= $halaman_saat_ini === 'members.php' ? 'bg-[#164E33] text-white' : 'bg-white text-black' ?> border-2 border-black font-mono font-bold text-xs uppercase tracking-wider neo-shadow-sm neo-btn transition">
                        DATA PENGURUS
                    </a>
                    <a href="divisions.php" class="px-3.5 py-2 <?= $halaman_saat_ini === 'divisions.php' ? 'bg-[#164E33] text-white' : 'bg-white text-black' ?> border-2 border-black font-mono font-bold text-xs uppercase tracking-wider neo-shadow-sm neo-btn transition">
                        DATA DIVISI
                    </a>
                    <a href="../index.php" target="_blank" class="px-3.5 py-2 bg-white border-2 border-black text-black font-mono font-bold text-xs uppercase tracking-wider flex items-center gap-1.5 neo-shadow-sm neo-btn transition">
                        <span>LIHAT WEB</span>
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" /></svg>
                    </a>
                    <a href="logout.php" onclick="bukaKonfirmasi({ href: this.href, pesan: 'Sesi akun admin Anda akan diakhiri. Yakin ingin keluar dari sistem?', judul: 'LOGOUT DARI SISTEM', badge: 'LOGOUT', tombolTeks: 'YA, KELUAR' }); return false;" class="px-4 py-2 bg-[#E84125] border-2 border-black text-white font-mono font-bold text-xs uppercase tracking-wider flex items-center gap-2 neo-shadow-sm neo-btn transition">
                        <span>KELUAR</span>
                        <span class="font-bold">&rarr;</span>
                    </a>
                </div>

                <button id="menu-toggle" type="button" aria-label="Menu Navigasi" class="xl:hidden p-2.5 bg-white border-2 border-black neo-shadow-sm neo-btn flex items-center justify-center cursor-pointer">
                    <svg id="menu-icon-bars" class="w-6 h-6 text-black" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16" />
                    </svg>
                    <svg id="menu-icon-close" class="w-6 h-6 text-black hidden" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>

        <div id="mobile-menu" class="hidden xl:hidden absolute top-full left-0 w-full bg-white/95 backdrop-blur-sm border-b-2 border-black shadow-[0_4px_0px_#000] px-4 sm:px-8 py-4 space-y-2.5 z-50">
            <a href="index.php" class="block w-full text-center px-4 py-2.5 <?= $halaman_saat_ini === 'index.php' ? 'bg-[#164E33] text-white' : 'bg-white text-black' ?> border-2 border-black font-mono font-bold text-xs uppercase tracking-wider neo-shadow-sm neo-btn transition">
                REKAP PRESENSI
            </a>
            <a href="schedule.php" class="block w-full text-center px-4 py-2.5 <?= $halaman_saat_ini === 'schedule.php' ? 'bg-[#164E33] text-white' : 'bg-white text-black' ?> border-2 border-black font-mono font-bold text-xs uppercase tracking-wider neo-shadow-sm neo-btn transition">
                JADWAL &amp; GACHA
            </a>
            <a href="members.php" class="block w-full text-center px-4 py-2.5 <?= $halaman_saat_ini === 'members.php' ? 'bg-[#164E33] text-white' : 'bg-white text-black' ?> border-2 border-black font-mono font-bold text-xs uppercase tracking-wider neo-shadow-sm neo-btn transition">
                DATA PENGURUS
            </a>
            <a href="divisions.php" class="block w-full text-center px-4 py-2.5 <?= $halaman_saat_ini === 'divisions.php' ? 'bg-[#164E33] text-white' : 'bg-white text-black' ?> border-2 border-black font-mono font-bold text-xs uppercase tracking-wider neo-shadow-sm neo-btn transition">
                DATA DIVISI
            </a>
            <a href="../index.php" target="_blank" class="flex items-center justify-center gap-1.5 w-full text-center px-4 py-2.5 bg-white border-2 border-black text-black font-mono font-bold text-xs uppercase tracking-wider neo-shadow-sm neo-btn transition">
                <span>LIHAT WEB</span>
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" /></svg>
            </a>
            <a href="logout.php" onclick="bukaKonfirmasi({ href: this.href, pesan: 'Sesi akun admin Anda akan diakhiri. Yakin ingin keluar dari sistem?', judul: 'LOGOUT DARI SISTEM', badge: 'LOGOUT', tombolTeks: 'YA, KELUAR' }); return false;" class="flex items-center justify-center gap-2 w-full text-center px-4 py-2.5 bg-[#E84125] border-2 border-black text-white font-mono font-bold text-xs uppercase tracking-wider neo-shadow-sm neo-btn transition">
                <span>KELUAR</span>
                <span class="font-bold">&rarr;</span>
            </a>
        </div>
    </header>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var toggle = document.getElementById('menu-toggle');
            var menu = document.getElementById('mobile-menu');
            var bars = document.getElementById('menu-icon-bars');
            var close = document.getElementById('menu-icon-close');
            if (toggle && menu) {
                toggle.addEventListener('click', function(e) {
                    e.stopPropagation();
                    var isHidden = menu.classList.contains('hidden');
                    if (isHidden) {
                        menu.classList.remove('hidden');
                        bars.classList.add('hidden');
                        close.classList.remove('hidden');
                    } else {
                        menu.classList.add('hidden');
                        bars.classList.remove('hidden');
                        close.classList.add('hidden');
                    }
                });
                document.addEventListener('click', function(e) {
                    if (!menu.contains(e.target) && !toggle.contains(e.target)) {
                        menu.classList.add('hidden');
                        bars.classList.remove('hidden');
                        close.classList.add('hidden');
                    }
                });
            }
        });
    </script>

    <?php if ($flash): ?>
        <?php 
            $is_sukses = ($flash['tipe'] === 'sukses');
            $badge_text = $is_sukses ? 'BERHASIL' : 'PERINGATAN';
            $judul_modal = $is_sukses ? 'NOTIFIKASI SISTEM' : 'TERJADI KESALAHAN';
        ?>
        <div id="modalFlash" class="fixed inset-0 bg-black/60 z-50 flex items-center justify-center p-4">
            <div class="bg-[#FAF8F5] border-2 border-black neo-shadow-lg max-w-lg w-full overflow-hidden relative">
                <div class="h-3.5 <?= $is_sukses ? 'bg-[#164E33]' : 'bg-[#E84125]' ?> border-b-2 border-black"></div>
                <div class="p-6 sm:p-7">
                    <div class="flex items-center justify-between gap-2">
                        <span class="<?= $is_sukses ? 'bg-[#164E33]' : 'bg-[#E84125]' ?> text-white px-2.5 py-0.5 text-[10px] sm:text-[11px] font-mono font-bold uppercase tracking-wider">
                            <?= $badge_text ?>
                        </span>
                        <button type="button" onclick="document.getElementById('modalFlash').remove()" class="w-7 h-7 border-2 border-black bg-white flex items-center justify-center font-bold text-sm hover:bg-black hover:text-white transition cursor-pointer">&times;</button>
                    </div>
                    <h3 class="text-lg sm:text-xl font-black uppercase text-black tracking-tight mt-3 leading-tight">
                        <?= $judul_modal ?>
                    </h3>
                    <p class="text-xs sm:text-sm font-mono text-slate-800 mt-2 leading-relaxed">
                        <?= htmlspecialchars($flash['pesan']) ?>
                    </p>
                </div>
                <div class="px-6 py-4 bg-white border-t-2 border-black flex justify-end">
                    <button type="button" onclick="document.getElementById('modalFlash').remove()" class="px-5 py-2 bg-black hover:bg-slate-800 text-white border-2 border-black font-mono font-bold text-xs uppercase tracking-wider neo-shadow-sm neo-btn transition cursor-pointer">
                        TUTUP
                    </button>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <div id="modalKonfirmasiGlobal" class="fixed inset-0 bg-black/60 z-50 flex items-center justify-center p-4 hidden" onclick="tutupModalKonfirmasiGlobal()">
        <div class="bg-[#FAF8F5] border-2 border-black neo-shadow-lg max-w-lg w-full overflow-hidden relative" onclick="event.stopPropagation()">
            <div id="modalKonfirmasiAccent" class="h-3.5 bg-[#E84125] border-b-2 border-black"></div>
            <div class="p-6 sm:p-7">
                <div class="flex items-center justify-between gap-2">
                    <span id="modalKonfirmasiBadge" class="bg-[#E84125] text-white px-2.5 py-0.5 text-[10px] sm:text-[11px] font-mono font-bold uppercase tracking-wider">
                        KONFIRMASI
                    </span>
                    <button type="button" onclick="tutupModalKonfirmasiGlobal()" class="w-7 h-7 border-2 border-black bg-white flex items-center justify-center font-bold text-sm hover:bg-black hover:text-white transition cursor-pointer">&times;</button>
                </div>
                <h3 id="modalKonfirmasiJudul" class="text-lg sm:text-xl font-black uppercase text-black tracking-tight mt-3 leading-tight">
                    KONFIRMASI TINDAKAN
                </h3>
                <p id="modalKonfirmasiPesan" class="text-xs sm:text-sm font-mono text-slate-800 mt-2 leading-relaxed">
                    Apakah Anda yakin ingin melanjutkan tindakan ini?
                </p>
            </div>
            <div class="px-6 py-4 bg-white border-t-2 border-black flex items-center justify-end gap-3">
                <button type="button" onclick="tutupModalKonfirmasiGlobal()" class="px-4 py-2 bg-white hover:bg-zinc-100 text-black border-2 border-black font-mono font-bold text-xs uppercase tracking-wider shadow-[2px_2px_0px_#000] transition cursor-pointer">
                    BATAL
                </button>
                <button type="button" id="modalKonfirmasiBtnOk" class="px-5 py-2 bg-[#E84125] hover:bg-[#c9351d] text-white border-2 border-black font-mono font-bold text-xs uppercase tracking-wider shadow-[2px_2px_0px_#000] hover:translate-x-[1px] hover:translate-y-[1px] transition cursor-pointer">
                    YA, LANJUTKAN
                </button>
            </div>
        </div>
    </div>

    <div id="modalAlertGlobal" class="fixed inset-0 bg-black/60 z-50 flex items-center justify-center p-4 hidden" onclick="tutupModalAlertGlobal()">
        <div class="bg-[#FAF8F5] border-2 border-black neo-shadow-lg max-w-lg w-full overflow-hidden relative" onclick="event.stopPropagation()">
            <div id="modalAlertAccent" class="h-3.5 bg-[#E84125] border-b-2 border-black"></div>
            <div class="p-6 sm:p-7">
                <div class="flex items-center justify-between gap-2">
                    <span id="modalAlertBadge" class="bg-[#E84125] text-white px-2.5 py-0.5 text-[10px] sm:text-[11px] font-mono font-bold uppercase tracking-wider">
                        PERINGATAN
                    </span>
                    <button type="button" onclick="tutupModalAlertGlobal()" class="w-7 h-7 border-2 border-black bg-white flex items-center justify-center font-bold text-sm hover:bg-black hover:text-white transition cursor-pointer">&times;</button>
                </div>
                <h3 id="modalAlertJudul" class="text-lg sm:text-xl font-black uppercase text-black tracking-tight mt-3 leading-tight">
                    PERHATIAN
                </h3>
                <p id="modalAlertPesan" class="text-xs sm:text-sm font-mono text-slate-800 mt-2 leading-relaxed">
                    Pesan peringatan
                </p>
            </div>
            <div class="px-6 py-4 bg-white border-t-2 border-black flex justify-end">
                <button type="button" onclick="tutupModalAlertGlobal()" class="px-5 py-2 bg-black hover:bg-slate-800 text-white border-2 border-black font-mono font-bold text-xs uppercase tracking-wider neo-shadow-sm neo-btn transition cursor-pointer">
                    MENGERTI
                </button>
            </div>
        </div>
    </div>

    <script>
    var modalKonfirmasiCallback = null;

    function bukaKonfirmasi(options) {
        var modal = document.getElementById('modalKonfirmasiGlobal');
        if (!modal) return;

        var pesan = typeof options === 'string' ? options : (options.pesan || 'Apakah Anda yakin ingin melanjutkan tindakan ini?');
        var judul = options.judul || 'KONFIRMASI TINDAKAN';
        var badge = options.badge || 'KONFIRMASI';
        var tombolTeks = options.tombolTeks || 'YA, LANJUTKAN';
        var warna = options.warna || 'danger';

        document.getElementById('modalKonfirmasiPesan').textContent = pesan;
        document.getElementById('modalKonfirmasiJudul').textContent = judul;
        document.getElementById('modalKonfirmasiBadge').textContent = badge;
        
        var btnOk = document.getElementById('modalKonfirmasiBtnOk');
        btnOk.textContent = tombolTeks;

        var accent = document.getElementById('modalKonfirmasiAccent');
        var badgeEl = document.getElementById('modalKonfirmasiBadge');

        if (warna === 'success') {
            accent.className = 'h-3.5 bg-[#164E33] border-b-2 border-black';
            badgeEl.className = 'bg-[#164E33] text-white px-2.5 py-0.5 text-[10px] sm:text-[11px] font-mono font-bold uppercase tracking-wider';
            btnOk.className = 'px-5 py-2 bg-[#164E33] hover:bg-[#123e29] text-white border-2 border-black font-mono font-bold text-xs uppercase tracking-wider shadow-[2px_2px_0px_#000] hover:translate-x-[1px] hover:translate-y-[1px] transition cursor-pointer';
        } else {
            accent.className = 'h-3.5 bg-[#E84125] border-b-2 border-black';
            badgeEl.className = 'bg-[#E84125] text-white px-2.5 py-0.5 text-[10px] sm:text-[11px] font-mono font-bold uppercase tracking-wider';
            btnOk.className = 'px-5 py-2 bg-[#E84125] hover:bg-[#c9351d] text-white border-2 border-black font-mono font-bold text-xs uppercase tracking-wider shadow-[2px_2px_0px_#000] hover:translate-x-[1px] hover:translate-y-[1px] transition cursor-pointer';
        }

        modalKonfirmasiCallback = function() {
            tutupModalKonfirmasiGlobal();
            if (options.href) {
                window.location.href = options.href;
            } else if (typeof options.onConfirm === 'function') {
                options.onConfirm();
            }
        };

        btnOk.onclick = modalKonfirmasiCallback;
        modal.classList.remove('hidden');
    }

    function tutupModalKonfirmasiGlobal() {
        var modal = document.getElementById('modalKonfirmasiGlobal');
        if (modal) modal.classList.add('hidden');
        modalKonfirmasiCallback = null;
    }

    function bukaAlert(pesan, judul) {
        var modal = document.getElementById('modalAlertGlobal');
        if (!modal) return;
        document.getElementById('modalAlertPesan').textContent = pesan;
        document.getElementById('modalAlertJudul').textContent = judul || 'PERHATIAN';
        modal.classList.remove('hidden');
    }

    function tutupModalAlertGlobal() {
        var modal = document.getElementById('modalAlertGlobal');
        if (modal) modal.classList.add('hidden');
    }

    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            tutupModalKonfirmasiGlobal();
            tutupModalAlertGlobal();
            var mf = document.getElementById('modalFlash');
            if (mf) mf.remove();
        }
    });
    </script>

    <main class="flex-grow max-w-[1720px] w-full mx-auto px-4 sm:px-8 lg:px-12 py-6 sm:py-8">
