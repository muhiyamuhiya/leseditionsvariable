<?php $authorName = book_author_name($book); ?>

<!-- Schema.org JSON-LD -->
<script type="application/ld+json">
{
    "@context": "https://schema.org",
    "@type": "Book",
    "name": <?= json_encode($book->titre) ?>,
    "author": { "@type": "Person", "name": <?= json_encode($authorName) ?> },
    "publisher": { "@type": "Organization", "name": "Les éditions Variable" },
    "inLanguage": <?= json_encode($book->langue ?? 'fr') ?>,
    "numberOfPages": <?= (int) $book->nombre_pages ?>,
    "offers": {
        "@type": "Offer",
        "price": "<?= number_format($book->prix_unitaire_usd, 2, '.', '') ?>",
        "priceCurrency": "USD"
    }
}
</script>

<!-- ============================================================
     SECTION 1 : HEADER DU LIVRE
     ============================================================ -->
<section class="bg-surface">
    <div class="max-w-[1200px] mx-auto px-4 sm:px-6 py-8 sm:py-12 md:py-16">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8 md:gap-12">

            <!-- Couverture -->
            <div class="md:col-span-1 flex flex-col items-center">
                <?php $bookCoverUrl = book_cover_url($book); ?>
                <div class="relative w-full max-w-[280px] aspect-[2/3] overflow-hidden rounded-lg bg-gradient-to-br <?= book_cover_gradient($book->id) ?> shadow-2xl">
                    <?php if ($bookCoverUrl): ?>
                        <img src="<?= e($bookCoverUrl) ?>" alt="<?= e($book->titre) ?>" class="w-full h-full object-cover">
                    <?php else: ?>
                        <div class="w-full h-full flex flex-col items-center justify-between p-6 text-center">
                            <div class="w-16 h-0.5 bg-accent/60"></div>
                            <div class="flex flex-col items-center">
                                <span class="text-[9px] uppercase tracking-[0.2em] text-accent/80 mb-3"><?= e($book->category_nom ?? '') ?></span>
                                <p class="text-white font-display font-bold text-xl sm:text-2xl leading-tight drop-shadow-lg"><?= e($book->titre) ?></p>
                            </div>
                            <div class="flex flex-col items-center">
                                <span class="text-white/60 text-xs italic mb-2"><?= e($authorName) ?></span>
                                <div class="w-16 h-0.5 bg-accent/60"></div>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="flex flex-col gap-2 mt-5 w-full max-w-[280px]">
                    <a href="/lire/<?= e($book->slug) ?>/extrait" class="btn-secondary w-full text-center text-sm py-2.5">Lire l'extrait gratuit</a>

                    <?php if ($user): ?>
                    <button x-data="{ favori: <?= $estFavori ? 'true' : 'false' ?>, loading: false }"
                            @click="if(loading) return; loading=true;
                                fetch('/livre/<?= e($book->slug) ?>/favori', {
                                    method:'POST',
                                    headers:{'X-Requested-With':'XMLHttpRequest','X-CSRF-Token':'<?= csrf_token() ?>'}
                                }).then(r=>r.json()).then(d=>{favori=d.favori;loading=false}).catch(()=>loading=false)"
                            class="w-full text-center text-sm py-2.5 border border-border rounded hover:border-accent transition-colors flex items-center justify-center gap-2 text-text-muted hover:text-white">
                        <svg x-show="!favori" class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12z"/></svg>
                        <svg x-show="favori" x-cloak class="w-4 h-4 text-rose-500" fill="currentColor" viewBox="0 0 24 24"><path d="M11.645 20.91l-.007-.003-.022-.012a15.247 15.247 0 01-.383-.218 25.18 25.18 0 01-4.244-3.17C4.688 15.36 2.25 12.174 2.25 8.25 2.25 5.322 4.714 3 7.688 3A5.5 5.5 0 0112 5.052 5.5 5.5 0 0116.313 3c2.973 0 5.437 2.322 5.437 5.25 0 3.925-2.438 7.111-4.739 9.256a25.175 25.175 0 01-4.244 3.17 15.247 15.247 0 01-.383.219l-.022.012-.007.004-.003.001a.752.752 0 01-.704 0l-.003-.001z"/></svg>
                        <span x-text="favori ? 'Dans mes favoris' : 'Ajouter aux favoris'"></span>
                    </button>
                    <?php else: ?>
                    <a href="/connexion?redirect=/livre/<?= e($book->slug) ?>"
                       class="w-full text-center text-sm py-2.5 border border-border rounded hover:border-accent transition-colors flex items-center justify-center gap-2 text-text-muted hover:text-white">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12z"/></svg>
                        Ajouter aux favoris
                    </a>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Infos -->
            <div class="md:col-span-2">
                <!-- Catégorie -->
                <?php if ($book->category_nom): ?>
                    <a href="/catalogue?categorie=<?= e($book->category_slug) ?>" class="text-[11px] font-medium tracking-widest uppercase text-accent hover:text-accent-hover transition-colors">
                        <?= e($book->category_nom) ?>
                    </a>
                <?php endif; ?>

                <!-- Titre -->
                <h1 class="font-display font-bold text-3xl sm:text-4xl md:text-5xl text-white mt-2 leading-tight"><?= e($book->titre) ?></h1>
                <?php if ($book->sous_titre): ?>
                    <p class="text-text-muted text-lg mt-1"><?= e($book->sous_titre) ?></p>
                <?php endif; ?>

                <!-- Auteur -->
                <p class="mt-3 text-base sm:text-lg">
                    <span class="text-text-muted">par </span>
                    <a href="/auteur/<?= e($book->author_slug) ?>" class="text-accent font-medium hover:text-accent-hover transition-colors"><?= e($authorName) ?></a>
                </p>

                <!-- Note -->
                <div class="flex items-center gap-2 mt-4">
                    <?= stars_html($noteMoyenne) ?>
                    <span class="text-text-dim text-sm">(<?= count($avis) ?> avis)</span>
                </div>

                <!-- Métadonnées en ligne -->
                <div class="flex flex-wrap items-center gap-x-4 gap-y-1 mt-4 text-sm text-text-dim">
                    <?php if ($book->nombre_pages): ?>
                        <span><?= $book->nombre_pages ?> pages</span>
                        <span class="text-border">|</span>
                    <?php endif; ?>
                    <span><?= e(ucfirst($book->langue ?? 'Français')) ?></span>
                    <?php if ($book->annee_publication): ?>
                        <span class="text-border">|</span>
                        <span><?= $book->annee_publication ?></span>
                    <?php endif; ?>
                </div>

                <!-- Prix -->
                <div class="mt-6">
                    <span class="font-display font-bold text-3xl sm:text-4xl text-accent"><?= number_format($book->prix_unitaire_usd, 2) ?>&nbsp;$</span>
                    <?php if ($book->accessible_abonnement): ?>
                        <span class="ml-3 text-sm text-emerald-400 font-medium">Inclus avec l'abonnement</span>
                    <?php endif; ?>
                </div>

                <!-- Boutons d'action -->
                <div class="flex flex-wrap gap-3 mt-6">
                    <?php if (!$user): ?>
                        <a href="/connexion" class="btn-primary text-base px-8 py-3">Se connecter pour acheter</a>
                        <a href="/abonnement" class="btn-secondary text-base px-6 py-3">S'abonner et lire illimité</a>
                    <?php elseif ($aAchete || $estAbonne): ?>
                        <?php if ($progression && $progression->derniere_page_lue > 1): ?>
                            <a href="/lire/<?= e($book->slug) ?>" class="btn-primary text-base px-8 py-3">
                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M8 5v14l11-7z"/></svg>
                                Continuer la lecture (p.<?= $progression->derniere_page_lue ?>)
                            </a>
                        <?php else: ?>
                            <a href="/lire/<?= e($book->slug) ?>" class="btn-primary text-base px-8 py-3">
                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M8 5v14l11-7z"/></svg>
                                Commencer la lecture
                            </a>
                        <?php endif; ?>
                    <?php else: ?>
                        <a href="/achat/livre/<?= $book->id ?>" class="btn-primary text-base px-8 py-3">Acheter pour <?= number_format($book->prix_unitaire_usd, 2) ?>&nbsp;$</a>
                        <?php if ($book->accessible_abonnement): ?>
                            <a href="/abonnement" class="btn-secondary text-base px-6 py-3">Ou lire avec l'abonnement</a>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>

                <!-- Progression si existante -->
                <?php if ($progression && $progression->pourcentage_complete > 0): ?>
                    <div class="mt-4 max-w-sm">
                        <div class="flex justify-between text-xs text-text-dim mb-1">
                            <span>Progression</span>
                            <span><?= number_format($progression->pourcentage_complete, 0) ?>%</span>
                        </div>
                        <div class="w-full bg-border rounded-full h-1.5">
                            <div class="bg-accent h-1.5 rounded-full" style="width: <?= min(100, $progression->pourcentage_complete) ?>%"></div>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Partage -->
                <div class="flex items-center gap-4 mt-6 pt-6 border-t border-border">
                    <span class="text-text-dim text-xs uppercase tracking-wider">Partager</span>
                    <a href="https://www.facebook.com/sharer/sharer.php?u=<?= urlencode(url('/livre/' . $book->slug)) ?>" target="_blank" rel="noopener" class="text-text-dim hover:text-accent transition-colors">
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg>
                    </a>
                    <a href="https://twitter.com/intent/tweet?text=<?= urlencode($book->titre . ' par ' . $authorName) ?>&url=<?= urlencode(url('/livre/' . $book->slug)) ?>" target="_blank" rel="noopener" class="text-text-dim hover:text-accent transition-colors">
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/></svg>
                    </a>
                    <a href="https://wa.me/?text=<?= urlencode($book->titre . ' — ' . url('/livre/' . $book->slug)) ?>" target="_blank" rel="noopener" class="text-text-dim hover:text-accent transition-colors">
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
                    </a>
                    <a href="mailto:?subject=<?= urlencode($book->titre) ?>&body=<?= urlencode('Découvre ce livre : ' . url('/livre/' . $book->slug)) ?>" class="text-text-dim hover:text-accent transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 01-2.25 2.25h-15a2.25 2.25 0 01-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25m19.5 0v.243a2.25 2.25 0 01-1.07 1.916l-7.5 4.615a2.25 2.25 0 01-2.36 0L3.32 8.91a2.25 2.25 0 01-1.07-1.916V6.75"/></svg>
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ============================================================
     SECTION 2 : DESCRIPTION
     ============================================================ -->
