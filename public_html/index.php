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

// Authentification
$router->get('/connexion', 'AuthController@showLogin');
$router->post('/connexion', 'AuthController@processLogin');
$router->get('/inscription', 'AuthController@showRegister');
$router->post('/inscription', 'AuthController@processRegister');
$router->post('/deconnexion', 'AuthController@logout');

// Catalogue
$router->get('/catalogue', 'BookController@catalogue');
$router->get('/catalogue/categorie/:slug', 'BookController@byCategory');

// Vérification email
$router->get('/verifier-email/:token', 'AuthController@verifyEmail');

// Mot de passe oublié / réinitialisation
$router->get('/mot-de-passe-oublie', 'AuthController@showForgotPassword');
$router->post('/mot-de-passe-oublie', 'AuthController@processForgotPassword');
$router->get('/reset-password/:token', 'AuthController@showResetPassword');
$router->post('/reset-password/:token', 'AuthController@processResetPassword');

// ============================================
// Dispatcher la requête
// ============================================
$router->dispatch();
