<?php
// Couleurs de catégories
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

// Icônes SVG Heroicons par rangée
$icones = [
    'tendances' => '<path stroke-linecap="round" stroke-linejoin="round" d="M15.362 5.214A8.252 8.252 0 0112 21 8.25 8.25 0 016.038 7.048 8.287 8.287 0 009 9.6a8.983 8.983 0 013.361-6.867 8.21 8.21 0 003 2.48z"/><path stroke-linecap="round" stroke-linejoin="round" d="M12 18a3.75 3.75 0 00.495-7.468 5.99 5.99 0 00-1.925 3.547 5.975 5.975 0 01-2.133-1.001A3.75 3.75 0 0012 18z"/>',
    'nouveautes' => '<path stroke-linecap="round" stroke-linejoin="round" d="M9.813 15.904L9 18.75l-.813-2.846a4.5 4.5 0 00-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 003.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 003.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 00-3.09 3.09zM18.259 8.715L18 9.75l-.259-1.035a3.375 3.375 0 00-2.455-2.456L14.25 6l1.036-.259a3.375 3.375 0 002.455-2.456L18 2.25l.259 1.035a3.375 3.375 0 002.456 2.456L21.75 6l-1.035.259a3.375 3.375 0 00-2.456 2.456z"/>',
    'afrique' => '<path stroke-linecap="round" stroke-linejoin="round" d="M12 21a9.004 9.004 0 008.716-6.747M12 21a9.004 9.004 0 01-8.716-6.747M12 21c2.485 0 4.5-4.03 4.5-9S14.485 3 12 3m0 18c-2.485 0-4.5-4.03-4.5-9S9.515 3 12 3m0 0a8.997 8.997 0 017.843 4.582M12 3a8.997 8.997 0 00-7.843 4.582m15.686 0A11.953 11.953 0 0112 10.5c-2.998 0-5.74-1.1-7.843-2.918m15.686 0A8.959 8.959 0 0121 12c0 .778-.099 1.533-.284 2.253m0 0A17.919 17.919 0 0112 16.5c-3.162 0-6.133-.815-8.716-2.247m0 0A9.015 9.015 0 013 12c0-1.605.42-3.113 1.157-4.418"/>',
    'business' => '<path stroke-linecap="round" stroke-linejoin="round" d="M20.25 14.15v4.25c0 1.094-.787 2.036-1.872 2.18-2.087.277-4.216.42-6.378.42s-4.291-.143-6.378-.42c-1.085-.144-1.872-1.086-1.872-2.18v-4.25m16.5 0a2.18 2.18 0 00.75-1.661V8.706c0-1.081-.768-2.015-1.837-2.175a48.114 48.114 0 00-3.413-.387m4.5 8.006c-.194.165-.42.295-.673.38A23.978 23.978 0 0112 15.75c-2.648 0-5.195-.429-7.577-1.22a2.016 2.016 0 01-.673-.38m0 0A2.18 2.18 0 013 12.489V8.706c0-1.081.768-2.015 1.837-2.175a48.111 48.111 0 013.413-.387m7.5 0V5.25A2.25 2.25 0 0013.5 3h-3a2.25 2.25 0 00-2.25 2.25v.894m7.5 0a48.667 48.667 0 00-7.5 0"/>',
    'recommandes' => '<path stroke-linecap="round" stroke-linejoin="round" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12z"/>',
];

// Rangées de carrousels
$rangees = [
    ['key' => 'tendances',   'titre' => 'Tendances cette semaine',   'livres' => array_slice($livresFactices, 0, 10)],
    ['key' => 'nouveautes',  'titre' => 'Nouveautés',                'livres' => array_merge(array_slice($livresFactices, 3), array_slice($livresFactices, 0, 3))],
    ['key' => 'afrique',     'titre' => 'Voix d\'Afrique francophone','livres' => array_merge(array_slice($livresFactices, 5), array_slice($livresFactices, 0, 5))],
    ['key' => 'business',    'titre' => 'Business & Entrepreneuriat','livres' => array_merge(array_slice($livresFactices, 1), array_slice($livresFactices, 6, 4))],
    ['key' => 'recommandes', 'titre' => 'Recommandés pour vous',     'livres' => array_merge(array_slice($livresFactices, 7), array_slice($livresFactices, 0, 7))],
];
?>

<!-- ============================================================
     SECTION 1 : HERO IMMERSIF
     ============================================================ -->
