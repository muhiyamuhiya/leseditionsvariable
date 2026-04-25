-- =============================================================================
-- Migration 006 — Newsletter subscribers
-- =============================================================================

CREATE TABLE IF NOT EXISTS newsletter_subscribers (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255) UNIQUE NOT NULL,
    prenom VARCHAR(100) DEFAULT NULL,
    confirmed_at DATETIME DEFAULT NULL,
    unsubscribed_at DATETIME DEFAULT NULL,
    confirmation_token VARCHAR(64) DEFAULT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_email (email),
    INDEX idx_confirmed (confirmed_at)
) ENGINE=InnoDB;
