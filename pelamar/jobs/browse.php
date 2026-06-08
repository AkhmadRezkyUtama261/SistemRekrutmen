<?php
/*
 * @Module:      Browse Jobs Page
 * @Author:      FE-04 (Pelamar Dashboard UI)
 * @Date:        2026-05-24
 * @Description: Public/Pelamar job listing with filters and search.
 * @Ownership:   FE-04
 */

require_once __DIR__ . '/../../config/app.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../auth/session.php';

$pdo = Database::getConnection();

// Filters
$keyword = $_GET['q'] ?? '';
$industry = $_GET['industry'] ?? '';
$jobType = $_GET['type'] ?? '';

// Build Query dynamically
$where = ["j.status = 'active'"];
$params = [];

if (!empty($keyword)) {
    $where[] = "(j.title LIKE :kw1 OR j.description LIKE :kw2 OR h.company_name LIKE :kw3 OR j.location LIKE :kw4 OR j.requirements LIKE :kw5)";
    $kwParam = "%$keyword%";
    $params['kw1'] = $kwParam;
    $params['kw2'] = $kwParam;
    $params['kw3'] = $kwParam;
    $params['kw4'] = $kwParam;
    $params['kw5'] = $kwParam;
}
if (!empty($industry)) {
    $where[] = "j.industry_category = :ind";
    $params['ind'] = $industry;
}
if (!empty($jobType)) {
    $where[] = "j.job_type = :type";
    $params['type'] = $jobType;
}

$whereClause = implode(" AND ", $where);

// Fetch Jobs
$sql = "
    SELECT j.*, h.company_name 
    FROM jobs j
    JOIN hr_profiles h ON j.hr_profile_id = h.id
    WHERE $whereClause
    ORDER BY j.created_at DESC
";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$jobs = $stmt->fetchAll();

