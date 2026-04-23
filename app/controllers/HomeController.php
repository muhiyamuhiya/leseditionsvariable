<?php
namespace App\Controllers;

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
        $this->view('home/index', [
            'titre' => 'Accueil',
        ]);
    }
}
