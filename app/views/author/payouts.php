<?php
/** @var array $versements  Historique des demandes (statut IN requested/en_cours/verse/refuse/annule/echec) */

$statusLabels = [
    'requested' => ['label' => 'En attente',   'cls' => 'text-amber-400'],
    'en_cours'  => ['label' => 'En traitement', 'cls' => 'text-amber-400'],
    'verse'     => ['label' => 'Versé',         'cls' => 'text-emerald-400'],
    'refuse'    => ['label' => 'Refusé',        'cls' => 'text-red-400'],
    'annule'    => ['label' => 'Annulé',        'cls' => 'text-text-dim'],
    'echec'     => ['label' => 'Échec',         'cls' => 'text-red-400'],
];
?>
<?php $s = flash('author_success'); if ($s): ?><div class="bg-emerald-500/10 border border-emerald-500/30 text-emerald-400 px-4 py-3 rounded-lg mb-6 text-sm"><?= e($s) ?></div><?php endif; ?>
<?php $err = flash('error'); if ($err): ?><div class="bg-red-500/10 border border-red-500/30 text-red-400 px-4 py-3 rounded-lg mb-6 text-sm"><?= e($err) ?></div><?php endif; ?>

<div class="flex items-center justify-between gap-3 mb-6 flex-wrap">
    <p class="text-text-dim text-sm"><?= count($versements) ?> demande(s)</p>
    <a href="/auteur/revenus" class="btn-primary text-sm">+ Demander un versement</a>
</div>

<?php if (empty($versements)): ?>
    <div class="bg-surface border border-border rounded-lg p-8 text-center">
        <p class="text-text-muted">Aucune demande de versement pour l'instant.</p>
        <p class="text-text-dim text-xs mt-2">Tu peux en demander un dès que ton solde dépasse 10 $.</p>
    </div>
<?php else: ?>
<div class="bg-surface border border-border rounded-lg overflow-hidden">
<table class="w-full text-sm">
    <thead>
        <tr class="bg-surface-2 border-b border-border text-text-dim text-[11px] uppercase tracking-wider text-left">
            <th class="px-4 py-2.5">Demandée le</th>
            <th class="px-4 py-2.5">Montant</th>
            <th class="px-4 py-2.5 hidden sm:table-cell">Méthode</th>
            <th class="px-4 py-2.5">Statut</th>
            <th class="px-4 py-2.5 hidden md:table-cell">Versée le</th>
            <th class="px-4 py-2.5 hidden lg:table-cell">Référence</th>
        </tr>
    </thead>
    <tbody>
    <?php foreach ($versements as $v): $st = $statusLabels[$v->statut] ?? ['label' => $v->statut, 'cls' => 'text-text-dim']; ?>
        <tr class="border-b border-border/30 last:border-0 hover:bg-surface-2/30">
            <td class="px-4 py-2 text-text-muted text-xs">
                <?= $v->requested_at ? date('d/m/Y', strtotime((string) $v->requested_at)) : date('d/m/Y', strtotime((string) $v->created_at)) ?>
            </td>
            <td class="px-4 py-2 text-amber-400 font-medium"><?= number_format((float) $v->total_a_verser, 2) ?> $</td>
            <td class="px-4 py-2 text-text-muted hidden sm:table-cell"><?= e(str_replace('_', ' ', (string) ($v->requested_method ?? $v->methode_versement ?? '—'))) ?></td>
            <td class="px-4 py-2"><span class="text-xs font-medium <?= $st['cls'] ?>"><?= e($st['label']) ?></span></td>
            <td class="px-4 py-2 text-text-muted text-xs hidden md:table-cell"><?= $v->date_versement ? date('d/m/Y', strtotime((string) $v->date_versement)) : '—' ?></td>
            <td class="px-4 py-2 text-text-dim text-xs hidden lg:table-cell font-mono"><?= e($v->reference_versement ?: '—') ?></td>
        </tr>
        <?php if ($v->statut === 'refuse' && !empty($v->rejection_reason)): ?>
        <tr class="border-b border-border/30">
            <td colspan="6" class="px-4 py-2 bg-red-500/5">
                <p class="text-red-400 text-xs"><strong>Motif du refus :</strong> <?= e($v->rejection_reason) ?></p>
            </td>
        </tr>
        <?php endif; ?>
    <?php endforeach; ?>
    </tbody>
</table>
</div>
<?php endif; ?>
