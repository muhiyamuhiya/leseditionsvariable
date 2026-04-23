<?php
// Couleurs de catégories pour la section grille
$catColors = [
    'biographies-memoires'     => 'from-blue-800 to-blue-950',
    'developpement-personnel'  => 'from-teal-800 to-teal-950',
    'spiritualite-religion'    => 'from-violet-800 to-violet-950',
    'roman-fiction'            => 'from-red-800 to-red-950',
    'essais-societe'           => 'from-slate-700 to-slate-900',
    'histoire-afrique'         => 'from-amber-800 to-amber-950',
    'poesie-theatre'           => 'from-purple-800 to-purple-950',
    'business-entrepreneuriat' => 'from-emerald-800 to-emerald-950',
    'sante-bien-etre'          => 'from-pink-800 to-pink-950',
    'jeunesse-education'       => 'from-cyan-800 to-cyan-950',
];

// Emojis de catégories
$catEmojis = [
    'biographies-memoires'     => '&#x1F464;',
    'developpement-personnel'  => '&#x1F4C8;',
    'spiritualite-religion'    => '&#x1F54A;',
    'roman-fiction'            => '&#x1F4D6;',
    'essais-societe'           => '&#x1F4DC;',
    'histoire-afrique'         => '&#x1F30D;',
    'poesie-theatre'           => '&#x270D;',
    'business-entrepreneuriat' => '&#x1F4BC;',
    'sante-bien-etre'          => '&#x2764;',
    'jeunesse-education'       => '&#x1F393;',
];

// Rangées de carrousels
$rangees = [
    ['titre' => '&#x1F525; Tendances cette semaine',   'livres' => array_slice($livresFactices, 0, 10)],
    ['titre' => '&#x2728; Nouveautés',                  'livres' => array_merge(array_slice($livresFactices, 3), array_slice($livresFactices, 0, 3))],
    ['titre' => '&#x1F30D; Voix d\'Afrique centrale',   'livres' => array_merge(array_slice($livresFactices, 5), array_slice($livresFactices, 0, 5))],
    ['titre' => '&#x1F4BC; Business & Entrepreneuriat', 'livres' => array_merge(array_slice($livresFactices, 1), array_slice($livresFactices, 6, 4))],
    ['titre' => '&#x2764;&#xFE0F; Recommandés pour toi','livres' => array_merge(array_slice($livresFactices, 7), array_slice($livresFactices, 0, 7))],
];
?>

<!-- ============================================================
     SECTION 1 : HERO IMMERSIF
     ============================================================ -->
<section class="relative h-[50vh] sm:h-[60vh] md:h-[70vh] flex items-end overflow-hidden">
    <!-- Fond dégradé -->
    <div class="absolute inset-0 bg-gradient-to-br from-amber-950 via-red-950 to-bg"></div>
    <!-- Overlay bas -->
    <div class="absolute inset-0 bg-gradient-to-t from-bg via-bg/60 to-transparent"></div>

    <div class="relative z-10 max-w-[1400px] mx-auto px-4 sm:px-6 w-full pb-8 sm:pb-12 md:pb-16">
        <p class="text-[10px] sm:text-xs font-display font-600 tracking-widest uppercase text-accent mb-3">Livre du mois</p>
        <h1 class="font-display font-800 text-3xl sm:text-5xl md:text-6xl lg:text-7xl text-white leading-[1.1] max-w-2xl">
            <?= e($livreDuMois->titre) ?>
        </h1>
        <p class="text-text-muted text-sm sm:text-base md:text-lg mt-2 sm:mt-3"><?= e($livreDuMois->auteur) ?></p>
        <p class="text-text-muted text-sm sm:text-base mt-2 max-w-lg leading-relaxed hidden sm:block">
            <?= e($livreDuMois->description) ?>
        </p>
        <div class="flex flex-wrap gap-3 mt-5 sm:mt-6">
            <a href="/catalogue" class="btn-primary">
                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M8 5v14l11-7z"/></svg>
                Lire maintenant
            </a>
            <a href="/catalogue" class="btn-secondary">Plus d'infos</a>
        </div>
    </div>
</section>

<!-- ============================================================
     SECTION 2 : CARROUSELS DE LIVRES
     ============================================================ -->
<section class="py-6 sm:py-10">
    <?php foreach ($rangees as $i => $rangee): ?>
    <div class="mb-8 sm:mb-10">
        <!-- Titre rangée -->
        <div class="max-w-[1400px] mx-auto px-4 sm:px-6 flex items-center justify-between mb-3 sm:mb-4">
            <h2 class="font-display font-600 text-lg sm:text-xl md:text-2xl text-white"><?= $rangee['titre'] ?></h2>
            <a href="/catalogue" class="btn-ghost text-xs sm:text-sm">Voir tout <span class="ml-1">&rarr;</span></a>
        </div>

        <!-- Carrousel -->
        <div class="relative carousel-wrapper group">
            <button onclick="this.parentElement.querySelector('.carousel-container').scrollBy({left:-600,behavior:'smooth'})"
                    class="carousel-arrow carousel-arrow-left">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5"/></svg>
            </button>

            <div class="carousel-container max-w-[1400px] mx-auto px-4 sm:px-6">
                <?php foreach ($rangee['livres'] as $j => $livre): ?>
                <div class="card-book" style="width: 140px; min-width: 140px;">
                    <div class="card-book-cover aspect-[2/3] bg-gradient-to-br <?= $livre['couleur'] ?> relative flex flex-col items-center justify-between p-3 sm:p-4">
                        <p class="self-start text-[9px] sm:text-[10px] font-medium tracking-wider uppercase text-accent/80"><?= e($livre['categorie']) ?></p>
                        <p class="font-display font-600 text-white text-center text-sm sm:text-[15px] leading-snug px-1"><?= e($livre['titre']) ?></p>
                        <p class="text-white/40 text-[10px]">&nbsp;</p>
                    </div>
                    <div class="mt-2 px-0.5">
                        <p class="text-white text-[13px] font-medium leading-snug truncate"><?= e($livre['titre']) ?></p>
                        <p class="text-text-dim text-[12px] mt-0.5 truncate"><?= e($livre['auteur']) ?></p>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>

            <button onclick="this.parentElement.querySelector('.carousel-container').scrollBy({left:600,behavior:'smooth'})"
                    class="carousel-arrow carousel-arrow-right">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5"/></svg>
            </button>
        </div>
    </div>
    <?php endforeach; ?>
