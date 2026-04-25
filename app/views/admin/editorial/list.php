<?php
$success = flash('admin_success');
$error = flash('admin_error');
$statutLabels = [
    'en_attente_devis' => ['Devis en cours', 'bg-amber-500/20 text-amber-300'],
    'devis_envoye'     => ['Devis envoyé', 'bg-blue-500/20 text-blue-300'],
    'accepte'          => ['Accepté (à payer)', 'bg-emerald-500/20 text-emerald-300'],
    'en_cours'         => ['En cours', 'bg-purple-500/20 text-purple-300'],
    'livre'            => ['Livré', 'bg-emerald-500/20 text-emerald-400'],
    'annule'           => ['Annulé', 'bg-rose-500/20 text-rose-400'],
    'rembourse'        => ['Remboursé', 'bg-rose-500/20 text-rose-400'],
];
$icons = ['edit'=>'✏️','layout'=>'🧱','image'=>'🎨','message'=>'💬','package'=>'📦','plus'=>'➕'];
$buildUrl = function ($overrides) use ($statut, $q) {
    $params = array_filter([
        'statut' => $statut !== 'tous' ? $statut : null,
        'q' => $q ?: null,
    ], fn($v) => $v !== null && $v !== '');
    foreach ($overrides as $k => $v) {
        if ($v === null || $v === '') unset($params[$k]); else $params[$k] = $v;
    }
    return '/admin/services-editoriaux' . ($params ? '?' . http_build_query($params) : '');
};
?>
<?php if ($success): ?><div class="bg-emerald-500/10 border border-emerald-500/30 text-emerald-400 px-4 py-3 rounded-lg mb-4 text-sm"><?= e($success) ?></div><?php endif; ?>
<?php if ($error): ?><div class="bg-red-500/10 border border-red-500/30 text-red-400 px-4 py-3 rounded-lg mb-4 text-sm"><?= e($error) ?></div><?php endif; ?>

<div class="flex items-center justify-between mb-5 flex-wrap gap-3">
    <h1 class="font-display font-bold text-2xl text-white">Services éditoriaux <span class="text-text-dim text-base">(<?= count($orders) ?>)</span></h1>
    <form action="/admin/services-editoriaux" method="GET" class="flex gap-2">
        <?php if ($statut !== 'tous'): ?><input type="hidden" name="statut" value="<?= e($statut) ?>"><?php endif; ?>
        <input type="text" name="q" value="<?= e($q) ?>" placeholder="Auteur ou titre…" class="bg-surface-2 border border-border rounded px-3 py-2 text-sm text-white outline-none focus:border-accent w-56">
        <button type="submit" class="btn-primary text-sm">Filtrer</button>
    </form>
</div>

<div class="flex flex-wrap items-center gap-2 mb-6 text-xs">
    <?php foreach (['tous'=>'Tous','en_attente_devis'=>'À devis','devis_envoye'=>'Devis envoyé','accepte'=>'À payer','en_cours'=>'En cours','livre'=>'Livré'] as $key => $label): ?>
        <a href="<?= e($buildUrl(['statut' => $key === 'tous' ? null : $key])) ?>"
           class="px-2.5 py-1 rounded-full border <?= ($statut === $key || ($statut === 'tous' && $key === 'tous')) ? 'bg-accent text-black border-accent font-semibold' : 'bg-surface border-border text-text-muted hover:border-accent hover:text-accent' ?> transition-colors">
            <?= e($label) ?>
        </a>
    <?php endforeach; ?>
</div>

<div class="bg-surface border border-border rounded-xl overflow-x-auto">
<table class="w-full text-sm min-w-[700px]">
    <thead><tr class="border-b border-border text-text-dim text-xs uppercase tracking-wider text-left">
        <th class="py-3 px-3">Service / Projet</th><th class="py-3 px-2">Auteur</th><th class="py-3 px-2">Statut</th><th class="py-3 px-2">Montant</th><th class="py-3 px-2">Date</th><th class="py-3 px-2"></th>
    </tr></thead>
    <tbody>
    <?php foreach ($orders as $o): ?>
        <?php [$lab, $cls] = $statutLabels[$o->statut] ?? [$o->statut, 'bg-surface-2 text-text-dim']; ?>
        <tr class="border-b border-border/30 hover:bg-surface-2/50">
            <td class="py-3 px-3">
                <div class="flex items-start gap-2">
                    <span class="text-base flex-shrink-0"><?= $icons[$o->service_icon] ?? '📌' ?></span>
                    <div class="min-w-0">
                        <p class="text-white font-medium truncate"><?= e($o->service_nom) ?></p>
                        <p class="text-text-dim text-xs truncate max-w-[260px]"><?= e($o->titre_projet ?? '—') ?></p>
                    </div>
                </div>
            </td>
            <td class="py-3 px-2 text-text-muted text-xs"><?= e($o->prenom . ' ' . $o->nom) ?></td>
            <td class="py-3 px-2"><span class="text-[10px] font-semibold px-2 py-0.5 rounded <?= $cls ?>"><?= e($lab) ?></span></td>
            <td class="py-3 px-2 text-accent text-xs"><?= !empty($o->montant_propose) ? number_format((float) $o->montant_propose, 2) . ' ' . e($o->devise) : '—' ?></td>
            <td class="py-3 px-2 text-text-dim text-xs"><?= date('d/m/Y', strtotime($o->created_at)) ?></td>
            <td class="py-3 px-2 text-right"><a href="/admin/services-editoriaux/<?= (int) $o->id ?>" class="text-accent hover:text-accent-hover text-xs font-medium">Voir →</a></td>
        </tr>
    <?php endforeach; ?>
    <?php if (empty($orders)): ?>
        <tr><td colspan="6" class="py-10 text-center text-text-dim">Aucune commande.</td></tr>
    <?php endif; ?>
    </tbody>
</table>
</div>
