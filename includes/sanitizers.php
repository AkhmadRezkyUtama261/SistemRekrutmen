<?php
/*
 * @Module:      XSS Prevention — Input/Output Sanitizers
 * @Author:      BE-05 (Search & Integration)
 * @Date:        2026-05-24
 * @Description: Sanitization functions for all user input and output.
 *               Prevents XSS, injection, and path traversal attacks.
 * @Ownership:   BE-05
 *
 * RecruitPro Enterprise — © 2026
 * Do NOT modify without PR approval from module owner.
 */

// ══════════════════════════════════════════════════════════════
// INPUT SANITIZATION
// ══════════════════════════════════════════════════════════════

/**
 * Sanitize a single input string.
 *
 * Trims whitespace, strips HTML/PHP tags, and applies htmlspecialchars.
 * Use for all user-submitted text before storage or display.
 *
 * @param  mixed  $data Raw input value
 * @return string Sanitized string
 */
function sanitizeInput($data): string
{
    if ($data === null) {
        return '';
    }

    $data = (string) $data;
    $data = trim($data);
    $data = strip_tags($data);
    $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');

    return $data;
}

/**
 * Sanitize string for database storage (strips tags, trims).
 *
 * Lighter than sanitizeInput() — does NOT encode HTML entities,
 * so the original text is preserved in the database.
 * Always use sanitizeOutput() when rendering stored data.
 *
 * @param  string $input Raw input string
 * @return string Trimmed and tag-stripped string
 */
function sanitizeString(string $input): string
{
    return trim(strip_tags($input));
}

/**
 * Sanitize an email address.
 *
 * Uses filter_var with FILTER_SANITIZE_EMAIL to remove illegal
 * characters, then trims and lowercases the result.
 *
 * @param  string $email Raw email input
 * @return string Sanitized email (lowercase, trimmed)
 */
function sanitizeEmail(string $email): string
{
    $email = trim($email);
    $email = mb_strtolower($email, 'UTF-8');
    $email = filter_var($email, FILTER_SANITIZE_EMAIL);

    return $email;
}

/**
 * Sanitize a value to an integer.
 *
 * Uses filter_var with FILTER_SANITIZE_NUMBER_INT to strip
 * everything except digits and plus/minus signs.
 *
 * @param  mixed    $value Raw input value
 * @return int|null Sanitized integer or null if empty/invalid
 */
function sanitizeInt($value): ?int
{
    if ($value === null || $value === '') {
        return null;
    }

    $sanitized = filter_var($value, FILTER_SANITIZE_NUMBER_INT);

    if ($sanitized === '' || $sanitized === false) {
        return null;
    }

    return (int) $sanitized;
}

/**
 * Recursively sanitize all values in an array.
 *
 * Applies sanitizeInput() to every string value in a nested array.
 * Useful for sanitizing entire $_POST or $_GET arrays at once.
 *
 * @param  array $array Raw input array
 * @return array Sanitized array with all strings cleaned
 */
function sanitizeArray(array $array): array
{
    $sanitized = [];

    foreach ($array as $key => $value) {
        // Sanitize the key itself
        $cleanKey = is_string($key) ? sanitizeInput($key) : $key;

        if (is_array($value)) {
            $sanitized[$cleanKey] = sanitizeArray($value);
        } elseif (is_string($value)) {
            $sanitized[$cleanKey] = sanitizeInput($value);
        } else {
            // Preserve non-string types (int, float, bool, null)
            $sanitized[$cleanKey] = $value;
        }
    }

    return $sanitized;
}

// ══════════════════════════════════════════════════════════════
// OUTPUT SANITIZATION
// ══════════════════════════════════════════════════════════════

/**
 * Sanitize data for safe HTML output.
 *
 * Primary output sanitizer — use whenever rendering user-generated
 * content in HTML templates to prevent XSS.
 *
 * @param  mixed  $data String or null to sanitize
 * @return string Sanitized string safe for HTML rendering
 */
function sanitizeOutput($data): string
{
    if ($data === null) {
        return '';
    }
    return htmlspecialchars((string) $data, ENT_QUOTES, 'UTF-8');
}

// ══════════════════════════════════════════════════════════════
// FILE NAME SANITIZATION
// ══════════════════════════════════════════════════════════════

/**
 * Sanitize a file name by removing dangerous characters.
 *
 * Prevents directory traversal (../), null byte injection,
 * hidden files, and double extensions.
 *
 * @param  string $name Raw file name
 * @return string Sanitized file name safe for filesystem storage
 */
function sanitizeFileName(string $name): string
{
    // Remove path components to prevent directory traversal
    $name = basename($name);

    // Remove null bytes
    $name = str_replace("\0", '', $name);

    // Remove directory traversal patterns
    $name = str_replace(['../', '..\\', '..'], '', $name);

    // Keep only safe characters: alphanumeric, dash, underscore, dot
    $name = preg_replace('/[^a-zA-Z0-9\-_\.]/', '_', $name);

    // Remove multiple consecutive dots (prevent .php.jpg attacks)
    $name = preg_replace('/\.{2,}/', '.', $name);

    // Remove leading dots (prevent hidden files)
    $name = ltrim($name, '.');

    // Ensure we have a valid filename
    if (empty($name)) {
        $name = 'unnamed_file';
    }

    return $name;
}

// ══════════════════════════════════════════════════════════════
// URL SANITIZATION
// ══════════════════════════════════════════════════════════════

/**
 * Sanitize a URL string.
 *
 * @param  string $url Raw URL
 * @return string Sanitized URL
 */
function sanitizeUrl(string $url): string
{
    return filter_var(trim($url), FILTER_SANITIZE_URL);
}
