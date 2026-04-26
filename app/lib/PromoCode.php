<?php
namespace App\Lib;

/**
 * Génération de codes promo uniques (drip campaigns + marketing).
 *
 * Format des codes : préfixe lisible + 6 chars hex (ex: REVIENS-A1B2C3).
 * Unicité garantie via la contrainte UNIQUE de la colonne `code`.
 *
 * NOTE : la validation au checkout n'est pas encore implémentée.
 *        Voir migration 008 pour le contexte.
 */
class PromoCode
{
    /**
     * Génère et persiste un code promo unique pour un utilisateur.
     *
     * @param int      $userId        ID de l'utilisateur (peut être 0 pour code générique)
     * @param int      $discountPct   Pourcentage de réduction (1-100)
     * @param int      $validForDays  Durée de validité en jours
     * @param string   $source        Tag d'origine (ex: 'drip_day30', 'newsletter')
     * @param string   $prefix        Préfixe lisible du code (ex: 'REVIENS')
     *
     * @return string Le code généré (ex: 'REVIENS-A1B2C3')
     */
    public static function generateForUser(
        int $userId,
        int $discountPct = 20,
        int $validForDays = 30,
        string $source = 'manual',
        string $prefix = 'PROMO'
    ): string {
        $db = Database::getInstance();
        $prefix = strtoupper(preg_replace('/[^A-Z0-9]/i', '', $prefix)) ?: 'PROMO';

        // Tente jusqu'à 5 fois avant de lever — collision quasi-impossible mais on garde la ceinture
        for ($i = 0; $i < 5; $i++) {
            $code = $prefix . '-' . strtoupper(bin2hex(random_bytes(3))); // 6 chars hex
            $exists = $db->fetch("SELECT 1 FROM promo_codes WHERE code = ?", [$code]);
            if (!$exists) {
                $db->insert('promo_codes', [
                    'code'         => $code,
                    'user_id'      => $userId ?: null,
                    'discount_pct' => $discountPct,
                    'max_uses'     => 1,
                    'valid_from'   => date('Y-m-d H:i:s'),
                    'valid_until'  => date('Y-m-d H:i:s', strtotime("+{$validForDays} days")),
                    'source'       => $source,
                ]);
                return $code;
            }
        }

        throw new \RuntimeException('Impossible de générer un code promo unique après 5 tentatives.');
    }

    /**
     * Récupère un code promo existant non utilisé pour un user et une source donnés
     * (idempotence : si on regénère pour le même user/source, on renvoie le précédent).
     */
    public static function findActiveForUser(int $userId, string $source): ?object
    {
        $row = Database::getInstance()->fetch(
            "SELECT * FROM promo_codes
              WHERE user_id = ? AND source = ?
                AND used_at IS NULL
                AND (valid_until IS NULL OR valid_until > NOW())
              ORDER BY id DESC LIMIT 1",
            [$userId, $source]
        );
        return $row ?: null; // fetch() retourne false si pas de match — on normalise en null
    }
}
