<?php
/** @var object $user */
/** @var array  $books   3 derniers livres publiés */
/** @var string $appName */
/** @var string $appUrl */

$prenom = htmlspecialchars($user->prenom ?? '', ENT_QUOTES, 'UTF-8');

$title   = 'Les nouveautés de la semaine sur Variable';
$preview = '3 nouveaux livres publiés récemment — découvre-les en avant-première.';

ob_start();
?>
<h1 class="h1 text-main" style="margin:0 0 16px 0;font-family:'Helvetica Neue',Arial,sans-serif;font-size:28px;line-height:1.2;color:#0B0B0F;font-weight:700;letter-spacing:-0.3px;">
    Tout frais sur Variable ✨
</h1>
<p class="text-muted" style="margin:0 0 24px 0;font-family:'Helvetica Neue',Arial,sans-serif;font-size:16px;line-height:1.6;color:#525866;">
    <?= $prenom ?: 'Hé' ?>, voici les <strong style="color:#0B0B0F;">3 derniers livres publiés</strong> sur la plateforme. De nouvelles voix, de nouveaux récits — chacun avec ses 10 pages d'extrait gratuit.
</p>

<?php if (!empty($books)): ?>
    <?php foreach ($books as $b):
        $titre  = htmlspecialchars($b->titre ?? '', ENT_QUOTES, 'UTF-8');
        $author = htmlspecialchars($b->author_display ?? '', ENT_QUOTES, 'UTF-8');
        $desc   = htmlspecialchars(mb_strimwidth((string) ($b->description_courte ?? ''), 0, 140, '…'), ENT_QUOTES, 'UTF-8');
        $url    = $appUrl . '/livre/' . htmlspecialchars($b->slug ?? '', ENT_QUOTES, 'UTF-8');
        $datePub = $b->date_publication ? date('d/m/Y', strtotime((string) $b->date_publication)) : '';
    ?>
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="background:#FAF7F2;border-radius:10px;border:1px solid #EBE6DD;margin:0 0 12px 0;">
        <tr>
            <td style="padding:18px 22px;font-family:'Helvetica Neue',Arial,sans-serif;">
                <?php if ($datePub): ?>
                    <p style="margin:0 0 4px 0;font-size:11px;letter-spacing:1.5px;text-transform:uppercase;color:#F59E0B;font-weight:700;">
                        ✨ Publié le <?= $datePub ?>
                    </p>
                <?php endif; ?>
                <p style="margin:0 0 4px 0;font-size:18px;line-height:1.3;color:#0B0B0F;font-weight:700;">
                    <a href="<?= $url ?>" style="color:#0B0B0F;text-decoration:none;"><?= $titre ?></a>
                </p>
                <?php if ($author): ?>
                    <p style="margin:0 0 8px 0;font-size:13px;color:#525866;">par <?= $author ?></p>
                <?php endif; ?>
                <?php if ($desc): ?>
                    <p style="margin:0;font-size:14px;line-height:1.5;color:#525866;"><?= $desc ?></p>
                <?php endif; ?>
            </td>
        </tr>
    </table>
    <?php endforeach; ?>
<?php else: ?>
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="background:#FAF7F2;border-radius:10px;border:1px solid #EBE6DD;margin:0 0 24px 0;">
        <tr><td style="padding:24px;font-family:'Helvetica Neue',Arial,sans-serif;font-size:14px;color:#525866;text-align:center;">
            Cette semaine on est dans les coulisses — les nouvelles publications arrivent bientôt.
        </td></tr>
    </table>
<?php endif; ?>

<table role="presentation" cellpadding="0" cellspacing="0" border="0" align="center" width="100%" style="margin:24px 0 16px 0;">
    <tr>
        <td align="left" style="padding:0;">
            <table role="presentation" cellpadding="0" cellspacing="0" border="0">
                <tr>
                    <td class="btn-cell" align="center" style="background:#0B0B0F;border-radius:10px;">
                        <a href="<?= htmlspecialchars($appUrl . '/catalogue', ENT_QUOTES, 'UTF-8') ?>" style="display:inline-block;padding:14px 36px;color:#F59E0B;font-weight:700;font-size:15px;font-family:'Helvetica Neue',Arial,sans-serif;text-decoration:none;letter-spacing:0.3px;">
                            Découvrir le catalogue →
                        </a>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>
<div style="clear:both;font-size:0;line-height:0;height:0;">&nbsp;</div>

<p class="text-muted" style="margin:24px 0 0 0;font-family:'Helvetica Neue',Arial,sans-serif;font-size:14px;line-height:1.6;color:#525866;">
    Tu veux qu'on t'envoie les nouveautés chaque semaine ? Inscris-toi à <a href="<?= htmlspecialchars($appUrl . '/newsletter', ENT_QUOTES, 'UTF-8') ?>" style="color:#0B0B0F;font-weight:600;text-decoration:underline;">la newsletter</a>.
</p>
<?php
$content_html = ob_get_clean();
require __DIR__ . '/layout.php';
