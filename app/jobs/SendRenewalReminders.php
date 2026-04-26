<?php
/**
 * Job : envoyer un rappel email J-3 avant le renouvellement automatique
 *       d'un abonnement (Stripe en auto-renouvellement, statut=actif).
 *
 * À déclencher quotidiennement (cron production).
 *
 * Usage local :
 *   /Applications/MAMP/bin/php/php8.4.17/bin/php app/jobs/SendRenewalReminders.php
 *
 * Usage cron cPanel (1 fois par jour à 09:00 Kinshasa) :
 *   0 9 * * * /usr/bin/php /home/USERNAME/public_html/app/jobs/SendRenewalReminders.php >> /home/USERNAME/logs/cron.log 2>&1
 *
 * Idempotence : on évite les doublons en taggant la notification user
 *   avec type 'renewal_reminder_sent_{sub_id}' — pas renvoyé deux fois pour la même fenêtre.
 */

require_once __DIR__ . '/../../bootstrap.php';

use App\Lib\Database;
use App\Lib\Mailer;
use App\Lib\Notification;

$PLANS = [
    'essentiel_mensuel' => ['label' => 'Essentiel Mensuel'],
    'essentiel_annuel'  => ['label' => 'Essentiel Annuel'],
    'premium_mensuel'   => ['label' => 'Premium Mensuel'],
    'premium_annuel'    => ['label' => 'Premium Annuel'],
];

$db = Database::getInstance();

echo "=== Rappels de renouvellement (J-3) ===" . PHP_EOL;
echo "Date d'exécution : " . date('Y-m-d H:i:s') . PHP_EOL . PHP_EOL;

// On vise les abonnements actifs, en auto-renouvellement, qui se renouvellent dans EXACTEMENT 3 jours.
// Stripe ne fournit pas de "prochaine date de renouvellement" dans notre table → on prend date_fin
// comme proxy (en pratique, Stripe prélève juste avant date_fin pour étendre la période).
$rows = $db->fetchAll(
    "SELECT s.id AS sub_id, s.user_id, s.type, s.prix_paye, s.devise, s.date_fin,
            s.stripe_subscription_id, u.prenom, u.nom, u.email
       FROM subscriptions s
       JOIN users u ON u.id = s.user_id
      WHERE s.statut = 'actif'
        AND s.renouvellement_auto = 1
        AND DATE(s.date_fin) = DATE(DATE_ADD(NOW(), INTERVAL 3 DAY))
        AND (u.statut = 'actif' OR u.statut IS NULL)"
);

echo "Abonnements à notifier : " . count($rows) . PHP_EOL . PHP_EOL;

$sent = 0;
$skipped = 0;
$errors = 0;

foreach ($rows as $row) {
    $tag = 'renewal_reminder_sent_' . (int) $row->sub_id;

    // Idempotence : si on a déjà créé cette notif dans les 23 dernières heures, on skip
    $already = $db->fetch(
        "SELECT 1 FROM notifications
          WHERE user_id = ? AND type = ?
            AND created_at >= DATE_SUB(NOW(), INTERVAL 23 HOUR)",
        [$row->user_id, $tag]
    );
    if ($already) {
        $skipped++;
        continue;
    }

    $planLabel = $PLANS[$row->type]['label'] ?? ucfirst(str_replace('_', ' ', $row->type));

    try {
        Mailer::sendRenewalReminder(
            (object) [
                'prenom' => $row->prenom,
                'nom'    => $row->nom,
                'email'  => $row->email,
            ],
            $planLabel,
            (float) $row->prix_paye,
            (string) $row->devise,
            (string) $row->date_fin
        );
        $sent++;

        // Tag idempotence
        Notification::create(
            (int) $row->user_id,
            $tag,
            'Rappel de renouvellement envoyé',
            'Ton abonnement ' . $planLabel . ' se renouvelle le ' . date('d/m/Y', strtotime((string) $row->date_fin)) . '.',
            '/mon-compte/abonnement',
            'bell'
        );

        echo "  ✓ " . $row->email . " (sub #" . $row->sub_id . ")" . PHP_EOL;
    } catch (\Throwable $e) {
        $errors++;
        echo "  ✗ " . $row->email . " — " . $e->getMessage() . PHP_EOL;
    }
}

echo PHP_EOL . "=== Récapitulatif ===" . PHP_EOL;
echo "Envoyés         : {$sent}" . PHP_EOL;
echo "Doublons évités : {$skipped}" . PHP_EOL;
echo "Erreurs         : {$errors}" . PHP_EOL;
