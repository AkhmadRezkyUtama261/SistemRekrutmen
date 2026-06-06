<?php
/*
 * @Module:      Email Notification Handler
 * @Author:      BE-06 (Status & Email Lead)
 * @Date:        2026-05-24
 * @Description: Email notification system for application events.
 *               Uses PHPMailer for SMTP delivery with HTML templates.
 *               Falls back to mail() in development mode.
 *
 *               NOTE: PHPMailer must be installed via Composer:
 *                     composer require phpmailer/phpmailer
 *
 * @Ownership:   BE-06
 *
 * RecruitPro Enterprise — © 2026
 * Do NOT modify without PR approval from module owner.
 */

require_once __DIR__ . '/../config/app.php';

/*
 * ┌─────────────────────────────────────────────────────────┐
 * │  IMPORTANT: PHPMailer Installation                      │
 * │                                                         │
 * │  Run the following in the project root:                 │
 * │    composer require phpmailer/phpmailer                 │
 * │                                                         │
 * │  Then uncomment the PHPMailer use statements below      │
 * │  and the PHPMailer implementation in sendEmail().       │
 * └─────────────────────────────────────────────────────────┘
 */

// Uncomment after installing PHPMailer via Composer:
// use PHPMailer\PHPMailer\PHPMailer;
// use PHPMailer\PHPMailer\SMTP;
// use PHPMailer\PHPMailer\Exception as PHPMailerException;
//
// $autoloadPath = __DIR__ . '/../vendor/autoload.php';
// if (file_exists($autoloadPath)) {
//     require_once $autoloadPath;
// }

// ══════════════════════════════════════════════════════════════
// GENERIC EMAIL SENDER
// ══════════════════════════════════════════════════════════════

/**
 * Send an HTML email.
 *
 * In development mode, logs the email instead of sending it.
 * In production, uses PHPMailer with SMTP configuration from
 * config/app.php constants.
 *
 * @param  string $to       Recipient email address
 * @param  string $subject  Email subject line
 * @param  string $htmlBody Full HTML email body
 * @return bool   True if sent (or logged in dev mode), false on failure
 */
function sendEmail(string $to, string $subject, string $htmlBody): bool
{
    // ── Development mode: log instead of send ──
    if (APP_ENV === 'development') {
        error_log(sprintf(
            "[RecruitPro Email] TO: %s | SUBJECT: %s | LENGTH: %d bytes",
            $to,
            $subject,
            strlen($htmlBody)
        ));
        return true; // Simulate successful send
    }

    // ── Production mode: use PHPMailer ──
    // Uncomment and configure after `composer require phpmailer/phpmailer`:
    //
    // try {
    //     $mail = new PHPMailer(true);
    //
    //     // SMTP Configuration
    //     $mail->isSMTP();
    //     $mail->Host       = SMTP_HOST;
    //     $mail->SMTPAuth   = true;
    //     $mail->Username   = SMTP_USERNAME;
    //     $mail->Password   = SMTP_PASSWORD;
    //     $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    //     $mail->Port       = SMTP_PORT;
    //     $mail->CharSet    = 'UTF-8';
    //
    //     // Sender & Recipient
    //     $mail->setFrom(SMTP_FROM_EMAIL, SMTP_FROM_NAME);
    //     $mail->addAddress($to);
    //
    //     // Content
    //     $mail->isHTML(true);
    //     $mail->Subject = $subject;
    //     $mail->Body    = $htmlBody;
    //     $mail->AltBody = strip_tags(str_replace(['<br>', '<br/>', '<br />'], "\n", $htmlBody));
    //
    //     $mail->send();
    //     return true;
    // } catch (PHPMailerException $e) {
    //     error_log(sprintf(
    //         '[RecruitPro Email Error] Failed to send to %s. Error: %s',
    //         $to,
    //         $e->getMessage()
    //     ));
    //     return false;
    // }

    // Fallback: PHP mail() function
    $headers  = "MIME-Version: 1.0\r\n";
    $headers .= "Content-type: text/html; charset=UTF-8\r\n";
    $headers .= "From: " . SMTP_FROM_NAME . " <" . SMTP_FROM_EMAIL . ">\r\n";
    $headers .= "Reply-To: " . SMTP_FROM_EMAIL . "\r\n";
    $headers .= "X-Mailer: RecruitPro-Enterprise/1.0\r\n";

    $result = @mail($to, $subject, $htmlBody, $headers);

    if (!$result) {
        error_log(sprintf(
            '[RecruitPro Email Error] mail() failed for recipient: %s, subject: %s',
            $to,
            $subject
        ));
    }

    return $result;
}

// ══════════════════════════════════════════════════════════════
// APPLICATION CONFIRMATION EMAIL
// ══════════════════════════════════════════════════════════════

