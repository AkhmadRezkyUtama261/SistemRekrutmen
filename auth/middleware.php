<?php
/*
 * @Module:      Authentication Middleware
 * @Author:      BE-02 (Auth & Session Lead)
 * @Date:        2026-05-24
 * @Description: Route guard functions for role-based access control.
 *               Call requireLogin(), requireHR(), or requirePelamar()
 *               at the top of protected pages.
 * @Ownership:   BE-02
 *
 * RecruitPro Enterprise — © 2026
 * Do NOT modify without PR approval from module owner.
 */

require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/session.php';

// ══════════════════════════════════════════════════════════════
// ROUTE GUARD FUNCTIONS
// ══════════════════════════════════════════════════════════════

/**
 * Require authentication — redirect to login if not authenticated.
 *
 * Usage:
 *   <?php requireLogin(); // Place at top of protected page ?>
 */
function requireLogin(): void
{
    if (!isLoggedIn()) {
        setFlash('warning', 'Silakan login terlebih dahulu untuk mengakses halaman ini.');
        header('Location: ' . BASE_URL . '/auth/login.php');
        exit;
    }
}

/**
 * Require HR role — verify user is authenticated AND has HR role.
 *
 * Redirects to login if not authenticated, or to pelamar dashboard
 * if authenticated but not HR.
 */
function requireHR(): void
{
    requireLogin();

    if (getUserRole() !== ROLE_HR) {
        setFlash('error', 'Anda tidak memiliki akses ke halaman ini.');
        header('Location: ' . BASE_URL . '/pelamar/dashboard.php');
        exit;
    }
}

/**
 * Require Pelamar role — verify user is authenticated AND has pelamar role.
 *
 * Redirects to login if not authenticated, or to HR dashboard
 * if authenticated but not pelamar.
 */
function requirePelamar(): void
{
    requireLogin();

    if (getUserRole() !== ROLE_PELAMAR) {
        setFlash('error', 'Anda tidak memiliki akses ke halaman ini.');
        header('Location: ' . BASE_URL . '/hr/dashboard.php');
        exit;
    }
}

/**
 * Require guest — redirect authenticated users to their dashboard.
 *
 * Use on login/register pages to prevent already-logged-in users
 * from seeing auth forms.
 */
function requireGuest(): void
{
    if (isLoggedIn()) {
        $role = getUserRole();
        if ($role === ROLE_HR) {
            header('Location: ' . BASE_URL . '/hr/dashboard.php');
        } else {
            header('Location: ' . BASE_URL . '/pelamar/dashboard.php');
        }
        exit;
    }
}

// ══════════════════════════════════════════════════════════════
// USER DATA ACCESSORS
// ══════════════════════════════════════════════════════════════

/**
 * Get the current authenticated user's data from session.
 *
 * @return array Associative array with user_id, role, email, full_name/company_name
 *               Returns empty array if not authenticated
 */
function getCurrentUser(): array
{
    if (!isLoggedIn()) {
        return [];
    }

    return [
        'user_id'      => $_SESSION['user_id']      ?? null,
        'role'         => $_SESSION['role']          ?? null,
        'email'        => $_SESSION['email']         ?? null,
        'full_name'    => $_SESSION['full_name']     ?? null,
        'company_name' => $_SESSION['company_name']  ?? null,
    ];
}

/**
 * Check if the current user has the HR role.
 *
 * @return bool True if authenticated and role is 'hr'
 */
function isHR(): bool
{
    return isLoggedIn() && getUserRole() === ROLE_HR;
}

/**
 * Check if the current user has the Pelamar role.
 *
 * @return bool True if authenticated and role is 'pelamar'
 */
function isPelamar(): bool
{
    return isLoggedIn() && getUserRole() === ROLE_PELAMAR;
}

// ══════════════════════════════════════════════════════════════
// LEGACY ALIASES (backwards compatibility)
// ══════════════════════════════════════════════════════════════

/**
 * Alias for requireLogin() — backwards compatibility.
 */
function requireAuth(): void
{
    requireLogin();
}

/**
 * Alias for requireGuest() — backwards compatibility.
 */
function redirectIfAuthenticated(): void
{
    requireGuest();
}
