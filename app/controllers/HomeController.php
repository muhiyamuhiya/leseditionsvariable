<?php
namespace App\Controllers;

use App\Models\Book;
use App\Models\Category;

/**
 * Contrôleur de la page d'accueil
 */
class HomeController extends BaseController
{
    public function index(): void
    {
        // Livre du mois (premier livre mis en avant)
        $recommandes = Book::findRecommandes(10);
        $livreDuMois = $recommandes[0] ?? null;

        // Carrousels
        $nouveautes = Book::findNouveautes(10);
        $tendances = Book::findTendances(10);
        $romansFiction = Book::findByCategory('roman-fiction', 10);
        $business = Book::findByCategory('business-entrepreneuriat', 10);

        // Catégories
        $categories = Category::findActive();

        $this->view('home/index', [
            'titre'        => 'Accueil',
            'livreDuMois'  => $livreDuMois,
            'nouveautes'   => $nouveautes,
            'tendances'    => $tendances,
            'romansFiction'=> $romansFiction,
            'business'     => $business,
            'recommandes'  => $recommandes,
            'categories'   => $categories,
        ]);
    }
}
