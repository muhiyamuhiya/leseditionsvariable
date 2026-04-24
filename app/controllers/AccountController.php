<?php
namespace App\Controllers;

use App\Lib\Auth;
use App\Lib\Database;

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

        // Livres achetés/en bibliothèque
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

        // Abonnement actif
        $abonnement = $db->fetch(
            "SELECT * FROM subscriptions WHERE user_id = ? AND statut = 'actif' AND date_fin > NOW() ORDER BY date_fin DESC LIMIT 1",
            [$user->id]
        );

        // Stats lecture
        $stats = $db->fetch(
            "SELECT COUNT(*) as nb_livres, SUM(total_pages_lues) as pages_lues, SUM(total_temps_lecture) as temps_total
             FROM reading_progress WHERE user_id = ?",
            [$user->id]
        );

        $this->view('account/index', [
            'titre'      => 'Mon compte',
            'user'       => $user,
            'livres'     => $livres,
            'abonnement' => $abonnement,
            'stats'      => $stats,
        ]);
    }
}
