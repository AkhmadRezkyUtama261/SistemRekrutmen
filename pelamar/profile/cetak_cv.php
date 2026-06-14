<?php
/*
 * @Feature:     Auto-Generate CV (Print to PDF)
 * @Author:      Muhammad Randyano (Randy)
 * @Description: Mengambil data profil pelamar dan mengubahnya menjadi format CV siap cetak.
 */

require_once __DIR__ . '/../../config/app.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../auth/session.php';

// Pastikan pelamar sudah login
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== ROLE_PELAMAR) {
    header('Location: ' . BASE_URL . '/auth/login.php');
    exit;
}

try {
    // Mengambil data profil lengkap pelamar
    $stmt = Database::getConnection()->prepare("
        SELECT p.*, u.email 
        FROM pelamar_profiles p
        JOIN users u ON p.user_id = u.id
        WHERE p.user_id = :uid
    ");
    $stmt->execute(['uid' => $_SESSION['user_id']]);
    $profile = $stmt->fetch();
    
    if (!$profile) {
        die("Profil tidak ditemukan! Silakan lengkapi profil Anda terlebih dahulu di dashboard.");
    }
} catch (PDOException $e) {
    die("Terjadi kesalahan database: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cetak CV — <?= htmlspecialchars($profile['full_name']) ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f1f5f9;
            color: #0f172a;
            margin: 0;
            padding: 40px;
            display: flex;
            justify-content: center;
        }
        .cv-container {
            background-color: #ffffff;
            width: 100%;
            max-width: 800px;
            padding: 50px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.1);
            border-top: 8px solid #4f46e5;
        }
        .header {
            text-align: center;
            border-bottom: 2px solid #e2e8f0;
            padding-bottom: 20px;
            margin-bottom: 30px;
        }
        .header h1 {
            margin: 0 0 10px 0;
            font-size: 32px;
            color: #1e293b;
            letter-spacing: -0.5px;
        }
        .contact-info {
            color: #64748b;
            font-size: 14px;
        }
        .contact-info span {
            margin: 0 10px;
        }
        .section {
            margin-bottom: 30px;
        }
        .section-title {
            font-size: 18px;
            font-weight: 700;
            color: #4f46e5;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 15px;
            border-left: 4px solid #4f46e5;
            padding-left: 10px;
        }
        .content-text {
            line-height: 1.6;
            color: #334155;
            font-size: 15px;
            white-space: pre-wrap;
        }
        .print-btn {
            position: fixed;
            bottom: 30px;
            right: 30px;
            background-color: #4f46e5;
            color: white;
            border: none;
            padding: 12px 24px;
            border-radius: 50px;
            font-weight: bold;
            font-size: 16px;
            cursor: pointer;
            box-shadow: 0 4px 12px rgba(79,70,229,0.3);
            transition: transform 0.2s;
        }
        .print-btn:hover {
            transform: translateY(-2px);
        }
        /* Mode Print */
        @media print {
            body { background: white; padding: 0; }
            .cv-container { box-shadow: none; max-width: 100%; border-top: 4px solid #4f46e5; }
            .print-btn { display: none; }
        }
    </style>
</head>
<body>

    <div class="cv-container">
        <div class="header">
            <h1><?= htmlspecialchars($profile['full_name'] ?: 'Nama Pelamar') ?></h1>
            <div class="contact-info">
                <span>📧 <?= htmlspecialchars($profile['email']) ?></span>
                |
                <span>📱 <?= htmlspecialchars($profile['phone'] ?: 'Belum diatur') ?></span>
            </div>
        </div>

        <div class="section">
            <div class="section-title">Profil Profesional</div>
            <div class="content-text"><?= htmlspecialchars($profile['resume_text'] ?: 'Pelamar belum menambahkan profil profesional atau ringkasan pengalaman kerja mereka. Silakan lengkapi di dashboard.') ?></div>
        </div>

        <div class="section">
            <div class="section-title">Keahlian (Skills)</div>
            <div class="content-text"><?= htmlspecialchars($profile['skills'] ?: 'Belum ada data keahlian.') ?></div>
        </div>

        <div class="section">
            <div class="section-title">Portofolio / Tautan</div>
            <div class="content-text"><?= htmlspecialchars($profile['portfolio_url'] ?: 'Tidak ada tautan portofolio.') ?></div>
        </div>
    </div>

    <button class="print-btn" onclick="window.print()">🖨️ Cetak PDF</button>

</body>
</html>
