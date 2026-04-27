-- =============================================================================
-- Migration 013 — Taux de conversion USD → XOF (FCFA Ouest) pour Money Fusion
-- =============================================================================
-- Money Fusion travaille en FCFA (XOF), pas en USD. Variable affiche les prix
-- en USD côté utilisateur, mais doit convertir AVANT d'envoyer le payload à
-- l'API MF — sinon le montant brut (ex: 2 USD → MF interprète "2") tombe
-- sous le minimum 200 F et l'API rejette avec "Montant doit être supérieur
-- à 200 F".
--
-- Le taux est stocké en setting (et non en constante) pour pouvoir l'ajuster
-- sans toucher au code en cas de fluctuation marquée. Valeur stable retenue
-- par Variable : 750 XOF / USD.
--
-- Idempotent : INSERT IGNORE skip si la clé existe déjà.
-- =============================================================================

INSERT IGNORE INTO settings (`key`, `value`, description) VALUES
('taux_conversion_usd_xof', '750', 'Taux USD vers FCFA Ouest (XOF) — utilisé pour les paiements Money Fusion');
