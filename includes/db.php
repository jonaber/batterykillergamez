<?php
/**
 * Database access (PDO / MySQL).
 *
 * get_db() connects to MySQL and, on first use per request, transparently
 * creates the database, all tables, and seeds the homepage content from
 * includes/data.php — so a stock XAMPP install works with zero manual setup.
 */

function get_config(): array
{
    static $config = null;
    if ($config === null) {
        $config = require __DIR__ . '/../config.php';
    }
    return $config;
}

function get_db(): PDO
{
    static $pdo = null;
    if ($pdo instanceof PDO) {
        return $pdo;
    }

    $cfg = get_config()['db'];

    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ];

    // Connect without selecting a database first, so we can create it if needed.
    $dsn = "mysql:host={$cfg['host']};charset={$cfg['charset']}";
    $pdo = new PDO($dsn, $cfg['user'], $cfg['pass'], $options);

    $pdo->exec(
        "CREATE DATABASE IF NOT EXISTS `{$cfg['name']}` " .
        "CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci"
    );
    $pdo->exec("USE `{$cfg['name']}`");

    db_install($pdo);

    return $pdo;
}

/**
 * Create tables (idempotent) and seed content the first time around.
 */
function db_install(PDO $pdo): void
{
    // Run each statement in schema.sql separately (PDO::exec is one stmt).
    $sql = file_get_contents(__DIR__ . '/../db/schema.sql');
    foreach (array_filter(array_map('trim', explode(';', $sql))) as $statement) {
        if ($statement !== '') {
            $pdo->exec($statement);
        }
    }

    db_seed($pdo);
}

/**
 * Seed homepage content from data.php, but only for tables that are empty.
 */
function db_seed(PDO $pdo): void
{
    $data = require __DIR__ . '/data.php';

    // Slides
    if ((int) $pdo->query('SELECT COUNT(*) FROM slides')->fetchColumn() === 0) {
        $stmt = $pdo->prepare(
            'INSERT INTO slides (tag, title, description, btn_label, img, sort_order)
             VALUES (:tag, :title, :description, :btn, :img, :sort)'
        );
        foreach ($data['slides'] as $i => $s) {
            $stmt->execute([
                ':tag' => $s['tag'], ':title' => $s['title'], ':description' => $s['desc'],
                ':btn' => $s['btn'], ':img' => $s['img'], ':sort' => $i,
            ]);
        }
    }

    // Games
    if ((int) $pdo->query('SELECT COUNT(*) FROM games')->fetchColumn() === 0) {
        $stmt = $pdo->prepare(
            'INSERT INTO games (genre, title, rating, meta, img, sort_order)
             VALUES (:genre, :title, :rating, :meta, :img, :sort)'
        );
        foreach ($data['games'] as $i => $g) {
            $stmt->execute([
                ':genre' => $g['genre'], ':title' => $g['title'], ':rating' => $g['rating'],
                ':meta' => implode(', ', $g['meta']), ':img' => $g['img'], ':sort' => $i,
            ]);
        }
    }

    // Pages
    if ((int) $pdo->query('SELECT COUNT(*) FROM pages')->fetchColumn() === 0) {
        $stmt = $pdo->prepare(
            'INSERT INTO pages (slug, title, body, in_menu, status, sort_order)
             VALUES (:slug, :title, :body, :in_menu, "published", :sort)'
        );
        foreach ($data['pages'] as $i => $p) {
            $stmt->execute([
                ':slug' => $p['slug'], ':title' => $p['title'], ':body' => $p['body'],
                ':in_menu' => $p['in_menu'], ':sort' => $i,
            ]);
        }
    }

    // First admin user (seeded from config so a fresh install can log in).
    if ((int) $pdo->query('SELECT COUNT(*) FROM users')->fetchColumn() === 0) {
        $admin = get_config()['admin'];
        $stmt = $pdo->prepare('INSERT INTO users (username, password) VALUES (:u, :p)');
        $stmt->execute([
            ':u' => trim((string) ($admin['username'] ?? 'admin')) ?: 'admin',
            ':p' => password_hash((string) ($admin['password'] ?? 'changeme'), PASSWORD_DEFAULT),
        ]);
    }

    // About settings
    if ((int) $pdo->query("SELECT COUNT(*) FROM settings WHERE name='about_html'")->fetchColumn() === 0) {
        $stmt = $pdo->prepare('INSERT INTO settings (name, value) VALUES (:n, :v)');
        $stmt->execute([':n' => 'about_html', ':v' => $data['about']['html']]);
        $stmt->execute([':n' => 'about_tags', ':v' => implode(', ', $data['about']['tags'])]);
    }
}
