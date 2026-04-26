<?php
/** @var array $grouped  Templates groupés par catégorie */
$labels = [
    'paiement'   => '💳 Paiement',
    'onboarding' => '👋 Onboarding',
    'compte'     => '⚙️ Compte',
    'auteur'     => '✍️ Auteur',
    'admin'      => '🔔 Notifs admin',
    'autre'      => 'Autre',
];
?>
<?php $s = flash('success'); if ($s): ?><div class="bg-emerald-500/10 border border-emerald-500/30 text-emerald-400 px-4 py-3 rounded-lg mb-6 text-sm"><?= e($s) ?></div><?php endif; ?>
<?php $e = flash('error');   if ($e): ?><div class="bg-red-500/10 border border-red-500/30 text-red-400 px-4 py-3 rounded-lg mb-6 text-sm"><?= e($e) ?></div><?php endif; ?>

<div class="mb-6">
    <p class="text-text-muted text-sm">
        Aperçu visuel des <?= count(array_merge(...array_values($grouped))) ?> templates emails du projet, avec des données fictives.
        Utile pour vérifier le rendu avant un envoi en prod.
    </p>
</div>

<?php foreach ($labels as $key => $label): ?>
    <?php if (empty($grouped[$key])) continue; ?>
    <div class="mb-8">
        <h2 class="font-display text-white text-lg font-semibold mb-3"><?= $label ?></h2>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
            <?php foreach ($grouped[$key] as $slug => $tpl): ?>
                <a href="/admin/emails/preview/<?= e($slug) ?>"
                   class="block bg-surface border border-border rounded-lg p-4 hover:border-accent transition-colors">
                    <div class="flex items-start gap-2">
                        <div class="flex-grow min-w-0">
                            <p class="text-white text-sm font-semibold truncate"><?= e($tpl['label']) ?></p>
                            <p class="text-text-dim text-xs font-mono truncate mt-1"><?= e($slug) ?>.php</p>
                        </div>
                        <?php if (!empty($tpl['has_pdf'])): ?>
                            <span class="text-amber-400 text-[10px] uppercase tracking-wider font-semibold border border-amber-400/40 rounded px-1.5 py-0.5 shrink-0">PDF</span>
                        <?php endif; ?>
                    </div>
                </a>
            <?php endforeach; ?>
        </div>
    </div>
<?php endforeach; ?>
