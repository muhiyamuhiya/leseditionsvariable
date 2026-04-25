<?php
$displayName = $author->nom_plume ?: ($author->prenom . ' ' . $author->nom);
$photoUrl = author_photo_url($author);
?>

<!-- SECTION 1 : HEADER AUTEUR -->
<section class="bg-surface py-10 sm:py-16">
    <div class="max-w-[1200px] mx-auto px-4 sm:px-6">
        <div class="flex flex-col sm:flex-row items-center sm:items-start gap-6 sm:gap-10">
            <!-- Photo -->
            <?php if ($photoUrl): ?>
                <img src="<?= e($photoUrl) ?>" alt="<?= e($displayName) ?>"
                     class="w-32 h-32 sm:w-44 sm:h-44 rounded-full object-cover border-2 border-accent shadow-xl flex-shrink-0">
            <?php else: ?>
                <div class="w-32 h-32 sm:w-44 sm:h-44 rounded-full bg-gradient-to-br from-accent to-amber-700 flex items-center justify-center text-4xl sm:text-5xl font-display font-bold text-black shadow-xl flex-shrink-0">
                    <?= e(author_initials($author)) ?>
                </div>
            <?php endif; ?>

            <!-- Infos -->
            <div class="text-center sm:text-left">
                <p class="text-[10px] font-semibold tracking-widest uppercase text-accent mb-2">Auteur</p>
                <h1 class="font-display font-bold text-3xl sm:text-4xl md:text-5xl text-white"><?= e($displayName) ?></h1>

                <?php if ($author->pays_origine || $author->ville_residence): ?>
                <p class="text-text-muted text-sm mt-2 flex items-center justify-center sm:justify-start gap-1.5">
                    <svg class="w-4 h-4 text-text-dim" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 0115 0z"/></svg>
                    <?= e(implode(' · ', array_filter([$author->ville_residence ?? '', $author->pays_origine ?? '']))) ?>
                </p>
                <?php endif; ?>

                <div class="flex items-center justify-center sm:justify-start gap-6 mt-5">
                    <div class="text-center">
                        <p class="font-display font-bold text-xl text-accent"><?= $totalBooks ?></p>
                        <p class="text-text-dim text-xs">livres</p>
                    </div>
                    <div class="text-center">
                        <p class="font-display font-bold text-xl text-white"><?= number_format($totalLectures) ?></p>
                        <p class="text-text-dim text-xs">lectures</p>
                    </div>
                    <div class="text-center">
                        <p class="font-display font-bold text-xl text-white"><?= number_format($totalVentes) ?></p>
                        <p class="text-text-dim text-xs">ventes</p>
                    </div>
                </div>

                <!-- Réseaux sociaux -->
                <?php
                $socials = array_filter([
                    'site_web'       => $author->site_web ?? null,
                    'facebook_url'   => $author->facebook_url ?? null,
                    'instagram_url'  => $author->instagram_url ?? null,
                    'twitter_x_url'  => $author->twitter_x_url ?? null,
                    'linkedin_url'   => $author->linkedin_url ?? null,
                ]);
                ?>
                <?php if ($socials): ?>
                <div class="flex items-center justify-center sm:justify-start gap-4 mt-5">
                    <?php if (!empty($socials['site_web'])): ?>
                        <a href="<?= e($socials['site_web']) ?>" target="_blank" class="text-text-dim hover:text-accent transition-colors"><svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 21a9.004 9.004 0 008.716-6.747M12 21a9.004 9.004 0 01-8.716-6.747M12 21c2.485 0 4.5-4.03 4.5-9S14.485 3 12 3m0 18c-2.485 0-4.5-4.03-4.5-9S9.515 3 12 3m0 0a8.997 8.997 0 017.843 4.582M12 3a8.997 8.997 0 00-7.843 4.582m15.686 0A11.953 11.953 0 0112 10.5c-2.998 0-5.74-1.1-7.843-2.918m15.686 0A8.959 8.959 0 0121 12c0 .778-.099 1.533-.284 2.253m0 0A17.919 17.919 0 0112 16.5c-3.162 0-6.133-.815-8.716-2.247m0 0A9.015 9.015 0 013 12c0-1.605.42-3.113 1.157-4.418"/></svg></a>
                    <?php endif; ?>
                    <?php if (!empty($socials['facebook_url'])): ?>
                        <a href="<?= e($socials['facebook_url']) ?>" target="_blank" class="text-text-dim hover:text-accent transition-colors"><svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg></a>
                    <?php endif; ?>
                    <?php if (!empty($socials['instagram_url'])): ?>
                        <a href="<?= e($socials['instagram_url']) ?>" target="_blank" class="text-text-dim hover:text-accent transition-colors"><svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zM12 0C8.741 0 8.333.014 7.053.072 2.695.272.273 2.69.073 7.052.014 8.333 0 8.741 0 12c0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98C8.333 23.986 8.741 24 12 24c3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98C15.668.014 15.259 0 12 0zm0 5.838a6.162 6.162 0 100 12.324 6.162 6.162 0 000-12.324zM12 16a4 4 0 110-8 4 4 0 010 8zm6.406-11.845a1.44 1.44 0 100 2.881 1.44 1.44 0 000-2.881z"/></svg></a>
                    <?php endif; ?>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>

