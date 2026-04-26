-- =============================================================================
-- Migration 012 — Versements aux auteurs : flux "sur demande" + 70/30
-- =============================================================================
-- Adaptations pour le sprint Versements :
--   1. Settings business : commission plateforme 30%, seuil minimum 10$
--      (avant : 20% commission, 20$ seuil)
--   2. author_payouts : nouvelles colonnes pour tracer la demande de
--      versement (snapshot des coords bancaires au moment de la demande,
--      qui a traité, raison du refus le cas échéant)
--   3. Statut enum étendu : 'available' (crédité, prêt à demander),
--      'requested' (auteur a demandé), 'refuse' (admin a refusé) en plus
--      des existants (calcule, a_verser, en_cours, verse, echec, annule)
--
-- Idempotence :
--   - settings : UPDATE rejouable
--   - ALTER ADD COLUMN : guard via INFORMATION_SCHEMA + PREPARE
--   - ALTER MODIFY COLUMN (enum) : rejouable
-- =============================================================================

-- 1. Settings business
UPDATE settings SET `value` = '30' WHERE `key` = 'commission_variable_pct';
UPDATE settings SET `value` = '10' WHERE `key` = 'seuil_minimum_versement_usd';

-- 2. Colonnes author_payouts (idempotent)
SET @col := (SELECT COUNT(*) FROM information_schema.columns WHERE table_schema=DATABASE() AND table_name='author_payouts' AND column_name='requested_at');
SET @s := IF(@col=0, 'ALTER TABLE author_payouts ADD COLUMN requested_at DATETIME NULL AFTER notes', 'DO 0');
PREPARE q FROM @s; EXECUTE q; DEALLOCATE PREPARE q;

SET @col := (SELECT COUNT(*) FROM information_schema.columns WHERE table_schema=DATABASE() AND table_name='author_payouts' AND column_name='requested_method');
SET @s := IF(@col=0, 'ALTER TABLE author_payouts ADD COLUMN requested_method VARCHAR(50) NULL AFTER requested_at', 'DO 0');
PREPARE q FROM @s; EXECUTE q; DEALLOCATE PREPARE q;

SET @col := (SELECT COUNT(*) FROM information_schema.columns WHERE table_schema=DATABASE() AND table_name='author_payouts' AND column_name='requested_account_snapshot');
SET @s := IF(@col=0, 'ALTER TABLE author_payouts ADD COLUMN requested_account_snapshot VARCHAR(255) NULL AFTER requested_method', 'DO 0');
PREPARE q FROM @s; EXECUTE q; DEALLOCATE PREPARE q;

SET @col := (SELECT COUNT(*) FROM information_schema.columns WHERE table_schema=DATABASE() AND table_name='author_payouts' AND column_name='processed_by_admin_id');
SET @s := IF(@col=0, 'ALTER TABLE author_payouts ADD COLUMN processed_by_admin_id INT UNSIGNED NULL AFTER requested_account_snapshot, ADD INDEX idx_processed_by (processed_by_admin_id), ADD CONSTRAINT fk_payout_admin FOREIGN KEY (processed_by_admin_id) REFERENCES users(id) ON DELETE SET NULL', 'DO 0');
PREPARE q FROM @s; EXECUTE q; DEALLOCATE PREPARE q;

SET @col := (SELECT COUNT(*) FROM information_schema.columns WHERE table_schema=DATABASE() AND table_name='author_payouts' AND column_name='rejection_reason');
SET @s := IF(@col=0, 'ALTER TABLE author_payouts ADD COLUMN rejection_reason TEXT NULL AFTER processed_by_admin_id', 'DO 0');
PREPARE q FROM @s; EXECUTE q; DEALLOCATE PREPARE q;

-- 3. Statut enum étendu (rejouable — MODIFY repose la même définition)
ALTER TABLE author_payouts
  MODIFY COLUMN statut ENUM('calcule','available','a_verser','requested','en_cours','verse','echec','annule','refuse')
  DEFAULT 'calcule';
