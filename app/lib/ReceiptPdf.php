<?php
namespace App\Lib;

use Dompdf\Dompdf;
use Dompdf\Options;

/**
 * Génération de reçus PDF (achat livre, abonnement) via dompdf.
 *
 * Le rendu utilise app/views/emails/pdf/receipt.php — un template HTML
 * volontairement simple (dompdf supporte un sous-ensemble de CSS).
 */
class ReceiptPdf
{
    /**
     * Génère le binaire PDF d'un reçu et le retourne (string).
     *
     * @param array $data Doit contenir : kind, itemLabel, amount, currency,
     *                    paymentMethod, transactionId, dateIso, user (object).
     */
    public static function render(array $data): string
    {
        // Variables globales utiles au template
        $data['appName'] = $data['appName'] ?? (function_exists('env') ? env('APP_NAME', 'Les éditions Variable') : 'Les éditions Variable');
        $data['appUrl']  = $data['appUrl']  ?? rtrim((string) (function_exists('env') ? env('APP_URL', 'https://leseditionsvariable.com') : 'https://leseditionsvariable.com'), '/');

        $template = BASE_PATH . '/app/views/emails/pdf/receipt.php';
        if (!file_exists($template)) {
            throw new \RuntimeException("Template PDF introuvable : {$template}");
        }

        extract($data, EXTR_SKIP);
        ob_start();
        require $template;
        $html = (string) ob_get_clean();

        $options = new Options();
        $options->set('isRemoteEnabled', false);     // sécurité : pas de remote URL
        $options->set('defaultFont', 'DejaVu Sans'); // gère bien l'UTF-8 / accents
        $options->set('chroot', BASE_PATH);

        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html, 'UTF-8');
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        return (string) $dompdf->output();
    }

    /**
     * Nom de fichier suggéré pour la pièce jointe.
     */
    public static function suggestedFilename(array $data): string
    {
        $kind = ($data['kind'] ?? '') === 'subscription' ? 'abonnement' : 'achat';
        $date = date('Ymd', strtotime($data['dateIso'] ?? 'now'));
        return "recu-variable-{$kind}-{$date}.pdf";
    }
}
