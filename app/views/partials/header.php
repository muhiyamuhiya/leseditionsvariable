<?php $currentUser = App\Lib\Auth::user(); ?>

<!-- Barre supérieure -->
<div class="bg-brand-dark text-gray-300 text-xs py-2 hidden sm:block">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
        &#x1F4D6; Plateforme de lecture numérique africaine francophone | Livraison gratuite en RDC pour les abonnés Premium
    </div>
</div>

<!-- Header principal -->
<header x-data="{ mobileOpen: false, searchOpen: false }" class="bg-white border-b border-gray-100 sticky top-0 z-50 shadow-sm">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between h-16 lg:h-20">

            <!-- Logo -->
            <a href="/" class="flex-shrink-0">
                <img src="<?= asset('images/logo.jpg') ?>" alt="Les éditions Variable" class="h-10 lg:h-12 w-auto">
            </a>

            <!-- Navigation centrale (desktop) -->
            <nav class="hidden lg:flex items-center gap-8">
                <a href="/catalogue" class="text-sm font-medium text-gray-600 hover:text-brand-indigo transition">Catalogue</a>
                <a href="/abonnement" class="text-sm font-medium text-gray-600 hover:text-brand-indigo transition">Abonnement</a>
                <a href="/auteurs" class="text-sm font-medium text-gray-600 hover:text-brand-indigo transition">Auteurs</a>
                <a href="/blog" class="text-sm font-medium text-gray-600 hover:text-brand-indigo transition">Blog</a>
                <a href="/a-propos" class="text-sm font-medium text-gray-600 hover:text-brand-indigo transition">À propos</a>
            </nav>

            <!-- Droite : recherche + auth -->
            <div class="flex items-center gap-3">

                <!-- Recherche -->
                <div class="relative hidden sm:block">
                    <button @click="searchOpen = !searchOpen" class="p-2 text-gray-500 hover:text-brand-indigo transition">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    </button>
                    <div x-show="searchOpen" x-transition @click.outside="searchOpen = false"
                         class="absolute right-0 top-full mt-2 w-72 bg-white rounded-lg shadow-lg border border-gray-200 p-3 z-50">
                        <form action="/recherche" method="GET">
                            <input type="text" name="q" placeholder="Rechercher un livre, un auteur..."
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-brand-indigo focus:border-brand-indigo outline-none"
                                   autofocus>
                        </form>
                    </div>
                </div>

                <?php if ($currentUser): ?>
                    <!-- Utilisateur connecté -->
                    <div x-data="{ open: false }" class="relative">
                        <button @click="open = !open" @click.outside="open = false"
                                class="flex items-center gap-2 text-sm text-gray-600 hover:text-gray-900 transition">
                            <span class="bg-brand-indigo w-8 h-8 rounded-full flex items-center justify-center text-white font-semibold text-sm">
                                <?= e(mb_strtoupper(mb_substr($currentUser->prenom, 0, 1))) ?>
                            </span>
                            <span class="hidden md:inline font-medium"><?= e($currentUser->prenom) ?></span>
                            <svg class="w-4 h-4 transition-transform" :class="{ 'rotate-180': open }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                            </svg>
                        </button>

                        <div x-show="open"
                             x-transition:enter="transition ease-out duration-100"
                             x-transition:enter-start="opacity-0 scale-95"
                             x-transition:enter-end="opacity-100 scale-100"
                             x-transition:leave="transition ease-in duration-75"
                             x-transition:leave-start="opacity-100 scale-100"
                             x-transition:leave-end="opacity-0 scale-95"
                             class="absolute right-0 mt-2 w-52 bg-white rounded-xl shadow-xl border border-gray-100 py-2 z-50">
                            <div class="px-4 py-2 border-b border-gray-100">
                                <p class="text-sm font-semibold text-gray-800"><?= e($currentUser->prenom . ' ' . $currentUser->nom) ?></p>
                                <p class="text-xs text-gray-400"><?= e($currentUser->email) ?></p>
                            </div>
                            <a href="/mon-compte" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">Mon compte</a>
                            <a href="/ma-bibliotheque" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">Ma bibliothèque</a>
                            <?php if ($currentUser->role === 'auteur'): ?>
                                <a href="/auteur/dashboard" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">Espace auteur</a>
                            <?php endif; ?>
                            <?php if ($currentUser->role === 'admin'): ?>
                                <a href="/admin" class="flex items-center gap-2 px-4 py-2 text-sm text-brand-indigo font-medium hover:bg-indigo-50">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.066 2.573c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.573 1.066c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.066-2.573c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    </svg>
                                    Administration
                                    <span class="ml-auto text-xs bg-indigo-100 text-brand-indigo px-1.5 py-0.5 rounded">Admin</span>
                                </a>
                            <?php endif; ?>
                            <hr class="my-1 border-gray-100">
                            <form action="/deconnexion" method="POST">
                                <?= csrf_field() ?>
                                <button type="submit" class="block w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-red-50">Déconnexion</button>
                            </form>
                        </div>
                    </div>
                <?php else: ?>
                    <a href="/connexion" class="hidden sm:inline text-sm font-medium text-gray-600 hover:text-brand-indigo transition">Se connecter</a>
                    <a href="/inscription" class="btn-primary text-sm !py-2 !px-4">S'inscrire</a>
                <?php endif; ?>

                <!-- Burger mobile -->
                <button @click="mobileOpen = !mobileOpen" class="lg:hidden p-2 text-gray-600 hover:text-brand-indigo">
                    <svg x-show="!mobileOpen" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
                    <svg x-show="mobileOpen" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Menu mobile (drawer) -->
    <div x-show="mobileOpen" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 -translate-y-4" x-transition:enter-end="opacity-100 translate-y-0"
         class="lg:hidden bg-white border-t border-gray-100 shadow-lg">
        <div class="max-w-7xl mx-auto px-4 py-4 space-y-1">
            <!-- Recherche mobile -->
            <form action="/recherche" method="GET" class="mb-3">
                <input type="text" name="q" placeholder="Rechercher..."
                       class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-brand-indigo focus:border-brand-indigo outline-none">
            </form>
            <a href="/catalogue" class="block px-3 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-50 rounded-lg">Catalogue</a>
            <a href="/abonnement" class="block px-3 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-50 rounded-lg">Abonnement</a>
            <a href="/auteurs" class="block px-3 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-50 rounded-lg">Auteurs</a>
            <a href="/blog" class="block px-3 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-50 rounded-lg">Blog</a>
            <a href="/a-propos" class="block px-3 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-50 rounded-lg">À propos</a>
            <?php if (!$currentUser): ?>
                <hr class="my-2 border-gray-200">
                <a href="/connexion" class="block px-3 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-50 rounded-lg">Se connecter</a>
                <a href="/inscription" class="block px-3 py-2.5 text-sm font-medium text-white bg-brand-indigo hover:bg-indigo-700 rounded-lg text-center">S'inscrire</a>
            <?php endif; ?>
        </div>
    </div>
</header>
