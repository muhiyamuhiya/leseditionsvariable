-- =============================================================================
-- Migration 009 — Système d'automation emails (séquences + cron)
-- =============================================================================
-- 3 tables :
--   email_sequences      → catalogue de séquences (welcome_drip, etc.)
--   email_sequence_steps → étapes d'une séquence (template + day_offset)
--   email_user_progress  → progression d'un user dans une séquence
--
-- Le cron app/jobs/SendScheduledEmails.php boucle sur email_user_progress
-- (status='running' AND next_send_at <= NOW()) et dispatche les helpers Mailer.
--
-- Seed : la séquence "welcome_drip" est créée avec ses 5 étapes (J0/J2/J7/J14/J30).
-- =============================================================================

CREATE TABLE IF NOT EXISTS email_sequences (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    slug VARCHAR(80) UNIQUE NOT NULL,
    name VARCHAR(160) NOT NULL,
    description TEXT,
    active TINYINT(1) NOT NULL DEFAULT 1,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_active (active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS email_sequence_steps (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    sequence_id INT UNSIGNED NOT NULL,
    sort_order TINYINT UNSIGNED NOT NULL,
    day_offset SMALLINT UNSIGNED NOT NULL COMMENT 'Nombre de jours après inscription',
    template VARCHAR(80) NOT NULL COMMENT 'Slug du template (ex: drip_day2) — doit correspondre à un fichier app/views/emails/{template}.php',
    subject VARCHAR(200) DEFAULT NULL COMMENT 'Sujet override (sinon le helper choisit)',
    conditions JSON DEFAULT NULL COMMENT 'Conditions optionnelles à évaluer côté cron (ex: skip flags). La skip logic principale est dans les helpers Mailer.',
    UNIQUE KEY uniq_sequence_order (sequence_id, sort_order),
    INDEX idx_sequence (sequence_id),
    FOREIGN KEY (sequence_id) REFERENCES email_sequences(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS email_user_progress (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL,
    sequence_id INT UNSIGNED NOT NULL,
    current_step TINYINT UNSIGNED NOT NULL DEFAULT 1,
    next_send_at DATETIME DEFAULT NULL,
    last_sent_at DATETIME DEFAULT NULL,
    last_send_result ENUM('sent', 'skipped', 'error') DEFAULT NULL,
    last_send_error TEXT DEFAULT NULL,
    completed_at DATETIME DEFAULT NULL,
    status ENUM('running', 'completed', 'paused', 'cancelled') NOT NULL DEFAULT 'running',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uniq_user_sequence (user_id, sequence_id),
    INDEX idx_status_next (status, next_send_at),
    INDEX idx_user (user_id),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (sequence_id) REFERENCES email_sequences(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =============================================================================
-- Seed : séquence d'onboarding "welcome_drip"
-- =============================================================================

INSERT INTO email_sequences (slug, name, description, active) VALUES
('welcome_drip', 'Onboarding nouveaux inscrits', 'Séquence J+0 à J+30 pour activer les nouveaux comptes après vérification email.', 1);

SET @seq_id = LAST_INSERT_ID();

INSERT INTO email_sequence_steps (sequence_id, sort_order, day_offset, template, subject) VALUES
(@seq_id, 1, 0,  'welcome',     'Bienvenue sur Variable !'),
(@seq_id, 2, 2,  'drip_day2',   '3 livres qui marchent fort sur Variable'),
(@seq_id, 3, 7,  'drip_day7',   "Et si tu lisais sans limite pour 3$/mois ?"),
(@seq_id, 4, 14, 'drip_day14',  'Les nouveautés Variable'),
(@seq_id, 5, 30, 'drip_day30',  "On t'a oublié ? Tiens, -20% pour revenir.");
