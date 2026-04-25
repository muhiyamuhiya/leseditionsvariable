<?php
$success = flash('success');
$icons = [
    'bell' => '🔔', 'check' => '✅', 'star' => '⭐', 'book' => '📖',
    'alert' => '⚠️', 'mail' => '✉️', 'cart' => '🛒', 'premium' => '✨',
];
?>
<section class="py-8 sm:py-12">
    <div class="max-w-[800px] mx-auto px-4 sm:px-6">

        <?php if ($success): ?>
            <div class="bg-emerald-500/10 border border-emerald-500/30 text-emerald-400 px-4 py-3 rounded-lg mb-5 text-sm"><?= e($success) ?></div>
        <?php endif; ?>

        <div class="flex items-center justify-between flex-wrap gap-3 mb-7">
            <div>
                <h1 class="font-display font-bold text-2xl sm:text-3xl text-white">Mes notifications</h1>
                <p class="text-text-dim text-xs mt-1">
                    <?= count($notifications) ?> au total<?php if ($unread > 0): ?> · <span class="text-accent"><?= $unread ?> non lue<?= $unread > 1 ? 's' : '' ?></span><?php endif; ?>
                </p>
            </div>
            <?php if ($unread > 0): ?>
                <form action="/notifications/lire-toutes" method="POST">
                    <?= csrf_field() ?>
                    <button type="submit" class="btn-secondary text-sm">Tout marquer lu</button>
                </form>
            <?php endif; ?>
        </div>

        <?php if (empty($notifications)): ?>
            <div class="bg-surface border border-border rounded-xl p-10 text-center">
                <div class="w-16 h-16 rounded-full bg-surface-2 flex items-center justify-center mx-auto mb-4">
                    <svg class="w-8 h-8 text-text-dim" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75v-.7V9A6 6 0 006 9v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0"/></svg>
                </div>
                <p class="text-text-muted">Aucune notification pour le moment.</p>
            </div>
        <?php else: ?>
            <div class="bg-surface border border-border rounded-xl overflow-hidden">
                <?php foreach ($notifications as $n): ?>
                    <?php $isRead = !empty($n->read_at); ?>
                    <div class="flex items-start gap-3 p-4 border-b border-border/50 last:border-0 <?= $isRead ? '' : 'bg-accent/5' ?>">
                        <div class="flex-shrink-0 w-10 h-10 rounded-full bg-accent/15 flex items-center justify-center">
                            <span class="text-lg" aria-hidden="true"><?= $icons[$n->icon] ?? '🔔' ?></span>
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="flex items-start justify-between gap-2">
                                <p class="font-medium text-white text-sm">
                                    <?php if (!empty($n->link_url)): ?>
                                        <a href="<?= e($n->link_url) ?>" class="hover:text-accent transition-colors"><?= e($n->title) ?></a>
                                    <?php else: ?>
                                        <?= e($n->title) ?>
                                    <?php endif; ?>
                                </p>
                                <?php if (!$isRead): ?>
                                    <span class="text-[10px] font-semibold px-2 py-0.5 rounded bg-accent/20 text-accent flex-shrink-0">Nouveau</span>
                                <?php endif; ?>
                            </div>
                            <?php if (!empty($n->message)): ?>
                                <p class="text-text-muted text-sm mt-1"><?= e($n->message) ?></p>
                            <?php endif; ?>
                            <p class="text-text-dim text-[11px] mt-2"><?= date('d/m/Y H:i', strtotime($n->created_at)) ?></p>
                        </div>
                        <form action="/notifications/<?= (int) $n->id ?>/supprimer" method="POST" class="flex-shrink-0">
                            <?= csrf_field() ?>
                            <button type="submit" class="text-text-dim hover:text-rose-400 transition-colors p-1" aria-label="Supprimer">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0"/></svg>
                            </button>
                        </form>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

    </div>
</section>
