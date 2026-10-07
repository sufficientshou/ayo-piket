<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$login_sukses = false;
$admin_nama = "";

if ($_SERVER['REQUEST_METHOD'] !== 'POST' && isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
    header("Location: index.php");
    exit();
}

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

$pesan_error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = sanitize($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($username) || empty($password)) {
        $pesan_error = "Username dan password wajib diisi!";
    } else {
        $stmt = $koneksi->prepare("SELECT * FROM admins WHERE username = ? LIMIT 1");
        $stmt->execute([$username]);
        $admin = $stmt->fetch();

        if ($admin && password_verify($password, $admin['password'])) {
            session_regenerate_id(true);
            $_SESSION['admin_logged_in'] = true;
            $_SESSION['admin_id'] = $admin['id'];
            $_SESSION['admin_nama'] = $admin['nama'];
            $_SESSION['admin_username'] = $admin['username'];

            $login_sukses = true;
            $admin_nama = $admin['nama'];
        } else {
            $pesan_error = "Username atau password salah!";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Atmint - Himpunan Mahasiswa Informatika Unsika</title>
    <link rel="icon" type="image/png" href="<?= base_url('assets/images/logo.png') ?>?v=<?= time() ?>">
    <link rel="shortcut icon" type="image/png" href="<?= base_url('assets/images/logo.png') ?>?v=<?= time() ?>">
    <link rel="apple-touch-icon" href="<?= base_url('assets/images/logo.png') ?>">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800;900&family=Space+Mono:ital,wght@0,400;0,700;1,400&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: #FAF8F5;
            background-image: repeating-linear-gradient(
                45deg,
                rgba(0, 0, 0, 0.035) 0,
                rgba(0, 0, 0, 0.035) 1px,
                transparent 0,
                transparent 10px
            );
        }
        .font-mono {
            font-family: 'Space Mono', monospace;
        }
        .neo-shadow {
            box-shadow: 4px 4px 0px #000000;
        }
        .neo-shadow-lg {
            box-shadow: 8px 8px 0px #000000;
        }
        .neo-shadow-sm {
            box-shadow: 2px 2px 0px #000000;
        }
        .neo-shadow-xs {
            box-shadow: 1px 1px 0px #000000;
        }
    </style>
</head>
<body class="text-black min-h-screen flex flex-col justify-between relative selection:bg-[#ccff00] selection:text-black">

    <?php if ($login_sukses): ?>
        <div id="modalFlash" class="fixed inset-0 bg-black/60 z-50 flex items-center justify-center p-4">
            <div class="bg-[#FAF8F5] border-2 border-black neo-shadow-lg max-w-lg w-full overflow-hidden relative">
                <div class="h-3.5 bg-[#164E33] border-b-2 border-black"></div>
                <div class="p-6 sm:p-7">
                    <div class="flex items-center justify-between gap-2">
                        <span class="bg-[#164E33] text-white px-2.5 py-0.5 text-[10px] sm:text-[11px] font-mono font-bold uppercase tracking-wider">
                            BERHASIL
                        </span>
                        <a href="index.php" class="w-7 h-7 border-2 border-black bg-white flex items-center justify-center font-bold text-sm hover:bg-black hover:text-white transition cursor-pointer">&times;</a>
                    </div>
                    <h3 class="text-lg sm:text-xl font-black uppercase text-black tracking-tight mt-3 leading-tight">
                        LOGIN BERHASIL
                    </h3>
                    <p class="text-xs sm:text-sm font-mono text-slate-800 mt-2 leading-relaxed">
                        Selamat datang kembali, <?= htmlspecialchars($admin_nama) ?>! Mengalihkan ke dashboard...
                    </p>
                </div>
                <div class="px-6 py-4 bg-white border-t-2 border-black flex justify-end">
                    <a href="index.php" class="px-5 py-2 bg-black hover:bg-slate-800 text-white border-2 border-black font-mono font-bold text-xs uppercase tracking-wider neo-shadow-sm neo-btn transition cursor-pointer">
                        MASUK KE DASHBOARD
                    </a>
                </div>
            </div>
        </div>
        <script>
            setTimeout(function() {
                window.location.href = 'index.php';
            }, 1200);
            function tutupPopupFlash() {
                window.location.href = 'index.php';
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

    <div class="w-full px-4 sm:px-8 py-5 flex justify-end relative z-30">
        <a href="../index.php" class="inline-flex items-center gap-2 bg-white border-2 border-black px-4 py-2 text-xs font-mono font-bold uppercase tracking-wider neo-shadow-sm hover:translate-x-[1px] hover:translate-y-[1px] transition">
            &larr; KEMBALI KE JADWAL
        </a>
    </div>

    <main class="flex-grow flex flex-col justify-center items-center px-4 py-8 sm:py-12 relative z-20">
        <div class="w-full max-w-[560px]">

            <div class="bg-white border-[3px] border-black neo-shadow-lg p-6 sm:p-10 relative">
                <div class="w-16 h-16 bg-[#ccff00] border-2 border-black neo-shadow-sm mx-auto flex items-center justify-center mb-5">
                    <svg class="w-8 h-8 text-black" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                    </svg>
                </div>

                <h1 class="text-3xl sm:text-4xl font-black text-center text-black tracking-tight">
                    Atmint
                </h1>

                <div class="mt-6 bg-[#fffdf0] border-2 border-black p-3.5 flex items-start gap-3 shadow-[2px_2px_0px_#000]">
                    <div class="w-6 h-6 bg-[#ea3829] border-2 border-black text-white shrink-0 flex items-center justify-center font-black font-mono text-xs">
                        !
                    </div>
                    <div class="text-xs text-zinc-800 leading-snug">
                        <span class="font-mono font-bold text-black uppercase">PERHATIAN:</span> Akses dibatasi hanya untuk divisi terkait.
                    </div>
                </div>

                <?php if (!empty($pesan_error)): ?>
                    <div class="mt-4 bg-rose-50 border-2 border-black p-3 flex items-start gap-2.5 shadow-[3px_3px_0px_#e11d48]">
                        <div class="w-5 h-5 bg-[#ea3829] text-white font-mono font-bold text-xs flex items-center justify-center shrink-0 border border-black">!</div>
                        <div class="text-xs font-mono font-bold text-[#ea3829] leading-tight self-center">
                            <?= htmlspecialchars($pesan_error) ?>
                        </div>
                    </div>
                <?php endif; ?>

                <form action="login.php" method="POST" class="mt-6 space-y-4">
                    <div>
                        <div class="flex items-center justify-between mb-1.5">
                            <label for="username" class="font-mono text-xs font-bold text-black uppercase tracking-wider">
                                USERNAME <span class="text-[#ea3829] font-black">*</span>
                            </label>
                            <span class="bg-white border border-black px-2 py-0.5 font-mono text-[10px] font-bold text-black uppercase">
                                WAJIB DIISI
                            </span>
                        </div>
                        <div class="relative flex items-center border-2 border-black neo-shadow-sm bg-white focus-within:shadow-[3px_3px_0px_#000] transition">
                            <div class="pl-3.5 pr-2 text-zinc-600">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                </svg>
                            </div>
                            <input type="text" id="username" name="username" required
                                   value="<?= isset($username) ? htmlspecialchars($username) : '' ?>"
                                   placeholder="Masukkan username"
                                   class="w-full py-3 pr-4 font-mono text-sm text-black placeholder:text-zinc-500 placeholder:font-mono focus:outline-none bg-transparent">
                        </div>
                    </div>

                    <div>
                        <div class="flex items-center justify-between mb-1.5">
                            <label for="password" class="font-mono text-xs font-bold text-black uppercase tracking-wider">
                                KATA SANDI <span class="text-[#ea3829] font-black">*</span>
                            </label>
                            <a href="javascript:void(0)" onclick="bukaModalLupaPassword()" class="font-mono text-xs font-bold text-[#ea3829] uppercase tracking-wider hover:underline">
                                LUPA PASSWORD?
                            </a>
                        </div>
                        <div class="relative flex items-center border-2 border-black neo-shadow-sm bg-white focus-within:shadow-[3px_3px_0px_#000] transition">
                            <div class="pl-3.5 pr-2 text-zinc-600">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z" />
                                </svg>
                            </div>
                            <input type="password" id="password" name="password" required
                                   placeholder="&bull;&bull;&bull;&bull;&bull;&bull;&bull;&bull;&bull;&bull;&bull;&bull;"
                                   class="w-full py-3 pr-10 font-mono text-sm text-black placeholder:text-zinc-500 placeholder:font-mono focus:outline-none bg-transparent">
                            <button type="button" onclick="togglePasswordVisibility()" class="absolute right-3 text-zinc-600 hover:text-black focus:outline-none p-1" title="Lihat password">
                                <svg id="eye-icon" class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                </svg>
                            </button>
                        </div>
                    </div>

                    <button type="submit" class="w-full mt-2 bg-[#0e3b2e] hover:bg-[#144d3c] active:translate-x-[2px] active:translate-y-[2px] active:shadow-none text-white border-2 border-black shadow-[4px_4px_0px_#000] py-3.5 px-4 font-mono font-bold text-sm tracking-wider uppercase flex items-center justify-center gap-3 transition cursor-pointer">
                        <span>MASUK KE DASHBOARD ADMIN</span>
                        <svg class="w-5 h-5 text-[#ccff00]" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                        </svg>
                    </button>
                </form>

                <div class="mt-6 text-center">
                    <a href="../index.php" class="font-mono text-xs font-bold text-black uppercase tracking-wider hover:underline inline-flex items-center gap-1.5">
                        <span>&larr;</span>
                        <span>KEMBALI KE HALAMAN JADWAL &amp; LAPORAN PIKET</span>
                    </a>
                </div>
            </div>

        </div>
    </main>

    <footer class="border-t-2 border-black bg-transparent py-6 mt-auto relative z-30">
        <div class="max-w-[1720px] mx-auto px-4 sm:px-8 lg:px-12 flex items-center justify-center">
            <div class="flex items-center gap-3 sm:gap-3.5">
                <div class="w-7 h-7 sm:w-8 sm:h-8 bg-white border-2 border-black flex items-center justify-center p-0.5 shrink-0 neo-shadow-sm">
                    <img src="../assets/images/logo.png" alt="Logo" class="w-full h-full object-contain">
                </div>
                <div class="text-xs sm:text-sm font-mono font-bold text-black tracking-wider text-center">
                    Licensed, Registered, and authorized by HIMTIKA <?= date('Y') ?>
                </div>
            </div>
        </div>
    </footer>

    <div id="modalLupaPassword" class="fixed inset-0 bg-black/60 z-50 flex items-center justify-center p-4 hidden" onclick="tutupModalLupaPassword()">
        <div class="bg-[#FAF8F5] border-2 border-black neo-shadow-lg max-w-lg w-full overflow-hidden relative" onclick="event.stopPropagation()">
            <div class="h-3.5 bg-[#E84125] border-b-2 border-black"></div>
            <div class="p-6 sm:p-7">
                <div class="flex items-center justify-between gap-2">
                    <span class="bg-[#E84125] text-white px-2.5 py-0.5 text-[10px] sm:text-[11px] font-mono font-bold uppercase tracking-wider">
                        BANTUAN AKUN
                    </span>
                    <button type="button" onclick="tutupModalLupaPassword()" class="w-7 h-7 border-2 border-black bg-white flex items-center justify-center font-bold text-sm hover:bg-black hover:text-white transition cursor-pointer">&times;</button>
                </div>
                <h3 class="text-lg sm:text-xl font-black uppercase text-black tracking-tight mt-3 leading-tight">
                    RESET KATA SANDI
                </h3>
                <p class="text-xs sm:text-sm font-mono text-slate-800 mt-2 leading-relaxed">
                    Silakan hubungi Divisi Litbang atau Koordinator Kesekretariatan untuk melakukan reset akun pengurus.
                </p>
            </div>
            <div class="px-6 py-4 bg-white border-t-2 border-black flex justify-end">
                <button type="button" onclick="tutupModalLupaPassword()" class="px-5 py-2 bg-black hover:bg-slate-800 text-white border-2 border-black font-mono font-bold text-xs uppercase tracking-wider neo-shadow-sm neo-btn transition cursor-pointer">
                    MENGERTI
                </button>
            </div>
        </div>
    </div>

    <script>
        function bukaModalLupaPassword() {
            document.getElementById('modalLupaPassword').classList.remove('hidden');
        }
        function tutupModalLupaPassword() {
            document.getElementById('modalLupaPassword').classList.add('hidden');
        }
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                tutupModalLupaPassword();
            }
        });

        function togglePasswordVisibility() {
            const passwordInput = document.getElementById('password');
            const eyeIcon = document.getElementById('eye-icon');
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                eyeIcon.innerHTML = `
                    <path stroke-linecap="round" stroke-linejoin="round" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18" />
                `;
            } else {
                passwordInput.type = 'password';
                eyeIcon.innerHTML = `
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                `;
            }
        }
    </script>
</body>
</html>
