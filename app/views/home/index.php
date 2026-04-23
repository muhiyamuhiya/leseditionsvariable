<?php
// Mapping des icônes par slug de catégorie
$categoryIcons = [
    'biographies-memoires'       => '&#x1F464;',
    'developpement-personnel'    => '&#x1F4C8;',
    'spiritualite-religion'      => '&#x1F54A;',
    'roman-fiction'              => '&#x1F4D6;',
    'essais-societe'             => '&#x1F4DC;',
    'histoire-afrique'           => '&#x1F30D;',
    'poesie-theatre'             => '&#x1FAB6;',
    'business-entrepreneuriat'   => '&#x1F4BC;',
    'sante-bien-etre'            => '&#x2764;',
    'jeunesse-education'         => '&#x1F393;',
];
?>

<!-- ============================================================
     SECTION 1 : HERO
     ============================================================ -->
<section class="relative bg-gradient-to-br from-white via-indigo-50/40 to-indigo-100/30 overflow-hidden">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16 lg:py-24">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 items-center">

            <!-- Texte -->
            <div>
                <h1 class="font-serif text-4xl sm:text-5xl lg:text-6xl font-bold text-brand-dark leading-tight">
                    Lis. Soutiens.<br>
                    <span class="text-brand-indigo">Bâtis.</span>
                </h1>
                <p class="mt-6 text-lg text-gray-600 leading-relaxed max-w-xl">
                    La plateforme qui rend la littérature africaine francophone accessible à tous — et qui rémunère équitablement ses auteurs.
                </p>
                <div class="mt-8 flex flex-wrap gap-4">
                    <a href="/catalogue" class="btn-primary text-base">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                        Explorer le catalogue
                    </a>
                    <a href="/devenir-auteur" class="btn-secondary text-base">Devenir auteur</a>
                </div>
                <div class="mt-8 flex flex-wrap gap-6 text-sm text-gray-500">
                    <span>&#x1F4DA; <strong class="text-gray-800"><?= $statsHero['books'] ?>+</strong> livres</span>
                    <span>&#x270D;&#xFE0F; <strong class="text-gray-800"><?= $statsHero['authors'] ?></strong> auteurs</span>
                    <span>&#x1F30D; <strong class="text-gray-800"><?= $statsHero['countries'] ?></strong> pays</span>
                </div>
            </div>

            <!-- Grille décorative de couvertures -->
            <div class="hidden lg:grid grid-cols-2 gap-4 relative">
                <div class="space-y-4">
                    <div class="animate-float rounded-xl bg-gradient-to-br from-indigo-600 to-purple-700 h-64 flex items-end p-5 shadow-xl">
                        <div>
                            <p class="text-white/70 text-xs font-medium uppercase tracking-wider">Roman</p>
                            <p class="text-white font-serif font-bold text-lg mt-1">Les rives du fleuve Congo</p>
                            <p class="text-white/60 text-sm mt-1">A. Mukendi</p>
                        </div>
                    </div>
                    <div class="animate-float-delayed rounded-xl bg-gradient-to-br from-amber-500 to-orange-600 h-48 flex items-end p-5 shadow-xl">
                        <div>
                            <p class="text-white/70 text-xs font-medium uppercase tracking-wider">Essai</p>
                            <p class="text-white font-serif font-bold text-lg mt-1">L'Afrique qui entreprend</p>
                            <p class="text-white/60 text-sm mt-1">F. Diallo</p>
                        </div>
                    </div>
                </div>
                <div class="space-y-4 pt-8">
                    <div class="animate-float-delayed rounded-xl bg-gradient-to-br from-emerald-600 to-teal-700 h-48 flex items-end p-5 shadow-xl">
                        <div>
                            <p class="text-white/70 text-xs font-medium uppercase tracking-wider">Poésie</p>
                            <p class="text-white font-serif font-bold text-lg mt-1">Paroles de baobab</p>
                            <p class="text-white/60 text-sm mt-1">S. Ndiaye</p>
                        </div>
                    </div>
                    <div class="animate-float rounded-xl bg-gradient-to-br from-rose-600 to-pink-700 h-64 flex items-end p-5 shadow-xl">
                        <div>
                            <p class="text-white/70 text-xs font-medium uppercase tracking-wider">Biographie</p>
                            <p class="text-white font-serif font-bold text-lg mt-1">Ma route, mon histoire</p>
                            <p class="text-white/60 text-sm mt-1">C. Mbala</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ============================================================
     SECTION 2 : CHIFFRES CLÉS
     ============================================================ -->
