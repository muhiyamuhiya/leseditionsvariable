<section class="py-12 sm:py-20">
    <div class="max-w-6xl mx-auto px-4 sm:px-6">

        <div class="text-center mb-14">
            <h1 class="font-display font-extrabold text-3xl sm:text-4xl md:text-5xl text-white mb-3">Le carnet Variable</h1>
            <p class="text-text-muted text-base sm:text-lg">Pensées, conseils et coulisses de la littérature africaine.</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
            <?php foreach ($articles as $a): ?>
                <a href="/blog/<?= e($a['slug']) ?>" class="bg-surface border border-border rounded-xl p-6 hover:border-accent transition-colors flex flex-col">
                    <p class="text-text-dim text-xs uppercase tracking-wider mb-3"><?= date('d/m/Y', strtotime($a['date'])) ?> · <?= e($a['author']) ?></p>
                    <h2 class="font-display font-bold text-white text-lg leading-snug mb-3 hover:text-accent transition-colors"><?= e($a['title']) ?></h2>
                    <p class="text-text-muted text-sm flex-grow"><?= e($a['excerpt']) ?></p>
                    <span class="text-accent text-sm mt-4">Lire l'article →</span>
                </a>
            <?php endforeach; ?>
        </div>

    </div>
</section>
