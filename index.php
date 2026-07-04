<?php
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/content.php';
$data = require __DIR__ . '/includes/data.php';

$pageTitle = 'Battery Killer Gamez';

// Editable content from the database.
$slides = get_slides();
$games  = get_games();
$about  = get_about();

// Flash data from the contact handler (PRG pattern).
$errors = take_flash('errors', []);
$sent   = (bool) take_flash('sent', false);
$old    = take_flash('old', []);

// Build the "Related Game" options from the games list.
$gameOptions = array_map(fn($g) => $g['title'], $games);
$gameOptions[] = 'General / All Games';

include __DIR__ . '/includes/header.php';
?>

<!-- HERO SCREEN: hero + ticker + stats fill one viewport -->
<div class="hero-screen">

<!-- HERO CAROUSEL -->
<section class="hero">
  <div class="carousel-track" id="carousel">
<?php foreach ($slides as $i => $slide): ?>
    <div class="slide<?= $i === 0 ? ' active' : '' ?>">
      <div class="slide-bg" style="background-image: url('<?= e($slide['img']) ?>')"></div>
      <div class="slide-content">
        <span class="slide-tag"><?= e($slide['tag']) ?></span>
        <h1 class="slide-title"><?= $slide['title'] /* trusted markup, admin-managed */ ?></h1>
        <p class="slide-desc"><?= e($slide['description']) ?></p>
        <a href="#games" class="slide-btn"><?= e($slide['btn_label']) ?></a>
      </div>
    </div>
<?php endforeach; ?>
  </div>

  <button class="carousel-arrow prev" onclick="shiftSlide(-1)">&#8592;</button>
  <button class="carousel-arrow next" onclick="shiftSlide(1)">&#8594;</button>

  <div class="carousel-dots" id="dots"></div>
</section>

<!-- TICKER -->
<div class="ticker-wrap">
  <div class="ticker">
<?php // Print the ticker items twice for a seamless loop.
    foreach ([1, 2] as $pass):
        foreach ($data['ticker'] as $item): ?>
    <span class="ticker-item"><?= e($item) ?></span>
<?php   endforeach;
    endforeach; ?>
  </div>
</div>

<!-- STATS -->
<div class="stats-bar">
<?php foreach ($data['stats'] as $stat): ?>
  <div class="stat-item reveal">
    <span class="stat-num" data-target="<?= e((string) $stat['target']) ?>">0</span>
    <div class="stat-label"><?= e($stat['label']) ?></div>
  </div>
<?php endforeach; ?>
</div>

</div><!-- /.hero-screen -->

<!-- GAMES -->
<section id="games">
  <div class="section-header reveal">
    <span class="section-eyebrow">Our Portfolio</span>
    <h2 class="section-title">The Games</h2>
  </div>

  <div class="games-grid">
<?php foreach ($games as $game): ?>
    <div class="game-card reveal">
      <div class="game-card-bg" style="background-image: url('<?= e($game['img']) ?>')"></div>
      <div class="game-card-overlay"></div>
      <a href="#contact" class="game-play-btn">Play Now ▶</a>
      <div class="game-card-content">
        <span class="game-genre"><?= e($game['genre']) ?></span>
        <h3 class="game-title"><?= e($game['title']) ?></h3>
        <div class="game-meta">
          <span class="game-rating"><?= e($game['rating']) ?></span>
<?php foreach (array_filter(array_map('trim', explode(',', (string) $game['meta']))) as $meta): ?>
          <span><?= e($meta) ?></span>
<?php endforeach; ?>
        </div>
      </div>
    </div>
<?php endforeach; ?>

    <div class="game-card reveal" style="background: linear-gradient(135deg,#0a0a0a,#111); display:flex; align-items:center; justify-content:center; flex-direction:column; gap:12px; border: 1px dashed #222;">
      <div style="font-size:42px;">⚡</div>
      <p style="font-family:'Orbitron',monospace; font-size:11px; letter-spacing:0.2em; color:var(--grey); text-align:center; text-transform:uppercase;">More Titles<br>Coming Soon</p>
    </div>
  </div>
</section>

