<?php
namespace App\Controllers;

use App\Lib\Auth;
use App\Lib\Database;
use App\Lib\Mailer;
use App\Lib\Notification;
use App\Lib\PaymentConfig;
use App\Lib\Session;
use App\Models\Book;

/**
 * Paiements : achat livre (Stripe + Money Fusion) + abonnement
 */
class PaymentController extends BaseController
{
    private const PLANS = [
        'essentiel_mensuel' => ['prix' => 3,   'label' => 'Essentiel Mensuel', 'duree_jours' => 30,  'type_db' => 'essentiel_mensuel'],
        'essentiel_annuel'  => ['prix' => 30,  'label' => 'Essentiel Annuel',  'duree_jours' => 365, 'type_db' => 'essentiel_annuel'],
        'premium_mensuel'   => ['prix' => 10,  'label' => 'Premium Mensuel',   'duree_jours' => 30,  'type_db' => 'premium_mensuel'],
        'premium_annuel'    => ['prix' => 100, 'label' => 'Premium Annuel',    'duree_jours' => 365, 'type_db' => 'premium_annuel'],
    ];

    // =====================================================================
    // ACHAT LIVRE — CHOIX MÉTHODE
    // =====================================================================
    public function choosePaymentMethod(string $id): void
    {
        Auth::requireLogin();
        $book = $this->getPublishedBook((int) $id);
        if (!$book) { redirect('/catalogue'); return; }

        if ($this->alreadyBought($book->id)) {
            Session::flash('success', 'Tu as déjà acheté ce livre.');
            redirect('/livre/' . $book->slug);
            return;
        }

        $user = Auth::user();
        $this->view('payment/choose-method', [
            'titre' => 'Méthode de paiement',
            'book'  => $book,
            'user'  => $user,
            'type'  => 'book',
        ]);
    }

    // =====================================================================
    // ACHAT LIVRE — STRIPE
    // =====================================================================
    public function payWithStripe(string $id): void
    {
        Auth::requireLogin();
        $book = $this->getPublishedBook((int) $id);
        if (!$book) { redirect('/catalogue'); return; }
        if ($this->alreadyBought($book->id)) { redirect('/livre/' . $book->slug); return; }

        $user = Auth::user();
        $price = (float) ($book->prix_unitaire_usd ?? 9.99);

        if (!PaymentConfig::initStripe()) {
            Session::flash('error', 'Paiement par carte temporairement indisponible.');
            redirect('/livre/' . $book->slug);
            return;
        }

        try {
            $session = \Stripe\Checkout\Session::create([
                'payment_method_types' => ['card'],
                'line_items' => [[
                    'price_data' => [
                        'currency' => 'usd',
                        'product_data' => ['name' => $book->titre, 'description' => 'par ' . ($book->author_display ?? '')],
                        'unit_amount' => (int) round($price * 100),
                    ],
                    'quantity' => 1,
                ]],
                'mode' => 'payment',
                'customer_email' => $user->email,
                'success_url' => PaymentConfig::publicAppUrl() . '/paiement/succes?session_id={CHECKOUT_SESSION_ID}',
                'cancel_url'  => PaymentConfig::publicAppUrl() . '/livre/' . $book->slug . '?canceled=1',
                'metadata' => ['user_id' => $user->id, 'book_id' => $book->id, 'type' => 'book_purchase'],
            ]);

            $this->logTransaction('vente', $user->id, $book->id, 'books', 'stripe', $session->id, $price, 'USD');
            header('Location: ' . $session->url);
            exit;
        } catch (\Exception $e) {
            Session::flash('error', 'Erreur Stripe : ' . $e->getMessage());
            redirect('/livre/' . $book->slug);
        }
    }

