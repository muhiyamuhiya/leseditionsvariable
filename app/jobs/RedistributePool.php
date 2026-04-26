<?php
/**
 * Job mensuel : redistribue le pool d'abonnements aux auteurs au prorata
 * des pages lues sur leurs livres pendant le mois précédent.
 *
 * Logique :
 *   1. Cible le mois N-1 (clos)
 *   2. Calcule le revenu total abonnements de ce mois (subscriptions.prix_paye
 *      where MONTH/YEAR(date_debut)). Convertit tout en USD si nécessaire.
 *   3. Applique pourcentage_pool_redistribution (50% par défaut) → pool_auteurs
 *   4. Agrège les pages_lues_session par auteur depuis reading_sessions
 *      (en excluant les livres classiques is_classic=1 — domaine public)
 *   5. taux_par_page = pool_auteurs / total_pages_lues_eligibles
 *   6. Pour chaque auteur éligible : crée/upserte un row author_payouts
 *      statut='available' avec revenus_pool_abonnement = pages × taux
 *   7. Inscrit le snapshot dans subscription_pool (UNIQUE annee/mois)
 *
 * Idempotent : si subscription_pool contient déjà ce (annee, mois), le job
 * sort sans rien faire. Pour relancer, supprimer manuellement le row.
 *
 * Cron cPanel (1er du mois à 04h Kinshasa) :
 *   0 4 1 * * /usr/bin/php /home/USERNAME/public_html/app/jobs/RedistributePool.php >> /home/USERNAME/logs/cron-pool.log 2>&1
 *
 * Usage local pour test (force mois courant ou un mois spécifique) :
 *   php app/jobs/RedistributePool.php          # mois N-1
 *   php app/jobs/RedistributePool.php 2026 4   # année + mois explicites
 */

require_once __DIR__ . '/../../bootstrap.php';

use App\Lib\Database;

$db = Database::getInstance();

// Args : argv[1]=année, argv[2]=mois (sinon mois précédent)
$argYear  = isset($argv[1]) ? (int) $argv[1] : null;
$argMonth = isset($argv[2]) ? (int) $argv[2] : null;
if ($argYear && $argMonth) {
    $year  = $argYear;
    $month = $argMonth;
} else {
    $year  = (int) date('Y', strtotime('first day of last month'));
    $month = (int) date('n', strtotime('first day of last month'));
}

echo "=== RedistributePool ===" . PHP_EOL;
echo "Période ciblée : {$year}-" . str_pad((string) $month, 2, '0', STR_PAD_LEFT) . PHP_EOL;
echo "Date d'exécution : " . date('Y-m-d H:i:s') . PHP_EOL . PHP_EOL;

// Idempotence : si déjà calculé, on sort
$already = $db->fetch(
    "SELECT id, statut FROM subscription_pool WHERE annee = ? AND mois = ?",
    [$year, $month]
);
if ($already) {
    echo "✗ Pool déjà calculé pour cette période (id={$already->id}, statut={$already->statut}). Skip." . PHP_EOL;
    echo "  Pour relancer : DELETE FROM subscription_pool WHERE id={$already->id};" . PHP_EOL;
    exit(0);
}

// 1. Total revenus abonnements du mois (USD)
// Ici on prend prix_paye en USD comme proxy. Pour le multi-devises il faudra
// convertir via subscription_pool.frais_*. À ajouter quand multi-devise activé.
$totalAbo = (float) ($db->fetch(
    "SELECT COALESCE(SUM(prix_paye), 0) AS v
       FROM subscriptions
      WHERE YEAR(date_debut) = ? AND MONTH(date_debut) = ?
        AND devise = 'USD'",
    [$year, $month]
)->v ?? 0);

// Frais paiement : on estime 3% Stripe + 0% MF (MF facture autrement). À
// remplacer par sum(transactions_log) si besoin de précision comptable.
$fraisStripe = round($totalAbo * 0.03, 2);
$fraisMf     = 0.00;
$montantNet  = max(0.0, $totalAbo - $fraisStripe - $fraisMf);

// Pourcentage du pool (settings)
$poolPct = (float) ($db->fetch("SELECT `value` FROM settings WHERE `key` = 'pourcentage_pool_redistribution'")->value ?? 50);
$poolAuteurs = round($montantNet * ($poolPct / 100), 2);

