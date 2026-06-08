<?php
/*
 * @Feature:     Pusat Bantuan (Help Center & FAQ)
 * @Author:      Muhammad Randyano (Randy)
 * @Description: Halaman interaktif untuk pusat bantuan pelamar (FAQ)
 */

require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../auth/session.php';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pusat Bantuan — <?= APP_NAME ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/design-system.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/components.css">
    <style>
        .faq-answer {
            max-height: 0;
            overflow: hidden;
            transition: max-height 0.3s ease-out, padding 0.3s ease;
        }
        .faq-item.active .faq-answer {
            max-height: 500px;
            padding-bottom: 1.5rem;
        }
        .faq-item.active .faq-icon {
            transform: rotate(180deg);
        }
    </style>
</head>
<body class="bg-slate-900 text-slate-200 font-sans antialiased min-h-screen flex flex-col">
    <?php require_once __DIR__ . '/../components/header.php'; ?>

    <!-- Hero Bantuan -->
    <div class="bg-gradient-to-b from-indigo-900/40 to-slate-900 pt-32 pb-16 px-6 border-b border-white/5">
        <div class="max-w-3xl mx-auto text-center reveal">
            <h1 class="text-4xl font-extrabold text-white mb-4">Pusat Bantuan Pelamar</h1>
            <p class="text-lg text-slate-400 mb-8">Ada yang bisa kami bantu? Temukan jawaban dari pertanyaan yang sering diajukan di bawah ini.</p>
            
            <div class="relative max-w-xl mx-auto">
                <input type="text" id="searchInput" placeholder="Cari topik bantuan (Misal: Cara reset password)..." 
                       class="w-full bg-slate-800/50 border border-slate-700 rounded-full px-6 py-4 text-white placeholder-slate-400 focus:outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 transition-all">
                <button class="absolute right-2 top-2 bottom-2 bg-indigo-600 hover:bg-indigo-700 text-white p-2 rounded-full aspect-square flex items-center justify-center transition-colors">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                </button>
            </div>
        </div>
    </div>

    <main class="flex-grow py-16 px-6">
        <div class="max-w-[800px] mx-auto space-y-4">
            
            <!-- FAQ 1 -->
            <div class="faq-item glass-card rounded-2xl overflow-hidden reveal">
                <button class="w-full text-left px-6 py-5 flex justify-between items-center focus:outline-none" onclick="this.parentElement.classList.toggle('active')">
                    <span class="text-lg font-bold text-white">Bagaimana cara memperbarui CV atau Profil saya?</span>
                    <svg class="faq-icon w-6 h-6 text-indigo-400 transition-transform duration-300" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                </button>
                <div class="faq-answer px-6 text-slate-400 leading-relaxed">
                    Untuk memperbarui profil Anda, silakan *login* ke akun Anda lalu masuk ke menu <strong>Dashboard</strong>. Di sana, klik tombol <strong>"Edit Profil"</strong>. Anda dapat mengunggah CV terbaru (dalam format PDF maksimal 2MB), mengubah keahlian, hingga menautkan portofolio Anda.
                </div>
            </div>

            <!-- FAQ 2 -->
            <div class="faq-item glass-card rounded-2xl overflow-hidden reveal stagger-1">
                <button class="w-full text-left px-6 py-5 flex justify-between items-center focus:outline-none" onclick="this.parentElement.classList.toggle('active')">
                    <span class="text-lg font-bold text-white">Berapa lama proses seleksi setelah melamar?</span>
                    <svg class="faq-icon w-6 h-6 text-indigo-400 transition-transform duration-300" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7-7-7-7"/></svg>
                </button>
                <div class="faq-answer px-6 text-slate-400 leading-relaxed">
                    Waktu peninjauan bergantung sepenuhnya pada kebijakan masing-masing perusahaan. Biasanya memakan waktu 1-2 minggu. Namun tenang saja, Anda bisa memantau status lamaran Anda secara *real-time* di menu <strong>"Lamaran Saya"</strong>.
                </div>
            </div>

            <!-- FAQ 3 -->
            <div class="faq-item glass-card rounded-2xl overflow-hidden reveal stagger-2">
                <button class="w-full text-left px-6 py-5 flex justify-between items-center focus:outline-none" onclick="this.parentElement.classList.toggle('active')">
                    <span class="text-lg font-bold text-white">Apakah saya bisa menarik kembali lamaran saya?</span>
                    <svg class="faq-icon w-6 h-6 text-indigo-400 transition-transform duration-300" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7-7-7-7"/></svg>
                </button>
                <div class="faq-answer px-6 text-slate-400 leading-relaxed">
                    Sayangnya, saat ini lamaran yang sudah dikirimkan tidak dapat ditarik kembali karena data lamaran Anda langsung masuk ke sistem HRD Perusahaan. Pastikan Anda memeriksa CV dan *cover letter* Anda sebelum menekan tombol lamar.
                </div>
            </div>

            <!-- FAQ 4 -->
            <div class="faq-item glass-card rounded-2xl overflow-hidden reveal stagger-3">
                <button class="w-full text-left px-6 py-5 flex justify-between items-center focus:outline-none" onclick="this.parentElement.classList.toggle('active')">
                    <span class="text-lg font-bold text-white">Bagaimana cara mencetak CV dari profil saya?</span>
                    <svg class="faq-icon w-6 h-6 text-indigo-400 transition-transform duration-300" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7-7-7-7"/></svg>
                </button>
                <div class="faq-answer px-6 text-slate-400 leading-relaxed">
                    Anda dapat menggunakan fitur terbaru kami yaitu <strong>Auto-Generate CV</strong>. Buka Dashboard pelamar Anda, dan cari tombol "Cetak CV". Sistem kami akan secara otomatis merakit data Anda menjadi CV profesional berbasis PDF yang siap cetak.
                </div>
            </div>

        </div>

        <div class="mt-16 text-center reveal">
            <p class="text-slate-400 mb-4">Masih butuh bantuan? Tim support kami siap melayani.</p>
            <a href="mailto:support@recruitpro.com" class="inline-flex items-center gap-2 bg-slate-800 hover:bg-indigo-600 text-white px-6 py-3 rounded-lg font-semibold transition-colors border border-slate-700 hover:border-indigo-500">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                Hubungi Customer Support
            </a>
        </div>
    </main>

    <?php require_once __DIR__ . '/../components/footer.php'; ?>

    <!-- Script Live Search FAQ -->
    <script>
        document.getElementById('searchInput').addEventListener('input', function(e) {
            const query = e.target.value.toLowerCase();
            const items = document.querySelectorAll('.faq-item');
            
            items.forEach(item => {
                const question = item.querySelector('span').innerText.toLowerCase();
                const answer = item.querySelector('.faq-answer').innerText.toLowerCase();
                
                if (question.includes(query) || answer.includes(query)) {
                    item.style.display = 'block';
                } else {
                    item.style.display = 'none';
                }
            });
        });
    </script>
</body>
</html>
