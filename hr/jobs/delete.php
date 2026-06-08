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

// Perform soft delete (set status to closed)
try {
    $stmt = $pdo->prepare("UPDATE jobs SET status = :status, updated_at = NOW() WHERE id = :id AND hr_profile_id = :hr");
    $stmt->execute([
        'status' => JOB_STATUS_CLOSED,
        'id' => $jobId,
        'hr' => $hrId
    ]);
    flashMessage('success', 'Lowongan berhasil ditutup.');
} catch (PDOException $e) {
    error_log("Error closing job: " . $e->getMessage());
    flashMessage('error', 'Terjadi kesalahan sistem saat menutup lowongan.');
}

redirect(BASE_URL . '/hr/jobs/list.php');
