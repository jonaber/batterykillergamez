-- Schema for Battery Killer Gamez.
-- Applied automatically by includes/db.php on first DB use (each statement is
-- run separately, all IF NOT EXISTS, so it's safe to run repeatedly).

CREATE TABLE IF NOT EXISTS contact_messages (
    id          INT UNSIGNED NOT NULL AUTO_INCREMENT,
    name        VARCHAR(120)  NOT NULL,
    email       VARCHAR(190)  NOT NULL,
    subject     VARCHAR(120)  DEFAULT NULL,
    game        VARCHAR(120)  DEFAULT NULL,
    message     TEXT          NOT NULL,
    ip_address  VARCHAR(45)   DEFAULT NULL,
    created_at  TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Standalone CMS pages (Privacy, News, etc.)
CREATE TABLE IF NOT EXISTS pages (
    id          INT UNSIGNED NOT NULL AUTO_INCREMENT,
    slug        VARCHAR(160)  NOT NULL,
    title       VARCHAR(200)  NOT NULL,
    body        MEDIUMTEXT    NOT NULL,
    in_menu     TINYINT(1)    NOT NULL DEFAULT 1,
    status      ENUM('published','draft') NOT NULL DEFAULT 'published',
    sort_order  INT           NOT NULL DEFAULT 0,
    created_at  TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at  TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uniq_slug (slug)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Homepage games grid
CREATE TABLE IF NOT EXISTS games (
    id          INT UNSIGNED NOT NULL AUTO_INCREMENT,
    genre       VARCHAR(120)  NOT NULL,
    title       VARCHAR(160)  NOT NULL,
    rating      VARCHAR(10)   DEFAULT NULL,
    meta        VARCHAR(255)  DEFAULT NULL,  -- comma-separated tags
    img         VARCHAR(255)  DEFAULT NULL,
    sort_order  INT           NOT NULL DEFAULT 0,
    PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Homepage hero carousel slides
CREATE TABLE IF NOT EXISTS slides (
    id          INT UNSIGNED NOT NULL AUTO_INCREMENT,
    tag         VARCHAR(120)  DEFAULT NULL,
    title       VARCHAR(255)  NOT NULL,      -- may contain a <span> accent
    description TEXT          DEFAULT NULL,
    btn_label   VARCHAR(120)  DEFAULT NULL,
    img         VARCHAR(255)  DEFAULT NULL,
    sort_order  INT           NOT NULL DEFAULT 0,
    PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Admin users (back office login)
CREATE TABLE IF NOT EXISTS users (
    id          INT UNSIGNED NOT NULL AUTO_INCREMENT,
    username    VARCHAR(80)   NOT NULL,
    password    VARCHAR(255)  NOT NULL,   -- bcrypt hash (password_hash)
    created_at  TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at  TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uniq_username (username)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Simple key/value store (About text, tags, etc.)
CREATE TABLE IF NOT EXISTS settings (
    name        VARCHAR(80)   NOT NULL,
    value       MEDIUMTEXT    DEFAULT NULL,
    PRIMARY KEY (name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
