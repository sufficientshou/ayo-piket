<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../includes/auth_check.php';
require_once __DIR__ . '/../includes/functions.php';
$flash = get_flash_message();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($judul_halaman) ? $judul_halaman . ' - Admin Ayo Piket' : 'Admin Panel - Ayo Piket' ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
        }
    </style>
</head>
<body class="bg-slate-100 text-slate-800 min-h-screen flex flex-col">
    <header class="bg-slate-900 text-white shadow sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-16">
                <div class="flex items-center space-x-6">
                    <a href="index.php" class="font-bold text-lg text-indigo-400 flex items-center space-x-2">
                        <span>Ayo Piket</span>
                        <span class="text-xs bg-indigo-500/20 text-indigo-300 border border-indigo-500/30 px-2 py-0.5 rounded">Admin</span>
                    </a>
                    <nav class="hidden md:flex space-x-1 text-sm font-medium">
                        <a href="index.php" class="px-3 py-2 rounded-lg hover:bg-slate-800 transition">Rekap Presensi</a>
                        <a href="schedule.php" class="px-3 py-2 rounded-lg hover:bg-slate-800 transition">Jadwal & Gacha</a>
                        <a href="members.php" class="px-3 py-2 rounded-lg hover:bg-slate-800 transition">Data Pengurus</a>
                        <a href="divisions.php" class="px-3 py-2 rounded-lg hover:bg-slate-800 transition">Data Divisi</a>
                    </nav>
                </div>
                <div class="flex items-center space-x-3 text-sm">
                    <a href="../index.php" target="_blank" class="text-slate-400 hover:text-white px-2 py-1 transition flex items-center space-x-1">
                        <span>Lihat Web</span>
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path></svg>
                    </a>
                    <span class="text-slate-600">|</span>
                    <span class="text-slate-300 font-medium"><?= sanitize($_SESSION['admin_nama'] ?? 'Admin') ?></span>
                    <a href="logout.php" onclick="return confirm('Yakin ingin logout?')" class="bg-rose-600 hover:bg-rose-500 text-white px-3 py-1.5 rounded-lg text-xs font-semibold transition">Keluar</a>
                </div>
            </div>
            <div class="flex md:hidden space-x-1 pb-3 overflow-x-auto text-xs font-medium border-t border-slate-800 pt-2">
                <a href="index.php" class="px-3 py-1.5 rounded-md hover:bg-slate-800 text-slate-300 whitespace-nowrap">Rekap Presensi</a>
                <a href="schedule.php" class="px-3 py-1.5 rounded-md hover:bg-slate-800 text-slate-300 whitespace-nowrap">Jadwal & Gacha</a>
                <a href="members.php" class="px-3 py-1.5 rounded-md hover:bg-slate-800 text-slate-300 whitespace-nowrap">Pengurus</a>
                <a href="divisions.php" class="px-3 py-1.5 rounded-md hover:bg-slate-800 text-slate-300 whitespace-nowrap">Divisi</a>
            </div>
        </div>
    </header>

    <?php if ($flash): ?>
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-4 w-full">
            <div class="p-4 rounded-xl text-sm font-medium flex items-center justify-between <?= $flash['tipe'] === 'sukses' ? 'bg-emerald-50 text-emerald-800 border border-emerald-200' : 'bg-rose-50 text-rose-800 border border-rose-200' ?>">
                <span><?= $flash['pesan'] ?></span>
                <button onclick="this.parentElement.remove()" class="text-xs font-bold uppercase tracking-wider px-2 py-1 rounded hover:bg-black/5">Tutup</button>
            </div>
        </div>
    <?php endif; ?>

    <main class="flex-grow max-w-7xl w-full mx-auto px-4 sm:px-6 lg:px-8 py-6">
