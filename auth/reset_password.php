<?php
/*
 * @Module:      Reset Password Page
 * @Author:      BE-02 (Auth Engine)
 * @Date:        2026-06-15
 * @Description: Form to reset password using a secure token.
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
$token = $_GET['token'] ?? ($_POST['token'] ?? '');

if (empty($token)) {
    $error = 'Tautan tidak valid atau tidak lengkap.';
} else {
    try {
        $pdo = Database::getConnection();
        // Verify Token
        $stmt = $pdo->prepare("SELECT id, email, reset_expires_at FROM users WHERE reset_token = :token");
        $stmt->execute(['token' => $token]);
        $user = $stmt->fetch();

        if (!$user) {
            $error = 'Tautan reset kata sandi tidak valid atau sudah tidak berlaku.';
        } elseif (strtotime($user['reset_expires_at']) < time()) {
            $error = 'Tautan reset kata sandi telah kedaluwarsa. Silakan ajukan permintaan baru.';
        } else {
            // Token is valid, handle password reset submission
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                enforceCsrf();
                $password = $_POST['password'] ?? '';
                $confirm = $_POST['confirm_password'] ?? '';

                if (strlen($password) < 8) {
                    $error = 'Kata sandi minimal 8 karakter.';
                } elseif ($password !== $confirm) {
                    $error = 'Konfirmasi kata sandi tidak cocok.';
                } else {
                    // Update password and clear token
                    $hash = password_hash($password, PASSWORD_DEFAULT);
                    $updateStmt = $pdo->prepare("UPDATE users SET password_hash = :hash, reset_token = NULL, reset_expires_at = NULL WHERE id = :id");
                    $updateStmt->execute([
                        'hash' => $hash,
                        'id' => $user['id']
                    ]);
                    $success = 'Kata sandi berhasil diubah! Anda sekarang dapat masuk dengan kata sandi baru Anda.';
                }
            }
        }
    } catch (PDOException $e) {
        error_log("Reset Password Error: " . $e->getMessage());
        $error = 'Terjadi kesalahan sistem. Silakan coba lagi.';
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Atur Ulang Kata Sandi — <?= APP_NAME ?></title>
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
                        <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z" />
                    </svg>
                </div>
                <h1 class="text-2xl sm:text-3xl font-bold text-white tracking-tight mb-2">Atur Ulang Sandi</h1>
                <p class="text-sm text-slate-400">Buat kata sandi baru untuk akun Anda.</p>
            </div>

            <?php if (!empty($error)): ?>
                <div class="mb-6 bg-rose-500/10 border border-rose-500/20 text-rose-400 px-4 py-3 rounded-xl flex items-start gap-3">
                    <span class="text-sm font-medium"><?= htmlspecialchars($error) ?></span>
                </div>
                <div class="text-center">
                    <a href="forgot_password.php" class="text-sm font-semibold text-indigo-400 hover:text-white transition">Kirim ulang tautan reset</a>
                </div>
            <?php elseif (!empty($success)): ?>
                <div class="mb-6 bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 px-4 py-3 rounded-xl flex items-start gap-3">
                    <span class="text-sm font-medium"><?= htmlspecialchars($success) ?></span>
                </div>
                <a href="login.php" class="w-full btn-primary block text-center rounded-xl py-3.5 text-sm font-bold">
                    Lanjutkan ke Login
                </a>
            <?php else: ?>
                <form action="<?= htmlspecialchars($_SERVER['PHP_SELF']) ?>" method="POST" class="space-y-5">
                    <?= csrfField() ?>
                    <input type="hidden" name="token" value="<?= htmlspecialchars($token) ?>">
                    
                    <div class="space-y-1.5">
                        <label for="password" class="block text-sm font-medium text-slate-300">Kata Sandi Baru</label>
                        <input type="password" id="password" name="password" required autofocus minlength="8"
                               class="w-full bg-slate-900/50 border border-slate-700 rounded-xl px-4 py-3 text-slate-200 focus:outline-none focus:border-indigo-500 transition-all"
                               placeholder="Minimal 8 karakter">
                    </div>
                    
                    <div class="space-y-1.5">
                        <label for="confirm_password" class="block text-sm font-medium text-slate-300">Konfirmasi Kata Sandi Baru</label>
                        <input type="password" id="confirm_password" name="confirm_password" required minlength="8"
                               class="w-full bg-slate-900/50 border border-slate-700 rounded-xl px-4 py-3 text-slate-200 focus:outline-none focus:border-indigo-500 transition-all"
                               placeholder="Ulangi kata sandi">
                    </div>

                    <button type="submit" class="w-full btn-primary rounded-xl py-3.5 text-sm font-bold mt-2">
                        Simpan Kata Sandi Baru
                    </button>
                </form>
            <?php endif; ?>
        </div>
    </main>
    <?php require_once __DIR__ . '/../components/footer.php'; ?>
</body>
</html>
