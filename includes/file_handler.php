<?php
/*
 * @Module:      PDF File Management — CV Handler
 * @Author:      BE-04 (Profile & File Lead)
 * @Date:        2026-05-24
 * @Description: Secure file upload, storage, retrieval, and deletion
 *               for CV/resume PDF files. Includes hashed filenames,
 *               directory protection, and size formatting.
 * @Ownership:   BE-04
 *
 * RecruitPro Enterprise — © 2026
 * Do NOT modify without PR approval from module owner.
 */

require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/validators.php';

// ══════════════════════════════════════════════════════════════
// DIRECTORY SETUP & PROTECTION
// ══════════════════════════════════════════════════════════════

/**
 * Ensure the upload directory exists and is protected.
 *
 * Creates the uploads/cv/ directory if missing, and writes
 * an .htaccess file to prevent direct PHP execution.
 */
function ensureUploadDirectory(): void
{
    $uploadDir = UPLOAD_DIR;

    // Create directory if it doesn't exist
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }

    // Write .htaccess protection to prevent PHP execution in uploads
    $htaccessPath = $uploadDir . '.htaccess';
    if (!file_exists($htaccessPath)) {
        $htaccessContent = <<<'HTACCESS'
# RecruitPro Enterprise — Upload Directory Protection
# Prevent direct execution of uploaded files

# Deny access to all file types except PDF
<FilesMatch "\.(?!pdf$)">
    Require all denied
</FilesMatch>

# Disable PHP execution entirely
<FilesMatch "\.ph(p[0-9]?|tml|ar)$">
    Require all denied
</FilesMatch>

# Remove handler for PHP files
RemoveHandler .php .phtml .php3 .php4 .php5 .php7 .phps .pht .phar
RemoveType .php .phtml .php3 .php4 .php5 .php7 .phps .pht .phar

# Disable script execution
Options -ExecCGI
AddHandler cgi-script .php .pl .py .jsp .asp .sh .cgi

# Force PDF content type
AddType application/pdf .pdf
HTACCESS;

        file_put_contents($htaccessPath, $htaccessContent);
    }
}

// ══════════════════════════════════════════════════════════════
// CV UPLOAD
// ══════════════════════════════════════════════════════════════

/**
 * Validate and upload a CV file.
 *
 * Performs full validation (MIME, size, magic bytes), generates a
 * hashed filename, stores the file, and returns the result.
 *
 * @param  array $file   $_FILES['cv'] array element
 * @param  int   $userId User ID for filename association
 * @return array Result: ['success' => bool, 'message' => string, 'filename' => string|null]
 */
function uploadCV(array $file, int $userId): array
{
    // Validate the PDF file
    $validation = validatePDF($file);
    if (!$validation['valid']) {
        return [
            'success'  => false,
            'message'  => $validation['error'],
            'filename' => null,
        ];
    }

    // Ensure upload directory exists and is protected
    ensureUploadDirectory();

    // Generate a hashed filename
    $newFilename = generateHashedFilename($file['name']);
    $uploadPath  = UPLOAD_DIR . $newFilename;

    // Move the uploaded file
    if (!move_uploaded_file($file['tmp_name'], $uploadPath)) {
        error_log(sprintf(
            '[RecruitPro FileHandler] Failed to move uploaded file for user %d. Source: %s, Dest: %s',
            $userId,
            $file['tmp_name'],
            $uploadPath
        ));

        return [
            'success'  => false,
            'message'  => 'Gagal menyimpan file. Silakan coba lagi.',
            'filename' => null,
        ];
    }

    // Set restrictive permissions on the uploaded file
    chmod($uploadPath, 0644);

    return [
        'success'  => true,
        'message'  => 'CV berhasil diunggah.',
        'filename' => $newFilename,
    ];
}

/**
 * Handle CV upload with legacy interface compatibility.
 *
 * @param  array $file $_FILES['cv'] array element
 * @return array Result with success, message, filename keys
 */
function handleCvUpload(array $file): array
{
    // Check for upload errors first
    if ($file['error'] !== UPLOAD_ERR_OK) {
        $errors = [
            UPLOAD_ERR_INI_SIZE   => 'File terlalu besar (melebihi batas server).',
            UPLOAD_ERR_FORM_SIZE  => 'File terlalu besar (melebihi batas form).',
            UPLOAD_ERR_PARTIAL    => 'File hanya terunggah sebagian.',
            UPLOAD_ERR_NO_FILE    => 'Tidak ada file yang diunggah.',
            UPLOAD_ERR_NO_TMP_DIR => 'Folder temporary tidak tersedia.',
            UPLOAD_ERR_CANT_WRITE => 'Gagal menulis file ke disk.',
        ];
        return [
            'success'  => false,
            'message'  => $errors[$file['error']] ?? 'Error tidak diketahui.',
            'filename' => null,
        ];
    }

    // Validate file size
    if ($file['size'] > UPLOAD_MAX_SIZE) {
        return [
            'success'  => false,
            'message'  => 'Ukuran file maksimal 2MB.',
            'filename' => null,
        ];
    }

    // Validate MIME type
    $finfo    = new finfo(FILEINFO_MIME_TYPE);
    $mimeType = $finfo->file($file['tmp_name']);
    if ($mimeType !== ALLOWED_MIME) {
        return [
            'success'  => false,
            'message'  => 'Hanya file PDF yang diperbolehkan.',
            'filename' => null,
        ];
    }

    // Validate extension
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if ($ext !== ALLOWED_EXT) {
        return [
            'success'  => false,
            'message'  => 'Ekstensi file harus .pdf.',
            'filename' => null,
        ];
    }

    // Ensure upload directory exists
    ensureUploadDirectory();

    // Generate secure filename and move
    $newFilename = generateHashedFilename($file['name']);
    $uploadPath  = UPLOAD_DIR . $newFilename;

    if (!move_uploaded_file($file['tmp_name'], $uploadPath)) {
        return [
            'success'  => false,
            'message'  => 'Gagal menyimpan file. Silakan coba lagi.',
            'filename' => null,
        ];
    }

    chmod($uploadPath, 0644);

    return [
        'success'  => true,
        'message'  => 'File berhasil diunggah.',
        'filename' => $newFilename,
    ];
}

