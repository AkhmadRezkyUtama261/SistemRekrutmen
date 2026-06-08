<?php
/*
 * @Module:      Session Isolation Manager
 * @Author:      BE-02 (Auth & Session Lead)
 * @Date:        2026-05-24
 * @Description: Secure session configuration with httponly, samesite,
 *               strict mode, idle/absolute timeouts, and IP + User-Agent
 *               validation for session hijacking prevention.
 * @Ownership:   BE-02
 *
 * RecruitPro Enterprise — © 2026
 * Do NOT modify without PR approval from module owner.
 */

require_once __DIR__ . '/../config/app.php';

// ══════════════════════════════════════════════════════════════
// SECURE SESSION CONFIGURATION
// ══════════════════════════════════════════════════════════════

/**
 * Initialize session with security-hardened defaults.
 *
 * Must be called before any output. Sets httponly, secure (HTTPS),
 * samesite=Strict, use_strict_mode to reject uninitialized session IDs.
 */
function initSession(): void
{
    if (session_status() === PHP_SESSION_ACTIVE) {
        return; // Session already started
    }

    // Determine if running over HTTPS
    $isSecure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
             || (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443);

    // Configure session cookie parameters
    session_set_cookie_params([
        'lifetime' => 0,                     // Session cookie — expires when browser closes
        'path'     => '/',
        'domain'   => '',                     // Current domain only
        'secure'   => $isSecure,              // Only send over HTTPS in production
        'httponly'  => true,                   // Prevent JavaScript access
        'samesite' => 'Strict',               // Prevent CSRF via cross-origin requests
    ]);

    // Session hardening ini settings
    ini_set('session.use_strict_mode',      '1'); // Reject uninitialized session IDs
    ini_set('session.use_only_cookies',     '1'); // Never accept session IDs from URLs
    ini_set('session.use_trans_sid',        '0'); // Don't embed session ID in URLs
    ini_set('session.cookie_httponly',      '1');
    ini_set('session.sid_length',           '48');
    ini_set('session.sid_bits_per_character', '6');

    // Custom session name — avoid default "PHPSESSID" fingerprinting
    session_name('RPRO_SESSID');

    session_start();

    // ── Bind session to client fingerprint on first creation ──
    if (!isset($_SESSION['_initiated'])) {
        $_SESSION['_initiated']     = true;
        $_SESSION['_created_at']    = time();
        $_SESSION['_last_activity'] = time();
        $_SESSION['_client_ip']     = getClientIp();
        $_SESSION['_client_ua']     = $_SERVER['HTTP_USER_AGENT'] ?? '';
    }

    // ── Run validation checks ──
    enforceSessionSecurity();
}

// ══════════════════════════════════════════════════════════════
// SESSION SECURITY ENFORCEMENT
// ══════════════════════════════════════════════════════════════

/**
 * Enforce idle timeout, absolute timeout, and hijacking detection.
 *
 * Automatically called by initSession(). Destroys the session
 * and redirects to login on any violation.
 */
function enforceSessionSecurity(): void
{
    // Only validate for authenticated sessions
    if (!isset($_SESSION['_initiated'])) {
        return;
    }

    $now = time();

    // ── Idle Timeout Check ──
    // Destroy session if user inactive > SESSION_IDLE_TIMEOUT (15 min)
    if (isset($_SESSION['_last_activity'])) {
        if ($now - $_SESSION['_last_activity'] > SESSION_IDLE_TIMEOUT) {
            $wasLoggedIn = isset($_SESSION['user_id']);
            destroySession();
            if ($wasLoggedIn) {
                // Restart session to set flash message
                initSessionMinimal();
                setFlash('warning', 'Sesi Anda telah habis karena tidak ada aktivitas. Silakan login kembali.');
            }
            return;
        }
    }

    // ── Absolute Timeout Check ──
    // Destroy session after SESSION_ABSOLUTE_TIMEOUT (8 hours) regardless of activity
    if (isset($_SESSION['_created_at'])) {
        if ($now - $_SESSION['_created_at'] > SESSION_ABSOLUTE_TIMEOUT) {
            $wasLoggedIn = isset($_SESSION['user_id']);
            destroySession();
            if ($wasLoggedIn) {
                initSessionMinimal();
                setFlash('warning', 'Sesi Anda telah mencapai batas waktu maksimum. Silakan login kembali.');
            }
            return;
        }
    }

    // ── IP Address Validation (hijacking detection) ──
    if (isset($_SESSION['_client_ip']) && isset($_SESSION['user_id'])) {
        if ($_SESSION['_client_ip'] !== getClientIp()) {
            error_log(sprintf(
                '[RecruitPro Session] IP mismatch — Session IP: %s, Current IP: %s, User: %s, SID: %s',
                $_SESSION['_client_ip'],
                getClientIp(),
                $_SESSION['user_id'] ?? 'unknown',
                session_id()
            ));
            destroySession();
            initSessionMinimal();
            setFlash('error', 'Sesi tidak valid. Silakan login kembali.');
            return;
        }
    }

    // ── User-Agent Validation (hijacking detection) ──
    if (isset($_SESSION['_client_ua']) && isset($_SESSION['user_id'])) {
        $currentUA = $_SERVER['HTTP_USER_AGENT'] ?? '';
        if ($_SESSION['_client_ua'] !== $currentUA) {
            error_log(sprintf(
                '[RecruitPro Session] UA mismatch — User: %s, SID: %s',
                $_SESSION['user_id'] ?? 'unknown',
                session_id()
            ));
            destroySession();
            initSessionMinimal();
            setFlash('error', 'Sesi tidak valid. Silakan login kembali.');
            return;
        }
    }

    // ── Update last activity timestamp ──
    $_SESSION['_last_activity'] = $now;
}

