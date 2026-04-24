<?php
/**
 * Corrige les chemins d'images en DB pour pointer vers /image/ au lieu de /storage/
 * Usage : php database/seeds/fix_image_paths.php
 */
require_once __DIR__ . '/../../bootstrap.php';

use App\Lib\Database;

echo "=== Correction des chemins d'images ===" . PHP_EOL;

$db = Database::getInstance();
$count = 0;

// Livres : couverture_url_web
$books = $db->fetchAll("SELECT id, slug, couverture_url_web FROM books WHERE couverture_url_web IS NOT NULL AND couverture_url_web != ''");
foreach ($books as $book) {
    $url = $book->couverture_url_web;
    // Si c'est un chemin local storage, corriger vers /image/
    if (str_starts_with($url, '/storage/covers/') || str_starts_with($url, 'storage/covers/')) {
        $filename = basename($url);
        $newUrl = '/image/covers/' . $filename;
        $db->update('books', ['couverture_url_web' => $newUrl], 'id = ?', [$book->id]);
        echo "  Livre #{$book->id} ({$book->slug}) : {$url} -> {$newUrl}" . PHP_EOL;
        $count++;
    }
    // Les URLs externes (https://...) restent intactes
}

// Auteurs : photo_auteur et photo_url_web
$authors = $db->fetchAll("SELECT id, slug, photo_auteur FROM authors WHERE photo_auteur IS NOT NULL AND photo_auteur != ''");
foreach ($authors as $author) {
    $url = $author->photo_auteur;
    if (str_starts_with($url, '/storage/authors/') || str_starts_with($url, 'storage/authors/')) {
        $filename = basename($url);
        $newUrl = '/image/authors/' . $filename;
        $db->update('authors', ['photo_auteur' => $newUrl], 'id = ?', [$author->id]);
        echo "  Auteur #{$author->id} ({$author->slug}) : {$url} -> {$newUrl}" . PHP_EOL;
        $count++;
    }
}

echo PHP_EOL . "=== {$count} chemin(s) corrigé(s) ===" . PHP_EOL;
