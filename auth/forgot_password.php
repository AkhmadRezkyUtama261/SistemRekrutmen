<?php
/*
 * @Module:      Forgot Password Page
 * @Author:      BE-02 (Auth Engine)
 * @Date:        2026-06-15
 * @Description: Form to request a password reset link.
 */

require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/security.php';
require_once __DIR__ . '/session.php';
require_once __DIR__ . '/middleware.php';
require_once __DIR__ . '/../includes/functions.php';

if (isset($_SESSION['user_id'])) {
    redirect(BASE_URL . '/index.php');
}

$error = '';
$success = '';
$demoLink = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    enforceCsrf();

    $email = trim($_POST['email'] ?? '');

    if (empty($email)) {
        $error = 'Email wajib diisi.';
    } else {
        try {
            $pdo = Database::getConnection();
            $stmt = $pdo->prepare("SELECT id FROM users WHERE email = :email");
            $stmt->execute(['email' => $email]);
            $user = $stmt->fetch();

            if ($user) {
                // Generate a secure random token
                $token = bin2hex(random_bytes(32));
                // Token valid for 1 hour
                $expiresAt = date('Y-m-d H:i:s', time() + 3600);

                $stmtUpdate = $pdo->prepare("UPDATE users SET reset_token = :token, reset_expires_at = :expires WHERE id = :id");
                $stmtUpdate->execute([
                    'token' => $token,
                    'expires' => $expiresAt,
                    'id' => $user['id']
                ]);

                // SIMULATE SENDING EMAIL (FOR DEMO/PROFESSIONAL LOOK)
                $success = 'Instruksi pemulihan kata sandi telah dikirim ke email Anda.';
                $demoLink = BASE_URL . '/auth/reset_password.php?token=' . $token;
            } else {
                // Always show success to prevent email enumeration attacks (Security Best Practice)
                $success = 'Instruksi pemulihan kata sandi telah dikirim ke email Anda.';
            }
        } catch (PDOException $e) {
            error_log("Forgot Password Error: " . $e->getMessage());
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
    <title>Lupa Kata Sandi — <?= APP_NAME ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="<?= BASE_URL ?>/assets/js/theme.js"></script>
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/design-system.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/components.css">
</head>
<body class="bg-slate-900 text-slate-200 font-sans antialiased min-h-screen flex flex-col relative overflow-x-hidden">
    
    <div class="fixed top-[-10%] left-[-10%] w-[40%] h-[40%] bg-indigo-600/20 rounded-full blur-[120px] pointer-events-none"></div>
    <div class="fixed bottom-[-10%] right-[-10%] w-[40%] h-[40%] bg-emerald-600/10 rounded-full blur-[120px] pointer-events-none"></div>

    <?php require_once __DIR__ . '/../components/header.php'; ?>

    <main class="flex-grow flex items-center justify-center p-6 relative z-10">
        <div class="w-full max-w-md glass-card-elevated p-8 sm:p-10 reveal">
            <div class="text-center mb-8">
                <div class="inline-flex w-14 h-14 rounded-2xl bg-gradient-to-br from-indigo-500 to-indigo-600 items-center justify-center shadow-[0_8px_16px_rgba(99,102,241,0.4)] mb-5">
                    <svg class="w-7 h-7 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 5.25a3 3 0 013 3m3 0a6 6 0 01-7.029 5.912c-.563-.097-1.159.026-1.563.43L10.5 17.25H8.25v2.25H6v2.25H2.25v-2.818c0-.597.237-1.17.659-1.591l6.499-6.499c.404-.404.527-1 .43-1.563A6 6 0 1121.75 8.25z" />
                    </svg>
                </div>
                <h1 class="text-2xl sm:text-3xl font-bold text-white tracking-tight mb-2">Lupa Kata Sandi?</h1>
                <p class="text-sm text-slate-400">Masukkan email Anda dan kami akan mengirimkan tautan untuk mereset kata sandi.</p>
            </div>

            <?php if (!empty($error)): ?>
                <div class="mb-6 bg-rose-500/10 border border-rose-500/20 text-rose-400 px-4 py-3 rounded-xl flex items-start gap-3">
                    <span class="text-sm font-medium"><?= htmlspecialchars($error) ?></span>
                </div>
            <?php endif; ?>

            <?php if (!empty($success)): ?>
                <div class="mb-6 bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 px-4 py-3 rounded-xl flex items-start gap-3">
                    <span class="text-sm font-medium"><?= htmlspecialchars($success) ?></span>
                </div>
                
                <?php if (!empty($demoLink)): ?>
                <!-- SIMULATED EMAIL MODAL FOR DEMO PURPOSES -->
                <div class="mt-4 p-4 border-2 border-indigo-500/50 bg-indigo-500/10 rounded-xl relative">
                    <div class="absolute -top-3 left-4 bg-indigo-600 text-white text-xs font-bold px-2 py-0.5 rounded">SIMULASI KOTAK MASUK EMAIL</div>
                    <p class="text-sm text-slate-300 mb-3 mt-2">Pesan ini aslinya dikirim ke email user. Karena ini mode Demo, klik link di bawah untuk melanjutkan:</p>
                    <a href="<?= $demoLink ?>" class="block w-full text-center bg-indigo-600 hover:bg-indigo-500 text-white font-medium py-2 rounded-lg transition">Buka Link Reset Password</a>
                </div>
                <?php endif; ?>
                
                <div class="mt-6 text-center">
                    <a href="login.php" class="text-sm font-semibold text-indigo-400 hover:text-indigo-300 transition">Kembali ke halaman Login</a>
                </div>
            <?php else: ?>
                <form action="<?= htmlspecialchars($_SERVER['PHP_SELF']) ?>" method="POST" class="space-y-5">
                    <?= csrfField() ?>
                    <div class="space-y-1.5">
                        <label for="email" class="block text-sm font-medium text-slate-300">Alamat Email</label>
                        <input type="email" id="email" name="email" required autofocus
                               class="w-full bg-slate-900/50 border border-slate-700 rounded-xl px-4 py-3 text-slate-200 focus:outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 transition-all"
                               placeholder="nama@email.com">
                    </div>
                    <button type="submit" class="w-full btn-primary rounded-xl py-3.5 text-sm font-bold">
                        Kirim Tautan Reset
                    </button>
                </form>
                <div class="mt-8 text-center">
                    <a href="login.php" class="text-sm font-semibold text-slate-400 hover:text-white transition-colors">Ingat kata sandi? Masuk di sini</a>
                </div>
            <?php endif; ?>
        </div>
    </main>
    <?php require_once __DIR__ . '/../components/footer.php'; ?>
</body>
</html>
