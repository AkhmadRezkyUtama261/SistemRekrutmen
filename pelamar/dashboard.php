<?php
/*
 * @Module:      Pelamar Dashboard
 * @Author:      FE-04 (Pelamar Dashboard UI)
 * @Date:        2026-05-24
 * @Description: Dashboard pencari kerja dengan pelacakan lamaran.
 * @Ownership:   FE-04
 *
 * RecruitPro Enterprise — © 2026
 */

require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../auth/session.php';
require_once __DIR__ . '/../auth/middleware.php';
require_once __DIR__ . '/../includes/functions.php';

// Enforce Pelamar role
requirePelamar();

$userId = $_SESSION['user_id'];
$pdo = Database::getConnection();

// Fetch Profile
$stmt = $pdo->prepare("SELECT * FROM pelamar_profiles WHERE user_id = :uid");
$stmt->execute(['uid' => $userId]);
$profile = $stmt->fetch();

if (!$profile) {
    redirect(BASE_URL . '/pelamar/profile/edit.php');
}

$pelamarId = $profile['id'];

// ── Metrics ──
// 1. Total Applications
$stmt = $pdo->prepare("SELECT COUNT(*) FROM applications WHERE pelamar_profile_id = :pid");
$stmt->execute(['pid' => $pelamarId]);
$totalApps = $stmt->fetchColumn();

// 2. Under Review
$stmt = $pdo->prepare("SELECT COUNT(*) FROM applications WHERE pelamar_profile_id = :pid AND current_status = 'under_review'");
$stmt->execute(['pid' => $pelamarId]);
$inReview = $stmt->fetchColumn();

// 3. Interview Scheduled
$stmt = $pdo->prepare("SELECT COUNT(*) FROM applications WHERE pelamar_profile_id = :pid AND current_status = 'interview'");
$stmt->execute(['pid' => $pelamarId]);
$interviews = $stmt->fetchColumn();

// Profile Completion Calculation (Simple logic)
$completion = 0;
if (!empty($profile['full_name'])) $completion += 20;
if (!empty($profile['phone'])) $completion += 10;
if (!empty($profile['address'])) $completion += 10;
if (!empty($profile['education_level'])) $completion += 20;
if (!empty($profile['skills'])) $completion += 10;
if (!empty($profile['cv_file_path'])) $completion += 30;

