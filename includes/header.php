<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/functions.php';
$flash = get_flash_message();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($judul_halaman) ? $judul_halaman . ' - Ayo Piket' : 'Ayo Piket - Sistem Piket Himpunan' ?></title>
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
<body class="bg-[#F4F0EA] text-black min-h-screen flex flex-col">
    <header class="bg-white border-b-2 border-black sticky top-0 z-50">
        <div class="max-w-[1720px] mx-auto px-4 sm:px-8 lg:px-12">
            <div class="flex justify-between items-center h-20">
                <a href="index.php" class="flex items-center gap-3.5 group">
                    <div class="w-12 h-12 bg-[#FAF8F5] border-2 border-black flex items-center justify-center shrink-0 p-1 neo-shadow-sm group-hover:translate-x-[1px] group-hover:translate-y-[1px] transition">
                        <img src="assets/images/logo.png" alt="Logo Ayo Piket" class="w-full h-full object-contain">
                    </div>
                    <div class="flex flex-col">
                        <span class="font-black text-xl sm:text-2xl tracking-tight text-black leading-none">AYO PIKET</span>
                        <span class="font-mono text-[10px] sm:text-xs tracking-widest text-slate-700 font-bold uppercase mt-1 leading-none">HIMTIKA UNSIKA</span>
                    </div>
                </a>

                <div class="flex items-center gap-2.5 sm:gap-4">
                    <a href="index.php" class="px-4 sm:px-5 py-2 bg-white border-2 border-black text-black font-mono font-bold text-xs sm:text-sm uppercase tracking-wider neo-shadow-sm neo-btn transition">
                        JADWAL
                    </a>
                    <a href="presensi.php" class="px-4 sm:px-5 py-2 bg-white border-2 border-black text-black font-mono font-bold text-xs sm:text-sm uppercase tracking-wider neo-shadow-sm neo-btn transition">
                        LAPOR PIKET
                    </a>
                    <?php if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true): ?>
                        <a href="admin/index.php" class="px-4 sm:px-5 py-2 bg-black border-2 border-black text-white font-mono font-bold text-xs sm:text-sm uppercase tracking-wider flex items-center gap-2 neo-shadow-sm neo-btn transition">
                            <span>ADMIN PANEL</span>
                            <span class="font-bold">&rarr;</span>
                        </a>
                    <?php else: ?>
                        <a href="admin/login.php" class="px-4 sm:px-5 py-2 bg-black border-2 border-black text-white font-mono font-bold text-xs sm:text-sm uppercase tracking-wider flex items-center gap-2 neo-shadow-sm neo-btn transition">
                            <span>LOGIN ADMIN</span>
                            <span class="font-bold">&rarr;</span>
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </header>

    <div class="bg-[#E84125] border-b-2 border-black text-white flex items-stretch overflow-hidden">
        <div class="bg-black text-white px-4 sm:px-6 py-2 sm:py-2.5 border-r-2 border-black text-xs sm:text-sm font-mono font-bold tracking-wider shrink-0 z-10 flex items-center shadow-[2px_0_0_#000]">
            INFO RESMI
        </div>
        <marquee behavior="scroll" direction="left" scrollamount="7" onmouseover="this.stop()" onmouseout="this.start()" class="flex-grow py-2 sm:py-2.5 text-xs sm:text-sm font-mono font-bold uppercase tracking-wider flex items-center">
            PIKET RUANG SEKRETARIAT WAJIB DIISI SEBELUM PUKUL 18.00 WIB &bull; SETIAP SENIN &amp; KAMIS &nbsp;&nbsp;&nbsp;&bull;&nbsp;&nbsp;&nbsp; PERIODE KEPENGURUSAN 2026/2027 &nbsp;&nbsp;&nbsp;&bull;&nbsp;&nbsp;&nbsp; JANGAN LUPA UNGGAH FOTO DOKUMENTASI SETELAH SELESAI BERTUGAS &nbsp;&nbsp;&nbsp;&bull;&nbsp;&nbsp;&nbsp; PIKET RUANG SEKRETARIAT WAJIB DIISI SEBELUM PUKUL 18.00 WIB &bull; SETIAP SENIN &amp; KAMIS &nbsp;&nbsp;&nbsp;&bull;&nbsp;&nbsp;&nbsp; PERIODE KEPENGURUSAN 2026/2027
        </marquee>
    </div>

    <?php if ($flash): ?>
        <?php 
            $is_sukses = ($flash['tipe'] === 'sukses');
            $badge_text = $is_sukses ? 'BERHASIL' : 'PERINGATAN';
            $judul_modal = $is_sukses ? 'LAPORAN TERKIRIM' : 'TERJADI KESALAHAN';
        ?>
        <div id="modalFlash" class="fixed inset-0 bg-black/60 z-50 flex items-center justify-center p-4">
            <div class="bg-[#FAF8F5] border-2 border-black neo-shadow-lg max-w-lg w-full overflow-hidden relative">
                <div class="h-3.5 <?= $is_sukses ? 'bg-[#164E33]' : 'bg-[#E84125]' ?> border-b-2 border-black"></div>
                <div class="p-6 sm:p-8">
                    <div class="flex items-start gap-4 sm:gap-5">
                        <div class="w-14 h-14 sm:w-16 sm:h-16 <?= $is_sukses ? 'bg-[#B8E926]' : 'bg-[#E84125] text-white' ?> border-2 border-black neo-shadow-sm flex items-center justify-center shrink-0">
                            <?php if ($is_sukses): ?>
                                <svg class="w-8 h-8 sm:w-9 sm:h-9 text-black stroke-[3]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <polyline points="20 6 9 17 4 12"></polyline>
                                </svg>
                            <?php else: ?>
                                <span class="font-mono font-black text-2xl sm:text-3xl">!</span>
                            <?php endif; ?>
                        </div>
                        <div class="flex-grow">
                            <span class="<?= $is_sukses ? 'bg-[#164E33]' : 'bg-[#E84125]' ?> text-white px-2.5 py-0.5 text-[11px] font-mono font-bold uppercase tracking-wider">
                                <?= $badge_text ?>
                            </span>
                            <h3 class="text-xl sm:text-2xl font-black uppercase text-black tracking-tight mt-1.5 leading-tight">
                                <?= $judul_modal ?>
                            </h3>
                            <?php if (!$is_sukses && !empty($flash['pesan'])): ?>
                                <p class="text-xs sm:text-sm lg:text-base font-mono text-slate-800 mt-2 leading-relaxed">
                                    <?= $flash['pesan'] ?>
                                </p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <div class="px-6 py-4 bg-white border-t-2 border-black flex justify-end">
                    <button type="button" onclick="tutupPopupFlash()" class="px-6 py-2.5 bg-black text-white hover:bg-slate-800 border-2 border-black font-mono font-bold text-xs sm:text-sm uppercase tracking-wider neo-shadow-sm neo-btn transition flex items-center gap-2">
                        <span>TUTUP</span>
                        <span class="text-base font-bold">&times;</span>
                    </button>
                </div>
            </div>
        </div>
        <script>
            function tutupPopupFlash() {
                var modal = document.getElementById('modalFlash');
                if (modal) modal.remove();
            }
            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape') tutupPopupFlash();
            });
            document.addEventListener('click', function(e) {
                var modal = document.getElementById('modalFlash');
                if (modal && e.target === modal) tutupPopupFlash();
            });
        </script>
    <?php endif; ?>

    <main class="flex-grow max-w-[1720px] w-full mx-auto px-4 sm:px-8 lg:px-12 py-8 sm:py-10">