/**
 * Send application confirmation email to an applicant.
 *
 * Sent immediately after a pelamar submits a job application.
 *
 * @param  string $applicantEmail Applicant's email address
 * @param  string $jobTitle       Title of the applied position
 * @param  string $applicantName  Applicant's full name (optional)
 * @param  string $companyName    Hiring company name (optional)
 * @return bool   True if email was sent successfully
 */
function sendApplicationConfirmation(string $applicantEmail, string $jobTitle, string $applicantName = 'Pelamar', string $companyName = ''): bool
{
    $subject = 'Konfirmasi Lamaran — ' . $jobTitle;

    $safeName    = htmlspecialchars($applicantName, ENT_QUOTES, 'UTF-8');
    $safeTitle   = htmlspecialchars($jobTitle, ENT_QUOTES, 'UTF-8');
    $safeCompany = htmlspecialchars($companyName, ENT_QUOTES, 'UTF-8');

    $companyLine = $safeCompany ? " di <strong>{$safeCompany}</strong>" : '';

    $htmlBody = buildEmailTemplate(
        "Lamaran Berhasil Dikirim",
        "
        <p>Halo <strong>{$safeName}</strong>,</p>
        <p>Lamaran Anda untuk posisi <strong>{$safeTitle}</strong>{$companyLine} telah berhasil dikirim dan diterima oleh sistem kami.</p>
        <p>Tim rekrutmen akan meninjau lamaran Anda dan menghubungi Anda jika ada perkembangan lebih lanjut.</p>
        <div style='background-color: #1e293b; border-radius: 12px; padding: 16px; margin: 24px 0; border-left: 4px solid #6366f1;'>
            <p style='margin: 0; color: #94a3b8; font-size: 14px;'>📋 <strong style='color: #e2e8f0;'>Detail Lamaran</strong></p>
            <p style='margin: 8px 0 0; color: #cbd5e1;'>Posisi: <strong>{$safeTitle}</strong></p>
            " . ($safeCompany ? "<p style='margin: 4px 0 0; color: #cbd5e1;'>Perusahaan: <strong>{$safeCompany}</strong></p>" : "") . "
            <p style='margin: 4px 0 0; color: #cbd5e1;'>Status: <span style='color: #818cf8;'>Dilamar</span></p>
        </div>
        <p>Terima kasih telah menggunakan RecruitPro Enterprise.</p>
        "
    );

    return sendEmail($applicantEmail, $subject, $htmlBody);
}

// ══════════════════════════════════════════════════════════════
// STATUS UPDATE EMAIL
// ══════════════════════════════════════════════════════════════

/**
 * Send status update notification to an applicant.
 *
 * Sent whenever an HR updates the application status.
 *
 * @param  string $applicantEmail Applicant's email address
 * @param  string $jobTitle       Title of the position
 * @param  string $newStatus      New status constant value
 * @param  string $applicantName  Applicant's full name (optional)
 * @param  string $notes          HR notes about the status change (optional)
 * @return bool   True if email was sent successfully
 */
