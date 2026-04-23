<?php $currentUser = App\Lib\Auth::user(); ?>

<header x-data="{ mobileOpen: false, searchOpen: false }"
        class="fixed top-0 left-0 right-0 z-50 h-14 sm:h-16 bg-bg/90 backdrop-blur-md border-b border-transparent transition-colors"
        x-init="window.addEventListener('scroll', () => { $el.classList.toggle('border-border', window.scrollY > 10) })">
    <div class="max-w-[1400px] mx-auto px-4 sm:px-6 h-full flex items-center justify-between">

        <!-- Logo -->
        <a href="/" class="flex-shrink-0">
            <img src="<?= asset('images/logo-horizontal.jpg') ?>" alt="Les éditions Variable"
                 class="h-7 sm:h-9 w-auto" style="filter: brightness(0) invert(1);">
        </a>

        <!-- Nav desktop -->
        <nav class="hidden md:flex items-center gap-6 lg:gap-8">
            <a href="/" class="text-[13px] font-medium text-white hover:text-accent transition-colors font-display">Accueil</a>
            <a href="/catalogue" class="text-[13px] font-medium text-text-muted hover:text-accent transition-colors font-display">Catalogue</a>
            <?php if ($currentUser): ?>
                <a href="/ma-bibliotheque" class="text-[13px] font-medium text-text-muted hover:text-accent transition-colors font-display">Mes livres</a>
            <?php endif; ?>
            <a href="/catalogue?tri=nouveautes" class="text-[13px] font-medium text-text-muted hover:text-accent transition-colors font-display">Nouveautés</a>
            <a href="/abonnement" class="text-[13px] font-medium text-text-muted hover:text-accent transition-colors font-display">Abonnement</a>
        </nav>

        <!-- Droite -->
        <div class="flex items-center gap-3 sm:gap-4">
            <!-- Recherche -->
            <div class="relative">
                <button @click="searchOpen = !searchOpen" class="text-text-muted hover:text-white transition-colors p-1">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z"/></svg>
                </button>
                <div x-show="searchOpen" x-transition @click.outside="searchOpen = false"
                     class="absolute right-0 top-full mt-2 w-72 sm:w-80 bg-surface border border-border rounded-lg shadow-2xl p-3 z-50">
                    <form action="/recherche" method="GET">
                        <input type="text" name="q" placeholder="Rechercher un livre, un auteur..."
                               class="w-full px-3 py-2 bg-surface-2 border border-border rounded text-sm text-white outline-none focus:border-accent placeholder:text-text-dim"
                               autofocus>
                    </form>
                </div>
            </div>

            <?php if ($currentUser): ?>
                <!-- Connecté -->
                <div x-data="{ open: false }" class="relative">
                    <button @click="open = !open" @click.outside="open = false"
                            class="w-8 h-8 rounded-full bg-accent text-black flex items-center justify-center text-xs font-bold font-display">
                        <?= e(mb_strtoupper(mb_substr($currentUser->prenom, 0, 1))) ?>
                    </button>
                    <div x-show="open"
                         x-transition:enter="transition ease-out duration-150"
                         x-transition:enter-start="opacity-0 scale-95"
                         x-transition:enter-end="opacity-100 scale-100"
                         x-transition:leave="transition ease-in duration-100"
                         class="absolute right-0 mt-2 w-52 bg-surface border border-border rounded-lg shadow-2xl py-2 z-50">
                        <div class="px-4 py-2.5 border-b border-border">
                            <p class="text-sm font-semibold text-white"><?= e($currentUser->prenom . ' ' . $currentUser->nom) ?></p>
                            <p class="text-xs text-text-dim mt-0.5"><?= e($currentUser->email) ?></p>
                        </div>
                        <a href="/mon-compte" class="block px-4 py-2 text-sm text-text-muted hover:text-accent hover:bg-surface-2 transition-colors">Mon compte</a>
                        <a href="/ma-bibliotheque" class="block px-4 py-2 text-sm text-text-muted hover:text-accent hover:bg-surface-2 transition-colors">Ma bibliothèque</a>
                        <a href="/publier" class="block px-4 py-2 text-sm text-text-muted hover:text-accent hover:bg-surface-2 transition-colors">Publier chez Variable</a>
                        <?php if ($currentUser->role === 'auteur'): ?>
                            <a href="/auteur/dashboard" class="block px-4 py-2 text-sm text-text-muted hover:text-accent hover:bg-surface-2 transition-colors">Espace auteur</a>
                        <?php endif; ?>
                        <?php if ($currentUser->role === 'admin'): ?>
                            <a href="/admin" class="block px-4 py-2 text-sm text-red-400 hover:text-red-300 hover:bg-surface-2 transition-colors">Administration</a>
                        <?php endif; ?>
                        <div class="border-t border-border mt-1 pt-1">
                            <form action="/deconnexion" method="POST">
                                <?= csrf_field() ?>
                                <button type="submit" class="block w-full text-left px-4 py-2 text-sm text-text-dim hover:text-white hover:bg-surface-2 transition-colors">Déconnexion</button>
                            </form>
                        </div>
                    </div>
                </div>
            <?php else: ?>
                <a href="/connexion" class="text-text-muted hover:text-white transition-colors p-1">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z"/></svg>
                </a>
            <?php endif; ?>

            <!-- Burger mobile -->
            <button @click="mobileOpen = !mobileOpen" class="md:hidden text-white p-1">
                <svg x-show="!mobileOpen" class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5"/></svg>
                <svg x-show="mobileOpen" class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
    </div>

    <!-- Menu mobile fullscreen -->
    <div x-show="mobileOpen"
         x-transition:enter="transition ease-out duration-250"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200"
         class="md:hidden fixed inset-0 top-14 bg-bg z-40 flex flex-col items-center justify-center gap-6">
        <a href="/" @click="mobileOpen = false" class="font-display text-2xl font-600 text-white hover:text-accent transition-colors">Accueil</a>
        <a href="/catalogue" @click="mobileOpen = false" class="font-display text-2xl font-600 text-white hover:text-accent transition-colors">Catalogue</a>
        <a href="/catalogue?tri=nouveautes" @click="mobileOpen = false" class="font-display text-2xl font-600 text-white hover:text-accent transition-colors">Nouveautés</a>
        <a href="/abonnement" @click="mobileOpen = false" class="font-display text-2xl font-600 text-white hover:text-accent transition-colors">Abonnement</a>
        <div class="w-10 h-px bg-border my-1"></div>
        <?php if ($currentUser): ?>
            <a href="/ma-bibliotheque" @click="mobileOpen = false" class="text-lg text-text-muted hover:text-white transition-colors">Mes livres</a>
            <a href="/mon-compte" @click="mobileOpen = false" class="text-lg text-text-muted hover:text-white transition-colors">Mon compte</a>
        <?php else: ?>
            <a href="/connexion" @click="mobileOpen = false" class="text-lg text-text-muted hover:text-white transition-colors">Connexion</a>
            <a href="/inscription" @click="mobileOpen = false" class="text-lg text-text-muted hover:text-white transition-colors">Inscription</a>
        <?php endif; ?>
        <form action="/recherche" method="GET" class="mt-2 w-64">
            <input type="text" name="q" placeholder="Rechercher..."
                   class="w-full bg-surface border border-border rounded px-4 py-2.5 text-sm text-white outline-none placeholder:text-text-dim focus:border-accent">
        </form>
    </div>
</header>

<!-- Spacer pour le header fixe -->
<div class="h-14 sm:h-16"></div>