</section>

<!-- ============================================================
     SECTION 3 : BLOC ABONNEMENT
     ============================================================ -->
<section class="bg-surface py-12 sm:py-16 md:py-20">
    <div class="max-w-[1400px] mx-auto px-4 sm:px-6">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-10 lg:gap-16 items-center">
            <!-- Texte -->
            <div>
                <h2 class="font-display font-800 text-3xl sm:text-4xl md:text-5xl text-white leading-tight">Lis sans limite.</h2>
                <p class="text-text-muted text-base sm:text-lg mt-4 max-w-md leading-relaxed">
                    Pour 3&nbsp;$/mois, accède à tout notre catalogue sur tous tes appareils. Annule quand tu veux.
                </p>
                <ul class="mt-6 space-y-3">
                    <li class="flex items-center gap-3 text-sm sm:text-base text-white">
                        <span class="text-accent text-lg">&#x2713;</span>
                        Catalogue complet en illimité
                    </li>
                    <li class="flex items-center gap-3 text-sm sm:text-base text-white">
                        <span class="text-accent text-lg">&#x2713;</span>
                        Liseuse en ligne, progression sauvegardée
                    </li>
                    <li class="flex items-center gap-3 text-sm sm:text-base text-white">
                        <span class="text-accent text-lg">&#x2713;</span>
                        Chaque page lue rémunère l'auteur
                    </li>
                </ul>
                <div class="mt-8">
                    <a href="/abonnement" class="btn-primary w-full sm:w-auto text-center text-base px-8 py-3.5">Commencer l'essai gratuit</a>
                </div>
            </div>

            <!-- Mockup téléphone -->
            <div class="hidden lg:flex justify-center">
                <div class="relative w-[240px]">
                    <!-- Corps du téléphone -->
                    <div class="bg-surface-2 rounded-[2rem] p-3 border border-border shadow-2xl">
                        <div class="bg-bg rounded-[1.4rem] overflow-hidden aspect-[9/16] flex flex-col">
                            <!-- Barre statut simulée -->
                            <div class="h-6 bg-surface-2 flex items-center justify-center">
                                <div class="w-16 h-1 bg-border rounded-full"></div>
                            </div>
                            <!-- Contenu liseuse simulé -->
                            <div class="flex-grow p-5 flex flex-col">
                                <p class="text-accent text-[10px] font-medium uppercase tracking-wider mb-3">Chapitre 1</p>
                                <p class="text-white/90 text-[11px] leading-relaxed">
                                    Le fleuve coulait, indifférent au tumulte des hommes. Sur la rive, Makala regardait les pirogues s'éloigner en silence.
                                    Elle savait que cette nuit serait différente.
                                </p>
                                <p class="text-white/90 text-[11px] leading-relaxed mt-3">
                                    La lune perçait à travers les nuages, projetant des ombres sur le sol rouge. Au loin, la ville ronronnait...
                                </p>
                                <div class="mt-auto pt-4">
                                    <div class="w-full bg-border rounded-full h-1">
                                        <div class="bg-accent h-1 rounded-full" style="width: 35%"></div>
                                    </div>
                                    <p class="text-text-dim text-[9px] mt-1.5 text-center">Page 24 / 68</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ============================================================
     SECTION 4 : CATÉGORIES
     ============================================================ -->
<section class="py-12 sm:py-16">
    <div class="max-w-[1400px] mx-auto px-4 sm:px-6">
        <h2 class="font-display font-700 text-2xl sm:text-3xl text-white mb-6 sm:mb-8">Explorer par catégorie</h2>

        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-3 sm:gap-4">
            <?php foreach ($categories as $cat): ?>
            <a href="/catalogue?categorie=<?= e($cat->slug) ?>"
               class="card-category relative h-28 sm:h-32 bg-gradient-to-br <?= $catColors[$cat->slug] ?? 'from-gray-800 to-gray-900' ?> flex flex-col justify-end p-3 sm:p-4">
                <span class="absolute top-3 right-3 text-2xl sm:text-3xl opacity-60"><?= $catEmojis[$cat->slug] ?? '&#x1F4DA;' ?></span>
                <p class="font-display font-700 text-white text-sm sm:text-base leading-snug relative z-10"><?= e($cat->nom) ?></p>
            </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>
