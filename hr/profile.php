
<?php
/*
 * @Module:      HR Profile Edit
 * @Author:      FE-03 (HR Dashboard UI)
 * @Date:        2026-05-24
 * @Description: Form for employer to update company details.
 * @Ownership:   FE-03
 */

require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/security.php';
require_once __DIR__ . '/../auth/session.php';
require_once __DIR__ . '/../auth/middleware.php';
require_once __DIR__ . '/../includes/functions.php';

requireHR();

$pdo = Database::getConnection();
$userId = $_SESSION['user_id'];
$error = '';

// Fetch existing profile
$stmt = $pdo->prepare("SELECT * FROM hr_profiles WHERE user_id = :uid");
$stmt->execute(['uid' => $userId]);
$profile = $stmt->fetch();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    enforceCsrf();

    $companyName = trim($_POST['company_name'] ?? '');
    $industry = trim($_POST['industry'] ?? '');
    $location = trim($_POST['location'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $website = trim($_POST['website'] ?? '');
    $description = trim($_POST['company_description'] ?? '');

    if (empty($companyName)) {
        $error = 'Nama perusahaan wajib diisi.';
    } else {
        try {
            $stmtUpdate = $pdo->prepare("
                UPDATE hr_profiles 
                SET company_name = :name, industry = :ind, location = :loc, 
                    phone = :phone, website = :web, company_description = :desc, 
                    updated_at = NOW()
                WHERE user_id = :uid
            ");
            
            $stmtUpdate->execute([
                'name' => $companyName,
                'ind' => $industry,
                'loc' => $location,
                'phone' => $phone,
                'web' => $website,
                'desc' => $description,
                'uid' => $userId
            ]);

            // Update session name if changed
            $_SESSION['profile_name'] = $companyName;

            flashMessage('success', 'Profil perusahaan berhasil diperbarui.');
            redirect(BASE_URL . '/hr/profile.php');
            
        } catch (PDOException $e) {
            error_log("Update HR profile error: " . $e->getMessage());
            $error = 'Terjadi kesalahan saat memperbarui database.';
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
    <title>Profil Perusahaan — <?= APP_NAME ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="<?= BASE_URL ?>/assets/js/theme.js"></script>
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/design-system.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/components.css">
</head>
<body class="bg-slate-900 text-slate-200 font-sans antialiased min-h-screen flex flex-col relative">

    <?php require_once __DIR__ . '/../components/header.php'; ?>

    <main class="flex-grow max-w-[800px] mx-auto w-full px-4 sm:px-6 py-8">
        
        <div class="mb-8">
            <h1 class="text-2xl font-bold text-white mb-2">Profil Perusahaan</h1>
            <p class="text-sm text-slate-400">Lengkapi data perusahaan Anda agar terlihat profesional di mata pelamar.</p>
        </div>

        <?php if ($flash): ?>
            <div class="mb-6 bg-<?= $flash['type'] === 'success' ? 'emerald' : 'rose' ?>-500/10 border border-<?= $flash['type'] === 'success' ? 'emerald' : 'rose' ?>-500/20 text-<?= $flash['type'] === 'success' ? 'emerald' : 'rose' ?>-400 px-4 py-3 rounded-xl flex items-center gap-3">
                <span class="text-sm font-medium"><?= htmlspecialchars($flash['message']) ?></span>
            </div>
        <?php endif; ?>

        <?php if (!empty($error)): ?>
            <div class="mb-6 bg-rose-500/10 border border-rose-500/20 text-rose-400 px-4 py-3 rounded-xl">
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <form action="<?= htmlspecialchars($_SERVER['PHP_SELF']) ?>" method="POST" class="glass-card p-6 sm:p-8 space-y-6">
            <?= csrfField() ?>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="space-y-2">
                    <label class="block text-sm font-medium text-slate-300">Nama Perusahaan <span class="text-rose-400">*</span></label>
                    <input type="text" name="company_name" value="<?= htmlspecialchars($profile['company_name'] ?? '') ?>" required class="w-full bg-slate-900/50 border border-slate-700 rounded-xl px-4 py-3 text-white focus:outline-none focus:border-indigo-500">
                </div>
                <div class="space-y-2">
                    <label class="block text-sm font-medium text-slate-300">Sektor Industri</label>
                    <select name="industry" class="w-full bg-slate-900/50 border border-slate-700 rounded-xl px-4 py-3 text-white focus:outline-none focus:border-indigo-500">
                        <option value="">-- Pilih --</option>
                        <?php foreach(INDUSTRY_CATEGORIES as $k => $v): ?>
                            <option value="<?= $k ?>" <?= ($profile['industry'] ?? '') == $k ? 'selected' : '' ?>><?= $v ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="space-y-2">
                    <label class="block text-sm font-medium text-slate-300">Nomor Telepon Kantor</label>
                    <input type="text" name="phone" value="<?= htmlspecialchars($profile['phone'] ?? '') ?>" class="w-full bg-slate-900/50 border border-slate-700 rounded-xl px-4 py-3 text-white focus:outline-none focus:border-indigo-500">
                </div>
                <div class="space-y-2">
                    <label class="block text-sm font-medium text-slate-300">Website Perusahaan</label>
                    <input type="url" name="website" value="<?= htmlspecialchars($profile['website'] ?? '') ?>" class="w-full bg-slate-900/50 border border-slate-700 rounded-xl px-4 py-3 text-white focus:outline-none focus:border-indigo-500" placeholder="https://www.contoh.com">
                </div>
            </div>

            <div class="space-y-2">
                <label class="block text-sm font-medium text-slate-300">Lokasi / Kantor Pusat</label>
                <input type="text" name="location" value="<?= htmlspecialchars($profile['location'] ?? '') ?>" class="w-full bg-slate-900/50 border border-slate-700 rounded-xl px-4 py-3 text-white focus:outline-none focus:border-indigo-500" placeholder="Contoh: Menara Sudirman, Jakarta Selatan">
            </div>

            <div class="space-y-2">
                <label class="block text-sm font-medium text-slate-300">Deskripsi Singkat Perusahaan</label>
                <textarea name="company_description" rows="5" class="w-full bg-slate-900/50 border border-slate-700 rounded-xl px-4 py-3 text-white focus:outline-none focus:border-indigo-500" placeholder="Ceritakan tentang sejarah, visi, atau budaya kerja di perusahaan Anda..."><?= htmlspecialchars($profile['company_description'] ?? '') ?></textarea>
                <p class="text-xs text-slate-500 mt-1">Deskripsi ini akan ditampilkan di setiap halaman detail lowongan kerja Anda.</p>
            </div>

            <div class="pt-6 mt-6 border-t border-slate-700/50 flex justify-end">
                <button type="submit" class="btn-primary px-8 py-3 text-base">Simpan Perubahan</button>
            </div>
        </form>
    </main>

    <?php require_once __DIR__ . '/../components/footer.php'; ?>
</body>
</html>


