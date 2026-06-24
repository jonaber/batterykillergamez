<?php
/**
 * Public renderer for CMS pages: /page.php?slug=privacy-policy
 */

require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/content.php';

$slug = isset($_GET['slug']) ? trim((string) $_GET['slug']) : '';
$page = $slug !== '' ? get_page_by_slug($slug) : null;

if (!$page) {
    http_response_code(404);
    $pageTitle = 'Page Not Found — Battery Killer Gamez';
} else {
    $pageTitle = $page['title'] . ' — Battery Killer Gamez';
}

include __DIR__ . '/includes/header.php';
?>

<section class="page-wrap">
<?php if (!$page): ?>
  <div class="page-inner">
    <span class="section-eyebrow">Error 404</span>
    <h1 class="page-title">Page Not Found</h1>
    <div class="page-content">
      <p>Sorry, the page you're looking for doesn't exist or isn't published.</p>
      <p><a href="index.php">← Back to the homepage</a></p>
    </div>
  </div>
<?php else: ?>
  <div class="page-inner">
    <span class="section-eyebrow">Battery Killer Gamez</span>
    <h1 class="page-title"><?= e($page['title']) ?></h1>
    <div class="page-content">
      <?= $page['body'] /* trusted HTML, admin-managed */ ?>
    </div>
  </div>
<?php endif; ?>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>
