<?php
/**
 * Régénération des extraits PDF (FREE_PREVIEW_PAGES premières pages) via Ghostscript
 *
 * Contexte : avant ce script, le système copiait simplement le PDF complet en tant
 * qu'extrait quand Ghostscript n'était pas disponible — ce qui exposait l'intégralité
 * du livre via la liseuse en mode "extrait". ReaderController bloque désormais le
 * service d'un extrait identique au complet, donc la fonctionnalité "lecture extrait"
 * reste cassée tant que les vrais extraits n'ont pas été générés.
 *
 * Usage :
 *   /Applications/MAMP/bin/php/php8.3.30/bin/php database/seeds/regenerate_extracts.php
 *
 * Pré-requis : Ghostscript installé (brew install ghostscript)
 */

require_once __DIR__ . '/../../bootstrap.php';

use App\Lib\Database;

echo "=== Régénération des extraits PDF ===" . PHP_EOL . PHP_EOL;

// 1) Détection de Ghostscript
$gsPath = trim((string) shell_exec('which gs 2>/dev/null'));
if ($gsPath === '' || !is_executable($gsPath)) {
    echo "[ERREUR] Ghostscript (gs) introuvable sur ce système." . PHP_EOL;
    echo "   Installation : brew install ghostscript" . PHP_EOL;
    echo "   Puis relance : php database/seeds/regenerate_extracts.php" . PHP_EOL;
    exit(1);
}
echo "[OK] Ghostscript détecté : {$gsPath}" . PHP_EOL;

// 2) Connexion DB
try {
    $db = Database::getInstance();
} catch (\Throwable $e) {
    echo "[ERREUR] Connexion DB impossible : " . $e->getMessage() . PHP_EOL;
    exit(1);
}

// 3) Récupération des livres publiés ayant un fichier complet
$livres = $db->fetchAll(
    "SELECT id, slug, titre, fichier_complet_path, fichier_extrait_path
     FROM books
     WHERE statut = 'publie' AND fichier_complet_path IS NOT NULL AND fichier_complet_path != ''"
);

$total = count($livres);
echo "Livres publiés avec PDF complet : {$total}" . PHP_EOL . PHP_EOL;

if ($total === 0) {
    echo "Rien à faire." . PHP_EOL;
    exit(0);
}

// 4) Génération
$nbGeneres = 0;
$nbSkipFichierManquant = 0;
$nbErreurs = 0;
$nombrePages = defined('FREE_PREVIEW_PAGES') ? FREE_PREVIEW_PAGES : 10;

foreach ($livres as $livre) {
    echo "→ [#{$livre->id}] {$livre->titre}" . PHP_EOL;

    $abFull = BASE_PATH . '/' . $livre->fichier_complet_path;
    if (!file_exists($abFull)) {
        echo "  [SKIP]Skip : fichier complet introuvable ({$livre->fichier_complet_path})" . PHP_EOL;
        $nbSkipFichierManquant++;
        continue;
    }

    // Chemin extrait : on garde celui en DB s'il existe, sinon on génère un path standard
    $relExtrait = $livre->fichier_extrait_path
        ?: 'storage/extracts/' . $livre->slug . '-extrait.pdf';
    $abExtrait = BASE_PATH . '/' . $relExtrait;

    // S'assurer que le répertoire de destination existe
    $dirExtrait = dirname($abExtrait);
    if (!is_dir($dirExtrait) && !mkdir($dirExtrait, 0755, true) && !is_dir($dirExtrait)) {
        echo "  [ECHEC]Erreur : impossible de créer le dossier {$dirExtrait}" . PHP_EOL;
        $nbErreurs++;
        continue;
    }

    // Génération via Ghostscript
    $cmd = sprintf(
        '%s -dBATCH -dNOPAUSE -sDEVICE=pdfwrite -dFirstPage=1 -dLastPage=%d -sOutputFile=%s %s 2>&1',
        escapeshellarg($gsPath),
        (int) $nombrePages,
        escapeshellarg($abExtrait),
        escapeshellarg($abFull)
    );

    $output = [];
    $exitCode = 0;
    exec($cmd, $output, $exitCode);

    if ($exitCode !== 0 || !file_exists($abExtrait)) {
        echo "  [ECHEC]Échec Ghostscript (code {$exitCode})" . PHP_EOL;
        if (!empty($output)) {
            echo "    " . implode(PHP_EOL . '    ', array_slice($output, -3)) . PHP_EOL;
        }
        $nbErreurs++;
        continue;
    }

    $tailleFull = filesize($abFull);
    $tailleExtrait = filesize($abExtrait);

    // Garde-fou : si l'extrait fait la même taille que le complet, c'est un échec silencieux
    if ($tailleExtrait === $tailleFull) {
        echo "  [ECHEC]Extrait identique au complet — Ghostscript n'a pas tronqué" . PHP_EOL;
        @unlink($abExtrait);
        $nbErreurs++;
        continue;
    }

    // Mise à jour de fichier_extrait_path en DB si nécessaire
    if ($livre->fichier_extrait_path !== $relExtrait) {
        $db->update('books', ['fichier_extrait_path' => $relExtrait], 'id = ?', [$livre->id]);
    }

    $reduction = $tailleFull > 0 ? round((1 - $tailleExtrait / $tailleFull) * 100) : 0;
    echo "  [OK]Généré : " . number_format($tailleExtrait) . " octets ({$reduction}% du complet)" . PHP_EOL;
    $nbGeneres++;
}

// 5) Récapitulatif
echo PHP_EOL . "=== Récapitulatif ===" . PHP_EOL;
echo "Total examinés          : {$total}" . PHP_EOL;
echo "Extraits générés        : {$nbGeneres}" . PHP_EOL;
echo "Skip (fichier manquant) : {$nbSkipFichierManquant}" . PHP_EOL;
echo "Erreurs                 : {$nbErreurs}" . PHP_EOL;

exit($nbErreurs > 0 ? 2 : 0);
