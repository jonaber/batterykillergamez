<?php
require_once __DIR__ . '/../includes/auth.php';
require_admin();
require_once __DIR__ . '/_layout.php';

$db = get_db();

$id   = (int) ($_GET['id'] ?? 0);
$page = ['id' => 0, 'title' => '', 'slug' => '', 'body' => '', 'in_menu' => 1, 'status' => 'published', 'sort_order' => 0];

if ($id) {
    $stmt = $db->prepare('SELECT * FROM pages WHERE id = :id');
    $stmt->execute([':id' => $id]);
    $found = $stmt->fetch();
    if (!$found) {
        flash('Page not found.', 'error');
        header('Location: pages.php');
        exit;
    }
    $page = $found;
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();

    $page['title']      = trim($_POST['title'] ?? '');
    $page['slug']       = slugify($_POST['slug'] !== '' ? $_POST['slug'] : ($_POST['title'] ?? ''));
    $page['body']       = $_POST['body'] ?? '';
    $page['in_menu']    = isset($_POST['in_menu']) ? 1 : 0;
    $page['status']     = ($_POST['status'] ?? 'published') === 'draft' ? 'draft' : 'published';
    $page['sort_order'] = (int) ($_POST['sort_order'] ?? 0);

    if ($page['title'] === '') {
        $errors[] = 'Title is required.';
    }
    if ($page['slug'] === '') {
        $errors[] = 'Slug is required (letters, numbers and dashes).';
    } else {
        // Slug uniqueness (excluding self).
        $stmt = $db->prepare('SELECT id FROM pages WHERE slug = :slug AND id <> :id');
        $stmt->execute([':slug' => $page['slug'], ':id' => $id]);
        if ($stmt->fetch()) {
            $errors[] = 'That slug is already in use — choose another.';
        }
    }
    if (trim(strip_tags($page['body'])) === '') {
        $errors[] = 'Body content is required.';
    }

    if (!$errors) {
        if ($id) {
            $stmt = $db->prepare(
                'UPDATE pages SET title=:title, slug=:slug, body=:body, in_menu=:in_menu,
                 status=:status, sort_order=:sort WHERE id=:id'
            );
            $stmt->execute([
                ':title' => $page['title'], ':slug' => $page['slug'], ':body' => $page['body'],
                ':in_menu' => $page['in_menu'], ':status' => $page['status'],
                ':sort' => $page['sort_order'], ':id' => $id,
            ]);
            flash('Page updated.');
        } else {
            $stmt = $db->prepare(
                'INSERT INTO pages (title, slug, body, in_menu, status, sort_order)
                 VALUES (:title, :slug, :body, :in_menu, :status, :sort)'
            );
            $stmt->execute([
                ':title' => $page['title'], ':slug' => $page['slug'], ':body' => $page['body'],
                ':in_menu' => $page['in_menu'], ':status' => $page['status'], ':sort' => $page['sort_order'],
            ]);
            flash('Page created.');
        }
        header('Location: pages.php');
        exit;
    }
}

admin_layout_start('pages', $id ? 'Edit Page' : 'New Page', true);
?>
<p style="margin-bottom:18px;"><a href="pages.php">← Back to pages</a></p>

<?php if ($errors): ?>
<div class="flash flash-error">
  <?php foreach ($errors as $err): ?><div><?= e($err) ?></div><?php endforeach; ?>
</div>
<?php endif; ?>

<form class="card" method="post">
  <?= csrf_field() ?>
  <div class="field">
    <label for="title">Title</label>
    <input type="text" id="title" name="title" value="<?= e($page['title']) ?>" required>
  </div>
  <div class="field">
    <label for="slug">Slug</label>
    <input type="text" id="slug" name="slug" value="<?= e($page['slug']) ?>" placeholder="auto-generated from title if left blank">
    <div class="hint">URL: page.php?slug=<strong><?= e($page['slug'] ?: 'your-slug') ?></strong></div>
  </div>
  <div class="field">
    <label for="body">Content</label>
    <textarea id="body" name="body" class="wysiwyg"><?= e($page['body']) ?></textarea>
  </div>
  <div class="field-row">
    <div class="field">
      <label for="status">Status</label>
      <select id="status" name="status">
        <option value="published"<?= $page['status'] === 'published' ? ' selected' : '' ?>>Published</option>
        <option value="draft"<?= $page['status'] === 'draft' ? ' selected' : '' ?>>Draft</option>
      </select>
    </div>
    <div class="field">
      <label for="sort_order">Menu order</label>
      <input type="number" id="sort_order" name="sort_order" value="<?= (int) $page['sort_order'] ?>">
    </div>
    <div class="field">
      <label>Navigation</label>
      <label class="checkbox" style="margin-top:8px;">
        <input type="checkbox" name="in_menu" value="1"<?= $page['in_menu'] ? ' checked' : '' ?>>
        Show in top menu
      </label>
    </div>
  </div>
  <div class="form-actions">
    <button type="submit" class="btn btn-primary"><?= $id ? 'Save changes' : 'Create page' ?></button>
    <a href="pages.php" class="btn">Cancel</a>
  </div>
</form>

<script>
  tinymce.init({
    selector: 'textarea.wysiwyg',
    skin: 'oxide-dark',
    content_css: 'dark',
    height: 420,
    menubar: false,
    plugins: 'link lists code image table',
    toolbar: 'undo redo | blocks | bold italic | bullist numlist | link image table | code',
  });
</script>
<?php
admin_layout_end();
