<?php
/**
 * Compression des images de couvertures et photos auteurs >500 KB.
 * Cible : <500 KB tout en gardant une qualité visuelle correcte.
 *
 * Usage : /Applications/MAMP/bin/php/php8.3.30/bin/php database/seeds/compress_images.php
 */

require_once __DIR__ . '/../../bootstrap.php';

$targets = [
    'storage/authors/angello-luvungu-muhiya-1777038061.png',
    'storage/authors/angello-luvungu-muhiya-1777026037.png',
    'storage/covers/je-nai-pas-choisi-ma-naissance-1735000000.jpg',
    'storage/covers/je-nai-pas-choisi-ma-naissance-1777028498.jpg',
    'storage/covers/tes-du-site-1777028268.jpg',
    'storage/covers/tes-du-site-1777033661.png',
];

$maxBytes = 500 * 1024;
$totalBefore = 0;
$totalAfter = 0;
$processed = 0;
$errors = 0;

echo "=== Compression images >500 KB ===" . PHP_EOL . PHP_EOL;

foreach ($targets as $rel) {
    $path = BASE_PATH . '/' . $rel;
    if (!file_exists($path)) {
        echo "[SKIP] {$rel} introuvable" . PHP_EOL;
        continue;
    }

    $before = filesize($path);
    $totalBefore += $before;

    $info = getimagesize($path);
    if (!$info) {
        echo "[ERREUR] {$rel} : pas une image valide" . PHP_EOL;
        $errors++;
        continue;
    }

    [$w, $h, $type] = $info;

    // Charger selon le type
    switch ($type) {
        case IMAGETYPE_JPEG:
            $img = imagecreatefromjpeg($path);
            break;
        case IMAGETYPE_PNG:
            $img = imagecreatefrompng($path);
            break;
        case IMAGETYPE_WEBP:
            $img = imagecreatefromwebp($path);
            break;
        default:
            echo "[SKIP] {$rel} : type non supporté" . PHP_EOL;
            continue 2;
    }

    if (!$img) {
        echo "[ERREUR] {$rel} : chargement impossible" . PHP_EOL;
        $errors++;
        continue;
    }

    // Si plus large que 1500px, redimensionner (ratio préservé)
    $maxWidth = 1500;
    if ($w > $maxWidth) {
        $newH = (int) ($h * ($maxWidth / $w));
        $resized = imagecreatetruecolor($maxWidth, $newH);
        // Préserver la transparence pour PNG
        if ($type === IMAGETYPE_PNG) {
            imagealphablending($resized, false);
            imagesavealpha($resized, true);
            $transparent = imagecolorallocatealpha($resized, 0, 0, 0, 127);
            imagefilledrectangle($resized, 0, 0, $maxWidth, $newH, $transparent);
        }
        imagecopyresampled($resized, $img, 0, 0, 0, 0, $maxWidth, $newH, $w, $h);
        imagedestroy($img);
        $img = $resized;
        $w = $maxWidth;
        $h = $newH;
    }

    // Sauvegarder en JPEG qualité 80 (plus léger que PNG pour les couvertures)
    // pour les PNG d'origine, on convertit en JPEG (sauf si transparence essentielle — couvertures non transparentes)
    $newPath = $path;
    if ($type === IMAGETYPE_PNG) {
        // Pour les couvertures de livres : convertir en JPEG quality 82 (gain x10)
        // Pour les photos auteurs PNG : pareil
        $jpegPath = preg_replace('/\.png$/i', '.jpg', $path);
        // Aplatir la transparence sur fond blanc avant JPEG
        $bg = imagecreatetruecolor($w, $h);
        $white = imagecolorallocate($bg, 255, 255, 255);
        imagefilledrectangle($bg, 0, 0, $w, $h, $white);
        imagecopy($bg, $img, 0, 0, 0, 0, $w, $h);
        imagedestroy($img);
        $img = $bg;
        imagejpeg($img, $jpegPath, 82);

        // Si le fichier final est plus petit, garder le JPEG et supprimer l'original PNG
        if (file_exists($jpegPath) && filesize($jpegPath) < $before) {
            unlink($path);
            $newPath = $jpegPath;
        } else {
            // Échec : garder original
            @unlink($jpegPath);
        }
    } elseif ($type === IMAGETYPE_JPEG) {
        // JPEG : juste recompresser à quality 82
        imagejpeg($img, $path, 82);
    } else {
        imagewebp($img, $path, 82);
    }

    imagedestroy($img);

    $after = file_exists($newPath) ? filesize($newPath) : 0;
    $totalAfter += $after;
    $reduction = $before > 0 ? round((1 - $after / $before) * 100) : 0;
    $newRel = str_replace(BASE_PATH . '/', '', $newPath);

    if ($newPath !== $path) {
        echo "[OK] {$rel} → {$newRel}" . PHP_EOL;
    } else {
        echo "[OK] {$rel}" . PHP_EOL;
    }
    echo "     " . number_format($before) . " → " . number_format($after) . " octets ({$reduction}% de réduction)" . PHP_EOL;
    $processed++;
}

echo PHP_EOL . "=== Récapitulatif ===" . PHP_EOL;
echo "Fichiers traités : {$processed}" . PHP_EOL;
echo "Erreurs          : {$errors}" . PHP_EOL;
echo "Total avant      : " . number_format($totalBefore) . " octets (" . round($totalBefore / 1024 / 1024, 2) . " MB)" . PHP_EOL;
echo "Total après      : " . number_format($totalAfter) . " octets (" . round($totalAfter / 1024 / 1024, 2) . " MB)" . PHP_EOL;
$gain = $totalBefore > 0 ? round((1 - $totalAfter / $totalBefore) * 100) : 0;
echo "Gain global      : {$gain}%" . PHP_EOL;
