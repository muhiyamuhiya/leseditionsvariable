<?php
namespace App\Controllers;

use App\Lib\Auth;
use App\Lib\CSRF;
use App\Lib\Database;
use App\Lib\Mailer;
use App\Lib\Session;
use App\Models\Subscription;

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
            "SELECT b.id, b.titre, b.slug, b.nombre_pages, b.prix_unitaire_usd, b.author_id,
                    b.couverture_url_web,
                    b.accessible_abonnement_essentiel, b.accessible_abonnement_premium,
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
               AND ub.source IN ('achat_unitaire','abonnement')
               AND b.statut = 'publie'
             ORDER BY ub.date_ajout DESC",
            [$user->id]
        );

        // Calculer le statut d'accès courant pour chaque livre
        foreach ($livres as $livre) {
            $livre->access_status = $this->computeAccessStatus($user, $livre);
        }

        $abonnement = Subscription::getActive($user->id);

        $stats = $db->fetch(
            "SELECT COUNT(*) as nb_livres, SUM(total_pages_lues) as pages_lues, SUM(total_temps_lecture) as temps_total
             FROM reading_progress WHERE user_id = ?",
            [$user->id]
        );

        // Le compteur "Livres lus" doit refléter la biblio (achat + abo), pas seulement reading_progress
        $nbBiblio = (int) ($db->fetch(
            "SELECT COUNT(*) AS n FROM user_books ub JOIN books b ON b.id = ub.book_id
             WHERE ub.user_id = ? AND ub.source IN ('achat_unitaire','abonnement') AND b.statut = 'publie'",
            [$user->id]
        )->n ?? 0);

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
            'nbBiblio'   => $nbBiblio,
            'nbFavoris'  => $nbFavoris,
        ]);
    }

    /**
     * Statut d'accès courant à un livre dans la bibliothèque
     */
    private function computeAccessStatus(object $user, object $livre): array
    {
        if ($livre->source === 'achat_unitaire') {
            $progress = (float) ($livre->pourcentage_complete ?? 0);
            return [
                'can_read'    => true,
                'badge_label' => 'Acheté',
                'badge_color' => 'amber',
                'cta_label'   => $progress > 0 ? 'Continuer' : 'Lire',
                'cta_url'     => '/lire/' . $livre->slug,
            ];
        }

        if ($livre->source === 'abonnement') {
            // canReadFull re-vérifie tier + date_fin de l'abo courant
            $canRead = \App\Lib\BookAccess::canReadFull($user, (int) $livre->id);
            if ($canRead) {
                $progress = (float) ($livre->pourcentage_complete ?? 0);
                return [
                    'can_read'    => true,
                    'badge_label' => 'Avec ton abonnement',
                    'badge_color' => 'blue',
                    'cta_label'   => $progress > 0 ? 'Continuer' : 'Lire',
                    'cta_url'     => '/lire/' . $livre->slug,
                ];
            }
            // Abo expiré ou tier insuffisant
            return [
                'can_read'    => false,
                'badge_label' => 'Renouvelle ton abonnement',
                'badge_color' => 'gray',
                'cta_label'   => 'Renouveler',
                'cta_url'     => '/abonnement',
            ];
        }

        return ['can_read' => false, 'badge_label' => '', 'badge_color' => 'gray', 'cta_label' => '', 'cta_url' => '#'];
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

    // =====================================================================
    // ABONNEMENT — visualisation, annulation, réactivation
    // =====================================================================

    public function subscription(): void
    {
        Auth::requireLogin();
        $userId = Auth::id();
        $db = Database::getInstance();

        $sub = $db->fetch(
            "SELECT * FROM subscriptions
             WHERE user_id = ? AND date_fin >= NOW()
             ORDER BY date_fin DESC LIMIT 1",
            [$userId]
        );

        $planLabel = $sub ? (Subscription::PLANS[$sub->type]['label'] ?? $sub->type) : null;
        $tier = $sub ? (Subscription::PLANS[$sub->type]['tier'] ?? null) : null;

        $this->view('account/subscription', [
            'titre'     => 'Mon abonnement',
            'sub'       => $sub,
            'planLabel' => $planLabel,
            'tier'      => $tier,
        ]);
    }

    public function cancelSubscriptionForm(): void
    {
        Auth::requireLogin();
        $sub = Subscription::getActive(Auth::id());
        if (!$sub || $sub->statut !== 'actif') {
            redirect('/mon-compte/abonnement');
            return;
        }

        $this->view('account/cancel-subscription', [
            'titre' => 'Annuler mon abonnement',
            'sub'   => $sub,
            'user'  => Auth::user(),
        ]);
    }

    public function cancelSubscription(): void
    {
        Auth::requireLogin();
        CSRF::check();
        $userId = Auth::id();

        $motif = $_POST['motif'] ?? 'autre';
        $raison = trim($_POST['raison'] ?? '') ?: null;

        $motifsValides = ['trop_cher','pas_le_temps','catalogue','alternative','technique','autre'];
        if (!in_array($motif, $motifsValides, true)) {
            $motif = 'autre';
        }

        $sub = Subscription::getActive($userId);
        if (!$sub || $sub->statut !== 'actif') {
            Session::flash('error', 'Aucun abonnement actif à annuler.');
            redirect('/mon-compte/abonnement');
            return;
        }

        if (Subscription::cancel($userId, $motif, $raison)) {
            Mailer::sendSubscriptionCancellation(Auth::user(), $sub->date_fin);
            Session::flash('success', 'Ton abonnement est annulé. Tu gardes l\'accès jusqu\'au ' . date('d/m/Y', strtotime($sub->date_fin)) . '.');
        } else {
            Session::flash('error', 'Annulation impossible.');
        }
        redirect('/mon-compte/abonnement');
    }

    public function reactivateSubscription(): void
    {
        Auth::requireLogin();
        CSRF::check();
        $userId = Auth::id();

        if (Subscription::reactivate($userId)) {
            Session::flash('success', 'Ton abonnement est réactivé.');
        } else {
            Session::flash('error', 'Réactivation impossible.');
        }
        redirect('/mon-compte/abonnement');
    }

    // =====================================================================
    // SUPPRESSION DE COMPTE (RGPD) — demande puis confirmation par email
    // =====================================================================

    public function requestDeletion(): void
    {
        Auth::requireLogin();
        CSRF::check();
        $user = Auth::user();
        $db = Database::getInstance();

        // Invalider les anciens tokens non utilisés du user
        $db->update('user_deletion_tokens', ['used' => 1], 'user_id = ? AND used = 0', [$user->id]);

        $token = bin2hex(random_bytes(32));
        $db->insert('user_deletion_tokens', [
            'user_id'   => $user->id,
            'token'     => $token,
            'expire_at' => date('Y-m-d H:i:s', strtotime('+24 hours')),
            'used'      => 0,
        ]);

        Mailer::sendDeletionRequest($user, $token);

        Session::flash('success', "Email envoyé à {$user->email}. Clique sur le lien dans les 24h pour confirmer la suppression.");
        redirect('/mon-compte/profil');
    }
}
