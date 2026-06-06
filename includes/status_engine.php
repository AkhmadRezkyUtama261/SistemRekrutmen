<?php
/*
 * @Module:      Application Status Workflow Engine
 * @Author:      BE-06 (Status & Email Lead)
 * @Date:        2026-05-24
 * @Description: Application status transition management with strict
 *               validation, audit trail logging, and workflow enforcement.
 *               Status flow: applied → under_review → interview → accepted/rejected
 * @Ownership:   BE-06
 *
 * RecruitPro Enterprise — © 2026
 * Do NOT modify without PR approval from module owner.
 */

require_once __DIR__ . '/../config/database.php';

// ══════════════════════════════════════════════════════════════
// STATUS TRANSITION MAP
// ══════════════════════════════════════════════════════════════

/**
 * Define valid status transitions.
 *
 * Status Flow:
 *   applied ──→ under_review ──→ interview ──→ accepted
 *      │              │               │
 *      └──→ rejected  └──→ rejected   └──→ rejected
 *
 * Terminal states (no further transitions):
 *   - accepted
 *   - rejected
 *
 * @return array Map of current_status => [allowed_next_statuses]
 */
function getValidTransitions(): array
{
    return [
        STATUS_APPLIED      => [STATUS_UNDER_REVIEW, STATUS_REJECTED],
        STATUS_UNDER_REVIEW => [STATUS_INTERVIEW, STATUS_REJECTED],
        STATUS_INTERVIEW    => [STATUS_ACCEPTED, STATUS_REJECTED],
        STATUS_ACCEPTED     => [],  // Terminal state
        STATUS_REJECTED     => [],  // Terminal state
    ];
}

// ══════════════════════════════════════════════════════════════
// STATUS UPDATE
// ══════════════════════════════════════════════════════════════

/**
 * Update an application's status with full audit trail.
 *
 * Validates the transition is allowed, then in a single transaction:
 *   1. Updates the applications table
 *   2. Inserts a record into status_history for audit
 *
 * @param  int    $applicationId Application ID
 * @param  string $newStatus     Target status (must be a valid transition)
 * @param  int    $userId        ID of the user making the change (for audit)
 * @param  string $notes         Optional notes/reason for the status change
 * @return bool   True if update succeeded, false on validation failure or error
 */
function updateApplicationStatus(int $applicationId, string $newStatus, int $userId, string $notes = ''): bool
{
    // Fetch current application status
    $sql = "SELECT status FROM applications WHERE id = :id";
    $app = Database::query($sql, [':id' => $applicationId])->fetch();

    if (!$app) {
        error_log(sprintf(
            '[StatusEngine] Application not found: ID %d',
            $applicationId
        ));
        return false;
    }

    $currentStatus = $app['status'];

    // Validate the transition
    if (!isValidTransition($currentStatus, $newStatus)) {
        error_log(sprintf(
            '[StatusEngine] Invalid transition: %s → %s for application %d by user %d',
            $currentStatus,
            $newStatus,
            $applicationId,
            $userId
        ));
        return false;
    }

    try {
        Database::beginTransaction();

        // Update the application status
        $updateSql = "UPDATE applications
                      SET status = :new_status, updated_at = NOW()
                      WHERE id = :id";
        Database::query($updateSql, [
            ':new_status' => $newStatus,
            ':id'         => $applicationId,
        ]);

        // Log the status change to the audit trail
        $historySql = "INSERT INTO status_history
                       (application_id, from_status, to_status, notes, changed_by, created_at)
                       VALUES (:app_id, :from_status, :to_status, :notes, :changed_by, NOW())";
        Database::query($historySql, [
            ':app_id'      => $applicationId,
            ':from_status' => $currentStatus,
            ':to_status'   => $newStatus,
            ':notes'       => $notes,
            ':changed_by'  => $userId,
        ]);

        Database::commit();

        error_log(sprintf(
            '[StatusEngine] Status updated: Application %d, %s → %s by user %d',
            $applicationId,
            $currentStatus,
            $newStatus,
            $userId
        ));

        return true;
    } catch (Exception $e) {
        Database::rollback();
        error_log(sprintf(
            '[StatusEngine] Error updating status: %s | Application: %d | Transition: %s → %s',
            $e->getMessage(),
            $applicationId,
            $currentStatus,
            $newStatus
        ));
        return false;
    }
}

// ══════════════════════════════════════════════════════════════
// STATUS HISTORY (AUDIT TRAIL)
// ══════════════════════════════════════════════════════════════

/**
 * Get the full status change history for an application.
 *
 * Returns all status transitions in chronological order,
 * including who made each change and any notes.
 *
 * @param  int   $applicationId Application ID
 * @return array Array of history records with user email
 */
function getStatusHistory(int $applicationId): array
{
    $sql = "SELECT sh.*, u.email AS changed_by_email
            FROM status_history sh
            LEFT JOIN users u ON sh.changed_by = u.id
            WHERE sh.application_id = :app_id
            ORDER BY sh.created_at ASC";

    return Database::query($sql, [':app_id' => $applicationId])->fetchAll();
}

// ══════════════════════════════════════════════════════════════
// TRANSITION VALIDATION
// ══════════════════════════════════════════════════════════════

/**
 * Get the allowed next statuses for a given current status.
 *
 * @param  string $currentStatus Current application status
 * @return array  Array of allowed status strings (may be empty for terminal states)
 */
function getAllowedTransitions(string $currentStatus): array
{
    $transitions = getValidTransitions();
    return $transitions[$currentStatus] ?? [];
}

/**
 * Check if a specific status transition is valid.
 *
 * @param  string $from Current status
 * @param  string $to   Target status
 * @return bool   True if the transition is allowed
 */
function isValidTransition(string $from, string $to): bool
{
    $allowed = getAllowedTransitions($from);
    return in_array($to, $allowed, true);
}

/**
 * Check if a status is a terminal (final) state.
 *
 * Terminal states cannot transition to any other status.
 *
 * @param  string $status Status to check
 * @return bool   True if terminal (accepted or rejected)
 */
function isTerminalStatus(string $status): bool
{
    return empty(getAllowedTransitions($status));
}
