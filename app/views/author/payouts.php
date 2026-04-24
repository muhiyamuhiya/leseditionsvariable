<p class="text-text-dim text-sm mb-6"><?= count($versements) ?> versement(s)</p>
<div class="overflow-x-auto">
<table class="w-full text-sm">
    <thead><tr class="border-b border-border text-text-dim text-xs uppercase tracking-wider text-left">
        <th class="py-3 px-3">Période</th><th class="py-3 px-2">Ventes</th><th class="py-3 px-2">Pool abo</th><th class="py-3 px-2">Total</th><th class="py-3 px-2">Statut</th><th class="py-3 px-2">Date</th>
    </tr></thead>
    <tbody>
    <?php foreach ($versements as $v): ?>
        <tr class="border-b border-border/30">
            <td class="py-3 px-3 text-text-muted text-xs"><?= date('d/m/Y', strtotime($v->periode_debut)) ?> → <?= date('d/m/Y', strtotime($v->periode_fin)) ?></td>
            <td class="py-3 px-2 text-text-muted"><?= number_format($v->revenus_ventes_unitaires, 2) ?>&nbsp;$</td>
            <td class="py-3 px-2 text-text-muted"><?= number_format($v->revenus_pool_abonnement, 2) ?>&nbsp;$</td>
            <td class="py-3 px-2 text-accent font-medium"><?= number_format($v->total_a_verser, 2) ?>&nbsp;$</td>
            <td class="py-3 px-2"><?php $pc = ['verse'=>'text-emerald-400','a_verser'=>'text-accent','calcule'=>'text-text-dim']; ?><span class="text-xs <?= $pc[$v->statut] ?? 'text-text-dim' ?>"><?= ucfirst(str_replace('_',' ',$v->statut)) ?></span></td>
            <td class="py-3 px-2 text-text-dim text-xs"><?= $v->date_versement ? date('d/m/Y', strtotime($v->date_versement)) : '-' ?></td>
        </tr>
    <?php endforeach; ?>
    <?php if (empty($versements)): ?><tr><td colspan="6" class="py-8 text-center text-text-dim">Aucun versement pour le moment. Les versements sont effectués trimestriellement.</td></tr><?php endif; ?>
    </tbody>
</table>
</div>
