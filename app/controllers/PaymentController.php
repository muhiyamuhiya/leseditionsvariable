<?php
namespace App\Controllers;

use App\Lib\Auth;
use App\Lib\Database;
use App\Lib\PaymentConfig;
use App\Lib\Session;
use App\Models\Book;

/**
 * Contrôleur des paiements (Stripe + Money Fusion)
 */
class PaymentController extends BaseController
{
    /**
     * Créer une session Stripe Checkout pour l'achat d'un livre
     * GET /achat/livre/:id
     */
    public function purchaseBook(string $bookId): void
    {
        Auth::requireLogin();
        $db = Database::getInstance();
        $bookId = (int) $bookId;

        $book = Book::findBySlug('') ?: null;
        // Chercher par ID avec jointures
        $book = $db->fetch(
            "SELECT b.*, COALESCE(a.nom_plume, CONCAT(u.prenom,' ',u.nom)) as author_display, a.slug as author_slug
             FROM books b JOIN authors a ON b.author_id=a.id JOIN users u ON a.user_id=u.id
             WHERE b.id = ? AND b.statut = 'publie'",
            [$bookId]
        );

        if (!$book) {
            Session::flash('error', 'Ce livre n\'est pas disponible.');
            redirect('/catalogue');
            return;
        }

        $user = Auth::user();

        // Déjà acheté ?
        $already = $db->fetch("SELECT 1 FROM user_books WHERE user_id = ? AND book_id = ? AND source = 'achat_unitaire'", [$user->id, $book->id]);
        if ($already) {
            Session::flash('success', 'Tu as déjà acheté ce livre.');
            redirect('/livre/' . $book->slug);
            return;
        }

        // Prix selon devise utilisateur
        $devise = strtoupper($user->devise_preferee ?? 'USD');
        $priceField = 'prix_unitaire_' . strtolower($devise);
        $price = (float) ($book->$priceField ?? $book->prix_unitaire_usd ?? 9.99);

        // Stripe attend les centimes
        $priceInCents = (int) round($price * 100);

        if (!PaymentConfig::initStripe()) {
            Session::flash('error', 'Paiement temporairement indisponible.');
            redirect('/livre/' . $book->slug);
            return;
        }

        try {
            $session = \Stripe\Checkout\Session::create([
                'payment_method_types' => ['card'],
                'line_items' => [[
                    'price_data' => [
                        'currency' => strtolower($devise),
                        'product_data' => [
                            'name' => $book->titre,
                            'description' => 'par ' . $book->author_display,
                        ],
                        'unit_amount' => $priceInCents,
                    ],
                    'quantity' => 1,
                ]],
                'mode' => 'payment',
                'customer_email' => $user->email,
                'success_url' => PaymentConfig::publicAppUrl() . '/paiement/succes?session_id={CHECKOUT_SESSION_ID}',
                'cancel_url'  => PaymentConfig::publicAppUrl() . '/livre/' . $book->slug . '?canceled=1',
                'metadata' => [
                    'user_id' => $user->id,
                    'book_id' => $book->id,
                    'type'    => 'book_purchase',
                ],
            ]);

            // Log de la transaction en attente
            $db->insert('transactions_log', [
                'type'                    => 'vente',
                'user_id'                 => $user->id,
                'reference_id'            => $book->id,
                'reference_type'          => 'books',
                'provider'                => 'stripe',
                'provider_transaction_id' => $session->id,
                'montant'                 => $price,
                'devise'                  => $devise,
                'statut'                  => 'en_attente',
                'ip_address'              => $_SERVER['REMOTE_ADDR'] ?? null,
            ]);

            header('Location: ' . $session->url);
            exit;

        } catch (\Exception $e) {
            Session::flash('error', 'Erreur de paiement : ' . $e->getMessage());
            redirect('/livre/' . $book->slug);
        }
    }

