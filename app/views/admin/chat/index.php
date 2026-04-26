<?php
/** @var array $conversations */
/** @var ?object $selectedConv */
/** @var array $selectedMessages */
/** @var string $currentFilter */
/** @var int $unreadCount */

$flashSuccess = flash('success');
$flashError   = flash('error');

$filters = [
    'toutes'    => 'Toutes',
    'non_lues'  => 'Non lues',
    'visiteurs' => 'Visiteurs',
    'membres'   => 'Membres',
    'archivees' => 'Archivées',
];
?>
<div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between mb-4">
    <div class="flex items-center gap-3">
        <h2 class="font-display text-xl text-white">Chat</h2>
        <?php if ($unreadCount > 0): ?>
            <span class="text-[11px] font-bold bg-red-500 text-white px-2 py-0.5 rounded-full"><?= (int) $unreadCount ?> non lues</span>
        <?php endif; ?>
    </div>
    <a href="/admin/chat/responses" class="self-start inline-flex items-center gap-2 px-3 py-2 rounded-lg bg-surface border border-border text-sm text-text-muted hover:text-accent hover:border-accent transition-colors">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9.813 15.904L9 18.75l-.813-2.846a4.5 4.5 0 00-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 003.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 003.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 00-3.09 3.09zM18.259 8.715L18 9.75l-.259-1.035a3.375 3.375 0 00-2.455-2.456L14.25 6l1.036-.259a3.375 3.375 0 002.455-2.456L18 2.25l.259 1.035a3.375 3.375 0 002.456 2.456L21.75 6l-1.035.259a3.375 3.375 0 00-2.456 2.456z"/></svg>
        Gérer les réponses du bot
    </a>
</div>

<?php if ($flashSuccess): ?>
    <div class="mb-3 bg-emerald-500/10 border border-emerald-500/30 text-emerald-300 text-sm px-3 py-2 rounded-lg"><?= e($flashSuccess) ?></div>
<?php endif; ?>
<?php if ($flashError): ?>
    <div class="mb-3 bg-red-500/10 border border-red-500/30 text-red-300 text-sm px-3 py-2 rounded-lg"><?= e($flashError) ?></div>
<?php endif; ?>

<div class="flex flex-wrap gap-2 mb-4">
    <?php foreach ($filters as $key => $label): ?>
        <a href="/admin/chat?filter=<?= e($key) ?>"
           class="px-3 py-1.5 text-xs rounded-full border transition-colors <?= $currentFilter === $key ? 'bg-accent text-black border-accent font-semibold' : 'bg-surface border-border text-text-muted hover:text-white' ?>">
            <?= e($label) ?>
        </a>
    <?php endforeach; ?>
</div>

