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
     * Convertit un montant USD en FCFA Ouest (XOF) pour l'envoi à Money Fusion.
     *
     * Le taux est lu depuis settings.taux_conversion_usd_xof (configurable
     * sans redeploy) avec fallback à 750 XOF/USD si le setting est absent.
     *
     * Cache statique en mémoire pour éviter une requête DB par paiement
     * (la valeur ne change qu'à la main par l'admin via /admin/parametres).
     *
     * Retourne un INT car le FCFA n'a pas de centimes et MF arrondit de
     * toute façon. Exemple : 2.00 USD → 1500 XOF, 8.99 USD → 6743 XOF.
     */
    public static function convertUsdToXof(float $usd): int
    {
        static $rate = null;
        if ($rate === null) {
            try {
                $row = Database::getInstance()->fetch(
                    "SELECT `value` FROM settings WHERE `key` = 'taux_conversion_usd_xof'"
                );
                $rate = $row ? (float) $row->value : 750.0;
                if ($rate <= 0) { $rate = 750.0; }
            } catch (\Throwable $e) {
                error_log('PaymentConfig::convertUsdToXof — read setting failed: ' . $e->getMessage());
                $rate = 750.0;
            }
        }
        return (int) round($usd * $rate);
    }
}
