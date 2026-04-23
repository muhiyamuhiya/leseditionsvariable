<?php
namespace App\Controllers;

use App\Models\Category;

/**
 * Contrôleur de la page d'accueil
 */
class HomeController extends BaseController
{
    /**
     * Afficher la homepage
     */
    public function index(): void
    {
        // Récupérer les catégories actives
        $categories = Category::findActive();

        // Statistiques du hero (valeurs en dur pour l'instant)
        $statsHero = [
            'books'     => 50,
            'authors'   => 15,
            'countries' => 10,
        ];

        $this->view('home/index', [
            'titre'      => 'Accueil',
            'categories' => $categories,
            'statsHero'  => $statsHero,
        ]);
    }
}
