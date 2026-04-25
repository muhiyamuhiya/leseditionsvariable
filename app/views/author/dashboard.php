<?php $s = flash('author_success') ?: flash('success'); if ($s): ?><div class="bg-emerald-500/10 border border-emerald-500/30 text-emerald-400 px-4 py-3 rounded-lg mb-6 text-sm"><?= e($s) ?></div><?php endif; ?>

<p class="text-text-muted mb-6">Bienvenue, <?= e(App\Lib\Auth::user()->prenom ?? '') ?>.</p>

<!-- Bandeau alertes -->
<?php if (!empty($alertes)): ?>
<div class="bg-gradient-to-r from-amber-500/15 to-purple-500/15 border border-amber-500/30 rounded-xl p-4 sm:p-5 mb-6">
    <h2 class="font-display font-bold text-lg sm:text-xl text-white mb-4 flex items-center gap-2">
        <span aria-hidden="true">🔔</span>
        À ton attention
        <span class="bg-rose-500 text-white text-xs px-2 py-0.5 rounded-full"><?= count($alertes) ?></span>
    </h2>
    <div class="space-y-2">
        <?php foreach ($alertes as $a): ?>
            <a href="<?= e($a['url']) ?>" class="flex items-center justify-between bg-white/5 hover:bg-white/10 rounded-lg p-3 transition-colors">
                <div class="flex items-center gap-3 min-w-0">
                    <span class="text-2xl flex-shrink-0" aria-hidden="true"><?= $a['icon'] ?></span>
                    <div class="min-w-0">
                        <p class="font-medium text-white text-sm truncate"><?= e($a['title']) ?></p>
                        <p class="text-text-dim text-xs truncate"><?= e($a['message']) ?></p>
                    </div>
                </div>
                <span class="text-accent text-lg ml-2 flex-shrink-0">→</span>
            </a>
        <?php endforeach; ?>
    </div>
</div>
<?php endif; ?>

<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
    <div class="bg-surface border border-border rounded-xl p-5"><p class="text-text-dim text-xs uppercase tracking-wider">Livres publiés</p><p class="font-display font-bold text-2xl text-accent mt-1"><?= $stats['livres'] ?></p></div>
    <div class="bg-surface border border-border rounded-xl p-5"><p class="text-text-dim text-xs uppercase tracking-wider">Ventes ce mois</p><p class="font-display font-bold text-2xl text-white mt-1"><?= $stats['ventes_mois'] ?></p></div>
    <div class="bg-surface border border-border rounded-xl p-5"><p class="text-text-dim text-xs uppercase tracking-wider">Revenus ce mois</p><p class="font-display font-bold text-2xl text-accent mt-1"><?= number_format((float)$stats['revenus_mois'], 2) ?>&nbsp;$</p></div>
    <div class="bg-surface border border-border rounded-xl p-5"><p class="text-text-dim text-xs uppercase tracking-wider">Pages lues</p><p class="font-display font-bold text-2xl text-white mt-1"><?= number_format((int)$stats['pages_lues']) ?></p></div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
    <div class="bg-surface border border-border rounded-xl p-5">
        <h3 class="font-display font-semibold text-sm text-white mb-4">Mes derniers livres</h3>
        <?php if (empty($dernLivres)): ?>
            <p class="text-text-dim text-sm">Aucun livre pour le moment.</p>
        <?php else: ?>
            <?php foreach ($dernLivres as $i => $l): ?>
            <div class="flex items-center gap-3 py-2 <?= $i > 0 ? 'border-t border-border/50' : '' ?>">
                <div class="w-8 h-12 bg-gradient-to-br <?= book_cover_gradient($l->id) ?> rounded flex-shrink-0"></div>
                <div class="flex-grow min-w-0">
                    <p class="text-white text-sm truncate"><?= e($l->titre) ?></p>
                    <p class="text-text-dim text-xs"><?= e($l->cat_nom ?? '-') ?></p>
                </div>
                <?php $bc = ['publie'=>'text-emerald-400','en_revue'=>'text-accent','brouillon'=>'text-text-dim','retire'=>'text-red-400']; ?>
                <span class="text-[11px] font-medium <?= $bc[$l->statut] ?? 'text-text-dim' ?>"><?= ucfirst(str_replace('_',' ',$l->statut)) ?></span>
            </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <div class="bg-surface border border-border rounded-xl p-5 flex flex-col items-center justify-center text-center">
        <svg class="w-12 h-12 text-accent/30 mb-4" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
        <p class="text-text-muted text-sm mb-4">Prêt à publier ton prochain livre ?</p>
        <a href="/auteur/livres/nouveau" class="btn-primary text-sm">Ajouter un livre</a>
    </div>
</div>