<section class="py-10 sm:py-14">
    <div class="max-w-[800px] mx-auto px-4 sm:px-6">
        <h2 class="font-display font-semibold text-xl sm:text-2xl text-white mb-6">À propos du livre</h2>
        <div class="text-text-muted text-[15px] sm:text-base leading-[1.8] whitespace-pre-line"><?= e($book->description_longue ?? $book->description_courte) ?></div>
    </div>
</section>

<!-- ============================================================
     SECTION 3 : INFOS DE PUBLICATION
     ============================================================ -->
<section class="bg-surface py-8 sm:py-10">
    <div class="max-w-[800px] mx-auto px-4 sm:px-6">
        <h2 class="font-display font-semibold text-lg text-white mb-5">Informations</h2>
        <div class="grid grid-cols-2 sm:grid-cols-3 gap-4 text-sm">
            <div>
                <p class="text-text-dim text-xs uppercase tracking-wider mb-1">Éditeur</p>
                <p class="text-white"><?= e($book->editeur ?? 'Les éditions Variable') ?></p>
            </div>
            <div>
                <p class="text-text-dim text-xs uppercase tracking-wider mb-1">Publication</p>
                <p class="text-white"><?= $book->date_publication ? date('d/m/Y', strtotime($book->date_publication)) : '-' ?></p>
            </div>
            <div>
                <p class="text-text-dim text-xs uppercase tracking-wider mb-1">Pages</p>
                <p class="text-white"><?= $book->nombre_pages ?? '-' ?></p>
            </div>
            <div>
                <p class="text-text-dim text-xs uppercase tracking-wider mb-1">Langue</p>
                <p class="text-white"><?= e(ucfirst($book->langue ?? 'Français')) ?></p>
            </div>
            <?php if ($book->isbn): ?>
            <div>
                <p class="text-text-dim text-xs uppercase tracking-wider mb-1">ISBN</p>
                <p class="text-white"><?= e($book->isbn) ?></p>
            </div>
            <?php endif; ?>
            <?php if ($book->category_nom): ?>
            <div>
                <p class="text-text-dim text-xs uppercase tracking-wider mb-1">Catégorie</p>
                <a href="/catalogue?categorie=<?= e($book->category_slug) ?>" class="text-accent hover:text-accent-hover transition-colors"><?= e($book->category_nom) ?></a>
            </div>
            <?php endif; ?>
        </div>
    </div>
