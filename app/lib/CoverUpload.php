<?php
namespace App\Lib;

/**
 * Upload de couvertures de livres — pipeline unique pour admin et auteur.
 *
 * Pourquoi ce helper :
 *  - Avant, chaque contrôleur dupliquait le code d'upload, et certains ne
 *    vérifiaient pas le retour de move_uploaded_file() : la DB était mise
 *    à jour avec un chemin vers un fichier inexistant -> image cassée
 *    côté lecteur.
 *  - Le check de type MIME se faisait sur $file['type'] (header navigateur,
 *    non fiable). Une photo iPhone (HEIC) ou un MIME atypique passait par
 *    le else silencieusement -> aucune couverture en DB.
 *
 * Garanties après cet appel :
 *  - retour null = la DB ne doit pas être touchée (rien de cassé)
 *  - retour array = le fichier existe sur disque ET la DB peut être MAJ
 */
class CoverUpload
{
    /** Limite raisonnable pour une couverture HD (1200x1800 jpeg ~ 2-3 Mo, png plus lourd). */
    public const MAX_SIZE_BYTES = 5 * 1024 * 1024;

    /** MIME -> extension finale stockée. HEIC/HEIF acceptés (photos iPhone modernes). */
    private const ACCEPTED_MIMES = [
        'image/jpeg' => 'jpg',
        'image/pjpeg' => 'jpg',
        'image/jpg' => 'jpg',
        'image/png' => 'png',
        'image/webp' => 'webp',
        'image/heic' => 'heic',
        'image/heif' => 'heif',
    ];

    /**
     * Tente de stocker la couverture uploadée. Retourne le couple de chemins
     * à persister en DB, ou null si quelque chose a foiré (logué dans error.log).
     *
     * @param array{tmp_name?:string,error?:int,size?:int,name?:string,type?:string}|null $file Le tableau $_FILES['couverture']
     * @param string $slug Slug du livre (sert de préfixe au nom de fichier)
     * @param int $bookId ID du livre (pour identifier les logs)
     * @return array{couverture_path:string,couverture_url_web:string}|null
     */
    public static function store(?array $file, string $slug, int $bookId): ?array
    {
        if (empty($file) || empty($file['tmp_name']) || !is_uploaded_file($file['tmp_name'])) {
            return null;
        }

        // Erreur niveau PHP (UPLOAD_ERR_INI_SIZE, _FORM_SIZE, _PARTIAL...)
        if (($file['error'] ?? UPLOAD_ERR_OK) !== UPLOAD_ERR_OK) {
            error_log(sprintf(
                'CoverUpload REJECT book=%d err=%d name=%s',
                $bookId, (int) $file['error'], $file['name'] ?? '?'
            ));
            return null;
        }

        $size = (int) ($file['size'] ?? 0);
        if ($size <= 0 || $size > self::MAX_SIZE_BYTES) {
            error_log(sprintf(
                'CoverUpload REJECT book=%d size=%d max=%d name=%s',
                $bookId, $size, self::MAX_SIZE_BYTES, $file['name'] ?? '?'
            ));
            return null;
        }

        // Validation MIME serverside via finfo (le $file['type'] côté navigateur
        // n'est pas fiable : iPhone HEIC, Edge envoie parfois image/jpg, etc.)
        $detectedMime = self::detectMime($file['tmp_name']);
        if (!isset(self::ACCEPTED_MIMES[$detectedMime])) {
            error_log(sprintf(
                'CoverUpload REJECT book=%d mime=%s clientMime=%s name=%s',
                $bookId, $detectedMime, $file['type'] ?? '?', $file['name'] ?? '?'
            ));
            return null;
        }

        $ext = self::ACCEPTED_MIMES[$detectedMime];
        $safeSlug = preg_replace('#[^a-z0-9-]+#i', '-', $slug) ?: 'book-' . $bookId;
        $filename = $safeSlug . '-' . time() . '.' . $ext;
        $absDir   = BASE_PATH . '/storage/covers';
        $absPath  = $absDir . '/' . $filename;

        if (!is_dir($absDir) && !mkdir($absDir, 0755, true) && !is_dir($absDir)) {
            error_log(sprintf('CoverUpload FAIL book=%d mkdir=%s', $bookId, $absDir));
            return null;
        }

        if (!move_uploaded_file($file['tmp_name'], $absPath)) {
            error_log(sprintf(
                'CoverUpload FAIL book=%d move_uploaded_file dest=%s',
                $bookId, $absPath
            ));
            return null;
        }

        // Sanity check : la cible existe vraiment et fait la bonne taille
        if (!is_file($absPath) || filesize($absPath) <= 0) {
            error_log(sprintf(
                'CoverUpload FAIL book=%d post-move missing dest=%s',
                $bookId, $absPath
            ));
            return null;
        }

        return [
            'couverture_path'    => 'storage/covers/' . $filename,
            'couverture_url_web' => '/image/covers/' . $filename,
        ];
    }

    /**
     * Détecte le MIME réel du fichier uploadé. Préfère finfo (extension PHP
     * standard), retombe sur mime_content_type() si finfo absent (rare),
     * puis retourne 'application/octet-stream' en dernier recours.
     */
    private static function detectMime(string $tmpPath): string
    {
        if (class_exists(\finfo::class)) {
            $finfo = new \finfo(FILEINFO_MIME_TYPE);
            $mime  = $finfo->file($tmpPath);
            if (is_string($mime) && $mime !== '') {
                return strtolower($mime);
            }
        }
        if (function_exists('mime_content_type')) {
            $mime = mime_content_type($tmpPath);
            if (is_string($mime) && $mime !== '') {
                return strtolower($mime);
            }
        }
        return 'application/octet-stream';
    }
}
