<?php
namespace App\Controllers;

use App\Lib\Auth;
use App\Lib\CSRF;
use App\Lib\Session;
use App\Lib\Mailer;
use App\Models\User;

/**
 * Contrôleur d'authentification
 * Gère inscription, connexion, déconnexion, vérification email et reset mot de passe
 */
class AuthController extends BaseController
{
    // =========================================================================
    // CONNEXION
    // =========================================================================

    /**
     * Afficher le formulaire de connexion
     */
    public function showLogin(): void
    {
        // Rediriger si déjà connecté
        if (Auth::check()) {
            redirect('/');
        }

        $this->view('auth/login', [
            'titre' => 'Connexion',
        ]);
    }

    /**
     * Traiter le formulaire de connexion
     */
    public function processLogin(): void
    {
        CSRF::check();

        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        // Validation basique
        if (empty($email) || empty($password)) {
            Session::flash('error', 'Veuillez remplir tous les champs.');
            Session::flash('old_email', $email);
            redirect('/connexion');
        }

        // Tentative de connexion
        if (Auth::login($email, $password)) {
            redirect('/');
        } else {
            Session::flash('old_email', $email);
            redirect('/connexion');
        }
    }

    // =========================================================================
    // INSCRIPTION
    // =========================================================================

    /**
     * Afficher le formulaire d'inscription
     */
    public function showRegister(): void
    {
        if (Auth::check()) {
            redirect('/');
        }

        $this->view('auth/register', [
            'titre'      => 'Inscription',
            'refCode'    => $_GET['ref'] ?? '',
        ]);
    }

    /**
     * Traiter le formulaire d'inscription
     */
    public function processRegister(): void
    {
        CSRF::check();

        $prenom           = trim($_POST['prenom'] ?? '');
        $nom              = trim($_POST['nom'] ?? '');
        $email            = trim($_POST['email'] ?? '');
        $password         = $_POST['password'] ?? '';
        $passwordConfirm  = $_POST['password_confirmation'] ?? '';
        $accepteCgu       = isset($_POST['accepte_cgu']);
        $accepteNewsletter = isset($_POST['accepte_newsletter']) ? 1 : 0;
        $codeParrain      = trim($_POST['code_parrain'] ?? '');

        // Conserver les anciennes valeurs en cas d'erreur
        $oldData = [
            'old_prenom'       => $prenom,
            'old_nom'          => $nom,
            'old_email'        => $email,
            'old_code_parrain' => $codeParrain,
            'old_newsletter'   => $accepteNewsletter,
        ];

        // Validation
        $errors = [];

        if (mb_strlen($prenom) < 2 || mb_strlen($prenom) > 100) {
            $errors[] = 'Le prénom doit contenir entre 2 et 100 caractères.';
        }

        if (mb_strlen($nom) < 2 || mb_strlen($nom) > 100) {
            $errors[] = 'Le nom doit contenir entre 2 et 100 caractères.';
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'L\'adresse email n\'est pas valide.';
        }

        if (mb_strlen($password) < 8) {
            $errors[] = 'Le mot de passe doit contenir au moins 8 caractères.';
        }

        if (!preg_match('/[A-Za-z]/', $password) || !preg_match('/[0-9]/', $password)) {
            $errors[] = 'Le mot de passe doit contenir au moins une lettre et un chiffre.';
        }

        if ($password !== $passwordConfirm) {
            $errors[] = 'Les mots de passe ne correspondent pas.';
        }

        if (!$accepteCgu) {
            $errors[] = 'Vous devez accepter les conditions générales d\'utilisation.';
        }

        // Vérifier que l'email n'existe pas déjà
        if (empty($errors) && User::findByEmail($email)) {
            $errors[] = 'Cette adresse email est déjà utilisée.';
        }

        // S'il y a des erreurs, rediriger avec les messages
        if (!empty($errors)) {
            Session::flash('errors', $errors);
            foreach ($oldData as $key => $value) {
                Session::flash($key, $value);
            }
            redirect('/inscription');
        }

        // Créer l'utilisateur
        $userId = User::create([
            'email'              => $email,
            'password'           => $password,
            'prenom'             => $prenom,
            'nom'                => $nom,
            'accepte_newsletter' => $accepteNewsletter,
            'code_parrain'       => $codeParrain,
        ]);

        if ($userId === false) {
            Session::flash('error', 'Une erreur est survenue lors de l\'inscription. Veuillez réessayer.');
            foreach ($oldData as $key => $value) {
                Session::flash($key, $value);
            }
            redirect('/inscription');
        }

        // Récupérer l'utilisateur créé pour envoyer l'email
        $user = User::find($userId);
        if ($user) {
            Mailer::sendVerificationEmail($user, $user->token_verification);
        }

        Session::flash('success', 'Inscription réussie ! Consultez votre boîte email pour activer votre compte.');
        redirect('/connexion');
    }

