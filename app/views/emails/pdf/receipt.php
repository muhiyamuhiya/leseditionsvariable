<?php
/**
 * Template PDF — Reçu de paiement Variable.
 * HTML très simple (dompdf est plus restreint que les navigateurs).
 *
 * Variables attendues :
 *   $kind          'book' | 'subscription'
 *   $itemLabel     Titre du livre ou nom du plan
 *   $amount        Montant payé
 *   $currency      Devise (USD, EUR, CDF, ...)
 *   $paymentMethod 'stripe' | 'money_fusion' | autre
 *   $transactionId
 *   $dateIso       Date Y-m-d H:i:s
 *   $user          object (prenom, nom, email)
 *   $appName, $appUrl
 */
$prenom        = htmlspecialchars(($user->prenom ?? '') . ' ' . ($user->nom ?? ''), ENT_QUOTES, 'UTF-8');
$emailUser     = htmlspecialchars($user->email ?? '', ENT_QUOTES, 'UTF-8');
$itemSafe      = htmlspecialchars($itemLabel ?? '', ENT_QUOTES, 'UTF-8');
$txSafe        = htmlspecialchars($transactionId ?? '', ENT_QUOTES, 'UTF-8');
$dateLisible   = date('d/m/Y à H:i', strtotime($dateIso ?? 'now'));
$amountFormatte = number_format((float) ($amount ?? 0), 2, ',', ' ');
$deviseSafe    = htmlspecialchars(strtoupper($currency ?? 'USD'), ENT_QUOTES, 'UTF-8');
$methodLabel   = match ($paymentMethod ?? '') {
    'stripe'        => 'Carte (Stripe)',
    'money_fusion'  => 'Mobile Money (Money Fusion)',
    default         => 'Paiement en ligne',
};
$typeLabel = ($kind ?? '') === 'subscription' ? 'Abonnement' : 'Achat unitaire';
$numero    = 'VAR-' . date('Ymd-His') . '-' . substr(md5((string) ($transactionId ?? uniqid())), 0, 6);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<title>Reçu Variable — <?= $numero ?></title>
<style>
    @page { margin: 30mm 20mm 25mm 20mm; }
    body { font-family: DejaVu Sans, sans-serif; font-size: 11pt; color: #0B0B0F; line-height: 1.5; }
    .header { border-bottom: 3px solid #F59E0B; padding-bottom: 12px; margin-bottom: 24px; }
    .brand { font-size: 18pt; font-weight: bold; color: #0B0B0F; }
    .brand .dot { color: #F59E0B; }
    .meta { font-size: 9pt; color: #6B6B7D; margin-top: 4px; }
    h1 { font-size: 16pt; margin: 0 0 4px 0; color: #0B0B0F; }
    .receipt-num { font-family: monospace; font-size: 10pt; color: #525866; }
    table.kv { width: 100%; border-collapse: collapse; margin-top: 18px; }
    table.kv td { padding: 8px 0; border-bottom: 1px solid #EBE6DD; vertical-align: top; }
    table.kv td.label { color: #525866; font-weight: bold; width: 35%; font-size: 10pt; text-transform: uppercase; letter-spacing: 0.5px; }
    table.kv td.value { color: #0B0B0F; font-size: 11pt; }
    .total-box { margin-top: 24px; background: #0B0B0F; color: #FFFFFF; padding: 18px 22px; border-radius: 6px; }
    .total-box .label { font-size: 9pt; text-transform: uppercase; letter-spacing: 1.5px; color: #A0A0B0; }
    .total-box .value { font-size: 22pt; font-weight: bold; color: #F59E0B; margin-top: 4px; }
    .footer { margin-top: 40px; font-size: 9pt; color: #6B6B7D; text-align: center; border-top: 1px solid #EBE6DD; padding-top: 14px; }
    .footer .legal { color: #A8A29E; font-size: 8pt; margin-top: 6px; }
</style>
</head>
<body>

<div class="header">
    <div class="brand"><span class="dot">●</span> Les éditions <span style="color:#F59E0B;">Variable</span></div>
    <div class="meta">leseditionsvariable.com · contact@leseditionsvariable.com</div>
</div>

<h1>Reçu de paiement</h1>
<div class="receipt-num">N° <?= $numero ?></div>

<table class="kv">
    <tr>
        <td class="label">Client</td>
        <td class="value"><?= $prenom ?: $emailUser ?><br><span style="color:#525866;font-size:10pt;"><?= $emailUser ?></span></td>
    </tr>
    <tr>
        <td class="label">Date</td>
        <td class="value"><?= htmlspecialchars($dateLisible, ENT_QUOTES, 'UTF-8') ?></td>
    </tr>
    <tr>
        <td class="label">Type</td>
        <td class="value"><?= $typeLabel ?></td>
    </tr>
    <tr>
        <td class="label">Article</td>
        <td class="value"><?= $itemSafe ?></td>
    </tr>
    <tr>
        <td class="label">Méthode</td>
        <td class="value"><?= htmlspecialchars($methodLabel, ENT_QUOTES, 'UTF-8') ?></td>
    </tr>
    <tr>
        <td class="label">Référence transaction</td>
        <td class="value" style="font-family:monospace;font-size:10pt;"><?= $txSafe ?: '—' ?></td>
    </tr>
</table>

<div class="total-box">
    <div class="label">Total payé</div>
    <div class="value"><?= $amountFormatte ?> <?= $deviseSafe ?></div>
</div>

<div class="footer">
    Reçu généré automatiquement par Variable. À conserver pour ta comptabilité.<br>
    <span class="legal">Les éditions Variable · Plateforme de littérature africaine francophone · © <?= date('Y') ?></span>
</div>

</body>
</html>
