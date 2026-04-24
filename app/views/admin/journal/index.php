<p class="text-text-dim text-sm mb-6"><?= count($logs) ?> dernières actions</p>
<div class="overflow-x-auto">
<table class="w-full text-sm">
    <thead><tr class="border-b border-border text-text-dim text-xs uppercase tracking-wider text-left">
        <th class="py-3 px-3">Date</th><th class="py-3 px-2">Admin</th><th class="py-3 px-2">Action</th><th class="py-3 px-2">Entité</th><th class="py-3 px-2 hidden sm:table-cell">IP</th>
    </tr></thead>
    <tbody>
    <?php foreach ($logs as $l): ?>
        <tr class="border-b border-border/30 hover:bg-surface-2/50">
            <td class="py-3 px-3 text-text-dim text-xs"><?= date('d/m/Y H:i', strtotime($l->created_at)) ?></td>
            <td class="py-3 px-2 text-white"><?= e($l->prenom . ' ' . $l->nom) ?></td>
            <td class="py-3 px-2 text-accent text-xs font-medium"><?= e($l->action) ?></td>
            <td class="py-3 px-2 text-text-muted text-xs"><?= e(($l->entity_type ?? '') . ($l->entity_id ? ' #' . $l->entity_id : '')) ?></td>
            <td class="py-3 px-2 text-text-dim text-xs font-mono hidden sm:table-cell"><?= e($l->ip_address ?? '') ?></td>
        </tr>
    <?php endforeach; ?>
    <?php if (empty($logs)): ?><tr><td colspan="5" class="py-8 text-center text-text-dim">Aucune entrée.</td></tr><?php endif; ?>
    </tbody>
</table>
</div>
