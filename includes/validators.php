<?php
/*
 * @Module:      Server-Side Validation Engine
 * @Author:      BE-04 (Profile & File Lead)
 * @Date:        2026-05-24
 * @Description: Comprehensive validation functions for forms, file uploads,
 *               and business data. Includes ValidationResult class for
 *               structured error collection.
 * @Ownership:   BE-04
 *
 * RecruitPro Enterprise — © 2026
 * Do NOT modify without PR approval from module owner.
 */

require_once __DIR__ . '/../config/app.php';

// ══════════════════════════════════════════════════════════════
// VALIDATION RESULT CLASS
// ══════════════════════════════════════════════════════════════

/**
 * Collects validation errors and provides query methods.
 *
 * Usage:
 *   $result = new ValidationResult();
 *   $result->addError('email', 'Email tidak valid.');
 *   if ($result->hasErrors()) { ... }
 */
class ValidationResult
{
    /** @var array<string, string> Field-keyed error messages */
    private array $errors = [];

    /**
     * Add a validation error for a specific field.
     *
     * @param string $field   Field name (form input name)
     * @param string $message Error message in Indonesian
     */
    public function addError(string $field, string $message): void
    {
        $this->errors[$field] = $message;
    }

    /**
     * Check if any validation errors exist.
     *
     * @return bool True if there are errors
     */
    public function hasErrors(): bool
    {
        return !empty($this->errors);
    }

    /**
     * Get all validation errors.
     *
     * @return array<string, string> Field => message pairs
     */
    public function getErrors(): array
    {
        return $this->errors;
    }

    /**
     * Get error message for a specific field.
     *
     * @param  string      $field Field name
     * @return string|null Error message or null if field is valid
     */
    public function getError(string $field): ?string
    {
        return $this->errors[$field] ?? null;
    }

    /**
     * Get the first error message.
     *
     * @return string|null First error message or null
     */
    public function getFirstError(): ?string
    {
        return !empty($this->errors) ? reset($this->errors) : null;
    }

    /**
     * Merge errors from another ValidationResult.
     *
     * @param ValidationResult $other Another result to merge
     */
    public function merge(ValidationResult $other): void
    {
        $this->errors = array_merge($this->errors, $other->getErrors());
    }
}

// ══════════════════════════════════════════════════════════════
// REQUIRED FIELD VALIDATION
// ══════════════════════════════════════════════════════════════

/**
 * Validate that required fields are present and non-empty.
 *
 * @param  array $fields Associative array: ['field_name' => 'Label Nama']
 * @param  array $data   Data array to validate (typically $_POST)
 * @return array Errors array: ['field_name' => 'Label Nama wajib diisi.']
 */
function validateRequired(array $fields, array $data): array
{
    $errors = [];

    foreach ($fields as $field => $label) {
        $value = $data[$field] ?? '';

        if (is_string($value)) {
            $value = trim($value);
        }

        if (empty($value) && $value !== '0') {
            $errors[$field] = htmlspecialchars($label, ENT_QUOTES, 'UTF-8') . ' wajib diisi.';
        }
    }

    return $errors;
}

// ══════════════════════════════════════════════════════════════
// EMAIL VALIDATION
// ══════════════════════════════════════════════════════════════

/**
 * Validate an email address format and length.
 *
 * Checks:
 *   - Non-empty
 *   - Valid format via filter_var
 *   - Maximum 255 characters
 *
 * @param  string $email Email address to validate
 * @return bool   True if valid
 */
