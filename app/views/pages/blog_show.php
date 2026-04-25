<section class="py-12 sm:py-20">
    <div class="max-w-3xl mx-auto px-4 sm:px-6">

        <div class="mb-6"><a href="/blog" class="text-text-dim hover:text-accent text-xs">← Retour au carnet</a></div>

        <article>
            <p class="text-text-dim text-xs uppercase tracking-wider mb-3"><?= date('d/m/Y', strtotime($article['date'])) ?> · <?= e($article['author']) ?></p>
            <h1 class="font-display font-extrabold text-3xl sm:text-4xl text-white leading-tight mb-4"><?= e($article['title']) ?></h1>
            <p class="text-text-muted text-lg italic mb-8"><?= e($article['excerpt']) ?></p>

            <div class="prose prose-invert max-w-none text-text-muted leading-relaxed space-y-4
                        [&_h2]:font-display [&_h2]:font-bold [&_h2]:text-2xl [&_h2]:text-white [&_h2]:mt-8 [&_h2]:mb-3
                        [&_p]:text-base [&_p]:leading-relaxed
                        [&_strong]:text-white
                        [&_em]:italic
                        [&_ul]:list-disc [&_ul]:pl-6 [&_ul]:space-y-1
                        [&_blockquote]:border-l-2 [&_blockquote]:border-accent [&_blockquote]:pl-4 [&_blockquote]:italic">
                <?= $article['content'] ?>
            </div>
        </article>

        <?php if (!empty($autres)): ?>
            <hr class="border-border my-12">
            <h2 class="font-display font-bold text-xl text-white mb-5">À lire aussi</h2>
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <?php foreach ($autres as $a): ?>
                    <a href="/blog/<?= e($a['slug']) ?>" class="bg-surface border border-border rounded-xl p-5 hover:border-accent transition-colors">
                        <p class="text-text-dim text-xs mb-2"><?= date('d/m/Y', strtotime($a['date'])) ?></p>
                        <p class="font-display font-semibold text-white text-sm leading-snug hover:text-accent transition-colors"><?= e($a['title']) ?></p>
                    </a>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

    </div>
</section>
