<?php
require_once __DIR__ . '/../includes/auth.php';
require_admin();
require_once __DIR__ . '/_layout.php';

$db = get_db();

$id   = (int) ($_GET['id'] ?? 0);
$game = ['id' => 0, 'genre' => '', 'title' => '', 'rating' => '', 'meta' => '', 'img' => '', 'sort_order' => 0];

if ($id) {
    $stmt = $db->prepare('SELECT * FROM games WHERE id = :id');
    $stmt->execute([':id' => $id]);
    $found = $stmt->fetch();
    if (!$found) {
        flash('Game not found.', 'error');
        header('Location: games.php');
        exit;
    }
    $game = $found;
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();

    $game['genre']      = trim($_POST['genre'] ?? '');
    $game['title']      = trim($_POST['title'] ?? '');
    $game['rating']     = trim($_POST['rating'] ?? '');
    $game['meta']       = trim($_POST['meta'] ?? '');
    $game['sort_order'] = (int) ($_POST['sort_order'] ?? 0);

    if ($game['title'] === '') {
        $errors[] = 'Title is required.';
    }
    if ($game['genre'] === '') {
        $errors[] = 'Genre is required.';
    }

    // Optional image upload (keeps existing image if none provided).
    try {
        $uploaded = save_uploaded_image('image');
        if ($uploaded !== null) {
            $game['img'] = $uploaded;
        }
    } catch (Throwable $ex) {
        $errors[] = $ex->getMessage();
    }

    if (!$errors) {
        if ($id) {
            $stmt = $db->prepare(
                'UPDATE games SET genre=:genre, title=:title, rating=:rating, meta=:meta,
                 img=:img, sort_order=:sort WHERE id=:id'
            );
            $stmt->execute([
                ':genre' => $game['genre'], ':title' => $game['title'], ':rating' => $game['rating'],
                ':meta' => $game['meta'], ':img' => $game['img'], ':sort' => $game['sort_order'], ':id' => $id,
            ]);
            flash('Game updated.');
        } else {
            $stmt = $db->prepare(
                'INSERT INTO games (genre, title, rating, meta, img, sort_order)
                 VALUES (:genre, :title, :rating, :meta, :img, :sort)'
            );
            $stmt->execute([
                ':genre' => $game['genre'], ':title' => $game['title'], ':rating' => $game['rating'],
                ':meta' => $game['meta'], ':img' => $game['img'], ':sort' => $game['sort_order'],
            ]);
            flash('Game created.');
        }
        header('Location: games.php');
        exit;
    }
}

admin_layout_start('games', $id ? 'Edit Game' : 'New Game');
?>
<p style="margin-bottom:18px;"><a href="games.php">← Back to games</a></p>

<?php if ($errors): ?>
<div class="flash flash-error">
  <?php foreach ($errors as $err): ?><div><?= e($err) ?></div><?php endforeach; ?>
</div>
<?php endif; ?>

<form class="card" method="post" enctype="multipart/form-data">
  <?= csrf_field() ?>
  <div class="field-row">
    <div class="field">
      <label for="title">Title</label>
      <input type="text" id="title" name="title" value="<?= e($game['title']) ?>" required>
    </div>
    <div class="field">
      <label for="genre">Genre</label>
      <input type="text" id="genre" name="genre" value="<?= e($game['genre']) ?>" required>
    </div>
  </div>
  <div class="field-row">
    <div class="field">
      <label for="rating">Rating</label>
      <input type="text" id="rating" name="rating" value="<?= e($game['rating']) ?>" placeholder="4.9">
    </div>
    <div class="field">
      <label for="meta">Meta tags</label>
      <input type="text" id="meta" name="meta" value="<?= e($game['meta']) ?>" placeholder="2–8 Players, Free to Play">
      <div class="hint">Comma-separated; shown next to the rating.</div>
    </div>
    <div class="field">
      <label for="sort_order">Order</label>
      <input type="number" id="sort_order" name="sort_order" value="<?= (int) $game['sort_order'] ?>">
    </div>
  </div>
  <div class="field">
    <label>Cover image</label>
<?php if ($game['img']): ?>
    <div style="margin-bottom:10px;"><img class="thumb" style="width:120px;height:74px;" src="../<?= e($game['img']) ?>" alt=""></div>
<?php endif; ?>
    <input type="file" name="image" accept="image/*">
    <div class="hint">Leave empty to keep the current image. JPG/PNG/GIF/WEBP, max 5 MB.</div>
  </div>
  <div class="form-actions">
    <button type="submit" class="btn btn-primary"><?= $id ? 'Save changes' : 'Create game' ?></button>
    <a href="games.php" class="btn">Cancel</a>
  </div>
</form>
<?php
admin_layout_end();
