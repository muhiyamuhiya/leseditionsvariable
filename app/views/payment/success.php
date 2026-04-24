<section class="min-h-[60vh] flex items-center justify-center px-4">
    <div class="text-center max-w-md">
        <div class="w-20 h-20 rounded-full bg-emerald-500/10 flex items-center justify-center mx-auto mb-6">
            <svg class="w-10 h-10 text-emerald-400" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        </div>
        <h1 class="font-display font-bold text-2xl sm:text-3xl text-white mb-3">Paiement confirmé !</h1>

        <?php if ($book): ?>
            <p class="text-text-muted mb-2">Tu as acheté :</p>
            <p class="text-white font-semibold text-lg mb-1"><?= e($book->titre) ?></p>
            <p class="text-text-dim text-sm mb-6">par <?= e($book->author_display ?? '') ?></p>
            <div class="flex flex-col sm:flex-row gap-3 justify-center">
                <a href="/lire/<?= e($book->slug) ?>" class="btn-primary">Lire maintenant</a>
                <a href="/mon-compte" class="btn-secondary">Ma bibliothèque</a>
            </div>
        <?php else: ?>
            <p class="text-text-muted mb-6">Ton livre a été ajouté à ta bibliothèque.</p>
            <div class="flex flex-col sm:flex-row gap-3 justify-center">
                <a href="/mon-compte" class="btn-primary">Ma bibliothèque</a>
                <a href="/catalogue" class="btn-secondary">Continuer à explorer</a>
            </div>
        <?php endif; ?>

        <p class="text-text-dim text-xs mt-8">Un email de confirmation a été envoyé à ton adresse.</p>
    </div>
</section>
