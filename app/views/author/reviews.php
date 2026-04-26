<?php
/** @var array  $reviews     Avis paginés (avec book_titre, book_slug, reviewer_prenom, reviewer_nom) */
/** @var array  $authorBooks  Liste {id, titre} des livres de l'auteur (filtre dropdown) */
/** @var ?object $stats       total + moyenne globales */
/** @var int    $total */
/** @var int    $page */
/** @var int    $perPage */
/** @var array  $filters      bookId, note, tri */

$totalPages = max(1, (int) ceil($total / $perPage));

// Helper local pour rendre les étoiles d'une note (0-5)
$renderStars = function (float $n): string {
    $full = (int) floor($n);
    $hasHalf = ($n - $full) >= 0.5;
    $out = str_repeat('★', $full);
    if ($hasHalf) { $out .= '☆'; }
    return str_pad($out, 5, '☆');
};
?>

<!-- Stats globales -->
<div class="grid grid-cols-2 sm:grid-cols-3 gap-3 mb-6">
    <div class="bg-surface border border-border rounded-lg p-4">
        <p class="text-text-dim text-[10px] uppercase tracking-wider">Total avis</p>
        <p class="text-white text-2xl font-display font-bold mt-1"><?= (int) ($stats->total ?? 0) ?></p>
    </div>
    <div class="bg-surface border border-border rounded-lg p-4">
        <p class="text-text-dim text-[10px] uppercase tracking-wider">Note moyenne</p>
        <p class="text-amber-400 text-2xl font-display font-bold mt-1"><?= number_format((float) ($stats->moyenne ?? 0), 2) ?> / 5</p>
        <p class="text-amber-400 text-sm mt-0.5"><?= $renderStars((float) ($stats->moyenne ?? 0)) ?></p>
    </div>
    <div class="bg-surface border border-border rounded-lg p-4 hidden sm:block">
        <p class="text-text-dim text-[10px] uppercase tracking-wider">Filtrés</p>
        <p class="text-white text-2xl font-display font-bold mt-1"><?= (int) $total ?></p>
    </div>
</div>

<!-- Filtres -->
<form method="GET" action="/auteur/avis" class="bg-surface border border-border rounded-lg p-4 mb-6">
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
        <div>
            <label class="block text-text-dim text-[10px] uppercase tracking-wider mb-1">Livre</label>
            <select name="book_id" class="w-full bg-surface-2 border border-border rounded px-3 py-2 text-sm text-white outline-none focus:border-accent">
                <option value="0">Tous mes livres</option>
                <?php foreach ($authorBooks as $b): ?>
                    <option value="<?= (int) $b->id ?>" <?= ($filters['bookId'] ?? 0) === (int) $b->id ? 'selected' : '' ?>>
                        <?= e($b->titre) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div>
            <label class="block text-text-dim text-[10px] uppercase tracking-wider mb-1">Note</label>
            <div class="flex gap-1">
                <a href="<?= e('/auteur/avis?' . http_build_query(array_merge($filters, ['note' => 0]))) ?>"
                   class="px-3 py-2 text-xs rounded border <?= ($filters['note'] ?? 0) === 0 ? 'border-accent bg-accent/10 text-accent' : 'border-border text-text-muted hover:border-accent' ?>">Toutes</a>
                <?php for ($n = 1; $n <= 5; $n++): ?>
                    <a href="<?= e('/auteur/avis?' . http_build_query(array_merge($filters, ['note' => $n]))) ?>"
                       class="px-2.5 py-2 text-xs rounded border <?= ($filters['note'] ?? 0) === $n ? 'border-accent bg-accent/10 text-accent' : 'border-border text-text-muted hover:border-accent' ?>"><?= $n ?>★</a>
                <?php endfor; ?>
            </div>
        </div>
        <div>
            <label class="block text-text-dim text-[10px] uppercase tracking-wider mb-1">Tri</label>
            <select name="tri" class="w-full bg-surface-2 border border-border rounded px-3 py-2 text-sm text-white outline-none focus:border-accent">
                <option value="recent" <?= ($filters['tri'] ?? 'recent') === 'recent' ? 'selected' : '' ?>>Plus récents</option>
                <option value="ancien" <?= ($filters['tri'] ?? 'recent') === 'ancien' ? 'selected' : '' ?>>Plus anciens</option>
            </select>
        </div>
    </div>
    <div class="flex items-center gap-3 mt-3">
        <button type="submit" class="btn-primary text-sm">Filtrer</button>
        <a href="/auteur/avis" class="text-text-dim hover:text-accent text-sm">Réinitialiser</a>
    </div>
