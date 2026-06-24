<?php
/**
 * Content loaders — read editable site content from the database.
 * Used by the public pages (index.php, page.php, header.php).
 */

require_once __DIR__ . '/db.php';

function get_slides(): array
{
    return get_db()
        ->query('SELECT * FROM slides ORDER BY sort_order, id')
        ->fetchAll();
}

function get_games(): array
{
    return get_db()
        ->query('SELECT * FROM games ORDER BY sort_order, id')
        ->fetchAll();
}

/**
 * Pages flagged to appear in the top navigation.
 */
function get_menu_pages(): array
{
    return get_db()
        ->query("SELECT slug, title FROM pages
                 WHERE status = 'published' AND in_menu = 1
                 ORDER BY sort_order, id")
        ->fetchAll();
}

function get_page_by_slug(string $slug): ?array
{
    $stmt = get_db()->prepare(
        "SELECT * FROM pages WHERE slug = :slug AND status = 'published' LIMIT 1"
    );
    $stmt->execute([':slug' => $slug]);
    $page = $stmt->fetch();
    return $page ?: null;
}

function get_setting(string $name, ?string $default = null): ?string
{
    $stmt = get_db()->prepare('SELECT value FROM settings WHERE name = :n LIMIT 1');
    $stmt->execute([':n' => $name]);
    $value = $stmt->fetchColumn();
    return $value === false ? $default : $value;
}

function set_setting(string $name, string $value): void
{
    $stmt = get_db()->prepare(
        'INSERT INTO settings (name, value) VALUES (:n, :v)
         ON DUPLICATE KEY UPDATE value = VALUES(value)'
    );
    $stmt->execute([':n' => $name, ':v' => $value]);
}

/**
 * About section content: rich-text HTML + a list of tag strings.
 */
function get_about(): array
{
    $tags = get_setting('about_tags', '');
    return [
        'html' => get_setting('about_html', ''),
        'tags' => array_filter(array_map('trim', explode(',', $tags ?? ''))),
    ];
}
