<?php
namespace App\Models;

use App\Lib\Database;

/**
 * Modèle Author
 */
class Author extends BaseModel
{
    protected static string $table = 'authors';

    /**
     * Trouver un auteur par son slug (avec infos user éventuelles).
     * LEFT JOIN car les auteurs classiques (is_classic=1) n'ont pas de user_id.
     */
    public static function findBySlug(string $slug): object|false
    {
        $db = Database::getInstance();
        return $db->fetch(
            "SELECT a.*, u.prenom, u.nom, u.email
             FROM authors a
             LEFT JOIN users u ON a.user_id = u.id
             WHERE a.slug = ?",
            [$slug]
        );
    }

    /**
     * Tous les auteurs validés (incluant les classiques sans compte user).
     */
    public static function findActive(): array
    {
        $db = Database::getInstance();
        return $db->fetchAll(
            "SELECT a.*, u.prenom, u.nom, u.email
             FROM authors a
             LEFT JOIN users u ON a.user_id = u.id
             WHERE a.statut_validation = 'valide'
             ORDER BY COALESCE(a.nom_plume, u.nom, '') ASC"
        );
    }

    /**
     * Génère un slug unique pour la table authors. Si "emile-zola" existe
     * déjà, retourne "emile-zola-2", puis "-3", etc.
     *
     * Utilisé par :
     *   - AuthorDashboardController::submitApplication (candidature auteur)
     *   - AdminController::authorAjaxCreate (création depuis le form livre)
     */
    public static function createUniqueSlug(string $base): string
    {
        $base = self::slugify($base);
        if ($base === '') {
            $base = 'auteur';
        }

        $db = Database::getInstance();
        $candidate = $base;
        $i = 2;
        while ($db->fetch("SELECT 1 FROM authors WHERE slug = ?", [$candidate])) {
            $candidate = $base . '-' . $i;
            $i++;
            if ($i > 1000) {
                throw new \RuntimeException("createUniqueSlug : impossible de générer un slug unique pour '{$base}'.");
            }
        }
        return $candidate;
    }

    /**
     * Transforme une chaîne en slug ASCII : "Émile Zola" → "emile-zola".
     *
     * Utilise l'extension intl si disponible, sinon un fallback PHP pur
     * (NitroHost en mutualisé n'a pas toujours intl activé).
     */
    public static function slugify(string $str): string
    {
        $str = trim($str);

        if (function_exists('transliterator_transliterate')) {
            $str = (string) transliterator_transliterate('Any-Latin; Latin-ASCII; Lower()', $str);
        } else {
            $str = self::stripDiacritics($str);
            $str = mb_strtolower($str, 'UTF-8');
        }

        $str = preg_replace('/[^a-z0-9]+/', '-', $str) ?? '';
        return trim($str, '-');
    }

    /**
     * Fallback PHP pur quand l'extension intl n'est pas dispo.
     * Liste explicite des accents/ligatures français + symboles courants.
     */
    private static function stripDiacritics(string $s): string
    {
        $from = [
            'À','Á','Â','Ã','Ä','Å','à','á','â','ã','ä','å',
            'Ò','Ó','Ô','Õ','Ö','Ø','ò','ó','ô','õ','ö','ø',
            'È','É','Ê','Ë','è','é','ê','ë',
            'Ì','Í','Î','Ï','ì','í','î','ï',
            'Ù','Ú','Û','Ü','ù','ú','û','ü',
            'Ý','Ÿ','ý','ÿ',
            'Ç','ç','Ñ','ñ',
            'Æ','æ','Œ','œ','ß',
            '’','‘','“','”','—','–','…',
        ];
        $to = [
            'A','A','A','A','A','A','a','a','a','a','a','a',
            'O','O','O','O','O','O','o','o','o','o','o','o',
            'E','E','E','E','e','e','e','e',
            'I','I','I','I','i','i','i','i',
            'U','U','U','U','u','u','u','u',
            'Y','Y','y','y',
            'C','c','N','n',
            'AE','ae','OE','oe','ss',
            "'","'",'"','"','-','-','...',
        ];
        return str_replace($from, $to, $s);
    }
}