<!-- ABOUT -->
<section id="about">
  <div class="about-inner">
    <div class="about-logo-wrap reveal">
      <img src="assets/img/logo.jpeg" alt="Battery Killer Gamez" onerror="this.style.display='none'">
    </div>
    <div class="about-text">
      <div class="section-header reveal">
        <span class="section-eyebrow">Our Story</span>
        <h2 class="section-title">About Us</h2>
      </div>
      <div class="about-content reveal">
        <?= $about['html'] /* trusted HTML, admin-managed */ ?>
      </div>
      <div class="about-tags reveal">
<?php foreach ($about['tags'] as $tag): ?>
        <span class="about-tag"><?= e($tag) ?></span>
<?php endforeach; ?>
      </div>
    </div>
  </div>
</section>

<!-- CONTACT -->
<section id="contact">
  <div class="section-header reveal">
    <span class="section-eyebrow">Get In Touch</span>
    <h2 class="section-title">Contact Us</h2>
  </div>

  <div class="contact-inner">
    <div id="contact-form-wrap">
<?php if ($errors): ?>
      <div class="form-errors" style="border:1px solid var(--red); background:rgba(139,0,0,0.12); color:#ffb3b3; padding:14px 18px; margin-bottom:22px; font-family:'Share Tech Mono',monospace; font-size:14px;">
        <strong>⚠ Please fix the following:</strong>
        <ul style="margin:8px 0 0 18px;">
<?php foreach ($errors as $err): ?>
          <li><?= e($err) ?></li>
<?php endforeach; ?>
        </ul>
      </div>
<?php endif; ?>

      <form action="contact.php" method="post"<?= $sent ? ' style="display:none;"' : '' ?>>
        <div class="contact-grid">
          <div class="form-group reveal">
            <label for="name">Your Name</label>
            <input type="text" id="name" name="name" placeholder="John Doe" value="<?= e($old['name'] ?? '') ?>" required>
          </div>
          <div class="form-group reveal">
            <label for="email">Email Address</label>
            <input type="email" id="email" name="email" placeholder="you@example.com" value="<?= e($old['email'] ?? '') ?>" required>
          </div>
          <div class="form-group reveal">
            <label for="subject">Subject</label>
            <select id="subject" name="subject" required>
              <option value="">Select a topic</option>
<?php foreach ($data['subjects'] as $subject): ?>
              <option<?= (($old['subject'] ?? '') === $subject) ? ' selected' : '' ?>><?= e($subject) ?></option>
<?php endforeach; ?>
            </select>
          </div>
          <div class="form-group reveal">
            <label for="game">Related Game</label>
            <select id="game" name="game" required>
              <option value="">Select a game</option>
<?php foreach ($gameOptions as $opt): ?>
              <option<?= (($old['game'] ?? '') === $opt) ? ' selected' : '' ?>><?= e($opt) ?></option>
<?php endforeach; ?>
            </select>
          </div>
          <div class="form-group full reveal">
            <label for="message">Message</label>
            <textarea id="message" name="message" placeholder="Tell us what's on your mind..." required><?= e($old['message'] ?? '') ?></textarea>
          </div>
        </div>
        <button type="submit" class="submit-btn">
          ⚡ Send Message
        </button>
      </form>
      <div class="form-success<?= $sent ? ' show' : '' ?>" id="form-success">
        <p>⚡ MESSAGE RECEIVED — WE'LL BE IN TOUCH SOON.</p>
      </div>
    </div>

    <div class="contact-info">
      <div class="contact-info-card reveal">
        <span class="contact-info-icon">🎮</span>
        <span class="contact-info-label">Support</span>
        <span class="contact-info-value">support@batterykillergamez.com</span>
      </div>
      <div class="contact-info-card reveal">
        <span class="contact-info-icon">🤝</span>
        <span class="contact-info-label">Partnerships</span>
        <span class="contact-info-value">biz@batterykillergamez.com</span>
      </div>
      <div class="contact-info-card reveal">
        <span class="contact-info-icon">📱</span>
        <span class="contact-info-label">Social</span>
        <span class="contact-info-value">@BatteryKillerGZ</span>
      </div>
    </div>
  </div>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>