    /**
     * Page succès après paiement Stripe
     * GET /paiement/succes
     */
    public function success(): void
    {
        $sessionId = $_GET['session_id'] ?? null;
        $book = null;

        if ($sessionId && PaymentConfig::initStripe()) {
            try {
                $session = \Stripe\Checkout\Session::retrieve($sessionId);
                $bookId = $session->metadata->book_id ?? null;
                if ($bookId) {
                    $db = Database::getInstance();
                    $book = $db->fetch(
                        "SELECT b.*, COALESCE(a.nom_plume, CONCAT(u.prenom,' ',u.nom)) as author_display
                         FROM books b JOIN authors a ON b.author_id=a.id JOIN users u ON a.user_id=u.id
                         WHERE b.id = ?",
                        [(int) $bookId]
                    );

                    // Fulfillment immédiat (backup du webhook)
                    $this->fulfillBookPurchase($session->metadata->user_id, $bookId, $sessionId);
                }
            } catch (\Exception $e) {
                // Pas grave, le webhook traitera
            }
        }

        $this->view('payment/success', [
            'titre' => 'Paiement confirmé',
            'book'  => $book,
        ]);
    }

    /**
     * Page échec / annulation
     * GET /paiement/echec
     */
    public function failed(): void
    {
        $this->view('payment/failed', [
            'titre' => 'Paiement annulé',
        ]);
    }

    /**
     * Webhook Stripe
     * POST /webhook/stripe
     */
    public function stripeWebhook(): void
    {
        $payload = file_get_contents('php://input');
        $sigHeader = $_SERVER['HTTP_STRIPE_SIGNATURE'] ?? '';
        $webhookSecret = PaymentConfig::stripeWebhookSecret();

        try {
            if ($webhookSecret) {
                PaymentConfig::initStripe();
                $event = \Stripe\Webhook::constructEvent($payload, $sigHeader, $webhookSecret);
            } else {
                // Mode dev sans vérification de signature
                $event = json_decode($payload);
            }

            if (($event->type ?? '') === 'checkout.session.completed') {
                $session = $event->data->object;
                $type = $session->metadata->type ?? '';

                if ($type === 'book_purchase') {
                    $this->fulfillBookPurchase(
                        $session->metadata->user_id ?? null,
                        $session->metadata->book_id ?? null,
                        $session->id ?? null
                    );
                }
            }

            http_response_code(200);
            echo 'OK';
            exit;

        } catch (\Exception $e) {
            error_log('Stripe webhook error: ' . $e->getMessage());
            http_response_code(400);
            echo 'Error';
            exit;
        }
    }

    /**
     * Traite l'achat : ajoute le livre à la bibliothèque + enregistre la vente
     */
    private function fulfillBookPurchase(?int $userId, ?int $bookId, ?string $sessionId): void
    {
        if (!$userId || !$bookId) return;

        $db = Database::getInstance();

        // Idempotence : déjà traité ?
        $already = $db->fetch("SELECT 1 FROM user_books WHERE user_id = ? AND book_id = ? AND source = 'achat_unitaire'", [$userId, $bookId]);
        if ($already) return;

        $book = $db->fetch("SELECT * FROM books WHERE id = ?", [$bookId]);
        if (!$book) return;

        $prix = (float) $book->prix_unitaire_usd;
        $commission = round($prix * COMMISSION_RATE, 2);
        $revenuAuteur = round($prix * AUTHOR_SHARE_RATE, 2);

        // Ajouter à la bibliothèque
        $db->insert('user_books', [
            'user_id'    => $userId,
            'book_id'    => $bookId,
            'source'     => 'achat_unitaire',
            'date_ajout' => date('Y-m-d H:i:s'),
        ]);

        // Enregistrer la vente
        $db->insert('sales', [
            'user_id'                 => $userId,
            'book_id'                 => $bookId,
            'author_id'               => $book->author_id,
            'prix_paye'               => $prix,
            'devise'                  => 'USD',
            'prix_paye_usd'           => $prix,
            'commission_variable'     => $commission,
            'revenu_auteur'           => $revenuAuteur,
            'methode_paiement'        => 'stripe',
            'transaction_id'          => $sessionId,
            'statut'                  => 'payee',
            'date_vente'              => date('Y-m-d H:i:s'),
            'date_paiement_confirme'  => date('Y-m-d H:i:s'),
        ]);

        // Mettre à jour les stats
        $db->update('books', [
            'total_ventes' => (int) $book->total_ventes + 1,
            'revenus_cumul' => (float) $book->revenus_cumul + $prix,
        ], 'id = ?', [$bookId]);

        // Mettre à jour la transaction_log
        if ($sessionId) {
            $db->update('transactions_log', ['statut' => 'reussi'], 'provider_transaction_id = ?', [$sessionId]);
        }
    }
}
