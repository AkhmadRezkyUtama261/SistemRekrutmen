<?php
/*
 * @Module:      Pelamar Profile Edit
 * @Author:      FE-04 (Pelamar Dashboard UI)
 * @Date:        2026-05-24
 * @Description: Form for applicant to update profile and upload PDF CV.
 * @Ownership:   FE-04
 */

require_once __DIR__ . '/../../config/app.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/security.php';
require_once __DIR__ . '/../../auth/session.php';
require_once __DIR__ . '/../../auth/middleware.php';
require_once __DIR__ . '/../../includes/functions.php';

requirePelamar();

$pdo = Database::getConnection();
$userId = $_SESSION['user_id'];
$error = '';

// Fetch existing profile
$stmt = $pdo->prepare("SELECT * FROM pelamar_profiles WHERE user_id = :uid");
$stmt->execute(['uid' => $userId]);
$profile = $stmt->fetch();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    enforceCsrf();

    $fullName = trim($_POST['full_name'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $dob = trim($_POST['date_of_birth'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $education = trim($_POST['education_level'] ?? '');
    $skills = trim($_POST['skills'] ?? '');

    // Handle CV Upload
    $cvPath = $profile['cv_file_path'] ?? null;
    
    if (isset($_FILES['cv_file']) && $_FILES['cv_file']['error'] === UPLOAD_ERR_OK) {
        $fileTmp = $_FILES['cv_file']['tmp_name'];
        $fileName = $_FILES['cv_file']['name'];
        $fileSize = $_FILES['cv_file']['size'];
        $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

        // Validation: Must be PDF, Max 2MB
        if ($fileExt !== 'pdf') {
            $error = 'File CV harus berformat PDF.';
        } elseif ($fileSize > 2097152) { // 2MB
            $error = 'Ukuran CV maksimal 2MB.';
        } else {
            // Verify MIME type using finfo
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mime = finfo_file($finfo, $fileTmp);
            finfo_close($finfo);

            if ($mime !== 'application/pdf') {
                $error = 'File tidak valid. Harap unggah PDF asli.';
            } else {
                // Secure Upload
                $uploadDir = __DIR__ . '/../../uploads/cv/';
                if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

                $newFileName = md5($userId . time() . rand()) . '.pdf';
                $destination = $uploadDir . $newFileName;

                if (move_uploaded_file($fileTmp, $destination)) {
                    // Delete old CV if exists
                    if ($cvPath && file_exists($uploadDir . $cvPath)) {
                        unlink($uploadDir . $cvPath);
                    }
                    $cvPath = $newFileName;
                } else {
                    $error = 'Gagal mengunggah file. Periksa izin folder server.';
                }
            }
        }
    }

    if (empty($error)) {
        try {
            $stmtUpdate = $pdo->prepare("
                UPDATE pelamar_profiles 
                SET full_name = :name, phone = :phone, date_of_birth = :dob, 
                    address = :addr, education_level = :edu, skills = :skills, 
                    cv_file_path = :cv, updated_at = NOW()
                WHERE user_id = :uid
            ");
            
            $stmtUpdate->execute([
                'name' => $fullName,
                'phone' => $phone,
                'dob' => empty($dob) ? null : $dob,
                'addr' => $address,
                'edu' => $education,
                'skills' => $skills,
                'cv' => $cvPath,
                'uid' => $userId
            ]);

            // Update session name if changed
            $_SESSION['profile_name'] = $fullName;

            flashMessage('success', 'Profil dan CV berhasil diperbarui.');
            redirect(BASE_URL . '/pelamar/profile/edit.php');
            
        } catch (PDOException $e) {
            error_log("Update profile error: " . $e->getMessage());
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
    <title>Profil Saya — <?= APP_NAME ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="<?= BASE_URL ?>/assets/js/theme.js"></script>
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/design-system.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/components.css">
</head>
<body class="bg-slate-900 text-slate-200 font-sans antialiased min-h-screen flex flex-col relative">

    <?php require_once __DIR__ . '/../../components/header.php'; ?>

    <main class="flex-grow max-w-[800px] mx-auto w-full px-4 sm:px-6 py-8">
        
        <div class="mb-8">
            <h1 class="text-2xl font-bold text-white mb-2">Profil & Dokumen</h1>
            <p class="text-sm text-slate-400">Lengkapi profil Anda dan unggah CV terbaru untuk mulai melamar pekerjaan.</p>
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

        <form action="<?= htmlspecialchars($_SERVER['PHP_SELF']) ?>" method="POST" enctype="multipart/form-data" class="glass-card p-6 sm:p-8 space-y-6">
            <?= csrfField() ?>

            <!-- CV Upload Section -->
            <div class="border-b border-slate-700/50 pb-6 mb-6">
                <h3 class="text-lg font-bold text-white mb-4">Curriculum Vitae (CV)</h3>
                
                <?php if (!empty($profile['cv_file_path'])): ?>
                    <div class="flex items-center gap-4 mb-4 p-4 bg-emerald-500/10 border border-emerald-500/20 rounded-xl text-emerald-400">
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                        <div>
                            <p class="font-semibold text-sm">CV Anda telah diunggah.</p>
                            <a href="<?= BASE_URL ?>/uploads/cv/<?= htmlspecialchars($profile['cv_file_path']) ?>" target="_blank" class="text-xs hover:underline mt-1 inline-block">Lihat Dokumen Saat Ini</a>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="flex items-center gap-4 mb-4 p-4 bg-rose-500/10 border border-rose-500/20 rounded-xl text-rose-400">
                        <svg class="w-6 h-6 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>
                        <p class="font-semibold text-sm">Anda belum mengunggah CV. Anda tidak dapat melamar pekerjaan sebelum mengunggah CV.</p>
                    </div>
                <?php endif; ?>

                <div class="space-y-2">
                    <label class="block text-sm font-medium text-slate-300">Unggah/Perbarui CV Baru (Opsional)</label>
                    <input type="file" name="cv_file" accept=".pdf" class="w-full bg-slate-900/50 border border-slate-700 rounded-xl px-4 py-3 text-white focus:outline-none focus:border-indigo-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-indigo-500/20 file:text-indigo-400 hover:file:bg-indigo-500/30 transition-all">
                    <p class="text-xs text-slate-500 mt-1">Format wajib: PDF. Maksimal ukuran file: 2 MB.</p>
                </div>
            </div>

            <!-- Profile Fields -->
            <h3 class="text-lg font-bold text-white mb-4">Informasi Pribadi</h3>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="space-y-2">
                    <label class="block text-sm font-medium text-slate-300">Nama Lengkap</label>
                    <input type="text" name="full_name" value="<?= htmlspecialchars($profile['full_name'] ?? '') ?>" required class="w-full bg-slate-900/50 border border-slate-700 rounded-xl px-4 py-3 text-white focus:outline-none focus:border-indigo-500">
                </div>
                <div class="space-y-2">
                    <label class="block text-sm font-medium text-slate-300">Nomor Telepon/WhatsApp</label>
                    <input type="text" name="phone" value="<?= htmlspecialchars($profile['phone'] ?? '') ?>" class="w-full bg-slate-900/50 border border-slate-700 rounded-xl px-4 py-3 text-white focus:outline-none focus:border-indigo-500">
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="space-y-2">
                    <label class="block text-sm font-medium text-slate-300">Tanggal Lahir</label>
                    <input type="date" name="date_of_birth" value="<?= htmlspecialchars($profile['date_of_birth'] ?? '') ?>" class="w-full bg-slate-900/50 border border-slate-700 rounded-xl px-4 py-3 text-white focus:outline-none focus:border-indigo-500">
                </div>
                <div class="space-y-2">
                    <label class="block text-sm font-medium text-slate-300">Tingkat Pendidikan Terakhir</label>
                    <select name="education_level" class="w-full bg-slate-900/50 border border-slate-700 rounded-xl px-4 py-3 text-white focus:outline-none focus:border-indigo-500">
                        <option value="">-- Pilih --</option>
                        <option value="SMA/SMK" <?= ($profile['education_level'] ?? '') == 'SMA/SMK' ? 'selected' : '' ?>>SMA/SMK Sederajat</option>
                        <option value="D3" <?= ($profile['education_level'] ?? '') == 'D3' ? 'selected' : '' ?>>Diploma (D3)</option>
                        <option value="S1" <?= ($profile['education_level'] ?? '') == 'S1' ? 'selected' : '' ?>>Sarjana (S1)</option>
                        <option value="S2" <?= ($profile['education_level'] ?? '') == 'S2' ? 'selected' : '' ?>>Magister (S2)</option>
                    </select>
                </div>
            </div>

            <div class="space-y-2">
                <label class="block text-sm font-medium text-slate-300">Alamat Domisili</label>
                <textarea name="address" rows="3" class="w-full bg-slate-900/50 border border-slate-700 rounded-xl px-4 py-3 text-white focus:outline-none focus:border-indigo-500"><?= htmlspecialchars($profile['address'] ?? '') ?></textarea>
            </div>

            <div class="space-y-2">
                <label class="block text-sm font-medium text-slate-300">Keahlian Utama (Pisahkan dengan koma)</label>
                <input type="text" name="skills" value="<?= htmlspecialchars($profile['skills'] ?? '') ?>" class="w-full bg-slate-900/50 border border-slate-700 rounded-xl px-4 py-3 text-white focus:outline-none focus:border-indigo-500" placeholder="Contoh: PHP, MySQL, Figma, Komunikasi">
            </div>

            <div class="pt-6 mt-6 border-t border-slate-700/50 flex justify-end">
                <button type="submit" class="btn-primary px-8 py-3 text-base">Simpan Profil</button>
            </div>
        </form>
    </main>

    <?php require_once __DIR__ . '/../../components/footer.php'; ?>
</body>
</html>



