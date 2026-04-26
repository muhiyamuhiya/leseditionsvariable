-- =====================================================
-- Les éditions Variable — Schéma complet (toutes migrations consolidées)
-- À importer en une fois sur une base MySQL/MariaDB neuve via phpMyAdmin
-- =====================================================

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS=0;

-- =============================================================================
-- SCHEMA BASE DE DONNÉES — LES ÉDITIONS VARIABLE
-- =============================================================================
-- MySQL 8+ / utf8mb4_unicode_ci
-- =============================================================================

-- Pas de USE ici : la base est sélectionnée par phpMyAdmin (ou l'outil d'import).
-- Sur cPanel mutualisé (NitroHost), le nom de la base est préfixé (ex: lesediti_variable).
-- USE leseditionsvariable;

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

-- Table: newsletter_subscribers
CREATE TABLE newsletter_subscribers (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255) UNIQUE NOT NULL,
    prenom VARCHAR(100) DEFAULT NULL,
    source VARCHAR(50) NOT NULL DEFAULT 'website',
    confirmed_at DATETIME DEFAULT NULL,
    unsubscribed_at DATETIME DEFAULT NULL,
    confirmation_token VARCHAR(64) DEFAULT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_email (email),
    INDEX idx_confirmed (confirmed_at),
    INDEX idx_source (source)
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
    user_id INT UNSIGNED UNIQUE NULL,
    is_classic TINYINT(1) NOT NULL DEFAULT 0,
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

-- =============================================================================
-- Migration 005 — Services éditoriaux pour auteurs (catalogue + commandes)
-- =============================================================================

