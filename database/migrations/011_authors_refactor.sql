-- =============================================================================
-- Migration 011 — Authors refactor (is_classic + user_id NULL + 11 classiques)
-- =============================================================================
-- Contexte : avant ce refactor, la table authors imposait user_id NOT NULL,
-- ce qui empêchait :
--   - L'admin de créer un auteur "fantôme" (Émile Zola, Hugo...) sans
--     compte user dédié
--   - L'upload de plusieurs livres pour un même auteur sans recréer le row
--     (et donc collision de slug 'editions-variable' — log prod du 09:54:21)
--
-- Cette migration :
--   1. Rend user_id NULLABLE (l'index UNIQUE existant accepte plusieurs NULL
--      en MySQL/InnoDB, donc plusieurs auteurs sans user_id cohabitent)
--   2. Ajoute is_classic TINYINT(1) DEFAULT 0 pour distinguer les auteurs
--      classiques (Zola, Hugo...) des auteurs avec compte user
--   3. Seed 11 auteurs classiques français (INSERT IGNORE → idempotent
--      sur le slug, donc relancer la migration ne casse rien)
--
-- Idempotence : check via INFORMATION_SCHEMA + PREPARE/EXECUTE (compatible
-- phpMyAdmin sans DELIMITER). Rejouable sans erreur sur MySQL 8.0+.
-- =============================================================================

-- 1. Ajout conditionnel de la colonne is_classic
SET @col_exists := (
    SELECT COUNT(*) FROM information_schema.columns
     WHERE table_schema = DATABASE()
       AND table_name = 'authors'
       AND column_name = 'is_classic'
);
SET @stmt := IF(@col_exists = 0,
    'ALTER TABLE authors ADD COLUMN is_classic TINYINT(1) NOT NULL DEFAULT 0 AFTER user_id',
    'DO 0');
PREPARE q FROM @stmt;
EXECUTE q;
DEALLOCATE PREPARE q;

-- 2. user_id NULLABLE (MODIFY est rejouable sans erreur)
ALTER TABLE authors MODIFY COLUMN user_id INT UNSIGNED NULL;

-- 3. Seed des 11 auteurs classiques (INSERT IGNORE → skip si slug existe déjà)
INSERT IGNORE INTO authors (user_id, slug, nom_plume, biographie_courte, pays_origine, statut_validation, is_classic, created_at) VALUES
(NULL, 'emile-zola',         'Émile Zola',          'Chef de file du naturalisme français — Les Rougon-Macquart, Germinal, L''Assommoir.',                  'FR', 'valide', 1, NOW()),
(NULL, 'guy-de-maupassant',  'Guy de Maupassant',   'Maître de la nouvelle réaliste — Bel-Ami, Boule de Suif, Le Horla.',                                  'FR', 'valide', 1, NOW()),
(NULL, 'honore-de-balzac',   'Honoré de Balzac',    'Architecte de La Comédie humaine — Le Père Goriot, Eugénie Grandet, Illusions perdues.',              'FR', 'valide', 1, NOW()),
(NULL, 'jules-verne',        'Jules Verne',         'Pionnier du roman d''aventure scientifique — Vingt mille lieues sous les mers, Le Tour du monde en 80 jours.', 'FR', 'valide', 1, NOW()),
(NULL, 'stendhal',           'Stendhal',            'Romantisme et psychologie amoureuse — Le Rouge et le Noir, La Chartreuse de Parme.',                  'FR', 'valide', 1, NOW()),
(NULL, 'charles-baudelaire', 'Charles Baudelaire',  'Précurseur du symbolisme — Les Fleurs du mal, Le Spleen de Paris.',                                   'FR', 'valide', 1, NOW()),
(NULL, 'arthur-rimbaud',     'Arthur Rimbaud',      'Génie précoce de la poésie symboliste — Une saison en enfer, Illuminations.',                         'FR', 'valide', 1, NOW()),
(NULL, 'edmond-rostand',     'Edmond Rostand',      'Théâtre néo-romantique en vers — Cyrano de Bergerac, L''Aiglon.',                                     'FR', 'valide', 1, NOW()),
(NULL, 'jean-racine',        'Jean Racine',         'Tragédie classique du XVIIe — Phèdre, Andromaque, Britannicus.',                                      'FR', 'valide', 1, NOW()),
(NULL, 'moliere',            'Molière',             'Maître de la comédie classique — Le Misanthrope, Tartuffe, L''Avare, Le Bourgeois gentilhomme.',       'FR', 'valide', 1, NOW()),
(NULL, 'victor-hugo',        'Victor Hugo',         'Figure majeure du romantisme français — Les Misérables, Notre-Dame de Paris, Les Contemplations.',    'FR', 'valide', 1, NOW());
