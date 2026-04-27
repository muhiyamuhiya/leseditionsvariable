-- =============================================================================
-- Migration 016 — Intégration CinetPay (RDC USD natif)
-- =============================================================================
-- CinetPay traite les paiements OMCDUSD (Orange Money), AIRTELCDUSD (Airtel),
-- MPESACDUSD (M-Pesa) et VISAMCDUSD (Visa/Master) directement en USD pour la
-- RDC, tranche 1-3000 USD. Pas de conversion de devise (contrairement à Money
-- Fusion). Cette migration ajoute :
--
--   1. Setting `cinetpay_active` (toggle ON/OFF côté admin sans toucher au .env).
--      Désactivé par défaut (=0) tant que les clés API ne sont pas validées.
--   2. Colonne `sales.cinetpay_transaction_id` pour tracer l'ID CinetPay
--      (préfixe LV-<unix_ms>-<rand>) sur les achats de livres.
--   3. Colonne `subscriptions.cinetpay_transaction_id` (idem pour les abos).
--
-- Idempotent : INSERT IGNORE sur settings, INFORMATION_SCHEMA + PREPARE pour
-- les ALTER (pattern compatible MySQL 8 sans IF NOT EXISTS sur ADD COLUMN
-- universel). Rejouable sans erreur.
-- =============================================================================

-- 1. Toggle d'activation côté admin
INSERT IGNORE INTO settings (`key`, `value`, description) VALUES
('cinetpay_active', '0', 'Active CinetPay (Mobile Money + carte RDC). Mettre à 1 une fois les clés API validées.');

-- 2. sales.cinetpay_transaction_id
SET @col_exists := (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME   = 'sales'
      AND COLUMN_NAME  = 'cinetpay_transaction_id'
);
SET @sql := IF(@col_exists = 0,
    'ALTER TABLE sales ADD COLUMN cinetpay_transaction_id VARCHAR(64) NULL AFTER transaction_id',
    'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- Index pour le webhook (lookup par transaction_id)
SET @idx_exists := (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.STATISTICS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME   = 'sales'
      AND INDEX_NAME   = 'idx_sales_cinetpay_tx'
);
SET @sql := IF(@idx_exists = 0,
    'CREATE INDEX idx_sales_cinetpay_tx ON sales (cinetpay_transaction_id)',
    'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- 3. subscriptions.cinetpay_transaction_id
SET @col_exists := (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME   = 'subscriptions'
      AND COLUMN_NAME  = 'cinetpay_transaction_id'
);
SET @sql := IF(@col_exists = 0,
    'ALTER TABLE subscriptions ADD COLUMN cinetpay_transaction_id VARCHAR(64) NULL',
    'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @idx_exists := (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.STATISTICS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME   = 'subscriptions'
      AND INDEX_NAME   = 'idx_subs_cinetpay_tx'
);
SET @sql := IF(@idx_exists = 0,
    'CREATE INDEX idx_subs_cinetpay_tx ON subscriptions (cinetpay_transaction_id)',
    'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;
