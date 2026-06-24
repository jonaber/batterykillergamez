<?php
require_once __DIR__ . '/functions.php';
require_once __DIR__ . '/content.php';

// Pages flagged for the top nav. Fail soft if the DB is unavailable.
try {
    $menuPages = get_menu_pages();
} catch (Throwable $ex) {
    $menuPages = [];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= e($pageTitle ?? 'Battery Killer Gamez') ?></title>
<link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;600;700;900&family=Rajdhani:wght@400;500;600;700&family=Share+Tech+Mono&display=swap" rel="stylesheet">
<link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

<!-- NAV -->
<nav>
  <a href="index.php" class="nav-logo">
    <img src="assets/img/logo.svg" alt="Battery Killer Gamez Logo" onerror="this.style.display='none'">
  </a>
  <ul class="nav-links">
    <li><a href="index.php#games">Games</a></li>
    <li><a href="index.php#about">About</a></li>
    <li><a href="index.php#contact">Contact</a></li>
<?php foreach ($menuPages as $mp): ?>
    <li><a href="page.php?slug=<?= e(urlencode($mp['slug'])) ?>"><?= e($mp['title']) ?></a></li>
<?php endforeach; ?>
    <li><a href="index.php#contact" class="nav-cta">Play Now</a></li>
  </ul>
</nav>
