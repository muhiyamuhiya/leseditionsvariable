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

// Paiements — achat livre
$router->get('/achat/livre/:id/stripe', 'PaymentController@payWithStripe');
$router->get('/achat/livre/:id/moneyfusion', 'PaymentController@payWithMoneyFusion');
$router->get('/achat/livre/:id', 'PaymentController@choosePaymentMethod');

// Paiements — abonnement
$router->get('/abonnement/souscrire/:plan/stripe', 'PaymentController@subscriptionStripe');
$router->get('/abonnement/souscrire/:plan/moneyfusion', 'PaymentController@subscriptionMoneyFusion');
$router->get('/abonnement/souscrire/:plan', 'PaymentController@subscriptionChoose');
$router->get('/abonnement/succes', 'PaymentController@subscriptionSuccess');

// Paiements — retour + webhooks
$router->get('/paiement/succes', 'PaymentController@success');
$router->get('/paiement/echec', 'PaymentController@failed');
$router->get('/paiement/moneyfusion/retour', 'PaymentController@moneyFusionReturn');
$router->post('/webhook/stripe', 'PaymentController@stripeWebhook');
$router->post('/webhook/moneyfusion', 'PaymentController@moneyFusionWebhook');

// Liseuse PDF (ordre important : routes spécifiques en premier)
$router->post('/lire/progress', 'ReaderController@saveProgress');
$router->get('/lire/pdf/:sessionToken/:fileType', 'ReaderController@streamPDF');
$router->get('/lire/:slug/extrait', 'ReaderController@readExtrait');
$router->get('/lire/:slug', 'ReaderController@read');

// Mon compte (dashboard lecteur)
$router->get('/mon-compte/profil', 'AccountController@profile');
$router->post('/mon-compte/profil', 'AccountController@updateProfile');
$router->post('/mon-compte/password', 'AccountController@updatePassword');
$router->get('/mon-compte/favoris', 'AccountController@favorites');
$router->get('/mon-compte/abonnement/annuler', 'AccountController@cancelSubscriptionForm');
$router->post('/mon-compte/abonnement/annuler', 'AccountController@cancelSubscription');
$router->post('/mon-compte/abonnement/reactiver', 'AccountController@reactivateSubscription');
$router->get('/mon-compte/abonnement', 'AccountController@subscription');
$router->post('/mon-compte/supprimer-demande', 'AccountController@requestDeletion');
$router->get('/supprimer-compte/confirmer/:token', 'AuthController@confirmDeletionForm');
$router->post('/supprimer-compte/confirmer/:token', 'AuthController@confirmDeletion');
$router->get('/mon-compte', 'AccountController@index');
$router->get('/ma-bibliotheque', 'AccountController@index');

// Abonnement
$router->get('/abonnement', 'PageController@abonnement');

// Dashboard auteur (racine + sous-routes)
$router->get('/auteur', 'AuthorDashboardController@dashboard');
$router->get('/auteur/candidater', 'AuthorDashboardController@showApplication');
$router->post('/auteur/candidater', 'AuthorDashboardController@submitApplication');
$router->get('/auteur/livres/nouveau', 'AuthorDashboardController@createBook');
$router->post('/auteur/livres/nouveau', 'AuthorDashboardController@storeBook');
$router->get('/auteur/livres/:id/editer', 'AuthorDashboardController@editBook');
$router->post('/auteur/livres/:id/editer', 'AuthorDashboardController@updateBook');
$router->get('/auteur/livres', 'AuthorDashboardController@books');
$router->get('/auteur/ventes', 'AuthorDashboardController@sales');
$router->get('/auteur/versements', 'AuthorDashboardController@payouts');
$router->get('/auteur/profil', 'AuthorDashboardController@profile');
$router->post('/auteur/profil', 'AuthorDashboardController@updateProfile');
$router->get('/auteur/dashboard', 'AuthorDashboardController@dashboard');

// Page auteur publique (APRÈS les routes /auteur/* spécifiques)
// Note : déplacée ici pour que /auteur/:slug ne matche pas "candidater", "livres", etc.

// Pages statiques
$router->get('/a-propos', 'PageController@aPropos');
$router->get('/contact', 'PageController@contact');
$router->get('/publier', 'PageController@publier');
$router->get('/devenir-auteur', 'PageController@publier');
$router->get('/cgu', 'PageController@cgu');
$router->get('/cgv', 'PageController@cgv');
$router->get('/mentions-legales', 'PageController@mentions');
$router->get('/confidentialite', 'PageController@confidentialite');

// Page auteur publique
$router->get('/auteur/:slug', 'AuthorController@show');

// Images storage
$router->get('/image/covers/:filename', 'ImageController@serveCover');
$router->get('/image/authors/:filename', 'ImageController@serveAuthorPhoto');
$router->get('/image/users/:filename', 'ImageController@serveUserPhoto');

// Admin
$router->get('/admin', 'AdminController@dashboard');
$router->get('/admin/livres', 'AdminController@books');
$router->get('/admin/livres/nouveau', 'AdminController@bookCreate');
$router->post('/admin/livres/nouveau', 'AdminController@bookStore');
$router->get('/admin/livres/:id/editer', 'AdminController@bookEdit');
$router->post('/admin/livres/:id/editer', 'AdminController@bookUpdate');
$router->post('/admin/livres/:id/supprimer', 'AdminController@bookDelete');
$router->get('/admin/auteurs', 'AdminController@authors');
$router->get('/admin/auteurs/:id/editer', 'AdminController@authorEdit');
$router->post('/admin/auteurs/:id/editer', 'AdminController@authorUpdate');
$router->get('/admin/candidatures', 'AdminController@authorCandidatures');
$router->get('/admin/candidatures/:id', 'AdminController@authorCandidatureShow');
$router->post('/admin/candidatures/:id/valider', 'AdminController@authorValidate');
$router->post('/admin/candidatures/:id/refuser', 'AdminController@authorRefuse');
$router->get('/admin/livres/:id/apercu', 'AdminController@bookPreview');
$router->post('/admin/livres/:id/publier', 'AdminController@bookPublish');
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
