<?php
/** @var object $user */
/** @var string $planLabel */
/** @var float  $amount */
/** @var string $currency */
/** @var string $dateNextRenewIso */
/** @var string $transactionId */
/** @var string $appName */
/** @var string $appUrl */

$prenom         = htmlspecialchars($user->prenom ?? '', ENT_QUOTES, 'UTF-8');
$dateLisible    = date('d/m/Y', strtotime($dateNextRenewIso ?? 'now'));
$amountFormatte = number_format((float) ($amount ?? 0), 2, ',', ' ');
$deviseSafe     = htmlspecialchars(strtoupper($currency ?? 'USD'), ENT_QUOTES, 'UTF-8');
$planSafe       = htmlspecialchars($planLabel ?? '', ENT_QUOTES, 'UTF-8');
$txSafe         = htmlspecialchars($transactionId ?? '', ENT_QUOTES, 'UTF-8');

$title   = 'Ton abonnement Variable est renouvelé';
$preview = "Prochain prélèvement le {$dateLisible}.";

ob_start();
?>
<h1 class="h1 text-main" style="margin:0 0 16px 0;font-family:'Helvetica Neue',Arial,sans-serif;font-size:28px;line-height:1.2;color:#0B0B0F;font-weight:700;letter-spacing:-0.3px;">
    C'est reparti, <?= $prenom ?: 'là' ?> 🎉
</h1>
<p class="text-muted" style="margin:0 0 24px 0;font-family:'Helvetica Neue',Arial,sans-serif;font-size:16px;line-height:1.6;color:#525866;">
    Ton abonnement <strong style="color:#0B0B0F;"><?= $planSafe ?></strong> vient d'être renouvelé. <?= $amountFormatte ?> <?= $deviseSafe ?> ont été prélevés. Continue à lire sans interruption.
</p>

<!-- Carte prochain renouvellement -->
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="background:#0B0B0F;border-radius:12px;margin:0 0 24px 0;">
    <tr><td style="padding:24px 28px;font-family:'Helvetica Neue',Arial,sans-serif;">
        <p style="margin:0 0 4px 0;font-size:11px;letter-spacing:1.5px;text-transform:uppercase;color:#A0A0B0;font-weight:600;">Prochain renouvellement</p>
        <p style="margin:0;font-size:32px;font-weight:700;color:#F59E0B;letter-spacing:-0.5px;"><?= htmlspecialchars($dateLisible, ENT_QUOTES, 'UTF-8') ?></p>
        <p style="margin:8px 0 0 0;font-size:14px;color:#FFFFFF;">Tu peux annuler ou changer de formule à tout moment.</p>
    </td></tr>
</table>

<!-- Détails transaction -->
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="background:#FAF7F2;border-radius:10px;border:1px solid #EBE6DD;margin:0 0 24px 0;">
    <tr>
        <td style="padding:16px 22px;font-family:'Helvetica Neue',Arial,sans-serif;font-size:13px;line-height:1.7;color:#525866;">
            <p style="margin:0;"><strong>Référence transaction :</strong> <span style="font-family:'SF Mono',Menlo,monospace;font-size:12px;color:#0B0B0F;"><?= $txSafe ?: '—' ?></span></p>
        </td>
    </tr>
</table>

<!-- CTA -->
<table role="presentation" cellpadding="0" cellspacing="0" border="0" align="left" style="margin:0 0 16px 0;">
    <tr>
        <td class="btn-cell" align="center" style="background:#0B0B0F;border-radius:10px;">
            <a href="<?= htmlspecialchars($appUrl . '/catalogue', ENT_QUOTES, 'UTF-8') ?>" style="display:inline-block;padding:14px 36px;color:#F59E0B;font-weight:700;font-size:15px;font-family:'Helvetica Neue',Arial,sans-serif;text-decoration:none;letter-spacing:0.3px;">
                Découvrir les nouveautés →
            </a>
        </td>
    </tr>
</table>

<p class="text-muted" style="margin:24px 0 0 0;font-family:'Helvetica Neue',Arial,sans-serif;font-size:14px;line-height:1.6;color:#525866;">
    Bonne lecture.
</p>
<?php
$content_html = ob_get_clean();
require __DIR__ . '/layout.php';
