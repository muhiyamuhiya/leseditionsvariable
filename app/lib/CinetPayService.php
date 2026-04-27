<?php
namespace App\Lib;

/**
 * CinetPay — initiation des paiements (achat livre, abonnement, services
 * éditoriaux). Couvre la RDC en USD natif (pas de conversion : CinetPay
 * accepte directement OMCDUSD / AIRTELCDUSD / MPESACDUSD / VISAMCDUSD entre
 * 1 et 3000 USD).
 *
 * Le webhook (HMAC + re-query API + fulfill idempotent) est géré dans
 * PaymentController::cinetPayWebhook (cf. commit dédié).
 *
 * Doc CinetPay : https://docs.cinetpay.com/api/1.0-fr/checkout/initialisation
 */
class CinetPayService
{
    /** Tranche USD acceptée par CinetPay pour les méthodes RDC. */
    public const MIN_AMOUNT_USD = 1;
    public const MAX_AMOUNT_USD = 3000;

    /**
     * CinetPay configuré côté .env (clés + URL API renseignées) ?
     * NB : le toggle d'activation côté admin est séparé (cf. isActive).
     */
    public static function isConfigured(): bool
    {
        return env('CINETPAY_API_KEY') !== '' && env('CINETPAY_API_KEY') !== null
            && env('CINETPAY_SITE_ID') !== '' && env('CINETPAY_SITE_ID') !== null
            && env('CINETPAY_SECRET_KEY') !== '' && env('CINETPAY_SECRET_KEY') !== null
            && env('CINETPAY_API_URL') !== '' && env('CINETPAY_API_URL') !== null;
    }

    /**
     * CinetPay activé côté admin (settings.cinetpay_active = '1') ET configuré ?
     * Permet de basculer le service ON/OFF sans toucher au code (.env).
     */
    public static function isActive(): bool
    {
        if (!self::isConfigured()) return false;
        $db = Database::getInstance();
        $row = $db->fetch("SELECT `value` FROM settings WHERE `key` = 'cinetpay_active' LIMIT 1");
        return $row && (string) $row->value === '1';
    }

    /**
     * Initie un paiement pour un achat de livre. Retourne l'URL de paiement
     * (à rediriger côté navigateur) ou null + message d'erreur.
     *
     * @param object $user  L'utilisateur acheteur (doit contenir id, email, prenom, nom, telephone, pays...)
     * @param object $book  Le livre (id, titre, prix_unitaire_usd, slug)
     * @return array{url:?string,transactionId:?string,error:?string}
     */
    public static function initBookPurchase(object $user, object $book): array
    {
        $amount = (float) ($book->prix_unitaire_usd ?? 0);
        if ($amount < self::MIN_AMOUNT_USD || $amount > self::MAX_AMOUNT_USD) {
            return self::failure(sprintf(
                'Montant %s USD hors tranche CinetPay (%d-%d USD).',
                $amount, self::MIN_AMOUNT_USD, self::MAX_AMOUNT_USD
            ));
        }

        $transactionId = self::generateTransactionId('LV');
        $description   = 'Achat livre : ' . mb_substr((string) $book->titre, 0, 100);

        $payload = self::baseCustomerPayload($user, $amount, $transactionId, $description);
        $payload['metadata'] = json_encode([
            'type'    => 'book_purchase',
            'user_id' => (int) $user->id,
            'book_id' => (int) $book->id,
        ], JSON_UNESCAPED_UNICODE);

        return self::callInit($payload, $transactionId);
    }