</section>

<!-- ============================================================
     SECTION 4 : DU MÊME AUTEUR + SIMILAIRES
     ============================================================ -->
<?php if (!empty($memeAuteur)): ?>
<section class="py-10 sm:py-14">
    <div class="max-w-[1400px] mx-auto px-4 sm:px-6">
        <div class="flex items-center gap-3 mb-4">
            <h2 class="font-display font-semibold text-xl sm:text-2xl text-white">Du même auteur</h2>
            <a href="/auteur/<?= e($book->author_slug) ?>" class="ml-auto text-accent text-sm font-medium hover:text-accent-hover transition-colors">Voir tout &rarr;</a>
        </div>
        <div class="carousel-container">
            <?php foreach ($memeAuteur as $l): ?>
            <?php $lCover = book_cover_url($l); ?>
            <a href="/livre/<?= e($l->slug) ?>" class="group block flex-shrink-0" style="width:160px;min-width:160px;">
                <div class="relative aspect-[2/3] overflow-hidden rounded-lg bg-gradient-to-br <?= book_cover_gradient($l->id) ?> transition-transform duration-300 group-hover:scale-105 group-hover:ring-2 group-hover:ring-accent">
                    <?php if ($lCover): ?>
                        <img src="<?= e($lCover) ?>" alt="<?= e($l->titre) ?>" class="w-full h-full object-cover" loading="lazy">
                        <div class="absolute inset-0 bg-gradient-to-b from-black/40 via-transparent to-black/50"></div>
                    <?php else: ?>
                        <div class="w-full h-full flex flex-col items-center justify-between p-4 text-center">
                            <div class="w-10 h-0.5 bg-accent/60"></div>
                            <p class="font-display font-semibold text-white text-sm leading-snug drop-shadow-lg"><?= e($l->titre) ?></p>
                            <div class="w-10 h-0.5 bg-accent/60"></div>
                        </div>
                    <?php endif; ?>
                    <span class="absolute top-2 left-2 text-[9px] font-semibold uppercase tracking-wider text-accent bg-black/60 backdrop-blur-sm px-1.5 py-0.5 rounded"><?= e($l->category_nom ?? '') ?></span>
                </div>
                <div class="mt-2.5 px-0.5">
                    <p class="text-white text-[13px] font-medium line-clamp-2 group-hover:text-accent transition-colors"><?= e($l->titre) ?></p>
                    <p class="text-text-dim text-[11px] mt-1 truncate"><?= e(book_author_name($l)) ?></p>
                </div>
            </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<?php if (!empty($similaires)): ?>
