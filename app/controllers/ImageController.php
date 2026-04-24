<?php
namespace App\Controllers;

/**
 * Sert les images stockées dans /storage/covers/
 */
class ImageController extends BaseController
{
    public function serveCover(string $filename): void
    {
        $path = BASE_PATH . '/storage/covers/' . basename($filename);

        if (!file_exists($path)) {
            http_response_code(404);
            exit;
        }

        $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
        $mime = match ($ext) {
            'jpg', 'jpeg' => 'image/jpeg',
            'png'         => 'image/png',
            'webp'        => 'image/webp',
            default       => 'application/octet-stream',
        };

        header('Content-Type: ' . $mime);
        header('Content-Length: ' . filesize($path));
        header('Cache-Control: public, max-age=604800');
        readfile($path);
        exit;
    }
}
