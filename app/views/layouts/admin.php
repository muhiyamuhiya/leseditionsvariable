<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($titre ?? 'Admin') ?> — Administration</title>
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

    <!-- Overlay mobile -->
    <div x-show="sidebarOpen" x-cloak
         x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
         @click="sidebarOpen = false"
         class="fixed inset-0 bg-black/60 z-40 lg:hidden"></div>

    <!-- Sidebar -->
    <aside x-cloak
           :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'"
           class="fixed inset-y-0 left-0 w-64 bg-surface border-r border-border z-50 flex flex-col transition-transform duration-300 lg:translate-x-0">

        <!-- Logo + fermer mobile -->
        <div class="h-14 flex items-center justify-between px-5 border-b border-border">
            <a href="/admin" class="flex items-center gap-2" @click="sidebarOpen = false">
                <img src="/assets/images/logo.png" class="h-7 w-7">
                <div class="leading-tight">
                    <span class="font-display text-[10px] font-bold tracking-wider uppercase text-white block">Administration</span>
                    <span class="font-display text-[9px] font-semibold tracking-[0.2em] uppercase text-accent block">Variable</span>
                </div>
            </a>
            <button @click="sidebarOpen = false" class="lg:hidden text-text-dim hover:text-white p-1">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>

        <nav class="flex-grow overflow-y-auto py-4 px-3 space-y-6 text-sm">
            <?php
            $uri = strtok($_SERVER['REQUEST_URI'] ?? '/admin', '?');
            $_adminDb = App\Lib\Database::getInstance();
            // Helper safe : retourne 0 si la query plante (table manquante en prod, etc.)
            // sans crasher la page entière. Le fail est loggé dans logs/db.log.
            $_safeCount = function ($sql, $params = []) use ($_adminDb): int {
                $row = $_adminDb->fetch($sql, $params);
                return ($row && isset($row->c)) ? (int) $row->c : 0;
            };
            $_pendingCandidatures = $_safeCount("SELECT COUNT(*) as c FROM authors WHERE statut_validation='en_attente'");
            $_pendingBrouillons = $_safeCount("SELECT COUNT(*) as c FROM books WHERE statut='brouillon'");
            $_pendingEnRevue    = $_safeCount("SELECT COUNT(*) as c FROM books WHERE statut='en_revue'");
            $_pendingBooks      = $_pendingBrouillons + $_pendingEnRevue;
            // URL et tooltip : si on a des brouillons, on pointe le clic sur la liste
            // filtrée. Sinon en_revue. Sinon liste complète.
            if ($_pendingBrouillons > 0) {
                $_booksHref  = '/admin/livres?statut=brouillon';
                $_booksTitle = $_pendingBrouillons . ' livre' . ($_pendingBrouillons > 1 ? 's' : '') . ' en brouillon'
                             . ($_pendingEnRevue > 0 ? ' + ' . $_pendingEnRevue . ' en revue' : '');
            } elseif ($_pendingEnRevue > 0) {
                $_booksHref  = '/admin/livres?statut=en_revue';
                $_booksTitle = $_pendingEnRevue . ' livre' . ($_pendingEnRevue > 1 ? 's' : '') . ' en revue';
            } else {
                $_booksHref  = '/admin/livres';
                $_booksTitle = '';
            }
            $_pendingEditorial = $_safeCount("SELECT COUNT(*) as c FROM editorial_orders WHERE statut IN ('en_attente_devis','en_cours')");
            $_pendingChatUnread = $_safeCount("SELECT COUNT(*) as c FROM chat_conversations WHERE has_unread_for_admin=1 AND statut!='archivee'");
            function navItem($href, $label, $icon, $uri, $badge = null, $title = '') {
                // Détection "active" basée sur le path uniquement (sans query string)
                // pour que /admin/livres?statut=brouillon highlight bien l'item Livres.
                $hrefPath = parse_url($href, PHP_URL_PATH) ?: $href;
                $active = ($uri === $hrefPath || ($hrefPath !== '/admin' && str_starts_with($uri, $hrefPath)));
                $cls = $active ? 'bg-accent/10 text-accent border-l-2 border-accent' : 'text-text-muted hover:bg-surface-2 hover:text-white border-l-2 border-transparent';
                $titleAttr = $title !== '' ? ' title="' . htmlspecialchars($title, ENT_QUOTES, 'UTF-8') . '"' : '';
                $html = '<a href="' . $href . '"' . $titleAttr . ' @click="sidebarOpen = false" class="flex items-center gap-3 px-3 py-2.5 rounded-r-lg transition-colors ' . $cls . '">';
                $html .= '<svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">' . $icon . '</svg>';
                $html .= '<span>' . $label . '</span>';
                if ($badge) $html .= '<span class="ml-auto text-[10px] font-bold bg-red-500 text-white px-1.5 py-0.5 rounded-full">' . $badge . '</span>';
                $html .= '</a>';
                return $html;
            }
            ?>
            <div>
                <p class="text-text-dim text-[10px] font-semibold uppercase tracking-wider px-3 mb-2">Pilotage</p>
                <?= navItem('/admin', 'Tableau de bord', '<path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12l8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25"/>', $uri) ?>
                <?= navItem('/admin/chat', 'Chat', '<path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12.76c0 1.6 1.123 2.994 2.707 3.227 1.068.157 2.148.279 3.238.364.466.037.893.281 1.153.671L12 21l2.652-3.978c.26-.39.687-.634 1.153-.67 1.09-.086 2.17-.208 3.238-.365 1.584-.233 2.707-1.626 2.707-3.228V6.741c0-1.602-1.123-2.995-2.707-3.228A48.394 48.394 0 0012 3c-2.392 0-4.744.175-7.043.513C3.373 3.746 2.25 5.14 2.25 6.741v6.018z"/>', $uri, $_pendingChatUnread ?: null) ?>
            </div>
            <div>
                <p class="text-text-dim text-[10px] font-semibold uppercase tracking-wider px-3 mb-2">Contenu</p>
                <?= navItem($_booksHref, 'Livres', '<path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 006 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 016 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 016-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0018 18a8.967 8.967 0 00-6 2.292m0-14.25v14.25"/>', $uri, $_pendingBooks ?: null, $_booksTitle) ?>
                <?= navItem('/admin/auteurs', 'Auteurs', '<path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z"/>', $uri) ?>
                <?= navItem('/admin/categories', 'Catégories', '<path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12.75V12A2.25 2.25 0 014.5 9.75h15A2.25 2.25 0 0121.75 12v.75m-8.69-6.44l-2.12-2.12a1.5 1.5 0 00-1.061-.44H4.5A2.25 2.25 0 002.25 6v12a2.25 2.25 0 002.25 2.25h15A2.25 2.25 0 0021.75 18V9a2.25 2.25 0 00-2.25-2.25h-5.379a1.5 1.5 0 01-1.06-.44z"/>', $uri) ?>
            </div>
            <div>
                <p class="text-text-dim text-[10px] font-semibold uppercase tracking-wider px-3 mb-2">Utilisateurs</p>
                <?= navItem('/admin/lecteurs', 'Tous les comptes', '<path stroke-linecap="round" stroke-linejoin="round" d="M18 18.72a9.094 9.094 0 003.741-.479 3 3 0 00-4.682-2.72m.94 3.198l.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0112 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 016 18.719m12 0a5.971 5.971 0 00-.941-3.197m0 0A5.995 5.995 0 0012 12.75a5.995 5.995 0 00-5.058 2.772m0 0a3 3 0 00-4.681 2.72 8.986 8.986 0 003.74.477m.94-3.197a5.971 5.971 0 00-.94 3.197M15 6.75a3 3 0 11-6 0 3 3 0 016 0zm6 3a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0zm-13.5 0a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0z"/>', $uri) ?>
                <?= navItem('/admin/candidatures', 'Candidatures', '<path stroke-linecap="round" stroke-linejoin="round" d="M19 7.5v3m0 0v3m0-3h3m-3 0h-3m-2.25-4.125a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zM4 19.235v-.11a6.375 6.375 0 0112.75 0v.109A12.318 12.318 0 0110.374 21c-2.331 0-4.512-.645-6.374-1.766z"/>', $uri, $_pendingCandidatures ?: null) ?>
                <?= navItem('/admin/services-editoriaux', 'Services éditoriaux', '<path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10"/>', $uri, $_pendingEditorial ?: null) ?>
            </div>
            <div>
                <p class="text-text-dim text-[10px] font-semibold uppercase tracking-wider px-3 mb-2">Commerce</p>
                <?= navItem('/admin/ventes', 'Ventes', '<path stroke-linecap="round" stroke-linejoin="round" d="M15.75 10.5V6a3.75 3.75 0 10-7.5 0v4.5m11.356-1.993l1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 01-1.12-1.243l1.264-12A1.125 1.125 0 015.513 7.5h12.974c.576 0 1.059.435 1.119 1.007zM8.625 10.5a.375.375 0 11-.75 0 .375.375 0 01.75 0zm7.5 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z"/>', $uri) ?>
                <?= navItem('/admin/abonnements', 'Abonnements', '<path stroke-linecap="round" stroke-linejoin="round" d="M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-3.75 3h15a2.25 2.25 0 002.25-2.25V6.75A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25v10.5A2.25 2.25 0 004.5 19.5z"/>', $uri) ?>
                <?= navItem('/admin/versements', 'Versements', '<path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0115.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 013 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 00-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 01-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 003 15h-.75M15 10.5a3 3 0 11-6 0 3 3 0 016 0zm3 0h.008v.008H18V10.5zm-12 0h.008v.008H6V10.5z"/>', $uri) ?>
            </div>
            <div>
                <p class="text-text-dim text-[10px] font-semibold uppercase tracking-wider px-3 mb-2">Système</p>
                <?= navItem('/admin/emails', 'Emails', '<path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 01-2.25 2.25h-15a2.25 2.25 0 01-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25m19.5 0v.243a2.25 2.25 0 01-1.07 1.916l-7.5 4.615a2.25 2.25 0 01-2.36 0L3.32 8.91a2.25 2.25 0 01-1.07-1.916V6.75"/>', $uri) ?>
                <?= navItem('/admin/parametres', 'Paramètres', '<path stroke-linecap="round" stroke-linejoin="round" d="M9.594 3.94c.09-.542.56-.94 1.11-.94h2.593c.55 0 1.02.398 1.11.94l.213 1.281c.063.374.313.686.645.87.074.04.147.083.22.127.324.196.72.257 1.075.124l1.217-.456a1.125 1.125 0 011.37.49l1.296 2.247a1.125 1.125 0 01-.26 1.431l-1.003.827c-.293.24-.438.613-.431.992a6.759 6.759 0 010 .255c-.007.378.138.75.43.99l1.005.828c.424.35.534.954.26 1.43l-1.298 2.247a1.125 1.125 0 01-1.369.491l-1.217-.456c-.355-.133-.75-.072-1.076.124a6.57 6.57 0 01-.22.128c-.331.183-.581.495-.644.869l-.213 1.28c-.09.543-.56.941-1.11.941h-2.594c-.55 0-1.02-.398-1.11-.94l-.213-1.281c-.062-.374-.312-.686-.644-.87a6.52 6.52 0 01-.22-.127c-.325-.196-.72-.257-1.076-.124l-1.217.456a1.125 1.125 0 01-1.369-.49l-1.297-2.247a1.125 1.125 0 01.26-1.431l1.004-.827c.292-.24.437-.613.43-.992a6.932 6.932 0 010-.255c.007-.378-.138-.75-.43-.99l-1.004-.828a1.125 1.125 0 01-.26-1.43l1.297-2.247a1.125 1.125 0 011.37-.491l1.216.456c.356.133.751.072 1.076-.124.072-.044.146-.087.22-.128.332-.183.582-.495.644-.869l.214-1.281z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>', $uri) ?>
                <?= navItem('/admin/journal', 'Journal', '<path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z"/>', $uri) ?>
            </div>
        </nav>

        <div class="p-3 border-t border-border">
            <a href="/" class="flex items-center gap-2 px-3 py-2 text-xs text-text-dim hover:text-accent transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 6H5.25A2.25 2.25 0 003 8.25v10.5A2.25 2.25 0 005.25 21h10.5A2.25 2.25 0 0018 18.75V10.5m-10.5 6L21 3m0 0h-5.25M21 3v5.25"/></svg>
                Voir le site public
            </a>
        </div>
    </aside>

    <!-- Contenu principal -->
    <div class="lg:ml-64 min-h-screen flex flex-col">
        <!-- Header admin avec menu utilisateur -->
        <header x-data="{ userMenu: false }" class="h-14 bg-surface/80 backdrop-blur border-b border-border flex items-center justify-between px-4 sm:px-6 sticky top-0 z-20">
            <div class="flex items-center gap-3">
                <button @click="sidebarOpen = true" class="lg:hidden text-text-muted hover:text-white p-1">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5M3.75 17.25h16.5"/></svg>
                </button>
                <h1 class="font-display font-semibold text-sm sm:text-base text-white"><?= e($titre ?? 'Administration') ?></h1>
            </div>
            <?php $adminUser = App\Lib\Auth::user(); ?>
            <div class="relative">
                <button @click="userMenu = !userMenu" @click.outside="userMenu = false" class="flex items-center gap-2 hover:bg-surface-2 rounded-full px-2 py-1 transition-colors">
                    <div class="w-8 h-8 rounded-full bg-accent text-black flex items-center justify-center text-xs font-bold font-display"><?= e(mb_strtoupper(mb_substr($adminUser->prenom ?? 'A', 0, 1))) ?></div>
                    <span class="hidden sm:block text-white text-sm"><?= e($adminUser->prenom ?? '') ?></span>
                    <svg class="w-4 h-4 text-text-dim" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5"/></svg>
                </button>
                <div x-show="userMenu" x-transition x-cloak class="absolute right-0 mt-2 w-56 bg-surface border border-border rounded-lg shadow-2xl py-2 z-50">
                    <div class="px-4 py-2.5 border-b border-border">
                        <p class="text-sm font-semibold text-white"><?= e(($adminUser->prenom ?? '') . ' ' . ($adminUser->nom ?? '')) ?></p>
                        <p class="text-xs text-text-dim mt-0.5"><?= e($adminUser->email ?? '') ?></p>
                    </div>
                    <a href="/" class="flex items-center gap-3 px-4 py-2 text-sm text-text-muted hover:text-accent hover:bg-surface-2 transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 6H5.25A2.25 2.25 0 003 8.25v10.5A2.25 2.25 0 005.25 21h10.5A2.25 2.25 0 0018 18.75V10.5m-10.5 6L21 3m0 0h-5.25M21 3v5.25"/></svg>
                        Retour au site
                    </a>
                    <a href="/mon-compte" class="flex items-center gap-3 px-4 py-2 text-sm text-text-muted hover:text-accent hover:bg-surface-2 transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z"/></svg>
                        Mon compte
                    </a>
                    <a href="/auteur" class="flex items-center gap-3 px-4 py-2 text-sm text-text-muted hover:text-accent hover:bg-surface-2 transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125"/></svg>
                        Espace auteur
                    </a>
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

        <main class="flex-grow p-4 sm:p-6">
            <?= $content ?>
        </main>
    </div>

</div><!-- fin x-data -->

<?php require BASE_PATH . '/app/views/partials/chat_widget.php'; ?>
</body>
</html>
