<?php
/*
 * @Module:      Edit Job Posting
 * @Author:      FE-03 (HR Dashboard UI)
 * @Date:        2026-05-24
 * @Description: Form to edit an existing job posting.
 * @Ownership:   FE-03
 */

require_once __DIR__ . '/../../config/app.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/security.php';
require_once __DIR__ . '/../../auth/session.php';
require_once __DIR__ . '/../../auth/middleware.php';
require_once __DIR__ . '/../../includes/functions.php';

requireHR();

$error = '';
$pdo = Database::getConnection();

// Get HR ID
$stmt = $pdo->prepare("SELECT id FROM hr_profiles WHERE user_id = :uid");
$stmt->execute(['uid' => $_SESSION['user_id']]);
$hrId = $stmt->fetchColumn();

// Get Job ID
$jobId = $_GET['id'] ?? ($_POST['id'] ?? null);
if (!$jobId) {
    redirect(BASE_URL . '/hr/jobs/list.php');
}

// Fetch Job to verify ownership and prefill form
$stmt = $pdo->prepare("SELECT * FROM jobs WHERE id = :id AND hr_profile_id = :hr");
$stmt->execute(['id' => $jobId, 'hr' => $hrId]);
$job = $stmt->fetch();

if (!$job) {
    flashMessage('error', 'Lowongan tidak ditemukan atau Anda tidak memiliki akses.');
    redirect(BASE_URL . '/hr/jobs/list.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    enforceCsrf();

    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $requirements = trim($_POST['requirements'] ?? '');
    $location = trim($_POST['location'] ?? '');
    $job_type = trim($_POST['job_type'] ?? '');
    $industry_category = trim($_POST['industry_category'] ?? '');
    $salary_range = trim($_POST['salary_range'] ?? '');
    $deadline = trim($_POST['deadline'] ?? '');

    if (empty($title) || empty($description) || empty($requirements) || empty($location) || empty($deadline)) {
        $error = 'Semua field wajib diisi.';
    } else {
        try {
            $updateStmt = $pdo->prepare("
                UPDATE jobs 
                SET title = :title, 
                    description = :desc, 
                    requirements = :req, 
                    location = :loc, 
                    job_type = :type, 
                    industry_category = :ind, 
                    salary_range = :salary, 
                    deadline = :deadline, 
                    updated_at = NOW()
                WHERE id = :id AND hr_profile_id = :hr
            ");
            $updateStmt->execute([
                'title' => $title,
                'desc' => $description,
                'req' => $requirements,
                'loc' => $location,
                'type' => $job_type,
                'ind' => $industry_category,
                'salary' => $salary_range,
                'deadline' => $deadline,
                'id' => $jobId,
                'hr' => $hrId
            ]);

            flashMessage('success', 'Lowongan kerja berhasil diperbarui!');
            redirect(BASE_URL . '/hr/jobs/list.php');
        } catch (PDOException $e) {
            error_log("Error updating job: " . $e->getMessage());
            $error = 'Terjadi kesalahan sistem.';
        }
    }
    
    // Update local variable so form shows new values if validation fails
    $job['title'] = $title;
    $job['description'] = $description;
    $job['requirements'] = $requirements;
    $job['location'] = $location;
    $job['job_type'] = $job_type;
    $job['industry_category'] = $industry_category;
    $job['salary_range'] = $salary_range;
    $job['deadline'] = $deadline;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Lowongan — <?= APP_NAME ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="<?= BASE_URL ?>/assets/js/theme.js"></script>
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/design-system.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/components.css">
</head>
<body class="bg-slate-900 text-slate-200 font-sans antialiased min-h-screen flex flex-col relative">

    <?php require_once __DIR__ . '/../../components/header.php'; ?>

    <main class="flex-grow max-w-[900px] mx-auto w-full px-4 sm:px-6 py-8">
        
        <div class="mb-8">
            <a href="list.php" class="text-sm font-semibold text-indigo-400 hover:text-indigo-300 flex items-center gap-2 mb-4">
                &larr; Kembali ke Daftar
            </a>
            <h1 class="text-2xl font-bold text-white">Edit Lowongan</h1>
            <p class="text-sm text-slate-400">Perbarui informasi lowongan di bawah ini.</p>
        </div>

        <?php if (!empty($error)): ?>
            <div class="mb-6 bg-rose-500/10 border border-rose-500/20 text-rose-400 px-4 py-3 rounded-xl">
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <form action="<?= htmlspecialchars($_SERVER['PHP_SELF']) ?>" method="POST" class="glass-card p-6 sm:p-8 space-y-6">
            <?= csrfField() ?>
            <input type="hidden" name="id" value="<?= htmlspecialchars($job['id']) ?>">

            <div class="space-y-2">
                <label class="block text-sm font-medium text-slate-300">Judul Posisi <span class="text-rose-400">*</span></label>
                <input type="text" name="title" value="<?= htmlspecialchars($job['title']) ?>" required class="w-full bg-slate-900/50 border border-slate-700 rounded-xl px-4 py-3 text-white focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 outline-none" placeholder="Contoh: Senior Frontend Developer">
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="space-y-2">
                    <label class="block text-sm font-medium text-slate-300">Tipe Pekerjaan <span class="text-rose-400">*</span></label>
                    <select name="job_type" required class="w-full bg-slate-900/50 border border-slate-700 rounded-xl px-4 py-3 text-white focus:border-indigo-500 outline-none">
                        <?php foreach(JOB_TYPES as $k => $v): ?>
                            <option value="<?= $k ?>" <?= ($job['job_type'] == $k) ? 'selected' : '' ?>><?= $v ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="space-y-2">
                    <label class="block text-sm font-medium text-slate-300">Kategori Industri <span class="text-rose-400">*</span></label>
                    <select name="industry_category" required class="w-full bg-slate-900/50 border border-slate-700 rounded-xl px-4 py-3 text-white focus:border-indigo-500 outline-none">
                        <?php foreach(INDUSTRY_CATEGORIES as $k => $v): ?>
                            <option value="<?= $k ?>" <?= ($job['industry_category'] == $k) ? 'selected' : '' ?>><?= $v ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="space-y-2">
                    <label class="block text-sm font-medium text-slate-300">Lokasi <span class="text-rose-400">*</span></label>
                    <input type="text" name="location" value="<?= htmlspecialchars($job['location']) ?>" required class="w-full bg-slate-900/50 border border-slate-700 rounded-xl px-4 py-3 text-white focus:border-indigo-500 outline-none" placeholder="Contoh: Jakarta Selatan (WFO)">
                </div>
                <div class="space-y-2">
                    <label class="block text-sm font-medium text-slate-300">Rentang Gaji (Opsional)</label>
                    <input type="text" name="salary_range" value="<?= htmlspecialchars($job['salary_range']) ?>" class="w-full bg-slate-900/50 border border-slate-700 rounded-xl px-4 py-3 text-white focus:border-indigo-500 outline-none" placeholder="Contoh: Rp 8.000.000 - Rp 12.000.000">
                </div>
            </div>

            <div class="space-y-2">
                <label class="block text-sm font-medium text-slate-300">Deskripsi Pekerjaan <span class="text-rose-400">*</span></label>
                <textarea name="description" rows="5" required class="w-full bg-slate-900/50 border border-slate-700 rounded-xl px-4 py-3 text-white focus:border-indigo-500 outline-none"><?= htmlspecialchars($job['description']) ?></textarea>
            </div>

            <div class="space-y-2">
                <label class="block text-sm font-medium text-slate-300">Persyaratan <span class="text-rose-400">*</span></label>
                <textarea name="requirements" rows="5" required class="w-full bg-slate-900/50 border border-slate-700 rounded-xl px-4 py-3 text-white focus:border-indigo-500 outline-none"><?= htmlspecialchars($job['requirements']) ?></textarea>
            </div>

            <div class="space-y-2">
                <label class="block text-sm font-medium text-slate-300">Batas Akhir Lamaran <span class="text-rose-400">*</span></label>
                <input type="date" name="deadline" value="<?= htmlspecialchars($job['deadline']) ?>" required class="w-full md:w-1/2 bg-slate-900/50 border border-slate-700 rounded-xl px-4 py-3 text-white focus:border-indigo-500 outline-none">
            </div>

            <div class="pt-4 border-t border-slate-700/50 flex justify-end">
                <button type="submit" class="btn-primary px-8 py-3 text-base">Simpan Perubahan</button>
            </div>
        </form>
    </main>

    <?php require_once __DIR__ . '/../../components/footer.php'; ?>
</body>
</html>
