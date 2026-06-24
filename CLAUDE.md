# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## What this is

Battery Killer Gamez — a small PHP/MySQL marketing site with a database-backed CMS back office. It targets a stock **XAMPP on Windows** install (Apache + MySQL, PHP via PDO). There is **no build step, no package manager, and no test suite** — files are served directly by Apache.

## Running & checking code

- **Run the site:** ensure the project sits at `c:\xampp\htdocs\BatteryKillerGamez`, start Apache + MySQL in the XAMPP control panel, then open `http://localhost/BatteryKillerGamez/index.php`. The database, tables, and seed content are created automatically on the first request — there is no manual DB setup or migration step.
- **Back office:** `http://localhost/BatteryKillerGamez/admin/` (the root `admin.php` just redirects there). First-run login is seeded from `config.php` (`admin` / `changeme`); after that, accounts are managed in Admin → Users.
- **Syntax-check PHP:** `C:\xampp\php\php.exe -l <file>` (use this after editing PHP — it's the only automated check available).
- **DB / admin credentials** are read from a `.env` file in the project root (see `.env.example`) by `config.php`, which falls back to stock-XAMPP defaults (MySQL user `root`, empty password) when a key or the whole `.env` is absent. `config.php` includes a tiny built-in `.env` parser — there is no Composer/dotenv dependency.

## Architecture

### Zero-setup database lifecycle (important)
`get_db()` in `includes/db.php` is the single entry point for the DB and does a lot on first call per request:
1. Connects without selecting a DB, then `CREATE DATABASE IF NOT EXISTS`.
2. `db_install()` runs `db/schema.sql` — every statement is `CREATE TABLE IF NOT EXISTS`, executed one-by-one (PDO can't run multiple statements at once).
3. `db_seed()` populates content **only for tables that are empty**, pulling defaults from `includes/data.php`.

Consequences for making changes:
- **To add/change a table, edit `db/schema.sql`** (idempotent CREATEs). Because `db_install()` runs on every request, new tables appear automatically — there is no separate migration tooling.
- `includes/data.php` is **seed data only**. Once a table is non-empty, editing `data.php` has no effect; content is then edited through the admin and stored in MySQL. `data.php` is *also* used at runtime by `contact.php` to validate the subject/game `<select>` options.

### Request flow
- **Public pages** include `includes/header.php` (nav) and `includes/footer.php`; both pull editable content through `includes/content.php` (`get_slides`, `get_games`, `get_menu_pages`, `get_about`, `get_setting`/`set_setting`, etc.).
  - `index.php` — homepage (hero carousel, games grid, about, contact) rendered by looping over DB content.
  - `page.php?slug=...` — renders a standalone CMS page; 404s if the slug isn't a published page.
  - `contact.php` — form handler using the **Post/Redirect/Get** pattern: validates, stores in `contact_messages`, stashes errors/old input in `$_SESSION`, and redirects back to `index.php#contact`.
- **`includes/functions.php`** is included everywhere: it starts the session and provides `e()` (HTML escaping), flash messages (`flash`/`take_flash`), CSRF helpers, `slugify()`, and `save_uploaded_image()` (writes to `assets/img/uploads/`).

### Authentication & admin
- `includes/auth.php` is DB-backed: logins are checked against the `users` table with `password_hash`/`password_verify`. Sessions hold `is_admin`, `user_id`, `username`.
- **Every protected admin page** starts with `require_once .../auth.php; require_admin();`.
- `admin/_layout.php` provides the chrome via `admin_layout_start($activeNav, $title, $loadEditor)` / `admin_layout_end()`, plus the sidebar nav list in `admin_nav_items()`. Pass `$loadEditor = true` to load TinyMCE (used by Pages and About for rich text).
- **CRUD pages follow a consistent two-file pattern**: a list page (`pages.php`, `games.php`, `slides.php`, `users.php`, `messages.php`) and an editor (`page-edit.php`, `game-edit.php`, `slide-edit.php`, `user-edit.php`) that handles both create and edit by `id`. Copy an existing pair when adding a new managed entity.

### Conventions to follow
- **Every state-changing admin form must call `csrf_check()` on POST and emit `csrf_field()` in the form.** This is uniform across the admin; keep it that way.
- **Trusted HTML:** slide titles, page bodies, and the About block are authored in the admin and rendered **unescaped** (`<?= $page['body'] ?>`) on purpose. All other user/DB output goes through `e()`.
- Image `<img>` tags use `onerror="this.style.display='none'"` so missing files fail silently.

### Frontend assets
- `assets/css/style.css` — public site styles. `admin/admin.css` — back office styles. `assets/js/main.js` — homepage carousel, scroll-reveal (`IntersectionObserver`), and animated stat counters. Carousel control functions (`shiftSlide`/`goTo`) are global because they're invoked from inline `onclick` in `index.php`.

## Gotcha: duplicated standalone mockup

`battery-killer-gamez-2-website.html` at the repo root is a **self-contained static mirror of the homepage** with its own inline `<style>` and `<script>` (it is not served by the PHP app). When changing public-site styling or homepage markup/JS, the same change usually has to be made **in both** `assets/css/style.css` (+ relevant `includes/*.php`) **and** this HTML file to keep them in sync. Note its inline copy is independent — e.g. its contact form uses an inline `handleSubmit()` while the live site posts to `contact.php`.

## Planned changes (for production hardening)

These are intentional future changes, not bugs. The current design favors local-XAMPP "zero setup" convenience; before going to production, revisit:

### Stop running install/seed on every request
Today `get_db()` runs `db_install()` (apply `schema.sql`) + `db_seed()` on the first DB call of **every HTTP request** (PHP is stateless per request). It's idempotent and cheap, but it doesn't belong on the runtime hot path. Target shape:
- **`get_db()` becomes connection-only** — put the database in the DSN (`dbname=...`) and just return the PDO. Remove `CREATE DATABASE`, `db_install()`, and `db_seed()` from the request path.
- **Move install + seed into a one-time setup step** — a `setup.php` (browser, run once) or `php db/install.php` (CLI) that creates the DB, applies `db/schema.sql`, and seeds from `data.php`. `schema.sql` stays the source of truth; it's just invoked once instead of per request.
- **Fail gracefully when not installed** — `get_db()` should catch the "unknown database / missing tables" case and show a clear "run setup first" message instead of a raw PDO error. Do this together with the split, or first-run becomes a confusing stack trace.

### Real migrations once the schema evolves
`CREATE TABLE IF NOT EXISTS` only covers a fresh build; it **cannot alter or add a column to an already-existing table**. The first time an existing table needs to change, introduce versioned migrations (a `schema_migrations` table tracking which numbered `.sql` files have run) rather than relying on the first-setup file.

### Cheaper middle ground (if keeping zero-setup)
If "just drop it in `htdocs` and go" is worth preserving, instead of removing auto-install, gate it behind a one-line check — e.g. a `schema_version` row in `settings` (or a sentinel file) — so the install block is skipped after the first run. Keeps the convenience, pays the cost only once.
