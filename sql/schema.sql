-- ═══════════════════════════════════════════════════════════════
-- RecruitPro Enterprise — Database Schema
-- @Author:      BE-01 (Database Core & Security)
-- @Date:        2026-05-24
-- @Description: Complete database schema matching all PHP codebase requirements.
-- ═══════════════════════════════════════════════════════════════

CREATE DATABASE IF NOT EXISTS recruitpro_db
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE recruitpro_db;

-- ──────────────────────────────────────────────────────────────
-- USERS TABLE
-- ──────────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS users (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    email           VARCHAR(255) NOT NULL UNIQUE,
    password_hash   VARCHAR(255) NOT NULL,
    role            ENUM('hr', 'pelamar') NOT NULL,
    is_active       TINYINT(1) NOT NULL DEFAULT 1,
    created_at      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    INDEX idx_users_email (email),
    INDEX idx_users_role (role)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ──────────────────────────────────────────────────────────────
-- HR PROFILES TABLE
-- ──────────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS hr_profiles (
    id                  INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id             INT UNSIGNED NOT NULL UNIQUE,
    company_name        VARCHAR(255) NOT NULL,
    industry            VARCHAR(100) DEFAULT NULL,
    location            VARCHAR(255) DEFAULT NULL,
    phone               VARCHAR(20) DEFAULT NULL,
    website             VARCHAR(255) DEFAULT NULL,
    company_description TEXT DEFAULT NULL,
    created_at          DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at          DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    CONSTRAINT fk_hr_profiles_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_hr_company (company_name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ──────────────────────────────────────────────────────────────
-- PELAMAR PROFILES TABLE
-- ──────────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS pelamar_profiles (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id         INT UNSIGNED NOT NULL UNIQUE,
    full_name       VARCHAR(255) NOT NULL,
    phone           VARCHAR(20) DEFAULT NULL,
    date_of_birth   DATE DEFAULT NULL,
    address         TEXT DEFAULT NULL,
    education_level VARCHAR(50) DEFAULT NULL,
    skills          TEXT DEFAULT NULL,
    cv_file_path    VARCHAR(255) DEFAULT NULL,
    created_at      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    CONSTRAINT fk_pelamar_profiles_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_pelamar_name (full_name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ──────────────────────────────────────────────────────────────
-- JOBS TABLE
-- ──────────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS jobs (
    id                  INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    hr_profile_id       INT UNSIGNED NOT NULL,
    title               VARCHAR(255) NOT NULL,
    description         TEXT NOT NULL,
    requirements        TEXT DEFAULT NULL,
    location            VARCHAR(255) DEFAULT NULL,
    job_type            VARCHAR(50) NOT NULL DEFAULT 'full_time',
    industry_category   VARCHAR(50) DEFAULT NULL,
    salary_range        VARCHAR(100) DEFAULT NULL,
    deadline            DATE DEFAULT NULL,
    status              VARCHAR(20) NOT NULL DEFAULT 'active',
    created_at          DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at          DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    CONSTRAINT fk_jobs_hr FOREIGN KEY (hr_profile_id) REFERENCES hr_profiles(id) ON DELETE CASCADE,
    INDEX idx_jobs_status (status),
    INDEX idx_jobs_hr (hr_profile_id),
    INDEX idx_jobs_industry (industry_category),
    INDEX idx_jobs_type (job_type),
    INDEX idx_jobs_deadline (deadline),
    FULLTEXT idx_jobs_search (title, description)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ──────────────────────────────────────────────────────────────
-- APPLICATIONS TABLE
-- ──────────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS applications (
    id                  INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    job_id              INT UNSIGNED NOT NULL,
    pelamar_profile_id  INT UNSIGNED NOT NULL,
    cover_letter        TEXT DEFAULT NULL,
    current_status      VARCHAR(30) NOT NULL DEFAULT 'applied',
    applied_at          DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at          DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    CONSTRAINT fk_applications_job FOREIGN KEY (job_id) REFERENCES jobs(id) ON DELETE CASCADE,
    CONSTRAINT fk_applications_pelamar FOREIGN KEY (pelamar_profile_id) REFERENCES pelamar_profiles(id) ON DELETE CASCADE,
    UNIQUE KEY uk_job_pelamar (job_id, pelamar_profile_id),
    INDEX idx_applications_status (current_status),
    INDEX idx_applications_pelamar (pelamar_profile_id),
    INDEX idx_applications_applied (applied_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ──────────────────────────────────────────────────────────────
-- STATUS HISTORY TABLE
-- ──────────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS status_history (
    id                  INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    application_id      INT UNSIGNED NOT NULL,
    status              VARCHAR(30) NOT NULL,
    notes               TEXT DEFAULT NULL,
    changed_by_user_id  INT UNSIGNED DEFAULT NULL,
    changed_at          DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_status_history_app FOREIGN KEY (application_id) REFERENCES applications(id) ON DELETE CASCADE,
    CONSTRAINT fk_status_history_user FOREIGN KEY (changed_by_user_id) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_status_history_app (application_id),
    INDEX idx_status_history_changed (changed_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------------
-- SAVED JOBS TABLE (Bookmark Feature)
-- --------------------------------------------------------------
CREATE TABLE IF NOT EXISTS saved_jobs (
    id                  INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    pelamar_profile_id  INT UNSIGNED NOT NULL,
    job_id              INT UNSIGNED NOT NULL,
    saved_at            DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_saved_pelamar FOREIGN KEY (pelamar_profile_id) REFERENCES pelamar_profiles(id) ON DELETE CASCADE,
    CONSTRAINT fk_saved_job FOREIGN KEY (job_id) REFERENCES jobs(id) ON DELETE CASCADE,
    UNIQUE KEY uk_saved_jobs (pelamar_profile_id, job_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
