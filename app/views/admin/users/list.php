<?php
$success = flash('admin_success');
$error = flash('admin_error');

$roleBadge = [
    'admin'   => 'bg-red-500/20 text-red-400',
    'auteur'  => 'bg-purple-500/20 text-purple-300',
    'lecteur' => 'bg-blue-500/20 text-blue-300',
];

// URL helper pour les pills de filtre (préserve les autres params)
$buildUrl = function (array $overrides) use ($search, $role, $statut) {
    $params = array_filter([
        'q'      => $search,
        'role'   => $role !== 'tous' ? $role : null,
        'statut' => $statut !== 'tous' ? $statut : null,
    ], fn($v) => $v !== null && $v !== '');
    foreach ($overrides as $k => $v) {
        if ($v === null || $v === '') unset($params[$k]); else $params[$k] = $v;
    }
    return '/admin/lecteurs' . ($params ? '?' . http_build_query($params) : '');
};
?>
<?php if ($success): ?>
    <div class="bg-emerald-500/10 border border-emerald-500/30 text-emerald-400 px-4 py-3 rounded-lg mb-6 text-sm"><?= e($success) ?></div>
<?php endif; ?>
<?php if ($error): ?>
    <div class="bg-red-500/10 border border-red-500/30 text-red-400 px-4 py-3 rounded-lg mb-6 text-sm"><?= e($error) ?></div>
<?php endif; ?>

<div class="flex items-center justify-between mb-5 flex-wrap gap-3">
    <div>
        <h1 class="font-display font-bold text-2xl text-white">Gestion des utilisateurs <span class="text-text-dim text-base">(<?= (int) $total ?>)</span></h1>
    </div>
    <form action="/admin/lecteurs" method="GET" class="flex gap-2 flex-wrap">
        <?php if ($role !== 'tous'): ?><input type="hidden" name="role" value="<?= e($role) ?>"><?php endif; ?>
        <?php if ($statut !== 'tous'): ?><input type="hidden" name="statut" value="<?= e($statut) ?>"><?php endif; ?>
        <input type="text" name="q" value="<?= e($search) ?>" placeholder="Email, prénom, nom…"
               class="bg-surface-2 border border-border rounded px-3 py-2 text-sm text-white outline-none focus:border-accent w-64">
        <button type="submit" class="btn-primary text-sm">Rechercher</button>
        <?php if ($search !== ''): ?>
            <a href="<?= e($buildUrl(['q' => null])) ?>" class="text-text-dim hover:text-accent text-sm py-2">Effacer</a>
        <?php endif; ?>
    </form>
</div>

<!-- Pills de filtre -->
<div class="flex flex-wrap items-center gap-2 mb-6 text-xs">
    <span class="text-text-dim mr-1">Rôle :</span>
    <?php foreach (['tous' => 'Tous', 'lecteur' => 'Lecteurs', 'auteur' => 'Auteurs', 'admin' => 'Admins'] as $key => $label): ?>
        <a href="<?= e($buildUrl(['role' => $key === 'tous' ? null : $key])) ?>"
           class="px-2.5 py-1 rounded-full border <?= $role === $key || ($role === 'tous' && $key === 'tous') ? 'bg-accent text-black border-accent font-semibold' : 'bg-surface border-border text-text-muted hover:border-accent hover:text-accent' ?> transition-colors">
            <?= e($label) ?>
        </a>
    <?php endforeach; ?>
    <span class="text-border mx-2">|</span>
    <span class="text-text-dim mr-1">Statut :</span>
    <?php foreach (['tous' => 'Tous', 'actif' => 'Actifs', 'supprime' => 'Supprimés'] as $key => $label): ?>
        <a href="<?= e($buildUrl(['statut' => $key === 'tous' ? null : $key])) ?>"
           class="px-2.5 py-1 rounded-full border <?= $statut === $key || ($statut === 'tous' && $key === 'tous') ? 'bg-accent text-black border-accent font-semibold' : 'bg-surface border-border text-text-muted hover:border-accent hover:text-accent' ?> transition-colors">
            <?= e($label) ?>
        </a>
    <?php endforeach; ?>
</div>

