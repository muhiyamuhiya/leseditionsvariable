-- =============================================================================
-- Migration 008 — Codes promo uniques (par utilisateur)
-- =============================================================================
-- Table simple utilisée pour les drip campaigns (ex: J+30 réactivation -20%)
-- et tout futur usage marketing. Chaque code est unique GLOBALEMENT et lié
-- (optionnellement) à un user_id pour les codes personnels.
--
-- La validation au checkout (Stripe / Money Fusion) est encore TODO — pour
-- l'instant on génère + stocke + affiche le code dans l'email. L'usage réel
-- sera implémenté quand on greffera la mécanique sur PaymentController.
-- =============================================================================

CREATE TABLE IF NOT EXISTS promo_codes (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    code VARCHAR(40) UNIQUE NOT NULL,
    user_id INT UNSIGNED DEFAULT NULL,
    discount_pct TINYINT UNSIGNED NOT NULL DEFAULT 20,
    max_uses INT UNSIGNED DEFAULT 1,
    times_used INT UNSIGNED NOT NULL DEFAULT 0,
    valid_from DATETIME DEFAULT CURRENT_TIMESTAMP,
    valid_until DATETIME DEFAULT NULL,
    source VARCHAR(50) DEFAULT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    used_at DATETIME DEFAULT NULL,
    INDEX idx_code (code),
    INDEX idx_user (user_id),
    INDEX idx_source (source),
    INDEX idx_valid (valid_until),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