<section class="py-10 sm:py-14 bg-surface">
    <div class="max-w-[1400px] mx-auto px-4 sm:px-6">
        <h2 class="font-display font-semibold text-xl sm:text-2xl text-white mb-4">Vous aimerez aussi</h2>
        <div class="carousel-container">
            <?php foreach ($similaires as $l): ?>
            <?php $lCover = book_cover_url($l); ?>
            <a href="/livre/<?= e($l->slug) ?>" class="group block flex-shrink-0" style="width:160px;min-width:160px;">
                <div class="relative aspect-[2/3] overflow-hidden rounded-lg bg-gradient-to-br <?= book_cover_gradient($l->id) ?> transition-transform duration-300 group-hover:scale-105 group-hover:ring-2 group-hover:ring-accent">
                    <?php if ($lCover): ?>
                        <img src="<?= e($lCover) ?>" alt="<?= e($l->titre) ?>" class="w-full h-full object-cover" loading="lazy">
                        <div class="absolute inset-0 bg-gradient-to-b from-black/40 via-transparent to-black/50"></div>
                    <?php else: ?>
                        <div class="w-full h-full flex flex-col items-center justify-between p-4 text-center">
                            <div class="w-10 h-0.5 bg-accent/60"></div>
                            <p class="font-display font-semibold text-white text-sm leading-snug drop-shadow-lg"><?= e($l->titre) ?></p>
                            <div class="w-10 h-0.5 bg-accent/60"></div>
                        </div>
                    <?php endif; ?>
                    <span class="absolute top-2 left-2 text-[9px] font-semibold uppercase tracking-wider text-accent bg-black/60 backdrop-blur-sm px-1.5 py-0.5 rounded"><?= e($l->category_nom ?? '') ?></span>
                </div>
                <div class="mt-2.5 px-0.5">
                    <p class="text-white text-[13px] font-medium line-clamp-2 group-hover:text-accent transition-colors"><?= e($l->titre) ?></p>
                    <p class="text-text-dim text-[11px] mt-1 truncate"><?= e(book_author_name($l)) ?></p>
                </div>
            </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- ============================================================
     SECTION 5 : AVIS
     ============================================================ -->
