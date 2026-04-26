<?php
namespace App\Controllers;

use App\Lib\Database;

/**
 * Pages statiques + pages dynamiques (auteurs, presse, aide…)
 */
class PageController extends BaseController
{
    public function abonnement(): void
    {
        $this->view('pages/abonnement', ['titre' => 'Abonnement']);
    }

    public function aPropos(): void
    {
        $this->view('pages/a-propos', ['titre' => 'À propos — Les éditions Variable']);
    }

    public function presse(): void
    {
        $this->view('pages/presse', ['titre' => 'Espace presse']);
    }

    public function contact(): void
    {
        $this->view('pages/contact', ['titre' => 'Contact']);
    }

    public function publier(): void
    {
        $this->view('pages/publier', ['titre' => 'Publier chez Variable']);
    }

    public function cgu(): void
    {
        $this->view('pages/cgu', ['titre' => 'Conditions générales d\'utilisation']);
    }

    public function cgv(): void
    {
        $this->view('pages/cgv', ['titre' => 'Conditions générales de vente']);
    }

    public function mentions(): void
    {
        $this->view('pages/mentions', ['titre' => 'Mentions légales']);
    }

    public function confidentialite(): void
    {
        $this->view('pages/confidentialite', ['titre' => 'Politique de confidentialité']);
    }

    public function aide(): void
    {
        $this->view('pages/aide', ['titre' => 'Centre d\'aide']);
    }

    public function newsletterPage(): void
    {
        $this->view('pages/newsletter', ['titre' => 'Notre newsletter']);
    }

    /**
     * GET /auteurs/comment-ca-marche
     * Page publique : transparence sur la rémunération des auteurs
     * (70/30, pool d'abonnements, seuil, méthodes de paiement).
     */
    public function commentCaMarche(): void
    {
        $this->view('pages/auteurs-comment-ca-marche', [
            'titre' => 'Comment Variable rémunère ses auteurs',
        ]);
    }

    /**
     * GET /auteurs/devenir
     * Endpoint intelligent qui route l'utilisateur selon son état actuel.
     * Tous les boutons "Devenir auteur" du site pointent ici pour avoir
     * un comportement cohérent peu importe le contexte (header, footer,
     * page comment-ca-marche).
     */
    public function redirectToAuthorPath(): void
    {
        $user = \App\Lib\Auth::user();

        // Cas 1 : visiteur non connecté → inscription, puis redirect auto
        // vers /auteur/candidater grâce au param ?redirect= traité par
        // AuthController::processRegister.
        if (!$user) {
            redirect('/inscription?redirect=' . urlencode('/auteur/candidater'));
            return;
        }

        // Cas 5 : déjà admin → on l'envoie sur /admin, l'admin n'a pas
        // besoin de "devenir auteur" pour publier (il a /admin/livres).
        if ($user->role === 'admin') {
            \App\Lib\Session::flash('success', 'Tu peux publier un livre directement depuis l\'admin.');
            redirect('/admin/livres/nouveau');
            return;
        }

        // Cas 2-4 : on regarde l'état du row authors lié
        $author = \App\Lib\Auth::getAuthorRecord();

        // Cas 2 : connecté lecteur sans candidature → formulaire
        if (!$author) {
            redirect('/auteur/candidater');
            return;
        }

        // Cas 3 : candidature en attente
        if ($author->statut_validation === 'en_attente') {
            \App\Lib\Session::flash('success', 'Ta candidature est en cours de revue. On revient vers toi sous 7-14 jours.');
            redirect('/auteur');
            return;
        }

        // Cas 4 : candidature refusée
        if ($author->statut_validation === 'refuse' || $author->statut_validation === 'suspendu') {
            \App\Lib\Session::flash('error', 'Ta candidature n\'a pas été validée. Contacte l\'équipe pour plus d\'infos.');
            redirect('/auteur');
            return;
        }

        // Cas 5 : auteur validé → dashboard
        redirect('/auteur');
    }

    public function auteurs(): void
    {
        $db = Database::getInstance();
        $auteurs = $db->fetchAll(
            "SELECT a.id, a.slug, a.nom_plume, a.biographie_courte, a.pays_origine, a.photo_auteur,
                    a.is_classic, u.prenom, u.nom,
                    (SELECT COUNT(*) FROM books WHERE author_id = a.id AND statut = 'publie') AS nb_livres
             FROM authors a
             LEFT JOIN users u ON u.id = a.user_id
             WHERE a.statut_validation = 'valide'
               AND (u.statut = 'actif' OR u.statut IS NULL OR a.is_classic = 1)
             HAVING nb_livres > 0
             ORDER BY a.is_classic DESC, a.created_at DESC"
        );
        $this->view('pages/auteurs', [
            'titre'   => 'Nos auteurs',
            'auteurs' => $auteurs,
        ]);
    }
}
