<?php
namespace App\Lib;

/**
 * Configuration centralisée des paiements (Stripe + Money Fusion)
 */
class PaymentConfig
{
    public static function stripePublicKey(): ?string
    {
        return Env::get('STRIPE_PUBLIC_KEY') ?: null;
    }

    public static function stripeSecretKey(): ?string
    {
        return Env::get('STRIPE_SECRET_KEY') ?: null;
    }

    public static function stripeWebhookSecret(): ?string
    {
        return Env::get('STRIPE_WEBHOOK_SECRET') ?: null;
    }

    public static function moneyFusionApiUrl(): ?string
    {
        return Env::get('MONEYFUSION_API_URL') ?: null;
    }

    public static function moneyFusionWebhookToken(): ?string
    {
        return Env::get('MONEYFUSION_WEBHOOK_TOKEN') ?: null;
    }

    public static function publicAppUrl(): string
    {
        return Env::get('APP_URL_PUBLIC') ?: Env::get('APP_URL', 'http://localhost:8888');
    }

    public static function webhookUrl(string $provider): string
    {
        return self::publicAppUrl() . '/webhook/' . $provider;
    }

    public static function returnUrl(string $context = 'success'): string
    {
        return self::publicAppUrl() . '/paiement/' . $context;
    }

    public static function isStripeConfigured(): bool
    {
        return !empty(self::stripeSecretKey()) && !empty(self::stripePublicKey());
    }

    public static function isMoneyFusionConfigured(): bool
    {
        return !empty(self::moneyFusionApiUrl());
    }

    public static function initStripe(): bool
    {
        if (!self::isStripeConfigured()) {
            return false;
        }
        \Stripe\Stripe::setApiKey(self::stripeSecretKey());
        return true;
    }

    /**
     * Calcule le montant + la devise à envoyer à Money Fusion selon le
     * pays de l'utilisateur (users.pays au format ISO 3166 alpha-2).
     *
     * Mapping :
     *   CD                              → USD       (×1)        — RDC supporte USD natif chez MF, tranche 1-3000 USD
     *   CI / SN / ML / BF / BJ / TG     → XOF       (×750)      — FCFA Ouest, tranche 100-1.5M
     *   CM                              → XAF       (×750)      — FCFA Centrale, tranche 100-1.5M
     *   GN                              → GNF       (×8500)     — Franc guinéen, tranche 1000-15M
     *   Diaspora ou pays inconnu        → USD       (×1)        — fallback ; le widget MF affiche les opérateurs USD disponibles, à défaut l'utilisateur peut choisir Stripe
     *
     * Les taux sont configurables via settings (taux_conversion_usd_xof,
     * _xaf, _gnf), ajustables sans redeploy depuis /admin/parametres.
     *
     * Retourne ['amount' => int, 'currency' => string]. Le montant est un
     * INT car les devises africaines n'ont pas de centimes côté MF.
     */
    public static function moneyFusionAmountForUser(float $usd, ?object $user = null): array
    {
        $pays = strtoupper((string) ($user->pays ?? '')) ?: 'CD';

        // Devises XOF (Afrique de l'Ouest)
        $xofCountries = ['CI', 'SN', 'ML', 'BF', 'BJ', 'TG'];

        if ($pays === 'CD') {
            // RDC : USD direct, pas de conversion
            return ['amount' => (int) round($usd), 'currency' => 'USD'];
        }
        if (in_array($pays, $xofCountries, true)) {
            return ['amount' => (int) round($usd * self::moneyFusionRate('xof', 750.0)), 'currency' => 'XOF'];
        }
        if ($pays === 'CM') {
            return ['amount' => (int) round($usd * self::moneyFusionRate('xaf', 750.0)), 'currency' => 'XAF'];
        }
        if ($pays === 'GN') {
            return ['amount' => (int) round($usd * self::moneyFusionRate('gnf', 8500.0)), 'currency' => 'GNF'];
        }

        // Diaspora ou pays non couvert : on tente USD (le widget MF gère
        // les opérateurs USD disponibles, sinon l'utilisateur a Stripe en
        // alternative dans le sélecteur de moyen de paiement).
        return ['amount' => (int) round($usd), 'currency' => 'USD'];
    }

    /**
     * Lit un taux de conversion USD→devise dans settings (cache statique).
     * Fallback sur la valeur par défaut si setting absent ou DB inaccessible.
     */
    private static function moneyFusionRate(string $currencyKey, float $default): float
    {
        static $cache = [];
        if (!isset($cache[$currencyKey])) {
            try {
                $row = Database::getInstance()->fetch(
                    "SELECT `value` FROM settings WHERE `key` = ?",
                    ['taux_conversion_usd_' . $currencyKey]
                );
                $rate = $row ? (float) $row->value : $default;
                if ($rate <= 0) { $rate = $default; }
                $cache[$currencyKey] = $rate;
            } catch (\Throwable $e) {
                error_log("PaymentConfig::moneyFusionRate({$currencyKey}) — DB read failed: " . $e->getMessage());
                $cache[$currencyKey] = $default;
            }
        }
        return $cache[$currencyKey];
    }

    /**
     * Convertit USD → XOF (FCFA Ouest) pour Money Fusion.
     * Conservé pour rétro-compat ; nouveau code utilise moneyFusionAmountForUser().
     *
     * @deprecated Utiliser moneyFusionAmountForUser($usd, $user) à la place.
     */
    public static function convertUsdToXof(float $usd): int
    {
        return (int) round($usd * self::moneyFusionRate('xof', 750.0));
    }
}
