<!-- ============================================================
     SECTION 1 : HERO
     ============================================================ -->
<section class="bg-paper min-h-screen md:min-h-[90vh] flex items-center">
    <div class="container-editorial w-full">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-16 lg:gap-24 items-center py-16 md:py-0">

            <!-- Texte -->
            <div class="max-w-[560px]">
                <p class="text-[11px] font-medium tracking-[0.2em] uppercase text-gold mb-8">
                    Maison d'édition &bull; Fondée en 2024
                </p>
                <h1 class="font-display italic text-[48px] sm:text-[64px] lg:text-[80px] leading-[1.05] text-ink">
                    Des voix qui<br>comptent.
                </h1>
                <p class="font-display italic text-[48px] sm:text-[64px] lg:text-[80px] leading-[1.05] text-gold mt-1">
                    Des lecteurs<br>qui paient.
                </p>
                <p class="text-lg sm:text-xl text-muted leading-relaxed mt-8 max-w-md">
                    Les éditions Variable rassemble les meilleurs auteurs francophones d'Afrique et de la diaspora, et rémunère chaque lecture à sa juste valeur.
                </p>
                <div class="mt-10">
                    <a href="/catalogue" class="btn-primary">Découvrir le catalogue</a>
                </div>
            </div>

            <!-- Citation -->
            <div class="hidden lg:flex items-center">
                <div class="border-l border-gold/30 pl-10 max-w-md">
                    <blockquote class="font-display italic text-[28px] leading-snug text-ink/70">
                        &laquo;&nbsp;J'ai été publiée chez Variable parce qu'ils m'ont lue. Pas parce qu'ils cherchaient une Africaine à publier.&nbsp;&raquo;
                    </blockquote>
                    <p class="mt-6 text-[12px] font-medium tracking-[0.15em] uppercase text-gold">
                        Marie Kasongo<span class="text-muted/40">, auteure</span>
                    </p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ============================================================
     SECTION 2 : CHIFFRES
     ============================================================ -->
<section class="bg-ink py-16 md:py-20">
    <div class="container-editorial">
        <div class="grid grid-cols-1 sm:grid-cols-3 divide-y sm:divide-y-0 sm:divide-x divide-gold/20">
            <div class="text-center py-8 sm:py-0">
                <p class="font-display text-[56px] md:text-[72px] text-white leading-none">20%</p>
                <p class="text-[11px] font-medium tracking-[0.15em] uppercase text-gold mt-3">Commission éditeur</p>
            </div>
            <div class="text-center py-8 sm:py-0">
                <p class="font-display text-[56px] md:text-[72px] text-white leading-none">80%</p>
                <p class="text-[11px] font-medium tracking-[0.15em] uppercase text-gold mt-3">Reversé à l'auteur</p>
            </div>
            <div class="text-center py-8 sm:py-0">
                <p class="font-display text-[56px] md:text-[72px] text-white leading-none">50%</p>
                <p class="text-[11px] font-medium tracking-[0.15em] uppercase text-gold mt-3">Des abonnements redistribués</p>
            </div>
        </div>
    </div>
</section>

<!-- ============================================================
     SECTION 3 : POURQUOI VARIABLE
     ============================================================ -->
