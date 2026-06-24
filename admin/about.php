<?php
require_once __DIR__ . '/../includes/auth.php';
require_admin();
require_once __DIR__ . '/../includes/content.php';
require_once __DIR__ . '/_layout.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    set_setting('about_html', $_POST['about_html'] ?? '');
    // Normalise the tag list.
    $tags = array_filter(array_map('trim', explode(',', $_POST['about_tags'] ?? '')));
    set_setting('about_tags', implode(', ', $tags));
    flash('About section updated.');
    header('Location: about.php');
    exit;
}

$about = get_about();

admin_layout_start('about', 'About Section', true);
?>
<p style="margin-bottom:18px;" class="muted">The "About Us" block on the homepage.</p>

<form class="card" method="post">
  <?= csrf_field() ?>
  <div class="field">
    <label for="about_html">About text</label>
    <textarea id="about_html" name="about_html" class="wysiwyg"><?= e($about['html']) ?></textarea>
  </div>
  <div class="field">
    <label for="about_tags">Tags</label>
    <input type="text" id="about_tags" name="about_tags" value="<?= e(implode(', ', $about['tags'])) ?>">
    <div class="hint">Comma-separated chips shown under the text (e.g. Football, Predictions, Arcade).</div>
  </div>
  <div class="form-actions">
    <button type="submit" class="btn btn-primary">Save changes</button>
    <a href="../index.php#about" class="btn" target="_blank">View on site</a>
  </div>
</form>

<script>
  tinymce.init({
    selector: 'textarea.wysiwyg',
    skin: 'oxide-dark',
    content_css: 'dark',
    height: 320,
    menubar: false,
    plugins: 'link lists code',
    toolbar: 'undo redo | bold italic | bullist numlist | link | code',
  });
</script>
<?php
admin_layout_end();
