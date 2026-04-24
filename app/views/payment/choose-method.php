<section class="py-10 sm:py-16">
    <div class="max-w-[700px] mx-auto px-4 sm:px-6">
        <h1 class="font-display font-extrabold text-2xl sm:text-3xl text-white mb-2">Choisir ta méthode de paiement</h1>

        <!-- Résumé -->
        <div class="bg-surface border border-border rounded-xl p-5 mt-6 mb-8 flex items-center gap-4">
            <?php if ($type === 'book' && $book): ?>
                <div class="w-16 h-24 bg-gradient-to-br <?= book_cover_gradient($book->id) ?> rounded flex-shrink-0 flex items-center justify-center overflow-hidden">
                    <?php $cv = book_cover_url($book); if ($cv): ?><img src="<?= e($cv) ?>" class="w-full h-full object-cover"><?php else: ?><span class="text-white text-[10px] font-semibold text-center px-1"><?= e($book->titre) ?></span><?php endif; ?>
                </div>
                <div class="flex-grow min-w-0">
                    <p class="text-white font-semibold truncate"><?= e($book->titre) ?></p>
                    <p class="text-text-dim text-sm"><?= e($book->author_display ?? '') ?></p>
                </div>
                <p class="text-accent font-display font-bold text-xl flex-shrink-0"><?= number_format($book->prix_unitaire_usd, 2) ?>&nbsp;$</p>
            <?php elseif ($type === 'subscription' && isset($planData)): ?>
                <div class="w-16 h-16 bg-accent/10 rounded-xl flex items-center justify-center flex-shrink-0">
                    <svg class="w-8 h-8 text-accent" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12z"/></svg>
                </div>
                <div class="flex-grow">
                    <p class="text-white font-semibold"><?= e($planData['label']) ?></p>
                    <p class="text-text-dim text-sm"><?= $planData['duree_jours'] ?> jours d'accès illimité</p>
                </div>
                <p class="text-accent font-display font-bold text-xl flex-shrink-0"><?= $planData['prix'] ?>&nbsp;$</p>
            <?php endif; ?>
        </div>

        <!-- Choix -->
        <?php
        if ($type === 'book' && $book) {
            $stripeUrl = '/achat/livre/' . $book->id . '/stripe';
            $mfUrl = '/achat/livre/' . $book->id . '/moneyfusion';
        } else {
            $stripeUrl = '/abonnement/souscrire/' . ($plan ?? 'mensuel') . '/stripe';
            $mfUrl = '/abonnement/souscrire/' . ($plan ?? 'mensuel') . '/moneyfusion';
        }
        ?>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
            <!-- Stripe -->
            <a href="<?= $stripeUrl ?>" class="group block p-6 sm:p-8 border-2 border-border rounded-xl hover:border-accent transition-all">
                <div class="flex items-start justify-between mb-5">
                    <svg class="w-10 h-10 text-blue-400" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-3.75 3h15a2.25 2.25 0 002.25-2.25V6.75A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25v10.5A2.25 2.25 0 004.5 19.5z"/></svg>
                    <span class="text-[10px] text-text-dim uppercase tracking-wider">International</span>
                </div>
                <h3 class="text-white font-display font-bold text-lg mb-1">Carte bancaire</h3>
                <p class="text-text-dim text-sm mb-4">Visa, Mastercard, American Express. Paiement sécurisé via Stripe.</p>
                <span class="text-accent group-hover:text-accent-hover font-medium text-sm">Payer avec ma carte &rarr;</span>
            </a>

            <!-- Money Fusion -->
            <a href="<?= $mfUrl ?>" class="group block p-6 sm:p-8 border-2 border-border rounded-xl hover:border-accent transition-all">
                <div class="flex items-start justify-between mb-5">
                    <svg class="w-10 h-10 text-emerald-400" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 1.5H8.25A2.25 2.25 0 006 3.75v16.5a2.25 2.25 0 002.25 2.25h7.5A2.25 2.25 0 0018 20.25V3.75a2.25 2.25 0 00-2.25-2.25H13.5m-3 0V3h3V1.5m-3 0h3m-3 18.75h3"/></svg>
                    <span class="text-[10px] text-text-dim uppercase tracking-wider">Afrique</span>
                </div>
                <h3 class="text-white font-display font-bold text-lg mb-1">Mobile Money</h3>
                <p class="text-text-dim text-sm mb-4">Airtel Money, Orange Money, M-Pesa, MTN. RDC, Sénégal, Côte d'Ivoire...</p>
                <span class="text-accent group-hover:text-accent-hover font-medium text-sm">Payer avec Mobile Money &rarr;</span>
            </a>
        </div>

        <p class="text-text-dim text-xs text-center mt-6">Paiement 100% sécurisé. Tes données bancaires ne sont jamais stockées sur nos serveurs.</p>
    </div>
</section>
