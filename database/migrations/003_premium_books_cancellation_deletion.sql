-- =============================================================================
-- Migration 003 — Modèle 2 niveaux + annulation abo + suppression compte (RGPD)
-- =============================================================================

-- -----------------------------------------------------------------------------
-- VOLET 1 — Books : split accès Essentiel / Premium
-- -----------------------------------------------------------------------------
ALTER TABLE books
    CHANGE accessible_abonnement accessible_abonnement_essentiel BOOLEAN DEFAULT TRUE;

ALTER TABLE books
    ADD COLUMN accessible_abonnement_premium BOOLEAN DEFAULT TRUE
    AFTER accessible_abonnement_essentiel;

-- Par défaut, tous les livres existants restent inclus dans les deux formules
UPDATE books SET accessible_abonnement_essentiel = 1, accessible_abonnement_premium = 1
WHERE accessible_abonnement_essentiel IS NULL OR accessible_abonnement_premium IS NULL;

-- -----------------------------------------------------------------------------
-- VOLET 1 — Subscriptions : ENUM type → ajouter essentiel_mensuel / essentiel_annuel
-- -----------------------------------------------------------------------------
ALTER TABLE subscriptions
    MODIFY COLUMN type ENUM(
        'mensuel', 'annuel',
        'essentiel_mensuel', 'essentiel_annuel',
        'premium_mensuel', 'premium_annuel'
    ) NOT NULL;

-- Migrer les anciennes lignes vers la nouvelle nomenclature
UPDATE subscriptions SET type = 'essentiel_mensuel' WHERE type = 'mensuel';
UPDATE subscriptions SET type = 'essentiel_annuel'  WHERE type = 'annuel';

-- Nettoyer l'ENUM (drop des anciennes valeurs maintenant qu'aucune ligne ne les utilise)
ALTER TABLE subscriptions
    MODIFY COLUMN type ENUM(
        'essentiel_mensuel', 'essentiel_annuel',
        'premium_mensuel', 'premium_annuel'
    ) NOT NULL;

-- -----------------------------------------------------------------------------
-- VOLET 2 — Annulation : ajouter motif structuré (raison libre déjà en place)
-- -----------------------------------------------------------------------------
ALTER TABLE subscriptions
    ADD COLUMN motif_annulation VARCHAR(50) DEFAULT NULL AFTER raison_annulation;

-- -----------------------------------------------------------------------------
-- VOLET 3 — Users : statut soft-delete + deleted_at
-- -----------------------------------------------------------------------------
ALTER TABLE users
    ADD COLUMN statut VARCHAR(20) DEFAULT 'actif' AFTER role;

ALTER TABLE users
    ADD COLUMN deleted_at DATETIME DEFAULT NULL AFTER updated_at;

CREATE INDEX idx_users_statut ON users(statut);

-- -----------------------------------------------------------------------------
-- VOLET 3 — Tokens de demande de suppression (24h validity)
-- -----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS user_deletion_tokens (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL,
    token VARCHAR(64) UNIQUE NOT NULL,
    expire_at DATETIME NOT NULL,
    used TINYINT(1) DEFAULT 0,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_token (token),
    INDEX idx_user (user_id),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;
