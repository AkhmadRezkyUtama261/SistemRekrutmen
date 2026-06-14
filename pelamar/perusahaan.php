<?php
/*
 * @Feature:     Eksplorasi Perusahaan
 * @Author:      Muhammad Randyano (Randy)
 * @Description: Menampilkan daftar perusahaan yang tergabung dalam portal RecruitPro.
 */

require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../auth/session.php';

// Pastikan pelamar sudah login
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== ROLE_PELAMAR) {
    header('Location: ' . BASE_URL . '/auth/login.php');
    exit;
}

try {
    // Mengambil data perusahaan yang memiliki lowongan aktif
    $stmt = Database::getConnection()->prepare("
        SELECT h.*, 
               (SELECT COUNT(id) FROM jobs WHERE hr_profile_id = h.id AND status = 'active') as total_jobs
        FROM hr_profiles h
        ORDER BY total_jobs DESC
    ");
    $stmt->execute();
    $companies = $stmt->fetchAll();
} catch (PDOException $e) {
    $companies = [];
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Eksplorasi Perusahaan — <?= APP_NAME ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="<?= BASE_URL ?>/assets/js/theme.js"></script>
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/design-system.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/components.css">
</head>
<body class="bg-slate-900 text-slate-200 font-sans antialiased min-h-screen flex flex-col">
    <?php require_once __DIR__ . '/../components/header.php'; ?>

    <main class="flex-grow pt-24 pb-12 px-6">
        <div class="max-w-[1200px] mx-auto">
            <div class="mb-10 reveal">
                <h1 class="text-3xl font-bold text-white mb-2">Eksplorasi Perusahaan</h1>
                <p class="text-slate-400">Temukan perusahaan impian Anda dan lihat lowongan yang tersedia.</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <?php if (empty($companies)): ?>
                    <div class="col-span-full text-center py-10 glass-card">
                        <p class="text-slate-400">Belum ada data perusahaan.</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($companies as $index => $company): ?>
                        <div class="glass-card p-6 hover:-translate-y-2 transition-transform duration-300 reveal stagger-<?= $index % 4 ?>">
                            <div class="flex items-center gap-4 mb-4">
                                <div class="w-16 h-16 rounded-xl bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center text-2xl font-bold text-white shadow-lg">
                                    <?= strtoupper(substr($company['company_name'], 0, 1)) ?>
                                </div>
                                <div>
                                    <h3 class="text-xl font-bold text-white"><?= htmlspecialchars($company['company_name']) ?></h3>
                                    <p class="text-sm text-indigo-400"><?= $company['total_jobs'] ?> Lowongan Aktif</p>
                                </div>
                            </div>
                            <p class="text-slate-400 text-sm mb-4 line-clamp-3">
                                <?= htmlspecialchars($company['description'] ?? 'Perusahaan ini belum menambahkan deskripsi profil mereka. Namun teruslah pantau lowongan terbarunya.') ?>
                            </p>
                            <a href="<?= BASE_URL ?>/pelamar/jobs/browse.php?q=<?= urlencode($company['company_name']) ?>" class="block w-full py-2 text-center rounded-lg text-sm font-semibold text-white bg-slate-800 hover:bg-indigo-600 transition-colors border border-slate-700 hover:border-indigo-500">
                                Lihat Lowongan
                            </a>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </main>

    <?php require_once __DIR__ . '/../components/footer.php'; ?>
</body>
</html>
