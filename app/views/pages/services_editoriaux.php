<?php $icons = ['edit'=>'✏️','layout'=>'🧱','image'=>'🎨','message'=>'💬','package'=>'📦','plus'=>'➕']; ?>
<section class="py-10 sm:py-16">
    <div class="max-w-[1100px] mx-auto px-4 sm:px-6">

        <div class="text-center mb-12">
            <h1 class="font-display font-extrabold text-3xl sm:text-4xl md:text-5xl text-white">Publie un livre qui marque.</h1>
            <p class="text-text-muted text-base sm:text-lg mt-3 max-w-xl mx-auto">Relecture, mise en page, couverture, coaching. Notre équipe accompagne les auteurs africains francophones de l'idée à la publication.</p>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5 mb-12">
            <?php foreach ($services as $s): ?>
                <div class="bg-surface border border-border rounded-xl p-6">
                    <div class="text-3xl mb-3" aria-hidden="true"><?= $icons[$s->icon] ?? '📌' ?></div>
                    <h3 class="font-display font-semibold text-white text-lg mb-2"><?= e($s->nom) ?></h3>
                    <p class="text-text-muted text-sm line-clamp-3 mb-4"><?= e($s->description) ?></p>
                    <div class="flex items-center justify-between">
                        <?php if ($s->sur_devis): ?>
                            <span class="text-accent text-sm font-semibold">Sur devis</span>
                        <?php else: ?>
                            <span class="text-accent font-display font-bold"><?= number_format((float) $s->prix_usd, 0) ?>&nbsp;$</span>
                        <?php endif; ?>
                        <span class="text-text-dim text-xs"><?= e($s->duree_estimee) ?></span>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <div class="bg-surface border border-border rounded-xl p-7 text-center">
            <h2 class="font-display font-bold text-2xl text-white mb-2">Pas encore auteur sur la plateforme ?</h2>
            <p class="text-text-muted mb-5">Crée ton compte auteur en 2 minutes, puis commande le service qui te convient.</p>
            <div class="flex flex-wrap gap-3 justify-center">
                <a href="/auteur/candidater" class="btn-primary">Devenir auteur</a>
                <a href="/connexion" class="btn-secondary">J'ai déjà un compte</a>
            </div>
        </div>

    </div>
</section>
