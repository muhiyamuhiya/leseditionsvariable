<?php $s = flash('admin_success'); if ($s): ?><div class="bg-emerald-500/10 border border-emerald-500/30 text-emerald-400 px-4 py-3 rounded-lg mb-6 text-sm"><?= e($s) ?></div><?php endif; ?>

<?php if (empty($candidatures)): ?>
    <div class="bg-surface border border-border rounded-xl p-8 text-center">
        <p class="text-text-muted">Aucune candidature en attente pour le moment.</p>
    </div>
<?php else: ?>
    <div class="space-y-4">
        <?php foreach ($candidatures as $c): ?>
        <div class="bg-surface border border-border rounded-xl p-5">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div>
                    <p class="text-white font-medium"><?= e($c->prenom . ' ' . $c->nom) ?></p>
                    <p class="text-text-dim text-sm"><?= e($c->email) ?> &middot; <?= e($c->pays_origine ?? '?') ?></p>
                    <?php if ($c->biographie_courte): ?>
                        <p class="text-text-muted text-sm mt-2 max-w-lg"><?= e($c->biographie_courte) ?></p>
                    <?php endif; ?>
                    <p class="text-text-dim text-xs mt-2">Candidature reçue le <?= date('d/m/Y', strtotime($c->created_at)) ?></p>
                </div>
                <div class="flex items-center gap-2 flex-shrink-0">
                    <form method="POST" action="/admin/candidatures/<?= $c->id ?>/valider"><?= csrf_field() ?><button class="bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-medium px-4 py-2 rounded transition-colors">Valider</button></form>
                    <form method="POST" action="/admin/candidatures/<?= $c->id ?>/refuser" onsubmit="this.querySelector('[name=motif]').value=prompt('Motif du refus :') || ''; return this.querySelector('[name=motif]').value !== '';">
                        <?= csrf_field() ?><input type="hidden" name="motif" value="">
                        <button class="bg-red-600/20 hover:bg-red-600/40 text-red-400 text-xs font-medium px-4 py-2 rounded transition-colors">Refuser</button>
                    </form>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>
