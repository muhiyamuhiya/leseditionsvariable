<section class="py-8 sm:py-12">
    <div class="max-w-[1200px] mx-auto px-4 sm:px-6">

        <!-- En-tête profil -->
        <div class="flex items-center gap-4 mb-8">
            <?php if (!empty($user->avatar_url)): ?>
                <img src="<?= e($user->avatar_url) ?>" alt="" class="w-14 h-14 rounded-full object-cover border-2 border-accent">
            <?php else: ?>
                <div class="w-14 h-14 rounded-full bg-accent text-black flex items-center justify-center text-xl font-bold font-display"><?= e(mb_strtoupper(mb_substr($user->prenom, 0, 1))) ?></div>
            <?php endif; ?>
            <div>
                <h1 class="font-display font-bold text-2xl sm:text-3xl text-white"><?= e($user->prenom . ' ' . $user->nom) ?></h1>
                <p class="text-text-dim text-sm"><?= e($user->email) ?> &middot; Membre depuis <?= date('M Y', strtotime($user->created_at)) ?></p>
            </div>
            <a href="/mon-compte/profil" class="ml-auto text-accent text-sm hover:text-accent-hover transition-colors">Modifier mon profil</a>
        </div>

        <!-- Stats -->
        <div class="grid grid-cols-3 gap-4 mb-10">
            <div class="bg-surface border border-border rounded-lg p-4 text-center">
                <p class="font-display font-bold text-2xl text-accent"><?= (int) ($stats->nb_livres ?? 0) ?></p>
                <p class="text-text-dim text-xs mt-1">Livres lus</p>
            </div>
            <div class="bg-surface border border-border rounded-lg p-4 text-center">
                <p class="font-display font-bold text-2xl text-accent"><?= number_format((int) ($stats->pages_lues ?? 0)) ?></p>
                <p class="text-text-dim text-xs mt-1">Pages lues</p>
            </div>
            <div class="bg-surface border border-border rounded-lg p-4 text-center">
                <?php $heures = round((int) ($stats->temps_total ?? 0) / 3600, 1); ?>
                <p class="font-display font-bold text-2xl text-accent"><?= $heures ?></p>
                <p class="text-text-dim text-xs mt-1">Heures de lecture</p>
            </div>
        </div>

        <!-- Abonnement -->
        <div class="bg-surface border border-border rounded-lg p-5 sm:p-6 mb-10">
            <h2 class="font-display font-semibold text-lg text-white mb-3">Mon abonnement</h2>
            <?php if ($abonnement): ?>
                <div class="flex items-center justify-between flex-wrap gap-3">
                    <div>
                        <span class="inline-block bg-accent/20 text-accent text-xs font-semibold px-2.5 py-1 rounded"><?= e(ucfirst($abonnement->type)) ?></span>
                        <p class="text-text-muted text-sm mt-2">Actif jusqu'au <?= date('d/m/Y', strtotime($abonnement->date_fin)) ?></p>
                    </div>
                    <a href="/abonnement" class="btn-secondary text-sm">Gérer</a>
                </div>
            <?php else: ?>
                <p class="text-text-muted text-sm mb-3">Aucun abonnement actif.</p>
                <a href="/abonnement" class="btn-primary text-sm">Découvrir les offres</a>
            <?php endif; ?>
        </div>

        <!-- Ma bibliothèque -->
        <h2 class="font-display font-semibold text-xl text-white mb-4">Ma bibliothèque</h2>
        <?php if (empty($livres)): ?>
            <div class="bg-surface border border-border rounded-lg p-8 text-center">
                <p class="text-text-muted mb-4">Ta bibliothèque est vide pour le moment.</p>
                <a href="/catalogue" class="btn-primary text-sm">Explorer le catalogue</a>
            </div>
        <?php else: ?>
            <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-4">
                <?php foreach ($livres as $l): ?>
                <a href="/livre/<?= e($l->slug) ?>" class="card-book block">
                    <div class="card-book-cover aspect-[2/3] bg-gradient-to-br <?= book_cover_gradient($l->book_id) ?> flex flex-col items-center justify-between p-3 relative">
                        <p class="self-start text-[9px] font-medium tracking-wider uppercase text-accent/80"><?= e($l->category_nom ?? '') ?></p>
                        <p class="font-display font-semibold text-white text-center text-sm leading-snug px-1"><?= e($l->titre) ?></p>
                        <?php if ($l->pourcentage_complete > 0): ?>
                            <div class="w-full">
                                <div class="w-full bg-white/20 rounded-full h-1"><div class="bg-accent h-1 rounded-full" style="width:<?= min(100, $l->pourcentage_complete) ?>%"></div></div>
                                <p class="text-white/50 text-[9px] text-center mt-0.5"><?= number_format($l->pourcentage_complete, 0) ?>%</p>
                            </div>
                        <?php else: ?>
                            <span></span>
                        <?php endif; ?>
                    </div>
                    <div class="mt-2 px-0.5">
                        <p class="text-white text-[13px] font-medium truncate"><?= e($l->titre) ?></p>
                        <p class="text-text-dim text-[12px] mt-0.5 truncate"><?= e($l->author_display) ?></p>
                    </div>
                </a>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</section>
