<?php
$currentUser = App\Lib\Auth::user();
?>
<header class="bg-[#1a1a2e] text-white shadow-lg">
    <nav class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between">
        <!-- Logo -->
        <a href="/" class="text-xl font-bold text-white hover:text-indigo-300 transition">
            Les éditions Variable
        </a>

        <!-- Navigation -->
        <div class="flex items-center gap-4">
            <?php if ($currentUser): ?>
                <!-- Utilisateur connecté -->
                <div x-data="{ open: false }" class="relative">
                    <button @click="open = !open"
                            @click.outside="open = false"
                            class="flex items-center gap-2 text-sm text-gray-200 hover:text-white transition">
                        <span class="bg-indigo-600 w-8 h-8 rounded-full flex items-center justify-center text-white font-medium">
                            <?= e(mb_substr($currentUser->prenom, 0, 1)) ?>
                        </span>
                        <span class="hidden sm:inline">Bonjour, <?= e($currentUser->prenom) ?></span>
                        <svg class="w-4 h-4 transition-transform" :class="{ 'rotate-180': open }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </button>

                    <!-- Menu déroulant -->
                    <div x-show="open"
                         x-transition:enter="transition ease-out duration-100"
                         x-transition:enter-start="opacity-0 scale-95"
                         x-transition:enter-end="opacity-100 scale-100"
                         x-transition:leave="transition ease-in duration-75"
                         x-transition:leave-start="opacity-100 scale-100"
                         x-transition:leave-end="opacity-0 scale-95"
                         class="absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-lg py-1 z-50">
                        <a href="/mon-compte" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Mon compte</a>
                        <a href="/ma-bibliotheque" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Ma bibliothèque</a>
                        <?php if ($currentUser->role === 'admin'): ?>
                            <a href="/admin" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Administration</a>
                        <?php endif; ?>
                        <?php if ($currentUser->role === 'auteur'): ?>
                            <a href="/auteur/dashboard" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Espace auteur</a>
                        <?php endif; ?>
                        <hr class="my-1 border-gray-200">
                        <form action="/deconnexion" method="POST">
                            <?= csrf_field() ?>
                            <button type="submit" class="block w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-red-50">
                                Déconnexion
                            </button>
                        </form>
                    </div>
                </div>
            <?php else: ?>
                <!-- Utilisateur non connecté -->
                <a href="/connexion"
                   class="text-sm text-gray-300 hover:text-white transition">
                    Se connecter
                </a>
                <a href="/inscription"
                   class="text-sm bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg transition">
                    S'inscrire
                </a>
            <?php endif; ?>
        </div>
    </nav>
</header>
