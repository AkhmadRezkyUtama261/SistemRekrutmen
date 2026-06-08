<?php
/*
 * @Module:      HR Dashboard
 * @Author:      FE-03 (HR Dashboard UI)
 * @Date:        2026-05-24
 * @Description: Main dashboard for Employer/HR with Bento-Grid layout
 *               and key metrics overview.
 * @Ownership:   FE-03
 *
 * RecruitPro Enterprise — © 2026
 */

require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../auth/session.php';
require_once __DIR__ . '/../auth/middleware.php';
require_once __DIR__ . '/../includes/functions.php';

// Enforce HR role
requireHR();

$userId = $_SESSION['user_id'];
$pdo = Database::getConnection();

// Fetch HR Profile ID
$stmt = $pdo->prepare("SELECT id, company_name FROM hr_profiles WHERE user_id = :uid");
$stmt->execute(['uid' => $userId]);
$hrProfile = $stmt->fetch();

if (!$hrProfile) {
    // Should not happen, but safeguard
    redirect(BASE_URL . '/hr/profile.php');
}

$hrId = $hrProfile['id'];
$companyName = $hrProfile['company_name'];

// ── Metrics Queries ──
// 1. Total Jobs
$stmt = $pdo->prepare("SELECT COUNT(*) FROM jobs WHERE hr_profile_id = :hr_id");
$stmt->execute(['hr_id' => $hrId]);
$totalJobs = $stmt->fetchColumn();

// 2. Active Jobs
$stmt = $pdo->prepare("SELECT COUNT(*) FROM jobs WHERE hr_profile_id = :hr_id AND status = 'active'");
$stmt->execute(['hr_id' => $hrId]);
$activeJobs = $stmt->fetchColumn();

