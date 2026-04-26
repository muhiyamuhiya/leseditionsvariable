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
// Notifications (routes spécifiques avant routes :id pour éviter les collisions)
$router->get('/notifications/api/recent', 'NotificationController@apiRecent');
$router->get('/notifications/api/count', 'NotificationController@apiCount');
$router->post('/notifications/lire-toutes', 'NotificationController@markAllRead');
$router->post('/notifications/:id/lire', 'NotificationController@markRead');
$router->post('/notifications/:id/supprimer', 'NotificationController@destroy');
$router->get('/notifications', 'NotificationController@index');

// Chat (visiteur et user connecté)
$router->post('/chat/send', 'ChatController@send');
$router->post('/chat/leave-email', 'ChatController@leaveEmail');
$router->get('/chat/conversation/:id', 'ChatController@getConversation');

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

// Services éditoriaux — page publique
$router->get('/services-editoriaux', 'EditorialController@publicServices');

// Services éditoriaux — auteur (routes spécifiques avant :id pour éviter collisions)
$router->get('/auteur/services-editoriaux', 'EditorialController@servicesList');
$router->get('/auteur/services-editoriaux/:slug/commander', 'EditorialController@orderForm');
$router->post('/auteur/services-editoriaux/:slug/commander', 'EditorialController@createOrder');
$router->get('/auteur/services-editoriaux/:slug', 'EditorialController@serviceDetail');
$router->get('/auteur/mes-commandes-editoriales', 'EditorialController@myOrders');
$router->get('/auteur/mes-commandes-editoriales/:id/payer', 'EditorialController@payOrder');
$router->get('/auteur/mes-commandes-editoriales/:id', 'EditorialController@orderDetail');

// Paiement commandes éditoriales
$router->get('/paiement/editorial/:id/stripe', 'PaymentController@payEditorialStripe');
$router->get('/paiement/editorial/:id/moneyfusion', 'PaymentController@payEditorialMoneyFusion');

// Téléchargement sécurisé fichiers éditoriaux
$router->get('/editorial/file/:type/:filename', 'EditorialController@serveFile');

// Services éditoriaux — admin
$router->get('/admin/services-editoriaux', 'AdminController@editorialOrdersList');
$router->get('/admin/services-editoriaux/:id', 'AdminController@editorialOrderDetail');
$router->post('/admin/services-editoriaux/:id/devis', 'AdminController@sendQuote');
$router->post('/admin/services-editoriaux/:id/statut', 'AdminController@updateOrderStatus');
$router->post('/admin/services-editoriaux/:id/livraison', 'AdminController@uploadDelivery');

// Dashboard auteur (racine + sous-routes)
$router->get('/auteur', 'AuthorDashboardController@dashboard');
$router->get('/auteur/candidater', 'AuthorDashboardController@showApplication');
$router->post('/auteur/candidater', 'AuthorDashboardController@submitApplication');
$router->get('/auteur/livres/nouveau', 'AuthorDashboardController@createBook');
$router->post('/auteur/livres/nouveau', 'AuthorDashboardController@storeBook');
$router->get('/auteur/livres/:slug/preview', 'AuthorDashboardController@previewBook');
$router->get('/auteur/livres/:id/editer', 'AuthorDashboardController@editBook');
$router->post('/auteur/livres/:id/editer', 'AuthorDashboardController@updateBook');
$router->get('/auteur/livres', 'AuthorDashboardController@books');
$router->get('/auteur/ventes', 'AuthorDashboardController@sales');
$router->get('/auteur/revenus', 'AuthorDashboardController@showRevenues');
$router->post('/auteur/versements/demander', 'AuthorDashboardController@requestPayout');
$router->get('/auteur/versements', 'AuthorDashboardController@payouts');
$router->get('/auteur/profil', 'AuthorDashboardController@profile');
$router->post('/auteur/profil', 'AuthorDashboardController@updateProfile');
$router->get('/auteur/dashboard', 'AuthorDashboardController@dashboard');

// Page auteur publique (APRÈS les routes /auteur/* spécifiques)
// Note : déplacée ici pour que /auteur/:slug ne matche pas "candidater", "livres", etc.