<section class="bg-paper section-padding">
    <div class="container-editorial">
        <div class="max-w-3xl mx-auto text-center mb-16 md:mb-24">
            <h2 class="font-display italic text-[36px] md:text-[48px] text-ink leading-tight">Une autre manière d'éditer.</h2>
            <p class="text-muted mt-4 text-lg">Trois principes qui nous distinguent.</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-12 md:gap-16">
            <!-- I -->
            <div>
                <p class="font-display text-[48px] text-gold/40 leading-none mb-6">I</p>
                <h3 class="font-display text-[24px] md:text-[28px] text-ink leading-snug">La rémunération juste</h3>
                <p class="text-muted mt-4 leading-relaxed">
                    Vingt pour cent pour la maison. Quatre-vingts pour l'auteur. Pour toujours.
                </p>
            </div>
            <!-- II -->
            <div>
                <p class="font-display text-[48px] text-gold/40 leading-none mb-6">II</p>
                <h3 class="font-display text-[24px] md:text-[28px] text-ink leading-snug">La lecture récompensée</h3>
                <p class="text-muted mt-4 leading-relaxed">
                    Chaque page lue par un abonné enrichit son auteur. Plus qu'un achat, un engagement.
                </p>
            </div>
            <!-- III -->
            <div>
                <p class="font-display text-[48px] text-gold/40 leading-none mb-6">III</p>
                <h3 class="font-display text-[24px] md:text-[28px] text-ink leading-snug">L'exigence éditoriale</h3>
                <p class="text-muted mt-4 leading-relaxed">
                    Chaque manuscrit est lu, accompagné, publié avec le soin qu'il mérite.
                </p>
            </div>
        </div>
    </div>
</section>

<!-- ============================================================
     SECTION 4 : DANS NOTRE CATALOGUE
     ============================================================ -->
<section class="bg-soft section-padding">
    <div class="container-editorial">
        <h2 class="font-display italic text-[36px] md:text-[48px] text-ink leading-tight mb-16">Dans notre catalogue.</h2>

        <div class="grid grid-cols-2 lg:grid-cols-4 gap-6 md:gap-8">
            <?php
            $livresPlaceholder = [
                ['titre' => 'Les rives du fleuve Congo', 'auteur' => 'Amara Mukendi', 'prix' => '12,99', 'genre' => 'Roman'],
                ['titre' => 'L\'Afrique qui entreprend', 'auteur' => 'Fatou Diallo', 'prix' => '9,99', 'genre' => 'Essai'],
                ['titre' => 'Paroles de baobab', 'auteur' => 'Samba Ndiaye', 'prix' => '7,99', 'genre' => 'Poésie'],
                ['titre' => 'Ma route, mon histoire', 'auteur' => 'Christelle Mbala', 'prix' => '14,99', 'genre' => 'Biographie'],
            ];
            foreach ($livresPlaceholder as $livre):
            ?>
                <div class="group">
                    <!-- Couverture typographique -->
                    <div class="aspect-[2/3] bg-ink flex flex-col justify-between p-5 sm:p-6 mb-4">
                        <p class="text-[10px] font-medium tracking-[0.15em] uppercase text-gold/60"><?= e($livre['genre']) ?></p>
                        <div>
                            <p class="font-display text-white text-lg sm:text-xl leading-snug"><?= e($livre['titre']) ?></p>
                            <p class="font-accent text-white/50 text-sm mt-2"><?= e($livre['auteur']) ?></p>
                        </div>
                    </div>
                    <!-- Infos -->
                    <h3 class="font-display text-lg text-ink group-hover:text-gold transition-colors"><?= e($livre['titre']) ?></h3>
                    <p class="font-accent text-muted text-sm mt-1"><?= e($livre['auteur']) ?></p>
                    <p class="font-accent text-gold text-lg mt-2"><?= e($livre['prix']) ?> $</p>
                </div>
            <?php endforeach; ?>
        </div>

        <div class="mt-16 text-center">
            <a href="/catalogue" class="text-sm font-medium text-ink border-b border-ink hover:text-gold hover:border-gold transition-colors pb-1">
                Voir tout le catalogue &rarr;
            </a>
        </div>
    </div>
</section>

<!-- ============================================================
     SECTION 5 : POUR LES AUTEURS
     ============================================================ -->
