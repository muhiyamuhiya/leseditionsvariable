<?php
/** @var string $email */
/** @var string $prenom */
/** @var string $appName */
/** @var string $appUrl */

$prenomEsc = htmlspecialchars($prenom ?: 'là', ENT_QUOTES, 'UTF-8');

$title   = 'Ton compte Variable a été supprimé';
$preview = "Suppression effective. Merci d'avoir fait partie de l'aventure.";

ob_start();
?>
<h1 class="h1 text-main" style="margin:0 0 16px 0;font-family:'Helvetica Neue',Arial,sans-serif;font-size:28px;line-height:1.2;color:#0B0B0F;font-weight:700;letter-spacing:-0.3px;">
    Au revoir <?= $prenomEsc ?>. 👋
</h1>
<p class="text-muted" style="margin:0 0 24px 0;font-family:'Helvetica Neue',Arial,sans-serif;font-size:16px;line-height:1.6;color:#525866;">
    Ton compte Variable a été <strong style="color:#0B0B0F;">supprimé</strong> conformément à ta demande. Tes données personnelles ont été effacées de notre base.
</p>

<p class="text-main" style="margin:0 0 16px 0;font-family:'Helvetica Neue',Arial,sans-serif;font-size:15px;line-height:1.6;color:#0B0B0F;">
    Merci d'avoir fait partie de l'aventure. Variable existe parce que des lecteurs comme toi ont cru au projet, ne serait-ce qu'un instant.
</p>
<p class="text-muted" style="margin:0 0 24px 0;font-family:'Helvetica Neue',Arial,sans-serif;font-size:14px;line-height:1.6;color:#525866;">
    Si l'envie te reprend un jour, tu pourras créer un nouveau compte avec la même adresse email.
</p>

<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="background:#FAF7F2;border-radius:10px;border:1px solid #EBE6DD;margin:8px 0;">
    <tr><td style="padding:18px 20px;font-family:'Helvetica Neue',Arial,sans-serif;font-size:13px;line-height:1.6;color:#525866;">
        🌍 Variable continue de soutenir les voix africaines francophones. Tu peux toujours nous suivre publiquement sur <a href="<?= htmlspecialchars($appUrl, ENT_QUOTES, 'UTF-8') ?>" style="color:#0B0B0F;font-weight:600;">leseditionsvariable.com</a>.
    </td></tr>
</table>
<?php
$content_html = ob_get_clean();
require __DIR__ . '/layout.php';
