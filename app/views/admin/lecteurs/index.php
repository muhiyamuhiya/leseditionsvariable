<p class="text-text-dim text-sm mb-6"><?= count($lecteurs) ?> lecteur(s)</p>
<div class="overflow-x-auto">
<table class="w-full text-sm">
    <thead><tr class="border-b border-border text-text-dim text-xs uppercase tracking-wider text-left">
        <th class="py-3 px-3">Nom</th><th class="py-3 px-2">Email</th><th class="py-3 px-2 hidden sm:table-cell">Pays</th><th class="py-3 px-2 hidden md:table-cell">Inscrit le</th><th class="py-3 px-2">Livres</th><th class="py-3 px-2">Abo</th>
    </tr></thead>
    <tbody>
    <?php foreach ($lecteurs as $l): ?>
        <tr class="border-b border-border/30 hover:bg-surface-2/50">
            <td class="py-3 px-3 text-white font-medium"><?= e($l->prenom . ' ' . $l->nom) ?></td>
            <td class="py-3 px-2 text-text-muted text-xs"><?= e($l->email) ?></td>
            <td class="py-3 px-2 text-text-muted hidden sm:table-cell"><?= e($l->pays ?? '-') ?></td>
            <td class="py-3 px-2 text-text-dim text-xs hidden md:table-cell"><?= date('d/m/Y', strtotime($l->created_at)) ?></td>
            <td class="py-3 px-2 text-text-muted"><?= $l->nb_livres ?></td>
            <td class="py-3 px-2"><?= $l->abo_actif ? '<span class="text-emerald-400 text-xs font-medium">Actif</span>' : '<span class="text-text-dim text-xs">Non</span>' ?></td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>
</div>