<section class="py-10 sm:py-14">
    <div class="max-w-[800px] mx-auto px-4 sm:px-6">
        <div class="flex items-center justify-between mb-6">
            <h2 class="font-display font-semibold text-xl sm:text-2xl text-white">Avis des lecteurs</h2>
            <?php if ($noteMoyenne > 0): ?>
                <div class="flex items-center gap-2">
                    <?= stars_html($noteMoyenne) ?>
                    <span class="text-accent font-semibold"><?= $noteMoyenne ?>/5</span>
                </div>
            <?php endif; ?>
        </div>

        <?php
        // Flash messages UNIQUEMENT liés aux avis (pas les "Bienvenue" de connexion)
        $avisSuccess = flash('avis_success');
        $avisError = flash('avis_error');
        ?>
        <?php if ($avisSuccess): ?>
            <div class="bg-emerald-500/10 border border-emerald-500/30 text-emerald-400 px-4 py-3 rounded-lg mb-6 text-sm"><?= e($avisSuccess) ?></div>
        <?php endif; ?>
        <?php if ($avisError): ?>
            <div class="bg-red-500/10 border border-red-500/30 text-red-400 px-4 py-3 rounded-lg mb-6 text-sm"><?= e($avisError) ?></div>
        <?php endif; ?>

        <!-- CAS 1 : Non connecté -->
        <?php if (!$user): ?>
            <div class="bg-surface border border-border rounded-lg p-5 sm:p-6 mb-8 text-center">
                <p class="text-text-muted text-sm mb-4">Connecte-toi pour laisser ton avis sur ce livre.</p>
                <a href="/connexion?redirect=/livre/<?= e($book->slug) ?>" class="btn-primary text-sm">Se connecter</a>
            </div>

        <!-- CAS 2 : Connecté mais n'a ni acheté ni abonnement -->
        <?php elseif (!$aAchete && !$estAbonne): ?>
            <div class="bg-surface border border-border rounded-lg p-5 sm:p-6 mb-8 text-center">
                <p class="text-text-muted text-sm mb-4">Seuls les lecteurs ayant lu ce livre peuvent laisser un avis.</p>
                <div class="flex flex-wrap justify-center gap-3">
                    <a href="/achat/livre/<?= $book->id ?>" class="btn-primary text-sm">Acheter le livre</a>
                    <a href="/abonnement" class="btn-secondary text-sm">S'abonner</a>
                </div>
            </div>

        <!-- CAS 3 : Connecté + a déjà noté -->
        <?php elseif ($aDejaNote): ?>
            <div class="bg-surface border border-border rounded-lg p-5 sm:p-6 mb-8">
                <p class="text-text-muted text-sm">Tu as déjà laissé un avis pour ce livre. Merci !</p>
            </div>

        <!-- CAS 4 : Connecté + peut noter -->
        <?php else: ?>
            <form action="/livre/<?= e($book->slug) ?>/avis" method="POST" class="bg-surface border border-border rounded-lg p-5 sm:p-6 mb-8">
                <?= csrf_field() ?>
                <p class="text-white font-medium mb-4">Laisser un avis</p>

                <div class="flex items-center gap-1 mb-4" x-data="{ note: 5 }">
                    <span class="text-text-dim text-sm mr-2">Note :</span>
                    <?php for ($i = 1; $i <= 5; $i++): ?>
                        <button type="button" @click="note = <?= $i ?>"
                                :class="note >= <?= $i ?> ? 'text-accent' : 'text-border'"
                                class="transition-colors">
                            <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                        </button>
                    <?php endfor; ?>
                    <input type="hidden" name="note" :value="note">
                </div>

                <input type="text" name="titre_avis" placeholder="Titre de ton avis (optionnel)" maxlength="200"
                       class="w-full bg-surface-2 border border-border rounded px-4 py-2.5 text-sm text-white outline-none focus:border-accent placeholder:text-text-dim mb-3">
                <textarea name="commentaire" rows="3" placeholder="Ton commentaire..." required minlength="10" maxlength="2000"
                          class="w-full bg-surface-2 border border-border rounded px-4 py-2.5 text-sm text-white outline-none focus:border-accent placeholder:text-text-dim resize-none mb-3"></textarea>
                <p class="text-text-dim text-xs mb-4">Ton avis sera visible après modération.</p>
                <button type="submit" class="btn-primary text-sm">Publier mon avis</button>
            </form>
        <?php endif; ?>

        <!-- Liste des avis approuvés -->
        <?php if (empty($avis)): ?>
            <p class="text-text-dim text-sm">Aucun avis pour l'instant. Sois le premier à partager ton avis !</p>
        <?php else: ?>
            <div class="space-y-5">
                <?php foreach ($avis as $a): ?>
                <div class="border-b border-border/50 pb-5 last:border-0">
                    <div class="flex items-center gap-3 mb-2">
                        <span class="w-8 h-8 rounded-full bg-surface-2 text-text-dim flex items-center justify-center text-xs font-bold"><?= e(mb_strtoupper(mb_substr($a->prenom, 0, 1))) ?></span>
                        <div>
                            <p class="text-white text-sm font-medium"><?= e($a->prenom . ' ' . mb_substr($a->nom, 0, 1) . '.') ?></p>
                            <p class="text-text-dim text-xs"><?= date('d/m/Y', strtotime($a->created_at)) ?></p>
                        </div>
                        <div class="ml-auto"><?= stars_html($a->note) ?></div>
                    </div>
                    <?php if ($a->titre): ?>
                        <p class="text-white font-medium text-sm mb-1"><?= e($a->titre) ?></p>
                    <?php endif; ?>
                    <p class="text-text-muted text-sm leading-relaxed"><?= e($a->commentaire) ?></p>
                </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</section>
