<?php
/** @var object $author */
/** @var array  $balance  total_lifetime, total_paid, total_pending, available, sales_total, pool_available, refused_total */
/** @var array  $byBook   Liste des livres avec nb_ventes, revenu_total, pages_lues */
/** @var float  $seuil */

$canRequest = $balance['available'] >= $seuil;
$method = $author->methode_versement ?: 'mobile_money';
$methodsLabel = [
    'mobile_money' => 'Mobile Money',
    'banque'       => 'Banque / Wise (IBAN)',
    'paypal'       => 'PayPal',
    'stripe'       => 'Stripe',
];
?>
<?php $s = flash('author_success'); if ($s): ?><div class="bg-emerald-500/10 border border-emerald-500/30 text-emerald-400 px-4 py-3 rounded-lg mb-6 text-sm"><?= e($s) ?></div><?php endif; ?>
<?php $err = flash('error'); if ($err): ?><div class="bg-red-500/10 border border-red-500/30 text-red-400 px-4 py-3 rounded-lg mb-6 text-sm"><?= e($err) ?></div><?php endif; ?>

<!-- Cards stats -->
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
    <div class="bg-surface border border-border rounded-lg p-4">
        <p class="text-text-dim text-[10px] uppercase tracking-wider">Solde disponible</p>
        <p class="text-amber-400 text-2xl font-display font-bold mt-1"><?= number_format($balance['available'], 2) ?> $</p>
        <p class="text-text-dim text-[11px] mt-1">À demander dès <?= number_format($seuil, 2) ?> $</p>
    </div>
    <div class="bg-surface border border-border rounded-lg p-4">
        <p class="text-text-dim text-[10px] uppercase tracking-wider">En cours</p>
        <p class="text-white text-2xl font-display font-bold mt-1"><?= number_format($balance['total_pending'], 2) ?> $</p>
        <p class="text-text-dim text-[11px] mt-1">Demandes en traitement</p>
    </div>
    <div class="bg-surface border border-border rounded-lg p-4">
        <p class="text-text-dim text-[10px] uppercase tracking-wider">Déjà versé</p>
        <p class="text-emerald-400 text-2xl font-display font-bold mt-1"><?= number_format($balance['total_paid'], 2) ?> $</p>
        <p class="text-text-dim text-[11px] mt-1">Lifetime</p>
    </div>
    <div class="bg-surface border border-border rounded-lg p-4">
        <p class="text-text-dim text-[10px] uppercase tracking-wider">Total généré</p>
        <p class="text-white text-2xl font-display font-bold mt-1"><?= number_format($balance['total_lifetime'], 2) ?> $</p>
        <p class="text-text-dim text-[11px] mt-1">Lifetime brut</p>
    </div>
</div>

<!-- Détail répartition -->
<div class="bg-surface border border-border rounded-lg p-5 mb-6">
    <h2 class="text-white text-sm font-semibold mb-3">D'où vient ton solde</h2>
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
        <div>
            <p class="text-text-dim text-xs">Ventes unitaires (lifetime)</p>
            <p class="text-white font-medium mt-0.5"><?= number_format($balance['sales_total'], 2) ?> $</p>
            <p class="text-text-dim text-[11px] mt-1">70% du prix de chaque vente — la plateforme garde 30%.</p>
        </div>
        <div>
            <p class="text-text-dim text-xs">Pool d'abonnements (en attente)</p>
            <p class="text-white font-medium mt-0.5"><?= number_format($balance['pool_available'], 2) ?> $</p>
            <p class="text-text-dim text-[11px] mt-1">Ta part au prorata des pages lues sur tes livres ce mois-ci.</p>
        </div>
    </div>
    <?php if ($balance['refused_total'] > 0): ?>
        <p class="text-red-400 text-xs mt-3">⚠ Demandes refusées (lifetime) : <?= number_format($balance['refused_total'], 2) ?> $ — vérifie tes coords de paiement.</p>
    <?php endif; ?>
</div>

<!-- CTA demande -->
<div class="bg-surface border border-border rounded-lg p-5 mb-6">
    <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-4">
        <div class="min-w-0">
            <h2 class="text-white text-base font-display font-semibold mb-1">Demander un versement</h2>
            <?php if ($canRequest): ?>
                <p class="text-text-muted text-sm">Tu peux demander un versement de <strong class="text-amber-400"><?= number_format($balance['available'], 2) ?> $</strong> dès maintenant.</p>
            <?php else: ?>
                <p class="text-text-muted text-sm">Tu pourras demander ton premier versement dès que ton solde atteindra <strong><?= number_format($seuil, 2) ?> $</strong>. Encore <?= number_format(max(0, $seuil - $balance['available']), 2) ?> $ à générer.</p>
            <?php endif; ?>
        </div>
        <?php if ($canRequest): ?>
            <button type="button" id="btn-open-payout-modal" class="btn-primary text-sm shrink-0">Demander un versement →</button>
        <?php else: ?>
            <button type="button" disabled class="btn-secondary text-sm opacity-50 cursor-not-allowed shrink-0">Solde insuffisant</button>
        <?php endif; ?>
    </div>
