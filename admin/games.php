<?php
require_once __DIR__ . '/../includes/auth.php';
require_admin();
require_once __DIR__ . '/_layout.php';

$db = get_db();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'delete') {
    csrf_check();
    $db->prepare('DELETE FROM games WHERE id = :id')->execute([':id' => (int) ($_POST['id'] ?? 0)]);
    flash('Game deleted.');
    header('Location: games.php');
    exit;
}

$games = $db->query('SELECT * FROM games ORDER BY sort_order, id')->fetchAll();

admin_layout_start('games', 'Games');
?>
<div class="toolbar">
  <p class="muted">The games grid on the homepage. Drag-free ordering via the "Order" field.</p>
  <a href="game-edit.php" class="btn btn-primary">+ New Game</a>
</div>

<?php if (!$games): ?>
  <div class="empty">No games yet.</div>
<?php else: ?>
<table>
  <thead>
    <tr><th>Image</th><th>Title</th><th>Genre</th><th>Rating</th><th>Order</th><th style="width:150px;">Actions</th></tr>
  </thead>
  <tbody>
<?php foreach ($games as $g): ?>
    <tr>
      <td><?= $g['img'] ? '<img class="thumb" src="../' . e($g['img']) . '" alt="">' : '<span class="muted">—</span>' ?></td>
      <td><?= e($g['title']) ?></td>
      <td class="muted"><?= e($g['genre']) ?></td>
      <td><?= e($g['rating']) ?></td>
      <td><?= (int) $g['sort_order'] ?></td>
      <td>
        <a class="btn btn-sm" href="game-edit.php?id=<?= (int) $g['id'] ?>">Edit</a>
        <form method="post" style="display:inline;" onsubmit="return confirm('Delete this game?');">
          <?= csrf_field() ?>
          <input type="hidden" name="action" value="delete">
          <input type="hidden" name="id" value="<?= (int) $g['id'] ?>">
          <button class="btn btn-sm btn-danger" type="submit">Delete</button>
        </form>
      </td>
    </tr>
<?php endforeach; ?>
  </tbody>
</table>
<?php endif; ?>
<?php
admin_layout_end();