// ══════════════════════════════════════════════════════════════
// CV DELETION
// ══════════════════════════════════════════════════════════════

/**
 * Securely delete a CV file from disk.
 *
 * Validates that the file is within the upload directory to prevent
 * path traversal attacks.
 *
 * @param  string $filePath Filename (not full path) of the CV to delete
 * @return bool   True if file was deleted or didn't exist
 */
function deleteCV(string $filePath): bool
{
    // Prevent directory traversal
    $filename = basename($filePath);
    $fullPath = UPLOAD_DIR . $filename;

    // Verify the resolved path is within the upload directory
    $realUploadDir = realpath(UPLOAD_DIR);
    $realFilePath  = realpath($fullPath);

    if ($realFilePath === false) {
        // File doesn't exist — consider it "deleted"
        return true;
    }

    if (strpos($realFilePath, $realUploadDir) !== 0) {
        error_log(sprintf(
            '[RecruitPro FileHandler] Path traversal attempt: %s resolved to %s',
            $filePath,
            $realFilePath
        ));
        return false;
    }

    return unlink($realFilePath);
}

/**
 * Alias for deleteCV — backwards compatibility.
 */
function deleteCvFile(string $filename): bool
{
    return deleteCV($filename);
}

// ══════════════════════════════════════════════════════════════
// CV PATH RETRIEVAL
// ══════════════════════════════════════════════════════════════

/**
 * Retrieve the CV file path for a user from the database.
 *
 * @param  int         $userId User ID
 * @return string|null CV filename or null if not found
 */
function getCVPath(int $userId): ?string
{
    $sql = "SELECT cv_file FROM pelamar_profiles WHERE user_id = :user_id";
    $stmt = Database::query($sql, [':user_id' => $userId]);
    $result = $stmt->fetch();

    if ($result && !empty($result['cv_file'])) {
        return $result['cv_file'];
    }

    return null;
}

// ══════════════════════════════════════════════════════════════
// FILENAME GENERATION
// ══════════════════════════════════════════════════════════════

/**
 * Generate a unique hashed filename for uploaded files.
 *
 * Combines a random token with a timestamp to ensure uniqueness.
 * Preserves the original file extension.
 *
 * @param  string $originalName Original filename from upload
 * @return string Hashed filename (e.g., "cv_a1b2c3d4e5f6_1716537600.pdf")
 */
function generateHashedFilename(string $originalName): string
{
    $ext  = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
    $hash = bin2hex(random_bytes(16));
    $time = time();

    return "cv_{$hash}_{$time}.{$ext}";
}

// ══════════════════════════════════════════════════════════════
// FILE SIZE FORMATTING
// ══════════════════════════════════════════════════════════════

/**
 * Convert bytes to human-readable file size.
 *
 * @param  int    $bytes     File size in bytes
 * @param  int    $precision Decimal precision (default 2)
 * @return string Formatted size (e.g., "1.45 MB", "340 KB")
 */
function getHumanFileSize(int $bytes, int $precision = 2): string
{
    if ($bytes <= 0) {
        return '0 B';
    }

    $units = ['B', 'KB', 'MB', 'GB', 'TB'];
    $pow   = floor(log($bytes, 1024));
    $pow   = min($pow, count($units) - 1);

    $size = $bytes / pow(1024, $pow);

    return round($size, $precision) . ' ' . $units[$pow];
}

// ══════════════════════════════════════════════════════════════
// CV DOWNLOAD STREAMING
// ══════════════════════════════════════════════════════════════

/**
 * Stream a CV file for download with proper headers.
 *
 * @param string $filename    Stored filename
 * @param string $displayName Display name for download (default 'CV.pdf')
 */
function streamCvDownload(string $filename, string $displayName = 'CV.pdf'): void
{
    $path = UPLOAD_DIR . basename($filename);

    if (!file_exists($path)) {
        http_response_code(404);
        die('File tidak ditemukan.');
    }

    header('Content-Type: application/pdf');
    header('Content-Disposition: attachment; filename="' . htmlspecialchars($displayName, ENT_QUOTES, 'UTF-8') . '"');
    header('Content-Length: ' . filesize($path));
    header('Cache-Control: no-cache, no-store, must-revalidate');
    header('Pragma: no-cache');
    header('Expires: 0');

    readfile($path);
    exit;
}

// Run directory setup on include
ensureUploadDirectory();
