<?php
/**
 * Cron-like : notifier les utilisateurs dont l'abonnement expire dans 7, 3 et 1 jour.
 * À déclencher quotidiennement (cron production ou exécution manuelle pendant le MVP).
 *
 * Usage :
 *   /Applications/MAMP/bin/php/php8.3.30/bin/php database/cron/notify_expiring_subscriptions.php
 *
 * Idempotence : on évite les doublons en vérifiant qu'aucune notification du même type
 * n'a été créée pour ce user dans les dernières 23h.
 */

require_once __DIR__ . '/../../bootstrap.php';

use App\Lib\Database;
use App\Lib\Notification;

$db = Database::getInstance();

echo "=== Notifications d'expiration d'abonnement ===" . PHP_EOL;

// Fenêtres : 7, 3, 1 jours avant date_fin
$windows = [7, 3, 1];

$totalNotifs = 0;
$totalSkipped = 0;

foreach ($windows as $days) {
    $type = 'sub_expiring_' . $days . '_days';

    // Cibler les abos actifs (pas annulés) qui expirent dans EXACTEMENT $days jours (à la journée près)
    $rows = $db->fetchAll(
        "SELECT s.id AS sub_id, s.user_id, s.type, s.date_fin
         FROM subscriptions s
         WHERE s.statut = 'actif'
           AND DATE(s.date_fin) = DATE(DATE_ADD(NOW(), INTERVAL ? DAY))",
        [$days]
    );

    echo PHP_EOL . "Fenêtre {$days} jour(s) : " . count($rows) . " abonnement(s) ciblé(s)." . PHP_EOL;

    foreach ($rows as $row) {
        // Idempotence : a-t-on déjà notifié ce type dans les dernières 23h ?
        $exists = $db->fetch(
            "SELECT 1 FROM notifications
             WHERE user_id = ? AND type = ? AND created_at >= DATE_SUB(NOW(), INTERVAL 23 HOUR)",
            [$row->user_id, $type]
        );
        if ($exists) {
            $totalSkipped++;
            continue;
        }

        $msg = $days === 1
            ? 'Ton abonnement expire demain. Renouvelle pour continuer à lire sans interruption.'
            : "Ton abonnement expire dans {$days} jours. Renouvelle pour continuer à lire sans interruption.";

        Notification::create(
            (int) $row->user_id,
            $type,
            'Abonnement bientôt expiré',
            $msg,
            '/abonnement',
            'alert'
        );
        $totalNotifs++;
    }
}

echo PHP_EOL . "=== Récapitulatif ===" . PHP_EOL;
echo "Notifications créées : {$totalNotifs}" . PHP_EOL;
echo "Doublons évités      : {$totalSkipped}" . PHP_EOL;
