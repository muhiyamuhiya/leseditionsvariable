<?php
/**
 * Seed : ajouter des couvertures Unsplash aux 8 livres factices
 * Usage : php database/seeds/add_book_covers.php
 */
require_once __DIR__ . '/../../bootstrap.php';

use App\Lib\Database;

echo "=== Ajout des couvertures de livres ===" . PHP_EOL;

$db = Database::getInstance();

$covers = [
    'les-rives-du-fleuve-congo'     => 'https://images.unsplash.com/photo-1544735716-392fe2489ffa?w=600&q=80',
    'l-afrique-qui-entreprend'      => 'https://images.unsplash.com/photo-1560472354-b33ff0c44a43?w=600&q=80',
    'paroles-de-baobab'             => 'https://images.unsplash.com/photo-1532009324734-20a7a5813719?w=600&q=80',
    'ma-route-mon-histoire'         => 'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?w=600&q=80',
    'l-enfant-de-kinshasa'          => 'https://images.unsplash.com/photo-1519681393784-d120267933ba?w=600&q=80',
    'sous-le-baobab'                => 'https://images.unsplash.com/photo-1459156212016-c812468e2115?w=600&q=80',
    'femmes-debout'                 => 'https://images.unsplash.com/photo-1573496359142-b8d87734a5a2?w=600&q=80',
    'le-sage-de-la-savane'          => 'https://images.unsplash.com/photo-1469474968028-56623f02e42e?w=600&q=80',
];

$count = 0;
foreach ($covers as $slug => $url) {
    $book = $db->fetch("SELECT id, titre FROM books WHERE slug = ?", [$slug]);
    if (!$book) {
        echo "  Livre '{$slug}' non trouvé, skip." . PHP_EOL;
        continue;
    }

    $db->update('books', ['couverture_url_web' => $url], 'id = ?', [$book->id]);
    echo "  Couverture ajoutée : \"{$book->titre}\"" . PHP_EOL;
    $count++;
}

echo PHP_EOL . "=== {$count} couvertures mises à jour ===" . PHP_EOL;