</div>

<!-- Modal demande -->
<?php if ($canRequest): ?>
<div id="payout-modal" class="fixed inset-0 bg-black/70 z-50 hidden items-center justify-center p-4">
    <div class="bg-surface border border-border rounded-xl max-w-md w-full">
        <div class="px-5 py-4 border-b border-border flex items-center justify-between">
            <h3 class="text-white font-display text-lg font-semibold">Demande de versement</h3>
            <button type="button" id="btn-close-payout-modal" class="text-text-dim hover:text-white text-xl leading-none">&times;</button>
        </div>
        <form method="POST" action="/auteur/versements/demander" class="p-5 space-y-4">
            <?= csrf_field() ?>
            <div class="bg-bg border border-border rounded p-3 text-sm">
                <p class="text-text-dim text-[10px] uppercase tracking-wider">Montant demandé</p>
                <p class="text-amber-400 text-2xl font-display font-bold mt-1"><?= number_format($balance['available'], 2) ?> $</p>
            </div>
            <div>
                <label class="block text-xs text-text-dim uppercase tracking-wider mb-1">Méthode de paiement</label>
                <select name="methode_versement" class="w-full bg-surface-2 border border-border rounded px-3 py-2 text-sm text-white outline-none focus:border-accent">
                    <?php foreach ($methodsLabel as $key => $label): ?>
                        <option value="<?= e($key) ?>" <?= $method === $key ? 'selected' : '' ?>><?= e($label) ?></option>
                    <?php endforeach; ?>
                </select>
                <p class="text-text-dim text-[11px] mt-1">Les coordonnées utilisées sont celles de <a href="/auteur/profil" class="text-accent hover:underline">ton profil</a>. Vérifie-les avant.</p>
            </div>
            <p class="text-text-muted text-xs">Une fois envoyée, la demande est traitée sous quelques jours ouvrés. Tu seras notifié par email à chaque étape.</p>
            <div class="flex gap-2 pt-2">
                <button type="submit" class="btn-primary text-sm">Confirmer la demande</button>
                <button type="button" id="btn-cancel-payout-modal" class="btn-secondary text-sm">Annuler</button>
            </div>
        </form>
    </div>
</div>
<script>
(function () {
    const modal = document.getElementById('payout-modal');
    const open  = document.getElementById('btn-open-payout-modal');
    const close = document.getElementById('btn-close-payout-modal');
    const cancel = document.getElementById('btn-cancel-payout-modal');
    function show(){ modal.classList.remove('hidden'); modal.classList.add('flex'); }
    function hide(){ modal.classList.add('hidden'); modal.classList.remove('flex'); }
    open.addEventListener('click', show);
    close.addEventListener('click', hide);
    cancel.addEventListener('click', hide);
    modal.addEventListener('click', e => { if (e.target === modal) hide(); });
})();
</script>
<?php endif; ?>

<!-- Détail par livre -->
<div class="bg-surface border border-border rounded-lg overflow-hidden">
    <div class="px-5 py-4 border-b border-border">
        <h2 class="text-white font-display font-semibold text-base">Détail par livre</h2>
    </div>
    <?php if (empty($byBook)): ?>
        <p class="text-text-muted text-sm p-5">Aucun livre publié pour l'instant.</p>
    <?php else: ?>
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-surface-2 border-b border-border text-text-dim text-[11px] uppercase tracking-wider">
                    <th class="text-left px-4 py-2.5">Livre</th>
                    <th class="text-right px-4 py-2.5">Ventes</th>
                    <th class="text-right px-4 py-2.5">Revenu (USD)</th>
                    <th class="text-right px-4 py-2.5 hidden sm:table-cell">Pages lues</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($byBook as $b): ?>
                    <tr class="border-b border-border/30 last:border-0">
                        <td class="px-4 py-2"><a href="/auteur/livres/<?= e($b->slug) ?>/preview" class="text-white hover:text-accent text-sm"><?= e($b->titre) ?></a></td>
                        <td class="px-4 py-2 text-right text-text-muted"><?= (int) $b->nb_ventes ?></td>
                        <td class="px-4 py-2 text-right text-amber-400 font-medium"><?= number_format((float) $b->revenu_total, 2) ?> $</td>
                        <td class="px-4 py-2 text-right text-text-muted hidden sm:table-cell"><?= number_format((int) $b->pages_lues) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>

<p class="text-text-dim text-xs mt-4">
    <a href="/auteurs/comment-ca-marche" class="hover:text-accent">📖 Comment ça marche : 70/30, pool, seuil</a>
</p>
