<?php
/*
 * @Module:      Application Configuration
 * @Author:      BE-01 (Database Core & Security)
 * @Date:        2026-05-24
 * @Description: Centralized application constants and environment
 *               configuration. All sensitive values should be moved
 *               to environment variables in production.
 * @Ownership:   BE-01
 *
 * RecruitPro Enterprise — © 2026
 * Do NOT modify without PR approval from module owner.
 */

// ══════════════════════════════════════════════════════════════
// APPLICATION IDENTITY
// ══════════════════════════════════════════════════════════════

define('APP_NAME',    'RecruitPro Enterprise');
define('APP_VERSION', '1.0.0');
define('APP_ENV',     'development'); // 'development' | 'staging' | 'production'
define('APP_DEBUG',   APP_ENV === 'development');

// Base URL — adjust for your environment
define('BASE_URL', 'http://localhost/recruitment-enterprise');

// ══════════════════════════════════════════════════════════════
// DATABASE CREDENTIALS
// ══════════════════════════════════════════════════════════════
// In production, load these from $_ENV or a .env file
// NEVER commit real credentials to version control

define('DB_HOST',    '127.0.0.1');
define('DB_PORT',    '3306');
define('DB_NAME',    'recruitpro_db');
define('DB_USER',    'root');           // Change in production!
define('DB_PASS',    '');               // Change in production!
define('DB_CHARSET', 'utf8mb4');

// ══════════════════════════════════════════════════════════════
// FILE UPLOAD SETTINGS
// ══════════════════════════════════════════════════════════════

define('UPLOAD_DIR',       __DIR__ . '/../uploads/cv/');
define('UPLOAD_MAX_SIZE',  2 * 1024 * 1024);  // 2MB in bytes
define('ALLOWED_MIME',     'application/pdf');
define('ALLOWED_EXT',      'pdf');

// ══════════════════════════════════════════════════════════════
// SESSION CONFIGURATION
// ══════════════════════════════════════════════════════════════

define('SESSION_IDLE_TIMEOUT',     900);    // 15 minutes
define('SESSION_ABSOLUTE_TIMEOUT', 28800);  // 8 hours

// ══════════════════════════════════════════════════════════════
// PAGINATION
// ══════════════════════════════════════════════════════════════

define('ITEMS_PER_PAGE', 12);

// ══════════════════════════════════════════════════════════════
// EMAIL (PHPMailer)
// ══════════════════════════════════════════════════════════════

define('SMTP_HOST',       'smtp.gmail.com');
define('SMTP_PORT',       587);
define('SMTP_USERNAME',   'noreply@recruitpro.com');  // Replace
define('SMTP_PASSWORD',   '');                         // Replace
define('SMTP_FROM_NAME',  'RecruitPro Enterprise');
define('SMTP_FROM_EMAIL', 'noreply@recruitpro.com');

// ══════════════════════════════════════════════════════════════
// APPLICATION STATUS DEFINITIONS
// ══════════════════════════════════════════════════════════════

define('STATUS_APPLIED',      'applied');
define('STATUS_UNDER_REVIEW', 'under_review');
define('STATUS_INTERVIEW',    'interview');
define('STATUS_REJECTED',     'rejected');
define('STATUS_ACCEPTED',     'accepted');

define('JOB_STATUS_ACTIVE', 'active');
define('JOB_STATUS_CLOSED', 'closed');
define('JOB_STATUS_DRAFT',  'draft');

// ══════════════════════════════════════════════════════════════
// ROLE DEFINITIONS
// ══════════════════════════════════════════════════════════════

define('ROLE_HR',      'hr');
define('ROLE_PELAMAR', 'pelamar');

// ══════════════════════════════════════════════════════════════
// INDUSTRY CATEGORIES (for job filter)
// ══════════════════════════════════════════════════════════════

define('INDUSTRY_CATEGORIES', [
    'technology'    => 'Teknologi & IT',
    'finance'       => 'Keuangan & Perbankan',
    'healthcare'    => 'Kesehatan & Medis',
    'education'     => 'Pendidikan',
    'manufacturing' => 'Manufaktur',
    'retail'        => 'Ritel & E-Commerce',
    'media'         => 'Media & Kreatif',
    'consulting'    => 'Konsulting',
    'government'    => 'Pemerintahan',
    'other'         => 'Lainnya',
]);

// ══════════════════════════════════════════════════════════════
// JOB TYPES (for job filter)
// ══════════════════════════════════════════════════════════════

define('JOB_TYPES', [
    'full-time'   => 'Full-time',
    'part-time'   => 'Part-time',
    'internship'  => 'Internship',
    'contract'    => 'Kontrak',
]);
