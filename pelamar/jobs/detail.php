<?php
/*
 * @Module:      Job Detail Page
 * @Author:      FE-04 (Pelamar Dashboard UI)
 * @Date:        2026-05-24
 * @Description: Public/Pelamar detailed view of a job posting.
 * @Ownership:   FE-04
 */

require_once __DIR__ . '/../../config/app.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/security.php';
require_once __DIR__ . '/../../auth/session.php';
require_once __DIR__ . '/../../includes/functions.php';

$jobId = $_GET['id'] ?? null;
if (!$jobId) {
    redirect(BASE_URL . '/pelamar/jobs/browse.php');
}

$pdo = Database::getConnection();

// Fetch Job Detail
$stmt = $pdo->prepare("
    SELECT j.*, h.company_name, h.company_description, h.website 
    FROM jobs j
    JOIN hr_profiles h ON j.hr_profile_id = h.id
    WHERE j.id = :id AND j.status = 'active'
");
$stmt->execute(['id' => $jobId]);
$job = $stmt->fetch();

if (!$job) {
    // Job not found or closed
    flashMessage('error', 'Lowongan kerja tidak ditemukan atau sudah ditutup.');
    redirect(BASE_URL . '/pelamar/jobs/browse.php');
}

// Check Application Status if Logged In as Pelamar
$hasApplied = false;
$missingProfile = false;

if (isset($_SESSION['user_id']) && $_SESSION['role'] === ROLE_PELAMAR) {
    // Get pelamar profile ID
    $stmtProf = $pdo->prepare("SELECT id, cv_file_path FROM pelamar_profiles WHERE user_id = :uid");
    $stmtProf->execute(['uid' => $_SESSION['user_id']]);
    $prof = $stmtProf->fetch();
    
    if ($prof) {
        if (empty($prof['cv_file_path'])) {
            $missingProfile = true;
        }
        
        $stmtApp = $pdo->prepare("SELECT id FROM applications WHERE pelamar_profile_id = :pid AND job_id = :jid");
        $stmtApp->execute(['pid' => $prof['id'], 'jid' => $jobId]);
        if ($stmtApp->fetch()) {
            $hasApplied = true;
        }
    }
}

$flash = getFlashMessage();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($job['title']) ?> di <?= htmlspecialchars($job['company_name']) ?> — <?= APP_NAME ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="<?= BASE_URL ?>/assets/js/theme.js"></script>
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/design-system.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/components.css">
</head>
<body class="bg-slate-900 text-slate-200 font-sans antialiased min-h-screen flex flex-col relative">

    <?php require_once __DIR__ . '/../../components/header.php'; ?>

    <main class="flex-grow max-w-[1200px] mx-auto w-full px-4 sm:px-6 py-8">
        
        <div class="mb-6">
            <a href="browse.php" class="text-sm font-semibold text-indigo-400 hover:text-indigo-300 flex items-center gap-2 mb-6 w-fit">
                &larr; Kembali ke Daftar Lowongan
            </a>
            
            <?php if ($flash): ?>
                <div class="mb-6 bg-<?= $flash['type'] === 'success' ? 'emerald' : 'rose' ?>-500/10 border border-<?= $flash['type'] === 'success' ? 'emerald' : 'rose' ?>-500/20 text-<?= $flash['type'] === 'success' ? 'emerald' : 'rose' ?>-400 px-4 py-3 rounded-xl flex items-center gap-3">
                    <span class="text-sm font-medium"><?= htmlspecialchars($flash['message']) ?></span>
                </div>
            <?php endif; ?>
            
            <?php if ($hasApplied): ?>
                <div class="mb-6 bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 px-4 py-4 rounded-xl flex items-start gap-4">
                    <div class="w-10 h-10 rounded-full bg-emerald-500/20 flex items-center justify-center shrink-0">
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                    </div>
                    <div>
                        <h4 class="font-bold text-lg mb-1">Anda sudah melamar posisi ini</h4>
                        <p class="text-emerald-400/80 text-sm">Lamaran Anda sedang dalam proses rekrutmen. Pantau status lamaran Anda melalui Dashboard Pelamar.</p>
                        <a href="<?= BASE_URL ?>/pelamar/applications/list.php" class="inline-block mt-3 px-4 py-2 bg-emerald-500/20 rounded-lg text-sm font-semibold hover:bg-emerald-500/30 transition-colors">Lihat Status Lamaran</a>
                    </div>
                </div>
            <?php elseif ($missingProfile): ?>
                <div class="mb-6 bg-amber-500/10 border border-amber-500/20 text-amber-400 px-4 py-4 rounded-xl flex items-start gap-4">
                    <div class="w-10 h-10 rounded-full bg-amber-500/20 flex items-center justify-center shrink-0">
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>
                    </div>
                    <div>
                        <h4 class="font-bold text-lg mb-1">CV Belum Diunggah</h4>
                        <p class="text-amber-400/80 text-sm">Anda harus mengunggah CV (format PDF) terlebih dahulu sebelum dapat melamar pekerjaan ini.</p>
                        <a href="<?= BASE_URL ?>/pelamar/profile/edit.php" class="inline-block mt-3 px-4 py-2 bg-amber-500/20 rounded-lg text-sm font-semibold hover:bg-amber-500/30 transition-colors">Unggah CV Sekarang</a>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            
            <!-- Main Content (Left) -->
            <div class="lg:col-span-2 space-y-8">
                <!-- Job Header -->
                <div class="glass-card p-8">
                    <div class="flex items-center gap-6 mb-6">
                        <div class="w-20 h-20 rounded-2xl bg-gradient-to-br from-indigo-500 to-indigo-600 flex items-center justify-center text-4xl font-extrabold text-white shadow-lg">
                            <?= strtoupper(substr($job['company_name'], 0, 1)) ?>
                        </div>
                        <div>
                            <h1 class="text-3xl font-bold text-white leading-tight mb-2"><?= htmlspecialchars($job['title']) ?></h1>
                            <p class="text-lg text-indigo-400 font-medium"><?= htmlspecialchars($job['company_name']) ?></p>
                        </div>
                    </div>
                    
                    <div class="flex flex-wrap gap-4 mt-8 pt-6 border-t border-slate-700/50">
                        <div class="flex items-center gap-2 text-slate-300">
                            <svg class="w-5 h-5 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z" /></svg>
                            <?= htmlspecialchars($job['location']) ?>
                        </div>
                        <div class="flex items-center gap-2 text-slate-300">
                            <svg class="w-5 h-5 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21 13.255A23.193 23.193 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" /></svg>
                            <?= JOB_TYPES[$job['job_type']] ?? $job['job_type'] ?>
                        </div>
                        <?php if (!empty($job['salary_range'])): ?>
                            <div class="flex items-center gap-2 text-emerald-400">
                                <svg class="w-5 h-5 text-emerald-500/70" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 6v12m-3-2.818l.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                <?= htmlspecialchars($job['salary_range']) ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Job Details -->
                <div class="glass-card p-8">
                    <h2 class="text-xl font-bold text-white mb-4">Deskripsi Pekerjaan</h2>
                    <div class="text-slate-300 leading-relaxed whitespace-pre-wrap mb-8"><?= htmlspecialchars($job['description']) ?></div>

                    <h2 class="text-xl font-bold text-white mb-4">Persyaratan Pekerjaan</h2>
                    <div class="text-slate-300 leading-relaxed whitespace-pre-wrap"><?= htmlspecialchars($job['requirements']) ?></div>
                </div>
            </div>

            <!-- Sidebar (Right) -->
            <div class="space-y-6">
                <!-- Apply Action Box -->
                <div class="glass-card p-6 border-t-4 border-indigo-500 relative overflow-hidden">
                    <div class="absolute inset-0 bg-gradient-to-br from-indigo-500/5 to-transparent pointer-events-none"></div>
                    <div class="relative z-10">
                        <div class="mb-6">
                            <p class="text-sm text-slate-400 mb-1">Batas Lamaran</p>
                            <p class="text-lg font-bold text-white"><?= date('d F Y', strtotime($job['deadline'])) ?></p>
                        </div>
                        
                        <?php if (!isset($_SESSION['user_id'])): ?>
                            <a href="<?= BASE_URL ?>/auth/login.php" class="btn-primary w-full justify-center py-4 text-base shadow-[0_4px_14px_rgba(99,102,241,0.4)] hover:shadow-[0_6px_20px_rgba(99,102,241,0.5)]">
                                Masuk untuk Melamar
                            </a>
                        <?php elseif ($_SESSION['role'] === ROLE_PELAMAR): ?>
                            <?php if (!$hasApplied && !$missingProfile): ?>
                                <button onclick="document.getElementById('apply-modal').classList.remove('hidden')" class="btn-primary w-full justify-center py-4 text-base shadow-[0_4px_14px_rgba(99,102,241,0.4)] hover:shadow-[0_6px_20px_rgba(99,102,241,0.5)] transition-all">
                                    Lamar Pekerjaan Ini
                                </button>
                            <?php elseif ($missingProfile): ?>
                                <button disabled class="w-full py-4 text-base font-semibold rounded-xl bg-slate-800 text-slate-500 cursor-not-allowed border border-slate-700">
                                    Lamar Pekerjaan Ini
                                </button>
                            <?php else: ?>
                                <button disabled class="w-full py-4 text-base font-semibold rounded-xl bg-emerald-500/10 text-emerald-400 cursor-not-allowed border border-emerald-500/20">
                                    ✓ Lamaran Terkirim
                                </button>
                            <?php endif; ?>
                        <?php else: ?>
                            <div class="text-center p-3 bg-slate-800 rounded-xl border border-slate-700 text-sm text-slate-400">
                                Akun HR tidak dapat melamar pekerjaan.
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Company Info Box -->
                <div class="glass-card p-6">
                    <h3 class="text-lg font-bold text-white mb-4">Tentang Perusahaan</h3>
                    <div class="text-sm text-slate-400 leading-relaxed mb-4">
                        <?= !empty($job['company_description']) ? nl2br(htmlspecialchars($job['company_description'])) : 'Deskripsi perusahaan belum ditambahkan.' ?>
                    </div>
                    <?php if (!empty($job['website'])): ?>
                        <a href="<?= htmlspecialchars($job['website']) ?>" target="_blank" rel="noopener noreferrer" class="inline-flex items-center text-sm font-medium text-indigo-400 hover:text-indigo-300">
                            Kunjungi Website
                            <svg class="w-4 h-4 ml-1" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" /></svg>
                        </a>
                    <?php endif; ?>
                </div>
            </div>

        </div>
    </main>

    <!-- ── Apply Modal ── -->
    <?php if (isset($_SESSION['user_id']) && $_SESSION['role'] === ROLE_PELAMAR && !$hasApplied && !$missingProfile): ?>
    <div id="apply-modal" class="fixed inset-0 z-[100] hidden">
        <!-- Backdrop -->
        <div class="absolute inset-0 bg-slate-900/40 backdrop-blur-md" onclick="document.getElementById('apply-modal').classList.add('hidden')"></div>
        
        <!-- Modal Content -->
        <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-full max-w-xl">
            <div class="bg-slate-800 rounded-3xl overflow-hidden shadow-[0_30px_60px_rgba(30,41,59,0.15)] border border-slate-700/50 transform transition-all scale-100 opacity-100">
                <!-- Modal Header -->
                <div class="px-8 py-6 border-b border-slate-700/50 flex justify-between items-center bg-slate-900/30">
                    <div>
                        <h3 class="text-2xl font-extrabold text-white">Kirim Lamaran</h3>
                        <p class="text-sm text-slate-400 mt-1">Anda melamar untuk posisi <span class="font-bold text-indigo-500"><?= htmlspecialchars($job['title']) ?></span></p>
                    </div>
                    <button onclick="document.getElementById('apply-modal').classList.add('hidden')" class="w-10 h-10 rounded-full flex items-center justify-center bg-slate-700/30 text-slate-400 hover:text-white hover:bg-slate-700 transition-colors">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12" /></svg>
                    </button>
                </div>
                
                <form action="apply.php" method="POST" class="p-8 space-y-6">
                    <?= csrfField() ?>
                    <input type="hidden" name="job_id" value="<?= $job['id'] ?>">
                    
                    <!-- Alert Box -->
                    <div class="bg-indigo-50 border border-indigo-100 rounded-2xl p-5 flex gap-4 shadow-sm">
                        <div class="w-10 h-10 rounded-full bg-indigo-100 flex items-center justify-center shrink-0">
                            <svg class="w-5 h-5 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13" /></svg>
                        </div>
                        <div>
                            <h4 class="text-sm font-bold text-indigo-900 mb-1">CV Otomatis Terlampir</h4>
                            <p class="text-sm text-indigo-800/80 leading-relaxed">
                                Dokumen CV terbaru yang ada di profil Anda akan langsung dikirimkan ke HR. Anda tidak perlu mengunggah ulang.
                            </p>
                        </div>
                    </div>

                    <!-- Textarea -->
                    <div class="space-y-3">
                        <label class="block text-sm font-bold text-white">Surat Pengantar / Cover Letter <span class="text-slate-400 font-normal">(Opsional)</span></label>
                        <div class="relative">
                            <textarea name="cover_letter" rows="5" class="w-full bg-slate-900 border border-slate-700 rounded-2xl px-5 py-4 text-white focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 outline-none transition-all resize-none shadow-sm placeholder:text-slate-400" placeholder="Jelaskan secara singkat mengapa Anda adalah kandidat yang paling tepat untuk posisi ini..."></textarea>
                            <div class="absolute bottom-4 right-4 text-xs text-slate-400 font-medium">Tips: Tulis 2-3 paragraf singkat</div>
                        </div>
                    </div>
                    
                    <!-- Footer Actions -->
                    <div class="pt-4 flex justify-end gap-4">
                        <button type="button" onclick="document.getElementById('apply-modal').classList.add('hidden')" class="px-6 py-3 rounded-xl text-sm font-bold text-slate-500 hover:text-white hover:bg-slate-700 transition-colors">Batal</button>
                        <button type="submit" class="btn-primary px-8 py-3 rounded-xl text-base shadow-[0_8px_20px_rgba(99,102,241,0.3)] hover:shadow-[0_12px_25px_rgba(99,102,241,0.4)] transform hover:-translate-y-0.5">
                            Kirim Lamaran Sekarang
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <?php require_once __DIR__ . '/../../components/footer.php'; ?>
</body>
</html>


