<?php
/**
 * Contact form handler.
 *
 * Validates the submission, stores it in MySQL, then redirects back to the
 * contact section (Post/Redirect/Get) so a refresh won't re-submit.
 */

require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/content.php';
require_once __DIR__ . '/includes/mailer.php';

// Only accept POST.
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php#contact');
    exit;
}

$data    = require __DIR__ . '/includes/data.php';
$subjects = $data['subjects'];
// Validate the game against the live DB list (what the form actually offers),
// not the data.php seed — otherwise admin-edited games get wrongly rejected.
$games    = array_merge(array_map(fn($g) => $g['title'], get_games()), ['General / All Games']);

// Collect + trim input.
$name    = trim($_POST['name']    ?? '');
$email   = trim($_POST['email']   ?? '');
$subject = trim($_POST['subject'] ?? '');
$game    = trim($_POST['game']    ?? '');
$message = trim($_POST['message'] ?? '');

// Validate.
$errors = [];

if ($name === '') {
    $errors[] = 'Please enter your name.';
} elseif (mb_strlen($name) > 120) {
    $errors[] = 'Your name is too long (max 120 characters).';
}

if ($email === '') {
    $errors[] = 'Please enter your email address.';
} elseif (!filter_var($email, FILTER_VALIDATE_EMAIL) || mb_strlen($email) > 190) {
    $errors[] = 'Please enter a valid email address.';
}

if ($subject === '') {
    $errors[] = 'Please choose a subject.';
} elseif (!in_array($subject, $subjects, true)) {
    $errors[] = 'Please choose a valid subject.';
}

if ($game === '') {
    $errors[] = 'Please choose a related game.';
} elseif (!in_array($game, $games, true)) {
    $errors[] = 'Please choose a valid game.';
}

if ($message === '') {
    $errors[] = 'Please enter a message.';
} elseif (mb_strlen($message) > 5000) {
    $errors[] = 'Your message is too long (max 5000 characters).';
}

// On error: remember input + errors, bounce back to the form.
if ($errors) {
    $_SESSION['errors'] = $errors;
    $_SESSION['old']    = compact('name', 'email', 'subject', 'game', 'message');
    header('Location: index.php#contact');
    exit;
}

// Store it.
try {
    $stmt = get_db()->prepare(
        'INSERT INTO contact_messages (name, email, subject, game, message, ip_address)
         VALUES (:name, :email, :subject, :game, :message, :ip)'
    );
    $stmt->execute([
        ':name'    => $name,
        ':email'   => $email,
        ':subject' => $subject !== '' ? $subject : null,
        ':game'    => $game !== '' ? $game : null,
        ':message' => $message,
        ':ip'      => $_SERVER['REMOTE_ADDR'] ?? null,
    ]);
} catch (Throwable $ex) {
    // Don't leak DB internals to the visitor; keep their input.
    $_SESSION['errors'] = ['Sorry — something went wrong saving your message. Please try again.'];
    $_SESSION['old']    = compact('name', 'email', 'subject', 'game', 'message');
    error_log('Contact form DB error: ' . $ex->getMessage());
    header('Location: index.php#contact');
    exit;
}

// Notify the site owner by email. The message is already safely stored, so a
// mail failure (or disabled/misconfigured SMTP) must not break the visitor's
// flow — log it and still report success.
try {
    send_contact_email(compact('name', 'email', 'subject', 'game', 'message'));
} catch (Throwable $ex) {
    error_log('Contact form email error: ' . $ex->getMessage());
}

// Success.
$_SESSION['sent'] = true;
header('Location: index.php#contact');
exit;
