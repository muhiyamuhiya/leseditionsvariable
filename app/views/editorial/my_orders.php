<?php
$success = flash('success');
$icons = ['edit'=>'✏️','layout'=>'🧱','image'=>'🎨','message'=>'💬','package'=>'📦','plus'=>'➕'];
$statutLabels = [
    'en_attente_devis' => ['Devis en cours', 'bg-amber-500/20 text-amber-300'],
    'devis_envoye'     => ['Devis reçu', 'bg-blue-500/20 text-blue-300'],
    'accepte'          => ['Prêt à payer', 'bg-emerald-500/20 text-emerald-300'],
    'en_cours'         => ['En cours', 'bg-purple-500/20 text-purple-300'],
    'livre'            => ['Livré', 'bg-emerald-500/20 text-emerald-400'],
    'annule'           => ['Annulé', 'bg-rose-500/20 text-rose-400'],
    'rembourse'        => ['Remboursé', 'bg-rose-500/20 text-rose-400'],
];
?>
<?php if ($success): ?><div class="bg-emerald-500/10 border border-emerald-500/30 text-emerald-400 px-4 py-3 rounded-lg mb-4 text-sm"><?= e($success) ?></div><?php endif; ?>

<div class="flex items-center justify-between flex-wrap gap-3 mb-6">
    <h1 class="font-display font-bold text-2xl sm:text-3xl text-white">Mes commandes éditoriales</h1>
    <a href="/auteur/services-editoriaux" class="btn-secondary text-sm">+ Nouvelle commande</a>
</div>

<?php if (empty($orders)): ?>
    <div class="bg-surface border border-border rounded-xl p-10 text-center">
        <p class="text-text-muted mb-4">Tu n'as pas encore de commande éditoriale.</p>
        <a href="/auteur/services-editoriaux" class="btn-primary">Découvrir nos services</a>
    </div>
<?php else: ?>
    <div class="space-y-3">
        <?php foreach ($orders as $o): ?>
            <?php [$lab, $cls] = $statutLabels[$o->statut] ?? [$o->statut, 'bg-surface-2 text-text-dim']; ?>
            <a href="/auteur/mes-commandes-editoriales/<?= (int) $o->id ?>" class="block bg-surface border border-border rounded-xl p-4 sm:p-5 hover:border-accent transition-colors">
                <div class="flex items-start gap-3">
                    <div class="text-2xl flex-shrink-0" aria-hidden="true"><?= $icons[$o->service_icon] ?? '📌' ?></div>
                    <div class="flex-1 min-w-0">
                        <div class="flex items-start justify-between flex-wrap gap-2">
                            <p class="text-white font-semibold text-sm"><?= e($o->service_nom) ?></p>
                            <span class="text-[10px] font-semibold px-2 py-0.5 rounded <?= $cls ?>"><?= e($lab) ?></span>
                        </div>
                        <p class="text-text-muted text-sm mt-1 line-clamp-1"><?= e($o->titre_projet ?? '—') ?></p>
                        <div class="flex items-center justify-between mt-2 text-xs">
                            <span class="text-text-dim">Créée le <?= date('d/m/Y', strtotime($o->created_at)) ?></span>
                            <?php if (!empty($o->montant_propose)): ?>
                                <span class="text-accent font-semibold"><?= number_format((float) $o->montant_propose, 2) ?>&nbsp;<?= e($o->devise) ?></span>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </a>
        <?php endforeach; ?>
    </div>
<?php endif; ?>
