    </main>

    <footer class="border-t-2 border-black bg-transparent py-6 mt-auto">
        <div class="max-w-[1720px] mx-auto px-4 sm:px-8 lg:px-12 flex items-center justify-center">
            <div class="flex items-center gap-3 sm:gap-3.5">
                <div class="w-7 h-7 sm:w-8 sm:h-8 bg-white border-2 border-black flex items-center justify-center p-0.5 shrink-0 neo-shadow-sm">
                    <img src="assets/images/logo.png" alt="Logo" class="w-full h-full object-contain">
                </div>
                <div class="text-xs sm:text-sm font-mono font-bold text-black tracking-wider text-center">
                    Licensed, Registered, and authorized by HIMTIKA <?= date('Y') ?>
                </div>
            </div>
        </div>
    </footer>

    <div id="modalSop" class="fixed inset-0 bg-black/60 z-50 hidden flex items-center justify-center p-4">
        <div class="bg-[#FAF8F5] border-2 border-black neo-shadow-lg max-w-2xl w-full p-6 relative max-h-[90vh] overflow-y-auto">
            <div class="flex items-center justify-between border-b-2 border-black pb-3 mb-4">
                <div class="flex items-center gap-2">
                    <span class="bg-[#E84125] text-white border border-black px-2 py-0.5 text-[10px] font-mono font-bold uppercase">RULES</span>
                    <h3 class="font-black text-sm uppercase text-black">RULES PIKET SEKRE HIMTIKA</h3>
                </div>
                <button type="button" onclick="tutupModalSop()" class="w-6 h-6 border-2 border-black bg-white flex items-center justify-center font-bold text-xs hover:bg-black hover:text-white transition">&times;</button>
            </div>
            <div class="space-y-3 text-xs text-slate-800 font-mono leading-relaxed">
                <div class="p-3 bg-white border border-black">
                    <p class="font-bold text-black mb-1">1. WAKTU PELAKSANAAN</p>
                    <p>Piket dilaksanakan sesuai jadwal yang telah ditentukan, dengan waktu pelaksanaan 15.00–20.00 WIB.</p>
                </div>
                <div class="p-3 bg-white border border-black">
                    <p class="font-bold text-black mb-1">2. TANGGUNG JAWAB PENGURUS</p>
                    <p>Pengurus yang terjadwal bertanggung jawab untuk melaksanakan piket pada hari tersebut.</p>
                </div>
                <div class="p-3 bg-white border border-black">
                    <p class="font-bold text-black mb-1">3. PEMBAGIAN TUGAS</p>
                    <p>Pelaksanaan dan pembagian tugas bersifat fleksibel dan disepakati bersama oleh pengurus yang bertugas.</p>
                </div>
                <div class="p-3 bg-white border border-black">
                    <p class="font-bold text-black mb-1">4. LINGKUP TUGAS</p>
                    <p>Tugas piket meliputi menyapu, mengepel, dan membersihkan Sekre HIMTIKA.</p>
                </div>
                <div class="p-3 bg-white border border-black">
                    <p class="font-bold text-black mb-1">5. KONDISI AKHIR SEKRE</p>
                    <p>Setelah piket selesai, pastikan Sekre HIMTIKA dalam keadaan bersih dan rapi.</p>
                </div>
                <div class="p-3 bg-white border border-black">
                    <p class="font-bold text-black mb-1">6. PENDATAAN &amp; DOKUMENTASI</p>
                    <p>Setiap pengurus yang melaksanakan piket wajib melakukan pendataan melalui Google Form dan mengunggah bukti dokumentasi.</p>
                </div>
                <div class="p-3 bg-white border border-black">
                    <p class="font-bold text-black mb-1">7. IZIN / PERGANTIAN JADWAL</p>
                    <p>Jika berhalangan hadir, pengurus wajib mengonfirmasi terlebih dahulu kepada HIMTIKA Care dan mencari pengurus yang bersedia untuk menggantikan pada jadwal piket lain yang telah ditentukan. Untuk kendala yang sudah diketahui sebelumnya, konfirmasi dilakukan paling lambat H-1. Pergantian jadwal hanya dilakukan jika memang ada keperluan yang tidak dapat ditinggalkan.</p>
                </div>
                <div class="p-3 bg-rose-50 border border-black">
                    <p class="font-bold text-rose-950 mb-1">8. KONSEKUENSI &amp; SANKSI</p>
                    <p class="text-rose-950">Bagi pengurus yang tidak melaksanakan piket dan tidak melakukan konfirmasi kepada HIMTIKA Care akan dikenakan punishment.</p>
                </div>
            </div>
            <div class="mt-5 pt-3 border-t-2 border-black flex justify-end">
                <button type="button" onclick="tutupModalSop()" class="px-4 py-1.5 bg-black text-white border-2 border-black font-mono font-bold text-xs uppercase neo-shadow-sm neo-btn">TUTUP</button>
            </div>
        </div>
    </div>

    <script>
        function bukaModalSop() {
            document.getElementById('modalSop').classList.remove('hidden');
        }
        function tutupModalSop() {
            document.getElementById('modalSop').classList.add('hidden');
        }
    </script>
</body>
</html>
