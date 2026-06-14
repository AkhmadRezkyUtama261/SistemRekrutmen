<?php
/*
 * @Module:      Close/Delete Job Posting
 * @Author:      FE-03 (HR Dashboard UI)
 * @Date:        2026-05-24
 * @Description: Soft delete (close) a job posting.
 * @Ownership:   FE-03
 */

require_once __DIR__ . '/../../config/app.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/security.php';
require_once __DIR__ . '/../../auth/session.php';
require_once __DIR__ . '/../../auth/middleware.php';
require_once __DIR__ . '/../../includes/functions.php';

requireHR();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect(BASE_URL . '/hr/jobs/list.php');
}

enforceCsrf();

$jobId = $_POST['id'] ?? null;
if (!$jobId) {
    redirect(BASE_URL . '/hr/jobs/list.php');
}

$pdo = Database::getConnection();

// Get HR ID
$stmt = $pdo->prepare("SELECT id FROM hr_profiles WHERE user_id = :uid");
$stmt->execute(['uid' => $_SESSION['user_id']]);
$hrId = $stmt->fetchColumn();

// Verify ownership
$stmt = $pdo->prepare("SELECT id FROM jobs WHERE id = :id AND hr_profile_id = :hr");
$stmt->execute(['id' => $jobId, 'hr' => $hrId]);
if (!$stmt->fetch()) {
    flashMessage('error', 'Lowongan tidak ditemukan atau Anda tidak memiliki akses.');
    redirect(BASE_URL . '/hr/jobs/list.php');
}

$action = $_POST['action'] ?? 'close';

// Perform action
try {
    if ($action === 'delete') {
        $stmt = $pdo->prepare("DELETE FROM jobs WHERE id = :id AND hr_profile_id = :hr");
        $stmt->execute(['id' => $jobId, 'hr' => $hrId]);
        flashMessage('success', 'Lowongan berhasil dihapus secara permanen beserta seluruh data pelamarnya.');
    } else {
        $stmt = $pdo->prepare("UPDATE jobs SET status = :status, updated_at = NOW() WHERE id = :id AND hr_profile_id = :hr");
        $stmt->execute([
            'status' => JOB_STATUS_CLOSED,
            'id' => $jobId,
            'hr' => $hrId
        ]);
        flashMessage('success', 'Lowongan berhasil ditutup. Pelamar tidak bisa mendaftar lagi.');
    }
} catch (PDOException $e) {
    error_log("Error processing job action: " . $e->getMessage());
    flashMessage('error', 'Terjadi kesalahan sistem saat memproses lowongan.');
}

redirect(BASE_URL . '/hr/jobs/list.php');
