<?php
$success = flash('success');
$error   = flash('error');
$icons = [
    'edit'    => '✏️','layout'  => '🧱','image'   => '🎨',
    'message' => '💬','package' => '📦','plus'    => '➕',
];
?>
<?php if ($success): ?><div class="bg-emerald-500/10 border border-emerald-500/30 text-emerald-400 px-4 py-3 rounded-lg mb-4 text-sm"><?= e($success) ?></div><?php endif; ?>
<?php if ($error): ?><div class="bg-red-500/10 border border-red-500/30 text-red-400 px-4 py-3 rounded-lg mb-4 text-sm"><?= e($error) ?></div><?php endif; ?>

<div class="mb-6">
    <h1 class="font-display font-bold text-2xl sm:text-3xl text-white">Services éditoriaux</h1>
    <p class="text-text-muted text-sm mt-1">Boost ton manuscrit avec nos experts. Relecture, mise en page, couverture, coaching.</p>
</div>

<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
    <?php foreach ($services as $s): ?>
        <a href="/auteur/services-editoriaux/<?= e($s->slug) ?>"
           class="block bg-surface border border-border rounded-xl p-5 hover:border-accent transition-colors">
            <div class="text-3xl mb-3" aria-hidden="true"><?= $icons[$s->icon] ?? '📌' ?></div>
            <h3 class="font-display font-semibold text-white text-base mb-2"><?= e($s->nom) ?></h3>
            <p class="text-text-muted text-sm line-clamp-3 mb-4"><?= e($s->description) ?></p>
            <div class="flex items-center justify-between">
                <?php if ($s->sur_devis): ?>
                    <span class="text-accent text-sm font-semibold">Sur devis</span>
                <?php else: ?>
                    <span class="text-accent font-display font-bold text-lg"><?= number_format((float) $s->prix_usd, 0) ?>&nbsp;$</span>
                <?php endif; ?>
                <span class="text-text-dim text-xs"><?= e($s->duree_estimee) ?></span>
            </div>
        </a>
    <?php endforeach; ?>
</div>

<div class="mt-8 text-center">
    <a href="/auteur/mes-commandes-editoriales" class="text-accent hover:text-accent-hover text-sm">→ Voir mes commandes</a>
</div>
