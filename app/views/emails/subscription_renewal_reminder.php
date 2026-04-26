<?php
/** @var object $user */
/** @var string $planLabel */
/** @var float  $amount */
/** @var string $currency */
/** @var string $dateRenewIso */
/** @var string $appName */
/** @var string $appUrl */

$prenom         = htmlspecialchars($user->prenom ?? '', ENT_QUOTES, 'UTF-8');
$dateLisible    = date('d/m/Y', strtotime($dateRenewIso ?? 'now'));
$amountFormatte = number_format((float) ($amount ?? 0), 2, ',', ' ');
$deviseSafe     = htmlspecialchars(strtoupper($currency ?? 'USD'), ENT_QUOTES, 'UTF-8');
$planSafe       = htmlspecialchars($planLabel ?? '', ENT_QUOTES, 'UTF-8');

$title   = 'Rappel : ton abonnement Variable se renouvelle dans 3 jours';
$preview = "Le {$dateLisible}, {$amountFormatte} {$deviseSafe} seront prélevés.";

ob_start();
?>
<h1 class="h1 text-main" style="margin:0 0 16px 0;font-family:'Helvetica Neue',Arial,sans-serif;font-size:28px;line-height:1.2;color:#0B0B0F;font-weight:700;letter-spacing:-0.3px;">
    Salut <?= $prenom ?: 'là' ?> 👋
</h1>
<p class="text-muted" style="margin:0 0 24px 0;font-family:'Helvetica Neue',Arial,sans-serif;font-size:16px;line-height:1.6;color:#525866;">
    Petit rappel amical : ton abonnement <strong style="color:#0B0B0F;"><?= $planSafe ?></strong> se renouvelle automatiquement dans <strong style="color:#0B0B0F;">3 jours</strong>.
</p>

<!-- Carte montant + date -->
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="background:#0B0B0F;border-radius:12px;margin:0 0 24px 0;">
    <tr><td style="padding:24px 28px;font-family:'Helvetica Neue',Arial,sans-serif;">
        <p style="margin:0 0 4px 0;font-size:11px;letter-spacing:1.5px;text-transform:uppercase;color:#A0A0B0;font-weight:600;">Prochain prélèvement</p>
        <p style="margin:0 0 8px 0;font-size:32px;font-weight:700;color:#F59E0B;letter-spacing:-0.5px;"><?= $amountFormatte ?> <?= $deviseSafe ?></p>
        <p style="margin:0;font-size:14px;color:#FFFFFF;">le <?= htmlspecialchars($dateLisible, ENT_QUOTES, 'UTF-8') ?></p>
    </td></tr>
</table>

<p class="text-main" style="margin:0 0 24px 0;font-family:'Helvetica Neue',Arial,sans-serif;font-size:15px;line-height:1.6;color:#0B0B0F;">
    Tu n'as <strong>rien à faire</strong> : le prélèvement est automatique. Si tu veux changer de formule, mettre à jour ta carte ou annuler, c'est une seule clic depuis ton espace.
</p>

<!-- CTA -->
<table role="presentation" cellpadding="0" cellspacing="0" border="0" align="left" style="margin:0 0 24px 0;">
    <tr>
        <td class="btn-cell" align="center" style="background:#0B0B0F;border-radius:10px;">
            <a href="<?= htmlspecialchars($appUrl . '/mon-compte/abonnement', ENT_QUOTES, 'UTF-8') ?>" style="display:inline-block;padding:14px 36px;color:#F59E0B;font-weight:700;font-size:15px;font-family:'Helvetica Neue',Arial,sans-serif;text-decoration:none;letter-spacing:0.3px;">
                Gérer mon abonnement →
            </a>
        </td>
    </tr>
</table>

<p class="text-muted" style="margin:0;font-family:'Helvetica Neue',Arial,sans-serif;font-size:14px;line-height:1.6;color:#525866;">
    Une question ? Réponds à cet email, on est là.
</p>
<?php
$content_html = ob_get_clean();
require __DIR__ . '/layout.php';
