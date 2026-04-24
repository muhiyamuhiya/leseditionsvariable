<?php
namespace App\Controllers;

use App\Models\Book;
use App\Models\Category;

/**
 * Contrôleur du catalogue de livres
 */
class BookController extends BaseController
{
    /**
     * Page catalogue avec filtres et pagination
     */
    public function catalogue(): void
    {
        $page = max(1, (int) ($_GET['page'] ?? 1));
        $perPage = 24;
        $offset = ($page - 1) * $perPage;

        $categorySlug = $_GET['categorie'] ?? null;
        $search = !empty($_GET['q']) ? trim($_GET['q']) : null;
        $tri = $_GET['tri'] ?? 'recent';

        // Mapping tri → ORDER BY SQL
        $orderMap = [
            'recent'    => 'b.date_publication DESC',
            'populaire' => 'b.total_ventes DESC, b.total_lectures DESC',
            'prix_asc'  => 'b.prix_unitaire_usd ASC',
            'prix_desc' => 'b.prix_unitaire_usd DESC',
        ];
        $orderBy = $orderMap[$tri] ?? $orderMap['recent'];

        // Récupérer les livres et le total
        $livres = Book::findPublished($perPage, $offset, $categorySlug, $search, $orderBy);
        $total = Book::countPublished($categorySlug, $search);
        $totalPages = max(1, (int) ceil($total / $perPage));

        // Catégorie active (pour afficher le nom)
        $categorieActive = $categorySlug ? Category::findBySlug($categorySlug) : null;

        // Toutes les catégories pour le filtre
        $categories = Category::findActive();

        $this->view('catalogue/index', [
            'titre'           => $categorieActive ? $categorieActive->nom : 'Catalogue',
            'livres'          => $livres,
            'categories'      => $categories,
            'categorieActive' => $categorieActive,
            'total'           => $total,
            'page'            => $page,
            'totalPages'      => $totalPages,
            'search'          => $search,
            'tri'             => $tri,
            'categorySlug'    => $categorySlug,
        ]);
    }

    /**
     * Catalogue filtré par catégorie via URL propre
     */
    public function byCategory(string $slug): void
    {
        $_GET['categorie'] = $slug;
        $this->catalogue();
    }
}
