<?php
/**
 * Layout email principal — wrapper commercial pour tous les emails transactionnels.
 *
 * Variables attendues :
 *   $title          — title HTML + preview header
 *   $preview        — preview text caché (avant l'aperçu boîte mail)
 *   $content_html   — corps de l'email (déjà sécurisé, HTML autorisé)
 *
 * Compatible Gmail, Apple Mail, Outlook (table-based, inline styles).
 */
$appName = function_exists('env') ? env('APP_NAME', 'Les éditions Variable') : 'Les éditions Variable';
$appUrl  = function_exists('env') ? rtrim((string) env('APP_URL', 'https://leseditionsvariable.com'), '/') : 'https://leseditionsvariable.com';
$year    = date('Y');
$title   = $title ?? $appName;
$preview = $preview ?? '';
?><!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<title><?= htmlspecialchars($title, ENT_QUOTES, 'UTF-8') ?></title>
<style>
    /* Reset rapide email + responsive */
    body, table, td, p, a { -webkit-text-size-adjust:100%; -ms-text-size-adjust:100%; }
    body { margin:0; padding:0; width:100%!important; background:#F4F1EC; font-family:-apple-system,BlinkMacSystemFont,'Segoe UI','Helvetica Neue',Arial,sans-serif; }
    img { border:0; outline:none; text-decoration:none; -ms-interpolation-mode:bicubic; display:block; }
    a { text-decoration:none; }
    .preview { display:none; max-height:0; overflow:hidden; opacity:0; mso-hide:all; visibility:hidden; }
    @media only screen and (max-width:620px) {
        .container { width:100%!important; padding:0!important; }
        .px { padding-left:24px!important; padding-right:24px!important; }
        .h1 { font-size:24px!important; line-height:1.25!important; }
        .btn-cell { padding-top:8px!important; padding-bottom:24px!important; }
        .stack-mob { display:block!important; width:100%!important; }
    }
    @media (prefers-color-scheme: dark) {
        body, .body-bg { background:#0B0B0F!important; }
        .card-bg { background:#141419!important; border-color:#2A2A35!important; }
        .text-main { color:#FFFFFF!important; }
        .text-muted { color:#A0A0B0!important; }
        .footer-bg { background:#0B0B0F!important; color:#6B6B7D!important; }
        .divider { border-color:#2A2A35!important; }
    }
</style>
</head>
<body class="body-bg" style="margin:0;padding:0;background:#F4F1EC;">

<span class="preview" style="display:none;max-height:0;overflow:hidden;opacity:0;visibility:hidden;mso-hide:all;"><?= htmlspecialchars($preview, ENT_QUOTES, 'UTF-8') ?></span>

<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="background:#F4F1EC;" class="body-bg">
    <tr>
        <td align="center" style="padding:32px 16px;">

            <!-- Container 600px -->
            <table role="presentation" width="600" cellpadding="0" cellspacing="0" border="0" class="container" style="max-width:600px;width:100%;">

                <!-- Header band ambre -->
                <tr>
                    <td style="background:#0B0B0F;border-radius:16px 16px 0 0;padding:28px 32px;" class="card-bg">
                        <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0">
                            <tr>
                                <td>
                                    <a href="<?= htmlspecialchars($appUrl, ENT_QUOTES, 'UTF-8') ?>" style="color:#FFFFFF;text-decoration:none;font-family:'Helvetica Neue',Arial,sans-serif;font-weight:700;font-size:18px;letter-spacing:0.5px;">
                                        <span style="color:#F59E0B;">●</span> Les éditions <span style="color:#F59E0B;">Variable</span>
                                    </a>
                                </td>
                                <td align="right" style="font-family:'Helvetica Neue',Arial,sans-serif;font-size:11px;letter-spacing:1.5px;text-transform:uppercase;color:#A0A0B0;">
                                    Lis. Vis. Crée.
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>

                <!-- Bande ambre -->
                <tr>
                    <td style="background:#F59E0B;height:4px;line-height:4px;font-size:0;">&nbsp;</td>
                </tr>

                <!-- Carte contenu -->
                <tr>
                    <td class="card-bg px" style="background:#FFFFFF;padding:40px 48px 32px 48px;">
                        <?= $content_html ?? '' ?>
                    </td>
                </tr>

                <!-- Bas de carte : signature + arrondi -->
                <tr>
                    <td class="card-bg px" style="background:#FFFFFF;border-radius:0 0 16px 16px;padding:0 48px 36px 48px;">
                        <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0">
                            <tr>
                                <td class="divider" style="border-top:1px solid #EBE6DD;padding-top:24px;font-family:'Helvetica Neue',Arial,sans-serif;font-size:14px;line-height:1.6;color:#0B0B0F;" >
                                    <p class="text-main" style="margin:0 0 4px 0;color:#0B0B0F;">Cordialement,</p>
                                    <p class="text-main" style="margin:0;font-weight:600;color:#0B0B0F;">L'équipe Variable</p>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>

                <!-- Spacer -->
                <tr><td style="height:24px;line-height:24px;font-size:0;">&nbsp;</td></tr>

                <!-- Footer -->
                <tr>
                    <td class="footer-bg" align="center" style="font-family:'Helvetica Neue',Arial,sans-serif;font-size:12px;line-height:1.6;color:#6B6B7D;padding:16px 24px;">
                        <p style="margin:0 0 8px 0;">
                            <a href="<?= htmlspecialchars($appUrl, ENT_QUOTES, 'UTF-8') ?>" style="color:#0B0B0F;font-weight:600;text-decoration:none;" class="text-main">leseditionsvariable.com</a>
                            &nbsp;·&nbsp;
                            <a href="<?= htmlspecialchars($appUrl, ENT_QUOTES, 'UTF-8') ?>/catalogue" style="color:#6B6B7D;text-decoration:none;" class="text-muted">Catalogue</a>
                            &nbsp;·&nbsp;
                            <a href="<?= htmlspecialchars($appUrl, ENT_QUOTES, 'UTF-8') ?>/abonnement" style="color:#6B6B7D;text-decoration:none;" class="text-muted">Abonnements</a>
                            &nbsp;·&nbsp;
                            <a href="mailto:contact@leseditionsvariable.com" style="color:#6B6B7D;text-decoration:none;" class="text-muted">Contact</a>
                        </p>
                        <p style="margin:0 0 4px 0;color:#6B6B7D;" class="text-muted">
                            Plateforme de littérature africaine francophone
                        </p>
                        <p style="margin:0;color:#A8A29E;font-size:11px;">
                            © <?= $year ?> Les éditions Variable · Tous droits réservés
                        </p>
                    </td>
                </tr>

            </table>
        </td>
    </tr>
</table>

</body>
</html>
