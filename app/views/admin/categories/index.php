<?php $s = flash('admin_success'); if ($s): ?><div class="bg-emerald-500/10 border border-emerald-500/30 text-emerald-400 px-4 py-3 rounded-lg mb-6 text-sm"><?= e($s) ?></div><?php endif; ?>
<form method="POST" action="/admin/categories">
    <?= csrf_field() ?>
    <div class="overflow-x-auto">
    <table class="w-full text-sm">
        <thead><tr class="border-b border-border text-text-dim text-xs uppercase tracking-wider text-left">
            <th class="py-3 px-3">Ordre</th><th class="py-3 px-2">Nom</th><th class="py-3 px-2">Slug</th><th class="py-3 px-2">Actif</th>
        </tr></thead>
        <tbody>
        <?php foreach ($cats as $c): ?>
            <tr class="border-b border-border/30">
                <td class="py-2 px-3"><input type="number" name="cat[<?= $c->id ?>][ordre]" value="<?= $c->ordre_affichage ?>" class="w-14 bg-surface border border-border rounded px-2 py-1 text-sm text-white text-center outline-none focus:border-accent"></td>
                <td class="py-2 px-2"><input type="text" name="cat[<?= $c->id ?>][nom]" value="<?= e($c->nom) ?>" class="w-full bg-surface border border-border rounded px-3 py-1 text-sm text-white outline-none focus:border-accent"></td>
                <td class="py-2 px-2 text-text-dim text-xs"><?= e($c->slug) ?></td>
                <td class="py-2 px-2"><input type="checkbox" name="cat[<?= $c->id ?>][actif]" <?= $c->actif ? 'checked' : '' ?> class="accent-accent"></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    </div>
    <div class="mt-4"><button type="submit" class="btn-primary text-sm">Enregistrer</button></div>
</form>
