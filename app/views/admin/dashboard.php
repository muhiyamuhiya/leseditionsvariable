<?php $s = flash('admin_success'); if ($s): ?><div class="bg-emerald-500/10 border border-emerald-500/30 text-emerald-400 px-4 py-3 rounded-lg mb-6 text-sm"><?= e($s) ?></div><?php endif; ?>

<!-- Stats -->
<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
    <div class="bg-surface border border-border rounded-xl p-5">
        <p class="text-text-dim text-xs uppercase tracking-wider">CA du mois</p>
        <p class="font-display font-bold text-2xl text-accent mt-1"><?= number_format((float)$stats['ca_mois'], 2) ?>&nbsp;$</p>
    </div>
    <div class="bg-surface border border-border rounded-xl p-5">
        <p class="text-text-dim text-xs uppercase tracking-wider">Abonnés actifs</p>
        <p class="font-display font-bold text-2xl text-white mt-1"><?= $stats['abonnes'] ?></p>
    </div>
    <div class="bg-surface border border-border rounded-xl p-5">
        <p class="text-text-dim text-xs uppercase tracking-wider">Livres publiés</p>
        <p class="font-display font-bold text-2xl text-white mt-1"><?= $stats['livres'] ?></p>
    </div>
    <div class="bg-surface border border-border rounded-xl p-5">
        <p class="text-text-dim text-xs uppercase tracking-wider">Auteurs validés</p>
        <p class="font-display font-bold text-2xl text-white mt-1"><?= $stats['auteurs'] ?></p>
    </div>
</div>

<!-- Alertes -->
<?php if ($stats['candidatures'] > 0 || $stats['livres_revue'] > 0): ?>
<div class="bg-accent/5 border border-accent/20 rounded-xl p-5 mb-8 space-y-2">
    <?php if ($stats['candidatures'] > 0): ?>
        <a href="/admin/candidatures" class="flex items-center gap-2 text-accent text-sm hover:underline">
            <span class="w-2 h-2 bg-accent rounded-full"></span>
            <?= $stats['candidatures'] ?> candidature(s) auteur en attente
        </a>
    <?php endif; ?>
    <?php if ($stats['livres_revue'] > 0): ?>
        <a href="/admin/livres?statut=en_revue" class="flex items-center gap-2 text-accent text-sm hover:underline">
            <span class="w-2 h-2 bg-accent rounded-full"></span>
            <?= $stats['livres_revue'] ?> livre(s) en revue
        </a>
    <?php endif; ?>
</div>
<?php endif; ?>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
    <!-- Top livres -->
    <div class="bg-surface border border-border rounded-xl p-5">
        <h3 class="font-display font-semibold text-sm text-white mb-4">Top livres</h3>
        <?php if (empty($topLivres)): ?>
            <p class="text-text-dim text-sm">Aucune vente pour le moment.</p>
        <?php else: ?>
            <?php foreach ($topLivres as $i => $l): ?>
            <div class="flex items-center gap-3 py-2 <?= $i > 0 ? 'border-t border-border/50' : '' ?>">
                <span class="text-text-dim text-xs w-5"><?= $i + 1 ?>.</span>
                <a href="/livre/<?= e($l->slug) ?>" class="text-sm text-white hover:text-accent transition-colors truncate flex-grow"><?= e($l->titre) ?></a>
                <span class="text-accent text-xs font-semibold"><?= $l->total_ventes ?> ventes</span>
            </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <!-- Actions rapides -->
    <div class="bg-surface border border-border rounded-xl p-5">
        <h3 class="font-display font-semibold text-sm text-white mb-4">Actions rapides</h3>
        <div class="space-y-2">
            <a href="/admin/livres/nouveau" class="flex items-center gap-3 px-3 py-2.5 bg-surface-2 rounded-lg text-sm text-text-muted hover:text-accent transition-colors">
                <svg class="w-4 h-4 text-accent" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
                Ajouter un livre
            </a>
            <a href="/admin/candidatures" class="flex items-center gap-3 px-3 py-2.5 bg-surface-2 rounded-lg text-sm text-text-muted hover:text-accent transition-colors">
                <svg class="w-4 h-4 text-accent" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7.5v3m0 0v3m0-3h3m-3 0h-3"/></svg>
                Voir les candidatures
            </a>
            <a href="/" target="_blank" class="flex items-center gap-3 px-3 py-2.5 bg-surface-2 rounded-lg text-sm text-text-muted hover:text-accent transition-colors">
                <svg class="w-4 h-4 text-accent" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 6H5.25A2.25 2.25 0 003 8.25v10.5A2.25 2.25 0 005.25 21h10.5A2.25 2.25 0 0018 18.75V10.5m-10.5 6L21 3m0 0h-5.25M21 3v5.25"/></svg>
                Voir le site public
            </a>
        </div>
    </div>
</div>
