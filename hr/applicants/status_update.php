<?php
/*
 * @Module:      Application Status Updater
 * @Author:      BE-06 (Status & Notifications)
 * @Date:        2026-05-24
 * @Description: Handles HR updating an applicant's status and sending email.
 * @Ownership:   BE-06
 */

require_once __DIR__ . '/../../config/app.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/security.php';
require_once __DIR__ . '/../../auth/session.php';
require_once __DIR__ . '/../../auth/middleware.php';

// Try to load includes safely
if (file_exists(__DIR__ . '/../../includes/functions.php')) {
    require_once __DIR__ . '/../../includes/functions.php';
}
if (file_exists(__DIR__ . '/../../includes/email_handler.php')) {
    require_once __DIR__ . '/../../includes/email_handler.php';
}

requireHR();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect(BASE_URL . '/hr/applicants/list.php');
}

enforceCsrf();

$applicationId = $_POST['application_id'] ?? null;
$newStatus = $_POST['new_status'] ?? null;

if (!$applicationId || !$newStatus) {
    flashMessage('error', 'Data tidak lengkap.');
    redirect(BASE_URL . '/hr/applicants/list.php');
}

$pdo = Database::getConnection();

try {
    // 1. Verify HR ownership of this application
    $stmt = $pdo->prepare("
        SELECT a.id, a.current_status, j.title, p.full_name, u.email as applicant_email
        FROM applications a
        JOIN jobs j ON a.job_id = j.id
        JOIN hr_profiles h ON j.hr_profile_id = h.id
        JOIN pelamar_profiles p ON a.pelamar_profile_id = p.id
        JOIN users u ON p.user_id = u.id
        WHERE a.id = :id AND h.user_id = :hr_uid
    ");
    $stmt->execute(['id' => $applicationId, 'hr_uid' => $_SESSION['user_id']]);
    $appData = $stmt->fetch();

    if (!$appData) {
        flashMessage('error', 'Otoritas ditolak atau lamaran tidak ditemukan.');
        redirect(BASE_URL . '/hr/applicants/list.php');
    }

    if ($appData['current_status'] === $newStatus) {
        flashMessage('error', 'Status sudah sama dengan status saat ini.');
        redirect(BASE_URL . '/hr/applicants/list.php');
    }

    // 2. Update Status and Log History
    $pdo->beginTransaction();

    $stmtUpdate = $pdo->prepare("UPDATE applications SET current_status = :status, updated_at = NOW() WHERE id = :id");
    $stmtUpdate->execute(['status' => $newStatus, 'id' => $applicationId]);

    $stmtHist = $pdo->prepare("
        INSERT INTO status_history (application_id, status, notes, changed_by_user_id, changed_at) 
        VALUES (:appid, :status, :notes, :uid, NOW())
    ");
    $stmtHist->execute([
        'appid' => $applicationId,
        'status' => $newStatus,
        'notes' => 'Status diubah oleh HR perusahaan.',
        'uid' => $_SESSION['user_id']
    ]);

    $pdo->commit();

    // 3. Send Email Notification (If email_handler is fully set up)
    if (function_exists('sendStatusUpdateEmail')) {
        sendStatusUpdateEmail($appData['applicant_email'], $appData['full_name'], $appData['title'], $newStatus);
    }

    flashMessage('success', 'Status lamaran berhasil diperbarui.');
    redirect(BASE_URL . '/hr/applicants/list.php');

} catch (PDOException $e) {
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    error_log("Update status error: " . $e->getMessage());
    flashMessage('error', 'Terjadi kesalahan sistem saat memperbarui status.');
    redirect(BASE_URL . '/hr/applicants/list.php');
}