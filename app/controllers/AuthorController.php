<?php
namespace App\Controllers;

use App\Models\Author;
use App\Models\Book;

/**
 * Page publique auteur
 */
class AuthorController extends BaseController
{
    public function show(string $slug): void
    {
        $author = Author::findBySlug($slug);
        if (!$author) {
            http_response_code(404);
            $this->view('errors/404', ['titre' => 'Auteur introuvable']);
            return;
        }

        $books = Book::findByAuthor($author->id, 50);

        $totalLectures = 0;
        $totalVentes = 0;
        foreach ($books as $b) {
            $totalLectures += (int) ($b->total_lectures ?? 0);
            $totalVentes += (int) ($b->total_ventes ?? 0);
        }

        $this->view('author/show', [
            'titre'          => ($author->nom_plume ?: $author->prenom . ' ' . $author->nom),
            'author'         => $author,
            'books'          => $books,
            'totalBooks'     => count($books),
            'totalLectures'  => $totalLectures,
            'totalVentes'    => $totalVentes,
        ]);
    }
}
