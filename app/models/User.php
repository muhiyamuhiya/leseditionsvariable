<?php
namespace App\Models;

use App\Lib\Database;

/**
 * Modèle User
 * Gère toutes les opérations liées aux utilisateurs
 */
class User extends BaseModel
{
    protected string $table = 'users';

    /**
     * Trouver un utilisateur par son ID
     */
    public static function find(int $id): ?object
    {
        $db = Database::getInstance();
        $user = $db->fetch("SELECT * FROM users WHERE id = ?", [$id]);
        return $user ?: null;
    }

    /**
     * Trouver un utilisateur par son email
     */
    public static function findByEmail(string $email): ?object
    {
        $db = Database::getInstance();
        $user = $db->fetch("SELECT * FROM users WHERE email = ?", [$email]);
        return $user ?: null;
    }

    /**
     * Trouver un utilisateur par token (vérification email ou reset mot de passe)
     *
     * @param string $type  'verification' ou 'reset'
     * @param string $token Le token à rechercher
     */
    public static function findByToken(string $type, string $token): ?object
    {
        $db = Database::getInstance();

        if ($type === 'verification') {
            $user = $db->fetch(
                "SELECT * FROM users WHERE token_verification = ? AND email_verifie = 0",
                [$token]
            );
        } elseif ($type === 'reset') {
            $user = $db->fetch(
                "SELECT * FROM users WHERE token_reset_password = ? AND token_reset_expiration > NOW()",
                [$token]
            );
        } else {
            return null;
        }

        return $user ?: null;
    }

    /**
     * Créer un nouvel utilisateur
     * Génère automatiquement le hash du mot de passe, le code de parrainage et le token de vérification
     *
     * @param array $data Données du formulaire (doit contenir 'password' en clair)
     * @return int|false ID de l'utilisateur créé ou false
     */
    public static function create(array $data): int|false
    {
        $db = Database::getInstance();

        // Hasher le mot de passe
        $passwordHash = password_hash($data['password'], PASSWORD_BCRYPT);
        unset($data['password']);

        // Générer un code de parrainage unique
        $codeParrainage = self::generateUniqueParrainageCode();

        // Générer le token de vérification email
        $tokenVerification = bin2hex(random_bytes(32));

        // Construire les données à insérer
        $insertData = [
            'email'              => $data['email'],
            'password_hash'      => $passwordHash,
            'prenom'             => $data['prenom'],
            'nom'                => $data['nom'],
            'role'               => $data['role'] ?? 'lecteur',
            'pays'               => $data['pays'] ?? 'CD',
            'devise_preferee'    => $data['devise_preferee'] ?? 'USD',
            'code_parrainage'    => $codeParrainage,
            'token_verification' => $tokenVerification,
            'email_verifie'      => 0,
            'actif'              => 1,
            'accepte_cgu_at'     => date('Y-m-d H:i:s'),
            'accepte_newsletter' => $data['accepte_newsletter'] ?? 0,
        ];

        // Gérer le parrainage si un code est fourni
        if (!empty($data['code_parrain'])) {
            $parrain = $db->fetch(
                "SELECT id FROM users WHERE code_parrainage = ?",
                [$data['code_parrain']]
            );
            if ($parrain) {
                $insertData['parrain_id'] = $parrain->id;
            }
        }

        return $db->insert('users', $insertData);
    }

    /**
     * Mettre à jour le mot de passe d'un utilisateur
     */
    public static function updatePassword(int $userId, string $newPassword): bool
    {
        $db = Database::getInstance();
        $hash = password_hash($newPassword, PASSWORD_BCRYPT);
        $result = $db->update('users', ['password_hash' => $hash], 'id = ?', [$userId]);
        return $result !== false;
    }

    /**
     * Incrémenter le compteur de tentatives échouées
     * Bloque le compte pendant 15 minutes après 5 tentatives
     */
    public static function incrementFailedAttempts(int $userId): void
    {
        $db = Database::getInstance();
        $user = self::find($userId);
        if (!$user) return;

        $attempts = $user->nombre_tentatives_echec + 1;
        $data = ['nombre_tentatives_echec' => $attempts];

        // Bloquer pendant 15 minutes après 5 tentatives
        if ($attempts >= 5) {
            $data['bloque_jusqu_a'] = date('Y-m-d H:i:s', strtotime('+15 minutes'));
        }

        $db->update('users', $data, 'id = ?', [$userId]);
    }

    /**
     * Remettre à zéro le compteur de tentatives échouées
     */
    public static function resetFailedAttempts(int $userId): void
    {
        $db = Database::getInstance();
        $db->update('users', [
            'nombre_tentatives_echec' => 0,
            'bloque_jusqu_a'         => null,
            'derniere_connexion'     => date('Y-m-d H:i:s'),
        ], 'id = ?', [$userId]);
    }

    /**
     * Vérifier si un utilisateur est bloqué
     */
    public static function isBlocked(int $userId): bool
    {
        $user = self::find($userId);
        if (!$user || !$user->bloque_jusqu_a) {
            return false;
        }
        return strtotime($user->bloque_jusqu_a) > time();
    }

    /**
     * Marquer l'email comme vérifié
     */
    public static function verifyEmail(int $userId): bool
    {
        $db = Database::getInstance();
        $result = $db->update('users', [
            'email_verifie'      => 1,
            'token_verification' => null,
        ], 'id = ?', [$userId]);
        return $result !== false;
    }

    /**
     * Générer un token de réinitialisation de mot de passe (valide 1 heure)
     * Retourne le token généré
     */
    public static function setResetToken(int $userId): string
    {
        $db = Database::getInstance();
        $token = bin2hex(random_bytes(32));
        $expiration = date('Y-m-d H:i:s', strtotime('+1 hour'));

        $db->update('users', [
            'token_reset_password'   => $token,
            'token_reset_expiration' => $expiration,
        ], 'id = ?', [$userId]);

        return $token;
    }

    /**
     * Supprimer le token de réinitialisation après utilisation
     */
    public static function clearResetToken(int $userId): void
    {
        $db = Database::getInstance();
        $db->update('users', [
            'token_reset_password'   => null,
            'token_reset_expiration' => null,
        ], 'id = ?', [$userId]);
    }

    /**
     * Générer un code de parrainage unique au format VAR + 6 caractères
     */
    private static function generateUniqueParrainageCode(): string
    {
        $db = Database::getInstance();

        do {
            $code = 'VAR' . strtoupper(bin2hex(random_bytes(3)));
            $exists = $db->fetch(
                "SELECT id FROM users WHERE code_parrainage = ?",
                [$code]
            );
        } while ($exists);

        return $code;
    }
}
