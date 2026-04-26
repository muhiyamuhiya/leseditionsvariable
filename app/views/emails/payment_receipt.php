<?php
/** @var object $user */
/** @var string $kind            'book' ou 'subscription' */
/** @var string $itemLabel       Titre du livre ou nom du plan */
/** @var float  $amount */
/** @var string $currency */
/** @var string $paymentMethod   'stripe' | 'money_fusion' | autre */
/** @var string $transactionId */
/** @var string $dateIso         Date au format Y-m-d H:i:s */
/** @var string $appName */
/** @var string $appUrl */

$prenom         = htmlspecialchars($user->prenom ?? '', ENT_QUOTES, 'UTF-8');
$itemLabelSafe  = htmlspecialchars($itemLabel ?? '', ENT_QUOTES, 'UTF-8');
$txSafe         = htmlspecialchars($transactionId ?? '', ENT_QUOTES, 'UTF-8');
$dateLisible    = date('d/m/Y à H:i', strtotime($dateIso ?? 'now'));
$amountFormatte = number_format((float) ($amount ?? 0), 2, ',', ' ');
$deviseSafe     = htmlspecialchars(strtoupper($currency ?? 'USD'), ENT_QUOTES, 'UTF-8');
$methodLabel    = match ($paymentMethod ?? '') {
    'stripe'        => 'Carte (Stripe)',
    'money_fusion'  => 'Mobile Money (Money Fusion)',
    default         => 'Paiement en ligne',
};
$ctaUrl   = ($kind ?? '') === 'subscription'
    ? $appUrl . '/catalogue'
    : $appUrl . '/mon-compte';
$ctaLabel = ($kind ?? '') === 'subscription'
    ? 'Explorer le catalogue →'
    : 'Voir mes livres →';

$title   = 'Reçu de paiement — ' . ($kind === 'subscription' ? 'Abonnement' : 'Achat');
$preview = "Merci {$prenom}, ton paiement de {$amountFormatte} {$deviseSafe} a bien été reçu.";

ob_start();
?>
<h1 class="h1 text-main" style="margin:0 0 16px 0;font-family:'Helvetica Neue',Arial,sans-serif;font-size:28px;line-height:1.2;color:#0B0B0F;font-weight:700;letter-spacing:-0.3px;">
    Merci <?= $prenom ?: 'cher lecteur' ?> ! ✨
</h1>
<p class="text-muted" style="margin:0 0 24px 0;font-family:'Helvetica Neue',Arial,sans-serif;font-size:16px;line-height:1.6;color:#525866;">
    On a bien reçu ton paiement. Voici le récapitulatif — un reçu PDF est joint à cet email pour ta comptabilité.
</p>

<!-- Carte récap montant -->
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="background:#0B0B0F;border-radius:12px;margin:0 0 24px 0;">
    <tr><td style="padding:24px 28px;font-family:'Helvetica Neue',Arial,sans-serif;">
        <p style="margin:0 0 4px 0;font-size:11px;letter-spacing:1.5px;text-transform:uppercase;color:#A0A0B0;font-weight:600;">Montant payé</p>
        <p style="margin:0;font-size:32px;font-weight:700;color:#F59E0B;letter-spacing:-0.5px;"><?= $amountFormatte ?> <?= $deviseSafe ?></p>
    </td></tr>
</table>

<!-- Détails -->
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="background:#FAF7F2;border-radius:10px;border:1px solid #EBE6DD;margin:0 0 24px 0;">
    <tr>
        <td style="padding:18px 22px;font-family:'Helvetica Neue',Arial,sans-serif;font-size:14px;line-height:1.7;color:#0B0B0F;">
            <p style="margin:0 0 6px 0;"><strong style="color:#525866;">Article :</strong> <?= $itemLabelSafe ?></p>
            <p style="margin:0 0 6px 0;"><strong style="color:#525866;">Type :</strong> <?= ($kind ?? '') === 'subscription' ? 'Abonnement' : 'Achat unitaire' ?></p>
            <p style="margin:0 0 6px 0;"><strong style="color:#525866;">Date :</strong> <?= htmlspecialchars($dateLisible, ENT_QUOTES, 'UTF-8') ?></p>
            <p style="margin:0 0 6px 0;"><strong style="color:#525866;">Méthode :</strong> <?= htmlspecialchars($methodLabel, ENT_QUOTES, 'UTF-8') ?></p>
            <p style="margin:0;"><strong style="color:#525866;">Transaction :</strong> <span style="font-family:'SF Mono',Menlo,monospace;font-size:12px;color:#0B0B0F;"><?= $txSafe ?: '—' ?></span></p>
        </td>
    </tr>
</table>

<!-- CTA -->
<table role="presentation" cellpadding="0" cellspacing="0" border="0" align="left" style="margin:8px 0 16px 0;">
    <tr>
        <td class="btn-cell" align="center" style="background:#0B0B0F;border-radius:10px;">
            <a href="<?= htmlspecialchars($ctaUrl, ENT_QUOTES, 'UTF-8') ?>" style="display:inline-block;padding:14px 36px;color:#F59E0B;font-weight:700;font-size:15px;font-family:'Helvetica Neue',Arial,sans-serif;text-decoration:none;letter-spacing:0.3px;">
                <?= $ctaLabel ?>
            </a>
        </td>
    </tr>
</table>

<p class="text-muted" style="margin:24px 0 0 0;font-family:'Helvetica Neue',Arial,sans-serif;font-size:14px;line-height:1.6;color:#525866;">
    Une question sur ton paiement ? Réponds à cet email — on regarde tout de suite.
</p>
<?php
$content_html = ob_get_clean();
require __DIR__ . '/layout.php';
