<?php $s = flash('admin_success'); if ($s): ?><div class="bg-emerald-500/10 border border-emerald-500/30 text-emerald-400 px-4 py-3 rounded-lg mb-6 text-sm"><?= e($s) ?></div><?php endif; ?>
<p class="text-text-dim text-sm mb-6"><?= count($versements) ?> versement(s)</p>
<div class="overflow-x-auto">
<table class="w-full text-sm">
    <thead><tr class="border-b border-border text-text-dim text-xs uppercase tracking-wider text-left">
        <th class="py-3 px-3">Auteur</th><th class="py-3 px-2">Période</th><th class="py-3 px-2">Montant</th><th class="py-3 px-2">Méthode</th><th class="py-3 px-2">Statut</th><th class="py-3 px-2">Actions</th>
    </tr></thead>
    <tbody>
    <?php foreach ($versements as $v): ?>
        <tr class="border-b border-border/30 hover:bg-surface-2/50">
            <td class="py-3 px-3 text-white"><?= e($v->author_name) ?></td>
            <td class="py-3 px-2 text-text-dim text-xs"><?= date('d/m/Y', strtotime($v->periode_debut)) ?> → <?= date('d/m/Y', strtotime($v->periode_fin)) ?></td>
            <td class="py-3 px-2 text-accent font-medium"><?= number_format($v->total_a_verser, 2) ?>&nbsp;$</td>
            <td class="py-3 px-2 text-text-muted"><?= e($v->methode_versement ?? '-') ?></td>
            <td class="py-3 px-2">
                <?php $pc = ['verse'=>'text-emerald-400','a_verser'=>'text-accent','calcule'=>'text-text-dim']; ?>
                <span class="text-xs font-medium <?= $pc[$v->statut] ?? 'text-text-dim' ?>"><?= ucfirst(str_replace('_',' ',$v->statut)) ?></span>
            </td>
            <td class="py-3 px-2">
                <?php if ($v->statut !== 'verse'): ?>
                <form method="POST" action="/admin/versements/<?= $v->id ?>/payer" onsubmit="this.querySelector('[name=reference]').value=prompt('Référence du versement :') || ''; return true;">
                    <?= csrf_field() ?><input type="hidden" name="reference" value="">
                    <button class="text-accent hover:text-accent-hover text-xs font-medium">Marquer payé</button>
                </form>
                <?php else: ?>
                    <span class="text-text-dim text-xs"><?= e($v->reference_versement ?? '') ?></span>
                <?php endif; ?>
            </td>
        </tr>
    <?php endforeach; ?>
    <?php if (empty($versements)): ?><tr><td colspan="6" class="py-8 text-center text-text-dim">Aucun versement.</td></tr><?php endif; ?>
    </tbody>
</table>
</div>
