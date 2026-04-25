<section class="py-12 sm:py-20">
    <div class="max-w-6xl mx-auto px-4 sm:px-6">

        <div class="text-center mb-14">
            <h1 class="font-display font-extrabold text-3xl sm:text-4xl md:text-5xl text-white mb-3">Nos auteurs</h1>
            <p class="text-text-muted text-base sm:text-lg">Les voix qui font Variable. Du Congo au Canada, du Sénégal à la France.</p>
        </div>

        <?php if (empty($auteurs)): ?>
            <div class="bg-surface border border-border rounded-xl p-10 sm:p-14 text-center max-w-2xl mx-auto">
                <div class="text-5xl mb-4" aria-hidden="true">📚</div>
                <h2 class="font-display font-semibold text-xl text-white mb-2">Notre première génération arrive</h2>
                <p class="text-text-muted text-sm mb-6">Les premiers auteurs publient leurs livres. Bientôt, leurs noms et leurs voix seront ici.</p>
                <p class="text-text-muted text-sm mb-6">Tu veux être du voyage ?</p>
                <a href="/publier" class="btn-primary">Candidater pour publier</a>
            </div>
        <?php else: ?>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">
                <?php foreach ($auteurs as $a): ?>
                    <a href="/auteur/<?= e($a->slug) ?>" class="bg-surface border border-border rounded-xl p-6 hover:border-accent transition-colors text-center">
                        <?php if (!empty($a->photo_url_web)): ?>
                            <img src="<?= e($a->photo_url_web) ?>" alt="<?= e($a->prenom . ' ' . $a->nom) ?>" class="w-24 h-24 rounded-full object-cover mx-auto mb-4 border-2 border-accent">
                        <?php else: ?>
                            <div class="w-24 h-24 rounded-full bg-accent text-black flex items-center justify-center text-3xl font-bold font-display mx-auto mb-4"><?= e(mb_strtoupper(mb_substr($a->prenom, 0, 1))) ?></div>
                        <?php endif; ?>
                        <h2 class="font-display font-semibold text-white text-base mb-1"><?= e($a->nom_plume ?: $a->prenom . ' ' . $a->nom) ?></h2>
                        <?php if ($a->nom_plume): ?>
                            <p class="text-text-dim text-xs">alias <?= e($a->prenom . ' ' . $a->nom) ?></p>
                        <?php endif; ?>
                        <?php if (!empty($a->pays)): ?>
                            <p class="text-text-muted text-xs mt-1"><?= e($a->pays) ?></p>
                        <?php endif; ?>
                        <p class="text-accent text-xs mt-2 font-semibold"><?= (int) $a->nb_livres ?> livre<?= $a->nb_livres > 1 ? 's' : '' ?> publié<?= $a->nb_livres > 1 ? 's' : '' ?></p>
                        <?php if (!empty($a->bio)): ?>
                            <p class="text-text-muted text-sm mt-3 line-clamp-3"><?= e($a->bio) ?></p>
                        <?php endif; ?>
                        <span class="inline-block mt-4 text-accent text-sm">Découvrir →</span>
                    </a>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

    </div>
</section>
