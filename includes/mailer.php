<?php
/**
 * Contact-form email sending via PHPMailer (SMTP).
 *
 * All transport settings come from config.php, which reads them from the
 * project-root `.env` (MAIL_* keys). PHPMailer is vendored in lib/PHPMailer/;
 * there is no Composer autoloader, so its three source files are required here.
 */

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception as PHPMailerException;

require_once __DIR__ . '/db.php'; // get_config()
require_once __DIR__ . '/../lib/PHPMailer/Exception.php';
require_once __DIR__ . '/../lib/PHPMailer/PHPMailer.php';
require_once __DIR__ . '/../lib/PHPMailer/SMTP.php';

/**
 * Send the contact-form notification email to the site owner.
 *
 * @param array $msg Keys: name, email, subject, game, message.
 * @throws RuntimeException if mailing is disabled/misconfigured or sending fails.
 */
function send_contact_email(array $msg): void
{
    $mail = get_config()['mail'];

    if (!$mail['enabled']) {
        throw new RuntimeException('Email sending is disabled (set MAIL_ENABLED=true in .env).');
    }
    if ($mail['host'] === '' || $mail['to_address'] === '') {
        throw new RuntimeException('Email is not configured (missing MAIL_HOST or MAIL_TO_ADDRESS in .env).');
    }

    $mailer = new PHPMailer(true); // throw exceptions on failure

    // ── Transport ──
    $mailer->isSMTP();
    $mailer->Host     = $mail['host'];
    $mailer->Port     = (int) $mail['port'];
    $mailer->SMTPAuth = $mail['username'] !== '';
    if ($mailer->SMTPAuth) {
        $mailer->Username = $mail['username'];
        $mailer->Password = $mail['password'];
    }

    switch (strtolower((string) $mail['encryption'])) {
        case 'tls':
            $mailer->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            break;
        case 'ssl':
            $mailer->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
            break;
        default: // 'none' / '' — no encryption (e.g. local mail catcher)
            $mailer->SMTPSecure  = false;
            $mailer->SMTPAutoTLS = false;
    }

    $mailer->CharSet = PHPMailer::CHARSET_UTF8;

    // ── Addresses ──
    $fromAddress = $mail['from_address'] !== '' ? $mail['from_address'] : $mail['username'];
    $fromName    = $mail['from_name'] !== '' ? $mail['from_name'] : 'Website';
    $mailer->setFrom($fromAddress, $fromName);
    $mailer->addAddress($mail['to_address']);

    // Let the owner reply straight to the visitor.
    if (!empty($msg['email']) && filter_var($msg['email'], FILTER_VALIDATE_EMAIL)) {
        $mailer->addReplyTo($msg['email'], (string) ($msg['name'] ?? ''));
    }

    // ── Content ──
    $subjectLabel = ($msg['subject'] ?? '') !== '' ? $msg['subject'] : 'General enquiry';
    $mailer->Subject = 'New contact message: ' . $subjectLabel;
    $mailer->isHTML(false);
    $mailer->Body = implode("\n", [
        'Name:    ' . ($msg['name'] ?? ''),
        'Email:   ' . ($msg['email'] ?? ''),
        'Subject: ' . (($msg['subject'] ?? '') !== '' ? $msg['subject'] : '—'),
        'Game:    ' . (($msg['game'] ?? '') !== '' ? $msg['game'] : '—'),
        '',
        (string) ($msg['message'] ?? ''),
    ]);

    try {
        $mailer->send();
    } catch (PHPMailerException $e) {
        // $mailer->ErrorInfo carries the human-readable detail.
        throw new RuntimeException('Mailer error: ' . $mailer->ErrorInfo, 0, $e);
    }
}
