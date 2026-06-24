<?php
require_once __DIR__ . '/../includes/auth.php';
require_admin();
require_once __DIR__ . '/_layout.php';

$db = get_db();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'delete') {
    csrf_check();
    $db->prepare('DELETE FROM slides WHERE id = :id')->execute([':id' => (int) ($_POST['id'] ?? 0)]);
    flash('Slide deleted.');
    header('Location: slides.php');
    exit;
}

$slides = $db->query('SELECT * FROM slides ORDER BY sort_order, id')->fetchAll();

admin_layout_start('slides', 'Hero Slides');
?>
<div class="toolbar">
  <p class="muted">The rotating hero carousel at the top of the homepage.</p>
  <a href="slide-edit.php" class="btn btn-primary">+ New Slide</a>
</div>

<?php if (!$slides): ?>
  <div class="empty">No slides yet.</div>
<?php else: ?>
<table>
  <thead>
    <tr><th>Image</th><th>Tag</th><th>Title</th><th>Order</th><th style="width:150px;">Actions</th></tr>
  </thead>
  <tbody>
<?php foreach ($slides as $s): ?>
    <tr>
      <td><?= $s['img'] ? '<img class="thumb" src="../' . e($s['img']) . '" alt="">' : '<span class="muted">—</span>' ?></td>
      <td class="muted"><?= e($s['tag']) ?></td>
      <td><?= strip_tags($s['title']) /* show plain in list */ ?></td>
      <td><?= (int) $s['sort_order'] ?></td>
      <td>
        <a class="btn btn-sm" href="slide-edit.php?id=<?= (int) $s['id'] ?>">Edit</a>
        <form method="post" style="display:inline;" onsubmit="return confirm('Delete this slide?');">
          <?= csrf_field() ?>
          <input type="hidden" name="action" value="delete">
          <input type="hidden" name="id" value="<?= (int) $s['id'] ?>">
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
