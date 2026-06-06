<?php
/*
 * @Module:      Global Helper Functions
 * @Author:      BE-05 (Search & Integration)
 * @Date:        2026-05-24
 * @Description: Utility functions used across the entire application:
 *               output sanitization, redirect, date/currency formatting,
 *               flash messages, pagination, status labels, and common helpers.
 * @Ownership:   BE-05
 *
 * RecruitPro Enterprise — © 2026
 * Do NOT modify without PR approval from module owner.
 */

require_once __DIR__ . '/../config/app.php';

// ══════════════════════════════════════════════════════════════
// OUTPUT SANITIZATION
// ══════════════════════════════════════════════════════════════

/**
 * Clean user data for safe HTML output.
 *
 * Primary sanitization wrapper — use this everywhere output is rendered.
 *
 * @param  mixed  $data String or null to sanitize
 * @return string Sanitized string safe for HTML output
 */
function clean($data): string
{
    if ($data === null) {
        return '';
    }
    return htmlspecialchars((string) $data, ENT_QUOTES, 'UTF-8');
}

/**
 * Alias for clean() — short-hand sanitizer.
 *
 * @param  string $value Value to escape
 * @return string Escaped value
 */
function e(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

// ══════════════════════════════════════════════════════════════
// REDIRECT HELPER
// ══════════════════════════════════════════════════════════════

/**
 * Redirect to a URL and terminate execution.
 *
 * @param string $url Full or relative URL to redirect to
 */
function redirect(string $url): void
{
    header('Location: ' . $url);
    exit;
}

// ══════════════════════════════════════════════════════════════
// DATE & TIME FORMATTING
// ══════════════════════════════════════════════════════════════

/**
 * Format a date/datetime string.
 *
 * Supports both custom PHP format strings and defaults to
 * Indonesian locale date (e.g. "24 Mei 2026").
 *
 * @param  string|null $date   Date string (Y-m-d or Y-m-d H:i:s)
 * @param  string      $format PHP date() format or 'indo' for Indonesian locale
 * @return string      Formatted date or '-' if null/invalid
 */
function formatDate(?string $date, string $format = 'indo'): string
{
    if (empty($date)) {
        return '-';
    }

    $ts = strtotime($date);
    if ($ts === false) {
        return '-';
    }

    // Indonesian locale format
    if ($format === 'indo') {
        $months = [
            1  => 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
            'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember',
        ];
        $d = (int) date('j', $ts);
        $m = (int) date('n', $ts);
        $y = date('Y', $ts);
        return "$d {$months[$m]} $y";
    }

    return date($format, $ts);
}

/**
 * Format date with time in Indonesian locale.
 *
 * @param  string|null $date DateTime string
 * @return string      e.g. "24 Mei 2026, 14:30"
 */
function formatDateTime(?string $date): string
{
    if (empty($date)) {
        return '-';
    }
    return formatDate($date) . ', ' . date('H:i', strtotime($date));
}

/**
 * Format amount as Indonesian Rupiah currency.
 *
 * @param  float|int|null $amount Numeric amount
 * @return string         e.g. "Rp 5.000.000"
 */
function formatCurrency($amount): string
{
    if ($amount === null || $amount === '' || $amount === 0) {
        return 'Rp 0';
    }
    return 'Rp ' . number_format((float) $amount, 0, ',', '.');
}

/**
 * Convert a datetime to relative time string in Indonesian.
 *
 * Examples: "Baru saja", "5 menit yang lalu", "2 jam yang lalu"
 *
 * @param  string|null $datetime DateTime string
 * @return string      Relative time string
 */
function timeAgo(?string $datetime): string
{
    if (empty($datetime)) {
        return '-';
    }

    $diff = time() - strtotime($datetime);

    if ($diff < 0) {
        return 'Baru saja';
    }

    $intervals = [
        ['label' => 'tahun',  'seconds' => 31536000],
        ['label' => 'bulan',  'seconds' => 2592000],
        ['label' => 'minggu', 'seconds' => 604800],
        ['label' => 'hari',   'seconds' => 86400],
        ['label' => 'jam',    'seconds' => 3600],
        ['label' => 'menit',  'seconds' => 60],
    ];

    foreach ($intervals as $interval) {
        $count = floor($diff / $interval['seconds']);
        if ($count >= 1) {
            return $count . ' ' . $interval['label'] . ' yang lalu';
        }
    }

    return 'Baru saja';
}

// ══════════════════════════════════════════════════════════════
// TEXT UTILITIES
// ══════════════════════════════════════════════════════════════

/**
 * Truncate text to a specified length with ellipsis.
 *
 * @param  string|null $text   Text to truncate
 * @param  int         $length Maximum character length (default 100)
 * @param  string      $suffix Suffix to append (default '...')
 * @return string      Truncated text
 */
function truncate(?string $text, int $length = 100, string $suffix = '...'): string
{
    if ($text === null) {
        return '';
    }
    if (mb_strlen($text, 'UTF-8') <= $length) {
        return $text;
    }
    return mb_substr($text, 0, $length, 'UTF-8') . $suffix;
}

/**
 * Generate a URL-safe slug from text.
 *
 * "Senior PHP Developer" → "senior-php-developer"
 *
 * @param  string $text Input text
 * @return string URL-safe slug
 */
function generateSlug(string $text): string
{
    $slug = mb_strtolower($text, 'UTF-8');
    $slug = preg_replace('/[^a-z0-9\-]/', '-', $slug);
    $slug = preg_replace('/-+/', '-', $slug);
    return trim($slug, '-');
}

// ══════════════════════════════════════════════════════════════
// FLASH MESSAGE HELPERS
// ══════════════════════════════════════════════════════════════

/**
 * Set a flash message in the session.
 *
 * @param string $type    Message type: 'success', 'error', 'warning', 'info'
 * @param string $message The message content
 */
function flashMessage(string $type, string $message): void
{
    $_SESSION['_flash_message'] = [
        'type'    => $type,
        'message' => $message,
    ];
}

/**
 * Retrieve and clear the flash message.
 *
 * @return array|null Flash data ['type' => ..., 'message' => ...] or null
 */
function getFlashMessage(): ?array
{
    $flash = $_SESSION['_flash_message'] ?? null;
    unset($_SESSION['_flash_message']);
    return $flash;
}

// ══════════════════════════════════════════════════════════════
// NAVIGATION HELPER
// ══════════════════════════════════════════════════════════════

/**
 * Check if a navigation item should be highlighted as active.
 *
 * @param  string $page Page identifier to check
 * @return bool   True if the current page matches
 */
function isActiveNav(string $page): bool
{
    $currentPage = basename($_SERVER['PHP_SELF'], '.php');
    return $currentPage === $page;
}

// ══════════════════════════════════════════════════════════════
// STATUS LABELS & COLORS
// ══════════════════════════════════════════════════════════════

/**
 * Get human-readable status label in Indonesian.
 *
 * @param  string $status Status constant value
 * @return string Indonesian label
 */
function getStatusLabel(string $status): string
{
    $labels = [
        STATUS_APPLIED      => 'Dilamar',
        STATUS_UNDER_REVIEW => 'Sedang Ditinjau',
        STATUS_INTERVIEW    => 'Wawancara',
        STATUS_REJECTED     => 'Ditolak',
        STATUS_ACCEPTED     => 'Diterima',
    ];

    return $labels[$status] ?? ucfirst(str_replace('_', ' ', $status));
}

/**
 * Alias for getStatusLabel() — backwards compatibility.
 */
function statusLabel(string $status): string
{
    return getStatusLabel($status);
}

/**
 * Get Tailwind CSS classes for a status badge.
 *
 * @param  string $status Status constant value
 * @return string Tailwind CSS class string
 */
function getStatusColor(string $status): string
{
    $colors = [
        STATUS_APPLIED      => 'bg-slate-500/20 text-slate-300 border-slate-500/30',
        STATUS_UNDER_REVIEW => 'bg-amber-500/20 text-amber-500 border-amber-500/30',
        STATUS_INTERVIEW    => 'bg-indigo-500/20 text-indigo-400 border-indigo-500/30',
        STATUS_REJECTED     => 'bg-rose-500/20 text-rose-400 border-rose-500/30',
        STATUS_ACCEPTED     => 'bg-emerald-500/20 text-emerald-500 border-emerald-500/30',
    ];

    return $colors[$status] ?? 'bg-slate-500/20 text-slate-300 border-slate-500/30';
}

// ══════════════════════════════════════════════════════════════
// PAGINATION CALCULATOR
// ══════════════════════════════════════════════════════════════

/**
 * Calculate pagination metadata.
 *
 * @param  int   $total   Total number of items
 * @param  int   $page    Current page number (1-indexed)
 * @param  int   $perPage Items per page
 * @return array Pagination data
 */
function getPaginationData(int $total, int $page = 1, int $perPage = ITEMS_PER_PAGE): array
{
    $totalPages  = max(1, (int) ceil($total / $perPage));
    $currentPage = max(1, min($page, $totalPages));
    $offset      = ($currentPage - 1) * $perPage;
    $from        = $total > 0 ? $offset + 1 : 0;
    $to          = min($offset + $perPage, $total);

    return [
        'total_items'  => $total,
        'total_pages'  => $totalPages,
        'current_page' => $currentPage,
        'per_page'     => $perPage,
        'offset'       => $offset,
        'hasNext'      => $currentPage < $totalPages,
        'hasPrev'      => $currentPage > 1,
        'from'         => $from,
        'to'           => $to,
    ];
}

/**
 * Alias for getPaginationData() — backwards compatibility.
 */
function paginate(int $totalItems, int $currentPage, int $perPage = ITEMS_PER_PAGE): array
{
    return getPaginationData($totalItems, $currentPage, $perPage);
}

// ══════════════════════════════════════════════════════════════
// DOMAIN-SPECIFIC HELPERS
// ══════════════════════════════════════════════════════════════

/**
 * Calculate profile completeness percentage.
 *
 * @param  array $profile Pelamar profile data
 * @return int   Percentage 0-100
 */
function profileCompleteness(array $profile): int
{
    $fields = ['full_name', 'phone', 'date_of_birth', 'address', 'education_level', 'institution', 'skills', 'cv_file'];
    $filled = 0;
    foreach ($fields as $field) {
        if (!empty($profile[$field])) {
            $filled++;
        }
    }
    return (int) round(($filled / count($fields)) * 100);
}

/**
 * Get job type label from constant key.
 *
 * @param  string $type Job type key
 * @return string Human-readable label
 */
function jobTypeLabel(string $type): string
{
    return JOB_TYPES[$type] ?? ucfirst($type);
}

/**
 * Get industry label from constant key.
 *
 * @param  string $key Industry key
 * @return string Human-readable label
 */
function industryLabel(string $key): string
{
    return INDUSTRY_CATEGORIES[$key] ?? ucfirst($key);
}

/**
 * Format salary for display.
 *
 * @param  string|null $salary Salary value
 * @return string      Formatted salary or "Negotiable"
 */
function formatSalary(?string $salary): string
{
    if (empty($salary) || $salary === '-') {
        return 'Negotiable';
    }
    return $salary;
}
