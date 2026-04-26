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
        $isCli = (php_sapi_name() === 'cli');
        $say = static function (string $msg) use ($isCli): void {
            // En mode CLI on print pour les scripts/cron, en mode web on logge.
            $isCli ? print($msg . PHP_EOL) : error_log('PDFProcessor: ' . $msg);
        };

        if (!file_exists($sourcePath)) {
            $say("ERREUR : fichier source introuvable : {$sourcePath}");
            return false;
        }

        // Créer le dossier de destination si nécessaire
        $dir = dirname($outputPath);
        if (!is_dir($dir) && !mkdir($dir, 0755, true) && !is_dir($dir)) {
            $say("ERREUR : impossible de créer le dossier {$dir}");
            return false;
        }

        // Vérifier si Ghostscript est disponible
        $gsPath = trim((string) (shell_exec('which gs 2>/dev/null') ?? ''));

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
                $say("Extrait généré ({$nbPages} pages) via Ghostscript");
                return true;
            }
            $say("Ghostscript a échoué (code {$returnCode}) — fallback sur copie");
        } else {
            $say('Ghostscript non installé — fallback sur copie');
        }

        // Fallback : copier le fichier complet
        if (@copy($sourcePath, $outputPath)) {
            $say('Fichier complet copié comme extrait (fallback)');
            return true;
        }
        $say("ERREUR : copie du fichier source impossible vers {$outputPath}");
        return false;
    }
}
