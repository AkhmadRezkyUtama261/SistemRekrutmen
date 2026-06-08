<?php
/*
 * @Module:      Login Page
 * @Author:      BE-02 (Auth Engine)
 * @Date:        2026-05-24
 * @Description: Unified login page with role detection, session creation, and rate limiting.
 * @Ownership:   BE-02
 *
 * RecruitPro Enterprise — © 2026
 * Do NOT modify without PR approval from module owner.
 */

require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/security.php';
require_once __DIR__ . '/session.php';
require_once __DIR__ . '/middleware.php';
require_once __DIR__ . '/../includes/functions.php';

// If already logged in, redirect to respective dashboard
if (isset($_SESSION['user_id'])) {
    if ($_SESSION['role'] === ROLE_HR) {
        redirect(BASE_URL . '/hr/dashboard.php');
    } else {
        redirect(BASE_URL . '/pelamar/dashboard.php');
    }
}

$error = '';
$email = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    enforceCsrf();

    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    // Rate Limiting Check (5 attempts per 5 mins)
    if (isRateLimited('login_attempt_' . $_SERVER['REMOTE_ADDR'], 5, 300)) {
        $error = 'Terlalu banyak percobaan masuk. Silakan tunggu 5 menit.';
    } elseif (empty($email) || empty($password)) {
        $error = 'Email dan kata sandi wajib diisi.';
    } else {
        try {
            // Find user by email
            $stmt = Database::getConnection()->prepare("SELECT id, email, password_hash, role FROM users WHERE email = :email");
            $stmt->execute(['email' => $email]);
            $user = $stmt->fetch();

            if ($user && password_verify($password, $user['password_hash'])) {
                // Login Success
                resetRateLimit('login_attempt_' . $_SERVER['REMOTE_ADDR']);
                
                // Get additional profile info based on role
                $profileName = '';
                if ($user['role'] === ROLE_HR) {
                    $stmtProfile = Database::getConnection()->prepare("SELECT company_name FROM hr_profiles WHERE user_id = :uid");
                    $stmtProfile->execute(['uid' => $user['id']]);
                    $profile = $stmtProfile->fetch();
                    $profileName = $profile['company_name'] ?? 'Perusahaan';
                } else {
                    $stmtProfile = Database::getConnection()->prepare("SELECT full_name FROM pelamar_profiles WHERE user_id = :uid");
                    $stmtProfile->execute(['uid' => $user['id']]);
                    $profile = $stmtProfile->fetch();
                    $profileName = $profile['full_name'] ?? 'Pelamar';
                }

                // Create secure session
                session_regenerate_id(true); // Prevent session fixation
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['role'] = $user['role'];
                $_SESSION['email'] = $user['email'];
                $_SESSION['profile_name'] = $profileName;
                $_SESSION['last_activity'] = time();

                // Redirect
                if ($user['role'] === ROLE_HR) {
                    redirect(BASE_URL . '/hr/dashboard.php');
                } else {
                    redirect(BASE_URL . '/pelamar/dashboard.php');
                }
            } else {
                // Login Failed
                $error = 'Email atau kata sandi tidak valid.';
            }
        } catch (PDOException $e) {
            error_log("Login error: " . $e->getMessage());
            $error = 'Terjadi kesalahan sistem. Silakan coba lagi.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Masuk — <?= APP_NAME ?></title>
    <meta name="description" content="Masuk ke portal RecruitPro Enterprise">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="<?= BASE_URL ?>/assets/js/theme.js"></script>
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/design-system.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/components.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/animations.css">
</head>
<body class="bg-slate-900 text-slate-200 font-sans antialiased min-h-screen flex flex-col relative overflow-x-hidden">
    
    <!-- Background Accents -->
    <div class="fixed top-[-10%] left-[-10%] w-[40%] h-[40%] bg-indigo-600/20 rounded-full blur-[120px] pointer-events-none"></div>
    <div class="fixed bottom-[-10%] right-[-10%] w-[40%] h-[40%] bg-emerald-600/10 rounded-full blur-[120px] pointer-events-none"></div>

    <?php require_once __DIR__ . '/../components/header.php'; ?>

    <main class="flex-grow flex items-center justify-center p-6 relative z-10">
        
        <div class="w-full max-w-md glass-card-elevated p-8 sm:p-10 reveal">
            <!-- Brand / Header -->
            <div class="text-center mb-8">
                <div class="inline-flex w-14 h-14 rounded-2xl bg-gradient-to-br from-indigo-500 to-indigo-600 items-center justify-center shadow-[0_8px_16px_rgba(99,102,241,0.4)] mb-5">
                    <svg class="w-7 h-7 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z" />
                    </svg>
                </div>
                <h1 class="text-2xl sm:text-3xl font-bold text-white tracking-tight mb-2">Selamat Datang Kembali</h1>
                <p class="text-sm text-slate-400">Masuk untuk melanjutkan ke portal RecruitPro.</p>
            </div>

            <!-- Error Message -->
            <?php if (!empty($error)): ?>
                <div class="mb-6 bg-rose-500/10 border border-rose-500/20 text-rose-400 px-4 py-3 rounded-xl flex items-start gap-3 animate-[fadeInDown_0.3s_ease-out]">
                    <svg class="w-5 h-5 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                    <span class="text-sm font-medium"><?= htmlspecialchars($error) ?></span>
                </div>
            <?php endif; ?>
            
            <?php if (isset($_GET['registered'])): ?>
                <div class="mb-6 bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 px-4 py-3 rounded-xl flex items-start gap-3 animate-[fadeInDown_0.3s_ease-out]">
                    <svg class="w-5 h-5 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span class="text-sm font-medium">Pendaftaran berhasil! Silakan masuk.</span>
                </div>
            <?php endif; ?>

            <!-- Login Form -->
            <form action="<?= htmlspecialchars($_SERVER['PHP_SELF']) ?>" method="POST" class="space-y-5">
                <?= csrfField() ?>
                
                <div class="space-y-1.5">
                    <label for="email" class="block text-sm font-medium text-slate-300">Alamat Email</label>
                    <input type="email" id="email" name="email" value="<?= htmlspecialchars($email) ?>" required autofocus
                           class="w-full bg-slate-900/50 border border-slate-700 rounded-xl px-4 py-3 text-slate-200 placeholder-slate-400 focus:outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 transition-all"
                           placeholder="nama@email.com">
                </div>

                <div class="space-y-1.5">
                    <div class="flex items-center justify-between">
                        <label for="password" class="block text-sm font-medium text-slate-300">Kata Sandi</label>
                        <a href="#" class="text-xs font-semibold text-indigo-600 dark:text-indigo-400 hover:underline transition-colors">Lupa sandi?</a>
                    </div>
                    <input type="password" id="password" name="password" required
                           class="w-full bg-slate-900/50 border border-slate-700 rounded-xl px-4 py-3 text-slate-200 placeholder-slate-400 focus:outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 transition-all"
                           placeholder="••••••••">
                </div>

                <button type="submit" class="w-full btn-primary rounded-xl py-3.5 text-sm">
                    Masuk ke Sistem
                </button>
            </form>

            <div class="mt-8 text-center">
                <p class="text-sm text-slate-400">
                    Belum punya akun? 
                    <a href="register.php" class="font-semibold text-indigo-600 dark:text-indigo-400 hover:underline transition-colors">Daftar Sekarang</a>
                </p>
            </div>
        </div>
    </main>

    <?php require_once __DIR__ . '/../components/footer.php'; ?>
</body>
</html>



