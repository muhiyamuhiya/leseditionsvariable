<?php
/** @var object $user */
/** @var string $token */
/** @var string $appName */
/** @var string $appUrl */

$prenom = htmlspecialchars($user->prenom ?? '', ENT_QUOTES, 'UTF-8');
$verifyUrl = $appUrl . '/verifier-email/' . $token;

$title   = 'Vérifie ton adresse email — Les éditions Variable';
$preview = 'Confirme ton adresse pour activer ton compte Variable.';

ob_start();
?>
<h1 class="h1 text-main" style="margin:0 0 16px 0;font-family:'Helvetica Neue',Arial,sans-serif;font-size:28px;line-height:1.2;color:#0B0B0F;font-weight:700;letter-spacing:-0.3px;">
    Bienvenue, <?= $prenom ?: 'futur lecteur' ?>. ✨
</h1>
<p class="text-muted" style="margin:0 0 24px 0;font-family:'Helvetica Neue',Arial,sans-serif;font-size:16px;line-height:1.6;color:#525866;">
    Plus qu'<strong style="color:#0B0B0F;">une étape</strong> pour rejoindre la communauté Variable et plonger dans la littérature africaine francophone.
</p>
<p class="text-main" style="margin:0 0 8px 0;font-family:'Helvetica Neue',Arial,sans-serif;font-size:15px;line-height:1.6;color:#0B0B0F;">
    Confirme ton adresse en cliquant sur le bouton ci-dessous :
</p>

<!-- CTA -->
<table role="presentation" cellpadding="0" cellspacing="0" border="0" align="left" style="margin:24px 0 32px 0;">
    <tr>
        <td class="btn-cell" align="center" style="background:#0B0B0F;border-radius:10px;">
            <a href="<?= htmlspecialchars($verifyUrl, ENT_QUOTES, 'UTF-8') ?>" style="display:inline-block;padding:14px 36px;color:#F59E0B;font-weight:700;font-size:15px;font-family:'Helvetica Neue',Arial,sans-serif;text-decoration:none;letter-spacing:0.3px;">
                Activer mon compte →
            </a>
        </td>
    </tr>
</table>

<p class="text-muted" style="margin:0 0 8px 0;font-family:'Helvetica Neue',Arial,sans-serif;font-size:13px;line-height:1.6;color:#8A93A6;">
    Ce lien est valable <strong>48 heures</strong>. Si tu n'as pas créé de compte, ignore simplement cet email.
</p>
<p class="text-muted" style="margin:0 0 4px 0;font-family:'Helvetica Neue',Arial,sans-serif;font-size:12px;line-height:1.5;color:#A8A29E;">
    Lien direct si le bouton ne fonctionne pas :
</p>
<p class="text-muted" style="margin:0;font-family:'Courier New',monospace;font-size:11px;line-height:1.5;color:#8A93A6;word-break:break-all;">
    <?= htmlspecialchars($verifyUrl, ENT_QUOTES, 'UTF-8') ?>
</p>
<?php
$content_html = ob_get_clean();
require __DIR__ . '/layout.php';
