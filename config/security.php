<?php
/*
 * @Module:      Security Configuration
 * @Author:      BE-01 (Database Core & Security)
 * @Date:        2026-05-24
 * @Description: CSRF protection, security headers, and rate limiting.
 *               Include this file in every page that handles form submissions.
 * @Ownership:   BE-01
 *
 * RecruitPro Enterprise — © 2026
 * Do NOT modify without PR approval from module owner.
 */

// ══════════════════════════════════════════════════════════════
// CSRF TOKEN MANAGEMENT
// ══════════════════════════════════════════════════════════════

/**
 * Generate a CSRF token and store it in the session.
 * Creates a new token if one doesn't exist.
 *
 * @return string The CSRF token (64 hex characters)
 */
function generateCsrfToken(): string
{
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['csrf_token'];
}

/**
 * Render a hidden CSRF input field for forms.
 *
 * Usage in templates:
 *   <form method="POST">
 *       <?= csrfField() ?>
 *       ...
 *   </form>
 *
 * @return string HTML hidden input element
 */
function csrfField(): string
{
    $token = generateCsrfToken();
    return '<input type="hidden" name="csrf_token" value="' . $token . '">';
}

/**
 * Validate a submitted CSRF token against the session token.
 * Uses hash_equals() for timing-safe comparison (prevents timing attacks).
 *
 * @param  string $submittedToken The token from $_POST
 * @return bool   True if valid, false otherwise
 */
function validateCsrfToken(string $submittedToken): bool
{
    if (empty($_SESSION['csrf_token'])) {
        return false;
    }

    return hash_equals($_SESSION['csrf_token'], $submittedToken);
}

/**
 * Enforce CSRF validation on POST requests.
 * Call this at the top of any page that processes form data.
 * Terminates execution with 403 if token is invalid.
 */
function enforceCsrf(): void
{
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $token = $_POST['csrf_token'] ?? '';
        if (!validateCsrfToken($token)) {
            http_response_code(403);
            die(json_encode([
                'success' => false,
                'message' => 'Sesi keamanan tidak valid. Silakan muat ulang halaman.'
            ]));
        }
    }
}

/**
 * Regenerate the CSRF token (call after successful form processing).
 * Prevents token reuse attacks.
 */
function regenerateCsrfToken(): void
{
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// ══════════════════════════════════════════════════════════════
// SECURITY HEADERS
// ══════════════════════════════════════════════════════════════

/**
 * Set security-hardened HTTP response headers.
 * Call this early in the request lifecycle (before any output).
 */
function setSecurityHeaders(): void
{
    // Prevent MIME type sniffing
    header('X-Content-Type-Options: nosniff');

    // Prevent clickjacking — only allow same-origin framing
    header('X-Frame-Options: SAMEORIGIN');

    // Enable browser XSS filter
    header('X-XSS-Protection: 1; mode=block');

    // Referrer policy — send origin only for cross-origin requests
    header('Referrer-Policy: strict-origin-when-cross-origin');

    // Permissions policy — disable unnecessary browser features
    header('Permissions-Policy: camera=(), microphone=(), geolocation=()');

    // Content Security Policy
    header("Content-Security-Policy: default-src 'self'; "
        . "script-src 'self' 'unsafe-inline' https://cdn.tailwindcss.com https://cdn.jsdelivr.net; "
        . "style-src 'self' 'unsafe-inline' https://fonts.googleapis.com https://cdn.tailwindcss.com; "
        . "font-src 'self' https://fonts.gstatic.com; "
        . "img-src 'self' data: https:; "
        . "connect-src 'self';"
    );

    // Prevent caching of sensitive pages
    header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
    header('Pragma: no-cache');
}

// ══════════════════════════════════════════════════════════════
// RATE LIMITING (Simple session-based)
// ══════════════════════════════════════════════════════════════

/**
 * Check if the current session has exceeded the rate limit
 * for a given action (e.g., login attempts).
 *
 * @param  string $action     The action identifier (e.g., 'login', 'apply')
 * @param  int    $maxAttempts Maximum attempts allowed
 * @param  int    $windowSec  Time window in seconds
 * @return bool   True if rate limited (should block), false if OK
 */
function isRateLimited(string $action, int $maxAttempts = 5, int $windowSec = 300): bool
{
    $key = 'rate_limit_' . $action;

    if (!isset($_SESSION[$key])) {
        $_SESSION[$key] = ['attempts' => 0, 'first_attempt' => time()];
    }

    $data = &$_SESSION[$key];

    // Reset if window has expired
    if (time() - $data['first_attempt'] > $windowSec) {
        $data = ['attempts' => 0, 'first_attempt' => time()];
    }

    $data['attempts']++;

    return $data['attempts'] > $maxAttempts;
}

/**
 * Reset the rate limit counter for a given action.
 * Call this after a successful action (e.g., after successful login).
 *
 * @param string $action The action identifier
 */
function resetRateLimit(string $action): void
{
    unset($_SESSION['rate_limit_' . $action]);
}
