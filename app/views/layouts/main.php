<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($titre ?? 'Accueil') ?> — Les éditions Variable</title>
    <meta name="description" content="<?= e($description ?? 'Les éditions Variable — La plateforme de lecture pour l\'Afrique francophone.') ?>">

    <meta property="og:title" content="<?= e($titre ?? 'Les éditions Variable') ?>">
    <meta property="og:description" content="<?= e($description ?? 'La plateforme de lecture pour l\'Afrique francophone.') ?>">
    <meta property="og:type" content="website">
    <meta property="og:locale" content="fr_FR">

    <meta name="csrf-token" content="<?= csrf_token() ?>">
    <link rel="icon" type="image/png" href="/assets/images/logo.png">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Poppins:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Inter', 'sans-serif'],
                        display: ['Poppins', 'sans-serif'],
                    },
                    colors: {
                        bg: '#0B0B0F',
                        surface: '#141419',
                        'surface-2': '#1C1C24',
                        border: '#2A2A35',
                        'text-main': '#FFFFFF',
                        'text-muted': '#A0A0B0',
                        'text-dim': '#6B6B7D',
                        accent: '#F59E0B',
                        'accent-hover': '#FBBF24',
                    },
                    screens: {
                        // Breakpoints standards Tailwind explicités pour clarté
                        'xs': '375px',  // iPhone SE / petits téléphones
                        'sm': '640px',  // gros téléphones / petites tablettes
                        'md': '768px',  // tablettes
                        'lg': '1024px', // petits laptops
                        'xl': '1280px', // desktop standard
                        '2xl': '1536px', // grand écran
                    },
                    spacing: {
                        'safe-bottom': 'env(safe-area-inset-bottom)',
                        'safe-top': 'env(safe-area-inset-top)',
                    }
                }
            }
        }
    </script>

    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <link rel="stylesheet" href="<?= asset('css/style.css') ?>">
    <style>[x-cloak] { display: none !important; }</style>
</head>
<body class="min-h-screen flex flex-col bg-bg text-white font-sans antialiased">

    <?php require BASE_PATH . '/app/views/partials/header.php'; ?>

    <main class="flex-grow">
        <?= $content ?>
    </main>

    <?php require BASE_PATH . '/app/views/partials/footer.php'; ?>

    <script>
    function notificationBell() {
        return {
            open: false,
            loading: false,
            unreadCount: 0,
            notifications: [],

            csrf() { return document.querySelector('meta[name="csrf-token"]')?.content || ''; },

            async loadCount() {
                try {
                    const res = await fetch('/notifications/api/count', { headers: { 'Accept': 'application/json' } });
                    const data = await res.json();
                    this.unreadCount = data.count || 0;
                } catch (e) {}
            },

            async toggle() {
                this.open = !this.open;
                if (this.open) await this.loadNotifications();
            },

            async loadNotifications() {
                this.loading = true;
                try {
                    const res = await fetch('/notifications/api/recent', { headers: { 'Accept': 'application/json' } });
                    const data = await res.json();
                    this.notifications = data.notifications || [];
                    this.unreadCount = data.unread_count || 0;
                } catch (e) {}
                this.loading = false;
            },

            async markAsRead(id) {
                const notif = this.notifications.find(n => n.id == id);
                if (notif && notif.read_at) return;
                try {
                    await fetch('/notifications/' + id + '/lire', {
                        method: 'POST',
                        headers: { 'X-CSRF-Token': this.csrf(), 'X-Requested-With': 'XMLHttpRequest' }
                    });
                    if (notif) notif.read_at = new Date().toISOString();
                    this.unreadCount = Math.max(0, this.unreadCount - 1);
                } catch (e) {}
            },

            async markAllRead() {
                try {
                    await fetch('/notifications/lire-toutes', {
                        method: 'POST',
                        headers: { 'X-CSRF-Token': this.csrf(), 'X-Requested-With': 'XMLHttpRequest' }
                    });
                    this.unreadCount = 0;
                    const now = new Date().toISOString();
                    this.notifications.forEach(n => { if (!n.read_at) n.read_at = now; });
                } catch (e) {}
            },

            iconFor(key) {
                const icons = { bell:'🔔', check:'✅', star:'⭐', book:'📖', alert:'⚠️', mail:'✉️', cart:'🛒', premium:'✨' };
                return icons[key] || '🔔';
            },

            timeAgo(dateStr) {
                if (!dateStr) return '';
                const d = new Date(dateStr.replace(' ', 'T'));
                const seconds = Math.floor((Date.now() - d.getTime()) / 1000);
                if (seconds < 60) return 'à l\'instant';
                if (seconds < 3600) return 'il y a ' + Math.floor(seconds / 60) + ' min';
                if (seconds < 86400) return 'il y a ' + Math.floor(seconds / 3600) + ' h';
                return 'il y a ' + Math.floor(seconds / 86400) + ' j';
            }
        }
    }

    function liveSearch() {
        return {
            q: '',
            results: [],
            searchOpen: false,
            timer: null,
            toggle() {
                this.searchOpen = !this.searchOpen;
                if (this.searchOpen) {
                    this.$nextTick(() => this.$refs.input?.focus());
                } else {
                    this.results = [];
                    this.q = '';
                }
            },
            close() {
                this.searchOpen = false;
                this.results = [];
                this.q = '';
            },
            goFull() {
                if (this.q.trim().length > 0) {
                    window.location.href = '/catalogue?q=' + encodeURIComponent(this.q.trim());
                }
            },
            async search() {
                if (this.q.trim().length < 2) {
                    this.results = [];
                    return;
                }
                try {
                    const res = await fetch('/api/recherche?q=' + encodeURIComponent(this.q.trim()));
                    this.results = await res.json();
                } catch (e) {
                    this.results = [];
                }
            }
        }
    }
    </script>

    <?php require BASE_PATH . '/app/views/partials/tawk.php'; ?>
</body>
</html>
