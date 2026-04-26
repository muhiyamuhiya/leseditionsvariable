<?php
/** @var object $user */
/** @var string $planLabel */
/** @var float  $amount */
/** @var string $currency */
/** @var string $dateRetryIso       Date de la prochaine tentative auto */
/** @var int    $attemptsRemaining  Tentatives restantes avant suspension */
/** @var string $appName */
/** @var string $appUrl */

$prenom         = htmlspecialchars($user->prenom ?? '', ENT_QUOTES, 'UTF-8');
$dateLisible    = date('d/m/Y', strtotime($dateRetryIso ?? 'now'));
$amountFormatte = number_format((float) ($amount ?? 0), 2, ',', ' ');
$deviseSafe     = htmlspecialchars(strtoupper($currency ?? 'USD'), ENT_QUOTES, 'UTF-8');
$planSafe       = htmlspecialchars($planLabel ?? '', ENT_QUOTES, 'UTF-8');
$attemptsLeft   = (int) ($attemptsRemaining ?? 1);

$title   = 'Échec du paiement de ton abonnement Variable';
$preview = "Mets à jour ta carte avant le {$dateLisible} pour éviter la suspension.";

ob_start();
?>
<h1 class="h1 text-main" style="margin:0 0 16px 0;font-family:'Helvetica Neue',Arial,sans-serif;font-size:28px;line-height:1.2;color:#0B0B0F;font-weight:700;letter-spacing:-0.3px;">
    Paiement non abouti, <?= $prenom ?: 'là' ?>.
</h1>
<p class="text-muted" style="margin:0 0 24px 0;font-family:'Helvetica Neue',Arial,sans-serif;font-size:16px;line-height:1.6;color:#525866;">
    On n'a pas pu encaisser le paiement de ton abonnement <strong style="color:#0B0B0F;"><?= $planSafe ?></strong> (<?= $amountFormatte ?> <?= $deviseSafe ?>). Carte expirée, plafond atteint, ou simple refus banque — c'est toujours réversible.
</p>

<!-- Carte alerte -->
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="background:#0B0B0F;border-radius:12px;margin:0 0 24px 0;border:2px solid #F59E0B;">
    <tr><td style="padding:24px 28px;font-family:'Helvetica Neue',Arial,sans-serif;">
        <p style="margin:0 0 4px 0;font-size:11px;letter-spacing:1.5px;text-transform:uppercase;color:#F59E0B;font-weight:700;">⚠ Action requise</p>
        <p style="margin:0 0 12px 0;font-size:20px;font-weight:700;color:#FFFFFF;line-height:1.3;">
            Mets à jour ta carte avant le <?= htmlspecialchars($dateLisible, ENT_QUOTES, 'UTF-8') ?>
        </p>
        <p style="margin:0;font-size:14px;color:#A0A0B0;">
            <?php if ($attemptsLeft > 1): ?>
                On retentera automatiquement le prélèvement dans 3 jours (<?= $attemptsLeft ?> tentative<?= $attemptsLeft > 1 ? 's' : '' ?> restante<?= $attemptsLeft > 1 ? 's' : '' ?>). Sans succès, ton abonnement sera suspendu.
            <?php else: ?>
                C'est la dernière tentative. Sans paiement réussi avant cette date, ton abonnement sera suspendu et tu perdras l'accès au catalogue.
            <?php endif; ?>
        </p>
    </td></tr>
</table>

<!-- CTA primaire -->
<table role="presentation" cellpadding="0" cellspacing="0" border="0" align="center" width="100%" style="margin:8px 0 16px 0;">
    <tr>
        <td align="left" style="padding:0;">
            <table role="presentation" cellpadding="0" cellspacing="0" border="0">
                <tr>
                    <td class="btn-cell" align="center" style="background:#F59E0B;border-radius:10px;">
                        <a href="<?= htmlspecialchars($appUrl . '/mon-compte/abonnement', ENT_QUOTES, 'UTF-8') ?>" style="display:inline-block;padding:14px 36px;color:#0B0B0F;font-weight:700;font-size:15px;font-family:'Helvetica Neue',Arial,sans-serif;text-decoration:none;letter-spacing:0.3px;">
                            Mettre à jour ma carte →
                        </a>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>
<div style="clear:both;font-size:0;line-height:0;height:0;">&nbsp;</div>

<p class="text-muted" style="margin:24px 0 0 0;font-family:'Helvetica Neue',Arial,sans-serif;font-size:14px;line-height:1.6;color:#525866;">
    Si le problème vient de notre côté ou si tu as un doute, réponds simplement à cet email — on règle ça avec toi.
</p>
<?php
$content_html = ob_get_clean();
require __DIR__ . '/layout.php';
