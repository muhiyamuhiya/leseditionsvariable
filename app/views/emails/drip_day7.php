<?php
/** @var object $user */
/** @var string $appName */
/** @var string $appUrl */

$prenom = htmlspecialchars($user->prenom ?? '', ENT_QUOTES, 'UTF-8');

$title   = "Et si tu lisais sans limite pour 3$/mois ?";
$preview = "Avec l'abonnement Variable, tout le catalogue est à toi. Pas de paiement par livre.";

ob_start();
?>
<h1 class="h1 text-main" style="margin:0 0 16px 0;font-family:'Helvetica Neue',Arial,sans-serif;font-size:28px;line-height:1.2;color:#0B0B0F;font-weight:700;letter-spacing:-0.3px;">
    <?= $prenom ?: 'Hé toi' ?>, prêt·e pour la lecture illimitée ?
</h1>
<p class="text-muted" style="margin:0 0 24px 0;font-family:'Helvetica Neue',Arial,sans-serif;font-size:16px;line-height:1.6;color:#525866;">
    Tu as ton compte depuis une semaine. Tu n'as pas encore choisi de livre — pas de souci, on a une solution qui change la donne :
    <strong style="color:#0B0B0F;">l'abonnement Essentiel à 3$/mois</strong>.
</p>

<!-- Bloc valeur abonnement -->
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="background:#0B0B0F;border-radius:12px;margin:0 0 24px 0;">
    <tr><td style="padding:28px 32px;font-family:'Helvetica Neue',Arial,sans-serif;">
        <p style="margin:0 0 4px 0;font-size:11px;letter-spacing:1.5px;text-transform:uppercase;color:#A0A0B0;font-weight:600;">Essentiel</p>
        <p style="margin:0 0 16px 0;font-size:36px;font-weight:700;color:#F59E0B;letter-spacing:-0.5px;line-height:1;">3$ <span style="font-size:16px;color:#A0A0B0;font-weight:400;">/ mois</span></p>
        <p style="margin:0 0 8px 0;font-size:14px;color:#FFFFFF;line-height:1.6;">
            ✓ Accès <strong>illimité</strong> au catalogue standard<br>
            ✓ Aucun paiement par livre, lis tout ce que tu veux<br>
            ✓ Annulable à tout moment, sans engagement
        </p>
    </td></tr>
</table>

<p class="text-main" style="margin:0 0 24px 0;font-family:'Helvetica Neue',Arial,sans-serif;font-size:15px;line-height:1.6;color:#0B0B0F;">
    Tu peux aussi prendre l'<strong>Essentiel Annuel</strong> à 30$/an (économise 6$ vs mensuel) ou le <strong>Premium</strong> à 8$/mois pour avoir aussi les livres exclusifs.
</p>

<!-- CTA -->
<table role="presentation" cellpadding="0" cellspacing="0" border="0" align="center" width="100%" style="margin:8px 0 16px 0;">
    <tr>
        <td align="left" style="padding:0;">
            <table role="presentation" cellpadding="0" cellspacing="0" border="0">
                <tr>
                    <td class="btn-cell" align="center" style="background:#0B0B0F;border-radius:10px;">
                        <a href="<?= htmlspecialchars($appUrl . '/abonnement', ENT_QUOTES, 'UTF-8') ?>" style="display:inline-block;padding:14px 36px;color:#F59E0B;font-weight:700;font-size:15px;font-family:'Helvetica Neue',Arial,sans-serif;text-decoration:none;letter-spacing:0.3px;">
                            Voir les formules →
                        </a>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>
<div style="clear:both;font-size:0;line-height:0;height:0;">&nbsp;</div>

<p class="text-muted" style="margin:24px 0 0 0;font-family:'Helvetica Neue',Arial,sans-serif;font-size:14px;line-height:1.6;color:#525866;">
    Tu préfères acheter livre par livre ? Aucun problème — le <a href="<?= htmlspecialchars($appUrl . '/catalogue', ENT_QUOTES, 'UTF-8') ?>" style="color:#0B0B0F;font-weight:600;text-decoration:underline;">catalogue</a> reste à ta disposition. L'abo c'est juste une option.
</p>
<?php
$content_html = ob_get_clean();
require __DIR__ . '/layout.php';
