<?php
namespace App\Controllers;

/**
 * Pages statiques et abonnement
 */
class PageController extends BaseController
{
    public function abonnement(): void
    {
        $this->view('pages/abonnement', ['titre' => 'Abonnement']);
    }

    public function aPropos(): void
    {
        $this->view('pages/a-propos', ['titre' => 'À propos']);
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
}