// Pages statiques
$router->get('/a-propos', 'PageController@aPropos');
$router->get('/presse', 'PageController@presse');
$router->get('/contact', 'PageController@contact');
$router->get('/publier', 'PageController@publier');
$router->get('/auteurs', 'PageController@auteurs');
$router->get('/auteurs/comment-ca-marche', 'PageController@commentCaMarche');
$router->get('/aide', 'PageController@aide');
$router->get('/newsletter', 'PageController@newsletterPage');
$router->post('/newsletter/subscribe', 'NewsletterController@subscribe');
$router->get('/blog', 'BlogController@index');
$router->get('/blog/:slug', 'BlogController@show');
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
$router->post('/admin/auteurs/ajax-create', 'AdminController@authorAjaxCreate');
$router->get('/admin/auteurs/:id/editer', 'AdminController@authorEdit');
$router->post('/admin/auteurs/:id/editer', 'AdminController@authorUpdate');
$router->get('/admin/candidatures', 'AdminController@authorCandidatures');
$router->get('/admin/candidatures/:id', 'AdminController@authorCandidatureShow');
$router->post('/admin/candidatures/:id/valider', 'AdminController@authorValidate');
$router->post('/admin/candidatures/:id/refuser', 'AdminController@authorRefuse');
$router->get('/admin/livres/:slug/preview', 'AdminController@bookPreview');
$router->post('/admin/livres/:id/publier', 'AdminController@bookPublish');
$router->get('/admin/lecteurs/:id/editer', 'AdminController@userDetail');
$router->post('/admin/lecteurs/:id/supprimer', 'AdminController@deleteUser');
$router->post('/admin/lecteurs/:id/restaurer', 'AdminController@restoreUser');
$router->get('/admin/lecteurs/:id', 'AdminController@userDetail');
$router->get('/admin/lecteurs', 'AdminController@usersList');
$router->get('/admin/categories', 'AdminController@categories');
$router->post('/admin/categories', 'AdminController@categoriesUpdate');
$router->get('/admin/abonnements', 'AdminController@subscriptions');
$router->get('/admin/ventes', 'AdminController@sales');
$router->get('/admin/versements', 'AdminController@payouts');
$router->post('/admin/versements/:id/payer', 'AdminController@payoutMarkPaid');
$router->get('/admin/finances', 'AdminController@showFinances');
$router->post('/admin/finances/:id/traiter', 'AdminController@processPayout');
$router->post('/admin/finances/:id/refuser', 'AdminController@rejectPayout');
$router->get('/admin/parametres', 'AdminController@settings');
$router->post('/admin/parametres', 'AdminController@settingsUpdate');
$router->get('/admin/journal', 'AdminController@auditLog');

// Admin Emails — dashboard (templates / sequences / sent)
$router->post('/admin/emails/preview/:template/test', 'AdminEmailController@sendTest');
$router->get('/admin/emails/preview/:template', 'AdminEmailController@preview');
$router->post('/admin/emails/sequences/:id/toggle', 'AdminEmailController@toggleSequence');
$router->get('/admin/emails/sequences', 'AdminEmailController@sequences');
$router->get('/admin/emails/sent', 'AdminEmailController@sent');
$router->get('/admin/emails', 'AdminEmailController@index');

// Admin Chat (dashboard + CRUD responses)
$router->get('/admin/chat/api/unread-count', 'AdminChatController@apiUnreadCount');
$router->get('/admin/chat/responses', 'AdminChatController@responses');
$router->post('/admin/chat/responses', 'AdminChatController@responseStore');
$router->post('/admin/chat/responses/:id/supprimer', 'AdminChatController@responseDelete');
$router->post('/admin/chat/responses/:id', 'AdminChatController@responseUpdate');
$router->post('/admin/chat/reply/:id', 'AdminChatController@reply');
$router->post('/admin/chat/mark-read/:id', 'AdminChatController@markRead');
$router->post('/admin/chat/archive/:id', 'AdminChatController@archive');
$router->get('/admin/chat', 'AdminChatController@index');

// API
$router->get('/api/recherche', 'BookController@searchApi');

$router->dispatch();
