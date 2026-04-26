<?php
/** @var object $user */
/** @var string $promoCode      Code promo unique (ex: REVIENS-A1B2C3) */
/** @var int    $discountPct    Pourcentage de réduction (ex: 20) */
/** @var string $validUntilIso  Date d'expiration du code (Y-m-d) */
/** @var string $appName */
/** @var string $appUrl */

$prenom    = htmlspecialchars($user->prenom ?? '', ENT_QUOTES, 'UTF-8');
$codeSafe  = htmlspecialchars($promoCode ?? '', ENT_QUOTES, 'UTF-8');
$pct       = (int) ($discountPct ?? 20);
$validDate = date('d/m/Y', strtotime($validUntilIso ?? '+30 days'));

$title   = "On t'a oublié ? Tiens, -{$pct}% pour revenir.";
$preview = "Ton code {$codeSafe} — valide jusqu'au {$validDate}.";

ob_start();
?>
<h1 class="h1 text-main" style="margin:0 0 16px 0;font-family:'Helvetica Neue',Arial,sans-serif;font-size:28px;line-height:1.2;color:#0B0B0F;font-weight:700;letter-spacing:-0.3px;">
    On t'a oublié ? <?= $prenom ?: 'Là' ?>, on s'inquiète. 👀
</h1>
<p class="text-muted" style="margin:0 0 24px 0;font-family:'Helvetica Neue',Arial,sans-serif;font-size:16px;line-height:1.6;color:#525866;">
    Ça fait un moment qu'on ne t'a pas vu·e sur Variable. Pas de jugement — la vie est pleine de choses. Mais on a une petite attention pour te tenter de revenir.
</p>

<!-- Carte code promo -->
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="background:#0B0B0F;border-radius:12px;margin:0 0 24px 0;border:2px dashed #F59E0B;">
    <tr><td align="center" style="padding:32px 24px;font-family:'Helvetica Neue',Arial,sans-serif;">
        <p style="margin:0 0 8px 0;font-size:11px;letter-spacing:2px;text-transform:uppercase;color:#A0A0B0;font-weight:600;">🎁 Code de réactivation</p>
        <p style="margin:0 0 12px 0;font-size:32px;font-weight:700;color:#F59E0B;letter-spacing:-0.5px;line-height:1;">-<?= $pct ?>%</p>
        <p style="margin:0 0 12px 0;font-family:'SF Mono',Menlo,Consolas,monospace;font-size:22px;letter-spacing:2px;font-weight:700;color:#FFFFFF;background:#1A1A22;padding:12px 18px;border-radius:8px;display:inline-block;">
            <?= $codeSafe ?>
        </p>
        <p style="margin:0;font-size:13px;color:#A0A0B0;">Valide jusqu'au <strong style="color:#FFFFFF;"><?= htmlspecialchars($validDate, ENT_QUOTES, 'UTF-8') ?></strong></p>
    </td></tr>
</table>

<p class="text-main" style="margin:0 0 24px 0;font-family:'Helvetica Neue',Arial,sans-serif;font-size:15px;line-height:1.6;color:#0B0B0F;">
    Utilisable sur n'importe quel <strong>abonnement</strong> ou <strong>achat unitaire</strong>. Un seul usage, pour toi uniquement.
</p>

<!-- CTA -->
<table role="presentation" cellpadding="0" cellspacing="0" border="0" align="center" width="100%" style="margin:8px 0 16px 0;">
    <tr>
        <td align="left" style="padding:0;">
            <table role="presentation" cellpadding="0" cellspacing="0" border="0">
                <tr>
                    <td class="btn-cell" align="center" style="background:#F59E0B;border-radius:10px;">
                        <a href="<?= htmlspecialchars($appUrl . '/abonnement', ENT_QUOTES, 'UTF-8') ?>" style="display:inline-block;padding:14px 36px;color:#0B0B0F;font-weight:700;font-size:15px;font-family:'Helvetica Neue',Arial,sans-serif;text-decoration:none;letter-spacing:0.3px;">
                            Profiter du -<?= $pct ?>% →
                        </a>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>
<div style="clear:both;font-size:0;line-height:0;height:0;">&nbsp;</div>

<p class="text-muted" style="margin:24px 0 0 0;font-family:'Helvetica Neue',Arial,sans-serif;font-size:14px;line-height:1.6;color:#525866;">
    Si Variable ne te correspond plus, tu peux te désinscrire ou supprimer ton compte depuis <a href="<?= htmlspecialchars($appUrl . '/mon-compte/parametres', ENT_QUOTES, 'UTF-8') ?>" style="color:#0B0B0F;font-weight:600;text-decoration:underline;">tes paramètres</a>. On préfère savoir, plutôt que de t'envoyer des emails dans le vide.
</p>
<?php
$content_html = ob_get_clean();
require __DIR__ . '/layout.php';
