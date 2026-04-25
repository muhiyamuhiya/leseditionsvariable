<?php
$icons = ['edit'=>'✏️','layout'=>'🧱','image'=>'🎨','message'=>'💬','package'=>'📦','plus'=>'➕'];
?>
<div class="mb-4"><a href="/auteur/services-editoriaux" class="text-text-dim hover:text-accent text-xs">← Retour aux services</a></div>

<div class="bg-surface border border-border rounded-xl p-6 sm:p-8">
    <div class="text-4xl mb-4" aria-hidden="true"><?= $icons[$service->icon] ?? '📌' ?></div>
    <h1 class="font-display font-bold text-2xl sm:text-3xl text-white mb-2"><?= e($service->nom) ?></h1>

    <div class="flex flex-wrap items-center gap-3 mb-5">
        <?php if ($service->sur_devis): ?>
            <span class="inline-block bg-accent/15 text-accent text-xs font-semibold px-2.5 py-1 rounded">Sur devis</span>
        <?php else: ?>
            <span class="inline-block bg-accent/15 text-accent text-xs font-semibold px-2.5 py-1 rounded"><?= number_format((float) $service->prix_usd, 2) ?>&nbsp;$</span>
        <?php endif; ?>
        <span class="text-text-dim text-xs">⏱ <?= e($service->duree_estimee) ?></span>
    </div>

    <p class="text-text-muted leading-relaxed mb-7"><?= e($service->description) ?></p>

    <a href="/auteur/services-editoriaux/<?= e($service->slug) ?>/commander"
       class="btn-primary inline-block">Commander maintenant</a>
</div>
