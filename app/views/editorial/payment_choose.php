<div class="mb-4"><a href="/auteur/mes-commandes-editoriales/<?= (int) $order->id ?>" class="text-text-dim hover:text-accent text-xs">← Retour à la commande</a></div>

<div class="max-w-[700px]">
    <h1 class="font-display font-bold text-2xl sm:text-3xl text-white mb-2">Paiement de ta commande</h1>

    <div class="bg-surface border border-border rounded-xl p-5 mt-6 mb-8 flex items-center gap-4">
        <div class="w-14 h-14 bg-accent/10 rounded-xl flex items-center justify-center flex-shrink-0">
            <span class="text-2xl">📦</span>
        </div>
        <div class="flex-grow min-w-0">
            <p class="text-white font-semibold truncate"><?= e($order->service_nom) ?></p>
            <p class="text-text-dim text-sm truncate"><?= e($order->titre_projet ?? '') ?></p>
        </div>
        <p class="text-accent font-display font-bold text-xl flex-shrink-0"><?= number_format((float) $order->montant_propose, 2) ?>&nbsp;<?= e($order->devise) ?></p>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
        <a href="/paiement/editorial/<?= (int) $order->id ?>/stripe" class="group block p-6 sm:p-8 border-2 border-border rounded-xl hover:border-accent transition-all">
            <div class="flex items-start justify-between mb-4">
                <svg class="w-10 h-10 text-blue-400" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-3.75 3h15a2.25 2.25 0 002.25-2.25V6.75A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25v10.5A2.25 2.25 0 004.5 19.5z"/></svg>
                <span class="text-[10px] text-text-dim uppercase tracking-wider">International</span>
            </div>
            <h3 class="text-white font-display font-bold text-lg mb-1">Carte bancaire</h3>
            <p class="text-text-dim text-sm mb-3">Visa, Mastercard, Amex via Stripe.</p>
            <span class="text-accent text-sm">Payer avec ma carte →</span>
        </a>

        <a href="/paiement/editorial/<?= (int) $order->id ?>/moneyfusion" class="group block p-6 sm:p-8 border-2 border-border rounded-xl hover:border-accent transition-all">
            <div class="flex items-start justify-between mb-4">
                <svg class="w-10 h-10 text-emerald-400" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 1.5H8.25A2.25 2.25 0 006 3.75v16.5a2.25 2.25 0 002.25 2.25h7.5A2.25 2.25 0 0018 20.25V3.75a2.25 2.25 0 00-2.25-2.25H13.5m-3 0V3h3V1.5m-3 0h3m-3 18.75h3"/></svg>
                <span class="text-[10px] text-text-dim uppercase tracking-wider">Afrique</span>
            </div>
            <h3 class="text-white font-display font-bold text-lg mb-1">Mobile Money</h3>
            <p class="text-text-dim text-sm mb-3">Airtel, Orange, M-Pesa, MTN.</p>
            <span class="text-accent text-sm">Payer avec Mobile Money →</span>
        </a>
    </div>
</div>
