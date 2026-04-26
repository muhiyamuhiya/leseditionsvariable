<?php
/** @var object $user */
/** @var string $token */
/** @var string $appName */
/** @var string $appUrl */

$prenom = htmlspecialchars($user->prenom ?? '', ENT_QUOTES, 'UTF-8');
$resetUrl = $appUrl . '/reset-password/' . $token;

$title   = 'Réinitialise ton mot de passe — Variable';
$preview = 'Tu as demandé un nouveau mot de passe. Lien valable 1h.';

ob_start();
?>
<h1 class="h1 text-main" style="margin:0 0 16px 0;font-family:'Helvetica Neue',Arial,sans-serif;font-size:28px;line-height:1.2;color:#0B0B0F;font-weight:700;letter-spacing:-0.3px;">
    Nouveau mot de passe 🔐
</h1>
<p class="text-muted" style="margin:0 0 24px 0;font-family:'Helvetica Neue',Arial,sans-serif;font-size:16px;line-height:1.6;color:#525866;">
    Bonjour <?= $prenom ?: 'là' ?>,<br>
    Tu as demandé à réinitialiser ton mot de passe. Clique sur le bouton ci-dessous pour choisir un nouveau mot de passe.
</p>

<!-- CTA -->
<table role="presentation" cellpadding="0" cellspacing="0" border="0" align="center" width="100%" style="margin:24px 0 32px 0;">
    <tr>
        <td align="left" style="padding:0;">
            <table role="presentation" cellpadding="0" cellspacing="0" border="0">
                <tr>
                    <td class="btn-cell" align="center" style="background:#0B0B0F;border-radius:10px;">
                        <a href="<?= htmlspecialchars($resetUrl, ENT_QUOTES, 'UTF-8') ?>" style="display:inline-block;padding:14px 36px;color:#F59E0B;font-weight:700;font-size:15px;font-family:'Helvetica Neue',Arial,sans-serif;text-decoration:none;letter-spacing:0.3px;">
                            Choisir un nouveau mot de passe →
                        </a>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>
<div style="clear:both;font-size:0;line-height:0;height:0;">&nbsp;</div>

<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="background:#FFF7E6;border-left:3px solid #F59E0B;border-radius:6px;margin:16px 0 24px 0;">
    <tr><td style="padding:14px 18px;font-family:'Helvetica Neue',Arial,sans-serif;font-size:13px;line-height:1.6;color:#0B0B0F;">
        ⏱️ Ce lien est valable <strong>1 heure</strong>. Au-delà, refais une demande.
    </td></tr>
</table>

<p class="text-muted" style="margin:0;font-family:'Helvetica Neue',Arial,sans-serif;font-size:13px;line-height:1.6;color:#8A93A6;">
    Si tu n'as pas fait cette demande, tu peux ignorer cet email — ton mot de passe actuel reste inchangé. Pour toute inquiétude, contacte-nous à
    <a href="mailto:contact@leseditionsvariable.com" style="color:#0B0B0F;font-weight:600;">contact@leseditionsvariable.com</a>.
</p>
<?php
$content_html = ob_get_clean();
require __DIR__ . '/layout.php';
