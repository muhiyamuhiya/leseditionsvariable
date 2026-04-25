<?php
namespace App\Lib;

use App\Models\Book;
use App\Models\Subscription;

/**
 * Matrice d'accès centralisée pour les livres
 */
class BookAccess
{
    /**
     * Le user peut-il lire le livre en entier ?
     * - admin : toujours
     * - achat unitaire : toujours
     * - abonnement Premium : si le livre est marqué accessible_abonnement_premium
     * - abonnement Essentiel : si le livre est marqué accessible_abonnement_essentiel
     */
    public static function canReadFull(?object $user, int $bookId): bool
    {
        if (!$user) return false;
        if ($user->role === 'admin') return true;

        $db = Database::getInstance();

        // Auteur sur ses propres livres : preview autorisée
        if ($user->role === 'auteur') {
            $author = $db->fetch("SELECT id FROM authors WHERE user_id = ?", [$user->id]);
            if ($author) {
                $owns = $db->fetch("SELECT 1 FROM books WHERE id = ? AND author_id = ?", [$bookId, $author->id]);
                if ($owns) return true;
            }
        }

        // Achat unitaire
        $bought = $db->fetch(
            "SELECT 1 FROM user_books WHERE user_id = ? AND book_id = ? AND source = 'achat_unitaire'",
            [$user->id, $bookId]
        );
        if ($bought) return true;

        // Abonnement actif (ou annulé mais encore dans la période payée)
        $sub = Subscription::getActive($user->id);
        if (!$sub) return false;

        $book = Book::find($bookId);
        if (!$book) return false;

        $tier = Subscription::tierOf($sub);
        if ($tier === 'premium') {
            return (bool) ($book->accessible_abonnement_premium ?? false);
        }
        if ($tier === 'essentiel') {
            return (bool) ($book->accessible_abonnement_essentiel ?? false);
        }
        return false;
    }

    public static function canReadExtract(?object $user): bool
    {
        return $user !== null;
    }

    public static function canReview(?object $user, int $bookId): bool
    {
        if (!$user) return false;
        if ($user->role === 'admin') return true;

        // Seules les sources légitimes (achat ou abonnement) ouvrent le droit d'avis
        $db = Database::getInstance();
        $owned = $db->fetch(
            "SELECT 1 FROM user_books WHERE user_id = ? AND book_id = ? AND source IN ('achat_unitaire','abonnement')",
            [$user->id, $bookId]
        );
        if ($owned) return true;

        return Subscription::isUserActive($user->id);
    }

    public static function canFavorite(?object $user): bool
    {
        return $user !== null;
    }

    public static function hasBought(?object $user, int $bookId): bool
    {
        if (!$user) return false;
        $db = Database::getInstance();
        return (bool) $db->fetch(
            "SELECT 1 FROM user_books WHERE user_id = ? AND book_id = ? AND source = 'achat_unitaire'",
            [$user->id, $bookId]
        );
    }

    /**
     * Décrit pourquoi l'accès est refusé (et propose une CTA contextuelle).
     * Retourne ['can_read' => true] si l'accès est OK, sinon une struct avec reason/message/cta.
     */
    public static function getRequiredAccess(?object $user, int $bookId): array
    {
        if (self::canReadFull($user, $bookId)) {
            return ['can_read' => true];
        }

        $book = Book::find($bookId);
        if (!$book) {
            return ['can_read' => false, 'reason' => 'not_found', 'message' => 'Livre introuvable.'];
        }

        // Cas spécifique : user abonné Essentiel mais livre Premium-only
        if ($user) {
            $sub = Subscription::getActive($user->id);
            $tier = Subscription::tierOf($sub);
            if ($tier === 'essentiel'
                && empty($book->accessible_abonnement_essentiel)
                && !empty($book->accessible_abonnement_premium)) {
                return [
                    'can_read'  => false,
                    'reason'    => 'premium_only',
                    'message'   => 'Ce livre est réservé aux abonnés Premium. Passe au plan Premium pour le lire.',
                    'cta_label' => 'Passer au Premium',
                    'cta_url'   => '/abonnement?upgrade=premium',
                ];
            }
        }

        return [
            'can_read'  => false,
            'reason'    => 'paywall',
            'message'   => 'Achète ce livre ou abonne-toi pour le lire en entier.',
            'cta_label' => 'Voir les options',
            'cta_url'   => '/livre/' . $book->slug,
        ];
    }
}
