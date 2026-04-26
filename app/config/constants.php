<?php
/**
 * Constantes globales du projet
 * Valeurs métier utilisées dans toute l'application
 */

// Commission de la plateforme sur les ventes unitaires (30%)
// Voir migration 012 et settings.commission_variable_pct
define('COMMISSION_RATE', 0.30);

// Part auteur sur les ventes unitaires (70%)
define('AUTHOR_SHARE_RATE', 0.70);

// Seuil minimum (USD) pour qu'un auteur puisse demander un versement
define('PAYOUT_MIN_USD', 10.00);

// Part des revenus d'abonnement redistribuée aux auteurs (50%)
define('SUBSCRIPTION_POOL_RATE', 0.50);

// Seuil minimum de versement aux auteurs en dollars
define('MIN_PAYOUT_AMOUNT', 20);

// Prix des abonnements
define('SUB_ESSENTIAL_MONTHLY', 3);
define('SUB_ESSENTIAL_ANNUAL', 30);
define('SUB_PREMIUM_MONTHLY', 8);

// Pagination par défaut
define('ITEMS_PER_PAGE', 12);

// Taille maximale d'upload (50 Mo en octets)
define('MAX_UPLOAD_SIZE', 50 * 1024 * 1024);

// Extensions autorisées pour les livres
define('ALLOWED_BOOK_EXTENSIONS', ['pdf']);

// Extensions autorisées pour les images (couvertures, avatars)
define('ALLOWED_IMAGE_EXTENSIONS', ['jpg', 'jpeg', 'png', 'webp']);

// Nombre de pages d'aperçu gratuit
define('FREE_PREVIEW_PAGES', 10);

// Durée minimum de lecture par page en secondes (anti-abus)
define('MIN_READ_TIME_PER_PAGE', 10);

// Pourcentage minimum du livre lu pour compter dans le pool (anti-abus)
define('MIN_READ_PERCENTAGE', 70);
