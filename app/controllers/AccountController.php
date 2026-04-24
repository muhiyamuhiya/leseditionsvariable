<?php
namespace App\Controllers;

use App\Lib\Auth;
use App\Lib\CSRF;
use App\Lib\Database;
use App\Lib\Session;

/**
 * Dashboard lecteur
 */
class AccountController extends BaseController
{
    public function index(): void
    {
        Auth::requireLogin();
        $user = Auth::user();
        $db = Database::getInstance();

        $livres = $db->fetchAll(
            "SELECT b.titre, b.slug, b.nombre_pages, b.prix_unitaire_usd, b.author_id,
                    COALESCE(a.nom_plume, CONCAT(u.prenom, ' ', u.nom)) AS author_display,
                    ub.date_ajout, ub.favori, ub.source,
                    rp.derniere_page_lue, rp.pourcentage_complete, b.id as book_id,
                    c.nom AS category_nom
             FROM user_books ub
             JOIN books b ON ub.book_id = b.id
             JOIN authors a ON b.author_id = a.id
             JOIN users u ON a.user_id = u.id
             LEFT JOIN categories c ON b.category_id = c.id
             LEFT JOIN reading_progress rp ON rp.user_id = ub.user_id AND rp.book_id = ub.book_id
             WHERE ub.user_id = ?
             ORDER BY ub.date_ajout DESC",
            [$user->id]
        );

        $abonnement = $db->fetch(
            "SELECT * FROM subscriptions WHERE user_id = ? AND statut = 'actif' AND date_fin > NOW() ORDER BY date_fin DESC LIMIT 1",
            [$user->id]
        );

        $stats = $db->fetch(
            "SELECT COUNT(*) as nb_livres, SUM(total_pages_lues) as pages_lues, SUM(total_temps_lecture) as temps_total
             FROM reading_progress WHERE user_id = ?",
            [$user->id]
        );

        $nbFavoris = (int) ($db->fetch(
            "SELECT COUNT(*) AS n FROM user_books ub JOIN books b ON b.id = ub.book_id
             WHERE ub.user_id = ? AND ub.favori = 1 AND b.statut = 'publie'",
            [$user->id]
        )->n ?? 0);

        $this->view('account/index', [
            'titre'      => 'Mon compte',
            'user'       => $user,
            'livres'     => $livres,
            'abonnement' => $abonnement,
            'stats'      => $stats,
            'nbFavoris'  => $nbFavoris,
        ]);
    }

    /**
     * Page "Mes favoris" — livres marqués favoris par l'utilisateur
     */
    public function favorites(): void
    {
        Auth::requireLogin();
        $userId = Auth::id();
        $db = Database::getInstance();

        $favoris = $db->fetchAll(
            "SELECT b.id, b.slug, b.titre, b.prix_unitaire_usd, b.couverture_url_web,
                    COALESCE(a.nom_plume, CONCAT(u.prenom, ' ', u.nom)) AS author_display,
                    a.slug AS author_slug,
                    c.nom AS category_nom,
                    c.slug AS category_slug,
                    ub.date_ajout AS date_ajout_favori
             FROM user_books ub
             JOIN books b ON ub.book_id = b.id
             JOIN authors a ON b.author_id = a.id
             JOIN users u ON a.user_id = u.id
             LEFT JOIN categories c ON b.category_id = c.id
             WHERE ub.user_id = ?
               AND ub.favori = 1
               AND b.statut = 'publie'
             ORDER BY ub.date_ajout DESC",
            [$userId]
        );

        $this->view('account/favorites', [
            'titre'   => 'Mes favoris',
            'favoris' => $favoris,
        ]);
    }

    /**
     * Page profil lecteur
     */
    public function profile(): void
    {
        Auth::requireLogin();
        $user = Auth::user();

        $this->view('account/profile', [
            'titre' => 'Mon profil',
            'user'  => $user,
        ]);
    }

    /**
     * Mise à jour du profil
     */
    public function updateProfile(): void
    {
        Auth::requireLogin();
        CSRF::check();
        $db = Database::getInstance();
        $userId = Auth::id();

        $data = [
            'prenom'            => trim($_POST['prenom'] ?? ''),
            'nom'               => trim($_POST['nom'] ?? ''),
            'telephone'         => trim($_POST['telephone'] ?? '') ?: null,
            'pays'              => trim($_POST['pays'] ?? '') ?: null,
            'devise_preferee'   => trim($_POST['devise_preferee'] ?? 'USD'),
            'accepte_newsletter'=> isset($_POST['accepte_newsletter']) ? 1 : 0,
        ];

        if (empty($data['prenom']) || empty($data['nom'])) {
            Session::flash('error', 'Prénom et nom sont obligatoires.');
            redirect('/mon-compte/profil');
            return;
        }

        // Upload photo
        if (!empty($_FILES['photo']['tmp_name'])) {
            $file = $_FILES['photo'];
            $allowed = ['image/jpeg', 'image/png', 'image/webp'];
            if (in_array($file['type'], $allowed) && $file['size'] <= 2 * 1024 * 1024) {
                $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
                $filename = 'user-' . $userId . '-' . time() . '.' . $ext;
                $absPath = BASE_PATH . '/storage/users/' . $filename;
                if (!is_dir(dirname($absPath))) mkdir(dirname($absPath), 0755, true);
                move_uploaded_file($file['tmp_name'], $absPath);
                $data['avatar_url'] = '/image/users/' . $filename;
            }
        }

        $db->update('users', $data, 'id = ?', [$userId]);

        Session::flash('success', 'Profil mis à jour.');
        redirect('/mon-compte/profil');
    }

    /**
     * Changement de mot de passe
     */
    public function updatePassword(): void
    {
        Auth::requireLogin();
        CSRF::check();
        $db = Database::getInstance();
        $userId = Auth::id();

        $current = $_POST['current_password'] ?? '';
        $new = $_POST['new_password'] ?? '';

        if (mb_strlen($new) < 8) {
            Session::flash('error', 'Le nouveau mot de passe doit contenir au moins 8 caractères.');
            redirect('/mon-compte/profil');
            return;
        }

        $user = $db->fetch("SELECT password_hash FROM users WHERE id = ?", [$userId]);
        if (!$user || !password_verify($current, $user->password_hash)) {
            Session::flash('error', 'Mot de passe actuel incorrect.');
            redirect('/mon-compte/profil');
            return;
        }

        $db->update('users', ['password_hash' => password_hash($new, PASSWORD_BCRYPT)], 'id = ?', [$userId]);

        Session::flash('success', 'Mot de passe changé avec succès.');
        redirect('/mon-compte/profil');
    }
}
