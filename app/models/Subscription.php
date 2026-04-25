<?php
namespace App\Models;

use App\Lib\Database;

/**
 * Modèle Subscription
 */
class Subscription extends BaseModel
{
    protected static string $table = 'subscriptions';

    /**
     * Plans disponibles (source de vérité — à synchroniser avec PaymentController::PLANS)
     */
    public const PLANS = [
        'essentiel_mensuel' => ['prix' => 3,   'label' => 'Essentiel Mensuel', 'duree_jours' => 30,  'tier' => 'essentiel'],
        'essentiel_annuel'  => ['prix' => 30,  'label' => 'Essentiel Annuel',  'duree_jours' => 365, 'tier' => 'essentiel'],
        'premium_mensuel'   => ['prix' => 10,  'label' => 'Premium Mensuel',   'duree_jours' => 30,  'tier' => 'premium'],
        'premium_annuel'    => ['prix' => 100, 'label' => 'Premium Annuel',    'duree_jours' => 365, 'tier' => 'premium'],
    ];

    /**
     * Un user a-t-il l'accès lecture courant via abonnement ?
     * On accepte 'actif' OU 'annule' tant que date_fin >= NOW() : un user qui a annulé
     * mais reste dans sa période payée garde son accès.
     */
    public static function isUserActive(int $userId): bool
    {
        $db = Database::getInstance();
        $sub = $db->fetch(
            "SELECT id FROM subscriptions
             WHERE user_id = ?
               AND statut IN ('actif','annule')
               AND date_fin >= NOW()",
            [$userId]
        );
        return (bool) $sub;
    }

    /**
     * Récupérer l'abonnement courant (encore valide ou en période d'annulation post-paiement)
     */
    public static function getActive(int $userId): ?object
    {
        $db = Database::getInstance();
        $sub = $db->fetch(
            "SELECT * FROM subscriptions
             WHERE user_id = ?
               AND statut IN ('actif','annule')
               AND date_fin >= NOW()
             ORDER BY date_fin DESC LIMIT 1",
            [$userId]
        );
        return $sub ?: null;
    }

    /**
     * Niveau (tier) d'un abonnement : 'essentiel' ou 'premium'
     */
    public static function tierOf(?object $subscription): ?string
    {
        if (!$subscription) return null;
        return self::PLANS[$subscription->type]['tier'] ?? null;
    }

    /**
     * Annuler l'abonnement actif d'un user. La date_fin n'est PAS modifiée :
     * l'utilisateur garde son accès jusqu'à expiration de la période payée.
     */
    public static function cancel(int $userId, string $motif, ?string $raison = null): bool
    {
        $db = Database::getInstance();
        $sub = $db->fetch(
            "SELECT id FROM subscriptions WHERE user_id = ? AND statut = 'actif' AND date_fin >= NOW() ORDER BY date_fin DESC LIMIT 1",
            [$userId]
        );
        if (!$sub) return false;

        $db->update('subscriptions', [
            'statut'             => 'annule',
            'date_annulation'    => date('Y-m-d H:i:s'),
            'motif_annulation'   => $motif,
            'raison_annulation'  => $raison,
            'renouvellement_auto'=> 0,
        ], 'id = ?', [$sub->id]);

        return true;
    }

    /**
     * Réactiver un abonnement annulé tant que la période payée n'est pas expirée.
     */
    public static function reactivate(int $userId): bool
    {
        $db = Database::getInstance();
        $sub = $db->fetch(
            "SELECT id FROM subscriptions WHERE user_id = ? AND statut = 'annule' AND date_fin >= NOW() ORDER BY date_fin DESC LIMIT 1",
            [$userId]
        );
        if (!$sub) return false;

        $db->update('subscriptions', [
            'statut'              => 'actif',
            'date_annulation'     => null,
            'motif_annulation'    => null,
            'raison_annulation'   => null,
            'renouvellement_auto' => 1,
        ], 'id = ?', [$sub->id]);

        return true;
    }
}
