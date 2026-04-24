<?php
/**
 * Point d'entrée unique de l'application
 */
require_once __DIR__ . '/../bootstrap.php';

$router = new App\Lib\Router();

// Page d'accueil
$router->get('/', 'HomeController@index');

// Authentification
$router->get('/connexion', 'AuthController@showLogin');
$router->post('/connexion', 'AuthController@processLogin');
$router->get('/inscription', 'AuthController@showRegister');
$router->post('/inscription', 'AuthController@processRegister');
$router->post('/deconnexion', 'AuthController@logout');
$router->get('/verifier-email/:token', 'AuthController@verifyEmail');
$router->get('/mot-de-passe-oublie', 'AuthController@showForgotPassword');
$router->post('/mot-de-passe-oublie', 'AuthController@processForgotPassword');
$router->get('/reset-password/:token', 'AuthController@showResetPassword');
$router->post('/reset-password/:token', 'AuthController@processResetPassword');

// Catalogue
$router->get('/catalogue', 'BookController@catalogue');
$router->get('/catalogue/categorie/:slug', 'BookController@byCategory');

// Livre détail + avis + favoris
$router->get('/livre/:slug', 'BookController@show');
$router->post('/livre/:slug/avis', 'BookController@submitReview');
$router->post('/livre/:slug/favori', 'BookController@toggleFavorite');

// Liseuse PDF (ordre important : routes spécifiques en premier)
$router->post('/lire/progress', 'ReaderController@saveProgress');
$router->get('/lire/pdf/:sessionToken/:fileType', 'ReaderController@streamPDF');
$router->get('/lire/:slug/extrait', 'ReaderController@readExtrait');
$router->get('/lire/:slug', 'ReaderController@read');

// Mon compte (dashboard lecteur)
$router->get('/mon-compte', 'AccountController@index');
$router->get('/ma-bibliotheque', 'AccountController@index');

// Abonnement
$router->get('/abonnement', 'PageController@abonnement');

// Pages statiques
$router->get('/a-propos', 'PageController@aPropos');
$router->get('/contact', 'PageController@contact');
$router->get('/publier', 'PageController@publier');
$router->get('/cgu', 'PageController@cgu');
$router->get('/cgv', 'PageController@cgv');
$router->get('/mentions-legales', 'PageController@mentions');
$router->get('/confidentialite', 'PageController@confidentialite');

// Admin
$router->get('/admin', 'AdminController@dashboard');
$router->get('/admin/livres', 'AdminController@books');
$router->get('/admin/livres/nouveau', 'AdminController@bookCreate');
$router->post('/admin/livres/nouveau', 'AdminController@bookStore');
$router->get('/admin/livres/:id/editer', 'AdminController@bookEdit');
$router->post('/admin/livres/:id/editer', 'AdminController@bookUpdate');
$router->post('/admin/livres/:id/supprimer', 'AdminController@bookDelete');
$router->get('/admin/auteurs', 'AdminController@authors');
$router->get('/admin/candidatures', 'AdminController@authorCandidatures');
$router->post('/admin/candidatures/:id/valider', 'AdminController@authorValidate');
$router->post('/admin/candidatures/:id/refuser', 'AdminController@authorRefuse');
$router->get('/admin/lecteurs', 'AdminController@readers');
$router->get('/admin/categories', 'AdminController@categories');
$router->post('/admin/categories', 'AdminController@categoriesUpdate');
$router->get('/admin/abonnements', 'AdminController@subscriptions');
$router->get('/admin/ventes', 'AdminController@sales');
$router->get('/admin/versements', 'AdminController@payouts');
$router->post('/admin/versements/:id/payer', 'AdminController@payoutMarkPaid');
$router->get('/admin/parametres', 'AdminController@settings');
$router->post('/admin/parametres', 'AdminController@settingsUpdate');
$router->get('/admin/journal', 'AdminController@auditLog');

// API
$router->get('/api/recherche', 'BookController@searchApi');

$router->dispatch();
