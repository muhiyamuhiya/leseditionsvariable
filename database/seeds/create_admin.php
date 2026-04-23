<?php
/**
 * Script de création du compte administrateur
 * Usage : php database/seeds/create_admin.php
 */

// Charger le bootstrap de l'application
require_once __DIR__ . '/../../bootstrap.php';

use App\Lib\Database;

echo "=== Création du compte administrateur ===" . PHP_EOL;
echo PHP_EOL;

try {
    $db = Database::getInstance();
    echo "Connexion à la base de données : OK" . PHP_EOL;
} catch (\Throwable $e) {
    echo "ERREUR : Impossible de se connecter à la base de données." . PHP_EOL;
    echo "Détail : " . $e->getMessage() . PHP_EOL;
    exit(1);
}

// Données du compte admin
$email = 'contact@variablefly.com';
$motDePasseClair = 'Variable2026!';
$passwordHash = password_hash($motDePasseClair, PASSWORD_BCRYPT);

// Vérifier si l'admin existe déjà
$existant = $db->fetch("SELECT id, email FROM users WHERE email = ?", [$email]);

if ($existant) {
    echo "Admin déjà existant (ID = {$existant->id}, email = {$existant->email})" . PHP_EOL;
    echo "Aucune modification effectuée." . PHP_EOL;
    exit(0);
}

// Créer le compte admin
$id = $db->insert('users', [
    'email'            => $email,
    'password_hash'    => $passwordHash,
    'prenom'           => 'Angello',
    'nom'              => 'Luvungu Muhiya',
    'role'             => 'admin',
    'email_verifie'    => 1,
    'pays'             => 'CA',
    'devise_preferee'  => 'USD',
    'code_parrainage'  => 'ANGELLO2026',
    'actif'            => 1,
    'accepte_cgu_at'   => date('Y-m-d H:i:s'),
]);

if ($id !== false) {
    echo "Admin créé avec succès, ID = {$id}" . PHP_EOL;
    echo "  Email    : {$email}" . PHP_EOL;
    echo "  Nom      : Angello Luvungu Muhiya" . PHP_EOL;
    echo "  Rôle     : admin" . PHP_EOL;
    echo "  Parrain  : ANGELLO2026" . PHP_EOL;
} else {
    echo "ERREUR : L'insertion a échoué. Vérifiez les logs dans logs/db.log" . PHP_EOL;
    exit(1);
}

echo PHP_EOL;
echo "=== Terminé ===" . PHP_EOL;
