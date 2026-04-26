<?php
/** @var object $user */
/** @var string $appName */
/** @var string $appUrl */

$prenom = htmlspecialchars($user->prenom ?? '', ENT_QUOTES, 'UTF-8');

$title   = 'Bienvenue sur Variable !';
$preview = 'Ton compte est activé. Découvre nos auteurs, abonne-toi, ou publie ton livre.';

ob_start();
?>
<h1 class="h1 text-main" style="margin:0 0 16px 0;font-family:'Helvetica Neue',Arial,sans-serif;font-size:28px;line-height:1.2;color:#0B0B0F;font-weight:700;letter-spacing:-0.3px;">
    Bienvenue à bord, <?= $prenom ?: 'cher lecteur' ?> ! 🎉
</h1>
<p class="text-muted" style="margin:0 0 24px 0;font-family:'Helvetica Neue',Arial,sans-serif;font-size:16px;line-height:1.6;color:#525866;">
    Ton compte est activé. Tu fais maintenant partie d'une communauté qui célèbre les voix africaines francophones, partout dans le monde.
</p>

<!-- 3 cards stack-able -->
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="margin:8px 0 28px 0;">
    <tr>
        <td class="stack-mob" valign="top" style="padding:0 8px 12px 0;">
            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="background:#FAF7F2;border-radius:10px;border:1px solid #EBE6DD;">
                <tr><td style="padding:16px;font-family:'Helvetica Neue',Arial,sans-serif;">
                    <p style="margin:0 0 4px 0;font-size:11px;letter-spacing:1px;text-transform:uppercase;color:#F59E0B;font-weight:700;">📚 Lecture</p>
                    <p style="margin:0;font-size:13px;line-height:1.5;color:#0B0B0F;">Parcours notre catalogue, lis 10 pages gratuites de chaque livre.</p>
                </td></tr>
            </table>
        </td>
        <td class="stack-mob" valign="top" style="padding:0 8px 12px 8px;">
            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="background:#FAF7F2;border-radius:10px;border:1px solid #EBE6DD;">
                <tr><td style="padding:16px;font-family:'Helvetica Neue',Arial,sans-serif;">
                    <p style="margin:0 0 4px 0;font-size:11px;letter-spacing:1px;text-transform:uppercase;color:#F59E0B;font-weight:700;">∞ Abonnement</p>
                    <p style="margin:0;font-size:13px;line-height:1.5;color:#0B0B0F;">Accès illimité dès 3$/mois. Premium à 8$/mois.</p>
                </td></tr>
            </table>
        </td>
        <td class="stack-mob" valign="top" style="padding:0 0 12px 8px;">
            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="background:#FAF7F2;border-radius:10px;border:1px solid #EBE6DD;">
                <tr><td style="padding:16px;font-family:'Helvetica Neue',Arial,sans-serif;">
                    <p style="margin:0 0 4px 0;font-size:11px;letter-spacing:1px;text-transform:uppercase;color:#F59E0B;font-weight:700;">✍️ Auteur</p>
                    <p style="margin:0;font-size:13px;line-height:1.5;color:#0B0B0F;">Tu écris ? Publie ton livre, garde 70% des revenus.</p>
                </td></tr>
            </table>
        </td>
    </tr>
</table>

<!-- CTA -->
<table role="presentation" cellpadding="0" cellspacing="0" border="0" align="left" style="margin:8px 0 16px 0;">
    <tr>
        <td class="btn-cell" align="center" style="background:#0B0B0F;border-radius:10px;">
            <a href="<?= htmlspecialchars($appUrl . '/catalogue', ENT_QUOTES, 'UTF-8') ?>" style="display:inline-block;padding:14px 36px;color:#F59E0B;font-weight:700;font-size:15px;font-family:'Helvetica Neue',Arial,sans-serif;text-decoration:none;letter-spacing:0.3px;">
                Explorer le catalogue →
            </a>
        </td>
    </tr>
</table>

<p class="text-muted" style="margin:24px 0 0 0;font-family:'Helvetica Neue',Arial,sans-serif;font-size:14px;line-height:1.6;color:#525866;">
    Une question ? Réponds simplement à cet email, ou utilise le chat sur le site.
    <br>Bonne lecture, et à bientôt sur Variable.
</p>
<?php
$content_html = ob_get_clean();
require __DIR__ . '/layout.php';
