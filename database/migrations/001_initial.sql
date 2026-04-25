-- =============================================================================
-- SCHEMA BASE DE DONNÉES — LES ÉDITIONS VARIABLE
-- =============================================================================
-- MySQL 8+ / utf8mb4_unicode_ci
-- =============================================================================

USE leseditionsvariable;

-- Table: users
CREATE TABLE users (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(191) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    prenom VARCHAR(100) NOT NULL,
    nom VARCHAR(100) NOT NULL,
    telephone VARCHAR(30),
    pays VARCHAR(2) DEFAULT 'CD',
    devise_preferee VARCHAR(3) DEFAULT 'USD',
    role ENUM('lecteur', 'auteur', 'admin') DEFAULT 'lecteur',
    statut VARCHAR(20) DEFAULT 'actif',
    avatar_url VARCHAR(500),
    bio TEXT,
    google_id VARCHAR(100),
    stripe_customer_id VARCHAR(100),
    email_verifie BOOLEAN DEFAULT FALSE,
    token_verification VARCHAR(64),
    token_reset_password VARCHAR(64),
    token_reset_expiration DATETIME,
    derniere_connexion DATETIME,
    nombre_tentatives_echec INT DEFAULT 0,
    bloque_jusqu_a DATETIME,
    actif BOOLEAN DEFAULT TRUE,
    accepte_cgu_at DATETIME,
    accepte_newsletter BOOLEAN DEFAULT FALSE,
    parrain_id INT UNSIGNED,
    code_parrainage VARCHAR(20) UNIQUE,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at DATETIME DEFAULT NULL,
    INDEX idx_email (email),
    INDEX idx_role (role),
    INDEX idx_users_statut (statut),
    INDEX idx_parrain (parrain_id),
    FOREIGN KEY (parrain_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- Table: notifications (système de notifications utilisateur)
CREATE TABLE notifications (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL,
    type VARCHAR(50) NOT NULL,
    title VARCHAR(200) NOT NULL,
    message TEXT,
    link_url VARCHAR(500) DEFAULT NULL,
    icon VARCHAR(20) DEFAULT 'bell',
    read_at DATETIME DEFAULT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_user_unread (user_id, read_at),
    INDEX idx_created (created_at),
    INDEX idx_type (type),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Table: user_deletion_tokens (RGPD — confirmation par email avant soft-delete)
CREATE TABLE user_deletion_tokens (
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

-- Table: authors
CREATE TABLE authors (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED UNIQUE NOT NULL,
    slug VARCHAR(150) UNIQUE NOT NULL,
    nom_plume VARCHAR(200),
    biographie_courte TEXT,
    biographie_longue TEXT,
    photo_auteur VARCHAR(500),
    pays_origine VARCHAR(2),
    ville_residence VARCHAR(100),
    site_web VARCHAR(500),
    facebook_url VARCHAR(500),
    twitter_x_url VARCHAR(500),
    linkedin_url VARCHAR(500),
    instagram_url VARCHAR(500),
    youtube_url VARCHAR(500),
    methode_versement ENUM('mobile_money', 'banque', 'paypal', 'stripe') DEFAULT 'mobile_money',
    numero_mobile_money VARCHAR(30),
    operateur_mobile_money VARCHAR(50),
    iban VARCHAR(50),
    bic_swift VARCHAR(20),
    nom_banque VARCHAR(200),
    email_paypal VARCHAR(191),
    statut_validation ENUM('en_attente', 'valide', 'suspendu', 'refuse') DEFAULT 'en_attente',
    date_validation DATETIME,
    valide_par_admin_id INT UNSIGNED,
    notes_admin TEXT,
    contrat_signe BOOLEAN DEFAULT FALSE,
    contrat_url VARCHAR(500),
    total_livres_publies INT DEFAULT 0,
    total_ventes_cumul DECIMAL(10,2) DEFAULT 0,
    total_lectures_cumul INT DEFAULT 0,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_slug (slug),
    INDEX idx_statut (statut_validation),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (valide_par_admin_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- Table: categories
CREATE TABLE categories (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(100) NOT NULL,
    slug VARCHAR(100) UNIQUE NOT NULL,
    description TEXT,
    parent_id INT UNSIGNED,
    ordre_affichage INT DEFAULT 0,
    icone VARCHAR(50),
    actif BOOLEAN DEFAULT TRUE,
    FOREIGN KEY (parent_id) REFERENCES categories(id) ON DELETE SET NULL
) ENGINE=InnoDB;

INSERT INTO categories (nom, slug, ordre_affichage, icone) VALUES
('Biographies & Mémoires', 'biographies-memoires', 1, 'BookUser'),
('Développement personnel', 'developpement-personnel', 2, 'TrendingUp'),
('Spiritualité & Religion', 'spiritualite-religion', 3, 'BookOpenCheck'),
('Roman & Fiction', 'roman-fiction', 4, 'BookOpen'),
('Essais & Société', 'essais-societe', 5, 'ScrollText'),
('Histoire Afrique', 'histoire-afrique', 6, 'Globe'),
('Poésie & Théâtre', 'poesie-theatre', 7, 'Feather'),
('Business & Entrepreneuriat', 'business-entrepreneuriat', 8, 'Briefcase'),
('Santé & Bien-être', 'sante-bien-etre', 9, 'Heart'),
('Jeunesse & Éducation', 'jeunesse-education', 10, 'GraduationCap');

-- Table: books
CREATE TABLE books (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    author_id INT UNSIGNED NOT NULL,
    category_id INT UNSIGNED,
    titre VARCHAR(255) NOT NULL,
    sous_titre VARCHAR(500),
    slug VARCHAR(250) UNIQUE NOT NULL,
    isbn VARCHAR(20),
    description_courte VARCHAR(500),
    description_longue TEXT,
    mots_cles VARCHAR(500),
    langue VARCHAR(10) DEFAULT 'fr',
    nombre_pages INT,
    annee_publication YEAR,
    editeur VARCHAR(200) DEFAULT 'Les éditions Variable',
    fichier_complet_path VARCHAR(500),
    fichier_extrait_path VARCHAR(500),
    fichier_epub_path VARCHAR(500),
    couverture_path VARCHAR(500),
    couverture_url_web VARCHAR(500),
    prix_unitaire_usd DECIMAL(8,2) DEFAULT 9.99,
    prix_unitaire_cdf DECIMAL(10,2),
    prix_unitaire_xof DECIMAL(10,2),
    prix_unitaire_eur DECIMAL(8,2),
    prix_unitaire_cad DECIMAL(8,2),
    accessible_abonnement_essentiel BOOLEAN DEFAULT TRUE,
    accessible_abonnement_premium BOOLEAN DEFAULT TRUE,
    exclusif_achat BOOLEAN DEFAULT FALSE,
    statut ENUM('brouillon', 'en_revue', 'publie', 'retire') DEFAULT 'brouillon',
    date_publication DATETIME,
    mis_en_avant BOOLEAN DEFAULT FALSE,
    nouveaute BOOLEAN DEFAULT TRUE,
    total_ventes INT DEFAULT 0,
    total_lectures INT DEFAULT 0,
    total_pages_lues_cumul BIGINT DEFAULT 0,
    note_moyenne DECIMAL(3,2) DEFAULT 0,
    nombre_avis INT DEFAULT 0,
    revenus_cumul DECIMAL(10,2) DEFAULT 0,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_slug (slug),
    INDEX idx_author (author_id),
    INDEX idx_category (category_id),
    INDEX idx_statut (statut),
    INDEX idx_mis_en_avant (mis_en_avant),
    FOREIGN KEY (author_id) REFERENCES authors(id) ON DELETE CASCADE,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL,
    FULLTEXT INDEX ft_search (titre, description_courte, description_longue, mots_cles)
) ENGINE=InnoDB;

-- Table: subscriptions
CREATE TABLE subscriptions (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL,
    type ENUM('essentiel_mensuel', 'essentiel_annuel', 'premium_mensuel', 'premium_annuel') NOT NULL,
    date_debut DATETIME NOT NULL,
    date_fin DATETIME NOT NULL,
    prix_paye DECIMAL(8,2) NOT NULL,
    devise VARCHAR(3) NOT NULL,
    methode_paiement VARCHAR(50),
    transaction_id VARCHAR(100),
    stripe_subscription_id VARCHAR(100),
    stripe_price_id VARCHAR(100),
    renouvellement_auto BOOLEAN DEFAULT TRUE,
    statut ENUM('actif', 'en_pause', 'annule', 'expire', 'echec_paiement') DEFAULT 'actif',
    date_annulation DATETIME,
    raison_annulation TEXT,
    motif_annulation VARCHAR(50) DEFAULT NULL,
    nb_tentatives_renouvellement INT DEFAULT 0,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_user (user_id),
    INDEX idx_statut (statut),
    INDEX idx_date_fin (date_fin),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Table: sales
CREATE TABLE sales (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL,
    book_id INT UNSIGNED NOT NULL,
    author_id INT UNSIGNED NOT NULL,
    prix_paye DECIMAL(8,2) NOT NULL,
    devise VARCHAR(3) NOT NULL,
    prix_paye_usd DECIMAL(8,2) NOT NULL,
    commission_variable DECIMAL(8,2) NOT NULL,
    revenu_auteur DECIMAL(8,2) NOT NULL,
    frais_plateforme DECIMAL(8,2) DEFAULT 0,
    methode_paiement VARCHAR(50),
    transaction_id VARCHAR(100) UNIQUE,
    statut ENUM('en_attente', 'payee', 'remboursee', 'echec') DEFAULT 'en_attente',
    date_vente DATETIME DEFAULT CURRENT_TIMESTAMP,
    date_paiement_confirme DATETIME,
    INDEX idx_user (user_id),
    INDEX idx_book (book_id),
    INDEX idx_author (author_id),
    INDEX idx_statut (statut),
    INDEX idx_date (date_vente),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (book_id) REFERENCES books(id) ON DELETE CASCADE,
    FOREIGN KEY (author_id) REFERENCES authors(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Table: user_books
CREATE TABLE user_books (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL,
    book_id INT UNSIGNED NOT NULL,
    source ENUM('achat_unitaire', 'abonnement', 'favori') NOT NULL,
    sale_id INT UNSIGNED,
    date_ajout DATETIME DEFAULT CURRENT_TIMESTAMP,
    dernier_acces DATETIME,
    favori BOOLEAN DEFAULT FALSE,
    UNIQUE KEY uniq_user_book (user_id, book_id),
    INDEX idx_user (user_id),
    INDEX idx_book (book_id),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (book_id) REFERENCES books(id) ON DELETE CASCADE,
    FOREIGN KEY (sale_id) REFERENCES sales(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- Table: reading_sessions
CREATE TABLE reading_sessions (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL,
    book_id INT UNSIGNED NOT NULL,
    session_token VARCHAR(64) UNIQUE,
    ip_address VARCHAR(45),
    user_agent VARCHAR(500),
    page_debut INT DEFAULT 1,
    page_fin INT,
    pages_lues_session INT DEFAULT 0,
    temps_lecture_secondes INT DEFAULT 0,
    debut_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    fin_at DATETIME,
    statut ENUM('active', 'close', 'expiree') DEFAULT 'active',
    INDEX idx_user_book (user_id, book_id),
    INDEX idx_statut (statut),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (book_id) REFERENCES books(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Table: reading_progress
CREATE TABLE reading_progress (
    user_id INT UNSIGNED NOT NULL,
    book_id INT UNSIGNED NOT NULL,
    derniere_page_lue INT DEFAULT 1,
    total_pages_lues INT DEFAULT 0,
    total_temps_lecture INT DEFAULT 0,
    pourcentage_complete DECIMAL(5,2) DEFAULT 0,
    premiere_lecture_at DATETIME,
    derniere_lecture_at DATETIME,
    livre_termine BOOLEAN DEFAULT FALSE,
    date_completion DATETIME,
    PRIMARY KEY (user_id, book_id),
    INDEX idx_user (user_id),
    INDEX idx_book (book_id),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (book_id) REFERENCES books(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Table: reviews
CREATE TABLE reviews (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL,
    book_id INT UNSIGNED NOT NULL,
    note TINYINT UNSIGNED NOT NULL,
    titre VARCHAR(200),
    commentaire TEXT,
    approuve BOOLEAN DEFAULT FALSE,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uniq_user_book_review (user_id, book_id),
    INDEX idx_book (book_id),
    INDEX idx_approuve (approuve),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (book_id) REFERENCES books(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Table: author_payouts
CREATE TABLE author_payouts (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    author_id INT UNSIGNED NOT NULL,
    periode_debut DATE NOT NULL,
    periode_fin DATE NOT NULL,
    revenus_ventes_unitaires DECIMAL(10,2) DEFAULT 0,
    revenus_pool_abonnement DECIMAL(10,2) DEFAULT 0,
    total_a_verser DECIMAL(10,2) NOT NULL,
    devise VARCHAR(3) DEFAULT 'USD',
    methode_versement VARCHAR(50),
    reference_versement VARCHAR(100),
    statut ENUM('calcule', 'a_verser', 'en_cours', 'verse', 'echec', 'annule') DEFAULT 'calcule',
    date_versement DATETIME,
    notes TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_author (author_id),
    INDEX idx_statut (statut),
    INDEX idx_periode (periode_debut, periode_fin),
    FOREIGN KEY (author_id) REFERENCES authors(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Table: subscription_pool
CREATE TABLE subscription_pool (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    annee SMALLINT NOT NULL,
    mois TINYINT NOT NULL,
    total_abonnements DECIMAL(12,2) NOT NULL,
    frais_moneyfusion DECIMAL(10,2) DEFAULT 0,
    frais_stripe DECIMAL(10,2) DEFAULT 0,
    montant_net DECIMAL(12,2) NOT NULL,
    pourcentage_pool DECIMAL(5,2) DEFAULT 50,
    pool_auteurs DECIMAL(12,2) NOT NULL,
    total_pages_lues_mois BIGINT DEFAULT 0,
    taux_par_page DECIMAL(10,6) DEFAULT 0,
    statut ENUM('calcul_en_cours', 'fige', 'distribue') DEFAULT 'calcul_en_cours',
    date_calcul DATETIME DEFAULT CURRENT_TIMESTAMP,
    date_distribution DATETIME,
    UNIQUE KEY uniq_periode (annee, mois)
) ENGINE=InnoDB;

-- Table: transactions_log
CREATE TABLE transactions_log (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    type ENUM('vente', 'abonnement', 'remboursement', 'versement_auteur', 'autre') NOT NULL,
    user_id INT UNSIGNED,
    reference_id INT UNSIGNED,
    reference_type VARCHAR(50),
    provider ENUM('money_fusion', 'stripe', 'manuel') NOT NULL,
    provider_transaction_id VARCHAR(200),
    montant DECIMAL(10,2),
    devise VARCHAR(3),
    statut VARCHAR(50),
    payload_request JSON,
    payload_response JSON,
    ip_address VARCHAR(45),
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_type (type),
    INDEX idx_user (user_id),
    INDEX idx_provider_tx (provider_transaction_id),
    INDEX idx_date (created_at)
) ENGINE=InnoDB;

-- Table: settings
CREATE TABLE settings (
    `key` VARCHAR(100) PRIMARY KEY,
    `value` TEXT,
    description VARCHAR(500),
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

INSERT INTO settings (`key`, `value`, description) VALUES
('prix_abonnement_mensuel_usd', '3.00', 'Prix mensuel abonnement USD'),
('prix_abonnement_annuel_usd', '30.00', 'Prix annuel abonnement USD'),
('prix_abonnement_premium_mensuel_usd', '8.00', 'Prix mensuel Premium USD'),
('commission_variable_pct', '20', 'Pourcentage commission Variable sur ventes unitaires'),
('pourcentage_pool_redistribution', '50', 'Pourcentage des revenus abonnement redistribué aux auteurs'),
('seuil_minimum_versement_usd', '20', 'Montant minimum pour déclencher un versement auteur'),
('taux_conversion_usd_cdf', '2800', 'Taux USD vers Franc Congolais'),
('email_support', 'support@leseditionsvariable.com', 'Email de contact support'),
('email_contact', 'contact@leseditionsvariable.com', 'Email de contact général'),
('email_auteurs', 'auteurs@leseditionsvariable.com', 'Email pour les auteurs'),
('email_compta', 'compta@leseditionsvariable.com', 'Email pour la comptabilité'),
('frequence_paiement_auteurs', 'trimestriel', 'Mensuel ou trimestriel'),
('nb_pages_extrait_gratuit', '10', 'Nombre de pages accessibles gratuitement');

-- Table: audit_log
CREATE TABLE audit_log (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    admin_id INT UNSIGNED NOT NULL,
    action VARCHAR(100) NOT NULL,
    entity_type VARCHAR(50),
    entity_id INT UNSIGNED,
    old_values JSON,
    new_values JSON,
    ip_address VARCHAR(45),
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_admin (admin_id),
    INDEX idx_date (created_at),
    FOREIGN KEY (admin_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;