<!-- SECTION 2 : BIOGRAPHIE -->
<?php $bio = $author->biographie_longue ?: ($author->biographie_courte ?? ''); ?>
<?php if ($bio): ?>
<section class="py-10 sm:py-14">
    <div class="max-w-[800px] mx-auto px-4 sm:px-6">
        <h2 class="font-display font-semibold text-xl sm:text-2xl text-white mb-6">À propos de <?= e($author->prenom ?? $displayName) ?></h2>
        <div class="text-text-muted text-[15px] leading-[1.8] whitespace-pre-line"><?= e($bio) ?></div>
    </div>
</section>
<?php endif; ?>

<!-- SECTION 3 : LIVRES -->
<section class="py-10 sm:py-14 bg-surface">
    <div class="max-w-[1400px] mx-auto px-4 sm:px-6">
        <h2 class="font-display font-semibold text-xl sm:text-2xl text-white mb-6">Les livres de <?= e($author->prenom ?? $displayName) ?></h2>

        <?php if (empty($books)): ?>
            <p class="text-text-dim text-sm">Cet auteur n'a pas encore publié de livre sur la plateforme.</p>
        <?php else: ?>
            <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-4 sm:gap-5">
                <?php foreach ($books as $livre): ?>
                <?php $coverUrl = book_cover_url($livre); $tierBadge = book_tier_badge(\App\Lib\Auth::user(), $livre); ?>
                <a href="/livre/<?= e($livre->slug) ?>" class="group block">
                    <div class="relative aspect-[2/3] overflow-hidden rounded-lg bg-gradient-to-br <?= book_cover_gradient($livre->id) ?> transition-transform duration-300 group-hover:scale-105 group-hover:ring-2 group-hover:ring-accent">
                        <?php if ($coverUrl): ?>
                            <img src="<?= e($coverUrl) ?>" alt="<?= e($livre->titre) ?>" class="w-full h-full object-cover" loading="lazy">
                            <div class="absolute inset-0 bg-gradient-to-b from-black/40 via-transparent to-black/50"></div>
                        <?php else: ?>
                            <div class="w-full h-full flex flex-col items-center justify-between p-4 text-center">
                                <div class="w-10 h-0.5 bg-accent/60"></div>
                                <p class="font-display font-semibold text-white text-sm drop-shadow-lg"><?= e($livre->titre) ?></p>
                                <div class="w-10 h-0.5 bg-accent/60"></div>
                            </div>
                        <?php endif; ?>
                        <span class="absolute top-2 left-2 text-[9px] font-semibold uppercase tracking-wider text-accent bg-black/60 backdrop-blur-sm px-1.5 py-0.5 rounded"><?= e($livre->category_nom ?? '') ?></span>
                        <?php if ($tierBadge): ?>
                            <span class="absolute top-2 right-2 z-10 px-2 py-0.5 rounded-full text-[10px] font-bold border backdrop-blur-sm <?= $tierBadge['classes'] ?>"><?= $tierBadge['icon'] ?> <?= e($tierBadge['label']) ?></span>
                        <?php endif; ?>
                    </div>
                    <div class="mt-2.5 px-0.5">
                        <p class="text-white text-[13px] sm:text-sm font-medium leading-snug line-clamp-2 group-hover:text-accent transition-colors"><?= e($livre->titre) ?></p>
                        <p class="text-accent text-sm font-semibold mt-1"><?= number_format($livre->prix_unitaire_usd, 2) ?>&nbsp;$</p>
                    </div>
                </a>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</section>
