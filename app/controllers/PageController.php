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
