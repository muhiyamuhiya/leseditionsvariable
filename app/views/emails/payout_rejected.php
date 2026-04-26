<?php
/** @var object $user */
/** @var float  $amount */
/** @var string $reason */

$prenom = htmlspecialchars($user->prenom ?? '', ENT_QUOTES, 'UTF-8');
$amountFmt = number_format((float) ($amount ?? 0), 2, ',', ' ');
$reasonSafe = htmlspecialchars($reason ?? '', ENT_QUOTES, 'UTF-8');

$title   = 'Demande de versement refusée';
$preview = "Ton montant de {$amountFmt} USD reste disponible — voici comment corriger.";

ob_start();
?>
<h1 class="h1 text-main" style="margin:0 0 16px 0;font-family:'Helvetica Neue',Arial,sans-serif;font-size:26px;line-height:1.2;color:#0B0B0F;font-weight:700;letter-spacing:-0.3px;">
    Petit accroc, <?= $prenom ?: 'là' ?>.
</h1>
<p class="text-muted" style="margin:0 0 24px 0;font-family:'Helvetica Neue',Arial,sans-serif;font-size:15px;line-height:1.6;color:#525866;">
    On n'a pas pu traiter ta dernière demande de versement. Pas de panique : <strong style="color:#0B0B0F;">le montant reste à ton solde disponible</strong>, tu peux refaire une demande après avoir corrigé.
</p>

<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="background:#0B0B0F;border-radius:12px;margin:0 0 16px 0;border:2px solid #F59E0B;">
    <tr><td style="padding:20px 24px;font-family:'Helvetica Neue',Arial,sans-serif;">
        <p style="margin:0 0 4px 0;font-size:11px;letter-spacing:1.5px;text-transform:uppercase;color:#F59E0B;font-weight:700;">Motif du refus</p>
        <p style="margin:0;font-size:15px;color:#FFFFFF;line-height:1.5;"><?= $reasonSafe ?></p>
    </td></tr>
</table>

<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="background:#FAF7F2;border-radius:10px;border:1px solid #EBE6DD;margin:0 0 24px 0;">
    <tr><td style="padding:16px 22px;font-family:'Helvetica Neue',Arial,sans-serif;font-size:13px;line-height:1.7;color:#525866;">
        <strong style="color:#0B0B0F;">Montant à re-demander :</strong> <span style="color:#F59E0B;font-weight:700;"><?= $amountFmt ?> USD</span>
    </td></tr>
</table>

<table role="presentation" cellpadding="0" cellspacing="0" border="0" align="center" width="100%" style="margin:8px 0 16px 0;">
    <tr>
        <td align="left" style="padding:0;">
            <table role="presentation" cellpadding="0" cellspacing="0" border="0">
                <tr>
                    <td class="btn-cell" align="center" style="background:#F59E0B;border-radius:10px;">
                        <a href="<?= htmlspecialchars($appUrl . '/auteur/profil', ENT_QUOTES, 'UTF-8') ?>" style="display:inline-block;padding:14px 36px;color:#0B0B0F;font-weight:700;font-size:15px;font-family:'Helvetica Neue',Arial,sans-serif;text-decoration:none;letter-spacing:0.3px;">
                            Mettre à jour mes coordonnées →
                        </a>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>
<div style="clear:both;font-size:0;line-height:0;height:0;">&nbsp;</div>

<p class="text-muted" style="margin:24px 0 0 0;font-family:'Helvetica Neue',Arial,sans-serif;font-size:14px;line-height:1.6;color:#525866;">
    Une question sur le motif du refus ? Réponds simplement à cet email, on en parle.
</p>
<?php
$content_html = ob_get_clean();
require __DIR__ . '/layout.php';
