<?php
namespace App\Controllers;

/**
 * Sert les images stockées dans /storage/
 */
class ImageController extends BaseController
{
    public function serveCover(string $filename): void
    {
        $this->serveFromStorage('covers', $filename);
    }

    public function serveAuthorPhoto(string $filename): void
    {
        $this->serveFromStorage('authors', $filename);
    }

    public function serveUserPhoto(string $filename): void
    {
        $this->serveFromStorage('users', $filename);
    }

    private function serveFromStorage(string $folder, string $filename): void
    {
        // Sécurité path traversal
        $filename = basename($filename);
        if (str_contains($filename, '..') || $filename === '') {
            http_response_code(400);
            exit;
        }

        $absolutePath = BASE_PATH . '/storage/' . $folder . '/' . $filename;

        if (!file_exists($absolutePath)) {
            http_response_code(404);
            exit;
        }

        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        $mime = match ($ext) {
            'jpg', 'jpeg' => 'image/jpeg',
            'png'         => 'image/png',
            'webp'        => 'image/webp',
            'gif'         => 'image/gif',
            default       => 'application/octet-stream',
        };

        header('Content-Type: ' . $mime);
        header('Content-Length: ' . filesize($absolutePath));
        header('Cache-Control: public, max-age=604800');
        readfile($absolutePath);
        exit;
    }
}
