<?php
/**
 * Tabs de navigation entre les sections de l'admin emails.
 * Variable attendue : $current ∈ {'templates', 'sequences', 'sent'}
 */
$tabs = [
    'templates' => ['label' => 'Templates',  'href' => '/admin/emails'],
    'sequences' => ['label' => 'Séquences',  'href' => '/admin/emails/sequences'],
    'sent'      => ['label' => 'Historique', 'href' => '/admin/emails/sent'],
];
$active = $current ?? 'templates';
?>
<div class="border-b border-border mb-6 -mt-2">
    <nav class="flex gap-1">
        <?php foreach ($tabs as $key => $tab): ?>
            <a href="<?= e($tab['href']) ?>"
               class="px-4 py-2.5 text-sm font-medium border-b-2 -mb-[2px] transition-colors
                      <?= $active === $key
                            ? 'border-accent text-accent'
                            : 'border-transparent text-text-muted hover:text-white' ?>">
                <?= e($tab['label']) ?>
            </a>
        <?php endforeach; ?>
    </nav>
</div>
