<?php
namespace App\Controllers;

use App\Models\Category;

/**
 * Contrôleur de la page d'accueil
 */
class HomeController extends BaseController
{
    public function index(): void
    {
        // Livre du mois (factice)
        $livreDuMois = (object) [
            'titre'       => 'Les rives du fleuve Congo',
            'auteur'      => 'Amara Mukendi',
            'description' => 'Un roman puissant sur trois générations de femmes entre Kinshasa et Bruxelles, porté par une écriture lumineuse et sans concession.',
            'categorie'   => 'Roman',
        ];

        // Catégories actives
        $categories = Category::findActive();

        // Livres factices pour les carrousels
        $livresFactices = [
            ['titre' => 'Les rives du fleuve Congo', 'auteur' => 'Amara Mukendi', 'categorie' => 'Roman', 'couleur' => 'from-red-900 to-amber-900'],
            ['titre' => "L'Afrique qui entreprend", 'auteur' => 'Fatou Diallo', 'categorie' => 'Essai', 'couleur' => 'from-amber-900 to-orange-900'],
            ['titre' => 'Paroles de baobab', 'auteur' => 'Samba Ndiaye', 'categorie' => 'Poésie', 'couleur' => 'from-emerald-900 to-teal-900'],
            ['titre' => 'Ma route, mon histoire', 'auteur' => 'Christelle Mbala', 'categorie' => 'Biographie', 'couleur' => 'from-rose-900 to-red-900'],
            ['titre' => "L'enfant de Kinshasa", 'auteur' => 'Jean-Paul Lumumba', 'categorie' => 'Roman', 'couleur' => 'from-indigo-900 to-purple-900'],
            ['titre' => 'Sous le baobab', 'auteur' => 'Marie Kasongo', 'categorie' => 'Nouvelles', 'couleur' => 'from-yellow-900 to-orange-900'],
            ['titre' => 'Femmes debout', 'auteur' => 'Aïcha Touré', 'categorie' => 'Biographie', 'couleur' => 'from-fuchsia-900 to-pink-900'],
            ['titre' => 'Le sage de la savane', 'auteur' => 'Ibrahim Konaté', 'categorie' => 'Conte', 'couleur' => 'from-green-900 to-emerald-900'],
            ['titre' => 'Code noir, code lumière', 'auteur' => 'Adja Sylla', 'categorie' => 'Essai', 'couleur' => 'from-slate-800 to-gray-900'],
            ['titre' => 'Retour à Douala', 'auteur' => 'Patrice Ebolo', 'categorie' => 'Roman', 'couleur' => 'from-blue-900 to-cyan-900'],
        ];

        $this->view('home/index', [
            'titre'          => 'Accueil',
            'livreDuMois'    => $livreDuMois,
            'categories'     => $categories,
            'livresFactices' => $livresFactices,
        ]);
    }
}
