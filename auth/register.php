<?php
/*
 * @Module:      Registration Page
 * @Author:      BE-02 (Auth Engine)
 * @Date:        2026-05-24
 * @Description: Dual registration page (HR & Pelamar) with robust validation.
 * @Ownership:   BE-02
 *
 * RecruitPro Enterprise — © 2026
 * Do NOT modify without PR approval from module owner.
 */

require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/security.php';
require_once __DIR__ . '/session.php';
require_once __DIR__ . '/../includes/functions.php';

// If already logged in, redirect
if (isset($_SESSION['user_id'])) {
    redirect(BASE_URL . '/index.php');
}

$error = '';
$success = false;

// Helper to keep form values
$form = [
    'role' => ROLE_PELAMAR,
    'full_name' => '',
    'company_name' => '',
    'email' => '',
    'phone' => '',
    'industry' => ''
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    enforceCsrf();

    $role = $_POST['role'] ?? ROLE_PELAMAR;
    $form['role'] = $role;
    $form['email'] = trim($_POST['email'] ?? '');
    $form['phone'] = trim($_POST['phone'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm = $_POST['password_confirmation'] ?? '';

    // Specific fields
    if ($role === ROLE_PELAMAR) {
        $form['full_name'] = trim($_POST['full_name'] ?? '');
    } else {
        $form['company_name'] = trim($_POST['company_name'] ?? '');
        $form['industry'] = trim($_POST['industry'] ?? '');
    }

    // Validation
    if (!filter_var($form['email'], FILTER_VALIDATE_EMAIL)) {
        $error = 'Format email tidak valid.';
    } elseif (strlen($password) < 8) {
        $error = 'Kata sandi minimal 8 karakter.';
    } elseif ($password !== $confirm) {
        $error = 'Konfirmasi kata sandi tidak cocok.';
    } elseif ($role === ROLE_PELAMAR && empty($form['full_name'])) {
        $error = 'Nama lengkap wajib diisi.';
    } elseif ($role === ROLE_HR && (empty($form['company_name']) || empty($form['industry']))) {
        $error = 'Nama perusahaan dan industri wajib diisi.';
    } else {
        try {
            $pdo = Database::getConnection();
            
            // Check if email exists
            $stmt = $pdo->prepare("SELECT id FROM users WHERE email = :email");
            $stmt->execute(['email' => $form['email']]);
            if ($stmt->fetch()) {
                $error = 'Email sudah terdaftar. Silakan gunakan email lain atau masuk.';
            } else {
                $pdo->beginTransaction();

                // Insert into users
                $hash = password_hash($password, PASSWORD_DEFAULT);
                $stmtUser = $pdo->prepare("INSERT INTO users (email, password_hash, role, created_at, updated_at) VALUES (:email, :pass, :role, NOW(), NOW())");
                $stmtUser->execute([
                    'email' => $form['email'],
                    'pass' => $hash,
                    'role' => $role
                ]);
                $userId = $pdo->lastInsertId();

                // Insert into specific profile
                if ($role === ROLE_PELAMAR) {
                    $stmtProf = $pdo->prepare("INSERT INTO pelamar_profiles (user_id, full_name, phone) VALUES (:uid, :name, :phone)");
                    $stmtProf->execute([
                        'uid' => $userId,
                        'name' => $form['full_name'],
                        'phone' => $form['phone']
                    ]);
                } else {
                    $stmtProf = $pdo->prepare("INSERT INTO hr_profiles (user_id, company_name, phone, industry) VALUES (:uid, :company, :phone, :industry)");
                    $stmtProf->execute([
                        'uid' => $userId,
                        'company' => $form['company_name'],
                        'phone' => $form['phone'],
                        'industry' => $form['industry']
                    ]);
                }

                $pdo->commit();
                redirect(BASE_URL . '/auth/login.php?registered=1');
            }
        } catch (PDOException $e) {
            if (isset($pdo) && $pdo->inTransaction()) {
                $pdo->rollBack();
            }
            error_log("Register error: " . $e->getMessage());
            $error = 'Terjadi kesalahan sistem saat mendaftar. Silakan coba lagi.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Akun — <?= APP_NAME ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="<?= BASE_URL ?>/assets/js/theme.js"></script>
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/design-system.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/components.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/animations.css">
    
    <script>
        function toggleRole(role) {
            document.getElementById('role_input').value = role;
            
            const tabPelamar = document.getElementById('tab-pelamar');
            const tabHR = document.getElementById('tab-hr');
            const fieldsPelamar = document.getElementById('fields-pelamar');
            const fieldsHR = document.getElementById('fields-hr');
            
            if (role === 'pelamar') {
                tabPelamar.classList.add('bg-indigo-500/20', 'text-indigo-400', 'border-indigo-500/50');
                tabPelamar.classList.remove('text-slate-400', 'border-transparent', 'hover:bg-white/5');
                tabHR.classList.remove('bg-indigo-500/20', 'text-indigo-400', 'border-indigo-500/50');
                tabHR.classList.add('text-slate-400', 'border-transparent', 'hover:bg-white/5');
                
                fieldsPelamar.classList.remove('hidden');
                fieldsHR.classList.add('hidden');
                
                document.getElementById('full_name').required = true;
                document.getElementById('company_name').required = false;
                document.getElementById('industry').required = false;
            } else {
                tabHR.classList.add('bg-indigo-500/20', 'text-indigo-400', 'border-indigo-500/50');
                tabHR.classList.remove('text-slate-400', 'border-transparent', 'hover:bg-white/5');
                tabPelamar.classList.remove('bg-indigo-500/20', 'text-indigo-400', 'border-indigo-500/50');
                tabPelamar.classList.add('text-slate-400', 'border-transparent', 'hover:bg-white/5');
                
                fieldsHR.classList.remove('hidden');
                fieldsPelamar.classList.add('hidden');
                
                document.getElementById('full_name').required = false;
                document.getElementById('company_name').required = true;
                document.getElementById('industry').required = true;
            }
        }
    </script>
</head>
<body class="bg-slate-900 text-slate-200 font-sans antialiased min-h-screen flex flex-col relative overflow-x-hidden">
    
    <!-- Background Accents -->
    <div class="fixed top-[-10%] right-[-10%] w-[40%] h-[40%] bg-indigo-600/20 rounded-full blur-[120px] pointer-events-none"></div>

    <?php require_once __DIR__ . '/../components/header.php'; ?>

    <main class="flex-grow flex items-center justify-center p-6 relative z-10 my-8">
        
        <div class="w-full max-w-xl glass-card-elevated p-8 sm:p-10 reveal">
            <!-- Brand / Header -->
            <div class="text-center mb-8">
                <h1 class="text-2xl sm:text-3xl font-bold text-white tracking-tight mb-2">Buat Akun Baru</h1>
                <p class="text-sm text-slate-400">Bergabunglah dengan ekosistem rekrutmen terbaik.</p>
            </div>

            <!-- Error Message -->
            <?php if (!empty($error)): ?>
                <div class="mb-6 bg-rose-500/10 border border-rose-500/20 text-rose-400 px-4 py-3 rounded-xl flex items-start gap-3">
                    <svg class="w-5 h-5 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                    <span class="text-sm font-medium"><?= htmlspecialchars($error) ?></span>
                </div>
            <?php endif; ?>

            <!-- Role Selector -->
            <div class="flex p-1 bg-slate-900/50 border border-slate-700/50 rounded-xl mb-8">
                <button type="button" id="tab-pelamar" onclick="toggleRole('pelamar')" 
                        class="flex-1 py-2.5 text-sm font-semibold rounded-lg border transition-all <?= $form['role'] === ROLE_PELAMAR ? 'bg-indigo-500/20 text-indigo-400 border-indigo-500/50' : 'text-slate-400 border-transparent hover:bg-white/5' ?>">
                    Saya Pencari Kerja
                </button>
                <button type="button" id="tab-hr" onclick="toggleRole('hr')" 
                        class="flex-1 py-2.5 text-sm font-semibold rounded-lg border transition-all <?= $form['role'] === ROLE_HR ? 'bg-indigo-500/20 text-indigo-400 border-indigo-500/50' : 'text-slate-400 border-transparent hover:bg-white/5' ?>">
                    Saya Perwakilan HR
                </button>
            </div>

            <!-- Register Form -->
            <form action="<?= htmlspecialchars($_SERVER['PHP_SELF']) ?>" method="POST" class="space-y-5">
                <?= csrfField() ?>
                <input type="hidden" name="role" id="role_input" value="<?= htmlspecialchars($form['role']) ?>">
                
                <!-- Dynamic Fields -->
                <div id="fields-pelamar" class="space-y-5 <?= $form['role'] === ROLE_PELAMAR ? '' : 'hidden' ?>">
                    <div class="space-y-1.5">
                        <label for="full_name" class="block text-sm font-medium text-slate-300">Nama Lengkap</label>
                        <input type="text" id="full_name" name="full_name" value="<?= htmlspecialchars($form['full_name']) ?>" <?= $form['role'] === ROLE_PELAMAR ? 'required' : '' ?>
                               class="w-full bg-slate-900/50 border border-slate-700 rounded-xl px-4 py-3 text-white focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 outline-none">
                    </div>
                </div>

                <div id="fields-hr" class="space-y-5 <?= $form['role'] === ROLE_HR ? '' : 'hidden' ?>">
                    <div class="space-y-1.5">
                        <label for="company_name" class="block text-sm font-medium text-slate-300">Nama Perusahaan</label>
                        <input type="text" id="company_name" name="company_name" value="<?= htmlspecialchars($form['company_name']) ?>" <?= $form['role'] === ROLE_HR ? 'required' : '' ?>
                               class="w-full bg-slate-900/50 border border-slate-700 rounded-xl px-4 py-3 text-white focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 outline-none">
                    </div>
                    <div class="space-y-1.5">
                        <label for="industry" class="block text-sm font-medium text-slate-300">Kategori Industri</label>
                        <select id="industry" name="industry" <?= $form['role'] === ROLE_HR ? 'required' : '' ?>
                                class="w-full bg-slate-900/50 border border-slate-700 rounded-xl px-4 py-3 text-white focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 outline-none appearance-none">
                            <option value="">-- Pilih Industri --</option>
                            <?php foreach(INDUSTRY_CATEGORIES as $key => $val): ?>
                                <option value="<?= $key ?>" <?= $form['industry'] === $key ? 'selected' : '' ?>><?= $val ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <!-- Common Fields -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <div class="space-y-1.5">
                        <label for="email" class="block text-sm font-medium text-slate-300">Alamat Email</label>
                        <input type="email" id="email" name="email" value="<?= htmlspecialchars($form['email']) ?>" required
                               class="w-full bg-slate-900/50 border border-slate-700 rounded-xl px-4 py-3 text-white focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 outline-none">
                    </div>
                    <div class="space-y-1.5">
                        <label for="phone" class="block text-sm font-medium text-slate-300">Nomor Telepon</label>
                        <input type="tel" id="phone" name="phone" value="<?= htmlspecialchars($form['phone']) ?>" required
                               class="w-full bg-slate-900/50 border border-slate-700 rounded-xl px-4 py-3 text-white focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 outline-none">
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <div class="space-y-1.5">
                        <label for="password" class="block text-sm font-medium text-slate-300">Kata Sandi</label>
                        <input type="password" id="password" name="password" required minlength="8"
                               class="w-full bg-slate-900/50 border border-slate-700 rounded-xl px-4 py-3 text-white focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 outline-none">
                    </div>
                    <div class="space-y-1.5">
                        <label for="password_confirmation" class="block text-sm font-medium text-slate-300">Konfirmasi Sandi</label>
                        <input type="password" id="password_confirmation" name="password_confirmation" required minlength="8"
                               class="w-full bg-slate-900/50 border border-slate-700 rounded-xl px-4 py-3 text-white focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 outline-none">
                    </div>
                </div>

                <div class="pt-4">
                    <button type="submit" class="w-full btn-primary justify-center text-base py-3">
                        Daftar Sekarang
                    </button>
                </div>
            </form>

            <div class="mt-8 text-center">
                <p class="text-sm text-slate-400">
                    Sudah punya akun? 
                    <a href="login.php" class="font-semibold text-indigo-400 hover:text-indigo-300 transition-colors">Masuk di sini</a>
                </p>
            </div>
        </div>
    </main>

    <?php require_once __DIR__ . '/../components/footer.php'; ?>
</body>
</html>



