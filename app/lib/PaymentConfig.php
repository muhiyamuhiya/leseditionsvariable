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
}
