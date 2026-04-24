<p class="text-text-dim text-sm mb-6"><?= count($abos) ?> abonnement(s)</p>
<div class="overflow-x-auto">
<table class="w-full text-sm">
    <thead><tr class="border-b border-border text-text-dim text-xs uppercase tracking-wider text-left">
        <th class="py-3 px-3">Utilisateur</th><th class="py-3 px-2">Type</th><th class="py-3 px-2">Début</th><th class="py-3 px-2">Fin</th><th class="py-3 px-2">Prix</th><th class="py-3 px-2">Statut</th>
    </tr></thead>
    <tbody>
    <?php foreach ($abos as $a): ?>
        <tr class="border-b border-border/30 hover:bg-surface-2/50">
            <td class="py-3 px-3 text-white"><?= e($a->prenom . ' ' . $a->nom) ?><br><span class="text-text-dim text-xs"><?= e($a->email) ?></span></td>
            <td class="py-3 px-2 text-text-muted"><?= e(ucfirst(str_replace('_',' ',$a->type))) ?></td>
            <td class="py-3 px-2 text-text-dim text-xs"><?= date('d/m/Y', strtotime($a->date_debut)) ?></td>
            <td class="py-3 px-2 text-text-dim text-xs"><?= date('d/m/Y', strtotime($a->date_fin)) ?></td>
            <td class="py-3 px-2 text-accent font-medium"><?= number_format($a->prix_paye, 2) ?> <?= e($a->devise) ?></td>
            <td class="py-3 px-2">
                <?php $sc = ['actif'=>'text-emerald-400','annule'=>'text-red-400','expire'=>'text-text-dim','echec_paiement'=>'text-red-400']; ?>
                <span class="text-xs font-medium <?= $sc[$a->statut] ?? 'text-text-dim' ?>"><?= ucfirst(str_replace('_',' ',$a->statut)) ?></span>
            </td>
        </tr>
    <?php endforeach; ?>
    <?php if (empty($abos)): ?><tr><td colspan="6" class="py-8 text-center text-text-dim">Aucun abonnement.</td></tr><?php endif; ?>
    </tbody>
</table>
</div>
