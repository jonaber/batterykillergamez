<?php
require_once __DIR__ . '/../includes/auth.php';
require_admin();
require_once __DIR__ . '/_layout.php';

$db = get_db();

$id    = (int) ($_GET['id'] ?? 0);
$slide = ['id' => 0, 'tag' => '', 'title' => '', 'description' => '', 'btn_label' => '', 'img' => '', 'sort_order' => 0];

if ($id) {
    $stmt = $db->prepare('SELECT * FROM slides WHERE id = :id');
    $stmt->execute([':id' => $id]);
    $found = $stmt->fetch();
    if (!$found) {
        flash('Slide not found.', 'error');
        header('Location: slides.php');
        exit;
    }
    $slide = $found;
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();

    $slide['tag']         = trim($_POST['tag'] ?? '');
    $slide['title']       = trim($_POST['title'] ?? '');
    $slide['description'] = trim($_POST['description'] ?? '');
    $slide['btn_label']   = trim($_POST['btn_label'] ?? '');
    $slide['sort_order']  = (int) ($_POST['sort_order'] ?? 0);

    if ($slide['title'] === '') {
        $errors[] = 'Title is required.';
    }

    try {
        $uploaded = save_uploaded_image('image');
        if ($uploaded !== null) {
            $slide['img'] = $uploaded;
        }
    } catch (Throwable $ex) {
        $errors[] = $ex->getMessage();
    }

    if (!$errors) {
        if ($id) {
            $stmt = $db->prepare(
                'UPDATE slides SET tag=:tag, title=:title, description=:description,
                 btn_label=:btn, img=:img, sort_order=:sort WHERE id=:id'
            );
            $stmt->execute([
                ':tag' => $slide['tag'], ':title' => $slide['title'], ':description' => $slide['description'],
                ':btn' => $slide['btn_label'], ':img' => $slide['img'], ':sort' => $slide['sort_order'], ':id' => $id,
            ]);
            flash('Slide updated.');
        } else {
            $stmt = $db->prepare(
                'INSERT INTO slides (tag, title, description, btn_label, img, sort_order)
                 VALUES (:tag, :title, :description, :btn, :img, :sort)'
            );
            $stmt->execute([
                ':tag' => $slide['tag'], ':title' => $slide['title'], ':description' => $slide['description'],
                ':btn' => $slide['btn_label'], ':img' => $slide['img'], ':sort' => $slide['sort_order'],
            ]);
            flash('Slide created.');
        }
        header('Location: slides.php');
        exit;
    }
}

admin_layout_start('slides', $id ? 'Edit Slide' : 'New Slide');
?>
<p style="margin-bottom:18px;"><a href="slides.php">← Back to slides</a></p>

<?php if ($errors): ?>
<div class="flash flash-error">
  <?php foreach ($errors as $err): ?><div><?= e($err) ?></div><?php endforeach; ?>
</div>
<?php endif; ?>

<form class="card" method="post" enctype="multipart/form-data">
  <?= csrf_field() ?>
  <div class="field-row">
    <div class="field">
      <label for="tag">Tag / eyebrow</label>
      <input type="text" id="tag" name="tag" value="<?= e($slide['tag']) ?>" placeholder="Featured Title">
    </div>
    <div class="field">
      <label for="btn_label">Button label</label>
      <input type="text" id="btn_label" name="btn_label" value="<?= e($slide['btn_label']) ?>" placeholder="Play Now">
    </div>
    <div class="field">
      <label for="sort_order">Order</label>
      <input type="number" id="sort_order" name="sort_order" value="<?= (int) $slide['sort_order'] ?>">
    </div>
  </div>
  <div class="field">
    <label for="title">Title</label>
    <input type="text" id="title" name="title" value="<?= e($slide['title']) ?>" required>
    <div class="hint">You can wrap part of it in &lt;span&gt;…&lt;/span&gt; to highlight it in red, e.g. <code>The &lt;span&gt;Streets&lt;/span&gt; Game</code></div>
  </div>
  <div class="field">
    <label for="description">Description</label>
    <textarea id="description" name="description"><?= e($slide['description']) ?></textarea>
  </div>
  <div class="field">
    <label>Background image</label>
<?php if ($slide['img']): ?>
    <div style="margin-bottom:10px;"><img class="thumb" style="width:160px;height:74px;" src="../<?= e($slide['img']) ?>" alt=""></div>
<?php endif; ?>
    <input type="file" name="image" accept="image/*">
    <div class="hint">Leave empty to keep the current image. JPG/PNG/GIF/WEBP, max 5 MB.</div>
  </div>
  <div class="form-actions">
    <button type="submit" class="btn btn-primary"><?= $id ? 'Save changes' : 'Create slide' ?></button>
    <a href="slides.php" class="btn">Cancel</a>
  </div>
</form>
<?php
admin_layout_end();
