<?php
require_once __DIR__ . '/../includes/auth.php';
require_admin();
require_once __DIR__ . '/_layout.php';

$db = get_db();

$id   = (int) ($_GET['id'] ?? 0);
$user = ['id' => 0, 'username' => ''];

if ($id) {
    $stmt = $db->prepare('SELECT id, username FROM users WHERE id = :id');
    $stmt->execute([':id' => $id]);
    $found = $stmt->fetch();
    if (!$found) {
        flash('User not found.', 'error');
        header('Location: users.php');
        exit;
    }
    $user = $found;
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();

    $user['username'] = trim($_POST['username'] ?? '');
    $password         = (string) ($_POST['password'] ?? '');
    $confirm          = (string) ($_POST['password_confirm'] ?? '');

    // Username
    if ($user['username'] === '') {
        $errors[] = 'Username is required.';
    } elseif (mb_strlen($user['username']) > 80) {
        $errors[] = 'Username must be 80 characters or fewer.';
    } else {
        $stmt = $db->prepare('SELECT id FROM users WHERE username = :u AND id <> :id');
        $stmt->execute([':u' => $user['username'], ':id' => $id]);
        if ($stmt->fetch()) {
            $errors[] = 'That username is already taken.';
        }
    }

    // Password — required when creating; optional when editing (blank = keep current).
    $passwordRequired = ($id === 0);
    if ($passwordRequired && $password === '') {
        $errors[] = 'Password is required.';
    }
    if ($password !== '') {
        if (mb_strlen($password) < 6) {
            $errors[] = 'Password must be at least 6 characters.';
        }
        if ($password !== $confirm) {
            $errors[] = 'Password and confirmation do not match.';
        }
    }

    if (!$errors) {
        if ($id) {
            if ($password !== '') {
                $stmt = $db->prepare('UPDATE users SET username = :u, password = :p WHERE id = :id');
                $stmt->execute([
                    ':u' => $user['username'],
                    ':p' => password_hash($password, PASSWORD_DEFAULT),
                    ':id' => $id,
                ]);
            } else {
                $stmt = $db->prepare('UPDATE users SET username = :u WHERE id = :id');
                $stmt->execute([':u' => $user['username'], ':id' => $id]);
            }
            // Keep the session label in sync if you renamed yourself.
            if ($id === current_user_id()) {
                $_SESSION['username'] = $user['username'];
            }
            flash('User updated.');
        } else {
            $stmt = $db->prepare('INSERT INTO users (username, password) VALUES (:u, :p)');
            $stmt->execute([
                ':u' => $user['username'],
                ':p' => password_hash($password, PASSWORD_DEFAULT),
            ]);
            flash('User created.');
        }
        header('Location: users.php');
        exit;
    }
}

admin_layout_start('users', $id ? 'Edit User' : 'New User');
?>
<p style="margin-bottom:18px;"><a href="users.php">← Back to users</a></p>

<?php if ($errors): ?>
<div class="flash flash-error">
  <?php foreach ($errors as $err): ?><div><?= e($err) ?></div><?php endforeach; ?>
</div>
<?php endif; ?>

<form class="card" method="post" autocomplete="off">
  <?= csrf_field() ?>
  <div class="field">
    <label for="username">Username</label>
    <input type="text" id="username" name="username" value="<?= e($user['username']) ?>" autocomplete="off" required>
  </div>
  <div class="field-row">
    <div class="field">
      <label for="password">Password</label>
      <input type="password" id="password" name="password" autocomplete="new-password"<?= $id ? '' : ' required' ?>>
      <div class="hint"><?= $id ? 'Leave blank to keep the current password.' : 'At least 6 characters.' ?></div>
    </div>
    <div class="field">
      <label for="password_confirm">Confirm password</label>
      <input type="password" id="password_confirm" name="password_confirm" autocomplete="new-password">
    </div>
  </div>
  <div class="form-actions">
    <button type="submit" class="btn btn-primary"><?= $id ? 'Save changes' : 'Create user' ?></button>
    <a href="users.php" class="btn">Cancel</a>
  </div>
</form>
<?php
admin_layout_end();