// ── Recent Applications ──
$stmt = $pdo->prepare("
    SELECT a.id, a.current_status, a.applied_at, 
           j.title, h.company_name
    FROM applications a
    JOIN jobs j ON a.job_id = j.id
    JOIN hr_profiles h ON j.hr_profile_id = h.id
    WHERE a.pelamar_profile_id = :pid
    ORDER BY a.applied_at DESC
    LIMIT 5
");
$stmt->execute(['pid' => $pelamarId]);
$recentApps = $stmt->fetchAll();

// ── Saved Jobs ──
$stmt = $pdo->prepare("
    SELECT s.id as save_id, j.id as job_id, j.title, j.location, h.company_name, j.job_type 
    FROM saved_jobs s
    JOIN jobs j ON s.job_id = j.id
    JOIN hr_profiles h ON j.hr_profile_id = h.id
    WHERE s.pelamar_profile_id = :pid
    ORDER BY s.saved_at DESC
    LIMIT 3
");
$stmt->execute(['pid' => $pelamarId]);
$savedJobs = $stmt->fetchAll();

// ── Recommended Jobs ──
// Simple recommendation: jobs that match user's skills loosely, or just latest active jobs if skills are empty
$recQuery = "
    SELECT j.id, j.title, j.location, j.salary_range, h.company_name, j.job_type 
    FROM jobs j
    JOIN hr_profiles h ON j.hr_profile_id = h.id
    WHERE j.status = 'active' 
    AND j.id NOT IN (SELECT job_id FROM applications WHERE pelamar_profile_id = :pid)
";
$params = ['pid' => $pelamarId];

if (!empty($profile['skills'])) {
    $skills = explode(',', $profile['skills']);
    $skillCondition = [];
    foreach ($skills as $i => $skill) {
        $trimmedSkill = trim($skill);
        if (empty($trimmedSkill)) continue;
        $skillCondition[] = "(j.requirements LIKE :reqSkill$i OR j.title LIKE :titleSkill$i)";
        $params["reqSkill$i"] = '%' . $trimmedSkill . '%';
        $params["titleSkill$i"] = '%' . $trimmedSkill . '%';
    }
    if (!empty($skillCondition)) {
        $recQuery .= " AND (" . implode(" OR ", $skillCondition) . ")";
    }
}
$recQuery .= " ORDER BY j.created_at DESC LIMIT 4";
$stmt = $pdo->prepare($recQuery);
$stmt->execute($params);
$recommendedJobs = $stmt->fetchAll();

?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Pelamar — <?= APP_NAME ?></title>
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
    <div class="fixed top-[-10%] right-[-10%] w-[40%] h-[40%] bg-indigo-600/10 rounded-full blur-[120px] pointer-events-none"></div>

    <?php require_once __DIR__ . '/../components/header.php'; ?>

    <main class="flex-grow max-w-[1400px] mx-auto w-full px-4 sm:px-6 py-8">
        
        <!-- Welcome Banner -->
        <div class="glass-card border-l-4 border-l-indigo-500 p-6 sm:p-8 mb-8 reveal relative overflow-hidden">
            <div class="absolute right-0 top-0 w-64 h-full bg-gradient-to-l from-indigo-500/10 to-transparent pointer-events-none"></div>
            
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-6 relative z-10">
                <div>
                    <h1 class="text-2xl sm:text-3xl font-bold text-white mb-2">Halo, <?= htmlspecialchars($profile['full_name']) ?> 👋</h1>
                    <p class="text-slate-400">Siap untuk langkah karir Anda selanjutnya? Temukan dan lamar pekerjaan impian Anda.</p>
                </div>
                
                <!-- Profile Completeness -->
                <div class="w-full md:w-auto flex items-center gap-4 bg-slate-900/50 p-4 rounded-2xl border border-slate-700/50">
                    <div class="relative w-16 h-16 shrink-0">
                        <svg class="w-16 h-16 transform -rotate-90">
                            <circle class="text-slate-800" stroke-width="5" stroke="currentColor" fill="transparent" r="28" cx="32" cy="32"/>
                            <circle class="<?= $completion == 100 ? 'text-emerald-500' : 'text-indigo-500' ?>" stroke-width="5" stroke-dasharray="176" stroke-dashoffset="<?= 176 - (176 * $completion / 100) ?>" stroke-linecap="round" stroke="currentColor" fill="transparent" r="28" cx="32" cy="32" style="transition: stroke-dashoffset 1.5s ease-in-out"/>
                        </svg>
                        <div class="absolute inset-0 flex items-center justify-center">
                            <span class="text-xs font-bold <?= $completion == 100 ? 'text-emerald-400' : 'text-indigo-400' ?>"><?= $completion ?>%</span>
                        </div>
                    </div>
                    <div>
                        <p class="text-slate-300 font-bold text-sm mb-0.5">Kelengkapan Profil</p>
                        <?php if ($completion < 100): ?>
                            <a href="<?= BASE_URL ?>/pelamar/profile/edit.php" class="text-xs text-indigo-400 hover:text-indigo-300 font-medium">Lengkapi sekarang &rarr;</a>
                        <?php else: ?>
                            <span class="text-xs text-emerald-500/80 font-medium flex items-center gap-1">
                                <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                Profil Sempurna
                            </span>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- ── Smart Matches (Recommended) ── -->
        <?php if (!empty($recommendedJobs)): ?>
        <div class="mb-8 reveal">
            <div class="flex justify-between items-end mb-4">
                <div>
                    <h2 class="text-xl font-bold text-white mb-1">Rekomendasi Pintar</h2>
                    <p class="text-sm text-slate-400">Lowongan yang cocok dengan profil & keahlian Anda</p>
                </div>
            </div>
            <div class="flex gap-4 overflow-x-auto pb-4 hide-scrollbar snap-x">
                <?php foreach ($recommendedJobs as $rec): ?>
                    <a href="<?= BASE_URL ?>/pelamar/jobs/detail.php?id=<?= $rec['id'] ?>" class="glass-card interactive-card p-5 min-w-[280px] sm:min-w-[320px] max-w-[320px] flex-shrink-0 snap-start group border border-slate-700/50 hover:border-emerald-500/50">
                        <div class="flex justify-between items-start mb-4">
                            <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-emerald-500/20 to-emerald-600/10 flex items-center justify-center text-xl font-bold text-emerald-400 border border-emerald-500/20">
                                <?= strtoupper(substr($rec['company_name'], 0, 1)) ?>
                            </div>
                            <span class="px-2 py-1 rounded-md text-xs font-semibold bg-indigo-500/10 text-indigo-400 border border-indigo-500/20">
                                Match
                            </span>
                        </div>
                        <h3 class="text-lg font-bold text-white mb-1 truncate"><?= htmlspecialchars($rec['title']) ?></h3>
                        <p class="text-sm text-slate-400 mb-4 truncate"><?= htmlspecialchars($rec['company_name']) ?></p>
                        <div class="flex items-center justify-between text-xs font-medium text-slate-400">
                            <div class="flex items-center gap-1">
                                <svg class="w-4 h-4 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z" /></svg>
                                <?= htmlspecialchars($rec['location']) ?>
                            </div>
                            <span class="text-emerald-400 group-hover:text-emerald-300 transition-colors">Lamar &rarr;</span>
                        </div>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>
        <style>.hide-scrollbar::-webkit-scrollbar { display: none; } .hide-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }</style>
        <?php endif; ?>

        <!-- ── Metrics Grid ── -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-6 mb-8">
            <div class="glass-card p-6 reveal interactive-card">
                <p class="text-sm font-medium text-slate-400 mb-1">Total Lamaran</p>
                <div class="flex items-end gap-3">
                    <h3 class="text-4xl font-extrabold text-white leading-none"><?= number_format($totalApps) ?></h3>
                </div>
            </div>
            
            <div class="glass-card p-6 reveal interactive-card stagger-1">
                <p class="text-sm font-medium text-slate-400 mb-1">Sedang Direview</p>
                <div class="flex items-end gap-3">
                    <h3 class="text-4xl font-extrabold text-amber-400 leading-none"><?= number_format($inReview) ?></h3>
                </div>
            </div>
            
            <div class="glass-card p-6 reveal interactive-card stagger-2">
                <p class="text-sm font-medium text-slate-400 mb-1">Panggilan Wawancara</p>
                <div class="flex items-end gap-3">
                    <h3 class="text-4xl font-extrabold text-indigo-400 leading-none"><?= number_format($interviews) ?></h3>
                </div>
            </div>
        </div>

        <div class="flex flex-col lg:flex-row gap-6">
            <!-- ── Left Column (Main Content) ── -->
            <div class="lg:w-2/3 flex flex-col gap-6">
                <!-- ── Recent Applications ── -->
                <div class="glass-card p-0 overflow-hidden reveal">
                <div class="p-6 border-b border-slate-700/50 flex justify-between items-center">
                    <h3 class="text-lg font-bold text-white">Aktivitas Lamaran Terakhir</h3>
                    <a href="<?= BASE_URL ?>/pelamar/applications/list.php" class="text-sm text-indigo-400 hover:text-indigo-300 font-medium">Lihat Semua</a>
                </div>
                
                <div class="divide-y divide-slate-700/50">
                    <?php if (empty($recentApps)): ?>
                        <div class="p-12 text-center">
                            <svg class="w-12 h-12 text-slate-600 mx-auto mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
                            </svg>
                            <p class="text-slate-400 mb-4">Anda belum melamar pekerjaan apa pun.</p>
                            <a href="<?= BASE_URL ?>/pelamar/jobs/browse.php" class="btn-primary text-sm px-6 py-2">Cari Lowongan</a>
                        </div>
                    <?php else: ?>
                        <?php foreach ($recentApps as $app): ?>
                            <div class="p-5 hover:bg-white/[0.02] transition-colors flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                                <div class="flex items-start gap-4">
                                    <div class="w-10 h-10 rounded-lg bg-slate-800 flex items-center justify-center text-white font-bold shrink-0 border border-slate-700">
                                        <?= strtoupper(substr($app['company_name'], 0, 1)) ?>
                                    </div>
                                    <div>
                                        <h4 class="text-base font-semibold text-white"><?= htmlspecialchars($app['title']) ?></h4>
                                        <p class="text-sm text-slate-400"><?= htmlspecialchars($app['company_name']) ?> • <?= date('d M Y', strtotime($app['applied_at'])) ?></p>
                                    </div>
                                </div>
                                <div class="flex items-center gap-4 sm:ml-auto">
                                    <?php 
                                    $bg = 'bg-slate-500/20'; $text = 'text-slate-400';
                                    if ($app['current_status'] === 'applied') { $bg = 'bg-sky-500/20'; $text = 'text-sky-400'; }
                                    if ($app['current_status'] === 'under_review') { $bg = 'bg-amber-500/20'; $text = 'text-amber-400'; }
                                    if ($app['current_status'] === 'interview') { $bg = 'bg-indigo-500/20'; $text = 'text-indigo-400'; }
                                    if ($app['current_status'] === 'accepted') { $bg = 'bg-emerald-500/20'; $text = 'text-emerald-400'; }
                                    if ($app['current_status'] === 'rejected') { $bg = 'bg-rose-500/20'; $text = 'text-rose-400'; }
                                    ?>
                                    <span class="px-3 py-1 rounded-full text-xs font-semibold <?= $bg ?> <?= $text ?> capitalize shrink-0">
                                        <?= str_replace('_', ' ', $app['current_status']) ?>
                                    </span>
                                    <a href="<?= BASE_URL ?>/pelamar/applications/list.php" class="text-slate-400 hover:text-white p-2" title="Lihat Riwayat Lamaran">
                                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                        </svg>
                                    </a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div> <!-- End Recent Apps glass-card -->

            <!-- ── Saved Jobs ── -->
            <div class="glass-card p-0 overflow-hidden reveal stagger-1">
                <div class="p-6 border-b border-slate-700/50 flex justify-between items-center">
                    <h3 class="text-lg font-bold text-white flex items-center gap-2">
                        <svg class="w-5 h-5 text-rose-500 fill-rose-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" /></svg>
                        Lowongan Tersimpan
                    </h3>
                </div>
                
                <div class="divide-y divide-slate-700/50">
                    <?php if (empty($savedJobs)): ?>
                        <div class="p-8 text-center">
                            <p class="text-slate-400 text-sm">Belum ada lowongan yang Anda simpan.</p>
                        </div>
                    <?php else: ?>
                        <?php foreach ($savedJobs as $saved): ?>
                            <div class="p-5 hover:bg-white/[0.02] transition-colors flex items-center justify-between gap-4">
                                <div>
                                    <h4 class="text-base font-semibold text-white"><a href="<?= BASE_URL ?>/pelamar/jobs/detail.php?id=<?= $saved['job_id'] ?>" class="hover:text-indigo-400 transition-colors"><?= htmlspecialchars($saved['title']) ?></a></h4>
                                    <p class="text-sm text-slate-400 mt-1"><?= htmlspecialchars($saved['company_name']) ?> • <?= htmlspecialchars($saved['location']) ?></p>
                                </div>
                                <a href="<?= BASE_URL ?>/pelamar/jobs/detail.php?id=<?= $saved['job_id'] ?>" class="btn-primary py-2 px-4 text-sm whitespace-nowrap">Lihat Detail</a>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div> <!-- End Saved Jobs glass-card -->
        </div> <!-- End Left Column (w-2/3) -->

        <!-- ── Right Column (Sidebar) ── -->
        <div class="lg:w-1/3 flex flex-col gap-6">
            <!-- ── Quick Actions ── -->
            <div class="glass-card p-6 reveal interactive-card stagger-1">
                <h3 class="text-lg font-bold text-white mb-6">Aksi Cepat</h3>
                
                <div class="space-y-4">
                    <a href="<?= BASE_URL ?>/pelamar/jobs/browse.php" class="flex items-center gap-4 p-4 rounded-xl border border-slate-700/50 hover:bg-white/5 hover:border-indigo-500/30 transition-all group">
                        <div class="w-10 h-10 rounded-lg bg-indigo-500/10 flex items-center justify-center text-indigo-400 group-hover:bg-indigo-500/20">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" />
                            </svg>
                        </div>
                        <div>
                            <p class="text-sm font-semibold text-white">Cari Lowongan</p>
                            <p class="text-xs text-slate-400">Jelajahi pekerjaan terbaru</p>
                        </div>
                    </a>
                    
                    <a href="<?= BASE_URL ?>/pelamar/profile/edit.php" class="flex items-center gap-4 p-4 rounded-xl border border-slate-700/50 hover:bg-white/5 hover:border-indigo-500/30 transition-all group">
                        <div class="w-10 h-10 rounded-lg bg-slate-700/30 flex items-center justify-center text-slate-300 group-hover:bg-slate-700/50">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                            </svg>
                        </div>
                        <div>
                            <p class="text-sm font-semibold text-white">Perbarui Profil</p>
                            <p class="text-xs text-slate-400">Tingkatkan peluang diterima</p>
                        </div>
                    </a>
                </div>
            </div>

        </div>
    </div>

    </main>

    <?php require_once __DIR__ . '/../components/footer.php'; ?>
</body>
</html>