<section class="bg-ink section-padding">
    <div class="container-editorial">
        <div class="max-w-3xl mb-16">
            <h2 class="font-display italic text-[48px] md:text-[64px] text-white leading-tight">Tu écris ?</h2>
            <p class="font-accent text-gold text-[24px] md:text-[28px] mt-3">Publie chez ceux qui te liront vraiment.</p>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-16 lg:gap-24">
            <!-- Engagements -->
            <div>
                <ul class="space-y-6">
                    <li class="flex items-start gap-4">
                        <span class="w-1.5 h-1.5 rounded-full bg-gold mt-2.5 flex-shrink-0"></span>
                        <p class="text-white/80 text-lg leading-relaxed">Tes droits d'auteur restent à toi</p>
                    </li>
                    <li class="flex items-start gap-4">
                        <span class="w-1.5 h-1.5 rounded-full bg-gold mt-2.5 flex-shrink-0"></span>
                        <p class="text-white/80 text-lg leading-relaxed">80% de chaque vente te revient</p>
                    </li>
                    <li class="flex items-start gap-4">
                        <span class="w-1.5 h-1.5 rounded-full bg-gold mt-2.5 flex-shrink-0"></span>
                        <p class="text-white/80 text-lg leading-relaxed">Une équipe éditoriale qui accompagne ton oeuvre</p>
                    </li>
                    <li class="flex items-start gap-4">
                        <span class="w-1.5 h-1.5 rounded-full bg-gold mt-2.5 flex-shrink-0"></span>
                        <p class="text-white/80 text-lg leading-relaxed">Une distribution internationale</p>
                    </li>
                </ul>
            </div>

            <!-- Processus -->
            <div class="border border-white/10 p-8 md:p-10">
                <h3 class="text-[11px] font-medium tracking-[0.15em] uppercase text-gold mb-8">Le processus</h3>
                <div class="space-y-6">
                    <div class="flex gap-5">
                        <span class="font-accent text-white/20 text-2xl leading-none w-8 flex-shrink-0">01</span>
                        <p class="text-white/70">Tu soumets ton manuscrit</p>
                    </div>
                    <div class="flex gap-5">
                        <span class="font-accent text-white/20 text-2xl leading-none w-8 flex-shrink-0">02</span>
                        <p class="text-white/70">Nous le lisons sous 21 jours</p>
                    </div>
                    <div class="flex gap-5">
                        <span class="font-accent text-white/20 text-2xl leading-none w-8 flex-shrink-0">03</span>
                        <p class="text-white/70">Nous définissons ensemble les services</p>
                    </div>
                    <div class="flex gap-5">
                        <span class="font-accent text-white/20 text-2xl leading-none w-8 flex-shrink-0">04</span>
                        <p class="text-white/70">Ton livre est publié</p>
                    </div>
                </div>
                <div class="mt-10">
                    <a href="/devenir-auteur" class="btn-ghost-white">Candidater</a>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ============================================================
     SECTION 6 : ABONNEMENT
     ============================================================ -->
