-- =============================================================================
-- Migration 005 — Service éditorial pour auteurs (catalogue + commandes)
-- =============================================================================

CREATE TABLE IF NOT EXISTS editorial_services (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    slug VARCHAR(100) UNIQUE NOT NULL,
    nom VARCHAR(200) NOT NULL,
    description TEXT,
    icon VARCHAR(20),
    prix_usd DECIMAL(10,2) DEFAULT NULL,
    prix_cdf INT DEFAULT NULL,
    prix_eur DECIMAL(10,2) DEFAULT NULL,
    prix_cad DECIMAL(10,2) DEFAULT NULL,
    sur_devis TINYINT(1) DEFAULT 0,
    duree_estimee VARCHAR(100),
    actif TINYINT(1) DEFAULT 1,
    ordre INT DEFAULT 0,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS editorial_orders (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL,
    service_id INT UNSIGNED NOT NULL,
    titre_projet VARCHAR(300),
    description_projet TEXT,
    fichier_url VARCHAR(500) DEFAULT NULL,
    nombre_pages INT DEFAULT NULL,
    montant_propose DECIMAL(10,2) DEFAULT NULL,
    devise VARCHAR(10) DEFAULT 'USD',
    statut ENUM('en_attente_devis','devis_envoye','accepte','en_cours','livre','annule','rembourse') DEFAULT 'en_attente_devis',
    notes_admin TEXT,
    notes_auteur TEXT,
    fichier_livraison_url VARCHAR(500) DEFAULT NULL,
    transaction_id VARCHAR(200) DEFAULT NULL,
    paye_at DATETIME DEFAULT NULL,
    livre_at DATETIME DEFAULT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_user (user_id),
    INDEX idx_statut (statut),
    INDEX idx_service (service_id),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (service_id) REFERENCES editorial_services(id) ON DELETE RESTRICT
) ENGINE=InnoDB;

INSERT INTO editorial_services (slug, nom, description, icon, prix_usd, sur_devis, duree_estimee, ordre) VALUES
('relecture-correction', 'Relecture et correction', 'Correction orthographique, grammaticale, syntaxique et stylistique de ton manuscrit par un éditeur expérimenté.', 'edit', 75.00, 0, '5 à 10 jours', 1),
('mise-en-page', 'Mise en page professionnelle', 'Maquette typographique pour publication imprimée ou numérique. Choix des polices, marges, hiérarchie visuelle.', 'layout', 120.00, 0, '3 à 7 jours', 2),
('couverture', 'Création de couverture', 'Couverture personnalisée conçue par un graphiste, format imprimé et numérique inclus.', 'image', 150.00, 0, '5 à 14 jours', 3),
('coaching', 'Coaching d''écriture', 'Séances de coaching individuel avec un auteur expert. Conseils sur structure, style, narration.', 'message', 40.00, 0, '1 séance d''1h', 4),
('pack-complet', 'Pack complet', 'Relecture + Mise en page + Couverture personnalisée. Tout inclus pour publier ton livre.', 'package', NULL, 1, 'À partir de 3 semaines', 5),
('autre', 'Autre service personnalisé', 'Décris ton besoin spécifique : illustration, traduction, audio, marketing, etc.', 'plus', NULL, 1, 'Variable', 6);