<section class="bg-brand-dark py-10">
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-8 text-center">
            <div>
                <p class="text-3xl sm:text-4xl font-bold text-white">20%</p>
                <p class="text-sm text-gray-400 mt-1">Commission Variable <span class="block text-xs text-gray-500">(vs 40-60% chez les autres)</span></p>
            </div>
            <div>
                <p class="text-3xl sm:text-4xl font-bold text-white">50%</p>
                <p class="text-sm text-gray-400 mt-1">Revenus abonnement <span class="block text-xs text-gray-500">redistribués aux auteurs</span></p>
            </div>
            <div>
                <p class="text-3xl sm:text-4xl font-bold text-brand-amber">Trimestriel</p>
                <p class="text-sm text-gray-400 mt-1">Rythme des versements <span class="block text-xs text-gray-500">aux auteurs</span></p>
            </div>
        </div>
    </div>
</section>

<!-- ============================================================
     SECTION 3 : COMMENT ÇA MARCHE (LECTEURS)
     ============================================================ -->
<section class="py-20 bg-white">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
        <h2 class="section-title text-center">Comment ça marche pour les lecteurs ?</h2>
        <p class="text-center text-gray-500 mt-3 max-w-2xl mx-auto">Trois étapes simples pour accéder à la littérature africaine francophone.</p>

        <div class="mt-14 grid grid-cols-1 md:grid-cols-3 gap-8">
            <!-- Étape 1 -->
            <div class="text-center p-8 rounded-2xl border border-gray-100 hover:border-brand-indigo/30 hover:shadow-lg transition">
                <div class="w-16 h-16 bg-indigo-100 rounded-2xl flex items-center justify-center mx-auto text-3xl">&#x1F4D6;</div>
                <h3 class="mt-5 text-lg font-semibold text-brand-dark">Choisis</h3>
                <p class="mt-3 text-sm text-gray-500 leading-relaxed">Parcours le catalogue par catégorie ou abonne-toi pour un accès illimité à toute la bibliothèque.</p>
            </div>
            <!-- Étape 2 -->
            <div class="text-center p-8 rounded-2xl border border-gray-100 hover:border-brand-indigo/30 hover:shadow-lg transition">
                <div class="w-16 h-16 bg-indigo-100 rounded-2xl flex items-center justify-center mx-auto text-3xl">&#x1F4F1;</div>
                <h3 class="mt-5 text-lg font-semibold text-brand-dark">Lis</h3>
                <p class="mt-3 text-sm text-gray-500 leading-relaxed">Liseuse sécurisée sur tous tes appareils, avec ta progression sauvegardée automatiquement.</p>
            </div>
            <!-- Étape 3 -->
            <div class="text-center p-8 rounded-2xl border border-gray-100 hover:border-brand-indigo/30 hover:shadow-lg transition">
                <div class="w-16 h-16 bg-indigo-100 rounded-2xl flex items-center justify-center mx-auto text-3xl">&#x2764;&#xFE0F;</div>
                <h3 class="mt-5 text-lg font-semibold text-brand-dark">Découvre</h3>
                <p class="mt-3 text-sm text-gray-500 leading-relaxed">Soutiens les auteurs que tu préfères et contribue directement à leur prochaine oeuvre.</p>
            </div>
        </div>
    </div>
</section>

<!-- ============================================================
     SECTION 4 : POUR LES AUTEURS
     ============================================================ -->