</form>

<!-- Liste -->
<?php if (empty($reviews)): ?>
    <div class="bg-surface border border-border rounded-lg p-10 text-center">
        <p class="text-text-muted">Aucun avis pour ces critères.</p>
        <?php if ((int) ($stats->total ?? 0) === 0): ?>
            <p class="text-text-dim text-xs mt-2">Tu n'as pas encore reçu d'avis. Ils apparaîtront ici dès qu'un lecteur en publiera un.</p>
        <?php endif; ?>
    </div>
<?php else: ?>
    <div class="space-y-3">
        <?php foreach ($reviews as $r):
            $reviewerName = trim(($r->reviewer_prenom ?? '') . ' ' . mb_substr((string) ($r->reviewer_nom ?? ''), 0, 1) . (!empty($r->reviewer_nom) ? '.' : '')) ?: 'Lecteur·rice';
            $cover = $r->couverture_url_web ?: ($r->couverture_path ? '/' . ltrim((string) $r->couverture_path, '/') : '');
        ?>
        <div class="bg-surface border border-border rounded-lg p-4 sm:p-5">
            <div class="flex items-start gap-4">
                <!-- Mini cover -->
                <a href="/livre/<?= e($r->book_slug) ?>#avis" class="flex-shrink-0 w-12 h-16 sm:w-14 sm:h-20 rounded overflow-hidden bg-surface-2 border border-border <?= $cover ? '' : 'flex items-center justify-center' ?>">
                    <?php if ($cover): ?>
                        <img src="<?= e($cover) ?>" alt="<?= e($r->book_titre) ?>" class="w-full h-full object-cover">
                    <?php else: ?>
                        <span class="text-text-dim text-[10px]">📕</span>
                    <?php endif; ?>
                </a>

                <!-- Contenu -->
                <div class="flex-grow min-w-0">
                    <div class="flex items-start justify-between gap-2 flex-wrap">
                        <div class="min-w-0">
                            <a href="/livre/<?= e($r->book_slug) ?>#avis" class="text-white text-sm font-semibold hover:text-accent truncate inline-block max-w-full">
                                <?= e($r->book_titre) ?>
                            </a>
                            <p class="text-text-dim text-xs mt-0.5">
                                par <?= e($reviewerName) ?> · <?= date('d/m/Y', strtotime((string) $r->created_at)) ?>
                            </p>
                        </div>
                        <span class="text-amber-400 text-sm whitespace-nowrap"><?= $renderStars((float) $r->note) ?> <span class="text-text-dim">(<?= (int) $r->note ?>/5)</span></span>
                    </div>
                    <?php if (!empty($r->titre)): ?>
                        <p class="text-white text-sm font-medium mt-2"><?= e($r->titre) ?></p>
                    <?php endif; ?>
                    <?php if (!empty($r->commentaire)): ?>
                        <p class="text-text-muted text-sm mt-1 leading-relaxed whitespace-pre-line"><?= e($r->commentaire) ?></p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

    <!-- Pagination -->
    <?php if ($totalPages > 1):
        $qs = $filters; $qs['page'] = null;
        $base = '/auteur/avis?' . http_build_query(array_filter($qs, static fn ($v) => $v !== null && $v !== '')) . '&page=';
    ?>
        <div class="flex justify-center gap-2 mt-6">
            <?php if ($page > 1): ?>
                <a href="<?= e($base . ($page - 1)) ?>" class="px-3 py-1.5 text-xs bg-surface border border-border rounded hover:border-accent text-text-muted">← Précédent</a>
            <?php endif; ?>
            <span class="px-3 py-1.5 text-xs text-text-dim">Page <?= $page ?> / <?= $totalPages ?></span>
            <?php if ($page < $totalPages): ?>
                <a href="<?= e($base . ($page + 1)) ?>" class="px-3 py-1.5 text-xs bg-surface border border-border rounded hover:border-accent text-text-muted">Suivant →</a>
            <?php endif; ?>
        </div>
    <?php endif; ?>
<?php endif; ?>
