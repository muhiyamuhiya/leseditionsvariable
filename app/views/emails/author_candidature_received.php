<?php
/** @var object $user */
/** @var string $appName */
/** @var string $appUrl */

$prenom = htmlspecialchars($user->prenom ?? '', ENT_QUOTES, 'UTF-8');

$title   = 'Ta candidature d\'auteur est reçue';
$preview = 'On lit ton dossier. Réponse sous 7-14 jours.';

ob_start();
?>
<h1 class="h1 text-main" style="margin:0 0 16px 0;font-family:'Helvetica Neue',Arial,sans-serif;font-size:28px;line-height:1.2;color:#0B0B0F;font-weight:700;letter-spacing:-0.3px;">
    Bien reçu, <?= $prenom ?: 'là' ?>. ✍️
</h1>
<p class="text-muted" style="margin:0 0 24px 0;font-family:'Helvetica Neue',Arial,sans-serif;font-size:16px;line-height:1.6;color:#525866;">
    Ta candidature pour devenir auteur chez Variable est <strong style="color:#0B0B0F;">enregistrée</strong>. Notre comité éditorial va lire ton dossier avec attention.
</p>

<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="background:#0B0B0F;border-radius:12px;margin:24px 0;">
    <tr><td style="padding:24px 28px;font-family:'Helvetica Neue',Arial,sans-serif;">
        <p style="margin:0 0 4px 0;font-size:11px;letter-spacing:1.5px;text-transform:uppercase;color:#A0A0B0;font-weight:600;">Délai de retour</p>
        <p style="margin:0;font-size:32px;font-weight:700;color:#F59E0B;letter-spacing:-0.5px;">7 à 14 jours</p>
        <p style="margin:8px 0 0 0;font-size:14px;color:#FFFFFF;">On t'écrira directement avec un retour détaillé.</p>
    </td></tr>
</table>

<p class="text-main" style="margin:0 0 16px 0;font-family:'Helvetica Neue',Arial,sans-serif;font-size:15px;line-height:1.6;color:#0B0B0F;">
    En attendant, sois patient — on lit chaque dossier avec soin. Si on a besoin d'éclaircissements, on revient vers toi.
</p>
<p class="text-muted" style="margin:0;font-family:'Helvetica Neue',Arial,sans-serif;font-size:13px;line-height:1.6;color:#8A93A6;">
    Une question ? Réponds simplement à cet email.
</p>
<?php
$content_html = ob_get_clean();
require __DIR__ . '/layout.php';