<section class="py-20 bg-amber-50/60">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 items-center">

            <!-- Avantages -->
            <div>
                <h2 class="section-title">Tu es auteur ? Publie avec nous.</h2>
                <p class="text-gray-500 mt-3 mb-8">Garde le contrôle de ton oeuvre tout en bénéficiant d'une diffusion internationale.</p>

                <ul class="space-y-4">
                    <li class="flex items-start gap-3">
                        <span class="flex-shrink-0 w-6 h-6 bg-green-100 text-green-600 rounded-full flex items-center justify-center text-sm font-bold">&#x2713;</span>
                        <span class="text-gray-700">Garde tes droits d'auteur</span>
                    </li>
                    <li class="flex items-start gap-3">
                        <span class="flex-shrink-0 w-6 h-6 bg-green-100 text-green-600 rounded-full flex items-center justify-center text-sm font-bold">&#x2713;</span>
                        <span class="text-gray-700"><strong>80% de chaque vente</strong> pour toi</span>
                    </li>
                    <li class="flex items-start gap-3">
                        <span class="flex-shrink-0 w-6 h-6 bg-green-100 text-green-600 rounded-full flex items-center justify-center text-sm font-bold">&#x2713;</span>
                        <span class="text-gray-700">Accès au pool de redistribution abonnements</span>
                    </li>
                    <li class="flex items-start gap-3">
                        <span class="flex-shrink-0 w-6 h-6 bg-green-100 text-green-600 rounded-full flex items-center justify-center text-sm font-bold">&#x2713;</span>
                        <span class="text-gray-700">Services professionnels (correction, mise en page, couverture)</span>
                    </li>
                    <li class="flex items-start gap-3">
                        <span class="flex-shrink-0 w-6 h-6 bg-green-100 text-green-600 rounded-full flex items-center justify-center text-sm font-bold">&#x2713;</span>
                        <span class="text-gray-700">Promotion sur nos réseaux et newsletter</span>
                    </li>
                </ul>
            </div>

            <!-- CTA + témoignage -->
            <div class="bg-white rounded-2xl p-8 shadow-sm border border-gray-100">
                <blockquote class="text-gray-600 italic leading-relaxed">
                    "Variable m'a permis de publier mon premier roman et d'atteindre des lecteurs dans 6 pays en seulement 3 mois. Le suivi est humain, professionnel, et je reçois mes revenus à chaque trimestre."
                </blockquote>
                <div class="mt-4 flex items-center gap-3">
                    <div class="w-10 h-10 rounded-full bg-brand-amber flex items-center justify-center text-white font-bold text-sm">MK</div>
                    <div>
                        <p class="text-sm font-semibold text-gray-800">Marie Kasongo</p>
                        <p class="text-xs text-gray-400">Auteure, Lubumbashi</p>
                    </div>
                </div>
                <a href="/devenir-auteur" class="btn-primary w-full justify-center mt-6 text-base">Publier mon livre</a>
            </div>
        </div>
    </div>
</section>

<!-- ============================================================
     SECTION 5 : ABONNEMENT
     ============================================================ -->
