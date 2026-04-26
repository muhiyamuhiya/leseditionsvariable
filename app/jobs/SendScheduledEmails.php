<?php
/**
 * Job : avancer toutes les progressions d'utilisateurs dans leurs séquences
 *       d'email automatisées (table email_user_progress).
 *
 * Pipeline pour chaque progression `running` avec next_send_at <= NOW() :
 *   1. Charge le step courant (sort_order = current_step)
 *   2. Dispatch vers le helper Mailer correspondant au template
 *   3. Update progress :
 *        - last_sent_at, last_send_result, last_send_error
 *        - Si dernier step  → status='completed', completed_at=NOW()
 *        - Sinon            → current_step++, next_send_at = NOW()
 *                              + (next.day_offset - current.day_offset) jours
 *
 * Idempotence : si un step a été envoyé il y a moins d'1h, on ne le rejoue pas.
 *
 * Usage local :
 *   /Applications/MAMP/bin/php/php8.4.17/bin/php app/jobs/SendScheduledEmails.php
 *
 * Usage cron cPanel (toutes les heures, par exemple) :
 *   0 * * * * /usr/bin/php /home/USERNAME/public_html/app/jobs/SendScheduledEmails.php >> /home/USERNAME/logs/cron-emails.log 2>&1
 *
 * Note de design :
 *   - Les helpers Mailer (sendDripDay2/7/14/30) appliquent leur propre skip
 *     logic. Quand un helper retourne false, on note 'skipped' mais on
 *     avance quand même au step suivant (le skip est définitif pour ce step,
 *     pas un report).
 *   - Welcome (step 1) n'est PAS dispatché par ce cron : il est envoyé
 *     instantanément par AuthController::verifyEmail. Les progress rows
 *     sont créées avec current_step=2 directement.
 */

require_once __DIR__ . '/../../bootstrap.php';

use App\Lib\Database;
use App\Lib\Mailer;

$db = Database::getInstance();

echo "=== SendScheduledEmails ===" . PHP_EOL;
echo "Date d'exécution : " . date('Y-m-d H:i:s') . PHP_EOL;

// Mapping template → helper Mailer (les fonctions retournent bool : true=envoyé, false=skip)
$helpers = [
    'drip_day2'  => 'sendDripDay2',
    'drip_day7'  => 'sendDripDay7',
    'drip_day14' => 'sendDripDay14',
    'drip_day30' => 'sendDripDay30',
    // welcome n'est pas dispatché ici : géré par AuthController::verifyEmail
];

$rows = $db->fetchAll(
    "SELECT p.id          AS prog_id,
            p.user_id, p.sequence_id, p.current_step, p.last_sent_at,
            u.prenom, u.nom, u.email, u.actif AS user_actif, u.statut AS user_statut,
            s.slug         AS seq_slug
       FROM email_user_progress p
       JOIN users u            ON u.id = p.user_id
       JOIN email_sequences s  ON s.id = p.sequence_id
      WHERE p.status = 'running'
        AND p.next_send_at IS NOT NULL
        AND p.next_send_at <= NOW()
        AND s.active = 1
        AND (u.statut = 'actif' OR u.statut IS NULL)
        AND (u.actif = 1 OR u.actif IS NULL)
      ORDER BY p.next_send_at ASC"
);

echo "Progressions à traiter : " . count($rows) . PHP_EOL . PHP_EOL;

$sent = $skipped = $errors = $completed = 0;

foreach ($rows as $row) {
    // Idempotence : pas plus d'1 envoi par heure pour un même progress
    if ($row->last_sent_at && strtotime((string) $row->last_sent_at) > strtotime('-1 hour')) {
        echo "  · prog #{$row->prog_id} ({$row->email}) — last_sent_at < 1h, skip protection" . PHP_EOL;
        continue;
    }

    // Step courant + suivant
    $currentStep = $db->fetch(
        "SELECT * FROM email_sequence_steps WHERE sequence_id = ? AND sort_order = ?",
        [$row->sequence_id, $row->current_step]
    );

    if (!$currentStep) {
        // Cas pathologique : current_step n'existe pas, on marque la progression complétée
        $db->update('email_user_progress', [
            'status'         => 'completed',
            'completed_at'   => date('Y-m-d H:i:s'),
            'last_send_error' => 'Step ' . $row->current_step . ' introuvable pour sequence ' . $row->seq_slug,
        ], 'id = ?', [$row->prog_id]);
        $errors++;
        echo "  ✗ prog #{$row->prog_id} — step introuvable, marqué completed" . PHP_EOL;
        continue;
    }

    $template = (string) $currentStep->template;
    $helper   = $helpers[$template] ?? null;

    // Dispatch helper
    $userObj = (object) [
        'id'     => (int) $row->user_id,
        'prenom' => $row->prenom,
        'nom'    => $row->nom,
        'email'  => $row->email,
    ];

    $result = 'skipped';
    $errMsg = null;

    if ($helper && method_exists(Mailer::class, $helper)) {
        try {
            $ok = Mailer::$helper($userObj);
            $result = $ok ? 'sent' : 'skipped';
            if ($ok) { $sent++; } else { $skipped++; }
        } catch (\Throwable $e) {
            $result = 'error';
            $errMsg = $e->getMessage();
            $errors++;
            error_log("SendScheduledEmails — prog #{$row->prog_id} ({$template}) : {$errMsg}");
        }
    } else {
        // Pas de helper dédié pour ce template (ex: welcome) → skip silencieux
        $result = 'skipped';
        $errMsg = "Pas de helper pour template '{$template}' — step ignoré";
        $skipped++;
    }

    echo "  " . match($result) { 'sent' => '✓', 'skipped' => '·', 'error' => '✗', default => '?' }
        . " prog #{$row->prog_id} step {$row->current_step} ({$template}) → {$result}"
        . ($errMsg ? " [{$errMsg}]" : '') . PHP_EOL;

    // Charger le step suivant (s'il existe) pour calculer next_send_at
    $nextStep = $db->fetch(
        "SELECT * FROM email_sequence_steps WHERE sequence_id = ? AND sort_order = ?",
        [$row->sequence_id, $row->current_step + 1]
    );

    if ($nextStep) {
        $deltaDays = max(1, (int) $nextStep->day_offset - (int) $currentStep->day_offset);
        $db->update('email_user_progress', [
            'current_step'    => (int) $row->current_step + 1,
            'next_send_at'    => date('Y-m-d H:i:s', strtotime("+{$deltaDays} days")),
            'last_sent_at'    => date('Y-m-d H:i:s'),
            'last_send_result'=> $result,
            'last_send_error' => $errMsg,
        ], 'id = ?', [$row->prog_id]);
    } else {
        // Dernier step → on complète la progression
        $db->update('email_user_progress', [
            'status'          => 'completed',
            'completed_at'    => date('Y-m-d H:i:s'),
            'last_sent_at'    => date('Y-m-d H:i:s'),
            'last_send_result'=> $result,
            'last_send_error' => $errMsg,
        ], 'id = ?', [$row->prog_id]);
        $completed++;
    }
}

echo PHP_EOL . "=== Récapitulatif ===" . PHP_EOL;
echo "Envoyés        : {$sent}" . PHP_EOL;
echo "Skip (logique) : {$skipped}" . PHP_EOL;
echo "Erreurs        : {$errors}" . PHP_EOL;
echo "Séquences finies : {$completed}" . PHP_EOL;
