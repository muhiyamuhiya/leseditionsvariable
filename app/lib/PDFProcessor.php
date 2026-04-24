<?php
namespace App\Lib;

/**
 * Traitement de fichiers PDF
 * Génération d'extraits, manipulation de pages
 */
class PDFProcessor
{
    /**
     * Générer un extrait PDF (N premières pages)
     */
    public static function generateExtract(string $sourcePath, string $outputPath, int $nbPages = 10): bool
    {
        if (!file_exists($sourcePath)) {
            echo "ERREUR : fichier source introuvable : {$sourcePath}" . PHP_EOL;
            return false;
        }

        // Créer le dossier de destination si nécessaire
        $dir = dirname($outputPath);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        // Vérifier si Ghostscript est disponible
        $gsPath = trim(shell_exec('which gs 2>/dev/null') ?? '');

        if ($gsPath) {
            $cmd = sprintf(
                '%s -dBATCH -dNOPAUSE -dQUIET -sDEVICE=pdfwrite -dFirstPage=1 -dLastPage=%d -sOutputFile=%s %s 2>&1',
                escapeshellarg($gsPath),
                $nbPages,
                escapeshellarg($outputPath),
                escapeshellarg($sourcePath)
            );
            exec($cmd, $output, $returnCode);

            if ($returnCode === 0 && file_exists($outputPath)) {
                echo "  Extrait généré ({$nbPages} pages) via Ghostscript" . PHP_EOL;
                return true;
            }
            echo "  ATTENTION : Ghostscript a échoué (code {$returnCode})" . PHP_EOL;
        } else {
            echo "  ATTENTION : Ghostscript non installé" . PHP_EOL;
        }

        // Fallback : copier le fichier complet
        copy($sourcePath, $outputPath);
        echo "  Fallback : fichier complet copié comme extrait" . PHP_EOL;
        return true;
    }
}