function validateEmail(string $email): bool
{
    $email = trim($email);

    if (empty($email)) {
        return false;
    }

    if (mb_strlen($email, 'UTF-8') > 255) {
        return false;
    }

    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

// ══════════════════════════════════════════════════════════════
// PASSWORD VALIDATION
// ══════════════════════════════════════════════════════════════

/**
 * Validate password strength.
 *
 * Requirements:
 *   - Minimum 8 characters
 *   - At least 1 uppercase letter
 *   - At least 1 number
 *   - At least 1 special character (!@#$%^&*()_+-=[]{}|;:',.<>?/~`)
 *
 * @param  string $password Password to validate
 * @return array  Array of error messages (empty if valid)
 */
function validatePassword(string $password): array
{
    $errors = [];

    if (mb_strlen($password, 'UTF-8') < 8) {
        $errors[] = 'Password minimal 8 karakter.';
    }

    if (!preg_match('/[A-Z]/', $password)) {
        $errors[] = 'Password harus mengandung minimal 1 huruf besar.';
    }

    if (!preg_match('/[0-9]/', $password)) {
        $errors[] = 'Password harus mengandung minimal 1 angka.';
    }

    if (!preg_match('/[!@#$%^&*()_+\-=\[\]{}|;:\',.<>?\/~`]/', $password)) {
        $errors[] = 'Password harus mengandung minimal 1 karakter spesial.';
    }

    return $errors;
}

/**
 * Validate that password and confirmation match.
 *
 * @param  string $password        Password
 * @param  string $confirmPassword Confirmation password
 * @return bool   True if they match
 */
function validatePasswordMatch(string $password, string $confirmPassword): bool
{
    return $password === $confirmPassword;
}

// ══════════════════════════════════════════════════════════════
// PHONE VALIDATION
// ══════════════════════════════════════════════════════════════

/**
 * Validate an Indonesian phone number format.
 *
 * Accepts formats:
 *   - +62xxx, 08xxx, 62xxx
 *   - 8-15 digits (after country code normalization)
 *   - Allows spaces, dashes, parentheses as separators
 *
 * @param  string $phone Phone number to validate
 * @return bool   True if valid Indonesian phone format
 */
function validatePhone(string $phone): bool
{
    $phone = trim($phone);

    if (empty($phone)) {
        return false;
    }

    // Remove common formatting characters for validation
    $cleaned = preg_replace('/[\s\-\(\)]/', '', $phone);

    // Must be between 8 and 20 characters
    if (strlen($cleaned) < 8 || strlen($cleaned) > 20) {
        return false;
    }

    // Match Indonesian phone patterns
    // +62xxx, 62xxx, 08xxx, or general international format
    return preg_match('/^(\+?62|0)[0-9]{8,15}$/', $cleaned) === 1
        || preg_match('/^[0-9+\-\s()]{8,20}$/', $phone) === 1;
}

// ══════════════════════════════════════════════════════════════
// FILE VALIDATION
// ══════════════════════════════════════════════════════════════

/**
 * Validate a PDF file upload.
 *
 * Performs three-layer validation:
 *   1. MIME type check via finfo_file()
 *   2. File size check (max 2MB from UPLOAD_MAX_SIZE constant)
 *   3. Magic bytes check (%PDF header)
 *
 * @param  array $file $_FILES array element for the upload
 * @return array Validation result: ['valid' => bool, 'error' => string|null]
 */
function validatePDF(array $file): array
{
    // Check for upload errors
    if (!isset($file['tmp_name']) || $file['error'] !== UPLOAD_ERR_OK) {
        $errorMessages = [
            UPLOAD_ERR_INI_SIZE   => 'File terlalu besar (melebihi batas server).',
            UPLOAD_ERR_FORM_SIZE  => 'File terlalu besar (melebihi batas form).',
            UPLOAD_ERR_PARTIAL    => 'File hanya terunggah sebagian.',
            UPLOAD_ERR_NO_FILE    => 'Tidak ada file yang dipilih.',
            UPLOAD_ERR_NO_TMP_DIR => 'Folder temporary tidak tersedia.',
            UPLOAD_ERR_CANT_WRITE => 'Gagal menulis file ke disk.',
        ];

        return [
            'valid' => false,
            'error' => $errorMessages[$file['error']] ?? 'Error upload tidak diketahui.',
        ];
    }

    // ── File size check (max 2MB) ──
    if ($file['size'] > UPLOAD_MAX_SIZE) {
        return [
            'valid' => false,
            'error' => 'Ukuran file maksimal 2MB.',
        ];
    }

    // ── MIME type check via finfo ──
    $finfo    = new finfo(FILEINFO_MIME_TYPE);
    $mimeType = $finfo->file($file['tmp_name']);

    if ($mimeType !== 'application/pdf') {
        return [
            'valid' => false,
            'error' => 'Hanya file PDF yang diperbolehkan. Tipe terdeteksi: '
                     . htmlspecialchars($mimeType, ENT_QUOTES, 'UTF-8'),
        ];
    }

    // ── Magic bytes check (%PDF) ──
    $handle = fopen($file['tmp_name'], 'rb');
    if ($handle) {
        $header = fread($handle, 4);
        fclose($handle);

        if ($header !== '%PDF') {
            return [
                'valid' => false,
                'error' => 'File bukan PDF yang valid (magic bytes tidak cocok).',
            ];
        }
    }

    // ── Extension check ──
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if ($ext !== 'pdf') {
        return [
            'valid' => false,
            'error' => 'Ekstensi file harus .pdf.',
        ];
    }

    return [
        'valid' => true,
        'error' => null,
    ];
}

// ══════════════════════════════════════════════════════════════
// BUSINESS DATA VALIDATION
// ══════════════════════════════════════════════════════════════

/**
 * Validate job posting data.
 *
 * Checks that title, description, and requirements are present
 * and within acceptable length limits.
 *
 * @param  array $data Job form data
 * @return array Array of error messages (empty if valid)
 */
function validateJobData(array $data): array
{
    $errors = [];

    // Title validation
    $title = trim($data['title'] ?? '');
    if (empty($title)) {
        $errors['title'] = 'Judul lowongan wajib diisi.';
    } elseif (mb_strlen($title, 'UTF-8') > 255) {
        $errors['title'] = 'Judul lowongan maksimal 255 karakter.';
    }

    // Description validation
    $description = trim($data['description'] ?? '');
    if (empty($description)) {
        $errors['description'] = 'Deskripsi lowongan wajib diisi.';
    } elseif (mb_strlen($description, 'UTF-8') < 50) {
        $errors['description'] = 'Deskripsi lowongan minimal 50 karakter.';
    }

    // Requirements validation
    $requirements = trim($data['requirements'] ?? '');
    if (empty($requirements)) {
        $errors['requirements'] = 'Persyaratan lowongan wajib diisi.';
    }

    return $errors;
}

// ══════════════════════════════════════════════════════════════
// DATE VALIDATION
// ══════════════════════════════════════════════════════════════

/**
 * Validate a date string against a specific format.
 *
 * @param  string $date   Date string to validate
 * @param  string $format Expected format (default 'Y-m-d')
 * @return bool   True if valid date in the specified format
 */
function validateDate(string $date, string $format = 'Y-m-d'): bool
{
    $d = DateTime::createFromFormat($format, $date);
    return $d && $d->format($format) === $date;
}
