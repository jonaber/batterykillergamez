<?php
/**
 * Shared admin chrome. Usage:
 *
 *   require_once __DIR__ . '/_layout.php';
 *   admin_layout_start('pages', 'Pages');   // active nav key + page title
 *   ... page body ...
 *   admin_layout_end();
 *
 * Pass $loadEditor = true to admin_layout_start() to load the TinyMCE editor.
 */

function admin_nav_items(): array
{
    return [
        'dashboard' => ['index.php',    '▦ Dashboard'],
        'pages'     => ['pages.php',    '▤ Pages'],
        'games'     => ['games.php',    '🎮 Games'],
        'slides'    => ['slides.php',   '🖼 Slides'],
        'about'     => ['about.php',    '★ About'],
        'messages'  => ['messages.php', '✉ Messages'],
        'users'     => ['users.php',    '👤 Users'],
    ];
}

function admin_layout_start(string $active, string $title, bool $loadEditor = false): void
{
    $flash = take_flash('flash');
    ?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= e($title) ?> — BKG Admin</title>
<link rel="stylesheet" href="admin.css">
<?php if ($loadEditor): ?>
<script src="https://cdn.jsdelivr.net/npm/tinymce@6/tinymce.min.js"></script>
<?php endif; ?>
</head>
<body>
<div class="admin-shell">
  <aside class="admin-side">
    <div class="admin-brand">
      <img src="../assets/img/logo.jpeg" alt="Battery Killer Gamez" onerror="this.style.display='none'">
      <span>ADMIN</span>
    </div>
    <nav>
<?php foreach (admin_nav_items() as $key => [$href, $label]): ?>
      <a href="<?= e($href) ?>"<?= $key === $active ? ' class="active"' : '' ?>><?= $label ?></a>
<?php endforeach; ?>
      <hr>
      <a href="../index.php" target="_blank">↗ View site</a>
      <a href="index.php?logout=1" class="logout">⏻ Log out</a>
    </nav>
<?php if (current_username() !== ''): ?>
    <div class="admin-signed-in">Signed in as <strong><?= e(current_username()) ?></strong></div>
<?php endif; ?>
  </aside>
  <main class="admin-main">
    <h1 class="admin-h1"><?= e($title) ?></h1>
<?php if ($flash): ?>
    <div class="flash flash-<?= e($flash['type']) ?>"><?= e($flash['message']) ?></div>
<?php endif; ?>
<?php
}

function admin_layout_end(): void
{
    ?>
  </main>
</div>
</body>
</html>
<?php
}
