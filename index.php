<?php
session_start();
if (!isset($_SESSION['user'])) {
    header('Location: login.php'); // Mengusir paksa user ke file login.php kamu jika belum auth
    exit;
}
?>

require_once __DIR__ . '/config/app.php';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/auth/session.php';

$isLoggedIn = isset($_SESSION['user_id']);
$role = $_SESSION['role'] ?? null;

// Fetch latest active jobs for featured section
try {
    $stmt = Database::getConnection()->prepare("
        SELECT j.*, h.company_name 
        FROM jobs j 
        JOIN hr_profiles h ON j.hr_profile_id = h.id 
        WHERE j.status = :status 
        ORDER BY j.created_at DESC 
        LIMIT 6
    ");
    $stmt->execute(['status' => JOB_STATUS_ACTIVE]);
    $featuredJobs = $stmt->fetchAll();
} catch (PDOException $e) {
    $featuredJobs = [];
}
?>
<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= APP_NAME ?> — Sistem Rekrutmen Enterprise</title>
    <meta name="description" content="Portal lowongan kerja modern untuk pencari kerja dan perusahaan. Temukan karir impianmu hari ini.">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="<?= BASE_URL ?>/assets/js/theme.js"></script>
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/design-system.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/components.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/animations.css">
</head>
<body class="bg-slate-900 text-slate-200 font-sans antialiased min-h-screen flex flex-col relative overflow-x-hidden selection:bg-indigo-500/30 selection:text-indigo-200">

    <?php require_once __DIR__ . '/components/header.php'; ?>

    <main class="flex-grow">
        
        <!-- ═══════════════════════════════════════════════════════════════
             HERO SECTION
             ═══════════════════════════════════════════════════════════════ -->
        <section class="relative pt-32 pb-20 lg:pt-48 lg:pb-32 px-6 overflow-hidden min-h-[90vh] flex items-center">
            <!-- Glowing Orbs Background -->
            <div class="absolute top-1/4 left-1/4 w-96 h-96 bg-indigo-600/20 rounded-full blur-[128px] pointer-events-none animate-[pulse_8s_ease-in-out_infinite]"></div>
            <div class="absolute bottom-1/4 right-1/4 w-96 h-96 bg-emerald-600/15 rounded-full blur-[128px] pointer-events-none animate-[pulse_10s_ease-in-out_infinite_reverse]"></div>
            
            <div class="max-w-[1400px] mx-auto w-full relative z-10">
                <div class="text-center max-w-4xl mx-auto reveal">
                    
                    <div class="inline-flex items-center gap-2 px-4 py-2 rounded-full glass-card border border-indigo-500/30 mb-8 bg-indigo-500/10">
                        <span class="flex h-2 w-2 relative">
                            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-indigo-500 opacity-75"></span>
                            <span class="relative inline-flex rounded-full h-2 w-2 bg-indigo-600"></span>
                        </span>
                        <span class="text-xs font-semibold tracking-wider text-indigo-600 uppercase">Rekrutmen Generasi Baru</span>
                    </div>

                    <h1 class="text-5xl md:text-7xl font-extrabold text-white tracking-tight leading-[1.1] mb-8">
                        Temukan Karir <br class="hidden md:block"/>
                        <span class="text-transparent bg-clip-text bg-gradient-to-r from-indigo-600 via-indigo-500 to-cyan-500">
                            Impianmu Disini.
                        </span>
                    </h1>
                    
                    <p class="text-lg md:text-xl text-slate-400 mb-12 max-w-2xl mx-auto leading-relaxed">
                        Platform rekrutmen terintegrasi yang menghubungkan talenta terbaik dengan perusahaan terkemuka. Proses mudah, transparan, dan profesional.
                    </p>

                    <!-- Floating Glass Search Bar -->
                    <form action="<?= BASE_URL ?>/pelamar/jobs/browse.php" method="GET" class="glass-card-lg p-2 rounded-full mb-16 max-w-3xl mx-auto flex flex-col md:flex-row gap-2 relative z-20 shadow-[0_20px_40px_rgba(79,70,229,0.15)] animate-fade-in-up delay-200 interactive-card">
                        <div class="flex-grow flex items-center pl-4 pr-2">
                            <svg class="w-6 h-6 text-indigo-500 mr-3 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" /></svg>
                            <input type="text" name="q" placeholder="Posisi, kata kunci, atau perusahaan..." class="w-full bg-transparent border-none text-white focus:ring-0 py-3 text-base font-medium placeholder:text-slate-300 outline-none">
                        </div>
                        <div class="hidden md:block w-[1px] bg-slate-200 my-2"></div>
                        <div class="flex-grow flex items-center pl-4 pr-2">
                            <svg class="w-6 h-6 text-emerald-500 mr-3 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                            <input type="text" name="location" placeholder="Lokasi pekerjaan..." class="w-full bg-transparent border-none text-white focus:ring-0 py-3 text-base font-medium placeholder:text-slate-300 outline-none">
                        </div>
                        <button type="submit" class="btn-primary rounded-full py-3 px-8 text-base shrink-0 justify-center min-w-[140px] shadow-lg shadow-indigo-500/30">
                            Cari Kerja
                        </button>
                    </form>

                    <!-- Trusted Companies Marquee -->
                    <div class="animate-fade-in-up delay-300">
                        <p class="text-sm font-semibold tracking-wider text-slate-400 uppercase mb-6">Dipercaya oleh Perusahaan Terkemuka</p>
                        <div class="flex flex-wrap justify-center items-center gap-8 md:gap-16 opacity-70 grayscale hover:grayscale-0 transition-all duration-500">
                            <div class="text-2xl font-black text-slate-300 flex items-center gap-2"><svg class="w-8 h-8 text-indigo-500" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5"/></svg> GOOGLE</div>
                            <div class="text-2xl font-black text-slate-300 flex items-center gap-2"><svg class="w-8 h-8 text-sky-500" viewBox="0 0 24 24" fill="currentColor"><path d="M22 12c0-5.523-4.477-10-10-10S2 6.477 2 12s4.477 10 10 10 10-4.477 10-10z"/></svg> GOTO</div>
                            <div class="text-2xl font-black text-slate-300 flex items-center gap-2"><svg class="w-8 h-8 text-emerald-500" viewBox="0 0 24 24" fill="currentColor"><path d="M4 4h16v16H4V4z"/></svg> STRIPE</div>
                            <div class="text-2xl font-black text-slate-300 flex items-center gap-2 hidden md:flex"><svg class="w-8 h-8 text-rose-500" viewBox="0 0 24 24" fill="currentColor"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg> TRAVELOKA</div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- ═══════════════════════════════════════════════════════════════
             STATS SECTION
             ═══════════════════════════════════════════════════════════════ -->
        <section class="py-16 border-y border-white/[0.05] bg-slate-900/50 relative z-20">
            <div class="max-w-[1400px] mx-auto px-6">
                <div class="grid grid-cols-2 md:grid-cols-4 gap-8 divide-x divide-white/[0.05]">
                    <div class="text-center reveal">
                        <div class="text-4xl font-extrabold text-white mb-2 tracking-tight">1,000<span class="text-indigo-500">+</span></div>
                        <div class="text-sm font-medium text-slate-400 uppercase tracking-wider">Lowongan Aktif</div>
                    </div>
                    <div class="text-center reveal stagger-1">
                        <div class="text-4xl font-extrabold text-white mb-2 tracking-tight">500<span class="text-indigo-500">+</span></div>
                        <div class="text-sm font-medium text-slate-400 uppercase tracking-wider">Perusahaan</div>
                    </div>
                    <div class="text-center reveal stagger-2">
                        <div class="text-4xl font-extrabold text-white mb-2 tracking-tight">10k<span class="text-indigo-500">+</span></div>
                        <div class="text-sm font-medium text-slate-400 uppercase tracking-wider">Pelamar</div>
                    </div>
                    <div class="text-center reveal stagger-3">
                        <div class="text-4xl font-extrabold text-white mb-2 tracking-tight">24<span class="text-indigo-500">/</span>7</div>
                        <div class="text-sm font-medium text-slate-400 uppercase tracking-wider">Dukungan Sistem</div>
                    </div>
                </div>
            </div>
        </section>

        <!-- ═══════════════════════════════════════════════════════════════
             TRENDING CATEGORIES SECTION
             ═══════════════════════════════════════════════════════════════ -->
        <section class="py-24 px-6 relative z-10 bg-slate-900">
            <div class="max-w-[1400px] mx-auto">
                <div class="text-center mb-16 reveal">
                    <h2 class="text-3xl md:text-4xl font-extrabold text-white mb-4">Kategori Terpopuler</h2>
                    <p class="text-slate-400 text-lg">Temukan peran yang sesuai dengan keahlian Anda</p>
                </div>

                <div class="grid grid-cols-2 md:grid-cols-4 gap-6">
                    <a href="<?= BASE_URL ?>/pelamar/jobs/browse.php?industry=IT%20%26%20Software" class="glass-card p-6 text-center hover-lift group border border-slate-800 hover:border-indigo-500/50 reveal">
                        <div class="w-16 h-16 mx-auto bg-indigo-500/10 rounded-2xl flex items-center justify-center mb-4 group-hover:bg-indigo-500/20 transition-colors">
                            <svg class="w-8 h-8 text-indigo-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4" /></svg>
                        </div>
                        <h3 class="text-lg font-bold text-white mb-1">Teknologi & IT</h3>
                        <p class="text-sm text-slate-400 group-hover:text-indigo-400">120+ Lowongan</p>
                    </a>
                    
                    <a href="<?= BASE_URL ?>/pelamar/jobs/browse.php?industry=Design%20%26%20Creative" class="glass-card p-6 text-center hover-lift group border border-slate-800 hover:border-pink-500/50 reveal stagger-1">
                        <div class="w-16 h-16 mx-auto bg-pink-500/10 rounded-2xl flex items-center justify-center mb-4 group-hover:bg-pink-500/20 transition-colors">
                            <svg class="w-8 h-8 text-pink-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                        </div>
                        <h3 class="text-lg font-bold text-white mb-1">Desain Kreatif</h3>
                        <p class="text-sm text-slate-400 group-hover:text-pink-400">85+ Lowongan</p>
                    </a>

                    <a href="<?= BASE_URL ?>/pelamar/jobs/browse.php?industry=Marketing" class="glass-card p-6 text-center hover-lift group border border-slate-800 hover:border-emerald-500/50 reveal stagger-2">
                        <div class="w-16 h-16 mx-auto bg-emerald-500/10 rounded-2xl flex items-center justify-center mb-4 group-hover:bg-emerald-500/20 transition-colors">
                            <svg class="w-8 h-8 text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M11 3.055A9.001 9.001 0 1020.945 13H11V3.055z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20.488 9H15V3.512A9.025 9.025 0 0120.488 9z" /></svg>
                        </div>
                        <h3 class="text-lg font-bold text-white mb-1">Marketing</h3>
                        <p class="text-sm text-slate-400 group-hover:text-emerald-400">92+ Lowongan</p>
                    </a>

                    <a href="<?= BASE_URL ?>/pelamar/jobs/browse.php?industry=Finance" class="glass-card p-6 text-center hover-lift group border border-slate-800 hover:border-amber-500/50 reveal stagger-3">
                        <div class="w-16 h-16 mx-auto bg-amber-500/10 rounded-2xl flex items-center justify-center mb-4 group-hover:bg-amber-500/20 transition-colors">
                            <svg class="w-8 h-8 text-amber-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                        </div>
                        <h3 class="text-lg font-bold text-white mb-1">Keuangan</h3>
                        <p class="text-sm text-slate-400 group-hover:text-amber-400">45+ Lowongan</p>
                    </a>
                </div>
            </div>
        </section>

        <!-- ═══════════════════════════════════════════════════════════════
             FEATURED JOBS SECTION
             ═══════════════════════════════════════════════════════════════ -->
        <section class="py-24 px-6 relative z-10">
            <div class="max-w-[1400px] mx-auto">
                <div class="flex flex-col md:flex-row justify-between items-end mb-12 reveal">
                    <div class="max-w-2xl">
                        <h2 class="text-3xl font-bold text-white mb-4">Lowongan Terbaru</h2>
                        <p class="text-slate-400">Jelajahi kesempatan karir terbaru dari perusahaan-perusahaan terkemuka.</p>
                    </div>
                    <a href="<?= BASE_URL ?>/pelamar/jobs/browse.php" class="hidden md:flex items-center gap-2 text-indigo-400 font-semibold hover:text-indigo-300 transition-colors group">
                        Lihat Semua Lowongan
                        <svg class="w-5 h-5 group-hover:translate-x-1 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M17 8l4 4m0 0l-4 4m4-4H3" />
                        </svg>
                    </a>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
                    <?php if (empty($featuredJobs)): ?>
                        <div class="col-span-full text-center py-12 glass-card-light rounded-2xl border-dashed border-2 border-slate-700">
                            <p class="text-slate-400">Belum ada lowongan aktif saat ini.</p>
                        </div>
                    <?php else: ?>
                        <?php foreach ($featuredJobs as $index => $job): ?>
                            <!-- Minimal Job Card implementation to avoid dependency error if component is missing -->
                            <div class="glass-card p-6 flex flex-col h-full hover:-translate-y-1 transition-transform duration-300 reveal stagger-<?= $index % 4 ?>">
                                <div class="flex justify-between items-start mb-4">
                                    <div class="w-12 h-12 rounded-xl bg-slate-800 flex items-center justify-center text-xl font-bold text-white border border-slate-700">
                                        <?= strtoupper(substr($job['company_name'], 0, 1)) ?>
                                    </div>
                                    <span class="px-3 py-1 rounded-full text-xs font-semibold bg-indigo-500/10 text-indigo-400 border border-indigo-500/20">
                                        <?= JOB_TYPES[$job['job_type']] ?? $job['job_type'] ?>
                                    </span>
                                </div>
                                
                                <h3 class="text-xl font-bold text-white mb-2 leading-tight"><?= htmlspecialchars($job['title']) ?></h3>
                                <p class="text-slate-400 text-sm font-medium mb-4"><?= htmlspecialchars($job['company_name']) ?></p>
                                
                                <div class="mt-auto space-y-3">
                                    <div class="flex items-center text-sm text-slate-300 gap-2">
                                        <svg class="w-4 h-4 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z" />
                                        </svg>
                                        <?= htmlspecialchars($job['location']) ?>
                                    </div>
                                    
                                    <div class="pt-4 border-t border-slate-700/50 mt-4">
                                        <a href="<?= BASE_URL ?>/pelamar/jobs/detail.php?id=<?= $job['id'] ?>" class="block w-full py-2.5 text-center rounded-lg text-sm font-semibold text-white bg-slate-800 hover:bg-indigo-600 transition-colors border border-slate-700 hover:border-indigo-500">
                                            Lihat Detail
                                        </a>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
                
                <div class="mt-8 text-center md:hidden">
                    <a href="<?= BASE_URL ?>/pelamar/jobs/browse.php" class="inline-flex items-center gap-2 text-indigo-400 font-semibold hover:text-indigo-300 transition-colors">
                        Lihat Semua Lowongan
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M17 8l4 4m0 0l-4 4m4-4H3" />
                        </svg>
                    </a>
                </div>
            </div>
        </section>

        <!-- ═══════════════════════════════════════════════════════════════
             CTA SECTION (EMPLOYERS)
             ═══════════════════════════════════════════════════════════════ -->
        <section class="py-24 px-6 relative z-10">
            <div class="max-w-[1200px] mx-auto rounded-3xl overflow-hidden relative reveal">
                <!-- Background Gradient -->
                <div class="absolute inset-0 bg-gradient-to-br from-indigo-900 to-slate-900"></div>
                <div class="absolute inset-0 bg-[url('<?= BASE_URL ?>/assets/img/noise.png')] opacity-20 mix-blend-overlay"></div>
                <div class="absolute top-0 right-0 w-1/2 h-full bg-gradient-to-l from-indigo-500/20 to-transparent blur-3xl"></div>
                
                <div class="relative z-10 p-12 md:p-16 lg:p-20 flex flex-col md:flex-row items-center justify-between gap-12 border border-white/10 rounded-3xl">
                    <div class="max-w-xl">
                        <h2 class="text-3xl md:text-4xl font-bold text-white mb-4 leading-tight">Mencari Talenta Terbaik untuk Perusahaan Anda?</h2>
                        <p class="text-indigo-100/80 text-lg mb-8">
                            Bergabunglah dengan ratusan perusahaan lainnya yang telah menemukan kandidat berkualitas melalui RecruitPro Enterprise.
                        </p>
                        <ul class="space-y-3 mb-8">
                            <li class="flex items-center gap-3 text-white">
                                <div class="flex-shrink-0 w-6 h-6 rounded-full bg-emerald-500/20 flex items-center justify-center">
                                    <svg class="w-4 h-4 text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                                </div>
                                Pasang lowongan tanpa batas
                            </li>
                            <li class="flex items-center gap-3 text-white">
                                <div class="flex-shrink-0 w-6 h-6 rounded-full bg-emerald-500/20 flex items-center justify-center">
                                    <svg class="w-4 h-4 text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                                </div>
                                Manajemen kandidat terintegrasi
                            </li>
                            <li class="flex items-center gap-3 text-white">
                                <div class="flex-shrink-0 w-6 h-6 rounded-full bg-emerald-500/20 flex items-center justify-center">
                                    <svg class="w-4 h-4 text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                                </div>
                                Keamanan data enterprise-grade
                            </li>
                        </ul>
                        
                        <?php if ($role !== ROLE_HR): ?>
                            <a href="<?= BASE_URL ?>/auth/register.php?role=hr" class="inline-flex items-center justify-center px-8 py-4 text-base font-bold text-indigo-900 bg-white rounded-xl hover:bg-indigo-50 transition-colors shadow-xl hover:-translate-y-1 duration-300">
                                Daftar sebagai HR
                            </a>
                        <?php else: ?>
                            <a href="<?= BASE_URL ?>/hr/dashboard.php" class="inline-flex items-center justify-center px-8 py-4 text-base font-bold text-indigo-900 bg-white rounded-xl hover:bg-indigo-50 transition-colors shadow-xl hover:-translate-y-1 duration-300">
                                Kelola Lowongan
                            </a>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Decorative Element -->
                    <div class="hidden lg:block w-72 h-72 relative">
                        <div class="absolute inset-0 border-2 border-indigo-400/30 rounded-3xl rotate-6 animate-[spin_20s_linear_infinite]"></div>
                        <div class="absolute inset-0 border-2 border-indigo-400/30 rounded-3xl -rotate-6 animate-[spin_25s_linear_infinite_reverse]"></div>
                        <div class="absolute inset-4 glass-card-elevated flex items-center justify-center bg-slate-900/80">
                            <svg class="w-24 h-24 text-indigo-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M21 13.255A23.193 23.193 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                            </svg>
                        </div>
                    </div>
                </div>
            </div>
        </section>

    </main>

    <?php require_once __DIR__ . '/components/footer.php'; ?>
    
    <!-- Simple Intersection Observer for reveal animation -->
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const observerOptions = {
                root: null,
                rootMargin: '0px',
                threshold: 0.1
            };

            const observer = new IntersectionObserver((entries, observer) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        entry.target.style.opacity = '1';
                        entry.target.style.transform = 'translateY(0)';
                        observer.unobserve(entry.target);
                    }
                });
            }, observerOptions);

            document.querySelectorAll('.reveal').forEach((el) => {
                // Initial state
                if (!el.style.opacity) {
                    el.style.opacity = '0';
                    el.style.transform = 'translateY(30px)';
                    el.style.transition = 'opacity 0.6s cubic-bezier(0.4, 0, 0.2, 1), transform 0.6s cubic-bezier(0.4, 0, 0.2, 1)';
                    
                    // Staggering
                    if (el.classList.contains('stagger-1')) el.style.transitionDelay = '100ms';
                    if (el.classList.contains('stagger-2')) el.style.transitionDelay = '200ms';
                    if (el.classList.contains('stagger-3')) el.style.transitionDelay = '300ms';
                }
                observer.observe(el);
            });
        });
    </script>
</body>
</html>



