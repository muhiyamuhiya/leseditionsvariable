-- =============================================================================
-- Migration 015 — Indexes composites sur books pour les hot paths catalogue
-- =============================================================================
-- Avec les 49 livres classiques à uploader prochainement, les requêtes du
-- catalogue (findNouveautes, findTendances) utilisent un seul index simple
-- (idx_statut) puis trient à la volée -> filesort sur le résultat.
--
-- Les indexes composites (statut, date_publication) et (statut, total_ventes)
-- permettent à InnoDB de servir le ORDER BY directement depuis l'index, donc
-- LIMIT 10 = ~10 lignes lues au lieu d'un trie complet.
--
-- Idempotent : check via INFORMATION_SCHEMA puis CREATE INDEX uniquement si
-- absent (compatible MySQL 8 sans IF NOT EXISTS sur CREATE INDEX qui n'est pas
-- universellement supporté). Rejouable sans erreur.
-- =============================================================================

-- Index 1 : (statut, date_publication) pour findNouveautes / findByCategory
SET @idx_exists := (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.STATISTICS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME   = 'books'
      AND INDEX_NAME   = 'idx_books_statut_date'
);
SET @sql := IF(@idx_exists = 0,
    'CREATE INDEX idx_books_statut_date ON books (statut, date_publication)',
    'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- Index 2 : (statut, total_ventes) pour findTendances
SET @idx_exists := (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.STATISTICS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME   = 'books'
      AND INDEX_NAME   = 'idx_books_statut_ventes'
);
SET @sql := IF(@idx_exists = 0,
    'CREATE INDEX idx_books_statut_ventes ON books (statut, total_ventes)',
    'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- Index 3 : (statut, mis_en_avant) pour findRecommandes
SET @idx_exists := (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.STATISTICS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME   = 'books'
      AND INDEX_NAME   = 'idx_books_statut_featured'
);
SET @sql := IF(@idx_exists = 0,
    'CREATE INDEX idx_books_statut_featured ON books (statut, mis_en_avant)',
    'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;