    /**
     * Initie un paiement pour un abonnement (Essentiel ou Premium, mensuel
     * ou annuel selon le plan).
     *
     * @param object $user  L'utilisateur
     * @param string $plan  Slug du plan (ex: 'essentiel-mensuel', 'premium-annuel')
     * @param float  $amountUsd Le prix du plan en USD
     * @return array{url:?string,transactionId:?string,error:?string}
     */
    public static function initSubscription(object $user, string $plan, float $amountUsd): array
    {
        if ($amountUsd < self::MIN_AMOUNT_USD || $amountUsd > self::MAX_AMOUNT_USD) {
            return self::failure(sprintf(
                'Montant %s USD hors tranche CinetPay (%d-%d USD).',
                $amountUsd, self::MIN_AMOUNT_USD, self::MAX_AMOUNT_USD
            ));
        }

        $transactionId = self::generateTransactionId('SUB');
        $description   = 'Abonnement Variable : ' . $plan;

        $payload = self::baseCustomerPayload($user, $amountUsd, $transactionId, $description);
        $payload['metadata'] = json_encode([
            'type'    => 'subscription',
            'user_id' => (int) $user->id,
            'plan'    => $plan,
        ], JSON_UNESCAPED_UNICODE);

        return self::callInit($payload, $transactionId);
    }

    /**
     * Initie un paiement pour une commande de service éditorial (relecture,
     * mise en page, couverture, coaching, pack).
     *
     * @param object $user     L'utilisateur (auteur typiquement)
     * @param object $service  La ligne editorial_services (id, nom, prix_usd)
     * @param int    $orderId  ID de la commande editorial_orders pré-créée
     * @return array{url:?string,transactionId:?string,error:?string}
     */
    public static function initEditorialOrder(object $user, object $service, int $orderId): array
    {
        $amount = (float) ($service->prix_usd ?? 0);
        if ($amount < self::MIN_AMOUNT_USD || $amount > self::MAX_AMOUNT_USD) {
            return self::failure(sprintf(
                'Montant %s USD hors tranche CinetPay (%d-%d USD).',
                $amount, self::MIN_AMOUNT_USD, self::MAX_AMOUNT_USD
            ));
        }

        $transactionId = self::generateTransactionId('ED');
        $description   = 'Service éditorial : ' . mb_substr((string) $service->nom, 0, 100);

        $payload = self::baseCustomerPayload($user, $amount, $transactionId, $description);
        $payload['metadata'] = json_encode([
            'type'       => 'editorial',
            'user_id'    => (int) $user->id,
            'service_id' => (int) $service->id,
            'order_id'   => $orderId,
        ], JSON_UNESCAPED_UNICODE);

        return self::callInit($payload, $transactionId);
    }

    // ---- helpers internes ------------------------------------------------

    /**
     * Construit le payload commun à tous les init de paiement, avec :
     *  - apikey, site_id, transaction_id, amount, currency, description
     *  - notify_url (webhook serveur) et return_url (retour navigateur)
     *  - channels=ALL (mobile money + carte + wallet)
     *  - customer_* obligatoires pour autoriser les paiements par carte
     *
     * Les customer_* sont obligatoires pour les paiements carte (Visa/MC).
     * Pour la RDC, country='CD', state='CD', zip='00000' selon doc CinetPay.
     */
    private static function baseCustomerPayload(object $user, float $amountUsd, string $txId, string $description): array
    {
        $appUrl = rtrim((string) env('APP_URL', 'https://leseditionsvariable.com'), '/');

        // Téléphone : on essaie plusieurs champs possibles selon comment users
        // a été rempli (telephone, phone, phone_number...). Fallback sur '000'
        // côté préfixe pour ne pas planter — CinetPay accepte mais préviendra
        // le user dans le widget.
        $phone     = (string) ($user->telephone ?? $user->phone ?? '');
        $phonePref = '243'; // RDC par défaut

        return [
            'apikey'                  => (string) env('CINETPAY_API_KEY', ''),
            'site_id'                 => (string) env('CINETPAY_SITE_ID', ''),
            'transaction_id'          => $txId,
            'amount'                  => (int) round($amountUsd), // CinetPay exige un entier en USD
            'currency'                => 'USD',
            'description'             => $description,
            'notify_url'              => $appUrl . '/webhook/cinetpay',
            'return_url'              => $appUrl . '/paiement/cinetpay/retour',
            'channels'                => 'ALL',
            'lang'                    => 'FR',
            'invoice_data'            => [],
            // Customer obligatoires pour le canal carte (Visa/MC) :
            'customer_id'             => (string) $user->id,
            'customer_name'           => mb_substr((string) ($user->prenom ?? 'Client'), 0, 100),
            'customer_surname'        => mb_substr((string) ($user->nom ?? 'Variable'), 0, 100),
            'customer_email'          => (string) ($user->email ?? ''),
            'customer_phone_number'   => $phone !== '' ? $phone : '000000000',
            'customer_address'        => mb_substr((string) ($user->adresse ?? 'N/A'), 0, 100),
            'customer_city'           => mb_substr((string) ($user->ville ?? 'Kinshasa'), 0, 100),
            'customer_country'        => 'CD',
            'customer_state'          => 'CD',
            'customer_zip_code'       => '00000',
        ];
    }