<!-- Tableau -->
<div class="overflow-x-auto bg-surface border border-border rounded-xl">
<table class="w-full text-sm">
    <thead>
        <tr class="border-b border-border text-text-dim text-xs uppercase tracking-wider text-left">
            <th class="py-3 px-3">Utilisateur</th>
            <th class="py-3 px-2 hidden md:table-cell">Email</th>
            <th class="py-3 px-2">Rôle</th>
            <th class="py-3 px-2">Statut</th>
            <th class="py-3 px-2 hidden lg:table-cell">Inscrit le</th>
            <th class="py-3 px-2 text-right">Achats</th>
            <th class="py-3 px-2 hidden md:table-cell">Abonnement</th>
            <th class="py-3 px-2 text-right hidden lg:table-cell">Dépensé</th>
            <th class="py-3 px-2 text-right">Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($users as $u): ?>
            <?php $isDeleted = ($u->statut ?? null) === 'supprime'; ?>
            <tr class="border-b border-border/30 hover:bg-surface-2/50 <?= $isDeleted ? 'opacity-60' : '' ?>">
                <td class="py-3 px-3">
                    <div class="flex items-center gap-2.5">
                        <?php if (!empty($u->avatar_url)): ?>
                            <img src="<?= e($u->avatar_url) ?>" alt="" class="w-8 h-8 rounded-full object-cover flex-shrink-0">
                        <?php else: ?>
                            <div class="w-8 h-8 rounded-full bg-accent text-black flex items-center justify-center text-xs font-bold flex-shrink-0"><?= e(mb_strtoupper(mb_substr($u->prenom, 0, 1))) ?></div>
                        <?php endif; ?>
                        <span class="text-white font-medium truncate"><?= e($u->prenom . ' ' . $u->nom) ?></span>
                    </div>
                </td>
                <td class="py-3 px-2 text-text-muted text-xs hidden md:table-cell"><?= e($u->email) ?></td>
                <td class="py-3 px-2">
                    <span class="text-[10px] font-semibold px-2 py-0.5 rounded <?= $roleBadge[$u->role] ?? 'bg-surface-2 text-text-dim' ?>"><?= e(ucfirst($u->role)) ?></span>
                </td>
                <td class="py-3 px-2">
                    <?php if ($isDeleted): ?>
                        <span class="text-[10px] font-semibold px-2 py-0.5 rounded bg-rose-500/20 text-rose-400">Supprimé</span>
                    <?php else: ?>
                        <span class="text-[10px] font-semibold px-2 py-0.5 rounded bg-emerald-500/20 text-emerald-400">Actif</span>
                    <?php endif; ?>
                </td>
                <td class="py-3 px-2 text-text-dim text-xs hidden lg:table-cell"><?= date('d/m/Y', strtotime($u->created_at)) ?></td>
                <td class="py-3 px-2 text-text-muted text-right"><?= (int) $u->nb_achats ?></td>
                <td class="py-3 px-2 hidden md:table-cell">
                    <?php if (!empty($u->date_fin_abo)): ?>
                        <?php $expired = strtotime($u->date_fin_abo) < time(); ?>
                        <span class="text-xs <?= $expired ? 'text-text-dim' : 'text-emerald-400' ?>">
                            <?= $expired ? 'Expiré' : 'jusqu\'au' ?> <?= date('d/m/Y', strtotime($u->date_fin_abo)) ?>
                        </span>
                    <?php else: ?>
                        <span class="text-text-dim text-xs">Aucun</span>
                    <?php endif; ?>
                </td>
                <td class="py-3 px-2 text-accent text-right text-xs font-medium hidden lg:table-cell">
                    <?= number_format((float) ($u->total_depense ?? 0), 2) ?>&nbsp;$
                </td>
                <td class="py-3 px-2 text-right">
                    <a href="/admin/lecteurs/<?= (int) $u->id ?>" class="text-accent hover:text-accent-hover text-xs font-medium">Voir →</a>
                </td>
            </tr>
        <?php endforeach; ?>
        <?php if (empty($users)): ?>
            <tr><td colspan="9" class="py-10 text-center text-text-dim">Aucun utilisateur ne correspond aux filtres.</td></tr>
        <?php endif; ?>
    </tbody>
</table>
</div>

<!-- Pagination -->
<?php if ($totalPages > 1): ?>
<nav class="flex items-center justify-center gap-2 mt-8 text-sm">
    <?php for ($p = 1; $p <= $totalPages; $p++): ?>
        <a href="<?= e($buildUrl(['page' => $p === 1 ? null : $p])) ?>"
           class="px-3 py-1.5 rounded <?= $p === $page ? 'bg-accent text-black font-semibold' : 'bg-surface border border-border text-text-muted hover:border-accent hover:text-accent' ?> transition-colors">
            <?= $p ?>
        </a>
    <?php endfor; ?>
</nav>
<?php endif; ?>
