<section class="py-8 sm:py-12">
    <div class="max-w-[1200px] mx-auto px-4 sm:px-6">

        <!-- En-tête page -->
        <div class="mb-8">
            <h1 class="font-display font-bold text-[32px] text-white mb-1">Mes favoris</h1>
            <p class="text-text-dim text-sm"><?= count($favoris) ?> <?= count($favoris) > 1 ? 'livres dans tes favoris' : 'livre dans tes favoris' ?></p>
        </div>

        <?php if (empty($favoris)): ?>
            <!-- État vide -->
            <div class="bg-surface border border-border rounded-lg p-10 sm:p-14 text-center">
                <div class="w-20 h-20 rounded-full bg-surface-2 flex items-center justify-center mx-auto mb-5">
                    <svg class="w-10 h-10 text-text-dim" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12z"/>
                    </svg>
                </div>
                <h2 class="font-display font-semibold text-xl text-white mb-2">Tu n'as pas encore de favoris</h2>
                <p class="text-text-muted text-sm max-w-md mx-auto mb-6">
                    Explore le catalogue et ajoute des livres à tes favoris en cliquant sur le cœur sur leur page.
                </p>
                <a href="/catalogue" class="btn-primary inline-block">Explorer le catalogue</a>
            </div>

        <?php else: ?>
            <!-- Grille favoris -->
            <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-4 sm:gap-5">
                <?php foreach ($favoris as $livre): ?>
                    <?php $coverUrl = book_cover_url($livre); ?>
                    <div x-data="{ visible: true, loading: false }"
                         x-show="visible"
                         x-transition:leave="transition ease-in duration-500"
                         x-transition:leave-start="opacity-100 scale-100"
                         x-transition:leave-end="opacity-0 scale-95"
                         class="relative group">

                        <a href="/livre/<?= e($livre->slug) ?>" class="block">
                            <div class="relative aspect-[2/3] overflow-hidden rounded-lg bg-gradient-to-br <?= book_cover_gradient($livre->id) ?> transition-transform duration-300 group-hover:scale-105 group-hover:ring-2 group-hover:ring-accent">
                                <?php if ($coverUrl): ?>
                                    <img src="<?= e($coverUrl) ?>" alt="<?= e($livre->titre) ?>" class="w-full h-full object-cover" loading="lazy">
                                    <div class="absolute inset-0 bg-gradient-to-b from-black/40 via-transparent to-black/50"></div>
                                <?php else: ?>
                                    <div class="w-full h-full flex items-center justify-center p-4">
                                        <span class="text-white font-display font-semibold text-center text-sm drop-shadow-lg"><?= e($livre->titre) ?></span>
                                    </div>
                                <?php endif; ?>
                                <?php if (!empty($livre->category_nom)): ?>
                                    <span class="absolute top-2 left-2 text-[9px] font-semibold uppercase tracking-wider text-accent bg-black/60 backdrop-blur-sm px-1.5 py-0.5 rounded"><?= e($livre->category_nom) ?></span>
                                <?php endif; ?>
                            </div>
                            <div class="mt-2.5 px-0.5">
                                <p class="text-white text-[13px] sm:text-sm font-medium leading-snug line-clamp-2 group-hover:text-accent transition-colors"><?= e($livre->titre) ?></p>
                                <p class="text-text-dim text-[11px] mt-1 truncate"><?= e($livre->author_display) ?></p>
                                <p class="text-text-dim text-[10px] mt-1">Ajouté le <?= date('d/m/Y', strtotime($livre->date_ajout_favori)) ?></p>
                            </div>
                        </a>

                        <!-- Bouton retirer -->
                        <button type="button"
                                @click.prevent="
                                    if (loading) return;
                                    loading = true;
                                    fetch('/livre/<?= e($livre->slug) ?>/favori', {
                                        method: 'POST',
                                        headers: {
                                            'X-Requested-With': 'XMLHttpRequest',
                                            'X-CSRF-Token': '<?= csrf_token() ?>'
                                        }
                                    })
                                    .then(r => r.json())
                                    .then(d => { if (!d.favori) { visible = false; } loading = false; })
                                    .catch(() => { loading = false; });
                                "
                                :disabled="loading"
                                class="absolute top-2 right-2 opacity-0 group-hover:opacity-100 focus:opacity-100 bg-rose-600 hover:bg-rose-700 text-white rounded-full w-8 h-8 flex items-center justify-center transition-opacity disabled:opacity-50"
                                aria-label="Retirer des favoris">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

    </div>
</section>
