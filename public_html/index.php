<?php
/**
 * Point d'entrée unique de l'application
 * Toutes les requêtes HTTP passent par ce fichier
 */

// Charger le bootstrap (un cran au-dessus de public_html/)
require_once __DIR__ . '/../bootstrap.php';

// Créer le routeur
$router = new App\Lib\Router();

// ============================================
// Définition des routes
// ============================================

// Page d'accueil
$router->get('/', 'HomeController@index');

// ============================================
// Dispatcher la requête
// ============================================
$router->dispatch();
