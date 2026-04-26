<?php
namespace App\Lib;

/**
 * Calcul du solde versable d'un auteur.
 *
 * Logique :
 *   - Toutes les sales `payee` créditent l'auteur via revenu_auteur (en USD).
 *   - Le cron mensuel RedistributePool crée des rows author_payouts
 *     `statut='available'` avec revenus_pool_abonnement = part de l'auteur.
 *   - Les demandes de versement (requestPayout) créent un row
 *     `statut='requested'` qui agrège tout, et annule les rows 'available'
 *     pré-existants pour éviter de double-compter.
 *
 * Le solde disponible = SUM(sales.revenu_auteur)
 *                     + SUM(author_payouts.revenus_pool_abonnement WHERE statut='available')
 *                     - SUM(author_payouts.total_a_verser WHERE statut IN ('requested','en_cours','verse'))
 */
class PayoutBalance
{
    /**
     * @return array{
     *   total_lifetime: float,        // total brut généré (ventes + pool)
     *   total_paid: float,             // versé
     *   total_pending: float,          // demandé ou en cours (pas encore versé)
     *   available: float,              // versable maintenant (>= 0)
     *   sales_total: float,            // part ventes (lifetime)
     *   pool_available: float,         // pool en attente de demande
     *   refused_total: float           // demandes refusées (info pour l'auteur)
     * }
     */
    public static function compute(int $authorId): array
    {
        $db = Database::getInstance();

        $row = $db->fetch(
            "SELECT
                COALESCE((SELECT SUM(s.revenu_auteur) FROM sales s
                          WHERE s.author_id = ? AND s.statut = 'payee'), 0) AS sales_total,
                COALESCE((SELECT SUM(ap.revenus_pool_abonnement) FROM author_payouts ap
                          WHERE ap.author_id = ? AND ap.statut = 'available'), 0) AS pool_available,
                COALESCE((SELECT SUM(ap.total_a_verser) FROM author_payouts ap
                          WHERE ap.author_id = ? AND ap.statut IN ('requested','en_cours')), 0) AS pending,
                COALESCE((SELECT SUM(ap.total_a_verser) FROM author_payouts ap
                          WHERE ap.author_id = ? AND ap.statut = 'verse'), 0) AS paid,
                COALESCE((SELECT SUM(ap.total_a_verser) FROM author_payouts ap
                          WHERE ap.author_id = ? AND ap.statut = 'refuse'), 0) AS refused",
            [$authorId, $authorId, $authorId, $authorId, $authorId]
        );

        $salesTotal    = (float) ($row->sales_total ?? 0);
        $poolAvailable = (float) ($row->pool_available ?? 0);
        $pending       = (float) ($row->pending ?? 0);
        $paid          = (float) ($row->paid ?? 0);
        $refused       = (float) ($row->refused ?? 0);

        // Lifetime brut généré
        $lifetime = $salesTotal + $poolAvailable + $pending + $paid;

        // Disponible = ce qui n'est ni payé ni en cours
        $available = $salesTotal + $poolAvailable - $pending - $paid;
        if ($available < 0) { $available = 0; } // garde-fou contre arrondis

        return [
            'total_lifetime' => round($lifetime, 2),
            'total_paid'     => round($paid, 2),
            'total_pending'  => round($pending, 2),
            'available'      => round($available, 2),
            'sales_total'    => round($salesTotal, 2),
            'pool_available' => round($poolAvailable, 2),
            'refused_total'  => round($refused, 2),
        ];
    }

    /**
     * Snapshot des coordonnées bancaires au moment de la demande, selon
     * la méthode_versement choisie par l'auteur. Stocké dans
     * author_payouts.requested_account_snapshot pour qu'un changement
     * ultérieur des coords n'impacte pas un versement déjà demandé.
     */
    public static function snapshotAccount(object $author): string
    {
        switch ($author->methode_versement ?? 'mobile_money') {
            case 'mobile_money':
                $op = $author->operateur_mobile_money ?? '';
                $no = $author->numero_mobile_money ?? '';
                return trim($op . ' ' . $no);
            case 'banque':
                return trim('IBAN ' . ($author->iban ?? '') . ' / ' . ($author->nom_banque ?? ''));
            case 'paypal':
                return (string) ($author->email_paypal ?? '');
            case 'stripe':
                return (string) ($author->email_paypal ?? ''); // Stripe Connect = email
            default:
                return '';
        }
    }
}
