<?php
/*
 * @Module:      Footer Component
 * @Author:      FE-01 (UI Shell Lead)
 * @Date:        2026-05-24
 * @Description: Dark glassmorphism footer with 4-column layout,
 *               brand info, navigation links, and copyright bar.
 * @Ownership:   FE-01
 *
 * RecruitPro Enterprise — © 2026
 * Do NOT modify without PR approval from module owner.
 */
?>

<!-- ═══════════════════════════════════════════════════════════════
     FOOTER — Glassmorphism Dark Footer
     ═══════════════════════════════════════════════════════════════ -->
<footer class="mt-20 border-t border-white/[0.06]">
    <div class="max-w-[1400px] mx-auto px-6">

        <!-- Main Footer Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-10 py-16">

            <!-- Brand Column -->
            <div class="lg:col-span-1">
                <a href="<?= BASE_URL ?>" class="flex items-center gap-3 mb-4">
                    <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-indigo-500 to-indigo-600
                                flex items-center justify-center shadow-[0_4px_14px_rgba(99,102,241,0.4)]">
                        <svg class="w-5 h-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21 13.255A23.193 23.193 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                        </svg>
                    </div>
                    <div>
                        <span class="text-lg font-bold text-white tracking-tight">Recruit<span class="text-indigo-400">Pro</span></span>
                        <span class="block text-[10px] font-semibold text-slate-500 uppercase tracking-[0.15em] -mt-1">Enterprise</span>
                    </div>
                </a>
                <p class="text-sm text-slate-400 leading-relaxed mb-6">
                    Platform rekrutmen enterprise terdepan di Indonesia. Menghubungkan talenta terbaik
                    dengan perusahaan-perusahaan ternama.
                </p>
                <!-- Social Icons -->
                <div class="flex items-center gap-3">
                    <a href="#" class="w-9 h-9 rounded-lg bg-white/5 border border-white/[0.08] flex items-center justify-center text-slate-400 hover:text-white hover:bg-white/10 transition-all duration-200">
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M24 4.557c-.883.392-1.832.656-2.828.775 1.017-.609 1.798-1.574 2.165-2.724-.951.564-2.005.974-3.127 1.195-.897-.957-2.178-1.555-3.594-1.555-3.179 0-5.515 2.966-4.797 6.045-4.091-.205-7.719-2.165-10.148-5.144-1.29 2.213-.669 5.108 1.523 6.574-.806-.026-1.566-.247-2.229-.616-.054 2.281 1.581 4.415 3.949 4.89-.693.188-1.452.232-2.224.084.626 1.956 2.444 3.379 4.6 3.419-2.07 1.623-4.678 2.348-7.29 2.04 2.179 1.397 4.768 2.212 7.548 2.212 9.142 0 14.307-7.721 13.995-14.646.962-.695 1.797-1.562 2.457-2.549z"/></svg>
                    </a>
                    <a href="#" class="w-9 h-9 rounded-lg bg-white/5 border border-white/[0.08] flex items-center justify-center text-slate-400 hover:text-white hover:bg-white/10 transition-all duration-200">
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433c-1.144 0-2.063-.926-2.063-2.065 0-1.138.92-2.063 2.063-2.063 1.14 0 2.064.925 2.064 2.063 0 1.139-.925 2.065-2.064 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z"/></svg>
                    </a>
                    <a href="#" class="w-9 h-9 rounded-lg bg-white/5 border border-white/[0.08] flex items-center justify-center text-slate-400 hover:text-white hover:bg-white/10 transition-all duration-200">
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M12 0C5.374 0 0 5.373 0 12c0 5.302 3.438 9.8 8.207 11.387.599.111.793-.261.793-.577v-2.234c-3.338.726-4.033-1.416-4.033-1.416-.546-1.387-1.333-1.756-1.333-1.756-1.089-.745.083-.729.083-.729 1.205.084 1.839 1.237 1.839 1.237 1.07 1.834 2.807 1.304 3.492.997.107-.775.418-1.305.762-1.604-2.665-.305-5.467-1.334-5.467-5.931 0-1.311.469-2.381 1.236-3.221-.124-.303-.535-1.524.117-3.176 0 0 1.008-.322 3.301 1.23A11.509 11.509 0 0112 5.803c1.02.005 2.047.138 3.006.404 2.291-1.552 3.297-1.23 3.297-1.23.653 1.653.242 2.874.118 3.176.77.84 1.235 1.911 1.235 3.221 0 4.609-2.807 5.624-5.479 5.921.43.372.823 1.102.823 2.222v3.293c0 .319.192.694.801.576C20.566 21.797 24 17.3 24 12c0-6.627-5.373-12-12-12z"/></svg>
                    </a>
                </div>
            </div>

            <!-- Untuk Pelamar Column -->
            <div>
                <h4 class="text-sm font-semibold text-white uppercase tracking-wider mb-4">Untuk Pelamar</h4>
                <ul class="space-y-3">
                    <li><a href="<?= BASE_URL ?>/pelamar/jobs/browse.php" class="text-sm text-slate-400 hover:text-white transition-colors duration-200">Cari Lowongan</a></li>
                    <li><a href="<?= BASE_URL ?>/auth/register.php" class="text-sm text-slate-400 hover:text-white transition-colors duration-200">Daftar Akun</a></li>
                    <li><a href="<?= BASE_URL ?>/auth/login.php" class="text-sm text-slate-400 hover:text-white transition-colors duration-200">Login</a></li>
                    <li><a href="#" class="text-sm text-slate-400 hover:text-white transition-colors duration-200">Tips Karir</a></li>
                    <li><a href="#" class="text-sm text-slate-400 hover:text-white transition-colors duration-200">FAQ</a></li>
                </ul>
            </div>

            <!-- Untuk Perusahaan Column -->
            <div>
                <h4 class="text-sm font-semibold text-white uppercase tracking-wider mb-4">Untuk Perusahaan</h4>
                <ul class="space-y-3">
                    <li><a href="<?= BASE_URL ?>/auth/register.php" class="text-sm text-slate-400 hover:text-white transition-colors duration-200">Pasang Lowongan</a></li>
                    <li><a href="#" class="text-sm text-slate-400 hover:text-white transition-colors duration-200">Harga & Paket</a></li>
                    <li><a href="#" class="text-sm text-slate-400 hover:text-white transition-colors duration-200">Solusi Enterprise</a></li>
                    <li><a href="#" class="text-sm text-slate-400 hover:text-white transition-colors duration-200">Integrasi API</a></li>
                    <li><a href="#" class="text-sm text-slate-400 hover:text-white transition-colors duration-200">Studi Kasus</a></li>
                </ul>
            </div>

            <!-- Kontak Column -->
            <div>
                <h4 class="text-sm font-semibold text-white uppercase tracking-wider mb-4">Kontak</h4>
                <ul class="space-y-3">
                    <li class="flex items-start gap-3">
                        <svg class="w-4 h-4 text-slate-500 mt-0.5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 01-2.25 2.25h-15a2.25 2.25 0 01-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25m19.5 0v.243a2.25 2.25 0 01-1.07 1.916l-7.5 4.615a2.25 2.25 0 01-2.36 0L3.32 8.91a2.25 2.25 0 01-1.07-1.916V6.75"/>
                        </svg>
                        <span class="text-sm text-slate-400">hello@recruitpro.id</span>
                    </li>
                    <li class="flex items-start gap-3">
                        <svg class="w-4 h-4 text-slate-500 mt-0.5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 6.75c0 8.284 6.716 15 15 15h2.25a2.25 2.25 0 002.25-2.25v-1.372c0-.516-.351-.966-.852-1.091l-4.423-1.106c-.44-.11-.902.055-1.173.417l-.97 1.293c-.282.376-.769.542-1.21.38a12.035 12.035 0 01-7.143-7.143c-.162-.441.004-.928.38-1.21l1.293-.97c.363-.271.527-.734.417-1.173L6.963 3.102a1.125 1.125 0 00-1.091-.852H4.5A2.25 2.25 0 002.25 4.5v2.25z"/>
                        </svg>
                        <span class="text-sm text-slate-400">+62 21 1234 5678</span>
                    </li>
                    <li class="flex items-start gap-3">
                        <svg class="w-4 h-4 text-slate-500 mt-0.5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z"/>
                        </svg>
                        <span class="text-sm text-slate-400">Jakarta, Indonesia</span>
                    </li>
                </ul>
            </div>
        </div>

        <!-- Copyright Bar -->
        <div class="border-t border-white/[0.06] py-6 flex flex-col md:flex-row items-center justify-between gap-4">
            <p class="text-xs text-slate-500">
                &copy; <?= date('Y') ?> RecruitPro Enterprise. All rights reserved.
            </p>
            <div class="flex items-center gap-6">
                <a href="#" class="text-xs text-slate-500 hover:text-slate-300 transition-colors">Kebijakan Privasi</a>
                <a href="#" class="text-xs text-slate-500 hover:text-slate-300 transition-colors">Syarat & Ketentuan</a>
                <a href="#" class="text-xs text-slate-500 hover:text-slate-300 transition-colors">Peta Situs</a>
            </div>
        </div>
    </div>
</footer>
< s c r i p t > d o c u m e n t . a d d E v e n t L i s t e n e r ( " m o u s e m o v e " , ( e ) = > { d o c u m e n t . q u e r y S e l e c t o r A l l ( " . i n t e r a c t i v e - c a r d " ) . f o r E a c h ( ( c a r d ) = > { c o n s t   r e c t = c a r d . g e t B o u n d i n g C l i e n t R e c t ( ) ; c o n s t   x = e . c l i e n t X - r e c t . l e f t ; c o n s t   y = e . c l i e n t Y - r e c t . t o p ; c a r d . s t y l e . s e t P r o p e r t y ( " - - m o u s e - x " , ` $ { x } p x ` ) ; c a r d . s t y l e . s e t P r o p e r t y ( " - - m o u s e - y " , ` $ { y } p x ` ) ; } ) ; } ) ; < / s c r i p t >  
 