// Get Saved Jobs for current user
$savedJobs = [];
if (isset($_SESSION['user_id']) && $_SESSION['role'] === ROLE_PELAMAR) {
    $stmt = $pdo->prepare("
        SELECT s.job_id 
        FROM saved_jobs s
        JOIN pelamar_profiles p ON s.pelamar_profile_id = p.id
        WHERE p.user_id = :uid
    ");
    $stmt->execute(['uid' => $_SESSION['user_id']]);
    $savedJobs = $stmt->fetchAll(PDO::FETCH_COLUMN);
}

?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Jelajahi Lowongan — <?= APP_NAME ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="<?= BASE_URL ?>/assets/js/theme.js"></script>
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/design-system.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/components.css">
</head>
<body class="bg-slate-900 text-slate-200 font-sans antialiased min-h-screen flex flex-col relative">

    <div class="fixed top-[20%] left-[-10%] w-[30%] h-[30%] bg-indigo-600/10 rounded-full blur-[120px] pointer-events-none"></div>

    <?php require_once __DIR__ . '/../../components/header.php'; ?>

    <main class="flex-grow max-w-[1400px] mx-auto w-full px-4 sm:px-6 py-8">
        
        <!-- Search Header -->
        <div class="text-center max-w-3xl mx-auto mb-12">
            <h1 class="text-3xl sm:text-4xl font-extrabold text-white tracking-tight mb-4">Temukan Pekerjaan Ideal Anda</h1>
            <p class="text-slate-400">Jelajahi ratusan lowongan dari perusahaan terkemuka.</p>
        </div>

        <!-- Filter Bar -->
        <form action="" method="GET" class="glass-card p-4 rounded-2xl mb-12 flex flex-col md:flex-row gap-4 relative z-20">
            <div class="flex-grow">
                <input type="text" name="q" value="<?= htmlspecialchars($keyword) ?>" placeholder="Cari posisi atau nama perusahaan..." class="w-full bg-slate-900/50 border border-slate-700/50 rounded-xl px-4 py-3 text-slate-200 placeholder:text-slate-400 focus:outline-none focus:border-indigo-500">
            </div>
            <div class="w-full md:w-48 shrink-0">
                <select name="industry" class="w-full bg-slate-900/50 border border-slate-700/50 rounded-xl px-4 py-3 text-slate-300 focus:outline-none focus:border-indigo-500">
                    <option value="">Semua Industri</option>
                    <?php foreach(INDUSTRY_CATEGORIES as $k => $v): ?>
                        <option value="<?= $k ?>" <?= $industry === $k ? 'selected' : '' ?>><?= $v ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="w-full md:w-48 shrink-0">
                <select name="type" class="w-full bg-slate-900/50 border border-slate-700/50 rounded-xl px-4 py-3 text-slate-300 focus:outline-none focus:border-indigo-500">
                    <option value="">Semua Tipe</option>
                    <?php foreach(JOB_TYPES as $k => $v): ?>
                        <option value="<?= $k ?>" <?= $jobType === $k ? 'selected' : '' ?>><?= $v ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <button type="submit" class="btn-primary py-3 px-6 shrink-0 justify-center">Cari</button>
        </form>

        <!-- Results Grid -->
        <div class="mb-6 flex justify-between items-center text-sm text-slate-400">
            <span>Menampilkan <strong><?= count($jobs) ?></strong> lowongan aktif</span>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 relative z-10">
            <?php if (empty($jobs)): ?>
                <div class="col-span-full glass-card p-12 text-center text-slate-400">
                    Tidak ditemukan lowongan yang cocok dengan kriteria pencarian Anda.
                </div>
            <?php else: ?>
                <?php foreach ($jobs as $job): ?>
                    <div class="glass-card interactive-card p-6 flex flex-col h-full">
                        <div class="flex justify-between items-start mb-4">
                            <div class="w-12 h-12 rounded-xl bg-slate-800 flex items-center justify-center text-xl font-bold text-white border border-slate-700">
                                <?= strtoupper(substr($job['company_name'], 0, 1)) ?>
                            </div>
                            <div class="flex items-center gap-2">
                                <span class="px-3 py-1 rounded-full text-xs font-semibold bg-indigo-500/10 text-indigo-400 border border-indigo-500/20">
                                    <?= JOB_TYPES[$job['job_type']] ?? $job['job_type'] ?>
                                </span>
                                <?php if (isset($_SESSION['role']) && $_SESSION['role'] === ROLE_PELAMAR): ?>
                                    <?php $isSaved = in_array($job['id'], $savedJobs); ?>
                                    <button onclick="toggleSave(<?= $job['id'] ?>, this)" class="p-2 rounded-full hover:bg-slate-800 transition-colors group">
                                        <svg class="w-6 h-6 <?= $isSaved ? 'text-rose-500 fill-rose-500' : 'text-slate-500 group-hover:text-rose-400' ?> transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
                                        </svg>
                                    </button>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <h3 class="text-xl font-bold text-white mb-1 leading-tight line-clamp-2"><?= htmlspecialchars($job['title']) ?></h3>
                        <p class="text-slate-400 text-sm font-medium mb-4"><?= htmlspecialchars($job['company_name']) ?></p>
                        
                        <div class="mt-auto space-y-3">
                            <div class="flex items-center text-sm text-slate-300 gap-2">
                                <svg class="w-4 h-4 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z" /></svg>
                                <?= htmlspecialchars($job['location']) ?>
                            </div>
                            <?php if (!empty($job['salary_range'])): ?>
                                <div class="flex items-center text-sm text-emerald-400 gap-2">
                                    <svg class="w-4 h-4 text-emerald-500/70" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 6v12m-3-2.818l.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                    <?= htmlspecialchars($job['salary_range']) ?>
                                </div>
                            <?php endif; ?>
                            
                            <div class="pt-4 border-t border-slate-700/50 mt-4">
                                <a href="detail.php?id=<?= $job['id'] ?>" class="block w-full py-2.5 text-center rounded-lg text-sm font-semibold text-white bg-slate-800 hover:bg-indigo-600 transition-colors border border-slate-700 hover:border-indigo-500">
                                    Lihat Lowongan
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </main>

    <?php require_once __DIR__ . '/../../components/footer.php'; ?>

    <script>
    function toggleSave(jobId, btn) {
        event.preventDefault();
        const svg = btn.querySelector('svg');
        
        fetch('save.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: 'job_id=' + jobId
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'saved') {
                svg.classList.remove('text-slate-500', 'group-hover:text-rose-400');
                svg.classList.add('text-rose-500', 'fill-rose-500');
            } else if (data.status === 'unsaved') {
                svg.classList.remove('text-rose-500', 'fill-rose-500');
                svg.classList.add('text-slate-500', 'group-hover:text-rose-400');
            } else {
                alert(data.error || 'Terjadi kesalahan');
            }
        })
        .catch(err => console.error(err));
    }
    </script>
</body>
</html>



