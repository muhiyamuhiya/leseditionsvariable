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
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-10">
            <div class="bg-surface border border-border rounded-lg p-4 text-center">
                <p class="font-display font-bold text-2xl text-accent"><?= (int) ($nbBiblio ?? 0) ?></p>
                <p class="text-text-dim text-xs mt-1">Livres dans ma biblio</p>
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
            <a href="/mon-compte/favoris" class="bg-surface border border-border rounded-lg p-4 text-center hover:border-accent transition-colors group">
                <div class="flex items-center justify-center gap-2">
                    <svg class="w-5 h-5 text-accent" fill="currentColor" viewBox="0 0 24 24"><path d="M11.645 20.91l-.007-.003-.022-.012a15.247 15.247 0 01-.383-.218 25.18 25.18 0 01-4.244-3.17C4.688 15.36 2.25 12.174 2.25 8.25 2.25 5.322 4.714 3 7.688 3A5.5 5.5 0 0112 5.052 5.5 5.5 0 0116.313 3c2.973 0 5.437 2.322 5.437 5.25 0 3.925-2.438 7.111-4.739 9.256a25.175 25.175 0 01-4.244 3.17 15.247 15.247 0 01-.383.219l-.022.012-.007.004-.003.001a.752.752 0 01-.704 0l-.003-.001z"/></svg>
                    <p class="font-display font-bold text-2xl text-accent"><?= (int) ($nbFavoris ?? 0) ?></p>
                </div>
                <p class="text-text-dim text-xs mt-1 group-hover:text-accent transition-colors">Mes favoris</p>
            </a>
        </div>

        <!-- Abonnement -->
        <div class="bg-surface border border-border rounded-lg p-5 sm:p-6 mb-10">
            <h2 class="font-display font-semibold text-lg text-white mb-3">Mon abonnement</h2>
            <?php if ($abonnement): ?>
                <div class="flex items-center justify-between flex-wrap gap-3">
                    <div>
                        <span class="inline-block bg-accent/20 text-accent text-xs font-semibold px-2.5 py-1 rounded"><?= e(\App\Models\Subscription::PLANS[$abonnement->type]['label'] ?? ucfirst(str_replace('_',' ', $abonnement->type))) ?></span>
                        <p class="text-text-muted text-sm mt-2">Actif jusqu'au <?= date('d/m/Y', strtotime($abonnement->date_fin)) ?></p>
                    </div>
                    <a href="/mon-compte/abonnement" class="btn-secondary text-sm">Gérer</a>
                </div>
            <?php else: ?>
                <p class="text-text-muted text-sm mb-3">Aucun abonnement actif.</p>
                <a href="/abonnement" class="btn-primary text-sm">Découvrir les offres</a>
            <?php endif; ?>
        </div>

        <!-- Ma bibliothèque -->
        <h2 class="font-display font-semibold text-xl text-white mb-4">Ma bibliothèque</h2>
        <?php if (empty($livres)): ?>
            <div class="bg-surface border border-border rounded-xl p-8 sm:p-12 text-center">
                <div class="w-16 h-16 rounded-full bg-surface-2 flex items-center justify-center mx-auto mb-5">
                    <svg class="w-8 h-8 text-text-dim" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 006 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 016 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 016-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0018 18a8.967 8.967 0 00-6 2.292m0-14.25v14.25"/></svg>
                </div>
                <p class="text-text-muted mb-1 text-base">Ta bibliothèque est vide pour le moment.</p>
                <p class="text-text-dim text-sm mb-6 max-w-md mx-auto">Achète des livres à l'unité ou souscris à un abonnement pour commencer ta collection.</p>
                <div class="flex flex-wrap gap-3 justify-center">
                    <a href="/catalogue" class="btn-primary">Explorer le catalogue</a>
                    <a href="/abonnement" class="btn-secondary">Découvrir l'abonnement</a>
                </div>
            </div>
        <?php else: ?>
            <?php
            // Map des classes badge selon couleur
            $badgeClasses = [
                'amber' => 'bg-amber-500/20 text-amber-300 border-amber-500/30',
                'blue'  => 'bg-blue-500/20 text-blue-300 border-blue-500/30',
                'gray'  => 'bg-gray-500/20 text-gray-400 border-gray-500/30',
            ];
            ?>
            <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-5">
                <?php foreach ($livres as $l): ?>
                    <?php
                    $coverUrl = book_cover_url($l);
                    $access = $l->access_status;
                    $badgeClass = $badgeClasses[$access['badge_color']] ?? $badgeClasses['gray'];
                    $isLocked = !$access['can_read'];
                    ?>
                    <div class="flex flex-col">
                        <a href="<?= e($access['cta_url']) ?>" class="block group <?= $isLocked ? '' : '' ?>">
                            <div class="relative aspect-[2/3] overflow-hidden rounded-lg <?= $coverUrl ? '' : 'bg-gradient-to-br ' . book_cover_gradient($l->book_id) ?> <?= $isLocked ? 'opacity-70 grayscale' : '' ?>">
                                <?php if ($coverUrl): ?>
                                    <img src="<?= e($coverUrl) ?>" alt="<?= e($l->titre) ?>" class="w-full h-full object-cover" loading="lazy">
                                    <div class="absolute inset-0 bg-gradient-to-b from-black/30 via-transparent to-black/50"></div>
                                <?php else: ?>
                                    <div class="w-full h-full flex flex-col items-center justify-between p-3">
                                        <p class="self-start text-[9px] font-medium tracking-wider uppercase text-accent/80"><?= e($l->category_nom ?? '') ?></p>
                                        <p class="font-display font-semibold text-white text-center text-sm leading-snug px-1 drop-shadow-lg"><?= e($l->titre) ?></p>
                                        <span></span>
                                    </div>
                                <?php endif; ?>

                                <!-- Badge état d'accès -->
                                <span class="absolute top-2 left-2 px-2 py-0.5 rounded-full text-[10px] font-semibold border backdrop-blur-sm <?= $badgeClass ?>">
                                    <?= e($access['badge_label']) ?>
                                </span>

                                <!-- Cadenas si verrouillé -->
                                <?php if ($isLocked): ?>
                                    <div class="absolute inset-0 flex items-center justify-center">
                                        <div class="w-12 h-12 rounded-full bg-black/60 backdrop-blur-sm flex items-center justify-center">
                                            <svg class="w-6 h-6 text-gray-300" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z"/></svg>
                                        </div>
                                    </div>
                                <?php endif; ?>

                                <!-- Barre de progression -->
                                <?php if (!$isLocked && ($l->pourcentage_complete ?? 0) > 0): ?>
                                    <div class="absolute bottom-2 left-2 right-2">
                                        <div class="w-full bg-black/40 rounded-full h-1"><div class="bg-accent h-1 rounded-full" style="width:<?= min(100, $l->pourcentage_complete) ?>%"></div></div>
                                        <p class="text-white text-[10px] text-center mt-0.5 drop-shadow"><?= number_format($l->pourcentage_complete, 0) ?>%</p>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </a>

                        <!-- Titre + auteur + CTA -->
                        <div class="mt-2 px-0.5 flex flex-col flex-grow">
                            <a href="/livre/<?= e($l->slug) ?>" class="text-white text-[13px] font-medium leading-snug line-clamp-2 hover:text-accent transition-colors"><?= e($l->titre) ?></a>
                            <p class="text-text-dim text-[12px] mt-0.5 truncate"><?= e($l->author_display) ?></p>
                            <a href="<?= e($access['cta_url']) ?>"
                               class="mt-2 inline-flex items-center gap-1 text-xs font-medium <?= $isLocked ? 'text-text-dim hover:text-accent' : 'text-accent hover:text-accent-hover' ?> transition-colors">
                                <?= e($access['cta_label']) ?>
                                <span aria-hidden="true">→</span>
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</section>
