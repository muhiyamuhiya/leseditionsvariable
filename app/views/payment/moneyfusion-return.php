<?php
/**
 * Page retour après paiement Money Fusion (Mobile Money)
 * 3 statuts gérés : paid (succès), pending (en cours), failure (échec)
 */
$statusLower = strtolower((string) ($status ?? 'unknown'));
$isPaid    = in_array($statusLower, ['paid', 'success', 'completed', 'reussi'], true);
$isFailed  = in_array($statusLower, ['failure', 'failed', 'no paid', 'cancelled', 'expired', 'echec'], true);
$isPending = !$isPaid && !$isFailed; // tout le reste = en cours / inconnu
?>
<section class="min-h-[60vh] flex items-center justify-center px-4">
    <div class="text-center max-w-md">

        <?php if ($isPaid): ?>
            <!-- ============================================================
                 CAS 1 — Paiement confirmé
                 ============================================================ -->
            <div class="w-20 h-20 rounded-full bg-emerald-500/10 flex items-center justify-center mx-auto mb-6">
                <svg class="w-10 h-10 text-emerald-400" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
            <h1 class="font-display font-bold text-2xl sm:text-3xl text-white mb-3">Paiement confirmé !</h1>
            <p class="text-text-muted mb-6">Ton paiement Mobile Money a bien été reçu.</p>

            <?php if (!empty($book)): ?>
                <p class="text-text-muted mb-2">Tu as acheté :</p>
                <p class="text-white font-semibold text-lg mb-1"><?= e($book->titre) ?></p>
                <p class="text-text-dim text-sm mb-6">par <?= e($book->author_display ?? '') ?></p>
                <div class="flex flex-col sm:flex-row gap-3 justify-center">
                    <a href="/lire/<?= e($book->slug) ?>" class="btn-primary">Lire maintenant</a>
                    <a href="/mon-compte" class="btn-secondary">Ma bibliothèque</a>
                </div>
            <?php elseif (!empty($subscriptionInfo)): ?>
                <?php if (!empty($subscriptionInfo['plan'])): ?>
                    <p class="text-accent font-semibold mb-2"><?= e($subscriptionInfo['plan']) ?></p>
                <?php endif; ?>
                <p class="text-text-muted mb-6">Ton abonnement est actif. Tu as maintenant accès à tout le catalogue en illimité.</p>
                <div class="flex flex-col sm:flex-row gap-3 justify-center">
                    <a href="/catalogue" class="btn-primary">Parcourir le catalogue</a>
                    <a href="/mon-compte" class="btn-secondary">Mon compte</a>
                </div>
            <?php else: ?>
                <div class="flex flex-col sm:flex-row gap-3 justify-center">
                    <a href="/mon-compte" class="btn-primary">Mon compte</a>
                    <a href="/catalogue" class="btn-secondary">Parcourir le catalogue</a>
                </div>
            <?php endif; ?>

            <p class="text-text-dim text-xs mt-8">Une confirmation t'a été envoyée par SMS et par email.</p>

        <?php elseif ($isFailed): ?>
            <!-- ============================================================
                 CAS 3 — Paiement non abouti
                 ============================================================ -->
            <div class="w-20 h-20 rounded-full bg-red-500/10 flex items-center justify-center mx-auto mb-6">
                <svg class="w-10 h-10 text-red-400" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9.75 9.75l4.5 4.5m0-4.5l-4.5 4.5M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
            <h1 class="font-display font-bold text-2xl sm:text-3xl text-white mb-3">Paiement non abouti</h1>
            <p class="text-text-muted mb-6">Ton paiement n'a pas pu être traité. Aucun montant n'a été débité de ton compte Mobile Money.</p>

            <div class="flex flex-col sm:flex-row gap-3 justify-center">
                <?php if (!empty($book)): ?>
                    <a href="/achat/livre/<?= (int) $book->id ?>" class="btn-primary">Réessayer</a>
                <?php elseif (!empty($subscriptionInfo)): ?>
                    <a href="/abonnement" class="btn-primary">Réessayer</a>
                <?php else: ?>
                    <a href="javascript:history.back()" class="btn-primary">Réessayer</a>
                <?php endif; ?>
                <a href="mailto:support@leseditionsvariable.com" class="btn-secondary">Contacter le support</a>
            </div>

        <?php else: ?>
            <!-- ============================================================
                 CAS 2 — Paiement en cours de confirmation
                 ============================================================ -->
            <div class="w-20 h-20 rounded-full bg-accent/10 flex items-center justify-center mx-auto mb-6">
                <svg class="w-10 h-10 text-accent" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
            <h1 class="font-display font-bold text-2xl sm:text-3xl text-white mb-3">Paiement en cours de confirmation</h1>
            <p class="text-text-muted mb-3">Money Fusion valide ton paiement. Cela peut prendre quelques minutes.</p>
            <p class="text-text-muted mb-6">Tu recevras une confirmation par SMS et par email dès que c'est fait.</p>

            <div class="flex flex-col sm:flex-row gap-3 justify-center mb-6">
                <a href="<?= e($_SERVER['REQUEST_URI']) ?>" class="btn-primary">Vérifier maintenant</a>
                <a href="/mon-compte" class="btn-secondary">Aller à ma bibliothèque</a>
            </div>

            <p class="text-text-dim text-xs">Ne paie pas à nouveau si tu ne reçois rien immédiatement — ton paiement peut encore arriver.</p>
        <?php endif; ?>

    </div>
</section>
