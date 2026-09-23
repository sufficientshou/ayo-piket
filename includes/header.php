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
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
        }
    </style>
</head>
<body class="bg-slate-50 text-slate-800 min-h-screen flex flex-col">
    <nav class="bg-indigo-600 text-white shadow-md sticky top-0 z-50">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-16">
                <div class="flex items-center space-x-3">
                    <a href="index.php" class="flex items-center space-x-2 font-bold text-xl tracking-tight">
                        <span class="bg-white text-indigo-600 p-1.5 rounded-lg shadow-sm">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                            </svg>
                        </span>
                        <span>Ayo Piket</span>
                    </a>
                </div>
                <div class="flex items-center space-x-2 sm:space-x-4 text-sm font-medium">
                    <a href="index.php" class="px-3 py-2 rounded-lg hover:bg-indigo-700 transition">Jadwal</a>
                    <a href="presensi.php" class="px-3 py-2 rounded-lg bg-indigo-500 hover:bg-indigo-400 text-white transition shadow-sm">Lapor Piket</a>
                    <?php if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true): ?>
                        <a href="admin/index.php" class="px-3 py-2 rounded-lg bg-emerald-500 hover:bg-emerald-600 text-white transition">Admin Panel</a>
                    <?php else: ?>
                        <a href="admin/login.php" class="px-3 py-2 rounded-lg hover:bg-indigo-700 transition">Login Admin</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </nav>

    <?php if ($flash): ?>
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 mt-4 w-full">
            <div class="p-4 rounded-xl text-sm font-medium flex items-center justify-between <?= $flash['tipe'] === 'sukses' ? 'bg-emerald-50 text-emerald-800 border border-emerald-200' : 'bg-rose-50 text-rose-800 border border-rose-200' ?>">
                <span><?= $flash['pesan'] ?></span>
                <button onclick="this.parentElement.remove()" class="text-xs font-bold uppercase tracking-wider px-2 py-1 rounded hover:bg-black/5">Tutup</button>
            </div>
        </div>
    <?php endif; ?>

    <main class="flex-grow max-w-6xl w-full mx-auto px-4 sm:px-6 lg:px-8 py-6">
