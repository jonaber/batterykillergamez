<?php
require_once __DIR__ . '/../includes/auth.php';
require_admin();
require_once __DIR__ . '/_layout.php';

$db = get_db();

// Delete
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'delete') {
    csrf_check();
    $stmt = $db->prepare('DELETE FROM pages WHERE id = :id');
    $stmt->execute([':id' => (int) ($_POST['id'] ?? 0)]);
    flash('Page deleted.');
    header('Location: pages.php');
    exit;
}

$pages = $db->query('SELECT * FROM pages ORDER BY sort_order, id')->fetchAll();

admin_layout_start('pages', 'Pages');
?>
<div class="toolbar">
  <p class="muted">Standalone pages rendered with the site theme. Menu pages appear in the top nav.</p>
  <a href="page-edit.php" class="btn btn-primary">+ New Page</a>
</div>

<?php if (!$pages): ?>
  <div class="empty">No pages yet. Create your first one.</div>
<?php else: ?>
<table>
  <thead>
    <tr><th>Title</th><th>Slug</th><th>Status</th><th>In menu</th><th style="width:180px;">Actions</th></tr>
  </thead>
  <tbody>
<?php foreach ($pages as $p): ?>
    <tr>
      <td><?= e($p['title']) ?></td>
      <td class="muted">/page.php?slug=<?= e($p['slug']) ?></td>
      <td><span class="pill pill-<?= $p['status'] === 'published' ? 'pub' : 'draft' ?>"><?= e($p['status']) ?></span></td>
      <td><?= $p['in_menu'] ? 'Yes' : '<span class="muted">No</span>' ?></td>
      <td>
        <a class="btn btn-sm" href="page-edit.php?id=<?= (int) $p['id'] ?>">Edit</a>
        <a class="btn btn-sm" href="../page.php?slug=<?= e(urlencode($p['slug'])) ?>" target="_blank">View</a>
        <form method="post" style="display:inline;" onsubmit="return confirm('Delete this page?');">
          <?= csrf_field() ?>
          <input type="hidden" name="action" value="delete">
          <input type="hidden" name="id" value="<?= (int) $p['id'] ?>">
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
