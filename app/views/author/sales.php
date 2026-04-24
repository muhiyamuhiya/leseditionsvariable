<div class="bg-surface border border-border rounded-xl p-5 mb-6">
    <p class="text-text-dim text-xs uppercase tracking-wider">Total des revenus</p>
    <p class="font-display font-bold text-3xl text-accent mt-1"><?= number_format((float)$totalRevenus, 2) ?>&nbsp;$</p>
</div>
<p class="text-text-dim text-sm mb-4"><?= count($ventes) ?> vente(s)</p>
<div class="overflow-x-auto">
<table class="w-full text-sm">
    <thead><tr class="border-b border-border text-text-dim text-xs uppercase tracking-wider text-left">
        <th class="py-3 px-3">Date</th><th class="py-3 px-2">Livre</th><th class="py-3 px-2">Prix</th><th class="py-3 px-2 hidden sm:table-cell">Commission</th><th class="py-3 px-2">Mon revenu</th><th class="py-3 px-2">Statut</th>
    </tr></thead>
    <tbody>
    <?php foreach ($ventes as $v): ?>
        <tr class="border-b border-border/30"><td class="py-3 px-3 text-text-dim text-xs"><?= date('d/m/Y', strtotime($v->date_vente)) ?></td><td class="py-3 px-2 text-white"><?= e($v->book_titre) ?></td><td class="py-3 px-2 text-text-muted"><?= number_format($v->prix_paye_usd, 2) ?>&nbsp;$</td><td class="py-3 px-2 text-text-dim hidden sm:table-cell"><?= number_format($v->commission_variable, 2) ?>&nbsp;$</td><td class="py-3 px-2 text-accent font-medium"><?= number_format($v->revenu_auteur, 2) ?>&nbsp;$</td><td class="py-3 px-2"><span class="text-xs <?= $v->statut === 'payee' ? 'text-emerald-400' : 'text-accent' ?>"><?= ucfirst(str_replace('_',' ',$v->statut)) ?></span></td></tr>
    <?php endforeach; ?>
    <?php if (empty($ventes)): ?><tr><td colspan="6" class="py-8 text-center text-text-dim">Aucune vente pour le moment.</td></tr><?php endif; ?>
    </tbody>
</table>
</div>
