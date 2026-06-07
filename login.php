<?php
// login.php
session_start();

// Nanti di sini kita hubungkan ke file database milik Dev/kelompok jika sudah siap
// require_once 'config/database.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    // Ini database sementara (dummy) untuk tes login, nanti bisa disesuaikan
    if ($username === 'admin' && $password === 'admin123') {
        $_SESSION['user'] = $username;
        header('Location: index.php'); // Jika sukses, diarahkan ke halaman utama
        exit;
    } else {
        $error = 'Username atau password salah!';
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Sistem Rekrutmen</title>
    <!-- Menggunakan Tailwind CSS untuk desain eksterior form -->
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
</head>
<body class="bg-slate-100 flex items-center justify-center h-screen">

    <div class="bg-white p-8 rounded-xl shadow-lg w-full max-w-md border border-slate-200">
        <h2 class="text-2xl font-bold mb-2 text-center text-slate-800">Sistem Rekrutmen</h2>
        <p class="text-sm text-center text-slate-500 mb-6">Silakan masuk menggunakan akun Anda</p>
        
        <!-- Notifikasi jika ada error login -->
        <?php if ($error): ?>
            <div class="bg-red-50 text-red-600 border border-red-200 p-3 rounded-lg mb-4 text-sm text-center font-medium">
                <?= $error; ?>
            </div>
        <?php endif; ?>

        <form action="" method="POST" class="space-y-4">
            <div>
                <label class="block text-slate-700 text-sm font-semibold mb-1">Username / Email</label>
                <input type="text" name="username" placeholder="Masukkan username" class="w-full p-2.5 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500" required>
            </div>
            
            <div>
                <div class="flex justify-between items-center mb-1">
                    <label class="block text-slate-700 text-sm font-semibold">Password</label>
                    <a href="reset_pwd.php" class="text-xs text-blue-600 hover:underline">Lupa Password?</a>
                </div>
                <input type="password" name="password" placeholder="Masukkan password" class="w-full p-2.5 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500" required>
            </div>

            <button type="submit" class="w-full bg-blue-600 text-white p-2.5 rounded-lg font-semibold hover:bg-blue-700 shadow-md shadow-blue-200 transition active:scale-[0.98]">
                Masuk
            </button>
        </form>

        <div class="mt-6 text-center text-sm text-slate-600">
            Belum punya akun? <a href="register.php" class="text-blue-600 font-medium hover:underline">Daftar sekarang</a>
        </div>
    </div>

</body>
</html>