    // =====================================================================
    // ACHAT LIVRE — MONEY FUSION
    // =====================================================================
    public function payWithMoneyFusion(string $id): void
    {
        Auth::requireLogin();
        $book = $this->getPublishedBook((int) $id);
        if (!$book) { redirect('/catalogue'); return; }
        if ($this->alreadyBought($book->id)) { redirect('/livre/' . $book->slug); return; }

        $user = Auth::user();
        $price = (float) ($book->prix_unitaire_usd ?? 9.99);
        $apiUrl = PaymentConfig::moneyFusionApiUrl();

        if (!$apiUrl) {
            Session::flash('error', 'Mobile Money temporairement indisponible.');
            redirect('/livre/' . $book->slug);
            return;
        }

        $payload = [
            'totalPrice'    => $price,
            'article'       => [['livre' => $price]],
            'personal_Info' => [['userId' => $user->id, 'bookId' => $book->id, 'type' => 'book_purchase']],
            'numeroSend'    => $user->telephone ?? '',
            'nomclient'     => $user->prenom . ' ' . $user->nom,
            'return_url'    => PaymentConfig::publicAppUrl() . '/paiement/moneyfusion/retour',
        ];

        $ch = curl_init($apiUrl);
        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($payload),
            CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 30,
        ]);
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        $data = json_decode($response);

        if ($data && !empty($data->url)) {
            $token = $data->token ?? $data->tokenPay ?? uniqid('mf_');
            $this->logTransaction('vente', $user->id, $book->id, 'books', 'money_fusion', $token, $price, 'USD');
            header('Location: ' . $data->url);
            exit;
        }

        Session::flash('error', 'Erreur Money Fusion. Réessaie ou choisis un autre moyen de paiement.');
        redirect('/achat/livre/' . $book->id);
    }

    // =====================================================================
    // ABONNEMENT — CHOIX MÉTHODE
    // =====================================================================
    public function subscriptionChoose(string $plan): void
    {
        Auth::requireLogin();
        if (!isset(self::PLANS[$plan])) { redirect('/abonnement'); return; }

        $this->view('payment/choose-method', [
            'titre'    => 'Abonnement — ' . self::PLANS[$plan]['label'],
            'plan'     => $plan,
            'planData' => self::PLANS[$plan],
            'user'     => Auth::user(),
            'type'     => 'subscription',
            'book'     => null,
        ]);
    }

    public function subscriptionStripe(string $plan): void
    {
        Auth::requireLogin();
        if (!isset(self::PLANS[$plan])) { redirect('/abonnement'); return; }

        $planData = self::PLANS[$plan];
        $user = Auth::user();

        if (!PaymentConfig::initStripe()) {
            Session::flash('error', 'Paiement par carte temporairement indisponible.');
            redirect('/abonnement');
            return;
        }

        try {
            $session = \Stripe\Checkout\Session::create([
                'payment_method_types' => ['card'],
                'line_items' => [[
                    'price_data' => [
                        'currency' => 'usd',
                        'product_data' => ['name' => $planData['label'], 'description' => 'Abonnement Les éditions Variable — ' . $planData['duree_jours'] . ' jours'],
                        'unit_amount' => $planData['prix'] * 100,
                    ],
                    'quantity' => 1,
                ]],
                'mode' => 'payment',
                'customer_email' => $user->email,
                'success_url' => PaymentConfig::publicAppUrl() . '/abonnement/succes?session_id={CHECKOUT_SESSION_ID}',
                'cancel_url'  => PaymentConfig::publicAppUrl() . '/abonnement?canceled=1',
                'metadata' => ['user_id' => $user->id, 'plan' => $plan, 'duree_jours' => $planData['duree_jours'], 'type' => 'subscription'],
            ]);

            $this->logTransaction('abonnement', $user->id, null, null, 'stripe', $session->id, $planData['prix'], 'USD');
            header('Location: ' . $session->url);
            exit;
        } catch (\Exception $e) {
            Session::flash('error', 'Erreur Stripe : ' . $e->getMessage());
            redirect('/abonnement');
        }
    }

    public function subscriptionMoneyFusion(string $plan): void
    {
        Auth::requireLogin();
        if (!isset(self::PLANS[$plan])) { redirect('/abonnement'); return; }

        $planData = self::PLANS[$plan];
        $user = Auth::user();
        $apiUrl = PaymentConfig::moneyFusionApiUrl();

        if (!$apiUrl) {
            Session::flash('error', 'Mobile Money temporairement indisponible.');
            redirect('/abonnement');
            return;
        }

        $payload = [
            'totalPrice'    => $planData['prix'],
            'article'       => [['abonnement' => $planData['prix']]],
            'personal_Info' => [['userId' => $user->id, 'plan' => $plan, 'duree_jours' => $planData['duree_jours'], 'type' => 'subscription']],
            'numeroSend'    => $user->telephone ?? '',
            'nomclient'     => $user->prenom . ' ' . $user->nom,
            'return_url'    => PaymentConfig::publicAppUrl() . '/paiement/moneyfusion/retour',
        ];

        $ch = curl_init($apiUrl);
        curl_setopt_array($ch, [CURLOPT_POST => true, CURLOPT_POSTFIELDS => json_encode($payload), CURLOPT_HTTPHEADER => ['Content-Type: application/json'], CURLOPT_RETURNTRANSFER => true, CURLOPT_TIMEOUT => 30]);
        $response = curl_exec($ch);
        curl_close($ch);
        $data = json_decode($response);

        if ($data && !empty($data->url)) {
            $token = $data->token ?? $data->tokenPay ?? uniqid('mf_');
            $this->logTransaction('abonnement', $user->id, null, null, 'money_fusion', $token, $planData['prix'], 'USD');
            header('Location: ' . $data->url);
            exit;
        }

        Session::flash('error', 'Erreur Money Fusion.');
        redirect('/abonnement');
    }

    // =====================================================================
    // COMMANDES ÉDITORIALES — paiement Stripe + Money Fusion
    // =====================================================================
    private function findEditorialOrder(int $orderId, int $userId): ?object
    {
        $row = Database::getInstance()->fetch(
            "SELECT o.*, s.nom AS service_nom FROM editorial_orders o
             JOIN editorial_services s ON s.id = o.service_id
             WHERE o.id = ? AND o.user_id = ?",
            [$orderId, $userId]
        );
        return $row ?: null;
    }

    public function payEditorialStripe(string $id): void
    {
        Auth::requireLogin();
        $user = Auth::user();
        $order = $this->findEditorialOrder((int) $id, $user->id);
        if (!$order || $order->statut !== 'accepte' || $order->montant_propose === null) {
            Session::flash('error', 'Commande indisponible pour le paiement.');
            redirect('/auteur/mes-commandes-editoriales');
            return;
        }
        if (!PaymentConfig::initStripe()) {
            Session::flash('error', 'Paiement par carte temporairement indisponible.');
            redirect('/auteur/mes-commandes-editoriales/' . $order->id);
            return;
        }

        try {
            $session = \Stripe\Checkout\Session::create([
                'payment_method_types' => ['card'],
                'line_items' => [[
                    'price_data' => [
                        'currency'     => strtolower($order->devise),
                        'product_data' => ['name' => 'Service éditorial — ' . $order->service_nom, 'description' => mb_substr($order->titre_projet ?? '', 0, 200)],
                        'unit_amount'  => (int) round((float) $order->montant_propose * 100),
                    ],
                    'quantity' => 1,
                ]],
                'mode' => 'payment',
                'customer_email' => $user->email,
                'success_url' => PaymentConfig::publicAppUrl() . '/paiement/succes?session_id={CHECKOUT_SESSION_ID}',
                'cancel_url'  => PaymentConfig::publicAppUrl() . '/auteur/mes-commandes-editoriales/' . $order->id . '?canceled=1',
                'metadata' => ['user_id' => $user->id, 'editorial_order_id' => $order->id, 'type' => 'editorial_order'],
            ]);
            $this->logTransaction('autre', $user->id, $order->id, 'editorial_orders', 'stripe', $session->id, (float) $order->montant_propose, $order->devise);
            header('Location: ' . $session->url);
            exit;
        } catch (\Exception $e) {
            Session::flash('error', 'Erreur Stripe : ' . $e->getMessage());
            redirect('/auteur/mes-commandes-editoriales/' . $order->id);
        }
    }

    public function payEditorialMoneyFusion(string $id): void
    {
        Auth::requireLogin();
        $user = Auth::user();
        $order = $this->findEditorialOrder((int) $id, $user->id);
        if (!$order || $order->statut !== 'accepte' || $order->montant_propose === null) {
            Session::flash('error', 'Commande indisponible pour le paiement.');
            redirect('/auteur/mes-commandes-editoriales');
            return;
        }
        $apiUrl = PaymentConfig::moneyFusionApiUrl();
        if (!$apiUrl) {
            Session::flash('error', 'Mobile Money temporairement indisponible.');
            redirect('/auteur/mes-commandes-editoriales/' . $order->id);
            return;
        }

        $payload = [
            'totalPrice'    => (float) $order->montant_propose,
            'article'       => [['service_editorial' => (float) $order->montant_propose]],
            'personal_Info' => [['userId' => $user->id, 'editorial_order_id' => (int) $order->id, 'type' => 'editorial_order']],
            'numeroSend'    => $user->telephone ?? '',
            'nomclient'     => $user->prenom . ' ' . $user->nom,
            'return_url'    => PaymentConfig::publicAppUrl() . '/paiement/moneyfusion/retour',
        ];

        $ch = curl_init($apiUrl);
        curl_setopt_array($ch, [CURLOPT_POST => true, CURLOPT_POSTFIELDS => json_encode($payload), CURLOPT_HTTPHEADER => ['Content-Type: application/json'], CURLOPT_RETURNTRANSFER => true, CURLOPT_TIMEOUT => 30]);
        $response = curl_exec($ch);
        curl_close($ch);
        $data = json_decode($response);

        if ($data && !empty($data->url)) {
            $token = $data->token ?? $data->tokenPay ?? uniqid('mf_');
            $this->logTransaction('autre', $user->id, (int) $order->id, 'editorial_orders', 'money_fusion', $token, (float) $order->montant_propose, $order->devise);
            header('Location: ' . $data->url);
            exit;
        }

        Session::flash('error', 'Erreur Money Fusion.');
        redirect('/auteur/mes-commandes-editoriales/' . $order->id);
    }

    // =====================================================================
    // PAGES RETOUR
    // =====================================================================
    public function success(): void
    {
        $sessionId = $_GET['session_id'] ?? null;
        $book = null;
        $editorialOrderId = null;

        if ($sessionId && PaymentConfig::initStripe()) {
            try {
                $session = \Stripe\Checkout\Session::retrieve($sessionId);
                $type = $session->metadata->type ?? '';
                if ($type === 'book_purchase') {
                    $this->fulfillBookPurchase((int) $session->metadata->user_id, (int) $session->metadata->book_id, $sessionId);
                    $book = Database::getInstance()->fetch("SELECT b.*, COALESCE(a.nom_plume, CONCAT(u.prenom,' ',u.nom)) as author_display FROM books b JOIN authors a ON b.author_id=a.id JOIN users u ON a.user_id=u.id WHERE b.id=?", [(int) $session->metadata->book_id]);
                } elseif ($type === 'subscription') {
                    $this->fulfillSubscription((int) $session->metadata->user_id, $session->metadata->plan, $sessionId);
                } elseif ($type === 'editorial_order') {
                    $this->fulfillEditorialOrder((int) $session->metadata->editorial_order_id, $sessionId);
                    $editorialOrderId = (int) $session->metadata->editorial_order_id;
                }
            } catch (\Exception $e) {}
        }

        // Redirection contextuelle pour les commandes éditoriales
        if ($editorialOrderId) {
            Session::flash('success', 'Paiement reçu. Notre équipe se met au travail.');
            redirect('/auteur/mes-commandes-editoriales/' . $editorialOrderId);
            return;
        }

        $this->view('payment/success', ['titre' => 'Paiement confirmé', 'book' => $book]);
    }

    public function subscriptionSuccess(): void
    {
        $sessionId = $_GET['session_id'] ?? null;
        $planLabel = '';

        if ($sessionId && PaymentConfig::initStripe()) {
            try {
                $session = \Stripe\Checkout\Session::retrieve($sessionId);
                $plan = $session->metadata->plan ?? '';
                $planLabel = self::PLANS[$plan]['label'] ?? '';
                $this->fulfillSubscription((int) $session->metadata->user_id, $plan, $sessionId);
            } catch (\Exception $e) {}
        }

        $sub = Auth::check() ? \App\Models\Subscription::getActive(Auth::id()) : null;

        $this->view('payment/subscription-success', [
            'titre'     => 'Abonnement activé',
            'planLabel' => $planLabel,
            'sub'       => $sub,
        ]);
    }

    public function failed(): void
    {
        $this->view('payment/failed', ['titre' => 'Paiement annulé']);
    }

    // =====================================================================
    // RETOUR DÉDIÉ MONEY FUSION
    // =====================================================================
    public function moneyFusionReturn(): void
    {
        Auth::requireLogin();

        $tokenPay = $_GET['tokenPay'] ?? $_GET['token'] ?? null;

        if (!$tokenPay) {
            $this->view('payment/moneyfusion-return', [
                'titre'            => 'Paiement Mobile Money',
                'status'           => 'unknown',
                'tokenPay'         => null,
                'book'             => null,
                'subscriptionInfo' => null,
            ]);
            return;
        }

        // Vérifier le statut du paiement auprès de Money Fusion
        $checkUrl = 'https://www.pay.moneyfusion.net/paiementNotif/' . urlencode($tokenPay);
        $ch = curl_init($checkUrl);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 10,
        ]);
        $response = curl_exec($ch);
        curl_close($ch);

        $data   = json_decode($response);
        $status = $data->data->statut ?? 'pending';

        // Récupérer la transaction loguée pour reconstruire le contexte
        $db = Database::getInstance();
        $transaction = $db->fetch(
            "SELECT * FROM transactions_log WHERE provider = 'money_fusion' AND provider_transaction_id = ?",
            [$tokenPay]
        );

        $book = null;
        $subscriptionInfo = null;

        if ($transaction) {
            if ($transaction->reference_type === 'books' && !empty($transaction->reference_id)) {
                $book = $db->fetch(
                    "SELECT b.*, COALESCE(a.nom_plume, CONCAT(u.prenom,' ',u.nom)) as author_display
                     FROM books b
                     JOIN authors a ON b.author_id = a.id
                     JOIN users u ON a.user_id = u.id
                     WHERE b.id = ?",
                    [(int) $transaction->reference_id]
                );
            } elseif ($transaction->type === 'abonnement') {
                // Le webhook a peut-être déjà créé la souscription : on retrouve le label du plan
                $sub = $db->fetch("SELECT type FROM subscriptions WHERE transaction_id = ?", [$tokenPay]);
                $planLabel = null;
                if ($sub) {
                    foreach (self::PLANS as $p) {
                        if ($p['type_db'] === $sub->type) { $planLabel = $p['label']; break; }
                    }
                }
                $subscriptionInfo = ['plan' => $planLabel];
            }
        }

        $this->view('payment/moneyfusion-return', [
            'titre'            => 'Paiement Mobile Money',
            'status'           => $status,
            'tokenPay'         => $tokenPay,
            'book'             => $book ?: null,
            'subscriptionInfo' => $subscriptionInfo,
        ]);
    }

    // =====================================================================
    // WEBHOOKS
    // =====================================================================
    public function stripeWebhook(): void
    {
        $payload = file_get_contents('php://input');
        $sig = $_SERVER['HTTP_STRIPE_SIGNATURE'] ?? '';
        $secret = PaymentConfig::stripeWebhookSecret();

        try {
            if ($secret) {
                PaymentConfig::initStripe();
                $event = \Stripe\Webhook::constructEvent($payload, $sig, $secret);
            } else {
                $event = json_decode($payload);
            }

            $eventType = $event->type ?? '';

            if ($eventType === 'checkout.session.completed') {
                $s = $event->data->object;
                $type = $s->metadata->type ?? '';
                if ($type === 'book_purchase') {
                    $this->fulfillBookPurchase((int) $s->metadata->user_id, (int) $s->metadata->book_id, $s->id);
                } elseif ($type === 'subscription') {
                    $this->fulfillSubscription((int) $s->metadata->user_id, $s->metadata->plan, $s->id);
                } elseif ($type === 'editorial_order') {
                    $this->fulfillEditorialOrder((int) $s->metadata->editorial_order_id, $s->id);
                }
            } elseif ($eventType === 'invoice.paid') {
                // Renouvellement automatique d'abonnement réussi
                $this->handleStripeInvoicePaid($event->data->object);
            } elseif ($eventType === 'invoice.payment_failed') {
                // Échec de prélèvement (renouvellement)
                $this->handleStripeInvoicePaymentFailed($event->data->object);
            }

            http_response_code(200);
            echo 'OK';
        } catch (\Exception $e) {
            error_log('Stripe webhook: ' . $e->getMessage());
            http_response_code(400);
            echo 'Error';
        }
        exit;
    }

    /**
     * Webhook Money Fusion — vérification d'origine renforcée.
     *
     * SÉCURITÉ : on ne fait JAMAIS confiance au payload reçu en POST.
     * Avant tout fulfill, on re-query l'API MF /paiementNotif/{token} pour
     * récupérer le statut authentique et le `personal_Info` officiel.
     * Sans cette re-query, n'importe qui peut envoyer un POST forgé pour
     * déclencher un fulfillBookPurchase et obtenir un livre gratuitement.
     *
     * Tout webhook qui ne passe pas la vérification est loggé via
     * error_log avec l'IP source — utile pour détecter des tentatives
     * d'attaque (reconnaissance de l'endpoint).
     *
     * On répond 200 (au lieu de 403) sur les payloads invalides pour
     * éviter de divulguer aux attaquants quels tokens existent.
     */
    public function moneyFusionWebhook(): void
    {
        $rawBody = file_get_contents('php://input');
        $payload = json_decode((string) $rawBody, true);

        // Token présent ? (premier pré-filtre cheap)
        $token = '';
        if (is_array($payload)) {
            $token = (string) ($payload['tokenPay'] ?? $payload['token'] ?? '');
        }

        $clientIp  = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '-';

        if ($token === '') {
            error_log("MF webhook REFUSÉ — token absent | IP={$clientIp} | UA={$userAgent} | body=" . mb_substr((string) $rawBody, 0, 200));
            http_response_code(200);
            echo 'OK';
            exit;
        }

        // Vérification d'origine : on re-query l'API MF avec le token reçu.
        // Si le token n'existe pas chez MF, c'est forcément une tentative
        // d'attaque ou un payload corrompu — on refuse.
        $verified = $this->verifyMoneyFusionStatus($token);
        if ($verified === null) {
            error_log("MF webhook REFUSÉ — verify failed pour token={$token} | IP={$clientIp} | UA={$userAgent}");
            http_response_code(200);
            echo 'OK';
            exit;
        }

        // À partir d'ici, $verified vient de l'API MF authentique. On ignore
        // complètement le payload reçu en POST et on travaille avec les
        // données officielles.
        $statut = (string) ($verified['statut'] ?? '');
        $info   = $verified['personal_Info'][0] ?? [];
        $type   = $info['type'] ?? '';

        $db = Database::getInstance();

        if (in_array($statut, ['paid', 'success', 'completed', 'pending succes', 'no paid'], true)) {
            // Note : MF retourne parfois 'pending succes' (sic) pour un
            // paiement Mobile Money en cours de validation côté opérateur.
            // On reste strict et on ne fulfill que sur succès confirmé.
            if (!in_array($statut, ['paid', 'success', 'completed'], true)) {
                // Pending : on attend, le webhook sera rappelé
                http_response_code(200);
                echo 'OK';
                exit;
            }

            if ($type === 'book_purchase' && !empty($info['userId']) && !empty($info['bookId'])) {
                $this->fulfillBookPurchase((int) $info['userId'], (int) $info['bookId'], $token);
            } elseif ($type === 'subscription' && !empty($info['userId']) && !empty($info['plan'])) {
                $this->fulfillSubscription((int) $info['userId'], $info['plan'], $token);
            } elseif ($type === 'editorial_order' && !empty($info['editorial_order_id'])) {
                $this->fulfillEditorialOrder((int) $info['editorial_order_id'], $token);
            }
            $db->update('transactions_log', ['statut' => 'reussi'], 'provider_transaction_id = ?', [$token]);
        } elseif (in_array($statut, ['failed', 'cancelled', 'expired', 'echec'], true)) {
            $db->update('transactions_log', ['statut' => 'echoue'], 'provider_transaction_id = ?', [$token]);
        }

        http_response_code(200);
        echo 'OK';
        exit;
    }

    /**
     * Re-query l'API Money Fusion pour récupérer le statut AUTHENTIQUE
     * d'une transaction à partir de son tokenPay. Utilisé à 2 endroits :
     *   - moneyFusionReturn (page de retour utilisateur, déjà avant)
     *   - moneyFusionWebhook (vérification d'origine, ajouté pour fix sécu)
     *
     * Retourne ['statut' => ..., 'personal_Info' => [...]] si OK, null sinon.
     */
    private function verifyMoneyFusionStatus(string $tokenPay): ?array
    {
        if ($tokenPay === '') return null;

        $checkUrl = 'https://www.pay.moneyfusion.net/paiementNotif/' . urlencode($tokenPay);
        $ch = curl_init($checkUrl);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 10,
            CURLOPT_HTTPHEADER     => ['Accept: application/json'],
        ]);
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode !== 200 || $response === false || $response === '') {
            return null;
        }

        $data = json_decode((string) $response, true);
        if (!is_array($data) || empty($data['data'])) {
            return null;
        }

        $core = $data['data'];
        $statut = $core['statut'] ?? $core['status'] ?? null;
        if ($statut === null) {
            // Pas de statut dans la réponse → réponse invalide
            return null;
        }

        return [
            'statut'        => (string) $statut,
            'personal_Info' => $core['personal_Info'] ?? $core['personalInfo'] ?? [],
        ];
    }

    // =====================================================================
    // FULFILLMENT (privé, idempotent)
    // =====================================================================
    private function fulfillBookPurchase(int $userId, int $bookId, ?string $txId): void
    {
        $db = Database::getInstance();
        // Idempotence : déjà acheté
        $already = $db->fetch("SELECT 1 FROM user_books WHERE user_id = ? AND book_id = ? AND source = 'achat_unitaire'", [$userId, $bookId]);
        if ($already) return;

        // On joint authors pour récupérer is_classic — les auteurs classiques
        // (domaine public, sans ayants droit) reçoivent 0% et la plateforme 100%.
        $book = $db->fetch(
            "SELECT b.*, a.is_classic
               FROM books b
               JOIN authors a ON a.id = b.author_id
              WHERE b.id = ?",
            [$bookId]
        );
        if (!$book) return;

        $prix = (float) $book->prix_unitaire_usd;
        if (!empty($book->is_classic)) {
            // Classique : 100% plateforme, 0% auteur (track la sale pour analytics)
            $commission = $prix;
            $revenu     = 0.00;
        } else {
            $commission = round($prix * COMMISSION_RATE, 2);
            $revenu     = round($prix * AUTHOR_SHARE_RATE, 2);
        }

        // Si une ligne existe déjà pour ce couple (user, livre) avec une autre source (ex: 'favori'),
        // on la met à niveau au lieu d'insérer (la contrainte UNIQUE (user_id, book_id) interdirait l'INSERT)
        $existing = $db->fetch("SELECT id FROM user_books WHERE user_id = ? AND book_id = ?", [$userId, $bookId]);
        if ($existing) {
            $db->update('user_books', [
                'source'     => 'achat_unitaire',
                'date_ajout' => date('Y-m-d H:i:s'),
            ], 'id = ?', [$existing->id]);
        } else {
            $db->insert('user_books', ['user_id' => $userId, 'book_id' => $bookId, 'source' => 'achat_unitaire', 'date_ajout' => date('Y-m-d H:i:s')]);
        }
        $db->insert('sales', [
            'user_id' => $userId, 'book_id' => $bookId, 'author_id' => $book->author_id,
            'prix_paye' => $prix, 'devise' => 'USD', 'prix_paye_usd' => $prix,
            'commission_variable' => $commission, 'revenu_auteur' => $revenu,
            'methode_paiement' => 'stripe', 'transaction_id' => $txId, 'statut' => 'payee',
            'date_vente' => date('Y-m-d H:i:s'), 'date_paiement_confirme' => date('Y-m-d H:i:s'),
        ]);
        $db->update('books', ['total_ventes' => (int) $book->total_ventes + 1, 'revenus_cumul' => (float) $book->revenus_cumul + $prix], 'id = ?', [$bookId]);

        if ($txId) { $db->update('transactions_log', ['statut' => 'reussi'], 'provider_transaction_id = ?', [$txId]); }

        Notification::create(
            $userId,
            'purchase_confirmed',
            'Achat confirmé',
            'Tu peux maintenant lire « ' . $book->titre . ' » dans ta bibliothèque.',
            '/lire/' . $book->slug,
            'cart'
        );

        // Reçu PDF par email — non-bloquant
        $user = $db->fetch("SELECT id, prenom, nom, email FROM users WHERE id = ?", [$userId]);
        if ($user) {
            try {
                Mailer::sendPaymentReceipt(
                    $user,
                    'book',
                    (string) $book->titre,
                    $prix,
                    'USD',
                    $txId && str_starts_with((string) $txId, 'cs_') ? 'stripe' : 'money_fusion',
                    (string) ($txId ?? ''),
                    date('Y-m-d H:i:s')
                );
            } catch (\Throwable $e) {
                error_log('Receipt email (book) failed: ' . $e->getMessage());
            }
        }
    }

    private function fulfillEditorialOrder(int $orderId, ?string $txId): void
    {
        $db = Database::getInstance();
        $order = $db->fetch(
            "SELECT o.*, s.nom AS service_nom FROM editorial_orders o
             JOIN editorial_services s ON s.id = o.service_id
             WHERE o.id = ?",
            [$orderId]
        );
        if (!$order) return;
        if (in_array($order->statut, ['en_cours', 'livre'], true)) return; // idempotent

        $db->update('editorial_orders', [
            'statut'         => 'en_cours',
            'transaction_id' => $txId,
            'paye_at'        => date('Y-m-d H:i:s'),
        ], 'id = ?', [$orderId]);

        if ($txId) { $db->update('transactions_log', ['statut' => 'reussi'], 'provider_transaction_id = ?', [$txId]); }

        Notification::create(
            (int) $order->user_id,
            'editorial_paid',
            'Paiement reçu',
            'On s\'occupe de ton projet « ' . ($order->service_nom ?? '') . ' ». Tu seras notifié dès la livraison.',
            '/auteur/mes-commandes-editoriales/' . $orderId,
            'check'
        );

        Notification::createForAdmins(
            'editorial_paid_admin',
            'Commande éditoriale payée',
            'La commande #' . $orderId . ' (« ' . ($order->service_nom ?? '') . ' ») a été payée et passe en cours.',
            '/admin/services-editoriaux/' . $orderId,
            'cart'
        );
    }

    private function fulfillSubscription(int $userId, string $plan, ?string $txId): void
    {
        if (!isset(self::PLANS[$plan])) return;
        $db = Database::getInstance();

        // Déjà actif avec ce tx ?
        if ($txId) {
            $exists = $db->fetch("SELECT 1 FROM subscriptions WHERE transaction_id = ?", [$txId]);
            if ($exists) return;
        }

        $planData = self::PLANS[$plan];

        // Désactiver l'ancien
        $db->update('subscriptions', ['statut' => 'expire'], "user_id = ? AND statut = 'actif'", [$userId]);

        $dateDebut = date('Y-m-d H:i:s');
        $dateFin = date('Y-m-d H:i:s', strtotime("+{$planData['duree_jours']} days"));

        $db->insert('subscriptions', [
            'user_id'       => $userId,
            'type'          => $planData['type_db'],
            'date_debut'    => $dateDebut,
            'date_fin'      => $dateFin,
            'prix_paye'     => $planData['prix'],
            'devise'        => 'USD',
            'methode_paiement' => 'stripe',
            'transaction_id' => $txId,
            'statut'        => 'actif',
        ]);

        if ($txId) { $db->update('transactions_log', ['statut' => 'reussi'], 'provider_transaction_id = ?', [$txId]); }

        Notification::create(
            $userId,
            'subscription_active',
            'Abonnement actif !',
            'Bienvenue dans ton abonnement ' . $planData['label'] . '. Lecture illimitée jusqu\'au ' . date('d/m/Y', strtotime($dateFin)) . '.',
            '/catalogue',
            'premium'
        );

        // Reçu PDF par email — non-bloquant
        $user = $db->fetch("SELECT id, prenom, nom, email FROM users WHERE id = ?", [$userId]);
        if ($user) {
            try {
                Mailer::sendPaymentReceipt(
                    $user,
                    'subscription',
                    $planData['label'],
                    (float) $planData['prix'],
                    'USD',
                    $txId && str_starts_with((string) $txId, 'cs_') ? 'stripe' : 'money_fusion',
                    (string) ($txId ?? ''),
                    $dateDebut
                );
            } catch (\Throwable $e) {
                error_log('Receipt email (subscription) failed: ' . $e->getMessage());
            }
        }
    }

    /**
     * Stripe webhook : invoice.paid → renouvellement automatique d'abonnement réussi.
     * On crée une nouvelle ligne subscriptions (nouvelle période) et envoie l'email de confirmation.
     */
    private function handleStripeInvoicePaid(object $invoice): void
    {
        $stripeSubId = (string) ($invoice->subscription ?? '');
        $stripeCustomerId = (string) ($invoice->customer ?? '');
        if ($stripeSubId === '' || $stripeCustomerId === '') return;

        $db = Database::getInstance();

        // Idempotence : si on a déjà traité cette invoice, on sort
        $invoiceId = (string) ($invoice->id ?? '');
        if ($invoiceId !== '') {
            $already = $db->fetch("SELECT 1 FROM subscriptions WHERE transaction_id = ?", [$invoiceId]);
            if ($already) return;
        }

        // Retrouver l'utilisateur par stripe_customer_id, sinon via la dernière subscription connue
        $user = $db->fetch("SELECT id, prenom, nom, email FROM users WHERE stripe_customer_id = ?", [$stripeCustomerId]);
        if (!$user) {
            $row = $db->fetch("SELECT user_id FROM subscriptions WHERE stripe_subscription_id = ? ORDER BY id DESC LIMIT 1", [$stripeSubId]);
            if ($row) {
                $user = $db->fetch("SELECT id, prenom, nom, email FROM users WHERE id = ?", [$row->user_id]);
            }
        }
        if (!$user) {
            error_log("invoice.paid: user introuvable pour customer={$stripeCustomerId}");
            return;
        }

        // Récupérer le plan à partir de l'abonnement précédent
        $prev = $db->fetch(
            "SELECT type, prix_paye, devise FROM subscriptions WHERE stripe_subscription_id = ? ORDER BY id DESC LIMIT 1",
            [$stripeSubId]
        );
        $type = $prev->type ?? 'essentiel_mensuel';
        $planData = self::PLANS[$type] ?? self::PLANS['essentiel_mensuel'];
        $amount = (float) ($invoice->amount_paid ?? 0) / 100; // Stripe en centimes
        if ($amount <= 0) { $amount = (float) ($prev->prix_paye ?? $planData['prix']); }
        $devise = strtoupper((string) ($invoice->currency ?? $prev->devise ?? 'USD'));

        // Désactiver l'ancien abo, créer la nouvelle période
        $db->update('subscriptions', ['statut' => 'expire'], "user_id = ? AND statut = 'actif'", [$user->id]);

        $dateDebut = date('Y-m-d H:i:s');
        $dateFin   = date('Y-m-d H:i:s', strtotime("+{$planData['duree_jours']} days"));

        $db->insert('subscriptions', [
            'user_id'                => $user->id,
            'type'                   => $type,
            'date_debut'             => $dateDebut,
            'date_fin'               => $dateFin,
            'prix_paye'              => $amount,
            'devise'                 => $devise,
            'methode_paiement'       => 'stripe',
            'transaction_id'         => $invoiceId,
            'stripe_subscription_id' => $stripeSubId,
            'renouvellement_auto'    => 1,
            'statut'                 => 'actif',
        ]);

        // Email de confirmation de renouvellement
        try {
            Mailer::sendSubscriptionRenewed(
                $user,
                $planData['label'],
                $amount,
                $devise,
                $dateFin,
                $invoiceId
            );
        } catch (\Throwable $e) {
            error_log('Renewal email failed: ' . $e->getMessage());
        }

        Notification::create(
            (int) $user->id,
            'subscription_renewed',
            'Abonnement renouvelé',
            'Ton abonnement ' . $planData['label'] . ' a été renouvelé jusqu\'au ' . date('d/m/Y', strtotime($dateFin)) . '.',
            '/catalogue',
            'premium'
        );
    }

    /**
     * Stripe webhook : invoice.payment_failed → échec d'un prélèvement.
     * Marque l'abonnement en échec (sans le suspendre immédiatement) et email l'utilisateur.
     */
    private function handleStripeInvoicePaymentFailed(object $invoice): void
    {
        $stripeSubId = (string) ($invoice->subscription ?? '');
        $stripeCustomerId = (string) ($invoice->customer ?? '');
        if ($stripeSubId === '' && $stripeCustomerId === '') return;

        $db = Database::getInstance();

        // Retrouver l'utilisateur
        $user = null;
        if ($stripeCustomerId !== '') {
            $user = $db->fetch("SELECT id, prenom, nom, email FROM users WHERE stripe_customer_id = ?", [$stripeCustomerId]);
        }
        if (!$user && $stripeSubId !== '') {
            $row = $db->fetch("SELECT user_id FROM subscriptions WHERE stripe_subscription_id = ? ORDER BY id DESC LIMIT 1", [$stripeSubId]);
            if ($row) {
                $user = $db->fetch("SELECT id, prenom, nom, email FROM users WHERE id = ?", [$row->user_id]);
            }
        }
        if (!$user) {
            error_log("invoice.payment_failed: user introuvable pour customer={$stripeCustomerId}");
            return;
        }

        // Récupérer le plan/montant depuis l'abo en cours
        $sub = $db->fetch(
            "SELECT type, prix_paye, devise, nb_tentatives_renouvellement FROM subscriptions
             WHERE stripe_subscription_id = ? ORDER BY id DESC LIMIT 1",
            [$stripeSubId]
        );
        $type = $sub->type ?? 'essentiel_mensuel';
        $planData = self::PLANS[$type] ?? self::PLANS['essentiel_mensuel'];
        $amount = (float) ($invoice->amount_due ?? 0) / 100;
        if ($amount <= 0) { $amount = (float) ($sub->prix_paye ?? $planData['prix']); }
        $devise = strtoupper((string) ($invoice->currency ?? $sub->devise ?? 'USD'));

        // Incrément du compteur de tentatives
        $tentatives = (int) ($sub->nb_tentatives_renouvellement ?? 0) + 1;
        $db->update(
            'subscriptions',
            ['statut' => 'echec_paiement', 'nb_tentatives_renouvellement' => $tentatives],
            "stripe_subscription_id = ?",
            [$stripeSubId]
        );

        // Stripe Smart Retries : prochain essai dans ~3 jours, max 4 tentatives par défaut
        $dateRetry = date('Y-m-d H:i:s', strtotime('+3 days'));
        $attemptsRemaining = max(1, 4 - $tentatives);

        try {
            Mailer::sendPaymentFailed(
                $user,
                $planData['label'],
                $amount,
                $devise,
                $dateRetry,
                $attemptsRemaining
            );
        } catch (\Throwable $e) {
            error_log('Payment-failed email failed: ' . $e->getMessage());
        }

        Notification::create(
            (int) $user->id,
            'payment_failed',
            'Échec de paiement',
            'Le prélèvement de ton abonnement a échoué. Mets à jour ta carte avant le ' . date('d/m/Y', strtotime($dateRetry)) . '.',
            '/mon-compte/abonnement',
            'alert'
        );
    }

    // =====================================================================
    // HELPERS
    // =====================================================================
    private function getPublishedBook(int $id): ?object
    {
        $db = Database::getInstance();
        return $db->fetch(
            "SELECT b.*, COALESCE(a.nom_plume, CONCAT(u.prenom,' ',u.nom)) as author_display, a.slug as author_slug
             FROM books b JOIN authors a ON b.author_id=a.id JOIN users u ON a.user_id=u.id WHERE b.id = ? AND b.statut = 'publie'",
            [$id]
        ) ?: null;
    }

    private function alreadyBought(int $bookId): bool
    {
        $db = Database::getInstance();
        return (bool) $db->fetch("SELECT 1 FROM user_books WHERE user_id = ? AND book_id = ? AND source = 'achat_unitaire'", [Auth::id(), $bookId]);
    }

    private function logTransaction(string $type, int $userId, ?int $refId, ?string $refType, string $provider, string $providerTxId, float $montant, string $devise): void
    {
        Database::getInstance()->insert('transactions_log', [
            'type' => $type, 'user_id' => $userId, 'reference_id' => $refId, 'reference_type' => $refType,
            'provider' => $provider === 'money_fusion' ? 'money_fusion' : 'stripe',
            'provider_transaction_id' => $providerTxId, 'montant' => $montant, 'devise' => $devise,
            'statut' => 'en_attente', 'ip_address' => $_SERVER['REMOTE_ADDR'] ?? null,
        ]);
    }
}
