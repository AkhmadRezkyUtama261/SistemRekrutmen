<?php
/*
 * @Module:      Pelamar Applications List
 * @Author:      FE-04 (Pelamar Dashboard UI)
 * @Date:        2026-05-24
 * @Description: Tracker for applicant to view their submitted applications.
 * @Ownership:   FE-04
 */

require_once __DIR__ . '/../../config/app.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../auth/session.php';
require_once __DIR__ . '/../../auth/middleware.php';

requirePelamar();

$pdo = Database::getConnection();

// Get Pelamar Profile ID
$stmt = $pdo->prepare("SELECT id FROM pelamar_profiles WHERE user_id = :uid");
$stmt->execute(['uid' => $_SESSION['user_id']]);
$pelamarId = $stmt->fetchColumn();

// Fetch Applications
$stmt = $pdo->prepare("
    SELECT a.id as app_id, a.current_status, a.applied_at, 
           j.title, j.location, j.job_type, h.company_name
    FROM applications a
    JOIN jobs j ON a.job_id = j.id
    JOIN hr_profiles h ON j.hr_profile_id = h.id
    WHERE a.pelamar_profile_id = :pid
    ORDER BY a.applied_at DESC
");
$stmt->execute(['pid' => $pelamarId]);
$applications = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lamaran Saya — <?= APP_NAME ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="<?= BASE_URL ?>/assets/js/theme.js"></script>
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/design-system.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/components.css">
</head>
<body class="bg-slate-900 text-slate-200 font-sans antialiased min-h-screen flex flex-col relative">

    <?php require_once __DIR__ . '/../../components/header.php'; ?>

    <main class="flex-grow max-w-[1000px] mx-auto w-full px-4 sm:px-6 py-8">
        
        <div class="mb-8">
            <h1 class="text-2xl font-bold text-white mb-2">Riwayat Lamaran</h1>
            <p class="text-sm text-slate-400">Pantau status seluruh lamaran kerja yang telah Anda kirimkan.</p>
        </div>

        <div class="space-y-4">
            <?php if (empty($applications)): ?>
                <div class="glass-card p-12 text-center border-dashed border-2 border-slate-700">
                    <div class="w-16 h-16 bg-slate-800 rounded-full flex items-center justify-center mx-auto mb-4">
                        <svg class="w-8 h-8 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" /></svg>
                    </div>
                    <h3 class="text-lg font-bold text-white mb-2">Belum Ada Lamaran</h3>
                    <p class="text-slate-400 mb-6 text-sm">Anda belum mengirimkan lamaran ke pekerjaan apa pun.</p>
                    <a href="<?= BASE_URL ?>/pelamar/jobs/browse.php" class="btn-primary inline-flex py-2.5 px-6 text-sm">Cari Lowongan Sekarang</a>
                </div>
            <?php else: ?>
                <?php foreach ($applications as $app): ?>
                    <div class="glass-card p-5 sm:p-6 flex flex-col sm:flex-row gap-6 justify-between items-start sm:items-center hover:-translate-y-1 hover:border-indigo-500/30 transition-all duration-300">
                        <div class="flex gap-4 sm:gap-6 w-full sm:w-auto">
                            <!-- Company Logo Placeholder -->
                            <div class="w-14 h-14 rounded-2xl bg-gradient-to-br from-slate-800 to-slate-700 flex items-center justify-center text-xl font-bold text-white shadow-inner shrink-0">
                                <?= strtoupper(substr($app['company_name'], 0, 1)) ?>
                            </div>
                            
                            <div class="flex-grow">
                                <h3 class="text-lg font-bold text-white mb-1"><?= htmlspecialchars($app['title']) ?></h3>
                                <p class="text-sm font-medium text-indigo-400 mb-2"><?= htmlspecialchars($app['company_name']) ?></p>
                                
                                <div class="flex flex-wrap items-center gap-y-2 gap-x-4 text-xs text-slate-400">
                                    <div class="flex items-center gap-1">
                                        <svg class="w-3.5 h-3.5 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z" /></svg>
                                        <?= htmlspecialchars($app['location']) ?>
                                    </div>
                                    <div class="flex items-center gap-1">
                                        <svg class="w-3.5 h-3.5 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                                        Terkirim: <?= date('d M Y', strtotime($app['applied_at'])) ?>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="flex items-center justify-between w-full sm:w-auto sm:flex-col sm:items-end gap-3 shrink-0 pt-4 sm:pt-0 border-t sm:border-0 border-slate-700/50">
                            <?php 
                            $bg = 'bg-slate-500/20'; $text = 'text-slate-400';
                            if ($app['current_status'] === 'applied') { $bg = 'bg-sky-500/20 text-sky-400 border-sky-500/30'; }
                            if ($app['current_status'] === 'under_review') { $bg = 'bg-amber-500/20 text-amber-400 border-amber-500/30'; }
                            if ($app['current_status'] === 'interview') { $bg = 'bg-indigo-500/20 text-indigo-400 border-indigo-500/30'; }
                            if ($app['current_status'] === 'accepted') { $bg = 'bg-emerald-500/20 text-emerald-400 border-emerald-500/30'; }
                            if ($app['current_status'] === 'rejected') { $bg = 'bg-rose-500/20 text-rose-400 border-rose-500/30'; }
                            ?>
                            <div class="px-4 py-1.5 rounded-full text-xs font-bold border <?= $bg ?> uppercase tracking-wider">
                                <?= str_replace('_', ' ', $app['current_status']) ?>
                            </div>
                            <!-- Disabled detail button for now as per requirements, just visual -->
                            <div class="text-sm text-slate-500 font-medium">
                                via Sistem Terpadu
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </main>

    <?php require_once __DIR__ . '/../../components/footer.php'; ?>
</body>
</html>



