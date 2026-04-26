<?php
/** @var object $user */
/** @var float  $amount */
/** @var string $method */

$prenom = htmlspecialchars($user->prenom ?? '', ENT_QUOTES, 'UTF-8');
$amountFmt = number_format((float) ($amount ?? 0), 2, ',', ' ');
$methodLabel = match ($method ?? '') {
    'mobile_money' => 'Mobile Money',
    'banque'       => 'Banque / Wise (IBAN)',
    'paypal'       => 'PayPal',
    'stripe'       => 'Stripe',
    default        => 'Paiement en ligne',
};

$title   = 'Demande de versement reçue';
$preview = "On a bien reçu ta demande de {$amountFmt} USD.";

ob_start();
?>
<h1 class="h1 text-main" style="margin:0 0 16px 0;font-family:'Helvetica Neue',Arial,sans-serif;font-size:28px;line-height:1.2;color:#0B0B0F;font-weight:700;letter-spacing:-0.3px;">
    On s'en occupe, <?= $prenom ?: 'là' ?>.
</h1>
<p class="text-muted" style="margin:0 0 24px 0;font-family:'Helvetica Neue',Arial,sans-serif;font-size:16px;line-height:1.6;color:#525866;">
    Ta demande de versement est bien dans la file. Petit récap :
</p>

<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="background:#0B0B0F;border-radius:12px;margin:0 0 24px 0;">
    <tr><td style="padding:24px 28px;font-family:'Helvetica Neue',Arial,sans-serif;">
        <p style="margin:0 0 4px 0;font-size:11px;letter-spacing:1.5px;text-transform:uppercase;color:#A0A0B0;font-weight:600;">Montant demandé</p>
        <p style="margin:0;font-size:32px;font-weight:700;color:#F59E0B;letter-spacing:-0.5px;"><?= $amountFmt ?> USD</p>
        <p style="margin:8px 0 0 0;font-size:14px;color:#FFFFFF;">Méthode : <?= htmlspecialchars($methodLabel, ENT_QUOTES, 'UTF-8') ?></p>
    </td></tr>
</table>

<p class="text-main" style="margin:0 0 24px 0;font-family:'Helvetica Neue',Arial,sans-serif;font-size:15px;line-height:1.6;color:#0B0B0F;">
    On traite ta demande sous quelques jours ouvrés. Tu recevras un email dès qu'on a confirmé le paiement avec sa référence de transaction.
</p>

<table role="presentation" cellpadding="0" cellspacing="0" border="0" align="center" width="100%" style="margin:8px 0 16px 0;">
    <tr>
        <td align="left" style="padding:0;">
            <table role="presentation" cellpadding="0" cellspacing="0" border="0">
                <tr>
                    <td class="btn-cell" align="center" style="background:#0B0B0F;border-radius:10px;">
                        <a href="<?= htmlspecialchars($appUrl . '/auteur/versements', ENT_QUOTES, 'UTF-8') ?>" style="display:inline-block;padding:14px 36px;color:#F59E0B;font-weight:700;font-size:15px;font-family:'Helvetica Neue',Arial,sans-serif;text-decoration:none;letter-spacing:0.3px;">
                            Suivre mes versements →
                        </a>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>
<div style="clear:both;font-size:0;line-height:0;height:0;">&nbsp;</div>

<p class="text-muted" style="margin:24px 0 0 0;font-family:'Helvetica Neue',Arial,sans-serif;font-size:14px;line-height:1.6;color:#525866;">
    Une question ? Réponds simplement à cet email.
</p>
<?php
$content_html = ob_get_clean();
require __DIR__ . '/layout.php';