    /**
     * POST vers l'API CinetPay /v2/payment et parse la réponse. Retourne le
     * payment_url et le transaction_id en cas de succès, ou un message
     * d'erreur sinon (logué dans error.log au format CINETPAY FAIL).
     */
    private static function callInit(array $payload, string $transactionId): array
    {
        $apiUrl = rtrim((string) env('CINETPAY_API_URL', ''), '/');
        if ($apiUrl === '') {
            return self::failure('CINETPAY_API_URL non configurée.');
        }

        $ch = curl_init($apiUrl);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => json_encode($payload, JSON_UNESCAPED_UNICODE),
            CURLOPT_HTTPHEADER     => ['Content-Type: application/json', 'Accept: application/json'],
            CURLOPT_CONNECTTIMEOUT => 10,
            CURLOPT_TIMEOUT        => 30,
        ]);
        $body     = curl_exec($ch);
        $httpCode = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlErr  = curl_error($ch);
        curl_close($ch);

        if ($body === false || $httpCode === 0) {
            error_log(sprintf(
                'CINETPAY FAIL init tx=%s curl=%s http=%d',
                $transactionId, $curlErr, $httpCode
            ));
            return self::failure('Connexion CinetPay impossible.');
        }

        $decoded = json_decode((string) $body, true);
        if (!is_array($decoded)) {
            error_log(sprintf(
                'CINETPAY FAIL init tx=%s parse http=%d body=%s',
                $transactionId, $httpCode, mb_substr((string) $body, 0, 500)
            ));
            return self::failure('Réponse CinetPay invalide.');
        }

        // Format attendu : { "code":"201", "message":"CREATED", "data":{ "payment_url":"...", "payment_token":"..." } }
        $code = (string) ($decoded['code'] ?? '');
        if ($code !== '201' || empty($decoded['data']['payment_url'])) {
            error_log(sprintf(
                'CINETPAY FAIL init tx=%s code=%s msg=%s body=%s',
                $transactionId,
                $code,
                $decoded['message'] ?? '',
                mb_substr((string) $body, 0, 500)
            ));
            return self::failure((string) ($decoded['message'] ?? 'Erreur CinetPay.'));
        }

        return [
            'url'           => (string) $decoded['data']['payment_url'],
            'transactionId' => $transactionId,
            'error'         => null,
        ];
    }

    /**
     * Format unique pour les transaction_id Variable -> CinetPay :
     *   <PREFIX>-<UNIX_MS>-<RANDOM6>
     * Cap à 50 caractères (limite CinetPay).
     */
    private static function generateTransactionId(string $prefix): string
    {
        $ms = (int) (microtime(true) * 1000);
        $rand = bin2hex(random_bytes(3)); // 6 hex chars
        return mb_substr(sprintf('%s-%d-%s', $prefix, $ms, $rand), 0, 50);
    }

    private static function failure(string $message): array
    {
        return ['url' => null, 'transactionId' => null, 'error' => $message];
    }
}
