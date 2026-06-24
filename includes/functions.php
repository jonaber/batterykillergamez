<?php
/**
 * Shared helpers + session bootstrap.
 * Include this at the top of every page.
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Escape a string for safe HTML output.
 */
function e(?string $value): string
{
    return htmlspecialchars($value ?? '', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

/**
 * Pull one-time flash data (errors / old input / success flag) and clear it.
 */
function take_flash(string $key, $default = null)
{
    $value = $_SESSION[$key] ?? $default;
    unset($_SESSION[$key]);
    return $value;
}

/**
 * Set a one-time flash message shown on the next admin page load.
 */
function flash(string $message, string $type = 'success'): void
{
    $_SESSION['flash'] = ['message' => $message, 'type' => $type];
}

/* ── CSRF protection ──────────────────────────────────────────────── */

function csrf_token(): string
{
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf'];
}

/**
 * Hidden input to drop into any state-changing <form>.
 */
function csrf_field(): string
{
    return '<input type="hidden" name="csrf" value="' . e(csrf_token()) . '">';
}

/**
 * Verify the submitted CSRF token; abort the request if it's missing/wrong.
 */
function csrf_check(): void
{
    $token = $_POST['csrf'] ?? '';
    if (!is_string($token) || !hash_equals(csrf_token(), $token)) {
        http_response_code(400);
        exit('Invalid or expired form token. Go back and try again.');
    }
}

/**
 * Turn a title into a URL-safe slug.
 */
function slugify(string $text): string
{
    $text = strtolower(trim($text));
    $text = preg_replace('/[^a-z0-9]+/', '-', $text);
    return trim($text, '-');
}

/**
 * Save an uploaded image (from $_FILES[$field]) into assets/img/uploads and
 * return its project-relative path, or null if no file was uploaded.
 * Throws RuntimeException on a bad/oversized/unsupported file.
 */
function save_uploaded_image(string $field, string $uploadDirRel = 'assets/img/uploads'): ?string
{
    if (empty($_FILES[$field]) || ($_FILES[$field]['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
        return null;
    }

    $file = $_FILES[$field];
    if ($file['error'] !== UPLOAD_ERR_OK) {
        throw new RuntimeException('Image upload failed (error code ' . $file['error'] . ').');
    }
    if ($file['size'] > 5 * 1024 * 1024) {
        throw new RuntimeException('Image is too large (max 5 MB).');
    }

    $info = @getimagesize($file['tmp_name']);
    $extByMime = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/gif' => 'gif', 'image/webp' => 'webp'];
    if ($info === false || !isset($extByMime[$info['mime']])) {
        throw new RuntimeException('Unsupported image. Please use JPG, PNG, GIF or WEBP.');
    }

    $absDir = dirname(__DIR__) . '/' . $uploadDirRel;
    if (!is_dir($absDir) && !mkdir($absDir, 0775, true) && !is_dir($absDir)) {
        throw new RuntimeException('Could not create the uploads directory.');
    }

    $name = bin2hex(random_bytes(8)) . '.' . $extByMime[$info['mime']];
    if (!move_uploaded_file($file['tmp_name'], $absDir . '/' . $name)) {
        throw new RuntimeException('Could not store the uploaded image.');
    }

    return $uploadDirRel . '/' . $name;
}
