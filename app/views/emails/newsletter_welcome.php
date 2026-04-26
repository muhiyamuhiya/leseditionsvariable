<?php
/** @var string $prenom */
/** @var string $appName */
/** @var string $appUrl */

$prenomEsc = htmlspecialchars($prenom ?: '', ENT_QUOTES, 'UTF-8');
$bonjour = $prenomEsc !== '' ? 'Bonjour ' . $prenomEsc : 'Bonjour';

$title   = 'Tu es dans la newsletter Variable !';
$preview = 'Une fois par mois — coulisses, nouveaux livres, codes promo. Pas de spam.';

ob_start();
?>
<h1 class="h1 text-main" style="margin:0 0 16px 0;font-family:'Helvetica Neue',Arial,sans-serif;font-size:28px;line-height:1.2;color:#0B0B0F;font-weight:700;letter-spacing:-0.3px;">
    Tu y es. 📬
</h1>
<p class="text-muted" style="margin:0 0 24px 0;font-family:'Helvetica Neue',Arial,sans-serif;font-size:16px;line-height:1.6;color:#525866;">
    <?= $bonjour ?>, merci pour ton inscription à la newsletter Variable !
</p>

<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="margin:24px 0;">
    <tr>
        <td class="stack-mob" valign="top" style="padding:0 8px 12px 0;width:50%;">
            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="background:#FAF7F2;border-radius:10px;border:1px solid #EBE6DD;">
                <tr><td style="padding:18px;font-family:'Helvetica Neue',Arial,sans-serif;">
                    <p style="margin:0 0 6px 0;font-size:11px;letter-spacing:1.5px;text-transform:uppercase;color:#F59E0B;font-weight:700;">📅 Rythme</p>
                    <p style="margin:0;font-size:14px;line-height:1.5;color:#0B0B0F;font-weight:600;">Une fois par mois</p>
                    <p style="margin:4px 0 0 0;font-size:12px;line-height:1.5;color:#525866;">Pas plus, jamais.</p>
                </td></tr>
            </table>
        </td>
        <td class="stack-mob" valign="top" style="padding:0 0 12px 8px;width:50%;">
            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="background:#FAF7F2;border-radius:10px;border:1px solid #EBE6DD;">
                <tr><td style="padding:18px;font-family:'Helvetica Neue',Arial,sans-serif;">
                    <p style="margin:0 0 6px 0;font-size:11px;letter-spacing:1.5px;text-transform:uppercase;color:#F59E0B;font-weight:700;">🎁 Bonus</p>
                    <p style="margin:0;font-size:14px;line-height:1.5;color:#0B0B0F;font-weight:600;">Codes promo exclusifs</p>
                    <p style="margin:4px 0 0 0;font-size:12px;line-height:1.5;color:#525866;">Avant tout le monde.</p>
                </td></tr>
            </table>
        </td>
    </tr>
</table>

<p class="text-main" style="margin:24px 0 0 0;font-family:'Helvetica Neue',Arial,sans-serif;font-size:15px;line-height:1.6;color:#0B0B0F;">
    Au programme : coulisses des éditions, nouveaux livres, conseils d'auteurs, et des codes promo réservés aux abonnés newsletter.
</p>

<!-- CTA -->
<table role="presentation" cellpadding="0" cellspacing="0" border="0" align="center" width="100%" style="margin:24px 0 8px 0;">
    <tr>
        <td align="left" style="padding:0;">
            <table role="presentation" cellpadding="0" cellspacing="0" border="0">
                <tr>
                    <td class="btn-cell" align="center" style="background:#0B0B0F;border-radius:10px;">
                        <a href="<?= htmlspecialchars($appUrl . '/catalogue', ENT_QUOTES, 'UTF-8') ?>" style="display:inline-block;padding:14px 36px;color:#F59E0B;font-weight:700;font-size:15px;font-family:'Helvetica Neue',Arial,sans-serif;text-decoration:none;letter-spacing:0.3px;">
                            Découvrir le catalogue →
                        </a>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>
<div style="clear:both;font-size:0;line-height:0;height:0;">&nbsp;</div>
<?php
$content_html = ob_get_clean();
require __DIR__ . '/layout.php';
