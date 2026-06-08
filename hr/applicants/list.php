<?php
/*
 * @Module:      HR Applicants List
 * @Author:      FE-03 (HR Dashboard UI)
 * @Date:        2026-05-24
 * @Description: Table view of all applicants for the HR's job postings.
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

// Filters
$jobFilter = $_GET['job_id'] ?? '';
$statusFilter = $_GET['status'] ?? '';

// Build Query
$where = ["j.hr_profile_id = :hr_id"];
$params = ['hr_id' => $hrId];

if (!empty($jobFilter)) {
    $where[] = "j.id = :jid";
    $params['jid'] = $jobFilter;
}
if (!empty($statusFilter)) {
    $where[] = "a.current_status = :status";
    $params['status'] = $statusFilter;
}

$whereStr = implode(" AND ", $where);

// Fetch Applications
$sql = "
    SELECT a.id as app_id, a.current_status, a.applied_at, 
           p.full_name, p.cv_file_path, 
           j.title as job_title, j.id as job_id
    FROM applications a
    JOIN jobs j ON a.job_id = j.id
    JOIN pelamar_profiles p ON a.pelamar_profile_id = p.id
    WHERE $whereStr
    ORDER BY a.applied_at DESC
";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$applicants = $stmt->fetchAll();

// Fetch HR's Jobs for Dropdown Filter
$stmtJobs = $pdo->prepare("SELECT id, title FROM jobs WHERE hr_profile_id = :hr_id ORDER BY created_at DESC");
$stmtJobs->execute(['hr_id' => $hrId]);
$hrJobsList = $stmtJobs->fetchAll();

$flash = getFlashMessage();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Kandidat — <?= APP_NAME ?></title>
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

        <div class="flex flex-col md:flex-row md:items-end justify-between gap-6 mb-8">
            <div>
                <h1 class="text-2xl font-bold text-white mb-2">Kelola Kandidat</h1>
                <p class="text-sm text-slate-400">Review profil pelamar dan ubah status lamaran mereka di sini.</p>
            </div>
            
            <!-- Filters -->
            <form action="" method="GET" class="flex flex-col sm:flex-row gap-3 w-full md:w-auto">
                <select name="job_id" class="bg-slate-900/50 border border-slate-700 rounded-xl px-4 py-2.5 text-sm text-slate-200 focus:outline-none focus:border-indigo-500">
                    <option value="">Semua Lowongan</option>
                    <?php foreach($hrJobsList as $j): ?>
                        <option value="<?= $j['id'] ?>" <?= $jobFilter == $j['id'] ? 'selected' : '' ?>><?= htmlspecialchars($j['title']) ?></option>
                    <?php endforeach; ?>
                </select>
                <select name="status" class="bg-slate-900/50 border border-slate-700 rounded-xl px-4 py-2.5 text-sm text-slate-200 focus:outline-none focus:border-indigo-500">
                    <option value="">Semua Status</option>
                    <option value="applied" <?= $statusFilter === 'applied' ? 'selected' : '' ?>>Baru Melamar</option>
                    <option value="under_review" <?= $statusFilter === 'under_review' ? 'selected' : '' ?>>Sedang Direview</option>
                    <option value="interview" <?= $statusFilter === 'interview' ? 'selected' : '' ?>>Wawancara</option>
                    <option value="accepted" <?= $statusFilter === 'accepted' ? 'selected' : '' ?>>Diterima</option>
                    <option value="rejected" <?= $statusFilter === 'rejected' ? 'selected' : '' ?>>Ditolak</option>
                </select>
                <button type="submit" class="btn-primary px-4 py-2.5 text-sm shrink-0">Filter</button>
            </form>
        </div>

        <div class="glass-card overflow-hidden relative">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse min-w-[800px]">
                    <thead>
                        <tr class="bg-slate-800/50 text-xs uppercase tracking-wider text-slate-400 border-b border-slate-700/50">
                            <th class="px-6 py-4 font-semibold">Kandidat</th>
                            <th class="px-6 py-4 font-semibold">Melamar Untuk</th>
                            <th class="px-6 py-4 font-semibold text-center">Status Saat Ini</th>
                            <th class="px-6 py-4 font-semibold text-center">Aksi & Update Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-700/50">
                        <?php if (empty($applicants)): ?>
                            <tr>
                                <td colspan="4" class="px-6 py-12 text-center text-slate-400">Belum ada kandidat yang sesuai kriteria.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($applicants as $app): ?>
                                <tr class="hover:bg-white/[0.02] transition-colors">
                                    <td class="px-6 py-4">
                                        <div class="flex items-center gap-4">
                                            <div class="w-10 h-10 rounded-full bg-slate-800 flex items-center justify-center text-white font-bold border border-slate-700 shrink-0">
                                                <?= strtoupper(substr($app['full_name'], 0, 1)) ?>
                                            </div>
                                            <div>
                                                <div class="font-bold text-white mb-1"><?= htmlspecialchars($app['full_name']) ?></div>
                                                <?php if (!empty($app['cv_file_path'])): ?>
                                                    <a href="<?= BASE_URL ?>/uploads/cv/<?= htmlspecialchars($app['cv_file_path']) ?>" target="_blank" class="text-xs font-semibold text-indigo-400 hover:underline flex items-center gap-1">
                                                        <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                                                        Lihat CV
                                                    </a>
                                                <?php else: ?>
                                                    <span class="text-xs text-rose-400">Tidak ada CV</span>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="text-sm font-medium text-slate-200"><?= htmlspecialchars($app['job_title']) ?></div>
                                        <div class="text-xs text-slate-500 mt-1">Tanggal: <?= date('d M Y', strtotime($app['applied_at'])) ?></div>
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        <?php 
                                        $bg = 'bg-slate-500/20'; $text = 'text-slate-400';
                                        if ($app['current_status'] === 'applied') { $bg = 'bg-sky-500/20 text-sky-400 border-sky-500/30'; }
                                        if ($app['current_status'] === 'under_review') { $bg = 'bg-amber-500/20 text-amber-400 border-amber-500/30'; }
                                        if ($app['current_status'] === 'interview') { $bg = 'bg-indigo-500/20 text-indigo-400 border-indigo-500/30'; }
                                        if ($app['current_status'] === 'accepted') { $bg = 'bg-emerald-500/20 text-emerald-400 border-emerald-500/30'; }
                                        if ($app['current_status'] === 'rejected') { $bg = 'bg-rose-500/20 text-rose-400 border-rose-500/30'; }
                                        ?>
                                        <div class="inline-block px-3 py-1.5 rounded-full text-xs font-bold border <?= $bg ?> uppercase tracking-wider">
                                            <?= str_replace('_', ' ', $app['current_status']) ?>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        <form action="status_update.php" method="POST" class="flex justify-center items-center gap-2">
                                            <?= csrfField() ?>
                                            <input type="hidden" name="application_id" value="<?= $app['app_id'] ?>">
                                            <select name="new_status" class="bg-slate-900 border border-slate-700 rounded-lg px-3 py-1.5 text-sm text-slate-300 focus:outline-none focus:border-indigo-500 appearance-none min-w-[140px]">
                                                <option value="applied" <?= $app['current_status'] == 'applied' ? 'selected' : '' ?>>Baru Melamar</option>
                                                <option value="under_review" <?= $app['current_status'] == 'under_review' ? 'selected' : '' ?>>Review CV</option>
                                                <option value="interview" <?= $app['current_status'] == 'interview' ? 'selected' : '' ?>>Panggil Wawancara</option>
                                                <option value="accepted" <?= $app['current_status'] == 'accepted' ? 'selected' : '' ?>>Terima</option>
                                                <option value="rejected" <?= $app['current_status'] == 'rejected' ? 'selected' : '' ?>>Tolak</option>
                                            </select>
                                            <button type="submit" class="bg-indigo-600 hover:bg-indigo-500 text-white p-1.5 rounded-lg transition-colors">
                                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                                            </button>
                                        </form>
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



