<?php
/*
 * @Module:      Save Job AJAX Endpoint
 * @Author:      BE-05 (Search & Filter Engine)
 * @Date:        2026-05-24
 * @Description: Endpoint for toggling save/bookmark job.
 */

require_once __DIR__ . '/../../config/app.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../auth/session.php';
require_once __DIR__ . '/../../includes/functions.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== ROLE_PELAMAR) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

$jobId = $_POST['job_id'] ?? null;
if (!$jobId) {
    http_response_code(400);
    echo json_encode(['error' => 'Job ID is required']);
    exit;
}

$pdo = Database::getConnection();

try {
    // Get Pelamar Profile ID
    $stmt = $pdo->prepare("SELECT id FROM pelamar_profiles WHERE user_id = :uid");
    $stmt->execute(['uid' => $_SESSION['user_id']]);
    $prof = $stmt->fetch();

    if (!$prof) {
        http_response_code(403);
        echo json_encode(['error' => 'Profile not found']);
        exit;
    }

    $pelamarId = $prof['id'];

    // Check if already saved
    $stmt = $pdo->prepare("SELECT id FROM saved_jobs WHERE pelamar_profile_id = :pid AND job_id = :jid");
    $stmt->execute(['pid' => $pelamarId, 'jid' => $jobId]);
    $existing = $stmt->fetch();

    if ($existing) {
        // Unsave
        $stmtDel = $pdo->prepare("DELETE FROM saved_jobs WHERE id = :id");
        $stmtDel->execute(['id' => $existing['id']]);
        echo json_encode(['status' => 'unsaved', 'message' => 'Lowongan dihapus dari tersimpan']);
    } else {
        // Save
        $stmtIns = $pdo->prepare("INSERT INTO saved_jobs (pelamar_profile_id, job_id) VALUES (:pid, :jid)");
        $stmtIns->execute(['pid' => $pelamarId, 'jid' => $jobId]);
        echo json_encode(['status' => 'saved', 'message' => 'Lowongan berhasil disimpan']);
    }
} catch (PDOException $e) {
    error_log("Save Job Error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'Database error']);
}
