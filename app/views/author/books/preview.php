<?php
/** @var object $book   Livre courant (avec cat_nom du LEFT JOIN) */
/** @var object $author Auteur (= utilisateur connecté côté auteur) */

$s   = flash('author_success');
$err = flash('error');

$bc = [
    'publie'    => 'bg-emerald-500/20 text-emerald-400 border-emerald-500/30',
    'brouillon' => 'bg-amber-500/20 text-amber-400 border-amber-500/30',
    'en_revue'  => 'bg-accent/20 text-accent border-accent/30',
    'retire'    => 'bg-red-500/20 text-red-400 border-red-500/30',
];
$statutClass = $bc[$book->statut] ?? 'bg-text-dim/20 text-text-dim';

$statutLabels = [
    'brouillon' => 'BROUILLON — visible que par toi et l\'admin',
    'en_revue'  => 'EN REVUE — l\'admin va l\'examiner sous 7-14 jours',
    'publie'    => 'PUBLIÉ — visible publiquement',
    'retire'    => 'RETIRÉ — n\'est plus visible publiquement',
];
?>

<?php if ($s): ?><div class="bg-emerald-500/10 border border-emerald-500/30 text-emerald-400 px-4 py-3 rounded-lg mb-6 text-sm"><?= e($s) ?></div><?php endif; ?>
<?php if ($err): ?><div class="bg-red-500/10 border border-red-500/30 text-red-400 px-4 py-3 rounded-lg mb-6 text-sm"><?= e($err) ?></div><?php endif; ?>

<!-- Bandeau statut + retour -->
<div class="flex items-center justify-between gap-3 mb-6 flex-wrap">
    <a href="/auteur/livres" class="text-text-dim text-xs hover:text-accent transition-colors inline-flex items-center gap-1">&larr; Retour à mes livres</a>
    <span class="text-[11px] font-bold px-3 py-1.5 rounded-full border uppercase tracking-wider <?= $statutClass ?>">
        <?= strtoupper(str_replace('_', ' ', $book->statut)) ?>
    </span>
</div>

<?php if (isset($statutLabels[$book->statut])): ?>
<p class="text-text-dim text-xs mb-6 -mt-3"><?= e($statutLabels[$book->statut]) ?></p>
<?php endif; ?>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <!-- Couverture + actions principales -->
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

            <p class="text-text-muted text-sm">par <span class="text-accent"><?= e($author->nom_plume ?: 'toi') ?></span></p>
            <p class="text-text-dim text-xs mt-1">Soumis le <?= date('d/m/Y', strtotime($book->created_at)) ?></p>

            <?php if ($book->fichier_complet_path): ?>
            <div class="mt-4 space-y-2">
                <a href="/lire/<?= e($book->slug) ?>" target="_blank" class="block w-full text-center bg-accent text-black font-semibold text-sm py-2 px-3 rounded hover:bg-accent-hover transition-colors">Lire un extrait (10 pages)</a>
                <?php if ($book->statut === 'publie'): ?>
                    <a href="/livre/<?= e($book->slug) ?>" target="_blank" class="block w-full text-center border border-border text-text-muted hover:text-white hover:border-accent text-xs py-2 px-3 rounded transition-colors">Voir comme un visiteur ↗</a>
                <?php endif; ?>
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
                <div><p class="text-text-dim text-xs uppercase tracking-wider mb-0.5">Ventes</p><p class="text-white"><?= (int) $book->total_ventes ?></p></div>
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
            <a href="/auteur/livres/<?= (int) $book->id ?>/editer" class="btn-primary text-sm">Modifier ce livre</a>
            <a href="/auteur/livres" class="btn-secondary text-sm">Retour à la liste</a>
        </div>
    </div>
</div>
