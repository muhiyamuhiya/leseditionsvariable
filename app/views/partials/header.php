<?php
$currentUser = App\Lib\Auth::user();
$showDevenirAuteur = !$currentUser || $currentUser->role === 'lecteur';
$devenirAuteurHref = $currentUser ? '/auteur/candidater' : '/connexion?redirect=/auteur/candidater';
?>

<!-- Wrapper Alpine pour partager menuOpen entre header et overlay -->
<div x-data="{ menuOpen: false, searchOpen: false }"
     x-init="$watch('menuOpen', v => document.body.classList.toggle('overflow-hidden', v))">

    <header class="fixed top-0 left-0 right-0 z-50 h-14 sm:h-16 bg-bg/90 backdrop-blur-md border-b border-transparent transition-colors"
            @scroll.window="$el.classList.toggle('border-border', window.scrollY > 10)">
        <div class="max-w-[1400px] mx-auto px-4 sm:px-6 h-full flex items-center justify-between">

            <!-- Logo -->
            <a href="/" class="flex items-center gap-2.5 flex-shrink-0">
                <img src="<?= asset('images/logo.png') ?>" alt="Les éditions Variable" class="h-7 w-7 sm:h-8 sm:w-8 md:h-9 md:w-9 object-contain">
                <div class="flex flex-col leading-tight">
                    <span class="font-display text-white text-[11px] sm:text-xs md:text-sm font-bold tracking-wider uppercase">Les éditions</span>
                    <span class="font-display text-accent text-[10px] sm:text-[11px] md:text-xs font-semibold tracking-[0.2em] uppercase">Variable</span>
                </div>
            </a>

            <!-- Nav desktop -->
            <nav class="hidden md:flex items-center gap-6 lg:gap-8">
                <a href="/" class="text-[13px] font-display font-medium text-white hover:text-accent transition-colors">Accueil</a>
                <a href="/catalogue" class="text-[13px] font-display font-medium text-text-muted hover:text-accent transition-colors">Catalogue</a>
                <?php if ($currentUser): ?>
                    <a href="/ma-bibliotheque" class="text-[13px] font-display font-medium text-text-muted hover:text-accent transition-colors">Mes livres</a>
                <?php endif; ?>
                <a href="/catalogue?tri=nouveautes" class="text-[13px] font-display font-medium text-text-muted hover:text-accent transition-colors">Nouveautés</a>
                <a href="/abonnement" class="text-[13px] font-display font-medium text-text-muted hover:text-accent transition-colors">Abonnement</a>
                <?php if ($showDevenirAuteur): ?>
                    <a href="<?= $devenirAuteurHref ?>" class="inline-flex items-center gap-1.5 px-3.5 py-1.5 text-accent border border-accent/30 rounded-full text-xs font-medium hover:bg-accent/10 hover:border-accent transition-all">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125"/></svg>
                        Devenir auteur
                    </a>
                <?php endif; ?>
            </nav>

            <!-- Droite -->
            <div class="flex items-center gap-3 sm:gap-4">
                <!-- Recherche desktop avec live search -->
                <div x-data="liveSearch()" class="hidden sm:flex items-center relative">
                    <div x-show="searchOpen"
                         x-transition:enter="transition ease-out duration-200"
                         x-transition:enter-start="opacity-0 scale-x-0 origin-right"
                         x-transition:enter-end="opacity-100 scale-x-100 origin-right"
                         x-transition:leave="transition ease-in duration-150"
                         x-transition:leave-start="opacity-100 scale-x-100 origin-right"
                         x-transition:leave-end="opacity-0 scale-x-0 origin-right"
                         @click.outside="close()"
                         @keydown.escape="close()"
                         x-cloak
                         class="mr-2 relative">
                        <form action="/catalogue" method="GET" @submit.prevent="goFull()">
                            <input type="text" name="q" x-model="q" x-ref="input"
                                   @input.debounce.300ms="search()"
                                   placeholder="Rechercher un livre, un auteur..."
                                   autocomplete="off"
                                   class="w-56 lg:w-64 bg-surface border border-border rounded-full px-4 py-2 text-sm text-white outline-none focus:border-accent placeholder:text-text-dim">
                        </form>
                        <!-- Résultats live -->
                        <div x-show="results.length > 0" x-cloak
                             class="absolute right-0 top-full mt-2 w-72 lg:w-80 bg-surface border border-border rounded-lg shadow-2xl overflow-hidden z-50">
                            <template x-for="r in results" :key="r.slug">
                                <a :href="'/livre/' + r.slug" class="flex items-center gap-3 px-4 py-3 hover:bg-surface-2 transition-colors border-b border-border/50 last:border-0">
                                    <div class="flex-grow min-w-0">
                                        <p class="text-sm text-white font-medium truncate" x-text="r.titre"></p>
                                        <p class="text-xs text-text-dim truncate" x-text="r.auteur + ' · ' + r.categorie"></p>
                                    </div>
                                    <span class="text-accent text-xs font-semibold whitespace-nowrap" x-text="r.prix"></span>
                                </a>
                            </template>
                            <a :href="'/catalogue?q=' + encodeURIComponent(q)" class="block px-4 py-2.5 text-center text-xs text-accent hover:bg-surface-2 transition-colors border-t border-border">
                                Voir tous les résultats
                            </a>
                        </div>
                    </div>
                    <button @click="toggle()"
                            class="p-1 text-text-muted hover:text-accent transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z"/></svg>
                    </button>
                </div>

                <?php if ($currentUser): ?>
                    <!-- Cloche notifications (visible mobile + desktop) -->
                    <div x-data="notificationBell()"
                         x-init="loadCount(); setInterval(() => loadCount(), 60000)"
                         class="relative">
                        <button @click="toggle()"
                                class="relative p-1.5 text-text-muted hover:text-accent transition-colors">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75v-.7V9A6 6 0 006 9v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0"/></svg>
                            <span x-show="unreadCount > 0" x-cloak x-text="unreadCount > 9 ? '9+' : unreadCount"
                                  class="absolute -top-0.5 -right-0.5 bg-rose-500 text-white text-[10px] font-bold rounded-full min-w-[18px] h-[18px] px-1 flex items-center justify-center"></span>
                        </button>

                        <div x-show="open" x-cloak @click.outside="open = false"
                             x-transition:enter="transition ease-out duration-150"
                             x-transition:enter-start="opacity-0 scale-95"
                             x-transition:enter-end="opacity-100 scale-100"
                             class="fixed sm:absolute right-2 sm:right-0 left-2 sm:left-auto top-14 sm:top-auto sm:mt-2 sm:w-96 bg-surface border border-border rounded-xl shadow-2xl z-50 max-h-[calc(100vh-5rem)] sm:max-h-none">
                            <div class="flex items-center justify-between p-4 border-b border-border">
                                <h3 class="font-display font-bold text-white text-sm">Notifications</h3>
                                <button @click="markAllRead()" x-show="unreadCount > 0" class="text-xs text-accent hover:text-accent-hover">Tout marquer lu</button>
                            </div>
                            <div class="max-h-96 overflow-y-auto">
                                <template x-if="loading">
                                    <div class="p-8 text-center text-text-dim text-sm">Chargement…</div>
                                </template>
                                <template x-if="!loading && notifications.length === 0">
                                    <div class="p-8 text-center text-text-dim">
                                        <svg class="w-12 h-12 mx-auto mb-2 opacity-30" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75v-.7V9A6 6 0 006 9v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 01-5.714 0"/></svg>
                                        <p class="text-sm">Aucune notification</p>
                                    </div>
                                </template>
                                <template x-for="notif in notifications" :key="notif.id">
                                    <a :href="notif.link_url || '#'" @click="markAsRead(notif.id)"
                                       class="block p-4 border-b border-border/50 hover:bg-surface-2 transition-colors"
                                       :class="!notif.read_at ? 'bg-accent/5' : ''">
                                        <div class="flex gap-3">
                                            <div class="flex-shrink-0 w-9 h-9 rounded-full bg-accent/15 flex items-center justify-center">
                                                <span x-text="iconFor(notif.icon)" class="text-base"></span>
                                            </div>
                                            <div class="flex-1 min-w-0">
                                                <p class="font-medium text-white text-sm truncate" x-text="notif.title"></p>
                                                <p class="text-xs text-text-muted mt-0.5 line-clamp-2" x-text="notif.message || ''"></p>
                                                <p class="text-[10px] text-text-dim mt-1" x-text="timeAgo(notif.created_at)"></p>
                                            </div>
                                            <span x-show="!notif.read_at" class="w-2 h-2 rounded-full bg-accent flex-shrink-0 mt-1.5"></span>
                                        </div>
                                    </a>
                                </template>
                            </div>
                            <div class="p-3 border-t border-border text-center">
                                <a href="/notifications" class="text-xs text-accent hover:text-accent-hover">Voir toutes les notifications</a>
                            </div>
                        </div>
                    </div>

                    <div x-data="{ open: false }" class="relative">
                        <button @click="open = !open" @click.outside="open = false"
                                class="w-8 h-8 rounded-full bg-accent text-black flex items-center justify-center text-xs font-bold font-display">
                            <?= e(mb_strtoupper(mb_substr($currentUser->prenom, 0, 1))) ?>
                        </button>
                        <div x-show="open" x-transition:enter="transition ease-out duration-150" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100" x-transition:leave="transition ease-in duration-100"
                             class="absolute right-0 mt-2 w-52 bg-surface border border-border rounded-lg shadow-2xl py-2 z-50">
                            <div class="px-4 py-2.5 border-b border-border">
                                <p class="text-sm font-semibold text-white"><?= e($currentUser->prenom . ' ' . $currentUser->nom) ?></p>
                                <p class="text-xs text-text-dim mt-0.5"><?= e($currentUser->email) ?></p>
                            </div>
                            <?php $currentPath = $_SERVER['REQUEST_URI'] ?? ''; ?>
                            <a href="/mon-compte" class="block px-4 py-2 text-sm <?= $currentPath === '/mon-compte' ? 'text-accent bg-surface-2' : 'text-text-muted hover:text-accent hover:bg-surface-2' ?> transition-colors">Mon compte</a>
                            <a href="/ma-bibliotheque" class="block px-4 py-2 text-sm <?= $currentPath === '/ma-bibliotheque' ? 'text-accent bg-surface-2' : 'text-text-muted hover:text-accent hover:bg-surface-2' ?> transition-colors">Ma bibliothèque</a>
                            <a href="/mon-compte/favoris" class="flex items-center gap-2 px-4 py-2 text-sm <?= str_starts_with($currentPath, '/mon-compte/favoris') ? 'text-accent bg-surface-2' : 'text-text-muted hover:text-accent hover:bg-surface-2' ?> transition-colors">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12z"/></svg>
                                Mes favoris
                            </a>
                            <a href="/mon-compte/abonnement" class="block px-4 py-2 text-sm <?= str_starts_with($currentPath, '/mon-compte/abonnement') ? 'text-accent bg-surface-2' : 'text-text-muted hover:text-accent hover:bg-surface-2' ?> transition-colors">Mon abonnement</a>
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
                    <a href="/connexion" class="text-text-muted hover:text-white transition-colors p-1 hidden sm:block">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z"/></svg>
                    </a>
                <?php endif; ?>

                <!-- Burger -->
                <button @click="menuOpen = !menuOpen" class="md:hidden text-white hover:text-accent transition-colors p-1">
                    <svg x-show="!menuOpen" class="w-7 h-7" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5M3.75 17.25h16.5"/></svg>
                    <svg x-show="menuOpen" x-cloak class="w-7 h-7" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
        </div>
    </header>

    <!-- Menu mobile fullscreen — EN DEHORS du header, mais DANS le div x-data -->
    <div x-show="menuOpen"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         @keydown.escape.window="menuOpen = false"
         x-cloak
         class="md:hidden fixed inset-0 top-14 sm:top-16 z-[55] bg-bg flex flex-col overflow-y-auto">

        <!-- Recherche mobile live -->
        <div x-data="liveSearch()" class="px-6 pt-5 pb-2 flex-shrink-0">
            <form action="/catalogue" method="GET" @submit.prevent="menuOpen = false; goFull()">
                <div class="relative">
                    <svg class="absolute left-4 top-1/2 -translate-y-1/2 w-5 h-5 text-text-dim" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z"/></svg>
                    <input type="text" name="q" x-model="q"
                           @input.debounce.300ms="search()"
                           placeholder="Rechercher un livre, un auteur..."
                           autocomplete="off"
                           class="w-full bg-surface-2 border border-border rounded-full pl-12 pr-4 py-3 text-white text-sm outline-none focus:border-accent placeholder:text-text-dim">
                </div>
            </form>
            <!-- Résultats live mobile -->
            <div x-show="results.length > 0" x-cloak class="mt-2 bg-surface border border-border rounded-lg overflow-hidden">
                <template x-for="r in results" :key="r.slug">
                    <a :href="'/livre/' + r.slug" @click="menuOpen = false" class="flex items-center gap-3 px-4 py-3 hover:bg-surface-2 transition-colors border-b border-border/50 last:border-0">
                        <div class="flex-grow min-w-0">
                            <p class="text-sm text-white font-medium truncate" x-text="r.titre"></p>
                            <p class="text-xs text-text-dim truncate" x-text="r.auteur + ' · ' + r.categorie"></p>
                        </div>
                        <span class="text-accent text-xs font-semibold whitespace-nowrap" x-text="r.prix"></span>
                    </a>
                </template>
            </div>
        </div>

        <?php if ($showDevenirAuteur): ?>
        <div class="px-6 pt-3 pb-1 flex-shrink-0">
            <a href="<?= $devenirAuteurHref ?>" @click="menuOpen = false"
               class="flex items-center gap-3 px-5 py-3.5 text-accent border border-accent/20 bg-accent/5 hover:bg-accent/10 rounded-xl font-display font-semibold text-base transition-all">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125"/></svg>
                Devenir auteur
            </a>
        </div>
        <?php endif; ?>

        <nav class="flex-grow flex flex-col justify-center px-8 py-6">
            <a href="/" @click="menuOpen = false" class="group flex items-center gap-4 py-5 border-b border-border/50 transition-all hover:translate-x-1">
                <svg class="w-5 h-5 text-text-dim group-hover:text-accent transition-colors flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12l8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25"/></svg>
                <span class="font-display font-medium text-2xl text-white group-hover:text-accent transition-colors">Accueil</span>
            </a>
            <a href="/catalogue" @click="menuOpen = false" class="group flex items-center gap-4 py-5 border-b border-border/50 transition-all hover:translate-x-1">
                <svg class="w-5 h-5 text-text-dim group-hover:text-accent transition-colors flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 006 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 016 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 016-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0018 18a8.967 8.967 0 00-6 2.292m0-14.25v14.25"/></svg>
                <span class="font-display font-medium text-2xl text-white group-hover:text-accent transition-colors">Catalogue</span>
            </a>
            <a href="/ma-bibliotheque" @click="menuOpen = false" class="group flex items-center gap-4 py-5 border-b border-border/50 transition-all hover:translate-x-1">
                <svg class="w-5 h-5 text-text-dim group-hover:text-accent transition-colors flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 9.776c.112-.017.227-.026.344-.026h15.812c.117 0 .232.009.344.026m-16.5 0a2.25 2.25 0 00-1.883 2.542l.857 6a2.25 2.25 0 002.227 1.932H19.05a2.25 2.25 0 002.227-1.932l.857-6a2.25 2.25 0 00-1.883-2.542m-16.5 0V6A2.25 2.25 0 016 3.75h3.879a1.5 1.5 0 011.06.44l2.122 2.12a1.5 1.5 0 001.06.44H18A2.25 2.25 0 0120.25 9v.776"/></svg>
                <span class="font-display font-medium text-2xl text-white group-hover:text-accent transition-colors">Mes livres</span>
            </a>
            <a href="/catalogue?tri=nouveautes" @click="menuOpen = false" class="group flex items-center gap-4 py-5 border-b border-border/50 transition-all hover:translate-x-1">
                <svg class="w-5 h-5 text-text-dim group-hover:text-accent transition-colors flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9.813 15.904L9 18.75l-.813-2.846a4.5 4.5 0 00-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 003.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 003.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 00-3.09 3.09z"/></svg>
                <span class="font-display font-medium text-2xl text-white group-hover:text-accent transition-colors">Nouveautés</span>
            </a>
            <a href="/abonnement" @click="menuOpen = false" class="group flex items-center gap-4 py-5 transition-all hover:translate-x-1">
                <svg class="w-5 h-5 text-text-dim group-hover:text-accent transition-colors flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12z"/></svg>
                <span class="font-display font-medium text-2xl text-white group-hover:text-accent transition-colors">Abonnement</span>
            </a>
        </nav>

        <div class="px-8 pb-8 flex-shrink-0">
            <?php if ($currentUser): ?>
                <div class="flex items-center gap-3 mb-5 pb-5 border-b border-border">
                    <span class="w-10 h-10 rounded-full bg-accent text-black flex items-center justify-center text-sm font-bold font-display"><?= e(mb_strtoupper(mb_substr($currentUser->prenom, 0, 1))) ?></span>
                    <div>
                        <p class="text-white text-sm font-medium"><?= e($currentUser->prenom) ?></p>
                        <a href="/mon-compte" @click="menuOpen = false" class="text-text-dim text-xs hover:text-accent transition-colors">Mon compte</a>
                    </div>
                </div>
            <?php else: ?>
                <div class="flex flex-col gap-3 mb-6">
                    <a href="/inscription" @click="menuOpen = false" class="btn-primary w-full text-center py-3">S'inscrire</a>
                    <a href="/connexion" @click="menuOpen = false" class="btn-secondary w-full text-center py-3">Se connecter</a>
                </div>
            <?php endif; ?>
            <div class="flex items-center justify-center gap-6 pt-4">
                <a href="#" class="text-text-dim hover:text-accent transition-colors"><svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg></a>
                <a href="#" class="text-text-dim hover:text-accent transition-colors"><svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zM12 0C8.741 0 8.333.014 7.053.072 2.695.272.273 2.69.073 7.052.014 8.333 0 8.741 0 12c0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98C8.333 23.986 8.741 24 12 24c3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98C15.668.014 15.259 0 12 0zm0 5.838a6.162 6.162 0 100 12.324 6.162 6.162 0 000-12.324zM12 16a4 4 0 110-8 4 4 0 010 8zm6.406-11.845a1.44 1.44 0 100 2.881 1.44 1.44 0 000-2.881z"/></svg></a>
                <a href="#" class="text-text-dim hover:text-accent transition-colors"><svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/></svg></a>
                <a href="#" class="text-text-dim hover:text-accent transition-colors"><svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M23.498 6.186a3.016 3.016 0 00-2.122-2.136C19.505 3.545 12 3.545 12 3.545s-7.505 0-9.377.505A3.017 3.017 0 00.502 6.186C0 8.07 0 12 0 12s0 3.93.502 5.814a3.016 3.016 0 002.122 2.136c1.871.505 9.376.505 9.376.505s7.505 0 9.377-.505a3.015 3.015 0 002.122-2.136C24 15.93 24 12 24 12s0-3.93-.502-5.814zM9.545 15.568V8.432L15.818 12l-6.273 3.568z"/></svg></a>
            </div>
        </div>
    </div>

</div><!-- fin wrapper Alpine -->

<!-- Spacer pour le header fixe -->
<div class="h-14 sm:h-16"></div>
