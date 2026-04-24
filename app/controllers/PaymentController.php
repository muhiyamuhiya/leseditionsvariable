<?php
namespace App\Controllers;

use App\Lib\Auth;
use App\Lib\Database;
use App\Lib\PaymentConfig;
use App\Lib\Session;
use App\Models\Book;

/**
 * Paiements : achat livre (Stripe + Money Fusion) + abonnement
 */
class PaymentController extends BaseController
{
    private const PLANS = [
        'mensuel' => ['prix' => 3, 'label' => 'Essentiel Mensuel', 'duree_jours' => 30, 'type_db' => 'mensuel'],
        'annuel'  => ['prix' => 30, 'label' => 'Essentiel Annuel', 'duree_jours' => 365, 'type_db' => 'annuel'],
        'premium' => ['prix' => 8, 'label' => 'Premium Mensuel', 'duree_jours' => 30, 'type_db' => 'premium_mensuel'],
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
    // PAGES RETOUR
    // =====================================================================
    public function success(): void
    {
        $sessionId = $_GET['session_id'] ?? null;
        $book = null;

        if ($sessionId && PaymentConfig::initStripe()) {
            try {
                $session = \Stripe\Checkout\Session::retrieve($sessionId);
                $type = $session->metadata->type ?? '';
                if ($type === 'book_purchase') {
                    $this->fulfillBookPurchase((int) $session->metadata->user_id, (int) $session->metadata->book_id, $sessionId);
                    $book = Database::getInstance()->fetch("SELECT b.*, COALESCE(a.nom_plume, CONCAT(u.prenom,' ',u.nom)) as author_display FROM books b JOIN authors a ON b.author_id=a.id JOIN users u ON a.user_id=u.id WHERE b.id=?", [(int) $session->metadata->book_id]);
                } elseif ($type === 'subscription') {
                    $this->fulfillSubscription((int) $session->metadata->user_id, $session->metadata->plan, $sessionId);
                }
            } catch (\Exception $e) {}
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

            if (($event->type ?? '') === 'checkout.session.completed') {
                $s = $event->data->object;
                $type = $s->metadata->type ?? '';
                if ($type === 'book_purchase') {
                    $this->fulfillBookPurchase((int) $s->metadata->user_id, (int) $s->metadata->book_id, $s->id);
                } elseif ($type === 'subscription') {
                    $this->fulfillSubscription((int) $s->metadata->user_id, $s->metadata->plan, $s->id);
                }
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

    public function moneyFusionWebhook(): void
    {
        $payload = json_decode(file_get_contents('php://input'), true);
        if (!$payload) { http_response_code(400); exit; }

        $token = $payload['tokenPay'] ?? $payload['token'] ?? '';
        $statut = $payload['statut'] ?? $payload['status'] ?? '';
        $info = $payload['personal_Info'][0] ?? $payload['personalInfo'][0] ?? [];
        $type = $info['type'] ?? '';

        $db = Database::getInstance();

        if (in_array($statut, ['paid', 'success', 'completed'])) {
            if ($type === 'book_purchase' && !empty($info['userId']) && !empty($info['bookId'])) {
                $this->fulfillBookPurchase((int) $info['userId'], (int) $info['bookId'], $token);
            } elseif ($type === 'subscription' && !empty($info['userId']) && !empty($info['plan'])) {
                $this->fulfillSubscription((int) $info['userId'], $info['plan'], $token);
            }
            $db->update('transactions_log', ['statut' => 'reussi'], 'provider_transaction_id = ?', [$token]);
        } elseif (in_array($statut, ['failed', 'cancelled', 'expired'])) {
            $db->update('transactions_log', ['statut' => 'echoue'], 'provider_transaction_id = ?', [$token]);
        }

        http_response_code(200);
        echo 'OK';
        exit;
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

        $book = $db->fetch("SELECT * FROM books WHERE id = ?", [$bookId]);
        if (!$book) return;

        $prix = (float) $book->prix_unitaire_usd;
        $commission = round($prix * COMMISSION_RATE, 2);
        $revenu = round($prix * AUTHOR_SHARE_RATE, 2);

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