function sendStatusUpdate(string $applicantEmail, string $jobTitle, string $newStatus, string $applicantName = 'Pelamar', string $notes = ''): bool
{
    // Status labels in Indonesian
    $statusLabels = [
        STATUS_APPLIED      => 'Dilamar',
        STATUS_UNDER_REVIEW => 'Sedang Ditinjau',
        STATUS_INTERVIEW    => 'Wawancara',
        STATUS_ACCEPTED     => 'Diterima',
        STATUS_REJECTED     => 'Ditolak',
    ];

    // Status colors for email styling
    $statusColors = [
        STATUS_APPLIED      => '#94a3b8',
        STATUS_UNDER_REVIEW => '#f59e0b',
        STATUS_INTERVIEW    => '#818cf8',
        STATUS_ACCEPTED     => '#10b981',
        STATUS_REJECTED     => '#f43f5e',
    ];

    $statusText  = $statusLabels[$newStatus] ?? ucfirst($newStatus);
    $statusColor = $statusColors[$newStatus] ?? '#94a3b8';
    $subject     = "Update Status Lamaran — {$jobTitle}";

    $safeName  = htmlspecialchars($applicantName, ENT_QUOTES, 'UTF-8');
    $safeTitle = htmlspecialchars($jobTitle, ENT_QUOTES, 'UTF-8');
    $safeNotes = htmlspecialchars($notes, ENT_QUOTES, 'UTF-8');

    // Build status-specific message
    $statusMessage = match ($newStatus) {
        STATUS_UNDER_REVIEW => 'Lamaran Anda saat ini sedang ditinjau oleh tim rekrutmen.',
        STATUS_INTERVIEW    => 'Selamat! Anda telah diundang untuk tahap wawancara. Tim HR akan menghubungi Anda untuk penjadwalan.',
        STATUS_ACCEPTED     => 'Selamat! 🎉 Anda telah diterima untuk posisi ini. Tim HR akan menghubungi Anda mengenai langkah selanjutnya.',
        STATUS_REJECTED     => 'Mohon maaf, setelah pertimbangan matang, kami memutuskan untuk tidak melanjutkan proses rekrutmen Anda untuk posisi ini.',
        default             => 'Status lamaran Anda telah diperbarui.',
    };

    $notesBlock = $safeNotes ? "
        <div style='background-color: #1e293b; border-radius: 8px; padding: 12px 16px; margin: 16px 0;'>
            <p style='margin: 0; color: #94a3b8; font-size: 13px;'>📝 Catatan dari HR:</p>
            <p style='margin: 8px 0 0; color: #e2e8f0; font-style: italic;'>\"{$safeNotes}\"</p>
        </div>
    " : '';

    $htmlBody = buildEmailTemplate(
        "Update Status Lamaran",
        "
        <p>Halo <strong>{$safeName}</strong>,</p>
        <p>{$statusMessage}</p>
        <div style='background-color: #1e293b; border-radius: 12px; padding: 16px; margin: 24px 0; border-left: 4px solid {$statusColor};'>
            <p style='margin: 0; color: #94a3b8; font-size: 14px;'>📋 <strong style='color: #e2e8f0;'>Detail Update</strong></p>
            <p style='margin: 8px 0 0; color: #cbd5e1;'>Posisi: <strong>{$safeTitle}</strong></p>
            <p style='margin: 4px 0 0; color: #cbd5e1;'>Status Baru: <span style='color: {$statusColor}; font-weight: bold;'>{$statusText}</span></p>
        </div>
        {$notesBlock}
        <p>Terima kasih atas minat Anda. Jika ada pertanyaan, jangan ragu untuk menghubungi kami.</p>
        "
    );

    return sendEmail($applicantEmail, $subject, $htmlBody);
}

/**
 * Alias for sendStatusUpdate — backwards compatibility.
 */
function sendStatusUpdateEmail(string $email, string $name, string $jobTitle, string $newStatus, string $notes = ''): bool
{
    return sendStatusUpdate($email, $jobTitle, $newStatus, $name, $notes);
}

// ══════════════════════════════════════════════════════════════
// HTML EMAIL TEMPLATE
// ══════════════════════════════════════════════════════════════

/**
 * Build a styled HTML email template.
 *
 * Uses inline styles for maximum email client compatibility.
 * Dark theme matching the "Midnight Luxe" design aesthetic.
 *
 * @param  string $title   Email heading
 * @param  string $content HTML content for the email body
 * @return string Complete HTML email document
 */
function buildEmailTemplate(string $title, string $content): string
{
    $appName = htmlspecialchars(APP_NAME, ENT_QUOTES, 'UTF-8');
    $year    = date('Y');

    return <<<HTML
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{$title}</title>
</head>
<body style="margin: 0; padding: 0; background-color: #0f172a; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;">
    <div style="max-width: 600px; margin: 0 auto; padding: 40px 20px;">
        <!-- Header -->
        <div style="text-align: center; margin-bottom: 32px;">
            <div style="display: inline-block; background: linear-gradient(135deg, #6366f1, #4f46e5); padding: 12px 20px; border-radius: 12px;">
                <span style="color: #ffffff; font-size: 20px; font-weight: bold; letter-spacing: -0.5px;">Recruit<span style="color: #c7d2fe;">Pro</span></span>
            </div>
            <p style="color: #64748b; font-size: 11px; text-transform: uppercase; letter-spacing: 2px; margin: 8px 0 0;">Enterprise</p>
        </div>

        <!-- Main Card -->
        <div style="background-color: #1e293b; border-radius: 16px; padding: 32px; border: 1px solid rgba(255,255,255,0.08); box-shadow: 0 8px 32px rgba(0,0,0,0.3);">
            <h2 style="color: #f1f5f9; font-size: 20px; margin: 0 0 24px; font-weight: 600;">{$title}</h2>
            <div style="color: #cbd5e1; font-size: 15px; line-height: 1.7;">
                {$content}
            </div>
        </div>

        <!-- Footer -->
        <div style="text-align: center; margin-top: 32px; padding-top: 24px; border-top: 1px solid rgba(255,255,255,0.06);">
            <p style="color: #475569; font-size: 12px; margin: 0;">
                Email ini dikirim otomatis oleh {$appName}.<br>
                Mohon jangan membalas email ini.
            </p>
            <p style="color: #334155; font-size: 11px; margin: 12px 0 0;">
                © {$year} {$appName}. All rights reserved.
            </p>
        </div>
    </div>
</body>
</html>
HTML;
}
