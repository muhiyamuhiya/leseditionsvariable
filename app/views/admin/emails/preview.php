<?php
/** @var string $template */
/** @var array  $tpl           Définition du template courant (label, fixtures, has_pdf) */
/** @var array  $allTemplates  Catalogue complet — pour le sélecteur */
$current = 'templates';
require __DIR__ . '/_tabs.php';
?>
<?php $s = flash('success'); if ($s): ?><div class="bg-emerald-500/10 border border-emerald-500/30 text-emerald-400 px-4 py-3 rounded-lg mb-6 text-sm"><?= e($s) ?></div><?php endif; ?>
<?php $err = flash('error'); if ($err): ?><div class="bg-red-500/10 border border-red-500/30 text-red-400 px-4 py-3 rounded-lg mb-6 text-sm"><?= e($err) ?></div><?php endif; ?>

<div class="flex flex-col lg:flex-row gap-6 mb-4">
    <!-- Sélecteur de template -->
    <div class="lg:w-72 shrink-0">
        <a href="/admin/emails" class="text-text-dim hover:text-accent text-sm mb-3 inline-block">← Tous les templates</a>
        <div class="bg-surface border border-border rounded-lg overflow-hidden">
            <div class="px-3 py-2 bg-surface-2 border-b border-border">
                <p class="text-text-dim text-xs uppercase tracking-wider">Template courant</p>
                <p class="text-white text-sm font-semibold mt-1"><?= e($tpl['label']) ?></p>
                <p class="text-text-dim text-xs font-mono mt-1"><?= e($template) ?>.php</p>
            </div>
            <ul class="max-h-[600px] overflow-auto">
                <?php foreach ($allTemplates as $slug => $other): ?>
                    <li>
                        <a href="/admin/emails/preview/<?= e($slug) ?>"
                           class="block px-3 py-2 text-xs border-b border-border <?= $slug === $template ? 'bg-accent/10 text-accent' : 'text-text-muted hover:bg-surface-2' ?>">
                            <?= e($other['label']) ?>
                        </a>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>

        <!-- Action : envoi test -->
        <form method="POST" action="/admin/emails/preview/<?= e($template) ?>/test" class="mt-4 bg-surface border border-border rounded-lg p-3">
            <?= csrf_field() ?>
            <p class="text-text-dim text-xs mb-2">Envoie-toi cet email avec les données fictives à ton adresse admin.</p>
            <button type="submit" class="btn-primary w-full text-sm">📤 Envoyer un test à mon email</button>
            <?php if (!empty($tpl['has_pdf'])): ?>
                <p class="text-amber-400 text-[11px] mt-2">PDF reçu joint à l'envoi.</p>
            <?php endif; ?>
        </form>

        <!-- Fixtures (debug) -->
        <details class="mt-4 bg-surface border border-border rounded-lg p-3">
            <summary class="text-text-dim text-xs cursor-pointer uppercase tracking-wider">Données fictives</summary>
            <pre class="text-text-muted text-[11px] font-mono mt-3 whitespace-pre-wrap break-all"><?= e(json_encode($tpl['fixtures'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)) ?></pre>
        </details>
    </div>

    <!-- Preview iframe -->
    <div class="flex-grow">
        <div class="bg-surface border border-border rounded-lg overflow-hidden">
            <div class="px-3 py-2 bg-surface-2 border-b border-border flex items-center justify-between">
                <p class="text-text-muted text-xs">Aperçu rendu (iframe responsive)</p>
                <a href="/admin/emails/preview/<?= e($template) ?>?raw=1" target="_blank" class="text-accent text-xs hover:underline">Ouvrir dans un nouvel onglet ↗</a>
            </div>
            <iframe src="/admin/emails/preview/<?= e($template) ?>?raw=1"
                    style="width:100%;height:80vh;border:0;background:#F4F1EC;"
                    title="Aperçu email — <?= e($template) ?>"></iframe>
        </div>
    </div>
</div>
