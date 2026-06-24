<?php
require_once __DIR__ . '/../includes/auth.php';
require_admin();
require_once __DIR__ . '/_layout.php';

$db = get_db();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'delete') {
    csrf_check();
    $db->prepare('DELETE FROM contact_messages WHERE id = :id')->execute([':id' => (int) ($_POST['id'] ?? 0)]);
    flash('Message deleted.');
    header('Location: messages.php');
    exit;
}

$messages = $db->query('SELECT * FROM contact_messages ORDER BY created_at DESC')->fetchAll();

admin_layout_start('messages', 'Contact Submissions');
?>
<p style="margin-bottom:18px;" class="muted"><?= count($messages) ?> message<?= count($messages) === 1 ? '' : 's' ?> from the contact form.</p>

<?php if (!$messages): ?>
  <div class="empty">No submissions yet.</div>
<?php else: ?>
<table>
  <thead>
    <tr><th>Received</th><th>Name</th><th>Email</th><th>Subject</th><th>Game</th><th>Message</th><th></th></tr>
  </thead>
  <tbody>
<?php foreach ($messages as $m): ?>
    <tr>
      <td class="muted" style="white-space:nowrap;"><?= e($m['created_at']) ?></td>
      <td><?= e($m['name']) ?></td>
      <td><a href="mailto:<?= e($m['email']) ?>"><?= e($m['email']) ?></a></td>
      <td><?= $m['subject'] ? '<span class="pill pill-tag">' . e($m['subject']) . '</span>' : '<span class="muted">—</span>' ?></td>
      <td><?= $m['game'] ? e($m['game']) : '<span class="muted">—</span>' ?></td>
      <td class="msg"><?= e($m['message']) ?></td>
      <td>
        <form method="post" onsubmit="return confirm('Delete this message?');">
          <?= csrf_field() ?>
          <input type="hidden" name="action" value="delete">
          <input type="hidden" name="id" value="<?= (int) $m['id'] ?>">
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
