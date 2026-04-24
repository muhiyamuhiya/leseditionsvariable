<?php // Utilise book_cover_gradient() et book_author_name() depuis helpers ?>

<section class="py-8 sm:py-12">
    <div class="max-w-[1400px] mx-auto px-4 sm:px-6">

        <!-- Titre -->
        <div class="mb-8">
            <h1 class="font-display font-extrabold text-3xl sm:text-4xl md:text-5xl text-white">
                <?= $categorieActive ? e($categorieActive->nom) : 'Notre catalogue' ?>
            </h1>
            <p class="text-text-muted mt-2 text-sm sm:text-base"><?= $total ?> livre<?= $total > 1 ? 's' : '' ?> disponible<?= $total > 1 ? 's' : '' ?></p>
        </div>

        <!-- Filtres -->
        <form method="GET" action="/catalogue" class="flex flex-col sm:flex-row gap-3 mb-8">
            <!-- Catégorie -->
            <select name="categorie" onchange="this.form.submit()"
                    class="bg-surface border border-border rounded-lg px-4 py-2.5 text-sm text-white outline-none focus:border-accent">
                <option value="">Toutes les catégories</option>
                <?php foreach ($categories as $cat): ?>
                    <option value="<?= e($cat->slug) ?>" <?= $categorySlug === $cat->slug ? 'selected' : '' ?>><?= e($cat->nom) ?></option>
                <?php endforeach; ?>
            </select>

            <!-- Tri -->
            <select name="tri" onchange="this.form.submit()"
                    class="bg-surface border border-border rounded-lg px-4 py-2.5 text-sm text-white outline-none focus:border-accent">
                <option value="recent" <?= $tri === 'recent' ? 'selected' : '' ?>>Plus récents</option>
                <option value="populaire" <?= $tri === 'populaire' ? 'selected' : '' ?>>Populaires</option>
                <option value="prix_asc" <?= $tri === 'prix_asc' ? 'selected' : '' ?>>Prix croissant</option>
                <option value="prix_desc" <?= $tri === 'prix_desc' ? 'selected' : '' ?>>Prix décroissant</option>
            </select>

            <!-- Recherche -->
            <div class="relative flex-grow sm:max-w-xs">
                <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-text-dim" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z"/></svg>
                <input type="text" name="q" value="<?= e($search ?? '') ?>" placeholder="Rechercher..."
                       class="w-full pl-10 pr-4 py-2.5 bg-surface border border-border rounded-lg text-sm text-white outline-none focus:border-accent placeholder:text-text-dim">
            </div>

            <?php if ($categorySlug): ?>
                <input type="hidden" name="categorie" value="<?= e($categorySlug) ?>">
            <?php endif; ?>
        </form>

        <!-- Grille de livres -->
        <?php if (empty($livres)): ?>
            <div class="text-center py-20">
                <p class="text-text-muted text-lg mb-4">Aucun livre trouvé.</p>
                <a href="/catalogue" class="btn-primary">Voir tout le catalogue</a>
            </div>
        <?php else: ?>
            <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-4 sm:gap-5">
                <?php foreach ($livres as $livre): ?>
                <?php $coverUrl = book_cover_url($livre); ?>
                <a href="/livre/<?= e($livre->slug) ?>" class="group block">
                    <div class="relative aspect-[2/3] overflow-hidden rounded-lg bg-gradient-to-br <?= book_cover_gradient($livre->id) ?> transition-transform duration-300 group-hover:scale-105 group-hover:ring-2 group-hover:ring-accent">
                        <?php if ($coverUrl): ?>
                            <img src="<?= e($coverUrl) ?>" alt="<?= e($livre->titre) ?>" class="w-full h-full object-cover" loading="lazy">
                            <div class="absolute inset-0 bg-gradient-to-b from-black/40 via-transparent to-black/50"></div>
                        <?php else: ?>
                            <div class="w-full h-full flex items-center justify-center p-4">
                                <span class="text-white font-display font-semibold text-center text-sm drop-shadow-lg"><?= e($livre->titre) ?></span>
                            </div>
                        <?php endif; ?>
                        <span class="absolute top-2 left-2 text-[9px] font-semibold uppercase tracking-wider text-accent bg-black/60 backdrop-blur-sm px-1.5 py-0.5 rounded"><?= e($livre->category_nom ?? '') ?></span>
                    </div>
                    <div class="mt-2.5 px-0.5">
                        <p class="text-white text-[13px] sm:text-sm font-medium leading-snug line-clamp-2 group-hover:text-accent transition-colors"><?= e($livre->titre) ?></p>
                        <p class="text-text-dim text-[11px] mt-1 truncate"><?= e(book_author_name($livre)) ?></p>
                        <p class="text-accent text-sm font-semibold mt-1"><?= number_format($livre->prix_unitaire_usd, 2) ?>&nbsp;$</p>
                    </div>
                </a>
                <?php endforeach; ?>
            </div>

            <!-- Pagination -->
            <?php if ($totalPages > 1): ?>
            <nav class="flex items-center justify-center gap-2 mt-10">
                <?php
                $baseUrl = '/catalogue?' . http_build_query(array_filter([
                    'categorie' => $categorySlug,
                    'q' => $search,
                    'tri' => $tri,
                ]));
                ?>

                <?php if ($page > 1): ?>
                    <a href="<?= $baseUrl ?>&page=<?= $page - 1 ?>" class="px-4 py-2 bg-surface border border-border rounded text-sm text-text-muted hover:text-accent hover:border-accent transition-colors">Précédent</a>
                <?php endif; ?>

                <?php for ($p = max(1, $page - 2); $p <= min($totalPages, $page + 2); $p++): ?>
                    <a href="<?= $baseUrl ?>&page=<?= $p ?>"
                       class="px-3.5 py-2 rounded text-sm font-medium transition-colors <?= $p === $page ? 'bg-accent text-black' : 'bg-surface border border-border text-text-muted hover:text-accent hover:border-accent' ?>">
                        <?= $p ?>
                    </a>
                <?php endfor; ?>

                <?php if ($page < $totalPages): ?>
                    <a href="<?= $baseUrl ?>&page=<?= $page + 1 ?>" class="px-4 py-2 bg-surface border border-border rounded text-sm text-text-muted hover:text-accent hover:border-accent transition-colors">Suivant</a>
                <?php endif; ?>
            </nav>
            <?php endif; ?>
        <?php endif; ?>

    </div>
</section>
