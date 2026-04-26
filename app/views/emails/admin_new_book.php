<?php
/** @var object $user */
/** @var string $titreLivre */
/** @var string $appUrl */

$nom = htmlspecialchars(trim(($user->prenom ?? '') . ' ' . ($user->nom ?? '')), ENT_QUOTES, 'UTF-8');
$email = htmlspecialchars($user->email ?? '', ENT_QUOTES, 'UTF-8');
$titreEsc = htmlspecialchars($titreLivre, ENT_QUOTES, 'UTF-8');

$title   = '📚 Nouveau livre soumis';
$preview = "{$nom} vient de soumettre « {$titreLivre} ».";

ob_start();
?>
<h1 class="h1 text-main" style="margin:0 0 16px 0;font-family:'Helvetica Neue',Arial,sans-serif;font-size:24px;line-height:1.2;color:#0B0B0F;font-weight:700;">
    📚 Nouveau livre soumis
</h1>

<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="background:#0B0B0F;border-radius:12px;margin:16px 0 24px 0;">
    <tr><td style="padding:24px 28px;font-family:'Helvetica Neue',Arial,sans-serif;">
        <p style="margin:0 0 4px 0;font-size:11px;letter-spacing:1.5px;text-transform:uppercase;color:#A0A0B0;font-weight:600;">Titre du livre</p>
        <p style="margin:0;font-size:22px;font-weight:700;color:#F59E0B;letter-spacing:-0.3px;">« <?= $titreEsc ?> »</p>
    </td></tr>
</table>

<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="background:#FAF7F2;border-radius:10px;border:1px solid #EBE6DD;margin:0 0 24px 0;">
    <tr><td style="padding:20px 24px;font-family:'Helvetica Neue',Arial,sans-serif;">
        <p style="margin:0 0 4px 0;font-size:11px;letter-spacing:1.5px;text-transform:uppercase;color:#F59E0B;font-weight:700;">Auteur</p>
        <p style="margin:0 0 8px 0;font-size:16px;font-weight:600;color:#0B0B0F;"><?= $nom ?: 'Sans nom' ?></p>
        <p style="margin:0;font-size:13px;color:#525866;">📧 <a href="mailto:<?= $email ?>" style="color:#0B0B0F;font-weight:600;"><?= $email ?></a></p>
    </td></tr>
</table>

<!-- CTA admin -->
<table role="presentation" cellpadding="0" cellspacing="0" border="0" align="left" style="margin:8px 0 24px 0;">
    <tr>
        <td class="btn-cell" align="center" style="background:#0B0B0F;border-radius:10px;">
            <a href="<?= htmlspecialchars($appUrl . '/admin/livres', ENT_QUOTES, 'UTF-8') ?>" style="display:inline-block;padding:14px 32px;color:#F59E0B;font-weight:700;font-size:15px;font-family:'Helvetica Neue',Arial,sans-serif;text-decoration:none;letter-spacing:0.3px;">
                Examiner le livre →
            </a>
        </td>
    </tr>
</table>

<p class="text-muted" style="margin:0;font-family:'Helvetica Neue',Arial,sans-serif;font-size:12px;line-height:1.6;color:#8A93A6;">
    Notification automatique.
</p>
<?php
$content_html = ob_get_clean();
require __DIR__ . '/layout.php';
