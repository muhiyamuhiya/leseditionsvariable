-- =============================================================================
-- Migration 014 — Taux de conversion USD vers XAF (FCFA Centrale) et GNF
-- (Franc guinéen) pour Money Fusion multi-pays.
-- =============================================================================
-- Variable cible toute l'Afrique francophone via Money Fusion. Selon le pays
-- de l'utilisateur (users.pays), on envoie le montant à MF dans la bonne
-- devise + on précise le champ `currency` dans le payload :
--
--   CD                       → USD       (tranche 1-3000 USD chez MF)
--   CI / SN / ML / BF / BJ / TG → XOF       (tranche 100-1.5M XOF)
--   CM                       → XAF       (tranche 100-1.5M XAF)
--   GN                       → GNF       (tranche 1000-15M GNF)
--   Diaspora / autre         → USD       (fallback, le widget MF affiche les
--                                          opérateurs USD disponibles)
--
-- Les taux sont stockés en settings (configurables via /admin/parametres).
-- Idempotent : INSERT IGNORE.
-- =============================================================================

INSERT IGNORE INTO settings (`key`, `value`, description) VALUES
('taux_conversion_usd_xaf', '750',  'Taux USD vers FCFA Centrale (XAF) — Money Fusion Cameroun'),
('taux_conversion_usd_gnf', '8500', 'Taux USD vers Franc guinéen (GNF) — Money Fusion Guinée');
