<?php
/*
 * @Module:      Apply Job Handler
 * @Author:      BE-05 (Search & Filter Engine)
 * @Date:        2026-05-24
 * @Description: POST handler for submitting a job application.
 * @Ownership:   BE-05
 */

require_once __DIR__ . '/../../config/app.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/security.php';
require_once __DIR__ . '/../../auth/session.php';
require_once __DIR__ . '/../../auth/middleware.php';
require_once __DIR__ . '/../../includes/functions.php';

requirePelamar();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect(BASE_URL . '/pelamar/jobs/browse.php');
}

enforceCsrf();

$jobId = $_POST['job_id'] ?? null;
$coverLetter = trim($_POST['cover_letter'] ?? '');

if (!$jobId) {
    redirect(BASE_URL . '/pelamar/jobs/browse.php');
}

$pdo = Database::getConnection();

try {
    // 1. Get Pelamar Profile ID
    $stmt = $pdo->prepare("SELECT id, cv_file_path FROM pelamar_profiles WHERE user_id = :uid");
    $stmt->execute(['uid' => $_SESSION['user_id']]);
    $prof = $stmt->fetch();

    if (!$prof) {
        flashMessage('error', 'Profil tidak ditemukan.');
        redirect(BASE_URL . '/pelamar/jobs/detail.php?id=' . $jobId);
    }

    if (empty($prof['cv_file_path'])) {
        flashMessage('error', 'Anda harus mengunggah CV terlebih dahulu.');
        redirect(BASE_URL . '/pelamar/profile/edit.php');
    }

    $pelamarId = $prof['id'];

    // 2. Verify Job Exists and is Active
    $stmt = $pdo->prepare("SELECT id FROM jobs WHERE id = :jid AND status = 'active'");
    $stmt->execute(['jid' => $jobId]);
    if (!$stmt->fetch()) {
        flashMessage('error', 'Lowongan kerja tidak aktif atau tidak ditemukan.');
        redirect(BASE_URL . '/pelamar/jobs/browse.php');
    }

    // 3. Check if already applied (handled by DB Unique Constraint, but good to check first)
    $stmt = $pdo->prepare("SELECT id FROM applications WHERE pelamar_profile_id = :pid AND job_id = :jid");
    $stmt->execute(['pid' => $pelamarId, 'jid' => $jobId]);
    if ($stmt->fetch()) {
        flashMessage('error', 'Anda sudah melamar pekerjaan ini.');
        redirect(BASE_URL . '/pelamar/jobs/detail.php?id=' . $jobId);
    }

    // 4. Insert Application using Transaction
    $pdo->beginTransaction();

    $stmtApp = $pdo->prepare("
        INSERT INTO applications (pelamar_profile_id, job_id, current_status, cover_letter, applied_at, updated_at) 
        VALUES (:pid, :jid, :status, :cover, NOW(), NOW())
    ");
    $stmtApp->execute([
        'pid' => $pelamarId,
        'jid' => $jobId,
        'status' => STATUS_APPLIED,
        'cover' => $coverLetter
    ]);
    
    $appId = $pdo->lastInsertId();

    // 5. Insert Initial Status History
    $stmtHist = $pdo->prepare("
        INSERT INTO status_history (application_id, status, notes, changed_by_user_id, changed_at) 
        VALUES (:appid, :status, :notes, :uid, NOW())
    ");
    $stmtHist->execute([
        'appid' => $appId,
        'status' => STATUS_APPLIED,
        'notes' => 'Lamaran berhasil dikirim oleh pelamar.',
        'uid' => $_SESSION['user_id']
    ]);

    $pdo->commit();

    flashMessage('success', 'Lamaran Anda berhasil dikirim! Semoga sukses.');
    redirect(BASE_URL . '/pelamar/jobs/detail.php?id=' . $jobId);

} catch (PDOException $e) {
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    error_log("Apply error: " . $e->getMessage());
    flashMessage('error', 'Terjadi kesalahan sistem saat mengirim lamaran.');
    redirect(BASE_URL . '/pelamar/jobs/detail.php?id=' . $jobId);
}
