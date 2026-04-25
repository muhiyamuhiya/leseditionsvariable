<?php $s = flash('author_success'); if ($s): ?><div class="bg-emerald-500/10 border border-emerald-500/30 text-emerald-400 px-4 py-3 rounded-lg mb-6 text-sm"><?= e($s) ?></div><?php endif; ?>

<div class="flex items-center justify-between mb-6">
    <p class="text-text-dim text-sm"><?= count($livres) ?> livre(s)</p>
    <a href="/auteur/livres/nouveau" class="btn-primary text-sm">+ Ajouter un livre</a>
</div>

<div class="overflow-x-auto">
<table class="w-full text-sm">
    <thead><tr class="border-b border-border text-text-dim text-xs uppercase tracking-wider text-left">
        <th class="py-3 px-3">Livre</th><th class="py-3 px-2 hidden sm:table-cell">Catégorie</th><th class="py-3 px-2">Prix</th><th class="py-3 px-2">Statut</th><th class="py-3 px-2 hidden md:table-cell">Ventes</th><th class="py-3 px-2">Actions</th>
    </tr></thead>
    <tbody>
    <?php foreach ($livres as $l): ?>
        <tr class="border-b border-border/30 hover:bg-surface-2/50">
            <td class="py-3 px-3"><div class="flex items-center gap-3"><div class="w-8 h-12 bg-gradient-to-br <?= book_cover_gradient($l->id) ?> rounded flex-shrink-0"></div><p class="text-white font-medium truncate max-w-[200px]"><?= e($l->titre) ?></p></div></td>
            <td class="py-3 px-2 text-text-muted hidden sm:table-cell"><?= e($l->cat_nom ?? '-') ?></td>
            <td class="py-3 px-2 text-accent"><?= number_format($l->prix_unitaire_usd, 2) ?>&nbsp;$</td>
            <td class="py-3 px-2"><?php $bc = ['publie'=>'bg-emerald-500/20 text-emerald-400','en_revue'=>'bg-accent/20 text-accent','brouillon'=>'bg-text-dim/20 text-text-dim','retire'=>'bg-red-500/20 text-red-400']; ?><span class="text-[11px] font-medium px-2 py-1 rounded <?= $bc[$l->statut] ?? '' ?>"><?= ucfirst(str_replace('_',' ',$l->statut)) ?></span></td>
            <td class="py-3 px-2 text-text-muted hidden md:table-cell"><?= $l->total_ventes ?></td>
            <td class="py-3 px-2">
                <a href="/auteur/livres/<?= $l->id ?>/editer" class="text-text-muted hover:text-accent text-xs">Éditer</a>
                <?php if (!empty($l->fichier_complet_path)): ?><a href="/lire/<?= e($l->slug) ?>" target="_blank" class="text-accent hover:text-accent-hover text-xs ml-2">Lire</a><?php endif; ?>
                <?php if ($l->statut === 'publie'): ?><a href="/livre/<?= e($l->slug) ?>" target="_blank" class="text-text-dim hover:text-accent text-xs ml-2">Voir</a><?php endif; ?>
            </td>
        </tr>
    <?php endforeach; ?>
    <?php if (empty($livres)): ?><tr><td colspan="6" class="py-8 text-center text-text-dim">Aucun livre. <a href="/auteur/livres/nouveau" class="text-accent">Ajouter ton premier livre</a></td></tr><?php endif; ?>
    </tbody>
</table>
</div>
