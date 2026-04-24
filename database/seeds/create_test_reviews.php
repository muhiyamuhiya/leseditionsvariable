<?php
/**
 * Seed : avis de test approuvés
 * Usage : php database/seeds/create_test_reviews.php
 */
require_once __DIR__ . '/../../bootstrap.php';

use App\Lib\Database;

$db = Database::getInstance();
echo "=== Seed : avis de test ===" . PHP_EOL;

// Récupérer l'admin (user_id = 1) et les auteurs comme reviewers
$users = $db->fetchAll("SELECT id, prenom FROM users LIMIT 6");
$books = $db->fetchAll("SELECT id, titre FROM books WHERE statut = 'publie' LIMIT 8");

if (empty($users) || empty($books)) {
    echo "ERREUR : pas assez de users ou books en base." . PHP_EOL;
    exit(1);
}

$avisTextes = [
    ['note' => 5, 'titre' => 'Un chef-d\'oeuvre',          'commentaire' => 'Ce livre m\'a profondément touché. L\'écriture est magnifique, les personnages sont attachants et l\'histoire vous transporte du début à la fin. Je le recommande à tous.'],
    ['note' => 4, 'titre' => 'Très bon livre',             'commentaire' => 'Une lecture captivante qui m\'a tenu en haleine pendant plusieurs jours. Quelques longueurs au milieu mais la conclusion est brillante. À lire absolument.'],
    ['note' => 5, 'titre' => 'Incontournable',             'commentaire' => 'Enfin un auteur qui parle de notre réalité avec justesse et talent. Chaque page résonne avec authenticité. Bravo à l\'auteur et aux éditions Variable pour cette publication.'],
    ['note' => 4, 'titre' => 'Belle découverte',           'commentaire' => 'Je ne connaissais pas cet auteur et je suis agréablement surpris. Le style est fluide, le propos est pertinent. J\'attends le prochain livre avec impatience.'],
    ['note' => 3, 'titre' => 'Intéressant mais inégal',    'commentaire' => 'Le début est prometteur et certains passages sont vraiment excellents, mais j\'ai trouvé que le rythme baissait vers le milieu. Reste une lecture enrichissante dans l\'ensemble.'],
    ['note' => 5, 'titre' => 'Coup de coeur',              'commentaire' => 'Rarement un livre m\'a autant marqué. L\'histoire est universelle tout en étant profondément ancrée dans son contexte africain. Un bijou littéraire.'],
    ['note' => 4, 'titre' => 'Bien écrit',                 'commentaire' => 'L\'auteur a un vrai talent pour décrire les scènes du quotidien avec poésie. On sent le vécu derrière chaque ligne. Une belle lecture pour le week-end.'],
    ['note' => 5, 'titre' => 'Magnifique',                 'commentaire' => 'Ce livre devrait être lu dans toutes les écoles francophones d\'Afrique. Il raconte notre histoire avec dignité et fierté. Merci pour ce cadeau littéraire.'],
];

$count = 0;
foreach ($books as $bi => $book) {
    // 2-3 avis par livre
    $nbAvis = min(count($avisTextes), rand(2, 3));
    for ($j = 0; $j < $nbAvis; $j++) {
        $reviewer = $users[($bi + $j + 1) % count($users)];
        $avis = $avisTextes[($bi * 3 + $j) % count($avisTextes)];

        // Vérifier doublon
        $exists = $db->fetch(
            "SELECT id FROM reviews WHERE user_id = ? AND book_id = ?",
            [$reviewer->id, $book->id]
        );
        if ($exists) continue;

        $db->insert('reviews', [
            'user_id'     => $reviewer->id,
            'book_id'     => $book->id,
            'note'        => $avis['note'],
            'titre'       => $avis['titre'],
            'commentaire' => $avis['commentaire'],
            'approuve'    => 1,
        ]);
        $count++;
    }
}

// Mettre à jour note_moyenne et nombre_avis dans books
foreach ($books as $book) {
    $stats = $db->fetch(
        "SELECT AVG(note) as avg_note, COUNT(*) as nb FROM reviews WHERE book_id = ? AND approuve = 1",
        [$book->id]
    );
    if ($stats && $stats->nb > 0) {
        $db->update('books', [
            'note_moyenne' => round($stats->avg_note, 2),
            'nombre_avis'  => $stats->nb,
        ], 'id = ?', [$book->id]);
    }
}

echo "{$count} avis créés et approuvés." . PHP_EOL;
echo "=== Terminé ===" . PHP_EOL;
