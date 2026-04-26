<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($titre ?? 'Espace auteur') ?> — Espace auteur</title>
    <link rel="icon" type="image/png" href="/assets/images/logo.png">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Poppins:wght@500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = { theme: { extend: { fontFamily: { sans:['Inter','sans-serif'], display:['Poppins','sans-serif'] }, colors: {
            bg:'#0B0B0F', surface:'#141419', 'surface-2':'#1C1C24', border:'#2A2A35',
            accent:'#F59E0B', 'accent-hover':'#FBBF24', 'text-muted':'#A0A0B0', 'text-dim':'#6B6B7D'
        }}}}
    </script>
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>[x-cloak]{display:none!important} body{font-family:'Inter',sans-serif;background:#0B0B0F;color:#fff}</style>
</head>
<body class="min-h-screen bg-bg">

<div x-data="{ sidebarOpen: false }">
    <div x-show="sidebarOpen" x-cloak x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150" @click="sidebarOpen = false" class="fixed inset-0 bg-black/60 z-40 lg:hidden"></div>

    <aside x-cloak :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'"
           class="fixed inset-y-0 left-0 w-64 bg-surface border-r border-border z-50 flex flex-col transition-transform duration-300 lg:translate-x-0">
        <div class="h-14 flex items-center justify-between px-5 border-b border-border">
            <a href="/auteur" class="flex items-center gap-2" @click="sidebarOpen = false">
                <img src="/assets/images/logo.png" class="h-7 w-7">
                <div class="leading-tight">
                    <span class="font-display text-[10px] font-bold tracking-wider uppercase text-white block">Espace auteur</span>
                    <span class="font-display text-[9px] font-semibold tracking-[0.2em] uppercase text-accent block">Variable</span>
                </div>
            </a>
            <button @click="sidebarOpen = false" class="lg:hidden text-text-dim hover:text-white p-1">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>

        <nav class="flex-grow overflow-y-auto py-4 px-3 space-y-6 text-sm">
            <?php
            $uri = strtok($_SERVER['REQUEST_URI'] ?? '/auteur', '?');
            $_authorDb = App\Lib\Database::getInstance();
            $_authorRecord = App\Lib\Auth::getAuthorRecord();
            $_authorId = $_authorRecord ? (int) $_authorRecord->id : 0;
            $_authorUserId = (int) (App\Lib\Auth::id() ?? 0);
            $_authorBooksRevue = $_authorId ? (int) ($_authorDb->fetch("SELECT COUNT(*) AS c FROM books WHERE author_id = ? AND statut = 'en_revue'", [$_authorId])->c ?? 0) : 0;
            $_authorOrdersAlert = $_authorUserId ? (int) ($_authorDb->fetch("SELECT COUNT(*) AS c FROM editorial_orders WHERE user_id = ? AND statut IN ('devis_envoye','accepte','livre')", [$_authorUserId])->c ?? 0) : 0;
            function authorNav($href, $label, $icon, $uri, $badge = null) {
                $active = ($uri === $href || ($href !== '/auteur' && str_starts_with($uri, $href)));
                $cls = $active ? 'bg-accent/10 text-accent border-l-2 border-accent' : 'text-text-muted hover:bg-surface-2 hover:text-white border-l-2 border-transparent';
                $html = '<a href="' . $href . '" @click="sidebarOpen = false" class="flex items-center gap-3 px-3 py-2.5 rounded-r-lg transition-colors ' . $cls . '"><svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">' . $icon . '</svg><span>' . $label . '</span>';
                if ($badge) $html .= '<span class="ml-auto text-[10px] font-bold bg-accent text-black px-1.5 py-0.5 rounded-full">' . $badge . '</span>';
                $html .= '</a>';
                return $html;
            }
            ?>
            <div>
                <p class="text-text-dim text-[10px] font-semibold uppercase tracking-wider px-3 mb-2">Mon activité</p>
                <?= authorNav('/auteur', 'Tableau de bord', '<path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12l8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25"/>', $uri) ?>
                <?= authorNav('/auteur/livres', 'Mes livres', '<path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 006 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 016 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 016-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0018 18a8.967 8.967 0 00-6 2.292m0-14.25v14.25"/>', $uri, $_authorBooksRevue ?: null) ?>
            </div>
            <div>
                <p class="text-text-dim text-[10px] font-semibold uppercase tracking-wider px-3 mb-2">Création</p>
                <a href="/auteur/livres/nouveau" @click="sidebarOpen = false" class="flex items-center gap-3 px-3 py-2.5 rounded-lg bg-accent/10 text-accent hover:bg-accent/20 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
                    <span class="font-medium">Ajouter un livre</span>
                </a>
            </div>
            <div>
                <p class="text-text-dim text-[10px] font-semibold uppercase tracking-wider px-3 mb-2">Revenus</p>
                <?= authorNav('/auteur/ventes', 'Mes ventes', '<path stroke-linecap="round" stroke-linejoin="round" d="M15.75 10.5V6a3.75 3.75 0 10-7.5 0v4.5m11.356-1.993l1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 01-1.12-1.243l1.264-12A1.125 1.125 0 015.513 7.5h12.974c.576 0 1.059.435 1.119 1.007zM8.625 10.5a.375.375 0 11-.75 0 .375.375 0 01.75 0zm7.5 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z"/>', $uri) ?>
                <?= authorNav('/auteur/versements', 'Mes versements', '<path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0115.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 013 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 00-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 01-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 003 15h-.75M15 10.5a3 3 0 11-6 0 3 3 0 016 0zm3 0h.008v.008H18V10.5zm-12 0h.008v.008H6V10.5z"/>', $uri) ?>
            </div>
            <div>
                <p class="text-text-dim text-[10px] font-semibold uppercase tracking-wider px-3 mb-2">Services éditoriaux</p>
                <?= authorNav('/auteur/services-editoriaux', 'Catalogue services', '<path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931z"/>', $uri) ?>
                <?= authorNav('/auteur/mes-commandes-editoriales', 'Mes commandes', '<path stroke-linecap="round" stroke-linejoin="round" d="M2.25 3h1.386c.51 0 .955.343 1.087.835l.383 1.437M7.5 14.25a3 3 0 00-3 3h15.75m-12.75-3h11.218c1.121-2.3 2.1-4.684 2.924-7.138a60.114 60.114 0 00-16.536-1.84M7.5 14.25L5.106 5.272M6 20.25a.75.75 0 11-1.5 0 .75.75 0 011.5 0zm12.75 0a.75.75 0 11-1.5 0 .75.75 0 011.5 0z"/>', $uri, $_authorOrdersAlert ?: null) ?>
            </div>
            <div>
                <p class="text-text-dim text-[10px] font-semibold uppercase tracking-wider px-3 mb-2">Profil</p>
                <?= authorNav('/auteur/profil', 'Mon profil', '<path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z"/>', $uri) ?>
            </div>
        </nav>
        <div class="p-3 border-t border-border">
            <a href="/" class="flex items-center gap-2 px-3 py-2 text-xs text-text-dim hover:text-accent transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 6H5.25A2.25 2.25 0 003 8.25v10.5A2.25 2.25 0 005.25 21h10.5A2.25 2.25 0 0018 18.75V10.5m-10.5 6L21 3m0 0h-5.25M21 3v5.25"/></svg>
                Voir le site
            </a>
        </div>
    </aside>

    <div class="lg:ml-64 min-h-screen flex flex-col">
        <header x-data="{ userMenu: false }" class="h-14 bg-surface/80 backdrop-blur border-b border-border flex items-center justify-between px-4 sm:px-6 sticky top-0 z-20">
            <div class="flex items-center gap-3">
                <button @click="sidebarOpen = true" class="lg:hidden text-text-muted hover:text-white p-1">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5M3.75 17.25h16.5"/></svg>
                </button>
                <h1 class="font-display font-semibold text-sm sm:text-base text-white"><?= e($titre ?? 'Espace auteur') ?></h1>
            </div>
            <?php $au = App\Lib\Auth::user(); ?>
            <div class="relative">
                <button @click="userMenu = !userMenu" @click.outside="userMenu = false" class="flex items-center gap-2 hover:bg-surface-2 rounded-full px-2 py-1 transition-colors">
                    <div class="w-8 h-8 rounded-full bg-accent text-black flex items-center justify-center text-xs font-bold font-display"><?= e(mb_strtoupper(mb_substr($au->prenom ?? 'A', 0, 1))) ?></div>
                    <span class="hidden sm:block text-white text-sm"><?= e($au->prenom ?? '') ?></span>
                    <svg class="w-4 h-4 text-text-dim" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5"/></svg>
                </button>
                <div x-show="userMenu" x-transition x-cloak class="absolute right-0 mt-2 w-56 bg-surface border border-border rounded-lg shadow-2xl py-2 z-50">
                    <div class="px-4 py-2.5 border-b border-border">
                        <p class="text-sm font-semibold text-white"><?= e(($au->prenom ?? '') . ' ' . ($au->nom ?? '')) ?></p>
                        <p class="text-xs text-text-dim mt-0.5"><?= e($au->email ?? '') ?></p>
                    </div>
                    <a href="/" class="flex items-center gap-3 px-4 py-2 text-sm text-text-muted hover:text-accent hover:bg-surface-2 transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 6H5.25A2.25 2.25 0 003 8.25v10.5A2.25 2.25 0 005.25 21h10.5A2.25 2.25 0 0018 18.75V10.5m-10.5 6L21 3m0 0h-5.25M21 3v5.25"/></svg>
                        Retour au site
                    </a>
                    <a href="/mon-compte" class="flex items-center gap-3 px-4 py-2 text-sm text-text-muted hover:text-accent hover:bg-surface-2 transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z"/></svg>
                        Mon compte
                    </a>
                    <?php if (($au->role ?? '') === 'admin'): ?>
                    <a href="/admin" class="flex items-center gap-3 px-4 py-2 text-sm text-text-muted hover:text-accent hover:bg-surface-2 transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9.594 3.94c.09-.542.56-.94 1.11-.94h2.593c.55 0 1.02.398 1.11.94l.213 1.281c.063.374.313.686.645.87.074.04.147.083.22.127.324.196.72.257 1.075.124l1.217-.456a1.125 1.125 0 011.37.49l1.296 2.247a1.125 1.125 0 01-.26 1.431l-1.003.827c-.293.24-.438.613-.431.992a6.759 6.759 0 010 .255c-.007.378.138.75.43.99l1.005.828c.424.35.534.954.26 1.43l-1.298 2.247a1.125 1.125 0 01-1.369.491l-1.217-.456c-.355-.133-.75-.072-1.076.124a6.57 6.57 0 01-.22.128c-.331.183-.581.495-.644.869l-.213 1.28c-.09.543-.56.941-1.11.941h-2.594c-.55 0-1.02-.398-1.11-.94l-.213-1.281z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                        Administration
                    </a>
                    <?php endif; ?>
                    <div class="border-t border-border mt-1 pt-1">
                        <form action="/deconnexion" method="POST">
                            <?= csrf_field() ?>
                            <button type="submit" class="flex items-center gap-3 w-full px-4 py-2 text-sm text-red-400 hover:bg-red-500/10 transition-colors text-left">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15m3 0l3-3m0 0l-3-3m3 3H9"/></svg>
                                Déconnexion
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </header>
        <main class="flex-grow p-4 sm:p-6"><?= $content ?></main>
    </div>
</div>

<?php require BASE_PATH . '/app/views/partials/tawk.php'; ?>
</body>
</html>
