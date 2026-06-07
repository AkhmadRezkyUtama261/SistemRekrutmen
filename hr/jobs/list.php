<?php
/*
 * @Module:      HR Jobs List
 * @Author:      FE-03 (HR Dashboard UI)
 * @Date:        2026-05-24
 * @Description: List of all jobs posted by the HR user with data table.
 * @Ownership:   FE-03
 */

require_once __DIR__ . '/../../config/app.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../auth/session.php';
require_once __DIR__ . '/../../auth/middleware.php';
require_once __DIR__ . '/../../config/security.php';
require_once __DIR__ . '/../../includes/functions.php';

requireHR();

$pdo = Database::getConnection();

// Get HR ID
$stmt = $pdo->prepare("SELECT id FROM hr_profiles WHERE user_id = :uid");
$stmt->execute(['uid' => $_SESSION['user_id']]);
$hrId = $stmt->fetchColumn();

// Fetch Jobs
$stmt = $pdo->prepare("
    SELECT j.*, 
           (SELECT COUNT(*) FROM applications WHERE job_id = j.id) as applicant_count
    FROM jobs j
    WHERE j.hr_profile_id = :hr_id
    ORDER BY j.created_at DESC
");
$stmt->execute(['hr_id' => $hrId]);
$jobs = $stmt->fetchAll();

// Handle Flash Messages
$flash = getFlashMessage();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Lowongan — <?= APP_NAME ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="<?= BASE_URL ?>/assets/js/theme.js"></script>
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/design-system.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/components.css">
</head>
<body class="bg-slate-900 text-slate-200 font-sans antialiased min-h-screen flex flex-col relative">

    <?php require_once __DIR__ . '/../../components/header.php'; ?>

    <main class="flex-grow max-w-[1400px] mx-auto w-full px-4 sm:px-6 py-8">
        
        <?php if ($flash): ?>
            <div class="mb-6 bg-<?= $flash['type'] === 'success' ? 'emerald' : 'rose' ?>-500/10 border border-<?= $flash['type'] === 'success' ? 'emerald' : 'rose' ?>-500/20 text-<?= $flash['type'] === 'success' ? 'emerald' : 'rose' ?>-400 px-4 py-3 rounded-xl flex items-center gap-3">
                <span class="text-sm font-medium"><?= htmlspecialchars($flash['message']) ?></span>
            </div>
        <?php endif; ?>

        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-8">
            <div>
                <h1 class="text-2xl font-bold text-white">Kelola Lowongan</h1>
                <p class="text-sm text-slate-400">Daftar semua lowongan pekerjaan yang Anda buat.</p>
            </div>
            <a href="<?= BASE_URL ?>/hr/jobs/create.php" class="btn-primary px-5 py-2.5 text-sm">
                + Buat Lowongan Baru
            </a>
        </div>

        <div class="glass-card overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-slate-800/50 text-xs uppercase tracking-wider text-slate-400 border-b border-slate-700/50">
                            <th class="px-6 py-4 font-semibold">Posisi</th>
                            <th class="px-6 py-4 font-semibold">Tipe & Lokasi</th>
                            <th class="px-6 py-4 font-semibold text-center">Pelamar</th>
                            <th class="px-6 py-4 font-semibold">Batas Waktu</th>
                            <th class="px-6 py-4 font-semibold">Status</th>
                            <th class="px-6 py-4 font-semibold text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-700/50">
                        <?php if (empty($jobs)): ?>
                            <tr>
                                <td colspan="6" class="px-6 py-12 text-center text-slate-400">Belum ada lowongan yang dibuat.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($jobs as $job): ?>
                                <tr class="hover:bg-white/[0.02] transition-colors">
                                    <td class="px-6 py-4">
                                        <div class="font-bold text-white text-base mb-1"><?= htmlspecialchars($job['title']) ?></div>
                                        <div class="text-xs text-slate-500">Dibuat: <?= date('d M Y', strtotime($job['created_at'])) ?></div>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-slate-300">
                                        <div class="font-medium"><?= JOB_TYPES[$job['job_type']] ?? $job['job_type'] ?></div>
                                        <div class="text-xs text-slate-500"><?= htmlspecialchars($job['location']) ?></div>
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        <span class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-slate-800 text-sm font-bold text-white border border-slate-700">
                                            <?= $job['applicant_count'] ?>
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-slate-400">
                                        <?= date('d M Y', strtotime($job['deadline'])) ?>
                                    </td>
                                    <td class="px-6 py-4">
                                        <?php
                                        $bg = 'bg-slate-500/20'; $text = 'text-slate-400';
                                        if ($job['status'] === JOB_STATUS_ACTIVE) { $bg = 'bg-emerald-500/20'; $text = 'text-emerald-400'; }
                                        if ($job['status'] === JOB_STATUS_CLOSED) { $bg = 'bg-rose-500/20'; $text = 'text-rose-400'; }
                                        if ($job['status'] === JOB_STATUS_DRAFT) { $bg = 'bg-amber-500/20'; $text = 'text-amber-400'; }
                                        ?>
                                        <span class="px-2.5 py-1 rounded-full text-xs font-semibold <?= $bg ?> <?= $text ?> capitalize">
                                            <?= htmlspecialchars($job['status']) ?>
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-right space-x-2">
                                        <a href="<?= BASE_URL ?>/hr/jobs/edit.php?id=<?= $job['id'] ?>" class="text-indigo-400 hover:text-indigo-300 text-sm font-medium">Edit</a>
                                        <?php if ($job['status'] === JOB_STATUS_ACTIVE): ?>
                                            <span class="text-slate-600">|</span>
                                            <form action="delete.php" method="POST" class="inline" onsubmit="return confirm('Tutup lowongan ini?');">
                                                <?= csrfField() ?>
                                                <input type="hidden" name="id" value="<?= $job['id'] ?>">
                                                <button type="submit" class="text-rose-400 hover:text-rose-300 text-sm font-medium">Tutup</button>
                                            </form>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>

    <?php require_once __DIR__ . '/../../components/footer.php'; ?>
</body>
</html>