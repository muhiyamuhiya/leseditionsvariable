-- =============================================================================
-- Migration 010 — Historique des envois emails (email_log)
-- =============================================================================
-- Chaque envoi réel (success + error) déclenché par Mailer::send() est tracé
-- ici. Les "skips" applicatifs (ex: helper drip qui retourne false avant
-- l'envoi) ne sont PAS loggés ici — ils sont visibles dans
-- email_user_progress.last_send_result.
-- =============================================================================

CREATE TABLE IF NOT EXISTS email_log (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED DEFAULT NULL,
    to_email VARCHAR(255) NOT NULL,
    template VARCHAR(80) DEFAULT NULL,
    subject VARCHAR(300),
    sequence_id INT UNSIGNED DEFAULT NULL,
    sequence_step TINYINT UNSIGNED DEFAULT NULL,
    result ENUM('sent', 'error') NOT NULL DEFAULT 'sent',
    error_message TEXT DEFAULT NULL,
    provider_id VARCHAR(100) DEFAULT NULL,
    sent_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_user (user_id),
    INDEX idx_template (template),
    INDEX idx_sent_at (sent_at),
    INDEX idx_result (result),
    INDEX idx_sequence (sequence_id, sequence_step),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (sequence_id) REFERENCES email_sequences(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
