<?php
// register.php
session_start();

// Nanti di sini kita hubungkan ke file database milik Dev jika sudah siap
// require_once 'config/database.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    // Validasi sederhana: Cek apakah password dan konfirmasi password cocok
    if ($password !== $confirm_password) {
        $error = 'Konfirmasi password tidak cocok!';
    } else {
        // Logika sementara, nanti data ini akan di-INSERT ke database MySQL bagian Dev
        // Contoh sukses pendaftaran dummy:
        $success = 'Pendaftaran berhasil! Silakan login.';
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Akun - Sistem Rekrutmen</title>
    <!-- Menggunakan Tailwind CSS -->
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
</head>
<body class="bg-slate-100 flex items-center justify-center min-h-screen py-10">

    <div class="bg-white p-8 rounded-xl shadow-lg w-full max-w-md border border-slate-200">
        <h2 class="text-2xl font-bold mb-2 text-center text-slate-800">Daftar Akun Baru</h2>
        <p class="text-sm text-center text-slate-500 mb-6">Lengkapi data di bawah untuk membuat akun pelamar</p>
        
        <!-- Notifikasi jika ada error -->
        <?php if ($error): ?>
            <div class="bg-red-50 text-red-600 border border-red-200 p-3 rounded-lg mb-4 text-sm text-center font-medium">
                <?= $error; ?>
            </div>
        <?php endif; ?>

        <!-- Notifikasi jika sukses -->
        <?php if ($success): ?>
            <div class="bg-green-50 text-green-600 border border-green-200 p-3 rounded-lg mb-4 text-sm text-center font-medium">
                <?= $success; ?>
                <div class="mt-2">
                    <a href="login.php" class="text-blue-600 underline font-semibold">Klik di sini untuk Login</a>
                </div>
            </div>
        <?php endif; ?>

        <form action="" method="POST" class="space-y-4">
            <div>
                <label class="block text-slate-700 text-sm font-semibold mb-1">Username</label>
                <input type="text" name="username" placeholder="Buat username kamu" class="w-full p-2.5 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" required>
            </div>

            <div>
                <label class="block text-slate-700 text-sm font-semibold mb-1">Email</label>
                <input type="email" name="email" placeholder="contoh@email.com" class="w-full p-2.5 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" required>
            </div>
            
            <div>
                <label class="block text-slate-700 text-sm font-semibold mb-1">Password</label>
                <input type="password" name="password" placeholder="Buat password minimal 6 karakter" class="w-full p-2.5 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" required>
            </div>

            <div>
                <label class="block text-slate-700 text-sm font-semibold mb-1">Konfirmasi Password</label>
                <input type="password" name="confirm_password" placeholder="Ulangi password kamu" class="w-full p-2.5 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" required>
            </div>

            <button type="submit" class="w-full bg-blue-600 text-white p-2.5 rounded-lg font-semibold hover:bg-blue-700 shadow-md shadow-blue-200 transition active:scale-[0.98]">
                Daftar Sekarang
            </button>
        </form>

        <div class="mt-6 text-center text-sm text-slate-600">
            Sudah punya akun? <a href="login.php" class="text-blue-600 font-medium hover:underline">Masuk di sini</a>
        </div>
    </div>

</body>
</html>