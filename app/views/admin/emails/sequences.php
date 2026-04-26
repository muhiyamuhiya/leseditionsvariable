<?php
/** @var array $sequences */
$current = 'sequences';
require __DIR__ . '/_tabs.php';
?>
<?php $s = flash('success'); if ($s): ?><div class="bg-emerald-500/10 border border-emerald-500/30 text-emerald-400 px-4 py-3 rounded-lg mb-6 text-sm"><?= e($s) ?></div><?php endif; ?>
<?php $err = flash('error'); if ($err): ?><div class="bg-red-500/10 border border-red-500/30 text-red-400 px-4 py-3 rounded-lg mb-6 text-sm"><?= e($err) ?></div><?php endif; ?>

<div class="mb-6">
    <p class="text-text-muted text-sm">
        Séquences automatisées exécutées par le cron <code class="text-amber-400 text-xs">app/jobs/SendScheduledEmails.php</code>.
        Désactiver une séquence stoppe l'avancement de toutes ses progressions en cours.
    </p>
</div>

<?php if (empty($sequences)): ?>
    <div class="bg-surface border border-border rounded-lg p-8 text-center">
        <p class="text-text-muted">Aucune séquence configurée.</p>
    </div>
<?php else: ?>
    <?php foreach ($sequences as $seq): ?>
        <div class="bg-surface border border-border rounded-lg mb-4 overflow-hidden">
            <div class="px-5 py-4 border-b border-border flex items-start gap-4">
                <div class="flex-grow min-w-0">
                    <div class="flex items-center gap-2 mb-1">
                        <h2 class="font-display text-white text-base font-semibold"><?= e($seq->name) ?></h2>
                        <?php if ($seq->active): ?>
                            <span class="text-emerald-400 text-[10px] uppercase tracking-wider font-semibold border border-emerald-500/30 rounded px-1.5 py-0.5">Active</span>
                        <?php else: ?>
                            <span class="text-text-dim text-[10px] uppercase tracking-wider font-semibold border border-border rounded px-1.5 py-0.5">Désactivée</span>
                        <?php endif; ?>
                    </div>
                    <p class="text-text-dim text-xs font-mono"><?= e($seq->slug) ?></p>
                    <?php if (!empty($seq->description)): ?>
                        <p class="text-text-muted text-xs mt-2 max-w-2xl"><?= e($seq->description) ?></p>
                    <?php endif; ?>
                </div>
                <form method="POST" action="/admin/emails/sequences/<?= (int) $seq->id ?>/toggle" class="shrink-0">
                    <?= csrf_field() ?>
                    <button type="submit"
                            class="px-3 py-1.5 text-xs rounded border <?= $seq->active
                                ? 'border-red-500/40 text-red-400 hover:bg-red-500/10'
                                : 'border-emerald-500/40 text-emerald-400 hover:bg-emerald-500/10' ?>">
                        <?= $seq->active ? 'Désactiver' : 'Activer' ?>
                    </button>
                </form>
            </div>

            <!-- Stats -->
            <div class="grid grid-cols-2 sm:grid-cols-4 border-b border-border">
                <div class="p-4 border-r border-border">
                    <p class="text-text-dim text-[10px] uppercase tracking-wider">Steps</p>
                    <p class="text-white text-xl font-semibold mt-1"><?= (int) $seq->nb_steps ?></p>
                </div>
                <div class="p-4 border-r border-border">
                    <p class="text-text-dim text-[10px] uppercase tracking-wider">En cours</p>
                    <p class="text-amber-400 text-xl font-semibold mt-1"><?= (int) $seq->nb_running ?></p>
                </div>
                <div class="p-4 border-r border-border">
                    <p class="text-text-dim text-[10px] uppercase tracking-wider">Complétés</p>
                    <p class="text-emerald-400 text-xl font-semibold mt-1"><?= (int) $seq->nb_completed ?></p>
                </div>
                <div class="p-4">
                    <p class="text-text-dim text-[10px] uppercase tracking-wider">Annulés / pause</p>
                    <p class="text-text-muted text-xl font-semibold mt-1"><?= (int) $seq->nb_cancelled ?></p>
                </div>
            </div>

            <!-- Steps -->
            <div class="px-5 py-3">
                <p class="text-text-dim text-[10px] uppercase tracking-wider mb-3">Étapes</p>
                <div class="space-y-2">
                    <?php foreach ($seq->steps as $step): ?>
                        <div class="flex items-center gap-3 text-sm">
                            <span class="text-text-dim w-8 text-xs">#<?= (int) $step->sort_order ?></span>
                            <span class="text-amber-400 text-xs font-mono w-12 text-right">J+<?= (int) $step->day_offset ?></span>
                            <a href="/admin/emails/preview/<?= e($step->template) ?>" class="text-text-muted text-xs font-mono hover:text-accent flex-shrink-0">
                                <?= e($step->template) ?>.php
                            </a>
                            <span class="text-white text-xs truncate"><?= e($step->subject ?? '') ?></span>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
<?php endif; ?>
