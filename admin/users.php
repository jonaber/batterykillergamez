<?php
require_once __DIR__ . '/../includes/auth.php';
require_admin();
require_once __DIR__ . '/_layout.php';

$db = get_db();

// Delete
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'delete') {
    csrf_check();
    $delId = (int) ($_POST['id'] ?? 0);
    $total = (int) $db->query('SELECT COUNT(*) FROM users')->fetchColumn();

    if ($delId === current_user_id()) {
        flash('You can’t delete the account you’re currently signed in with.', 'error');
    } elseif ($total <= 1) {
        flash('You can’t delete the last remaining admin user.', 'error');
    } else {
        $stmt = $db->prepare('DELETE FROM users WHERE id = :id');
        $stmt->execute([':id' => $delId]);
        flash('User deleted.');
    }
    header('Location: users.php');
    exit;
}

$users = $db->query('SELECT id, username, created_at, updated_at FROM users ORDER BY username')->fetchAll();

admin_layout_start('users', 'Users');
?>
<div class="toolbar">
  <p class="muted">Admin accounts that can sign in to this back office.</p>
  <a href="user-edit.php" class="btn btn-primary">+ New User</a>
</div>

<?php if (!$users): ?>
  <div class="empty">No users yet.</div>
<?php else: ?>
<table>
  <thead>
    <tr><th>Username</th><th>Created</th><th style="width:200px;">Actions</th></tr>
  </thead>
  <tbody>
<?php foreach ($users as $u): ?>
    <tr>
      <td>
        <?= e($u['username']) ?>
        <?php if ((int) $u['id'] === current_user_id()): ?><span class="pill pill-pub" style="margin-left:8px;">you</span><?php endif; ?>
      </td>
      <td class="muted"><?= e(substr((string) $u['created_at'], 0, 10)) ?></td>
      <td>
        <a class="btn btn-sm" href="user-edit.php?id=<?= (int) $u['id'] ?>">Edit</a>
<?php if ((int) $u['id'] !== current_user_id()): ?>
        <form method="post" style="display:inline;" onsubmit="return confirm('Delete user “<?= e($u['username']) ?>”?');">
          <?= csrf_field() ?>
          <input type="hidden" name="action" value="delete">
          <input type="hidden" name="id" value="<?= (int) $u['id'] ?>">
          <button class="btn btn-sm btn-danger" type="submit">Delete</button>
        </form>
<?php endif; ?>
      </td>
    </tr>
<?php endforeach; ?>
  </tbody>
</table>
<?php endif; ?>
<?php
admin_layout_end();
