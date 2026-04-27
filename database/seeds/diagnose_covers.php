<?php
/**
 * Diagnostic des couvertures de livres en prod.
 *
 * Mode lecture seule par défaut : affiche pour chaque livre l'état de
 * sa couverture (présente, manquante, URL externe, vide). Aucun
 * changement DB.
 *
 * Mode --fix : nullifie `couverture_url_web` pour les livres dont le
 * fichier local pointé n'existe pas sur disque -> la fiche affichera
 * le gradient placeholder au lieu d'une image cassée. Idempotent.
 *
 * Usage en SSH cPanel :
 *   php database/seeds/diagnose_covers.php          # lecture seule
 *   php database/seeds/diagnose_covers.php --fix    # nettoyage DB
 *
 * Les URLs externes (https://...) sont laissées telles quelles dans les
 * deux modes — Unsplash, Wikimedia etc. ne sont pas vérifiées (on ne
 * va pas faire un curl par row).
 */

require_once __DIR__ . '/../../bootstrap.php';

use App\Lib\Database;

$fix = in_array('--fix', $argv ?? [], true);

echo "=== Diagnostic couvertures ===" . PHP_EOL;
echo "Mode : " . ($fix ? "FIX (nullifie les URLs cassées)" : "LECTURE SEULE") . PHP_EOL;
echo "Racine storage : " . BASE_PATH . "/storage/covers/" . PHP_EOL . PHP_EOL;

$db = Database::getInstance();
$books = $db->fetchAll(
    "SELECT id, titre, slug, couverture_url_web, couverture_path
       FROM books
      ORDER BY id DESC"
);

$counts = ['ok' => 0, 'missing' => 0, 'external' => 0, 'empty' => 0];
$toNullify = [];

foreach ($books as $book) {
    $url = (string) ($book->couverture_url_web ?? '');

    if ($url === '') {
        $counts['empty']++;
        printf("  [---] #%-4d %s (gradient)\n", $book->id, mb_substr($book->titre, 0, 60));
        continue;
    }

    // URL externe (https://images.unsplash.com/..., wikimedia, etc.)
    if (preg_match('#^https?://#i', $url)) {
        $counts['external']++;
        printf("  [EXT] #%-4d %s -> %s\n", $book->id, mb_substr($book->titre, 0, 50), mb_substr($url, 0, 60));
        continue;
    }

    // URL locale : /image/covers/<file>  -> on convertit en path filesystem
    if (str_starts_with($url, '/image/covers/')) {
        $filename = basename($url);
        $absPath  = BASE_PATH . '/storage/covers/' . $filename;

        if (is_file($absPath) && filesize($absPath) > 0) {
            $counts['ok']++;
            printf("  [OK ] #%-4d %s\n", $book->id, mb_substr($book->titre, 0, 60));
        } else {
            $counts['missing']++;
            $toNullify[] = $book;
            printf("  [404] #%-4d %s -> %s (FICHIER ABSENT)\n",
                $book->id, mb_substr($book->titre, 0, 50), $filename);
        }
        continue;
    }

    // Anciennes URLs au format storage/covers/... (avant la migration de routes)
    if (str_starts_with($url, '/storage/covers/') || str_starts_with($url, 'storage/covers/')) {
        $filename = basename($url);
        $absPath  = BASE_PATH . '/storage/covers/' . $filename;
        if (is_file($absPath) && filesize($absPath) > 0) {
            $counts['ok']++;
            printf("  [OLD] #%-4d %s -> à corriger en /image/covers/%s\n",
                $book->id, mb_substr($book->titre, 0, 50), $filename);
        } else {
            $counts['missing']++;
            $toNullify[] = $book;
            printf("  [404] #%-4d %s -> %s (FICHIER ABSENT, ancien format)\n",
                $book->id, mb_substr($book->titre, 0, 50), $filename);
        }
        continue;
    }

    // Format inconnu
    printf("  [???] #%-4d %s -> %s (FORMAT INCONNU)\n",
        $book->id, mb_substr($book->titre, 0, 50), $url);
}

echo PHP_EOL . "=== Résumé ===" . PHP_EOL;
printf("  OK (fichier présent)    : %d\n", $counts['ok']);
printf("  External (URL https://) : %d\n", $counts['external']);
printf("  Manquant (404)          : %d\n", $counts['missing']);
printf("  Vide (gradient)         : %d\n", $counts['empty']);
printf("  Total                   : %d\n", count($books));

if ($counts['missing'] === 0) {
    echo PHP_EOL . "Aucune couverture cassée. RAS." . PHP_EOL;
    exit(0);
}

if (!$fix) {
    echo PHP_EOL . sprintf(
        "%d couverture(s) cassée(s) détectée(s). Pour nullifier en DB et afficher le gradient à la place :\n  php %s --fix\n",
        $counts['missing'], $argv[0] ?? __FILE__
    ) . PHP_EOL;
    exit(0);
}

// Mode --fix
echo PHP_EOL . "=== Application du fix ===" . PHP_EOL;
foreach ($toNullify as $book) {
    $db->update('books', [
        'couverture_url_web' => null,
        'couverture_path'    => null,
    ], 'id = ?', [$book->id]);
    printf("  Nullifié #%-4d %s\n", $book->id, mb_substr($book->titre, 0, 60));
}
echo PHP_EOL . sprintf("=== %d couverture(s) nullifiée(s) — gradient affiché à la place ===\n", count($toNullify)) . PHP_EOL;
