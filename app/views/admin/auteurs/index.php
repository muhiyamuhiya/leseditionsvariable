<?php $s = flash('admin_success'); if ($s): ?><div class="bg-emerald-500/10 border border-emerald-500/30 text-emerald-400 px-4 py-3 rounded-lg mb-6 text-sm"><?= e($s) ?></div><?php endif; ?>
<p class="text-text-dim text-sm mb-6"><?= count($auteurs) ?> auteur(s)</p>
<div class="overflow-x-auto">
<table class="w-full text-sm">
    <thead><tr class="border-b border-border text-text-dim text-xs uppercase tracking-wider text-left">
        <th class="py-3 px-3">Auteur</th><th class="py-3 px-2">Email</th><th class="py-3 px-2">Pays</th><th class="py-3 px-2">Livres</th><th class="py-3 px-2">Statut</th><th class="py-3 px-2">Actions</th>
    </tr></thead>
    <tbody>
    <?php foreach ($auteurs as $a): ?>
        <tr class="border-b border-border/30 hover:bg-surface-2/50">
            <td class="py-3 px-3 text-white font-medium"><?= e($a->display_name) ?></td>
            <td class="py-3 px-2 text-text-muted"><?= e($a->email) ?></td>
            <td class="py-3 px-2 text-text-muted"><?= e($a->pays_origine ?? '-') ?></td>
            <td class="py-3 px-2 text-text-muted"><?= $a->total_livres_publies ?></td>
            <td class="py-3 px-2">
                <?php $vc = ['valide'=>'text-emerald-400','en_attente'=>'text-accent','refuse'=>'text-red-400','suspendu'=>'text-text-dim']; ?>
                <span class="text-xs font-medium <?= $vc[$a->statut_validation] ?? 'text-text-dim' ?>"><?= ucfirst(str_replace('_',' ',$a->statut_validation)) ?></span>
            </td>
            <td class="py-3 px-2">
                <a href="/admin/auteurs/<?= $a->id ?>/editer" class="text-text-muted hover:text-accent text-xs">Éditer</a>
                <a href="/auteur/<?= e($a->slug) ?>" target="_blank" class="text-text-dim hover:text-accent text-xs ml-2">Profil</a>
            </td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>
</div>