<section class="bg-paper section-padding">
    <div class="container-editorial">
        <div class="max-w-3xl mx-auto text-center mb-16 md:mb-24">
            <h2 class="font-display italic text-[36px] md:text-[48px] text-ink leading-tight">L'abonnement.</h2>
            <p class="text-muted mt-4 text-lg">Trois formules. Une même exigence.</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 md:gap-8 max-w-5xl mx-auto">

            <!-- Essentiel Mensuel -->
            <div class="border border-subtle p-8 md:p-10 flex flex-col">
                <h3 class="font-display text-[22px] text-ink">Essentiel</h3>
                <p class="text-[11px] font-medium tracking-[0.1em] uppercase text-muted mt-1">Mensuel</p>
                <div class="mt-6 mb-8">
                    <span class="font-display text-[56px] text-ink leading-none">3</span>
                    <span class="font-accent text-muted text-xl ml-1">$ / mois</span>
                </div>
                <ul class="space-y-3 text-[15px] text-muted flex-grow">
                    <li class="flex items-start gap-3">
                        <span class="w-1 h-1 rounded-full bg-gold mt-2.5 flex-shrink-0"></span>
                        Accès illimité au catalogue
                    </li>
                    <li class="flex items-start gap-3">
                        <span class="w-1 h-1 rounded-full bg-gold mt-2.5 flex-shrink-0"></span>
                        Liseuse en ligne
                    </li>
                    <li class="flex items-start gap-3">
                        <span class="w-1 h-1 rounded-full bg-gold mt-2.5 flex-shrink-0"></span>
                        Progression sauvegardée
                    </li>
                </ul>
                <a href="/abonnement" class="btn-ghost w-full justify-center mt-10">Commencer</a>
            </div>

            <!-- Essentiel Annuel -->
            <div class="border border-subtle p-8 md:p-10 flex flex-col">
                <h3 class="font-display text-[22px] text-ink">Essentiel</h3>
                <p class="text-[11px] font-medium tracking-[0.1em] uppercase text-muted mt-1">Annuel</p>
                <div class="mt-6 mb-2">
                    <span class="font-display text-[56px] text-ink leading-none">30</span>
                    <span class="font-accent text-muted text-xl ml-1">$ / an</span>
                </div>
                <p class="font-accent text-gold text-sm mb-8">2 mois offerts</p>
                <ul class="space-y-3 text-[15px] text-muted flex-grow">
                    <li class="flex items-start gap-3">
                        <span class="w-1 h-1 rounded-full bg-gold mt-2.5 flex-shrink-0"></span>
                        Accès illimité au catalogue
                    </li>
                    <li class="flex items-start gap-3">
                        <span class="w-1 h-1 rounded-full bg-gold mt-2.5 flex-shrink-0"></span>
                        Liseuse en ligne
                    </li>
                    <li class="flex items-start gap-3">
                        <span class="w-1 h-1 rounded-full bg-gold mt-2.5 flex-shrink-0"></span>
                        Progression sauvegardée
                    </li>
                </ul>
                <a href="/abonnement" class="btn-ghost w-full justify-center mt-10">Commencer</a>
            </div>

            <!-- Premium -->
            <div class="border border-subtle p-8 md:p-10 flex flex-col">
                <h3 class="font-display text-[22px] text-ink">Premium</h3>
                <p class="text-[11px] font-medium tracking-[0.1em] uppercase text-muted mt-1">Mensuel</p>
                <div class="mt-6 mb-2">
                    <span class="font-display text-[56px] text-ink leading-none">8</span>
                    <span class="font-accent text-muted text-xl ml-1">$ / mois</span>
                </div>
                <p class="font-accent text-gold text-sm mb-8">+ livre physique</p>
                <ul class="space-y-3 text-[15px] text-muted flex-grow">
                    <li class="flex items-start gap-3">
                        <span class="w-1 h-1 rounded-full bg-gold mt-2.5 flex-shrink-0"></span>
                        Tout l'Essentiel inclus
                    </li>
                    <li class="flex items-start gap-3">
                        <span class="w-1 h-1 rounded-full bg-gold mt-2.5 flex-shrink-0"></span>
                        1 livre physique par trimestre
                    </li>
                    <li class="flex items-start gap-3">
                        <span class="w-1 h-1 rounded-full bg-gold mt-2.5 flex-shrink-0"></span>
                        Livraison gratuite en RDC
                    </li>
                </ul>
                <a href="/abonnement" class="btn-ghost w-full justify-center mt-10">Commencer</a>
            </div>
        </div>
    </div>
</section>

<!-- ============================================================
     SECTION 7 : NEWSLETTER
     ============================================================ -->
<section class="bg-soft py-20 md:py-24">
    <div class="max-w-xl mx-auto px-6 text-center">
        <p class="font-display italic text-[28px] md:text-[32px] text-ink leading-snug">
            Une lettre par mois. Nos choix, nos auteurs, nos découvertes.
        </p>
        <form action="/newsletter" method="POST" class="mt-10 flex flex-col sm:flex-row gap-3">
            <input type="email" name="email" placeholder="Votre adresse email" required
                   class="flex-grow px-5 py-3.5 bg-white border border-subtle text-sm text-ink outline-none focus:border-ink transition-colors placeholder:text-muted/40">
            <button type="submit" class="btn-ghost flex-shrink-0 !py-3.5">S'abonner</button>
        </form>
        <p class="font-accent text-muted/60 text-sm mt-5">Nous respectons votre vie privée.</p>
    </div>
</section>
