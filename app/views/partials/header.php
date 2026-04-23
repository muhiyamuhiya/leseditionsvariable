<?php $currentUser = App\Lib\Auth::user(); ?>

<!-- Barre annonce -->
<div class="bg-ink text-white/70 text-[10px] sm:text-[11px] tracking-wider uppercase font-sans font-medium py-1.5 text-center">
    Livraison Premium dans 15 pays francophones
</div>

<!-- Header principal -->
<header x-data="{ mobileOpen: false, searchOpen: false }"
        class="bg-paper/95 backdrop-blur-md border-b border-subtle sticky top-0 z-50">
    <div class="container-editorial">
        <div class="flex items-center justify-between h-14 sm:h-16 lg:h-20">

            <!-- Logo horizontal -->
            <a href="/" class="flex-shrink-0">
                <img src="<?= asset('images/logo-horizontal.jpg') ?>" alt="Les éditions Variable" class="h-9 sm:h-10 lg:h-12 w-auto">
            </a>

            <!-- Navigation centrale (desktop) -->
            <nav class="hidden lg:flex items-center gap-10">
                <a href="/catalogue" class="text-[13px] font-medium tracking-[0.12em] uppercase text-muted hover:text-ink transition-colors">Catalogue</a>
                <a href="/abonnement" class="text-[13px] font-medium tracking-[0.12em] uppercase text-muted hover:text-ink transition-colors">Abonnement</a>
                <a href="/auteurs" class="text-[13px] font-medium tracking-[0.12em] uppercase text-muted hover:text-ink transition-colors">Auteurs</a>
                <a href="/a-propos" class="text-[13px] font-medium tracking-[0.12em] uppercase text-muted hover:text-ink transition-colors">À propos</a>
            </nav>

            <!-- Droite -->
            <div class="flex items-center gap-4 sm:gap-5">

                <!-- Recherche (desktop) -->
                <div class="relative hidden sm:block">
                    <button @click="searchOpen = !searchOpen" class="text-muted hover:text-ink transition-colors">
                        <svg class="w-[18px] h-[18px]" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z"/></svg>
                    </button>
                    <div x-show="searchOpen" x-transition @click.outside="searchOpen = false"
                         class="absolute right-0 top-full mt-3 w-72 sm:w-80 bg-white border border-subtle shadow-xl p-4 z-50">
                        <form action="/recherche" method="GET">
                            <input type="text" name="q" placeholder="Rechercher un livre, un auteur..."
                                   class="w-full px-0 py-2 border-0 border-b border-subtle text-sm focus:border-ink focus:ring-0 outline-none bg-transparent placeholder:text-muted/50"
                                   autofocus>
                        </form>
                    </div>
                </div>

                <?php if ($currentUser): ?>
                    <!-- Connecté -->
                    <div x-data="{ open: false }" class="relative">
                        <button @click="open = !open" @click.outside="open = false"
                                class="flex items-center gap-2 text-muted hover:text-ink transition-colors">
                            <span class="w-8 h-8 rounded-full bg-ink text-white flex items-center justify-center text-[11px] font-semibold tracking-wide">
                                <?= e(mb_strtoupper(mb_substr($currentUser->prenom, 0, 1) . mb_substr($currentUser->nom, 0, 1))) ?>
                            </span>
                        </button>
                        <div x-show="open"
                             x-transition:enter="transition ease-out duration-150"
                             x-transition:enter-start="opacity-0 translate-y-1"
                             x-transition:enter-end="opacity-100 translate-y-0"
                             x-transition:leave="transition ease-in duration-100"
                             x-transition:leave-start="opacity-100 translate-y-0"
                             x-transition:leave-end="opacity-0 translate-y-1"
                             class="absolute right-0 mt-3 w-56 bg-white border border-subtle shadow-xl py-2 z-50">
                            <div class="px-4 py-3 border-b border-subtle">
                                <p class="text-sm font-medium text-ink"><?= e($currentUser->prenom . ' ' . $currentUser->nom) ?></p>
                                <p class="text-xs text-muted mt-0.5"><?= e($currentUser->email) ?></p>
                            </div>
                            <a href="/mon-compte" class="block px-4 py-2.5 text-sm text-muted hover:text-ink hover:bg-soft transition-colors">Mon compte</a>
                            <a href="/ma-bibliotheque" class="block px-4 py-2.5 text-sm text-muted hover:text-ink hover:bg-soft transition-colors">Ma bibliothèque</a>
                            <?php if ($currentUser->role === 'auteur'): ?>
                                <a href="/auteur/dashboard" class="block px-4 py-2.5 text-sm text-muted hover:text-ink hover:bg-soft transition-colors">Espace auteur</a>
                            <?php endif; ?>
                            <?php if ($currentUser->role === 'admin'): ?>
                                <a href="/admin" class="block px-4 py-2.5 text-sm text-gold font-medium hover:bg-soft transition-colors">Administration</a>
                            <?php endif; ?>
                            <div class="border-t border-subtle mt-1 pt-1">
                                <form action="/deconnexion" method="POST">
                                    <?= csrf_field() ?>
                                    <button type="submit" class="block w-full text-left px-4 py-2.5 text-sm text-muted hover:text-ink hover:bg-soft transition-colors">Déconnexion</button>
                                </form>
                            </div>
                        </div>
                    </div>
                <?php else: ?>
                    <!-- Non connecté -->
                    <a href="/connexion" class="text-muted hover:text-ink transition-colors">
                        <svg class="w-[18px] h-[18px]" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z"/></svg>
                    </a>
                <?php endif; ?>

                <!-- Burger mobile -->
                <button @click="mobileOpen = !mobileOpen" class="lg:hidden text-ink p-1">
                    <svg x-show="!mobileOpen" class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 9h16.5m-16.5 6.75h16.5"/></svg>
                    <svg x-show="mobileOpen" class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Menu mobile fullscreen -->
    <div x-show="mobileOpen"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="lg:hidden fixed inset-0 top-[calc(3.5rem+1.5rem)] sm:top-[calc(4rem+1.5rem)] bg-ink z-40">
        <!-- Bouton X en haut à droite -->
        <button @click="mobileOpen = false" class="absolute top-6 right-6 text-white/60 hover:text-white">
            <svg class="w-7 h-7" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
        </button>
        <div class="flex flex-col items-center justify-center h-full gap-7">
            <a href="/catalogue" @click="mobileOpen = false" class="font-display italic text-3xl text-white/90 hover:text-gold transition-colors">Catalogue</a>
            <a href="/abonnement" @click="mobileOpen = false" class="font-display italic text-3xl text-white/90 hover:text-gold transition-colors">Abonnement</a>
            <a href="/auteurs" @click="mobileOpen = false" class="font-display italic text-3xl text-white/90 hover:text-gold transition-colors">Auteurs</a>
            <a href="/a-propos" @click="mobileOpen = false" class="font-display italic text-3xl text-white/90 hover:text-gold transition-colors">À propos</a>
            <div class="w-10 h-px bg-gold/40 my-1"></div>
            <?php if ($currentUser): ?>
                <a href="/mon-compte" @click="mobileOpen = false" class="text-base text-white/50 hover:text-white transition-colors">Mon compte</a>
                <a href="/ma-bibliotheque" @click="mobileOpen = false" class="text-base text-white/50 hover:text-white transition-colors">Ma bibliothèque</a>
            <?php else: ?>
                <a href="/connexion" @click="mobileOpen = false" class="text-base text-white/50 hover:text-white transition-colors">Connexion</a>
                <a href="/inscription" @click="mobileOpen = false" class="text-base text-white/50 hover:text-white transition-colors">Inscription</a>
            <?php endif; ?>
            <!-- Recherche mobile -->
            <form action="/recherche" method="GET" class="mt-2 w-64">
                <input type="text" name="q" placeholder="Rechercher..."
                       class="w-full bg-transparent border-b border-white/20 text-white text-sm py-2 px-0 outline-none placeholder:text-white/30 focus:border-gold transition-colors">
            </form>
        </div>
    </div>
</header>
