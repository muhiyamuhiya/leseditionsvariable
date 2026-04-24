<?php $s = flash('admin_success'); if ($s): ?><div class="bg-emerald-500/10 border border-emerald-500/30 text-emerald-400 px-4 py-3 rounded-lg mb-6 text-sm"><?= e($s) ?></div><?php endif; ?>

<a href="/admin/livres" class="text-text-dim text-xs hover:text-accent transition-colors mb-4 inline-block">&larr; Retour aux livres</a>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <!-- Couverture -->
    <div class="lg:col-span-1">
        <div class="bg-surface border border-border rounded-xl p-5">
            <?php $coverUrl = book_cover_url($book); ?>
            <div class="aspect-[2/3] overflow-hidden rounded-lg bg-gradient-to-br <?= book_cover_gradient($book->id) ?> mb-4">
                <?php if ($coverUrl): ?>
                    <img src="<?= e($coverUrl) ?>" alt="<?= e($book->titre) ?>" class="w-full h-full object-cover">
                <?php else: ?>
                    <div class="w-full h-full flex flex-col items-center justify-between p-6 text-center">
                        <div class="w-16 h-0.5 bg-accent/60"></div>
                        <p class="font-display font-bold text-white text-xl drop-shadow-lg"><?= e($book->titre) ?></p>
                        <div class="w-16 h-0.5 bg-accent/60"></div>
                    </div>
                <?php endif; ?>
            </div>

            <?php if ($author): ?>
            <p class="text-text-muted text-sm">par <a href="/admin/auteurs/<?= $author->id ?>/editer" class="text-accent hover:text-accent-hover"><?= e($author->prenom . ' ' . $author->nom) ?></a></p>
            <?php endif; ?>
            <p class="text-text-dim text-xs mt-1">Soumis le <?= date('d/m/Y', strtotime($book->created_at)) ?></p>

            <?php $bc = ['publie'=>'bg-emerald-500/20 text-emerald-400','brouillon'=>'bg-text-dim/20 text-text-dim','en_revue'=>'bg-accent/20 text-accent','retire'=>'bg-red-500/20 text-red-400']; ?>
            <div class="mt-3"><span class="text-xs font-medium px-3 py-1 rounded-full <?= $bc[$book->statut] ?? '' ?>"><?= ucfirst(str_replace('_',' ',$book->statut)) ?></span></div>

            <?php if ($book->fichier_complet_path): ?>
            <div class="mt-4 space-y-2">
                <a href="/lire/<?= e($book->slug) ?>/extrait" target="_blank" class="block text-accent text-xs hover:text-accent-hover">Prévisualiser le PDF</a>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Détails -->
    <div class="lg:col-span-2 space-y-6">
        <div class="bg-surface border border-border rounded-xl p-5">
            <h2 class="font-display font-bold text-2xl text-white mb-1"><?= e($book->titre) ?></h2>
            <?php if ($book->sous_titre): ?><p class="text-text-muted mb-3"><?= e($book->sous_titre) ?></p><?php endif; ?>

            <div class="grid grid-cols-2 sm:grid-cols-3 gap-3 mt-4 text-sm">
                <div><p class="text-text-dim text-xs uppercase tracking-wider mb-0.5">Catégorie</p><p class="text-white"><?= e($book->cat_nom ?? '-') ?></p></div>
                <div><p class="text-text-dim text-xs uppercase tracking-wider mb-0.5">Pages</p><p class="text-white"><?= $book->nombre_pages ?? '-' ?></p></div>
                <div><p class="text-text-dim text-xs uppercase tracking-wider mb-0.5">Langue</p><p class="text-white"><?= e(ucfirst($book->langue ?? 'fr')) ?></p></div>
                <div><p class="text-text-dim text-xs uppercase tracking-wider mb-0.5">ISBN</p><p class="text-white"><?= e($book->isbn ?? '-') ?></p></div>
                <div><p class="text-text-dim text-xs uppercase tracking-wider mb-0.5">Prix USD</p><p class="text-accent font-semibold"><?= number_format($book->prix_unitaire_usd, 2) ?> $</p></div>
                <div><p class="text-text-dim text-xs uppercase tracking-wider mb-0.5">Abonnement</p><p class="text-white"><?= $book->accessible_abonnement ? 'Oui' : 'Non' ?></p></div>
            </div>
        </div>

        <?php if ($book->description_courte): ?>
        <div class="bg-surface border border-border rounded-xl p-5">
            <h3 class="text-white font-semibold text-sm mb-2">Description courte</h3>
            <p class="text-text-muted text-sm"><?= e($book->description_courte) ?></p>
        </div>
        <?php endif; ?>

        <?php if ($book->description_longue): ?>
        <div class="bg-surface border border-border rounded-xl p-5">
            <h3 class="text-white font-semibold text-sm mb-2">Description longue</h3>
            <div class="text-text-muted text-sm leading-relaxed whitespace-pre-line"><?= e($book->description_longue) ?></div>
        </div>
        <?php endif; ?>

        <?php if ($book->mots_cles): ?>
        <div class="bg-surface border border-border rounded-xl p-5">
            <h3 class="text-white font-semibold text-sm mb-2">Mots-clés</h3>
            <div class="flex flex-wrap gap-2">
                <?php foreach (explode(',', $book->mots_cles) as $kw): ?>
                    <span class="text-xs bg-surface-2 text-text-muted px-2 py-1 rounded"><?= e(trim($kw)) ?></span>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>

        <!-- Actions -->
        <div class="flex flex-wrap gap-3 pt-4">
            <?php if (in_array($book->statut, ['brouillon','en_revue'])): ?>
                <form method="POST" action="/admin/livres/<?= $book->id ?>/publier"><?= csrf_field() ?><button class="bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-medium px-6 py-2.5 rounded transition-colors">Publier le livre</button></form>
                <a href="/admin/livres/<?= $book->id ?>/editer" class="btn-secondary text-sm">Modifier</a>
            <?php elseif ($book->statut === 'publie'): ?>
                <a href="/livre/<?= e($book->slug) ?>" target="_blank" class="btn-primary text-sm">Voir page publique</a>
                <a href="/admin/livres/<?= $book->id ?>/editer" class="btn-secondary text-sm">Modifier</a>
            <?php endif; ?>
        </div>
    </div>
</div>