echo "Revenus abonnements    : " . number_format($totalAbo, 2) . " USD" . PHP_EOL;
echo "Frais Stripe (3% est.) : " . number_format($fraisStripe, 2) . " USD" . PHP_EOL;
echo "Montant net            : " . number_format($montantNet, 2) . " USD" . PHP_EOL;
echo "Pool auteurs ({$poolPct}%)    : " . number_format($poolAuteurs, 2) . " USD" . PHP_EOL . PHP_EOL;

// 2. Agrégation pages lues par auteur (livres NON-classiques)
$startOfMonth = sprintf('%04d-%02d-01 00:00:00', $year, $month);
$endOfMonth   = date('Y-m-d 23:59:59', strtotime($startOfMonth . ' +1 month -1 day'));

$pagesByAuthor = $db->fetchAll(
    "SELECT b.author_id,
            COALESCE(a.nom_plume, 'Auteur') AS author_name,
            SUM(rs.pages_lues_session) AS pages
       FROM reading_sessions rs
       JOIN books b   ON b.id = rs.book_id
       JOIN authors a ON a.id = b.author_id
      WHERE rs.debut_at >= ? AND rs.debut_at <= ?
        AND a.is_classic = 0
        AND rs.pages_lues_session > 0
      GROUP BY b.author_id, a.nom_plume
      ORDER BY pages DESC",
    [$startOfMonth, $endOfMonth]
);

$totalPagesEligibles = (int) array_sum(array_map(static fn ($r) => (int) $r->pages, $pagesByAuthor));

echo "Pages lues éligibles    : " . number_format($totalPagesEligibles) . PHP_EOL;
echo "Auteurs concernés       : " . count($pagesByAuthor) . PHP_EOL . PHP_EOL;

// 3. Insert subscription_pool (snapshot)
$tauxParPage = ($totalPagesEligibles > 0 && $poolAuteurs > 0)
    ? round($poolAuteurs / $totalPagesEligibles, 6)
    : 0.0;

$db->insert('subscription_pool', [
    'annee'                  => $year,
    'mois'                   => $month,
    'total_abonnements'      => $totalAbo,
    'frais_moneyfusion'      => $fraisMf,
    'frais_stripe'           => $fraisStripe,
    'montant_net'            => $montantNet,
    'pourcentage_pool'       => $poolPct,
    'pool_auteurs'           => $poolAuteurs,
    'total_pages_lues_mois'  => $totalPagesEligibles,
    'taux_par_page'          => $tauxParPage,
    'statut'                 => 'distribue',
    'date_calcul'            => date('Y-m-d H:i:s'),
    'date_distribution'      => date('Y-m-d H:i:s'),
]);

echo "Taux par page          : " . number_format($tauxParPage, 6) . " USD" . PHP_EOL . PHP_EOL;

// 4. Crédite chaque auteur éligible (statut='available')
$creditedCount = 0;
$creditedTotal = 0.0;
$periodeDebut = sprintf('%04d-%02d-01', $year, $month);
$periodeFin   = date('Y-m-t', strtotime($startOfMonth));

foreach ($pagesByAuthor as $row) {
    $part = round((int) $row->pages * $tauxParPage, 2);
    if ($part <= 0) continue;

    $db->insert('author_payouts', [
        'author_id'                => (int) $row->author_id,
        'periode_debut'            => $periodeDebut,
        'periode_fin'              => $periodeFin,
        'revenus_ventes_unitaires' => 0.00,
        'revenus_pool_abonnement'  => $part,
        'total_a_verser'           => $part,
        'devise'                   => 'USD',
        'statut'                   => 'available',
        'notes'                    => sprintf('Pool %04d-%02d : %s pages × %.6f USD', $year, $month, number_format((int) $row->pages), $tauxParPage),
    ]);

    $creditedCount++;
    $creditedTotal += $part;
    printf("  ✓ %-30s %s pages → %s USD\n",
        mb_substr((string) $row->author_name, 0, 30),
        number_format((int) $row->pages),
        number_format($part, 2)
    );
}

echo PHP_EOL . "=== Récapitulatif ===" . PHP_EOL;
echo "Auteurs crédités     : {$creditedCount}" . PHP_EOL;
echo "Total redistribué    : " . number_format($creditedTotal, 2) . " USD" . PHP_EOL;
echo "Reliquat (arrondis)  : " . number_format($poolAuteurs - $creditedTotal, 2) . " USD" . PHP_EOL;
