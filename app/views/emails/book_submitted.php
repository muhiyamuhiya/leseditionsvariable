<?php
/** @var object $user */
/** @var string $titreLivre */
/** @var string $appName */
/** @var string $appUrl */

$prenom = htmlspecialchars($user->prenom ?? '', ENT_QUOTES, 'UTF-8');
$titreEsc = htmlspecialchars($titreLivre, ENT_QUOTES, 'UTF-8');

$title   = "Ton livre « {$titreLivre} » est en cours d'examen";
$preview = 'Le comité éditorial va le lire et te revenir.';

ob_start();
?>
<h1 class="h1 text-main" style="margin:0 0 16px 0;font-family:'Helvetica Neue',Arial,sans-serif;font-size:28px;line-height:1.2;color:#0B0B0F;font-weight:700;letter-spacing:-0.3px;">
    Livre soumis ✓
</h1>
<p class="text-muted" style="margin:0 0 24px 0;font-family:'Helvetica Neue',Arial,sans-serif;font-size:16px;line-height:1.6;color:#525866;">
    Bonjour <?= $prenom ?: 'là' ?>,<br>
    Ton livre <strong style="color:#0B0B0F;">« <?= $titreEsc ?> »</strong> est bien arrivé chez nous. Le comité éditorial entame la lecture.
</p>

<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="margin:24px 0;">
    <tr>
        <td valign="top" style="padding:0 0 12px 0;">
            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="background:#FAF7F2;border-radius:10px;border:1px solid #EBE6DD;">
                <tr><td style="padding:18px 20px;font-family:'Helvetica Neue',Arial,sans-serif;">
                    <p style="margin:0 0 6px 0;font-size:11px;letter-spacing:1.5px;text-transform:uppercase;color:#F59E0B;font-weight:700;">Étape 1 — En cours</p>
                    <p style="margin:0;font-size:14px;line-height:1.5;color:#0B0B0F;">Lecture du manuscrit par le comité éditorial.</p>
                </td></tr>
            </table>
        </td>
    </tr>
    <tr>
        <td valign="top" style="padding:0 0 12px 0;">
            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="background:#F4F1EC;border-radius:10px;">
                <tr><td style="padding:18px 20px;font-family:'Helvetica Neue',Arial,sans-serif;">
                    <p style="margin:0 0 6px 0;font-size:11px;letter-spacing:1.5px;text-transform:uppercase;color:#A8A29E;font-weight:700;">Étape 2 — À venir</p>
                    <p style="margin:0;font-size:14px;line-height:1.5;color:#525866;">Retour avec décision (acceptation / ajustements / refus).</p>
                </td></tr>
            </table>
        </td>
    </tr>
    <tr>
        <td valign="top" style="padding:0;">
            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="background:#F4F1EC;border-radius:10px;">
                <tr><td style="padding:18px 20px;font-family:'Helvetica Neue',Arial,sans-serif;">
                    <p style="margin:0 0 6px 0;font-size:11px;letter-spacing:1.5px;text-transform:uppercase;color:#A8A29E;font-weight:700;">Étape 3 — Si validé</p>
                    <p style="margin:0;font-size:14px;line-height:1.5;color:#525866;">Mise en page, couverture, ISBN puis publication.</p>
                </td></tr>
            </table>
        </td>
    </tr>
</table>

<p class="text-muted" style="margin:24px 0 0 0;font-family:'Helvetica Neue',Arial,sans-serif;font-size:13px;line-height:1.6;color:#8A93A6;">
    Délai habituel de retour : <strong style="color:#0B0B0F;">7 à 14 jours</strong>. On t'écrira directement.
</p>
<?php
$content_html = ob_get_clean();
require __DIR__ . '/layout.php';
