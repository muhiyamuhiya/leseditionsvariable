<?php
/** @var object $user */
/** @var string $token */
/** @var string $appName */
/** @var string $appUrl */

$prenom = htmlspecialchars($user->prenom ?? '', ENT_QUOTES, 'UTF-8');
$confirmUrl = $appUrl . '/supprimer-compte/confirmer/' . $token;

$title   = 'Confirme la suppression de ton compte Variable';
$preview = 'Action irréversible. Lien valable 24h.';

ob_start();
?>
<h1 class="h1 text-main" style="margin:0 0 16px 0;font-family:'Helvetica Neue',Arial,sans-serif;font-size:28px;line-height:1.2;color:#0B0B0F;font-weight:700;letter-spacing:-0.3px;">
    Confirmation de suppression
</h1>
<p class="text-muted" style="margin:0 0 24px 0;font-family:'Helvetica Neue',Arial,sans-serif;font-size:16px;line-height:1.6;color:#525866;">
    Bonjour <?= $prenom ?: 'là' ?>,<br>
    Tu as demandé la suppression de ton compte Variable. Pour confirmer cette action <strong style="color:#0B0B0F;">irréversible</strong>, clique sur le bouton ci-dessous.
</p>

<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="background:#FFF1F0;border-left:3px solid #DC2626;border-radius:6px;margin:16px 0 24px 0;">
    <tr><td style="padding:14px 18px;font-family:'Helvetica Neue',Arial,sans-serif;font-size:13px;line-height:1.6;color:#0B0B0F;">
        ⚠️ <strong>Cette action est définitive.</strong> Toutes tes données (bibliothèque, favoris, historique de lecture) seront effacées.
    </td></tr>
</table>

<!-- CTA -->
<table role="presentation" cellpadding="0" cellspacing="0" border="0" align="center" width="100%" style="margin:24px 0 32px 0;">
    <tr>
        <td align="left" style="padding:0;">
            <table role="presentation" cellpadding="0" cellspacing="0" border="0">
                <tr>
                    <td class="btn-cell" align="center" style="background:#DC2626;border-radius:10px;">
                        <a href="<?= htmlspecialchars($confirmUrl, ENT_QUOTES, 'UTF-8') ?>" style="display:inline-block;padding:14px 36px;color:#FFFFFF;font-weight:700;font-size:15px;font-family:'Helvetica Neue',Arial,sans-serif;text-decoration:none;letter-spacing:0.3px;">
                            Confirmer la suppression
                        </a>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>
<div style="clear:both;font-size:0;line-height:0;height:0;">&nbsp;</div>

<p class="text-muted" style="margin:0 0 8px 0;font-family:'Helvetica Neue',Arial,sans-serif;font-size:13px;line-height:1.6;color:#8A93A6;">
    Ce lien est valable <strong>24 heures</strong>.
</p>
<p class="text-muted" style="margin:0;font-family:'Helvetica Neue',Arial,sans-serif;font-size:13px;line-height:1.6;color:#8A93A6;">
    <strong>Tu n'as pas demandé cela ?</strong> Ignore simplement cet email. Ton compte ne sera pas supprimé. Pour sécuriser ton compte, change ton mot de passe.
</p>
<?php
$content_html = ob_get_clean();
require __DIR__ . '/layout.php';
