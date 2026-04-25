<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title><?= e($book->titre) ?> — Liseuse</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = { theme: { extend: { colors: {
            bg: '#0B0B0F', surface: '#141419', 'surface-2': '#1C1C24',
            border: '#2A2A35', accent: '#F59E0B', 'accent-hover': '#FBBF24',
            'text-muted': '#A0A0B0', 'text-dim': '#6B6B7D'
        }}}}
    </script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.min.js"></script>
    <style>
        body { margin:0; overflow:hidden; background:#0B0B0F; font-family:'Poppins',sans-serif; }
        #pdf-canvas { display:block; margin:0 auto; user-select:none; -webkit-user-select:none; }
        [x-cloak] { display:none!important; }
    </style>
</head>
<body class="h-screen flex flex-col bg-bg text-white" oncontextmenu="return false">

    <!-- Barre haute -->
    <div class="h-12 sm:h-14 bg-surface border-b border-border flex items-center px-3 sm:px-5 gap-3 flex-shrink-0 z-20">
        <a href="/livre/<?= e($book->slug) ?>" class="text-text-muted hover:text-white transition-colors p-1">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
        </a>
        <div class="flex-grow min-w-0 text-center">
            <p class="text-white text-xs sm:text-sm font-semibold truncate"><?= e($book->titre) ?></p>
            <p class="text-text-dim text-[10px] sm:text-xs truncate"><?= e(book_author_name($book)) ?></p>
        </div>
        <div class="text-text-dim text-xs whitespace-nowrap">
            <span id="page-display">1</span> / <span id="total-pages"><?= $maxPages ?></span>
        </div>
    </div>

    <!-- Barre de progression -->
    <div class="h-0.5 bg-border flex-shrink-0">
        <div id="progress-bar" class="h-full bg-accent transition-all duration-300" style="width:0%"></div>
    </div>

    <?php if ($mode === 'extrait' && !$hasFullAccess): ?>
    <div class="bg-accent/10 border-b border-accent/30 px-4 py-2 text-center text-xs sm:text-sm text-accent flex-shrink-0">
        Extrait gratuit — <?= FREE_PREVIEW_PAGES ?> pages.
        <a href="/livre/<?= e($book->slug) ?>" class="underline font-medium ml-1">Acheter le livre complet</a>
    </div>
    <?php endif; ?>

    <!-- Zone PDF -->
    <div id="pdf-container" class="flex-grow overflow-auto flex items-start justify-center py-4">
        <canvas id="pdf-canvas"></canvas>
    </div>

    <!-- Contrôles bas -->
    <div class="h-12 sm:h-14 bg-surface border-t border-border flex items-center justify-center gap-3 sm:gap-6 px-4 flex-shrink-0 z-20">
        <button id="btn-prev" class="p-2 text-text-muted hover:text-white transition-colors disabled:opacity-30" disabled>
            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5"/></svg>
        </button>
        <div class="flex items-center gap-2 text-sm">
            <input type="number" id="page-input" min="1" max="<?= $maxPages ?>" value="1"
                   class="w-12 bg-surface-2 border border-border rounded text-center text-white text-sm py-1 outline-none focus:border-accent">
        </div>
        <button id="btn-next" class="p-2 text-text-muted hover:text-white transition-colors disabled:opacity-30">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5"/></svg>
        </button>
        <div class="hidden sm:flex items-center gap-2 ml-4 border-l border-border pl-4">
            <button id="btn-zoom-out" class="p-1 text-text-muted hover:text-white transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 12h-15"/></svg>
            </button>
            <span id="zoom-display" class="text-text-dim text-xs w-10 text-center">100%</span>
            <button id="btn-zoom-in" class="p-1 text-text-muted hover:text-white transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
            </button>
        </div>
    </div>

<script>
pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.worker.min.js';

const CONFIG = {
    pdfUrl: '/lire/pdf/<?= e($sessionToken) ?>/<?= e($mode) ?>',
    sessionToken: '<?= e($sessionToken) ?>',
    startPage: <?= $lastPage ?>,
    maxPages: <?= $maxPages ?>,
    saveInterval: 30000
};

let pdfDoc = null, currentPage = CONFIG.startPage, scale = 1.0, startTime = Date.now();
const canvas = document.getElementById('pdf-canvas');
const ctx = canvas.getContext('2d');

// Charger le PDF
pdfjsLib.getDocument(CONFIG.pdfUrl).promise.then(pdf => {
    pdfDoc = pdf;
    const totalRendered = Math.min(pdf.numPages, CONFIG.maxPages);
    document.getElementById('total-pages').textContent = totalRendered;
    document.getElementById('page-input').max = totalRendered;
    CONFIG.maxPages = totalRendered;
    renderPage(currentPage);
}).catch(err => {
    document.getElementById('pdf-container').innerHTML =
        '<p class="text-red-400 text-center p-8">Erreur de chargement du PDF.</p>';
});

function renderPage(num) {
    if (!pdfDoc || num < 1 || num > CONFIG.maxPages) return;
    currentPage = num;

    pdfDoc.getPage(num).then(page => {
        const container = document.getElementById('pdf-container');
        const containerWidth = container.clientWidth - 32;
        const viewport = page.getViewport({ scale: 1 });
        const fitScale = Math.min(containerWidth / viewport.width, 1.5);
        const finalScale = fitScale * scale;
        const scaledViewport = page.getViewport({ scale: finalScale });

        canvas.width = scaledViewport.width;
        canvas.height = scaledViewport.height;

        page.render({ canvasContext: ctx, viewport: scaledViewport }).promise.then(() => {
            updateUI();
        });
    });
}

function updateUI() {
    document.getElementById('page-display').textContent = currentPage;
    document.getElementById('page-input').value = currentPage;
    document.getElementById('progress-bar').style.width = ((currentPage / CONFIG.maxPages) * 100) + '%';
    document.getElementById('btn-prev').disabled = currentPage <= 1;
    document.getElementById('btn-next').disabled = currentPage >= CONFIG.maxPages;
    document.getElementById('zoom-display').textContent = Math.round(scale * 100) + '%';
}

// Navigation
document.getElementById('btn-prev').addEventListener('click', () => renderPage(currentPage - 1));
document.getElementById('btn-next').addEventListener('click', () => renderPage(currentPage + 1));
document.getElementById('page-input').addEventListener('change', function() {
    renderPage(parseInt(this.value) || 1);
});

// Zoom
document.getElementById('btn-zoom-in').addEventListener('click', () => { scale = Math.min(3, scale + 0.2); renderPage(currentPage); });
document.getElementById('btn-zoom-out').addEventListener('click', () => { scale = Math.max(0.4, scale - 0.2); renderPage(currentPage); });

// Clavier
document.addEventListener('keydown', e => {
    if (e.key === 'ArrowLeft' || e.key === 'ArrowUp') { e.preventDefault(); renderPage(currentPage - 1); }
    if (e.key === 'ArrowRight' || e.key === 'ArrowDown') { e.preventDefault(); renderPage(currentPage + 1); }
    // Anti-copie basique
    if ((e.ctrlKey || e.metaKey) && ['s','p','c'].includes(e.key.toLowerCase())) e.preventDefault();
    if (e.key === 'F12') e.preventDefault();
});

// Swipe mobile
let touchStartX = 0;
canvas.addEventListener('touchstart', e => { touchStartX = e.changedTouches[0].screenX; }, { passive: true });
canvas.addEventListener('touchend', e => {
    const diff = e.changedTouches[0].screenX - touchStartX;
    if (Math.abs(diff) > 50) { diff > 0 ? renderPage(currentPage - 1) : renderPage(currentPage + 1); }
});

// Sauvegarde progression
const CSRF_TOKEN = '<?= csrf_token() ?>';
function saveProgress() {
    const elapsed = Math.round((Date.now() - startTime) / 1000);
    fetch('/lire/progress', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-Token': CSRF_TOKEN },
        body: JSON.stringify({ session_token: CONFIG.sessionToken, page: currentPage, temps_secondes: elapsed })
    }).catch(() => {});
}
setInterval(saveProgress, CONFIG.saveInterval);
window.addEventListener('beforeunload', saveProgress);
</script>
</body>
</html>