-- Table: editorial_services
CREATE TABLE editorial_services (
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

INSERT INTO editorial_services (slug, nom, description, icon, prix_usd, sur_devis, duree_estimee, ordre) VALUES
('relecture-correction', 'Relecture et correction', 'Correction orthographique, grammaticale, syntaxique et stylistique de ton manuscrit par un éditeur expérimenté.', 'edit', 75.00, 0, '5 à 10 jours', 1),
('mise-en-page', 'Mise en page professionnelle', 'Maquette typographique pour publication imprimée ou numérique. Choix des polices, marges, hiérarchie visuelle.', 'layout', 120.00, 0, '3 à 7 jours', 2),
('couverture', 'Création de couverture', 'Couverture personnalisée conçue par un graphiste, format imprimé et numérique inclus.', 'image', 150.00, 0, '5 à 14 jours', 3),
('coaching', 'Coaching d''écriture', 'Séances de coaching individuel avec un auteur expert. Conseils sur structure, style, narration.', 'message', 40.00, 0, '1 séance d''1h', 4),
('pack-complet', 'Pack complet', 'Relecture + Mise en page + Couverture personnalisée. Tout inclus pour publier ton livre.', 'package', NULL, 1, 'À partir de 3 semaines', 5),
('autre', 'Autre service personnalisé', 'Décris ton besoin spécifique : illustration, traduction, audio, marketing, etc.', 'plus', NULL, 1, 'Variable', 6);

-- Table: editorial_orders
CREATE TABLE editorial_orders (
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

-- =============================================================================
-- Migration 007 — Chat custom maison (bot mots-clés + admin)
-- =============================================================================

-- Table: chat_conversations
CREATE TABLE chat_conversations (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED DEFAULT NULL,
    session_id VARCHAR(64) NOT NULL,
    visitor_email VARCHAR(255) DEFAULT NULL,
    visitor_name VARCHAR(100) DEFAULT NULL,
    statut ENUM('ouverte', 'en_attente_admin', 'repondue', 'archivee') NOT NULL DEFAULT 'ouverte',
    last_message_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    has_unread_for_admin TINYINT(1) NOT NULL DEFAULT 0,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_session (session_id),
    INDEX idx_user (user_id),
    INDEX idx_unread (has_unread_for_admin),
    INDEX idx_last_msg (last_message_at),
    INDEX idx_statut (statut),
    CONSTRAINT fk_chat_conv_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Table: chat_messages
CREATE TABLE chat_messages (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    conversation_id INT UNSIGNED NOT NULL,
    sender_type ENUM('visiteur', 'user', 'bot', 'admin') NOT NULL,
    sender_user_id INT UNSIGNED DEFAULT NULL,
    content TEXT NOT NULL,
    is_bot_response TINYINT(1) NOT NULL DEFAULT 0,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_conv (conversation_id),
    INDEX idx_created (created_at),
    CONSTRAINT fk_chat_msg_conv FOREIGN KEY (conversation_id) REFERENCES chat_conversations(id) ON DELETE CASCADE,
    CONSTRAINT fk_chat_msg_user FOREIGN KEY (sender_user_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Table: chat_responses (bot pré-écrit, matching mots-clés)
CREATE TABLE chat_responses (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    keywords TEXT NOT NULL,
    question TEXT NOT NULL,
    answer TEXT NOT NULL,
    category VARCHAR(50) DEFAULT NULL,
    actif TINYINT(1) NOT NULL DEFAULT 1,
    times_used INT UNSIGNED NOT NULL DEFAULT 0,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_actif (actif),
    INDEX idx_category (category)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Seed : 34 réponses initiales du bot
-- Convention : le PREMIER mot-clé vaut +2 points (priorité), les suivants +1.
-- Threshold pour matcher = score >= 2.
INSERT INTO chat_responses (keywords, question, answer, category) VALUES
-- Salutations
('bonjour,salut,coucou,hello,bonsoir,hi,hey,yo', 'Salutation', 'Salut ! 👋 Comment puis-je t''aider aujourd''hui ?', 'salutations'),
('merci,thanks,thx,thank you', 'Remerciement', 'Avec plaisir ! 😊 N''hésite pas si tu as d''autres questions.', 'salutations'),
('au revoir,bye,a plus,a bientot,ciao', 'Au revoir', 'À bientôt ! On reste là si tu as d''autres questions. 👋', 'salutations'),
('aide,help,support,assistance,besoin aide', 'Demande d''aide', 'Bien sûr ! Dis-moi ce qui se passe et je t''oriente. Je peux répondre à plein de choses, sinon Angello prend le relais.', 'salutations'),
('qui es tu,qui est angello,equipe,fondateur,patron', 'Qui est Angello', 'Angello Muhiya est le fondateur de Variable. Si je ne sais pas répondre, c''est lui qui te répond directement (souvent en quelques minutes).', 'salutations'),
('contact,contacter,nous joindre,vous joindre,email contact', 'Contact', 'Tu peux nous écrire ici via le chat, ou par email à <a href="mailto:contact@leseditionsvariable.com">contact@leseditionsvariable.com</a>.', 'salutations'),
('horaires,heure ouverture,disponible quand,quand reponse', 'Horaires', 'Réponses humaines : <strong>lun-sam 8h-19h (Kinshasa)</strong>. Le bot répond 24/7 aux questions courantes.', 'salutations'),

-- Abonnements
('abonnement,combien,prix abonnement,coute abonnement,tarif abonnement,formule', 'Combien coûte l''abonnement', 'On a 3 formules :<br><strong>• Essentiel Mensuel</strong> — 3$/mois<br><strong>• Essentiel Annuel</strong> — 30$/an (économise 6$)<br><strong>• Premium</strong> — 8$/mois (livres premium inclus)<br><br>Plus de détails : <a href="/abonnement">Voir les abonnements</a>', 'abonnement'),
('annuler,resilier,arreter abonnement,stop abonnement,desabonner', 'Annuler abonnement', 'Tu peux annuler ton abonnement à tout moment depuis <a href="/mon-compte/abonnement">Mon compte → Abonnement</a>. L''accès reste actif jusqu''à la fin de la période payée.', 'abonnement'),
('difference essentiel premium,essentiel premium,quelle difference,quelle formule choisir,comparer formules', 'Différence Essentiel/Premium', 'L''<strong>Essentiel</strong> donne accès au catalogue standard. Le <strong>Premium</strong> ajoute les livres premium (inédits, gros titres) et les avant-premières.', 'abonnement'),
('renouvellement,renouvelle,paiement automatique,reabonnement', 'Renouvellement', 'Stripe se renouvelle automatiquement chaque mois/an. Money Fusion (mobile RDC) est manuel : tu reçois un rappel avant l''expiration.', 'abonnement'),
('changer formule,changer abonnement,upgrade,passer premium', 'Changer de formule', 'Tu peux changer de formule à tout moment depuis <a href="/mon-compte/abonnement">Mon compte → Abonnement</a>. Le prorata est calculé automatiquement.', 'abonnement'),

-- Achat unitaire
('acheter livre,achat livre,prix livre,combien coute livre', 'Comment acheter un livre', 'Va sur la fiche du livre, clique "Acheter", choisis Stripe (carte) ou Money Fusion (mobile RDC). Le livre apparaît dans ta bibliothèque immédiatement.', 'achat'),
('part auteur achat,combien touche auteur,royalties,redistribution achat', 'Part auteur sur achat', 'Sur chaque vente unitaire, l''auteur reçoit <strong>70%</strong> du prix (après frais de paiement). Le reste finance la plateforme et la promotion.', 'achat'),
('mode paiement,modes paiement,quel paiement,carte,mobile money,mpesa,airtel', 'Modes de paiement', 'On accepte :<br>• <strong>Stripe</strong> — cartes Visa/Mastercard (international)<br>• <strong>Money Fusion</strong> — Mobile Money (RDC : M-Pesa, Airtel, Orange)', 'achat'),
('rembourser,remboursement,refund,reprendre argent', 'Remboursement', 'Pour un livre acheté par erreur ou un problème technique, écris-nous ici. On regarde au cas par cas.', 'achat'),

-- Services éditoriaux
('relecture,correction,corriger texte,relire manuscrit', 'Service de relecture', 'On propose relecture, correction, mise en page et accompagnement éditorial. Tarifs : <a href="/services-editoriaux">Voir les services éditoriaux</a>', 'services'),
('publier livre,faire publier,vous publiez,editeur', 'Publier mon livre', 'Pour soumettre ton livre : candidate via <a href="/auteur/candidater">/auteur/candidater</a>. On revient vers toi sous 7-14 jours après lecture du manuscrit.', 'services'),
('mise en page,maquette,couverture,design livre', 'Mise en page', 'Mise en page intérieur + couverture : tarif selon le nombre de pages. Devis personnalisé : <a href="/services-editoriaux">Services éditoriaux</a>', 'services'),
('isbn,depot legal,enregistrement livre', 'ISBN / Dépôt légal', 'Oui on s''occupe de l''ISBN et du dépôt légal pour les livres qu''on publie. Inclus dans l''accompagnement éditorial.', 'services'),

-- Compte
('creer compte,inscription,inscrire,creation compte', 'Créer un compte', 'Crée ton compte ici : <a href="/inscription">/inscription</a>. C''est gratuit et ça prend 30 secondes.', 'compte'),
('supprimer compte,fermer compte,delete compte,effacer compte', 'Supprimer mon compte', 'Tu peux supprimer ton compte depuis <a href="/mon-compte/parametres">Mon compte → Paramètres</a>. La suppression est définitive après confirmation par email.', 'compte'),
('mot de passe oublie,perdu mot de passe,reinitialiser,reset password', 'Mot de passe oublié', 'Pas de panique : <a href="/mot-de-passe-oublie">Réinitialise ton mot de passe</a>. Tu recevras un lien par email.', 'compte'),
('changer email,modifier email,changer adresse,nouvel email', 'Changer mon email', 'Tu peux changer ton email depuis <a href="/mon-compte/parametres">Mon compte → Paramètres</a>. Une vérification par email te sera demandée.', 'compte'),

-- Lecture / Liseuse
('format,formats,quel format,epub,pdf,ebook', 'Formats supportés', 'Les livres sont lus directement dans la liseuse Variable (PDF.js sécurisé). Pas de téléchargement direct, lecture page par page.', 'lecture'),
('telecharger livre,telecharger,download,offline,hors ligne', 'Télécharger un livre', 'Pour des raisons de protection des auteurs, les livres ne sont pas téléchargeables. Tu peux les lire à volonté en ligne dans la liseuse.', 'lecture'),
('extrait,apercu,gratuit,echantillon,decouvrir livre', 'Extrait gratuit', 'Tous les livres ont un extrait gratuit (10 premières pages). Clique "Aperçu" sur la fiche du livre.', 'lecture'),
('partager livre,recommander livre,offrir livre', 'Partager un livre', 'Pour offrir un livre à quelqu''un, partage simplement le lien. Pour l''instant pas de système cadeau direct, mais c''est en réflexion.', 'lecture'),

-- Devenir auteur
('devenir auteur,etre auteur,je veux ecrire,publier mon roman', 'Devenir auteur', 'Candidate via <a href="/auteur/candidater">/auteur/candidater</a>. Soumets ton manuscrit, ta bio et un extrait. Réponse sous 7-14 jours.', 'auteur'),
('delai validation,combien temps reponse,quand reponse manuscrit', 'Délai de validation', 'Validation candidature : 7-14 jours. Mise en ligne après acceptation : 2-4 semaines (selon mise en page nécessaire).', 'auteur'),
('manuscrit refuse,refus,non accepte,non publie', 'Manuscrit refusé', 'Si on refuse, on t''envoie un retour détaillé. Tu peux soumettre un nouveau manuscrit ensuite. On encourage à retravailler et retenter.', 'auteur'),

-- Technique
('paiement echec,paiement echoue,carte refusee,probleme paiement,bug paiement', 'Paiement échoué', 'Si ton paiement échoue, vérifie ton solde et ressaye. Pour Money Fusion : confirme bien le SMS sur ton téléphone. Si ça persiste, écris-nous ici, on règle ça.', 'technique'),
('bug,erreur,probleme technique,marche pas,plante', 'Problème technique', 'Décris ton souci ici (page concernée + ce qui se passe), on regarde tout de suite.', 'technique'),
('email pas recu,pas recu email,email confirmation,verification email', 'Email pas reçu', 'Vérifie tes spams/courrier indésirable. Si toujours rien, dis-le-nous ici avec ton adresse, on relance.', 'technique');

-- =============================================================================
-- Migration 008 — Codes promo uniques (drip campaigns + marketing)
-- =============================================================================

CREATE TABLE promo_codes (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    code VARCHAR(40) UNIQUE NOT NULL,
    user_id INT UNSIGNED DEFAULT NULL,
    discount_pct TINYINT UNSIGNED NOT NULL DEFAULT 20,
    max_uses INT UNSIGNED DEFAULT 1,
    times_used INT UNSIGNED NOT NULL DEFAULT 0,
    valid_from DATETIME DEFAULT CURRENT_TIMESTAMP,
    valid_until DATETIME DEFAULT NULL,
    source VARCHAR(50) DEFAULT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    used_at DATETIME DEFAULT NULL,
    INDEX idx_code (code),
    INDEX idx_user (user_id),
    INDEX idx_source (source),
    INDEX idx_valid (valid_until),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =============================================================================
-- Migration 009 — Automation emails (séquences + cron)
-- =============================================================================

CREATE TABLE email_sequences (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    slug VARCHAR(80) UNIQUE NOT NULL,
    name VARCHAR(160) NOT NULL,
    description TEXT,
    active TINYINT(1) NOT NULL DEFAULT 1,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_active (active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE email_sequence_steps (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    sequence_id INT UNSIGNED NOT NULL,
    sort_order TINYINT UNSIGNED NOT NULL,
    day_offset SMALLINT UNSIGNED NOT NULL,
    template VARCHAR(80) NOT NULL,
    subject VARCHAR(200) DEFAULT NULL,
    conditions JSON DEFAULT NULL,
    UNIQUE KEY uniq_sequence_order (sequence_id, sort_order),
    INDEX idx_sequence (sequence_id),
    FOREIGN KEY (sequence_id) REFERENCES email_sequences(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE email_user_progress (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL,
    sequence_id INT UNSIGNED NOT NULL,
    current_step TINYINT UNSIGNED NOT NULL DEFAULT 1,
    next_send_at DATETIME DEFAULT NULL,
    last_sent_at DATETIME DEFAULT NULL,
    last_send_result ENUM('sent', 'skipped', 'error') DEFAULT NULL,
    last_send_error TEXT DEFAULT NULL,
    completed_at DATETIME DEFAULT NULL,
    status ENUM('running', 'completed', 'paused', 'cancelled') NOT NULL DEFAULT 'running',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uniq_user_sequence (user_id, sequence_id),
    INDEX idx_status_next (status, next_send_at),
    INDEX idx_user (user_id),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (sequence_id) REFERENCES email_sequences(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO email_sequences (slug, name, description, active) VALUES
('welcome_drip', 'Onboarding nouveaux inscrits', 'Séquence J+0 à J+30 pour activer les nouveaux comptes après vérification email.', 1);

INSERT INTO email_sequence_steps (sequence_id, sort_order, day_offset, template, subject) VALUES
(LAST_INSERT_ID(), 1, 0,  'welcome',     'Bienvenue sur Variable !'),
((SELECT id FROM email_sequences WHERE slug='welcome_drip'), 2, 2,  'drip_day2',   '3 livres qui marchent fort sur Variable'),
((SELECT id FROM email_sequences WHERE slug='welcome_drip'), 3, 7,  'drip_day7',   "Et si tu lisais sans limite pour 3$/mois ?"),
((SELECT id FROM email_sequences WHERE slug='welcome_drip'), 4, 14, 'drip_day14',  'Les nouveautés Variable'),
((SELECT id FROM email_sequences WHERE slug='welcome_drip'), 5, 30, 'drip_day30',  "On t''a oublié ? Tiens, -20% pour revenir.");

-- =============================================================================
-- Migration 010 — Historique envois emails
-- =============================================================================

CREATE TABLE email_log (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED DEFAULT NULL,
    to_email VARCHAR(255) NOT NULL,
    template VARCHAR(80) DEFAULT NULL,
    subject VARCHAR(300),
    sequence_id INT UNSIGNED DEFAULT NULL,
    sequence_step TINYINT UNSIGNED DEFAULT NULL,
    result ENUM('sent', 'error') NOT NULL DEFAULT 'sent',
    error_message TEXT DEFAULT NULL,
    provider_id VARCHAR(100) DEFAULT NULL,
    sent_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_user (user_id),
    INDEX idx_template (template),
    INDEX idx_sent_at (sent_at),
    INDEX idx_result (result),
    INDEX idx_sequence (sequence_id, sequence_step),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (sequence_id) REFERENCES email_sequences(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =============================================================================
-- Migration 011 — Seed des 11 auteurs classiques (sans compte user)
-- =============================================================================

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

SET FOREIGN_KEY_CHECKS=1;