/**
 * Start a minimal session (for flash messages after destroy).
 * Does NOT run security enforcement to avoid recursion.
 */
function initSessionMinimal(): void
{
    if (session_status() === PHP_SESSION_ACTIVE) {
        return;
    }
    session_name('RPRO_SESSID');
    session_start();
}

// ══════════════════════════════════════════════════════════════
// SESSION REGENERATION
// ══════════════════════════════════════════════════════════════

/**
 * Regenerate session ID to prevent fixation attacks.
 *
 * Call on:
 *   - Successful login
 *   - Privilege escalation
 *   - Periodically during long sessions
 *
 * @param bool $deleteOld Whether to delete the old session file
 */
function regenerateSession(bool $deleteOld = true): void
{
    if (session_status() !== PHP_SESSION_ACTIVE) {
        return;
    }

    session_regenerate_id($deleteOld);

    // Update fingerprint data
    $_SESSION['_last_activity'] = time();
    $_SESSION['_client_ip']     = getClientIp();
    $_SESSION['_client_ua']     = $_SERVER['HTTP_USER_AGENT'] ?? '';
}

// ══════════════════════════════════════════════════════════════
// SESSION DESTRUCTION
// ══════════════════════════════════════════════════════════════

/**
 * Completely destroy the current session.
 *
 * Clears all session variables, removes the session cookie,
 * and destroys the server-side session file.
 */
function destroySession(): void
{
    if (session_status() !== PHP_SESSION_ACTIVE) {
        return;
    }

    // Clear all session variables
    $_SESSION = [];

    // Delete the session cookie from the browser
    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(
            session_name(),
            '',
            [
                'expires'  => time() - 42000,
                'path'     => $params['path'],
                'domain'   => $params['domain'],
                'secure'   => $params['secure'],
                'httponly'  => $params['httponly'],
                'samesite' => $params['samesite'] ?? 'Strict',
            ]
        );
    }

    // Destroy the session on the server
    session_destroy();
}

// ══════════════════════════════════════════════════════════════
// AUTH STATE HELPERS
// ══════════════════════════════════════════════════════════════

/**
 * Check if a user is currently authenticated.
 *
 * @return bool True if user_id exists in session
 */
function isLoggedIn(): bool
{
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

/**
 * Get current user's role.
 *
 * @return string|null Role constant or null if not logged in
 */
function getUserRole(): ?string
{
    return $_SESSION['role'] ?? null;
}

/**
 * Get current user's ID.
 *
 * @return int|null User ID or null if not logged in
 */
function getUserId(): ?int
{
    return isset($_SESSION['user_id']) ? (int) $_SESSION['user_id'] : null;
}

// ══════════════════════════════════════════════════════════════
// FLASH MESSAGE HELPERS
// ══════════════════════════════════════════════════════════════

/**
 * Set a flash message to be displayed on the next page load.
 *
 * @param string $type    Message type: 'success', 'error', 'warning', 'info'
 * @param string $message The message content
 */
function setFlash(string $type, string $message): void
{
    $_SESSION['flash'] = [
        'type'    => $type,
        'message' => $message,
    ];
}

/**
 * Get and clear the flash message.
 *
 * @return array|null Flash data ['type' => ..., 'message' => ...] or null
 */
function getFlash(): ?array
{
    $flash = $_SESSION['flash'] ?? null;
    unset($_SESSION['flash']);
    return $flash;
}

/**
 * Check if a flash message exists.
 *
 * @return bool
 */
function hasFlash(): bool
{
    return isset($_SESSION['flash']);
}

// ══════════════════════════════════════════════════════════════
// IP DETECTION
// ══════════════════════════════════════════════════════════════

/**
 * Get the client's real IP address.
 *
 * Checks proxy headers but falls back to REMOTE_ADDR.
 *
 * @return string Client IP address
 */
function getClientIp(): string
{
    $headers = [
        'HTTP_X_FORWARDED_FOR',
        'HTTP_X_REAL_IP',
        'HTTP_CLIENT_IP',
    ];

    foreach ($headers as $header) {
        if (!empty($_SERVER[$header])) {
            // X-Forwarded-For may contain multiple IPs; take the first
            $ip = trim(explode(',', $_SERVER[$header])[0]);
            if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
                return $ip;
            }
        }
    }

    return $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
}

// ══════════════════════════════════════════════════════════════
// AUTO-INITIALIZE
// ══════════════════════════════════════════════════════════════
initSession();
