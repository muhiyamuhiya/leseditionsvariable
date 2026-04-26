<?php
/** @var array  $rows */
/** @var array  $templates  Liste des templates distincts pour le filtre */
/** @var int    $total */
/** @var int    $page */
/** @var int    $perPage */
/** @var array  $filters    q, template, result, from, to */
$current = 'sent';
require __DIR__ . '/_tabs.php';

$totalPages = max(1, (int) ceil($total / $perPage));
?>

<!-- Filtres -->
<form method="GET" action="/admin/emails/sent" class="bg-surface border border-border rounded-lg p-4 mb-6">
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3">
        <div>
            <label class="block text-text-dim text-[10px] uppercase tracking-wider mb-1">Email contient</label>
            <input type="text" name="q" value="<?= e($filters['q'] ?? '') ?>" placeholder="@gmail.com"
                   class="w-full bg-surface-2 border border-border rounded px-3 py-2 text-sm text-white outline-none focus:border-accent">
        </div>
        <div>
            <label class="block text-text-dim text-[10px] uppercase tracking-wider mb-1">Template</label>
            <select name="template" class="w-full bg-surface-2 border border-border rounded px-3 py-2 text-sm text-white outline-none focus:border-accent">
                <option value="">Tous</option>
                <?php foreach ($templates as $t): ?>
                    <option value="<?= e($t) ?>" <?= ($filters['template'] ?? '') === $t ? 'selected' : '' ?>><?= e($t) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div>
            <label class="block text-text-dim text-[10px] uppercase tracking-wider mb-1">Résultat</label>
            <select name="result" class="w-full bg-surface-2 border border-border rounded px-3 py-2 text-sm text-white outline-none focus:border-accent">
                <option value="">Tous</option>
                <option value="sent"  <?= ($filters['result'] ?? '') === 'sent' ? 'selected' : '' ?>>sent</option>
                <option value="error" <?= ($filters['result'] ?? '') === 'error' ? 'selected' : '' ?>>error</option>
            </select>
        </div>
        <div>
            <label class="block text-text-dim text-[10px] uppercase tracking-wider mb-1">Du</label>
            <input type="date" name="from" value="<?= e($filters['from'] ?? '') ?>"
                   class="w-full bg-surface-2 border border-border rounded px-3 py-2 text-sm text-white outline-none focus:border-accent">
        </div>
        <div>
            <label class="block text-text-dim text-[10px] uppercase tracking-wider mb-1">Au</label>
            <input type="date" name="to" value="<?= e($filters['to'] ?? '') ?>"
                   class="w-full bg-surface-2 border border-border rounded px-3 py-2 text-sm text-white outline-none focus:border-accent">
        </div>
    </div>
    <div class="flex items-center gap-3 mt-3">
        <button type="submit" class="btn-primary text-sm">Filtrer</button>
        <a href="/admin/emails/sent" class="text-text-dim hover:text-accent text-sm">Réinitialiser</a>
        <span class="ml-auto text-text-muted text-xs">
            <strong class="text-white"><?= number_format($total, 0, ',', ' ') ?></strong> envoi<?= $total > 1 ? 's' : '' ?>
        </span>
    </div>
</form>

<!-- Table -->
<?php if (empty($rows)): ?>
    <div class="bg-surface border border-border rounded-lg p-8 text-center">
        <p class="text-text-muted">Aucun envoi pour ces critères.</p>
    </div>
<?php else: ?>
    <div class="bg-surface border border-border rounded-lg overflow-hidden">
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-surface-2 border-b border-border text-text-dim text-[11px] uppercase tracking-wider">
                    <th class="text-left px-4 py-2.5 w-44">Date</th>
                    <th class="text-left px-4 py-2.5">Destinataire</th>
                    <th class="text-left px-4 py-2.5 w-44">Template</th>
                    <th class="text-left px-4 py-2.5">Sujet</th>
                    <th class="text-left px-4 py-2.5 w-20">Résultat</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($rows as $r): ?>
                    <tr class="border-b border-border last:border-0 hover:bg-surface-2/50">
                        <td class="px-4 py-2 text-text-muted text-xs font-mono whitespace-nowrap">
                            <?= e(date('d/m/Y H:i', strtotime((string) $r->sent_at))) ?>
                        </td>
                        <td class="px-4 py-2">
                            <p class="text-white text-xs truncate max-w-[260px]"><?= e($r->to_email) ?></p>
                            <?php if ($r->prenom || $r->nom): ?>
                                <p class="text-text-dim text-[11px]"><?= e(trim(($r->prenom ?? '') . ' ' . ($r->nom ?? ''))) ?></p>
                            <?php endif; ?>
                        </td>
                        <td class="px-4 py-2">
                            <?php if ($r->template): ?>
                                <a href="/admin/emails/preview/<?= e($r->template) ?>" class="text-accent text-xs font-mono hover:underline"><?= e($r->template) ?></a>
                            <?php else: ?>
                                <span class="text-text-dim text-xs">—</span>
                            <?php endif; ?>
                        </td>
                        <td class="px-4 py-2">
                            <p class="text-text-muted text-xs truncate max-w-[420px]" title="<?= e($r->subject ?? '') ?>"><?= e($r->subject ?? '') ?></p>
                            <?php if ($r->error_message): ?>
                                <p class="text-red-400 text-[11px] truncate max-w-[420px]" title="<?= e($r->error_message) ?>"><?= e($r->error_message) ?></p>
                            <?php endif; ?>
                        </td>
                        <td class="px-4 py-2">
                            <?php if ($r->result === 'sent'): ?>
                                <span class="text-emerald-400 text-[10px] uppercase tracking-wider font-semibold">✓ sent</span>
                            <?php else: ?>
                                <span class="text-red-400 text-[10px] uppercase tracking-wider font-semibold">✗ error</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <?php if ($totalPages > 1): ?>
        <?php
        $qs = $filters; unset($qs['page']);
        $base = '/admin/emails/sent?' . http_build_query(array_filter($qs)) . '&page=';
        ?>
        <div class="flex justify-center gap-2 mt-4">
            <?php if ($page > 1): ?>
                <a href="<?= e($base . ($page - 1)) ?>" class="px-3 py-1.5 text-xs bg-surface border border-border rounded hover:border-accent text-text-muted">← Précédent</a>
            <?php endif; ?>
            <span class="px-3 py-1.5 text-xs text-text-dim">Page <?= $page ?> / <?= $totalPages ?></span>
            <?php if ($page < $totalPages): ?>
                <a href="<?= e($base . ($page + 1)) ?>" class="px-3 py-1.5 text-xs bg-surface border border-border rounded hover:border-accent text-text-muted">Suivant →</a>
            <?php endif; ?>
        </div>
    <?php endif; ?>
<?php endif; ?>
