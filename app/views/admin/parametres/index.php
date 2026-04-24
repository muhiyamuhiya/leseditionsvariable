<?php $s = flash('admin_success'); if ($s): ?><div class="bg-emerald-500/10 border border-emerald-500/30 text-emerald-400 px-4 py-3 rounded-lg mb-6 text-sm"><?= e($s) ?></div><?php endif; ?>
<form method="POST" action="/admin/parametres" class="max-w-2xl space-y-4">
    <?= csrf_field() ?>
    <?php foreach ($settings as $s): ?>
    <div class="bg-surface border border-border rounded-lg p-4">
        <label class="block text-xs text-text-dim uppercase tracking-wider mb-1"><?= e($s->description ?? $s->key) ?></label>
        <div class="flex items-center gap-3">
            <span class="text-text-dim text-xs font-mono w-56 truncate"><?= e($s->key) ?></span>
            <input type="text" name="setting[<?= e($s->key) ?>]" value="<?= e($s->value) ?>"
                   class="flex-grow bg-surface-2 border border-border rounded px-3 py-2 text-sm text-white outline-none focus:border-accent">
        </div>
    </div>
    <?php endforeach; ?>
    <div class="pt-4"><button type="submit" class="btn-primary">Enregistrer</button></div>
</form>
