<?php
/** @var object $user */
/** @var string $appUrl */

$nom = htmlspecialchars(trim(($user->prenom ?? '') . ' ' . ($user->nom ?? '')), ENT_QUOTES, 'UTF-8');
$email = htmlspecialchars($user->email ?? '', ENT_QUOTES, 'UTF-8');

$title   = '🆕 Nouvelle candidature auteur';
$preview = "{$nom} vient de candidater pour devenir auteur Variable.";

ob_start();
?>
<h1 class="h1 text-main" style="margin:0 0 16px 0;font-family:'Helvetica Neue',Arial,sans-serif;font-size:24px;line-height:1.2;color:#0B0B0F;font-weight:700;">
    🆕 Nouvelle candidature auteur
</h1>

<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="background:#FAF7F2;border-radius:10px;border:1px solid #EBE6DD;margin:16px 0 24px 0;">
    <tr><td style="padding:20px 24px;font-family:'Helvetica Neue',Arial,sans-serif;">
        <p style="margin:0 0 4px 0;font-size:11px;letter-spacing:1.5px;text-transform:uppercase;color:#F59E0B;font-weight:700;">Candidat</p>
        <p style="margin:0 0 12px 0;font-size:18px;font-weight:700;color:#0B0B0F;"><?= $nom ?: 'Sans nom' ?></p>
        <p style="margin:0;font-size:13px;color:#525866;">📧 <a href="mailto:<?= $email ?>" style="color:#0B0B0F;font-weight:600;"><?= $email ?></a></p>
    </td></tr>
</table>

<!-- CTA admin -->
<table role="presentation" cellpadding="0" cellspacing="0" border="0" align="left" style="margin:8px 0 24px 0;">
    <tr>
        <td class="btn-cell" align="center" style="background:#0B0B0F;border-radius:10px;">
            <a href="<?= htmlspecialchars($appUrl . '/admin/candidatures', ENT_QUOTES, 'UTF-8') ?>" style="display:inline-block;padding:14px 32px;color:#F59E0B;font-weight:700;font-size:15px;font-family:'Helvetica Neue',Arial,sans-serif;text-decoration:none;letter-spacing:0.3px;">
                Examiner la candidature →
            </a>
        </td>
    </tr>
</table>

<p class="text-muted" style="margin:0;font-family:'Helvetica Neue',Arial,sans-serif;font-size:12px;line-height:1.6;color:#8A93A6;">
    Notification automatique. Délai cible de réponse : 7-14 jours.
</p>
<?php
$content_html = ob_get_clean();
require __DIR__ . '/layout.php';
