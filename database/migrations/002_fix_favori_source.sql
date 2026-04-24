-- =============================================================================
-- Migration 002 — Correction faille sécurité critique sur les favoris
-- =============================================================================
--
-- Contexte : avant le patch, BookController@toggleFavorite insérait des lignes
-- user_books avec source='achat_unitaire' pour des livres NON achetés. Comme
-- BookAccess::canReadFull() autorise la lecture sur cette source, n'importe
-- quel utilisateur pouvait débloquer la lecture complète gratuitement en
-- cliquant simplement sur le cœur "favori" depuis la page d'un livre.
--
-- Cette migration :
--   1. Étend l'ENUM `source` pour accepter la valeur 'favori'
--   2. Reclasse les lignes corrompues en 'favori' quand elles sont actives
--      (favori = 1) et qu'aucune transaction réussie n'existe
--   3. Supprime les lignes orphelines (favori = 0 + source='achat_unitaire'
--      + aucune transaction) qui sont des résidus du toggle on/off avec le
--      bug, et qui continuaient à donner accès à la lecture
--
-- À exécuter manuellement sur les environnements existants. Les nouveaux
-- environnements créés via 001_initial.sql ont déjà l'ENUM corrigé.
-- =============================================================================

-- 1) Étendre l'ENUM source pour accepter 'favori'
ALTER TABLE user_books
    MODIFY COLUMN source ENUM('achat_unitaire', 'abonnement', 'favori') NOT NULL;

-- 2) Reclasser les lignes "favori bug" actives en source='favori'
--    Les rows "achat_unitaire" qui n'ont pas de transaction réussie associée
--    et qui sont marquées favori=1 sont des inserts du bug toggleFavorite.
UPDATE user_books ub
LEFT JOIN transactions_log tl
    ON tl.user_id = ub.user_id
    AND tl.reference_id = ub.book_id
    AND tl.reference_type = 'books'
    AND tl.statut = 'reussi'
SET ub.source = 'favori'
WHERE ub.source = 'achat_unitaire'
  AND ub.favori = 1
  AND tl.id IS NULL;

-- 3) Supprimer les lignes orphelines (toggle off avec le bug)
--    favori=0 + source='achat_unitaire' + aucune transaction → la ligne ne
--    sert plus à rien et continue à donner accès lecture : on la supprime.
DELETE ub FROM user_books ub
LEFT JOIN transactions_log tl
    ON tl.user_id = ub.user_id
    AND tl.reference_id = ub.book_id
    AND tl.reference_type = 'books'
    AND tl.statut = 'reussi'
WHERE ub.source = 'achat_unitaire'
  AND ub.favori = 0
  AND tl.id IS NULL;
