<?php
/**
 * Admin authentication.
 *
 * require_admin() guards every admin page: if the visitor isn't logged in it
 * redirects to the admin login. Login/logout themselves are handled here too.
 */

require_once __DIR__ . '/functions.php';
require_once __DIR__ . '/db.php';

function is_admin(): bool
{
    return !empty($_SESSION['is_admin']);
}

/**
 * ID of the currently logged-in admin user (0 if not logged in).
 */
function current_user_id(): int
{
    return (int) ($_SESSION['user_id'] ?? 0);
}

/**
 * Username of the currently logged-in admin user.
 */
function current_username(): string
{
    return (string) ($_SESSION['username'] ?? '');
}

/**
 * Call at the top of any protected admin page.
 */
function require_admin(): void
{
    if (!is_admin()) {
        header('Location: index.php');
        exit;
    }
}

/**
 * Attempt a login from a posted username + password. Returns an error string,
 * or '' on success (after which the session is flagged).
 */
function attempt_login(string $username, string $password): string
{
    $username = trim($username);

    $stmt = get_db()->prepare('SELECT id, username, password FROM users WHERE username = :u LIMIT 1');
    $stmt->execute([':u' => $username]);
    $user = $stmt->fetch();

    // Always run a hash comparison — even when the user doesn't exist — so the
    // response time doesn't reveal which usernames are valid.
    static $dummyHash = null;
    if ($dummyHash === null) {
        $dummyHash = password_hash('not-a-real-password', PASSWORD_DEFAULT);
    }

    $hash = $user ? (string) $user['password'] : $dummyHash;
    if (password_verify($password, $hash) && $user) {
        session_regenerate_id(true);
        $_SESSION['is_admin'] = true;
        $_SESSION['user_id']  = (int) $user['id'];
        $_SESSION['username'] = $user['username'];
        return '';
    }

    return 'Incorrect username or password.';
}

function logout(): void
{
    unset($_SESSION['is_admin'], $_SESSION['user_id'], $_SESSION['username']);
}
