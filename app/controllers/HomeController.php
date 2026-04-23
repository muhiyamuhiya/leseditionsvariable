<?php
namespace App\Controllers;

use App\Lib\Database;

/**
 * Contrôleur de la page d'accueil
 */
class HomeController extends BaseController
{
    /**
     * Afficher la homepage avec test de connexion à la base de données
     */
    public function index(): void
    {
        $dbStatus = '';
        $dbOk = false;

        try {
            $db = Database::getInstance();
            $result = $db->fetch("SELECT COUNT(*) as total FROM categories");

            if ($result !== false) {
                $dbOk = true;
                $dbStatus = $result->total . ' catégories trouvées';
            } else {
                $dbStatus = 'La requête a échoué (vérifiez que les tables existent)';
            }
        } catch (\Throwable $e) {
            $dbStatus = $e->getMessage();
        }

        $this->view('home/index', [
            'titre'    => 'Accueil',
            'dbOk'     => $dbOk,
            'dbStatus' => $dbStatus,
        ]);
    }
}
