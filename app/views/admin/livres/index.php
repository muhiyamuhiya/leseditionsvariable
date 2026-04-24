<?php $s = flash('admin_success'); if ($s): ?><div class="bg-emerald-500/10 border border-emerald-500/30 text-emerald-400 px-4 py-3 rounded-lg mb-6 text-sm"><?= e($s) ?></div><?php endif; ?>

<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
    <p class="text-text-dim text-sm"><?= count($livres) ?> livre(s)</p>
    <a href="/admin/livres/nouveau" class="btn-primary text-sm">+ Ajouter un livre</a>
</div>

<!-- Filtres -->
<form method="GET" class="flex flex-wrap gap-3 mb-6">
    <select name="statut" onchange="this.form.submit()" class="bg-surface border border-border rounded px-3 py-2 text-sm text-white outline-none">
        <option value="">Tous les statuts</option>
        <?php foreach (['brouillon','en_revue','publie','retire'] as $st): ?>
            <option value="<?= $st ?>" <?= $filtreStatut === $st ? 'selected' : '' ?>><?= ucfirst(str_replace('_', ' ', $st)) ?></option>
        <?php endforeach; ?>
    </select>
    <input type="text" name="q" value="<?= e($filtreQ ?? '') ?>" placeholder="Rechercher..." class="bg-surface border border-border rounded px-3 py-2 text-sm text-white outline-none focus:border-accent placeholder:text-text-dim w-48">
</form>

<!-- Tableau -->
<div class="overflow-x-auto">
<table class="w-full text-sm">
    <thead><tr class="border-b border-border text-text-dim text-xs uppercase tracking-wider text-left">
        <th class="py-3 px-3">Livre</th>
        <th class="py-3 px-2 hidden sm:table-cell">Catégorie</th>
        <th class="py-3 px-2">Prix</th>
        <th class="py-3 px-2">Statut</th>
        <th class="py-3 px-2 hidden md:table-cell">Ventes</th>
        <th class="py-3 px-2">Actions</th>
    </tr></thead>
    <tbody>
    <?php foreach ($livres as $l): ?>
        <tr class="border-b border-border/30 hover:bg-surface-2/50">
            <td class="py-3 px-3">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-12 bg-gradient-to-br <?= book_cover_gradient($l->id) ?> rounded flex-shrink-0"></div>
                    <div class="min-w-0">
                        <p class="text-white font-medium truncate max-w-[200px]"><?= e($l->titre) ?></p>
                        <p class="text-text-dim text-xs"><?= e($l->author_name) ?></p>
                    </div>
                </div>
            </td>
            <td class="py-3 px-2 text-text-muted hidden sm:table-cell"><?= e($l->cat_nom ?? '-') ?></td>
            <td class="py-3 px-2 text-accent font-medium"><?= number_format($l->prix_unitaire_usd, 2) ?>&nbsp;$</td>
            <td class="py-3 px-2">
                <?php
                $badgeColors = ['publie'=>'bg-emerald-500/20 text-emerald-400','brouillon'=>'bg-text-dim/20 text-text-dim','en_revue'=>'bg-accent/20 text-accent','retire'=>'bg-red-500/20 text-red-400'];
                $bc = $badgeColors[$l->statut] ?? 'bg-text-dim/20 text-text-dim';
                ?>
                <span class="text-[11px] font-medium px-2 py-1 rounded <?= $bc ?>"><?= ucfirst(str_replace('_',' ',$l->statut)) ?></span>
            </td>
            <td class="py-3 px-2 hidden md:table-cell text-text-muted"><?= $l->total_ventes ?></td>
            <td class="py-3 px-2">
                <div class="flex items-center gap-2">
                    <a href="/admin/livres/<?= $l->id ?>/apercu" class="text-accent hover:text-accent-hover text-xs font-medium"><?= in_array($l->statut, ['brouillon','en_revue']) ? 'Examiner' : 'Aperçu' ?></a>
                    <a href="/admin/livres/<?= $l->id ?>/editer" class="text-text-muted hover:text-accent text-xs">Éditer</a>
                    <form method="POST" action="/admin/livres/<?= $l->id ?>/supprimer" onsubmit="return confirm('Supprimer ce livre ?')" class="inline">
                        <?= csrf_field() ?>
                        <button class="text-text-dim hover:text-red-400 text-xs">Suppr.</button>
                    </form>
                </div>
            </td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>
</div>
