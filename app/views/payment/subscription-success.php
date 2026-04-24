<section class="min-h-[60vh] flex items-center justify-center px-4">
    <div class="text-center max-w-md">
        <div class="w-20 h-20 rounded-full bg-emerald-500/10 flex items-center justify-center mx-auto mb-6">
            <svg class="w-10 h-10 text-emerald-400" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        </div>
        <h1 class="font-display font-bold text-2xl sm:text-3xl text-white mb-3">Abonnement activé !</h1>
        <?php if ($planLabel): ?>
            <p class="text-accent font-semibold mb-2"><?= e($planLabel) ?></p>
        <?php endif; ?>
        <?php if ($sub): ?>
            <p class="text-text-muted mb-6">Actif jusqu'au <strong class="text-white"><?= date('d/m/Y', strtotime($sub->date_fin)) ?></strong></p>
        <?php endif; ?>
        <p class="text-text-muted text-sm mb-6">Tu as maintenant accès à tout le catalogue en illimité.</p>
        <div class="flex flex-col sm:flex-row gap-3 justify-center">
            <a href="/catalogue" class="btn-primary">Explorer le catalogue</a>
            <a href="/mon-compte" class="btn-secondary">Ma bibliothèque</a>
        </div>
    </div>
</section>
