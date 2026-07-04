<?php
/**
 * Application configuration.
 *
 * Connection details and the first-admin seed are read from a `.env` file in
 * the project root (see `.env.example` for the keys) so secrets stay out of the
 * code. Every value falls back to the stock-XAMPP default below, so a fresh
 * "drop it in htdocs" install still works even with no `.env` present.
 */

/**
 * Minimal .env reader — no Composer/dotenv dependency. Parses `KEY=VALUE` lines
 * (skipping blanks and `#` comments), strips optional surrounding quotes, and
 * returns an associative array. The result is cached so the file is read once
 * per request.
 */
$loadEnv = static function (string $path): array {
    static $cache = [];
    if (array_key_exists($path, $cache)) {
        return $cache[$path];
    }

    $vars = [];
    if (is_readable($path)) {
        foreach (file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
            $line = trim($line);
            if ($line === '' || $line[0] === '#' || strpos($line, '=') === false) {
                continue;
            }
            [$key, $value] = explode('=', $line, 2);
            $key   = trim($key);
            $value = trim($value);
            // Strip a single matching pair of surrounding quotes, if present.
            if (strlen($value) >= 2 && ($value[0] === '"' || $value[0] === "'") && $value[-1] === $value[0]) {
                $value = substr($value, 1, -1);
            }
            $vars[$key] = $value;
        }
    }

    return $cache[$path] = $vars;
};

$env = $loadEnv(__DIR__ . '/.env');

/**
 * Resolve a setting: prefer the .env file, then a real environment variable,
 * then the supplied default. An empty .env value (e.g. an empty DB password)
 * is respected rather than treated as "unset".
 */
$cfg = static function (string $key, string $default) use ($env): string {
    if (array_key_exists($key, $env)) {
        return $env[$key];
    }
    $fromSystem = getenv($key);
    return $fromSystem !== false ? $fromSystem : $default;
};

return [
    'db' => [
        'host'    => $cfg('DB_HOST', '127.0.0.1'),
        'name'    => $cfg('DB_NAME', 'batterykillergamez'),
        'user'    => $cfg('DB_USER', 'root'),
        'pass'    => $cfg('DB_PASS', ''),
        'charset' => $cfg('DB_CHARSET', 'utf8mb4'),
    ],

    'admin' => [
        // Seed credentials for the FIRST admin user. These are only used to
        // create the initial account the first time the database is set up.
        // After that, manage logins under Admin → Users (this value is ignored).
        'username' => $cfg('ADMIN_USERNAME', 'admin'),
        'password' => $cfg('ADMIN_PASSWORD', 'changeme'),
    ],

    // SMTP settings for the contact form (PHPMailer). See includes/mailer.php.
    'mail' => [
        'enabled'      => filter_var($cfg('MAIL_ENABLED', 'false'), FILTER_VALIDATE_BOOLEAN),
        'host'         => $cfg('MAIL_HOST', ''),
        'port'         => $cfg('MAIL_PORT', '587'),
        'username'     => $cfg('MAIL_USERNAME', ''),
        'password'     => $cfg('MAIL_PASSWORD', ''),
        'encryption'   => $cfg('MAIL_ENCRYPTION', 'tls'),  // tls | ssl | none
        'from_address' => $cfg('MAIL_FROM_ADDRESS', ''),
        'from_name'    => $cfg('MAIL_FROM_NAME', 'Battery Killer Gamez'),
        'to_address'   => $cfg('MAIL_TO_ADDRESS', ''),      // where submissions are sent
    ],

    // Used as the contact form's confirmation copy.
    'site_name' => $cfg('SITE_NAME', 'Battery Killer Gamez'),
];