<section class="relative h-[50vh] sm:h-[60vh] md:h-[70vh] flex items-end overflow-hidden">
    <div class="absolute inset-0 bg-gradient-to-br from-amber-950 via-red-950 to-bg"></div>
    <div class="absolute inset-0 bg-gradient-to-t from-bg via-bg/60 to-transparent"></div>

    <div class="relative z-10 max-w-[1400px] mx-auto px-4 sm:px-6 w-full pb-8 sm:pb-12 md:pb-16">
        <p class="text-[10px] sm:text-xs font-display font-semibold tracking-widest uppercase text-accent mb-3">Livre du mois</p>
        <h1 class="font-display font-extrabold text-3xl sm:text-5xl md:text-6xl lg:text-7xl text-white leading-[1.1] max-w-2xl">
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
    <?php foreach ($rangees as $rangee): ?>
    <div class="mb-8 sm:mb-10">
        <!-- Titre avec icône SVG -->
        <div class="max-w-[1400px] mx-auto px-4 sm:px-6 flex items-center gap-3 mb-3 sm:mb-4">
            <svg class="w-5 h-5 sm:w-6 sm:h-6 text-accent flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><?= $icones[$rangee['key']] ?></svg>
            <h2 class="font-display font-semibold text-lg sm:text-xl md:text-2xl text-white"><?= $rangee['titre'] ?></h2>
            <a href="/catalogue" class="ml-auto text-accent text-xs sm:text-sm font-medium hover:text-accent-hover transition-colors whitespace-nowrap">Voir tout &rarr;</a>
        </div>

        <!-- Carrousel -->
        <div class="relative carousel-wrapper group">
            <button onclick="this.parentElement.querySelector('.carousel-container').scrollBy({left:-600,behavior:'smooth'})"
                    class="carousel-arrow carousel-arrow-left">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5"/></svg>
            </button>

            <div class="carousel-container max-w-[1400px] mx-auto px-4 sm:px-6">
                <?php foreach ($rangee['livres'] as $livre): ?>
                <div class="card-book" style="width:140px;min-width:140px;">
                    <div class="card-book-cover aspect-[2/3] bg-gradient-to-br <?= $livre['couleur'] ?> relative flex flex-col items-center justify-between p-3 sm:p-4">
                        <p class="self-start text-[9px] sm:text-[10px] font-medium tracking-wider uppercase text-accent/80"><?= e($livre['categorie']) ?></p>
                        <p class="font-display font-semibold text-white text-center text-sm sm:text-[15px] leading-snug px-1"><?= e($livre['titre']) ?></p>
                        <span></span>
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
            <div>
                <h2 class="font-display font-extrabold text-3xl sm:text-4xl md:text-5xl text-white leading-tight">Lis sans limite.</h2>
                <p class="text-text-muted text-base sm:text-lg mt-4 max-w-md leading-relaxed">
                    Pour 3&nbsp;$/mois, accède à tout notre catalogue sur tous tes appareils. Annule quand tu veux.
                </p>
                <ul class="mt-6 space-y-3">
                    <li class="flex items-center gap-3 text-sm sm:text-base text-white">
                        <svg class="w-5 h-5 text-accent flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/></svg>
                        Catalogue complet en illimité
                    </li>
                    <li class="flex items-center gap-3 text-sm sm:text-base text-white">
                        <svg class="w-5 h-5 text-accent flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/></svg>
                        Liseuse en ligne, progression sauvegardée
                    </li>
                    <li class="flex items-center gap-3 text-sm sm:text-base text-white">
                        <svg class="w-5 h-5 text-accent flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/></svg>
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
                    <div class="bg-surface-2 rounded-[2rem] p-3 border border-border shadow-2xl">
                        <div class="bg-bg rounded-[1.4rem] overflow-hidden aspect-[9/16] flex flex-col">
                            <div class="h-6 bg-surface-2 flex items-center justify-center">
                                <div class="w-16 h-1 bg-border rounded-full"></div>
                            </div>
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
        <h2 class="font-display font-bold text-2xl sm:text-3xl text-white mb-6 sm:mb-8">Explorer par catégorie</h2>

        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-3 sm:gap-4">
            <?php foreach ($categories as $cat): ?>
            <a href="/catalogue?categorie=<?= e($cat->slug) ?>"
               class="card-category relative h-28 sm:h-32 bg-gradient-to-br <?= $catColors[$cat->slug] ?? 'from-gray-800 to-gray-900' ?> flex flex-col justify-end p-3 sm:p-4">
                <p class="font-display font-bold text-white text-sm sm:text-base leading-snug relative z-10"><?= e($cat->nom) ?></p>
            </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>