// 3. Total Applicants (across all jobs owned by this HR)
$stmt = $pdo->prepare("
    SELECT COUNT(*) 
    FROM applications a 
    JOIN jobs j ON a.job_id = j.id 
    WHERE j.hr_profile_id = :hr_id
");
$stmt->execute(['hr_id' => $hrId]);
$totalApplicants = $stmt->fetchColumn();

// 4. Candidates to Review
$stmt = $pdo->prepare("
    SELECT COUNT(*) 
    FROM applications a 
    JOIN jobs j ON a.job_id = j.id 
    WHERE j.hr_profile_id = :hr_id AND a.current_status = 'applied'
");
$stmt->execute(['hr_id' => $hrId]);
$toReview = $stmt->fetchColumn();

// ── Recent Applications ──
$stmt = $pdo->prepare("
    SELECT a.id, a.current_status, a.applied_at, 
           p.full_name, j.title as job_title
    FROM applications a
    JOIN jobs j ON a.job_id = j.id
    JOIN pelamar_profiles p ON a.pelamar_profile_id = p.id
    WHERE j.hr_profile_id = :hr_id
    ORDER BY a.applied_at DESC
    LIMIT 5
");
$stmt->execute(['hr_id' => $hrId]);
$recentApps = $stmt->fetchAll();

?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HR Dashboard — <?= APP_NAME ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="<?= BASE_URL ?>/assets/js/theme.js"></script>
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/design-system.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/components.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/animations.css">
</head>
<body class="bg-slate-900 text-slate-200 font-sans antialiased min-h-screen flex flex-col relative">
    
    <!-- Background Accents -->
    <div class="fixed top-[-10%] left-[-10%] w-[40%] h-[40%] bg-indigo-600/10 rounded-full blur-[120px] pointer-events-none"></div>

    <?php require_once __DIR__ . '/../components/header.php'; ?>

    <main class="flex-grow max-w-[1400px] mx-auto w-full px-4 sm:px-6 py-8">
        
        <!-- Header -->
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-8">
            <div>
                <h1 class="text-2xl font-bold text-white">Halo, <?= htmlspecialchars($companyName) ?></h1>
                <p class="text-sm text-slate-400">Berikut adalah ringkasan aktivitas rekrutmen Anda hari ini.</p>
            </div>
            <div class="flex gap-3">
                <a href="<?= BASE_URL ?>/hr/jobs/create.php" class="btn-primary px-5 py-2.5 text-sm">
                    <svg class="w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                    </svg>
                    Buat Lowongan
                </a>
            </div>
        </div>

        <!-- ── Bento Grid Layout ── -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            
            <!-- Stat Card 1 -->
            <div class="glass-card p-6 reveal interactive-card">
                <div class="flex items-start justify-between">
                    <div>
                        <p class="text-sm font-medium text-slate-400 mb-1">Total Pelamar</p>
                        <h3 class="text-3xl font-bold text-white"><?= number_format($totalApplicants) ?></h3>
                    </div>
                    <div class="w-10 h-10 rounded-lg bg-indigo-500/20 flex items-center justify-center text-indigo-600 dark:text-indigo-400">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Stat Card 2 -->
            <div class="glass-card p-6 reveal interactive-card stagger-1">
                <div class="flex items-start justify-between">
                    <div>
                        <p class="text-sm font-medium text-slate-400 mb-1">Perlu Direview</p>
                        <h3 class="text-3xl font-bold text-amber-600 dark:text-amber-400"><?= number_format($toReview) ?></h3>
                    </div>
                    <div class="w-10 h-10 rounded-lg bg-amber-500/20 flex items-center justify-center text-amber-600 dark:text-amber-400">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Stat Card 3 -->
            <div class="glass-card p-6 reveal interactive-card stagger-2">
                <div class="flex items-start justify-between">
                    <div>
                        <p class="text-sm font-medium text-slate-400 mb-1">Lowongan Aktif</p>
                        <h3 class="text-3xl font-bold text-emerald-600 dark:text-emerald-400"><?= number_format($activeJobs) ?></h3>
                    </div>
                    <div class="w-10 h-10 rounded-lg bg-emerald-500/20 flex items-center justify-center text-emerald-600 dark:text-emerald-400">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.193 23.193 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Stat Card 4 -->
            <div class="glass-card p-6 reveal interactive-card stagger-3">
                <div class="flex items-start justify-between">
                    <div>
                        <p class="text-sm font-medium text-slate-400 mb-1">Total Lowongan</p>
                        <h3 class="text-3xl font-bold text-white"><?= number_format($totalJobs) ?></h3>
                    </div>
                    <div class="w-10 h-10 rounded-lg bg-slate-700/50 flex items-center justify-center text-slate-300">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                        </svg>
                    </div>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- ── Recent Applications Table ── -->
            <div class="lg:col-span-2 glass-card p-0 overflow-hidden reveal">
                <div class="p-6 border-b border-slate-700/50 flex justify-between items-center">
                    <h3 class="text-lg font-bold text-white">Pelamar Terbaru</h3>
                    <a href="<?= BASE_URL ?>/hr/applicants/list.php" class="text-sm text-indigo-600 dark:text-indigo-400 hover:text-indigo-300 font-medium">Lihat Semua</a>
                </div>
                
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-slate-800/30 text-xs uppercase tracking-wider text-slate-400 border-b border-slate-700/50">
                                <th class="px-6 py-4 font-semibold">Nama Kandidat</th>
                                <th class="px-6 py-4 font-semibold">Posisi</th>
                                <th class="px-6 py-4 font-semibold">Tanggal</th>
                                <th class="px-6 py-4 font-semibold">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-700/50">
                            <?php if (empty($recentApps)): ?>
                                <tr>
                                    <td colspan="4" class="px-6 py-8 text-center text-slate-400">Belum ada data pelamar.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($recentApps as $app): ?>
                                    <tr class="border-b border-slate-700/50 last:border-0 hover:bg-white/[0.05] transition-colors cursor-pointer group" onclick="window.location.href='<?= BASE_URL ?>/hr/applicants/list.php'">
                                        <td class="px-6 py-4">
                                            <div class="font-bold text-white group-hover:text-indigo-600 dark:text-indigo-400 transition-colors"><?= htmlspecialchars($app['full_name']) ?></div>
                                        </td>
                                        <td class="px-6 py-4 text-sm text-slate-300">
                                            <?= htmlspecialchars($app['job_title']) ?>
                                        </td>
                                        <td class="px-6 py-4 text-sm text-slate-400">
                                            <?= date('d M Y', strtotime($app['applied_at'])) ?>
                                        </td>
                                        <td class="px-6 py-4">
                                            <div class="flex items-center justify-between">
                                                <?php 
                                                // Simple badge logic
                                                $bg = 'bg-slate-500/20'; $text = 'text-slate-400';
                                                if ($app['current_status'] === 'applied') { $bg = 'bg-sky-500/20'; $text = 'text-sky-600 dark:text-sky-400'; }
                                                if ($app['current_status'] === 'under_review') { $bg = 'bg-amber-500/20'; $text = 'text-amber-600 dark:text-amber-400'; }
                                                if ($app['current_status'] === 'interview') { $bg = 'bg-indigo-500/20'; $text = 'text-indigo-600 dark:text-indigo-400'; }
                                                if ($app['current_status'] === 'accepted') { $bg = 'bg-emerald-500/20'; $text = 'text-emerald-600 dark:text-emerald-400'; }
                                                if ($app['current_status'] === 'rejected') { $bg = 'bg-rose-500/20'; $text = 'text-rose-600 dark:text-rose-400'; }
                                                ?>
                                                <span class="px-2.5 py-1 rounded-full text-xs font-semibold <?= $bg ?> <?= $text ?> capitalize">
                                                    <?= str_replace('_', ' ', $app['current_status']) ?>
                                                </span>
                                                <svg class="w-4 h-4 text-slate-500 opacity-0 group-hover:opacity-100 transition-opacity" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- ── Quick Actions ── -->
            <div class="glass-card p-6 reveal interactive-card stagger-1">
                <h3 class="text-lg font-bold text-white mb-6">Aksi Cepat</h3>
                
                <div class="space-y-4">
                    <a href="<?= BASE_URL ?>/hr/jobs/create.php" class="flex items-center gap-4 p-4 rounded-xl border border-slate-700/50 hover:bg-white/5 hover:border-indigo-500/30 transition-all group">
                        <div class="w-10 h-10 rounded-lg bg-indigo-500/10 flex items-center justify-center text-indigo-600 dark:text-indigo-400 group-hover:bg-indigo-500/20">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.5v15m7.5-7.5h-15" />
                            </svg>
                        </div>
                        <div>
                            <p class="text-sm font-semibold text-white">Pasang Lowongan</p>
                            <p class="text-xs text-slate-400">Buat deskripsi pekerjaan baru</p>
                        </div>
                    </a>
                    
                    <a href="<?= BASE_URL ?>/hr/jobs/list.php" class="flex items-center gap-4 p-4 rounded-xl border border-slate-700/50 hover:bg-white/5 hover:border-indigo-500/30 transition-all group">
                        <div class="w-10 h-10 rounded-lg bg-slate-700/30 flex items-center justify-center text-slate-300 group-hover:bg-slate-700/50">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16" />
                            </svg>
                        </div>
                        <div>
                            <p class="text-sm font-semibold text-white">Kelola Lowongan</p>
                            <p class="text-xs text-slate-400">Edit atau tutup lowongan aktif</p>
                        </div>
                    </a>
                    
                    <a href="<?= BASE_URL ?>/hr/profile.php" class="flex items-center gap-4 p-4 rounded-xl border border-slate-700/50 hover:bg-white/5 hover:border-indigo-500/30 transition-all group">
                        <div class="w-10 h-10 rounded-lg bg-slate-700/30 flex items-center justify-center text-slate-300 group-hover:bg-slate-700/50">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                            </svg>
                        </div>
                        <div>
                            <p class="text-sm font-semibold text-white">Profil Perusahaan</p>
                            <p class="text-xs text-slate-400">Perbarui informasi perusahaan</p>
                        </div>
                    </a>
                </div>
            </div>
        </div>

    </main>

    <?php require_once __DIR__ . '/../components/footer.php'; ?>
</body>
</html>




