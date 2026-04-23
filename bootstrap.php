<?php
/**
 * Bootstrap — Initialisation de l'application
 * Charge l'autoloader, les variables d'environnement, la session et les helpers
 */

// Chemin racine du projet
define('BASE_PATH', __DIR__);

// Charger l'autoloader PSR-4
require_once BASE_PATH . '/app/lib/Autoloader.php';
$autoloader = new App\Lib\Autoloader();
$autoloader->register();

// Charger les variables d'environnement
App\Lib\Env::load(BASE_PATH . '/.env');

// Charger les fonctions utilitaires (avant tout usage de env())
require_once BASE_PATH . '/app/helpers/functions.php';

// Configurer le fuseau horaire
date_default_timezone_set(env('TIMEZONE', 'Africa/Kinshasa'));

// Configuration des erreurs selon le mode debug
if (env('APP_DEBUG', 'false') === 'true') {
    error_reporting(E_ALL);
    ini_set('display_errors', '1');
} else {
    error_reporting(0);
    ini_set('display_errors', '0');
}

// Log des erreurs dans tous les cas
ini_set('log_errors', '1');
ini_set('error_log', BASE_PATH . '/logs/error.log');

// Démarrer la session sécurisée
App\Lib\Session::start();

// Charger les constantes du projet
require_once BASE_PATH . '/app/config/constants.php';
