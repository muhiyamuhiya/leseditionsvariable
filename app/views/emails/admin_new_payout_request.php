<?php
/** @var object $user */
/** @var string $authorName */
/** @var float  $amount */
/** @var string $method */

$nameSafe = htmlspecialchars($authorName ?: trim(($user->prenom ?? '') . ' ' . ($user->nom ?? '')), ENT_QUOTES, 'UTF-8');
$amountFmt = number_format((float) ($amount ?? 0), 2, ',', ' ');
$methodLabel = match ($method ?? '') {
    'mobile_money' => 'Mobile Money',
    'banque'       => 'Banque / Wise (IBAN)',
    'paypal'       => 'PayPal',
    'stripe'       => 'Stripe',
    default        => 'Paiement en ligne',
};

$title   = '🆕 Nouvelle demande de versement';
$preview = "{$nameSafe} demande {$amountFmt} USD via {$methodLabel}.";

ob_start();
?>
<h1 class="h1 text-main" style="margin:0 0 16px 0;font-family:'Helvetica Neue',Arial,sans-serif;font-size:24px;line-height:1.2;color:#0B0B0F;font-weight:700;letter-spacing:-0.3px;">
    Demande de versement à traiter
</h1>
<p class="text-muted" style="margin:0 0 24px 0;font-family:'Helvetica Neue',Arial,sans-serif;font-size:15px;line-height:1.6;color:#525866;">
    <strong style="color:#0B0B0F;"><?= $nameSafe ?></strong> vient de demander un versement.
</p>

<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="background:#FAF7F2;border-radius:10px;border:1px solid #EBE6DD;margin:0 0 24px 0;">
    <tr><td style="padding:18px 22px;font-family:'Helvetica Neue',Arial,sans-serif;font-size:14px;line-height:1.7;color:#0B0B0F;">
        <p style="margin:0 0 6px 0;"><strong style="color:#525866;">Montant :</strong> <?= $amountFmt ?> USD</p>
        <p style="margin:0 0 6px 0;"><strong style="color:#525866;">Méthode :</strong> <?= htmlspecialchars($methodLabel, ENT_QUOTES, 'UTF-8') ?></p>
        <p style="margin:0;"><strong style="color:#525866;">Email auteur :</strong> <?= htmlspecialchars($user->email ?? '', ENT_QUOTES, 'UTF-8') ?></p>
    </td></tr>
</table>

<table role="presentation" cellpadding="0" cellspacing="0" border="0" align="center" width="100%" style="margin:8px 0 16px 0;">
    <tr>
        <td align="left" style="padding:0;">
            <table role="presentation" cellpadding="0" cellspacing="0" border="0">
                <tr>
                    <td class="btn-cell" align="center" style="background:#0B0B0F;border-radius:10px;">
                        <a href="<?= htmlspecialchars($appUrl . '/admin/finances', ENT_QUOTES, 'UTF-8') ?>" style="display:inline-block;padding:14px 36px;color:#F59E0B;font-weight:700;font-size:15px;font-family:'Helvetica Neue',Arial,sans-serif;text-decoration:none;letter-spacing:0.3px;">
                            Voir dans /admin/finances →
                        </a>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>
<div style="clear:both;font-size:0;line-height:0;height:0;">&nbsp;</div>
<?php
$content_html = ob_get_clean();
require __DIR__ . '/layout.php';
