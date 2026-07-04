<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/content.php';
require_once __DIR__ . '/_layout.php';

// Logout
if (isset($_GET['logout'])) {
    logout();
    header('Location: index.php');
    exit;
}

// Login
$loginError = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['password'])) {
    $loginError = attempt_login((string) ($_POST['username'] ?? ''), (string) $_POST['password']);
    if ($loginError === '') {
        header('Location: index.php');
        exit;
    }
}

/* ── Login screen ── */
if (!is_admin()):
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Login — BKG Admin</title>
<link rel="stylesheet" href="admin.css">
</head>
<body>
  <div class="login-wrap">
    <div class="admin-brand">
      <img src="../assets/img/logo.jpeg" alt="Battery Killer Gamez" onerror="this.style.display='none'">
      <span>ADMIN</span>
    </div>
    <form class="card" method="post" action="index.php">
<?php if ($loginError): ?>
      <div class="flash flash-error"><?= e($loginError) ?></div>
<?php endif; ?>
      <div class="field">
        <label for="username">Username</label>
        <input type="text" id="username" name="username" value="<?= e($_POST['username'] ?? '') ?>" autofocus required>
      </div>
      <div class="field">
        <label for="password">Password</label>
        <input type="password" id="password" name="password" required>
      </div>
      <button type="submit" class="btn btn-primary" style="width:100%;">Sign In</button>
    </form>
  </div>
</body>
</html>
<?php
exit;
endif;

/* ── Dashboard ── */
$db = get_db();
$counts = [
    'pages'    => (int) $db->query('SELECT COUNT(*) FROM pages')->fetchColumn(),
    'games'    => (int) $db->query('SELECT COUNT(*) FROM games')->fetchColumn(),
    'slides'   => (int) $db->query('SELECT COUNT(*) FROM slides')->fetchColumn(),
    'messages' => (int) $db->query('SELECT COUNT(*) FROM contact_messages')->fetchColumn(),
];

admin_layout_start('dashboard', 'Dashboard');
?>
<div class="dash-grid">
  <div class="dash-card"><div class="num"><?= $counts['pages'] ?></div><div class="lbl">Pages</div><div style="margin-top:10px;"><a href="pages.php">Manage →</a></div></div>
  <div class="dash-card"><div class="num"><?= $counts['games'] ?></div><div class="lbl">Games</div><div style="margin-top:10px;"><a href="games.php">Manage →</a></div></div>
  <div class="dash-card"><div class="num"><?= $counts['slides'] ?></div><div class="lbl">Hero Slides</div><div style="margin-top:10px;"><a href="slides.php">Manage →</a></div></div>
  <div class="dash-card"><div class="num"><?= $counts['messages'] ?></div><div class="lbl">Messages</div><div style="margin-top:10px;"><a href="messages.php">View →</a></div></div>
</div>
<p style="margin-top:28px; color:#888; font-size:14px;">
  Welcome to the Battery Killer Gamez backoffice. Use the menu on the left to edit the homepage
  content (games, slides, about) or create standalone pages that show in the site navigation.
</p>
<?php
admin_layout_end();
