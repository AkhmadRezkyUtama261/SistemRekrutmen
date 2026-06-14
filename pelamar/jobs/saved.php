<?php
/*
 * @Feature:     Lowongan Tersimpan (Saved Jobs)
 * @Author:      Muhammad Randyano (Randy)
 * @Description: Menampilkan daftar lowongan kerja yang telah dibookmark oleh pelamar.
 */

require_once __DIR__ . '/../../config/app.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../auth/session.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== ROLE_PELAMAR) {
    header('Location: ' . BASE_URL . '/auth/login.php');
    exit;
}

try {
    // Ambil daftar lowongan yang disimpan oleh user ini
    $stmt = Database::getConnection()->prepare("
        SELECT j.*, h.company_name, sj.created_at as saved_at
        FROM saved_jobs sj
        JOIN pelamar_profiles p ON sj.pelamar_profile_id = p.id
        JOIN jobs j ON sj.job_id = j.id
        JOIN hr_profiles h ON j.hr_profile_id = h.id
        WHERE p.user_id = :uid
        ORDER BY sj.created_at DESC
    ");
    $stmt->execute(['uid' => $_SESSION['user_id']]);
    $savedJobs = $stmt->fetchAll();
} catch (PDOException $e) {
    $savedJobs = [];
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lowongan Tersimpan — <?= APP_NAME ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="<?= BASE_URL ?>/assets/js/theme.js"></script>
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/design-system.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/components.css">
</head>
<body class="bg-slate-900 text-slate-200 font-sans antialiased min-h-screen flex flex-col">
    <?php require_once __DIR__ . '/../../components/header.php'; ?>

    <main class="flex-grow pt-24 pb-12 px-6">
        <div class="max-w-[1000px] mx-auto">
            <div class="mb-10 reveal">
                <h1 class="text-3xl font-bold text-white mb-2">Lowongan Tersimpan</h1>
                <p class="text-slate-400">Daftar pekerjaan yang Anda tandai untuk dilamar nanti.</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <?php if (empty($savedJobs)): ?>
                    <div class="col-span-full text-center py-16 glass-card border-dashed border-2 border-slate-700">
                        <svg class="w-16 h-16 text-slate-600 mx-auto mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z" />
                        </svg>
                        <h3 class="text-xl font-bold text-white mb-2">Belum ada lowongan tersimpan</h3>
                        <p class="text-slate-400 mb-6">Anda belum menandai lowongan apapun. Yuk cari pekerjaan impianmu!</p>
                        <a href="<?= BASE_URL ?>/pelamar/jobs/browse.php" class="btn-primary px-6 py-2 rounded-lg">Cari Lowongan</a>
                    </div>
                <?php else: ?>
                    <?php foreach ($savedJobs as $job): ?>
                        <div class="glass-card p-6 flex flex-col hover:border-indigo-500/50 transition-colors reveal">
                            <div class="flex justify-between items-start mb-4">
                                <div>
                                    <h3 class="text-xl font-bold text-white"><?= htmlspecialchars($job['title']) ?></h3>
                                    <p class="text-indigo-400 font-medium"><?= htmlspecialchars($job['company_name']) ?></p>
                                </div>
                                <span class="bg-indigo-500/10 text-indigo-400 text-xs font-bold px-3 py-1 rounded-full border border-indigo-500/20">
                                    <?= htmlspecialchars($job['job_type']) ?>
                                </span>
                            </div>
                            
                            <div class="text-sm text-slate-400 mb-6 space-y-2">
                                <div class="flex items-center gap-2">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                                    <?= htmlspecialchars($job['location']) ?>
                                </div>
                                <div class="flex items-center gap-2">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                    <?= htmlspecialchars($job['salary_range'] ?? 'Gaji dirahasiakan') ?>
                                </div>
                            </div>

                            <div class="mt-auto pt-4 border-t border-slate-700/50 flex gap-3">
                                <a href="<?= BASE_URL ?>/pelamar/jobs/detail.php?id=<?= $job['id'] ?>" class="flex-1 text-center py-2 rounded-lg bg-indigo-600 hover:bg-indigo-700 text-white font-semibold transition-colors">
                                    Lihat Detail
                                </a>
                                <button onclick="hapusSaved(<?= $job['id'] ?>)" class="px-4 py-2 rounded-lg bg-rose-500/10 hover:bg-rose-500/20 text-rose-500 font-semibold transition-colors border border-rose-500/20">
                                    Hapus
                                </button>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </main>

    <?php require_once __DIR__ . '/../../components/footer.php'; ?>
    <script>
    function hapusSaved(jobId) {
        if(!confirm('Hapus dari daftar simpan?')) return;
        fetch('save.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: 'job_id=' + jobId
        })
        .then(res => res.json())
        .then(data => {
            if(data.status === 'unsaved') window.location.reload();
        });
    }
    </script>
</body>
</html>