<section class="py-20 bg-white">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
        <h2 class="section-title text-center">Un abonnement. Tout le catalogue.</h2>
        <p class="text-center text-gray-500 mt-3 max-w-2xl mx-auto">Accède à tous les livres de la plateforme pour le prix d'un café par mois.</p>

        <div class="mt-14 grid grid-cols-1 md:grid-cols-3 gap-8">

            <!-- Essentiel Mensuel -->
            <div class="rounded-2xl border border-gray-200 p-8 hover:border-brand-indigo/40 hover:shadow-lg transition">
                <h3 class="text-lg font-semibold text-brand-dark">Essentiel Mensuel</h3>
                <div class="mt-4">
                    <span class="text-4xl font-bold text-brand-dark">3$</span>
                    <span class="text-gray-400">/mois</span>
                </div>
                <ul class="mt-6 space-y-3 text-sm text-gray-600">
                    <li class="flex items-center gap-2">
                        <svg class="w-4 h-4 text-green-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        Accès illimité au catalogue
                    </li>
                    <li class="flex items-center gap-2">
                        <svg class="w-4 h-4 text-green-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        Liseuse en ligne
                    </li>
                    <li class="flex items-center gap-2">
                        <svg class="w-4 h-4 text-green-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        Progression sauvegardée
                    </li>
                    <li class="flex items-center gap-2">
                        <svg class="w-4 h-4 text-gray-300 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        <span class="text-gray-400">Livre physique trimestriel</span>
                    </li>
                </ul>
                <a href="/abonnement" class="btn-secondary w-full justify-center mt-8">Choisir ce plan</a>
            </div>

            <!-- Essentiel Annuel — Populaire -->
            <div class="rounded-2xl border-2 border-brand-indigo p-8 relative shadow-lg">
                <span class="absolute -top-3 left-1/2 -translate-x-1/2 bg-brand-indigo text-white text-xs font-semibold px-3 py-1 rounded-full">Populaire</span>
                <h3 class="text-lg font-semibold text-brand-dark">Essentiel Annuel</h3>
                <div class="mt-4">
                    <span class="text-4xl font-bold text-brand-dark">30$</span>
                    <span class="text-gray-400">/an</span>
                </div>
                <p class="text-sm text-green-600 font-medium mt-1">Économise 17%</p>
                <ul class="mt-6 space-y-3 text-sm text-gray-600">
                    <li class="flex items-center gap-2">
                        <svg class="w-4 h-4 text-green-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        Accès illimité au catalogue
                    </li>
                    <li class="flex items-center gap-2">
                        <svg class="w-4 h-4 text-green-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        Liseuse en ligne
                    </li>
                    <li class="flex items-center gap-2">
                        <svg class="w-4 h-4 text-green-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        Progression sauvegardée
                    </li>
                    <li class="flex items-center gap-2">
                        <svg class="w-4 h-4 text-gray-300 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        <span class="text-gray-400">Livre physique trimestriel</span>
                    </li>
                </ul>
                <a href="/abonnement" class="btn-primary w-full justify-center mt-8">Choisir ce plan</a>
            </div>

            <!-- Premium -->
            <div class="rounded-2xl border border-gray-200 p-8 hover:border-brand-amber/40 hover:shadow-lg transition bg-gradient-to-b from-amber-50/50 to-white">
                <h3 class="text-lg font-semibold text-brand-dark">Premium</h3>
                <div class="mt-4">
                    <span class="text-4xl font-bold text-brand-dark">8$</span>
                    <span class="text-gray-400">/mois</span>
                </div>
                <ul class="mt-6 space-y-3 text-sm text-gray-600">
                    <li class="flex items-center gap-2">
                        <svg class="w-4 h-4 text-green-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        Accès illimité au catalogue
                    </li>
                    <li class="flex items-center gap-2">
                        <svg class="w-4 h-4 text-green-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        Liseuse en ligne
                    </li>
                    <li class="flex items-center gap-2">
                        <svg class="w-4 h-4 text-green-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        Progression sauvegardée
                    </li>
                    <li class="flex items-center gap-2">
                        <svg class="w-4 h-4 text-brand-amber flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        <strong class="text-brand-dark">1 livre physique / trimestre (RDC)</strong>
                    </li>
                </ul>
                <a href="/abonnement" class="btn-secondary w-full justify-center mt-8 !border-brand-amber !text-brand-amber hover:!bg-brand-amber hover:!text-white">Choisir ce plan</a>
            </div>
        </div>
    </div>
</section>

<!-- ============================================================
     SECTION 6 : CATÉGORIES
     ============================================================ -->
<section class="py-20 bg-gray-50">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
        <h2 class="section-title text-center">Explore notre catalogue</h2>
        <p class="text-center text-gray-500 mt-3">Découvre nos livres classés par thématique.</p>

        <div class="mt-14 grid grid-cols-2 sm:grid-cols-3 md:grid-cols-5 gap-4">
            <?php foreach ($categories as $cat): ?>
                <a href="/catalogue?categorie=<?= e($cat->slug) ?>" class="card-category group">
                    <div class="text-3xl mb-3"><?= $categoryIcons[$cat->slug] ?? '&#x1F4DA;' ?></div>
                    <h3 class="text-sm font-semibold text-gray-700 group-hover:text-brand-indigo transition"><?= e($cat->nom) ?></h3>
                </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- ============================================================
     SECTION 7 : NEWSLETTER
     ============================================================ -->
<section class="bg-brand-indigo py-12">
    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
        <h2 class="text-xl sm:text-2xl font-semibold text-white">Rejoins notre communauté</h2>
        <p class="text-indigo-200 mt-2 text-sm">1 email par mois, des histoires qui comptent. Pas de spam.</p>
        <form action="/newsletter" method="POST" class="mt-6 flex flex-col sm:flex-row gap-3 max-w-lg mx-auto">
            <input type="email" name="email" placeholder="Ton adresse email" required
                   class="flex-grow px-4 py-3 rounded-lg text-sm text-gray-800 outline-none focus:ring-2 focus:ring-white/50">
            <button type="submit" class="bg-white text-brand-indigo font-semibold px-6 py-3 rounded-lg text-sm hover:bg-indigo-50 transition">
                S'inscrire
            </button>
        </form>
    </div>
</section>