<div class="grid grid-cols-1 lg:grid-cols-[340px_1fr] gap-4 min-h-[600px]">

    <!-- Liste des conversations -->
    <aside class="bg-surface border border-border rounded-xl overflow-hidden flex flex-col max-h-[calc(100vh-220px)]">
        <div class="px-4 py-3 border-b border-border flex items-center justify-between">
            <span class="text-xs font-semibold uppercase tracking-wide text-text-dim"><?= count($conversations) ?> conversation<?= count($conversations) > 1 ? 's' : '' ?></span>
        </div>
        <div class="overflow-y-auto flex-grow">
            <?php if (empty($conversations)): ?>
                <p class="text-text-dim text-sm text-center py-10 px-4">Aucune conversation pour ce filtre.</p>
            <?php else: ?>
                <?php foreach ($conversations as $c): ?>
                    <?php
                    $isActive = $selectedConv && (int) $selectedConv->id === (int) $c->id;
                    $name = $c->user_prenom
                        ? trim($c->user_prenom . ' ' . ($c->user_nom ?? ''))
                        : ($c->visitor_name ?: ($c->visitor_email ?: 'Visiteur anonyme'));
                    $preview = $c->last_message_content ?: '';
                    $preview = trim(preg_replace('/<[^>]+>/', '', $preview));
                    if (mb_strlen($preview) > 70) {
                        $preview = mb_substr($preview, 0, 70) . '…';
                    }
                    ?>
                    <a href="/admin/chat?filter=<?= e($currentFilter) ?>&conversation_id=<?= (int) $c->id ?>"
                       class="block px-4 py-3 border-b border-border hover:bg-surface-2 transition-colors <?= $isActive ? 'bg-accent/10 border-l-2 border-l-accent' : '' ?>">
                        <div class="flex items-start justify-between gap-2 mb-1">
                            <div class="flex items-center gap-2 min-w-0">
                                <?php if ($c->has_unread_for_admin): ?>
                                    <span class="w-2 h-2 rounded-full bg-red-500 flex-shrink-0" title="Non lu"></span>
                                <?php endif; ?>
                                <span class="text-sm font-semibold text-white truncate"><?= e($name) ?></span>
                            </div>
                            <span class="text-[10px] text-text-dim flex-shrink-0 whitespace-nowrap"><?= e(date('d/m H:i', strtotime((string) $c->last_message_at))) ?></span>
                        </div>
                        <div class="flex items-center gap-2">
                            <?php if ($c->user_id): ?>
                                <span class="text-[9px] uppercase tracking-wide font-bold bg-emerald-500/10 text-emerald-300 px-1.5 py-0.5 rounded">Membre</span>
                            <?php else: ?>
                                <span class="text-[9px] uppercase tracking-wide font-bold bg-surface-2 text-text-dim px-1.5 py-0.5 rounded">Visiteur</span>
                            <?php endif; ?>
                            <?php if ($c->statut === 'en_attente_admin'): ?>
                                <span class="text-[9px] uppercase tracking-wide font-bold bg-amber-500/10 text-amber-300 px-1.5 py-0.5 rounded">À répondre</span>
                            <?php elseif ($c->statut === 'repondue'): ?>
                                <span class="text-[9px] uppercase tracking-wide font-bold bg-blue-500/10 text-blue-300 px-1.5 py-0.5 rounded">Répondue</span>
                            <?php elseif ($c->statut === 'archivee'): ?>
                                <span class="text-[9px] uppercase tracking-wide font-bold bg-surface-2 text-text-dim px-1.5 py-0.5 rounded">Archivée</span>
                            <?php endif; ?>
                        </div>
                        <p class="text-xs text-text-muted mt-1 line-clamp-2"><?= e($preview) ?></p>
                    </a>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </aside>

    <!-- Détail conversation -->
    <section class="bg-surface border border-border rounded-xl flex flex-col max-h-[calc(100vh-220px)]">
        <?php if (!$selectedConv): ?>
            <div class="flex-grow flex items-center justify-center text-center p-8">
                <div>
                    <svg class="w-12 h-12 mx-auto text-text-dim mb-3" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12.76c0 1.6 1.123 2.994 2.707 3.227 1.068.157 2.148.279 3.238.364.466.037.893.281 1.153.671L12 21l2.652-3.978c.26-.39.687-.634 1.153-.67 1.09-.086 2.17-.208 3.238-.365 1.584-.233 2.707-1.626 2.707-3.228V6.741c0-1.602-1.123-2.995-2.707-3.228A48.394 48.394 0 0012 3c-2.392 0-4.744.175-7.043.513C3.373 3.746 2.25 5.14 2.25 6.741v6.018z"/></svg>
                    <p class="text-text-dim text-sm">Sélectionne une conversation à gauche pour la consulter.</p>
                </div>
            </div>
        <?php else: ?>
            <?php
            $headerName = $selectedConv->user_id
                ? 'Membre #' . (int) $selectedConv->user_id
                : ($selectedConv->visitor_name ?: ($selectedConv->visitor_email ?: 'Visiteur anonyme'));
            ?>
            <header class="px-4 py-3 border-b border-border flex flex-wrap items-center gap-2 justify-between">
                <div class="min-w-0">
                    <p class="text-sm font-semibold text-white truncate"><?= e($headerName) ?></p>
                    <p class="text-xs text-text-dim mt-0.5">
                        <?php if ($selectedConv->visitor_email): ?>
                            ✉️ <a href="mailto:<?= e($selectedConv->visitor_email) ?>" class="hover:text-accent"><?= e($selectedConv->visitor_email) ?></a> ·
                        <?php endif; ?>
                        Conversation #<?= (int) $selectedConv->id ?> · ouverte le <?= e(date('d/m/Y H:i', strtotime((string) $selectedConv->created_at))) ?>
                    </p>
                </div>
                <div class="flex items-center gap-2">
                    <?php if ($selectedConv->statut !== 'archivee'): ?>
                    <form action="/admin/chat/archive/<?= (int) $selectedConv->id ?>" method="POST" onsubmit="return confirm('Archiver cette conversation ?')">
                        <?= csrf_field() ?>
                        <button type="submit" class="text-xs px-3 py-1.5 rounded-lg bg-surface-2 border border-border text-text-muted hover:text-white transition-colors">Archiver</button>
                    </form>
                    <?php endif; ?>
                </div>
            </header>

            <div class="flex-grow overflow-y-auto p-4 space-y-2.5" id="vc-admin-msglist">
                <?php if (empty($selectedMessages)): ?>
                    <p class="text-text-dim text-sm text-center py-10">Aucun message dans cette conversation.</p>
                <?php else: ?>
                    <?php foreach ($selectedMessages as $m): ?>
                        <?php
                        $isFromUser = in_array($m->sender_type, ['visiteur', 'user'], true);
                        $bubbleClass = $isFromUser
                            ? 'bg-surface-2 border border-border self-start'
                            : ($m->sender_type === 'admin' ? 'bg-accent text-black self-end' : 'bg-blue-500/10 border border-blue-500/30 text-blue-100 self-start');
                        $label = $m->sender_type === 'bot' ? 'Bot' : ($m->sender_type === 'admin' ? 'Toi (admin)' : ($m->sender_type === 'user' ? 'Membre' : 'Visiteur'));
                        ?>
                        <div class="flex flex-col <?= $isFromUser ? 'items-start' : 'items-end' ?>">
                            <span class="text-[10px] text-text-dim mb-0.5"><?= e($label) ?> · <?= e(date('H:i', strtotime((string) $m->created_at))) ?></span>
                            <div class="max-w-[80%] px-3 py-2 rounded-xl text-sm <?= $bubbleClass ?>">
                                <?php if (in_array($m->sender_type, ['bot', 'admin'], true)): ?>
                                    <?= $m->content /* HTML simple autorisé pour bot/admin */ ?>
                                <?php else: ?>
                                    <?= nl2br(e($m->content)) ?>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <?php if ($selectedConv->statut !== 'archivee'): ?>
                <form action="/admin/chat/reply/<?= (int) $selectedConv->id ?>" method="POST" class="border-t border-border p-3 flex flex-col gap-2">
                    <?= csrf_field() ?>
                    <textarea name="content" rows="3" required maxlength="5000"
                              placeholder="Réponds à <?= e($headerName) ?>…"
                              class="w-full bg-bg border border-border rounded-lg px-3 py-2 text-sm text-white placeholder-text-dim focus:border-accent outline-none resize-none"></textarea>
                    <div class="flex items-center justify-between gap-2">
                        <span class="text-[10px] text-text-dim">HTML simple autorisé : &lt;a&gt;, &lt;strong&gt;, &lt;br&gt;</span>
                        <button type="submit" class="px-4 py-2 bg-accent text-black text-sm font-semibold rounded-lg hover:bg-accent-hover transition-colors">Envoyer</button>
                    </div>
                </form>
            <?php else: ?>
                <div class="border-t border-border p-3 text-center text-text-dim text-xs">
                    Cette conversation est archivée. Plus de réponse possible.
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </section>
</div>

<script>
// Auto-scroll des messages vers le bas à l'ouverture d'une conversation
document.addEventListener('DOMContentLoaded', function () {
    var box = document.getElementById('vc-admin-msglist');
    if (box) box.scrollTop = box.scrollHeight;
});

// Polling 30s : refresh badge sidebar + ping pour nouvelles conversations
(function () {
    var URL = '/admin/chat/api/unread-count';
    setInterval(function () {
        fetch(URL, { headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' } })
            .then(function (r) { return r.ok ? r.json() : null; })
            .then(function (data) {
                if (!data) return;
                var badge = document.querySelector('a[href="/admin/chat"] span.bg-red-500');
                if (badge) {
                    if (data.count > 0) {
                        badge.textContent = data.count;
                        badge.style.display = '';
                    } else {
                        badge.style.display = 'none';
                    }
                }
            })
            .catch(function () {});
    }, 30000);
})();
</script>