    // =========================================================================
    // DÉCONNEXION
    // =========================================================================

    /**
     * Déconnecter l'utilisateur
     */
    public function logout(): void
    {
        Auth::logout();

        // Redémarrer la session pour pouvoir envoyer un flash
        Session::start();
        Session::flash('success', 'Vous êtes déconnecté.');
        redirect('/');
    }

    // =========================================================================
    // VÉRIFICATION EMAIL
    // =========================================================================

    /**
     * Vérifier l'email via le token reçu par email
     */
    public function verifyEmail(string $token): void
    {
        $user = User::findByToken('verification', $token);

        if (!$user) {
            Session::flash('error', 'Lien de vérification invalide ou expiré.');
            redirect('/connexion');
        }

        User::verifyEmail($user->id);

        // Envoyer l'email de bienvenue
        $user->email_verifie = 1;
        Mailer::sendWelcomeEmail($user);

        Session::flash('success', 'Votre adresse email a été vérifiée ! Vous pouvez maintenant vous connecter.');
        redirect('/connexion');
    }

    // =========================================================================
    // MOT DE PASSE OUBLIÉ
    // =========================================================================

    /**
     * Afficher le formulaire de mot de passe oublié
     */
    public function showForgotPassword(): void
    {
        if (Auth::check()) {
            redirect('/');
        }

        $this->view('auth/forgot-password', [
            'titre' => 'Mot de passe oublié',
        ]);
    }

    /**
     * Traiter la demande de réinitialisation
     */
    public function processForgotPassword(): void
    {
        CSRF::check();

        $email = trim($_POST['email'] ?? '');

        // Message générique pour ne pas révéler si l'email existe
        Session::flash('success', 'Si cette adresse email est associée à un compte, vous recevrez un lien de réinitialisation.');

        if (!empty($email)) {
            $user = User::findByEmail($email);
            if ($user && $user->email_verifie && $user->actif) {
                $token = User::setResetToken($user->id);
                Mailer::sendPasswordResetEmail($user, $token);
            }
        }

        redirect('/mot-de-passe-oublie');
    }

    // =========================================================================
    // RÉINITIALISATION DU MOT DE PASSE
    // =========================================================================

    /**
     * Afficher le formulaire de réinitialisation
     */
    public function showResetPassword(string $token): void
    {
        $user = User::findByToken('reset', $token);

        if (!$user) {
            Session::flash('error', 'Lien de réinitialisation invalide ou expiré. Veuillez refaire une demande.');
            redirect('/mot-de-passe-oublie');
        }

        $this->view('auth/reset-password', [
            'titre' => 'Nouveau mot de passe',
            'token' => $token,
        ]);
    }

    /**
     * Traiter la réinitialisation du mot de passe
     */
    public function processResetPassword(string $token): void
    {
        CSRF::check();

        $user = User::findByToken('reset', $token);

        if (!$user) {
            Session::flash('error', 'Lien de réinitialisation invalide ou expiré.');
            redirect('/mot-de-passe-oublie');
        }

        $password        = $_POST['password'] ?? '';
        $passwordConfirm = $_POST['password_confirmation'] ?? '';

        $errors = [];

        if (mb_strlen($password) < 8) {
            $errors[] = 'Le mot de passe doit contenir au moins 8 caractères.';
        }

        if (!preg_match('/[A-Za-z]/', $password) || !preg_match('/[0-9]/', $password)) {
            $errors[] = 'Le mot de passe doit contenir au moins une lettre et un chiffre.';
        }

        if ($password !== $passwordConfirm) {
            $errors[] = 'Les mots de passe ne correspondent pas.';
        }

        if (!empty($errors)) {
            Session::flash('errors', $errors);
            redirect('/reset-password/' . $token);
        }

        User::updatePassword($user->id, $password);
        User::clearResetToken($user->id);

        Session::flash('success', 'Mot de passe modifié avec succès ! Vous pouvez maintenant vous connecter.');
        redirect('/connexion');
    }
}
