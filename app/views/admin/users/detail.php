<?php
$success = flash('admin_success');
$error = flash('admin_error');

$isDeleted = ($user->statut ?? null) === 'supprime';
$isAdmin = $user->role === 'admin';

$roleBadge = [
    'admin'   => 'bg-red-500/20 text-red-400',
    'auteur'  => 'bg-purple-500/20 text-purple-300',
    'lecteur' => 'bg-blue-500/20 text-blue-300',
];

// Stats agrégées
$nbAchats = count($achats);
$nbAvis = count($avis);
$totalDepense = 0;
foreach ($transactions as $t) {
    if ($t->statut === 'reussi') $totalDepense += (float) $t->montant;
}

// Helper étoiles
$stars = function (int $note): string {
    $full = str_repeat('★', max(0, min(5, $note)));
    $empty = str_repeat('☆', 5 - max(0, min(5, $note)));
    return '<span class="text-amber-400">' . $full . '</span><span class="text-border">' . $empty . '</span>';
};
?>
<?php if ($success): ?>
    <div class="bg-emerald-500/10 border border-emerald-500/30 text-emerald-400 px-4 py-3 rounded-lg mb-6 text-sm"><?= e($success) ?></div>
<?php endif; ?>
<?php if ($error): ?>
    <div class="bg-red-500/10 border border-red-500/30 text-red-400 px-4 py-3 rounded-lg mb-6 text-sm"><?= e($error) ?></div>
<?php endif; ?>

<div class="mb-5">
    <a href="/admin/lecteurs" class="text-text-dim hover:text-accent text-xs">← Retour à la liste</a>
</div>

<!-- En-tête utilisateur -->
<div class="bg-surface border border-border rounded-xl p-5 sm:p-6 mb-6 flex items-start justify-between flex-wrap gap-4">
    <div class="flex items-center gap-4">
        <?php if (!empty($user->avatar_url)): ?>
            <img src="<?= e($user->avatar_url) ?>" alt="" class="w-16 h-16 rounded-full object-cover border-2 border-accent">
        <?php else: ?>
            <div class="w-16 h-16 rounded-full bg-accent text-black flex items-center justify-center text-2xl font-bold font-display"><?= e(mb_strtoupper(mb_substr($user->prenom, 0, 1))) ?></div>
        <?php endif; ?>
        <div>
            <h1 class="font-display font-bold text-2xl text-white"><?= e($user->prenom . ' ' . $user->nom) ?></h1>
            <p class="text-text-muted text-sm"><?= e($user->email) ?></p>
            <div class="flex items-center gap-2 mt-2">
                <span class="text-[10px] font-semibold px-2 py-0.5 rounded <?= $roleBadge[$user->role] ?? 'bg-surface-2 text-text-dim' ?>"><?= e(ucfirst($user->role)) ?></span>
                <?php if ($isDeleted): ?>
                    <span class="text-[10px] font-semibold px-2 py-0.5 rounded bg-rose-500/20 text-rose-400">Supprimé le <?= !empty($user->deleted_at) ? date('d/m/Y', strtotime($user->deleted_at)) : '?' ?></span>
                <?php else: ?>
                    <span class="text-[10px] font-semibold px-2 py-0.5 rounded bg-emerald-500/20 text-emerald-400">Actif</span>
                <?php endif; ?>
                <span class="text-text-dim text-xs">Inscrit le <?= date('d/m/Y', strtotime($user->created_at)) ?></span>
            </div>
        </div>
    </div>

    <?php if (!$isAdmin && !$isDeleted): ?>
        <div x-data="{ open: false }">
            <button @click="open = true" class="bg-rose-600 hover:bg-rose-700 text-white text-sm font-semibold px-4 py-2 rounded transition-colors">
                Supprimer ce compte
            </button>

            <!-- Modal de confirmation -->
            <div x-show="open" x-cloak
                 class="fixed inset-0 z-50 bg-black/70 flex items-center justify-center p-4"
                 @keydown.escape.window="open = false">
                <div class="bg-surface border border-border rounded-xl p-6 max-w-md w-full" @click.outside="open = false">
                    <h3 class="font-display font-bold text-xl text-white mb-2">Supprimer ce compte ?</h3>
                    <p class="text-text-muted text-sm mb-6">
                        Cette action est <strong class="text-white">définitive</strong>. L'utilisateur ne pourra plus se connecter.
                        Ses données personnelles seront anonymisées. Les statistiques (ventes, avis) seront conservées.
                    </p>
                    <form action="/admin/lecteurs/<?= (int) $user->id ?>/supprimer" method="POST">
                        <?= csrf_field() ?>
                        <div class="flex gap-3 justify-end">
                            <button type="button" @click="open = false" class="btn-secondary text-sm">Annuler</button>
                            <button type="submit" class="bg-rose-600 hover:bg-rose-700 text-white text-sm font-semibold px-4 py-2 rounded transition-colors">Supprimer définitivement</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<!-- Statistiques -->
<div class="grid grid-cols-2 md:grid-cols-4 gap-3 mb-8">
    <div class="bg-surface border border-border rounded-lg p-4 text-center">
        <p class="font-display font-bold text-2xl text-accent"><?= $nbAchats ?></p>
        <p class="text-text-dim text-xs mt-1">Achats unitaires</p>
    </div>
    <div class="bg-surface border border-border rounded-lg p-4 text-center">
        <p class="font-display font-bold text-2xl text-accent"><?= number_format($totalDepense, 2) ?>&nbsp;$</p>
        <p class="text-text-dim text-xs mt-1">Total dépensé</p>
    </div>
    <div class="bg-surface border border-border rounded-lg p-4 text-center">
        <p class="font-display font-bold text-2xl text-accent"><?= count($abonnements) ?></p>
        <p class="text-text-dim text-xs mt-1">Abonnements</p>
    </div>
    <div class="bg-surface border border-border rounded-lg p-4 text-center">
        <p class="font-display font-bold text-2xl text-accent"><?= $nbAvis ?></p>
        <p class="text-text-dim text-xs mt-1">Avis postés</p>
    </div>
</div>

<!-- Abonnements -->
<section class="mb-8">
    <h2 class="font-display font-semibold text-lg text-white mb-3">Abonnements</h2>
    <?php if (empty($abonnements)): ?>
        <p class="text-text-dim text-sm">Aucun abonnement.</p>
    <?php else: ?>
        <div class="bg-surface border border-border rounded-xl overflow-x-auto">
            <table class="w-full text-sm">
                <thead><tr class="border-b border-border text-text-dim text-xs uppercase tracking-wider text-left">
                    <th class="py-3 px-3">Type</th><th class="py-3 px-2">Prix</th><th class="py-3 px-2">Début</th><th class="py-3 px-2">Fin</th><th class="py-3 px-2">Statut</th>
                </tr></thead>
                <tbody>
                <?php foreach ($abonnements as $sub): ?>
                    <tr class="border-b border-border/30">
                        <td class="py-3 px-3 text-white"><?= e(\App\Models\Subscription::PLANS[$sub->type]['label'] ?? $sub->type) ?></td>
                        <td class="py-3 px-2 text-accent"><?= number_format($sub->prix_paye, 2) ?>&nbsp;<?= e($sub->devise) ?></td>
                        <td class="py-3 px-2 text-text-muted text-xs"><?= date('d/m/Y', strtotime($sub->date_debut)) ?></td>
                        <td class="py-3 px-2 text-text-muted text-xs"><?= date('d/m/Y', strtotime($sub->date_fin)) ?></td>
                        <td class="py-3 px-2"><span class="text-[10px] font-semibold px-2 py-0.5 rounded bg-surface-2 text-text-muted"><?= e($sub->statut) ?></span></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</section>

<!-- Achats unitaires -->
<section class="mb-8">
    <h2 class="font-display font-semibold text-lg text-white mb-3">Achats unitaires</h2>
    <?php if (empty($achats)): ?>
        <p class="text-text-dim text-sm">Aucun achat.</p>
    <?php else: ?>
        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-3">
            <?php foreach ($achats as $a): ?>
                <a href="/livre/<?= e($a->slug) ?>" target="_blank" class="block group">
                    <div class="aspect-[2/3] bg-gradient-to-br <?= book_cover_gradient((int) $a->book_id) ?> rounded overflow-hidden">
                        <?php if (!empty($a->couverture_url_web)): ?>
                            <img src="<?= e($a->couverture_url_web) ?>" alt="" class="w-full h-full object-cover">
                        <?php endif; ?>
                    </div>
                    <p class="text-white text-xs font-medium mt-2 line-clamp-2 group-hover:text-accent transition-colors"><?= e($a->titre) ?></p>
                    <p class="text-text-dim text-[10px] mt-0.5"><?= number_format((float) $a->prix_unitaire_usd, 2) ?>&nbsp;$ · <?= date('d/m/Y', strtotime($a->date_ajout)) ?></p>
                </a>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</section>

<!-- Transactions -->
<section class="mb-8">
    <h2 class="font-display font-semibold text-lg text-white mb-3">Transactions récentes</h2>
    <?php if (empty($transactions)): ?>
        <p class="text-text-dim text-sm">Aucune transaction.</p>
    <?php else: ?>
        <div class="bg-surface border border-border rounded-xl overflow-x-auto">
            <table class="w-full text-sm">
                <thead><tr class="border-b border-border text-text-dim text-xs uppercase tracking-wider text-left">
                    <th class="py-3 px-3">Date</th><th class="py-3 px-2">Type</th><th class="py-3 px-2">Provider</th><th class="py-3 px-2 text-right">Montant</th><th class="py-3 px-2">Statut</th>
                </tr></thead>
                <tbody>
                <?php foreach ($transactions as $t): ?>
                    <?php $statusColor = $t->statut === 'reussi' ? 'text-emerald-400' : ($t->statut === 'echoue' ? 'text-rose-400' : 'text-text-dim'); ?>
                    <tr class="border-b border-border/30">
                        <td class="py-2.5 px-3 text-text-muted text-xs"><?= date('d/m/Y H:i', strtotime($t->created_at)) ?></td>
                        <td class="py-2.5 px-2 text-white"><?= e($t->type) ?></td>
                        <td class="py-2.5 px-2 text-text-muted text-xs"><?= e($t->provider) ?></td>
                        <td class="py-2.5 px-2 text-accent text-right"><?= number_format((float) $t->montant, 2) ?>&nbsp;<?= e($t->devise) ?></td>
                        <td class="py-2.5 px-2 <?= $statusColor ?> text-xs"><?= e($t->statut) ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</section>

<!-- Avis -->
<section class="mb-8">
    <h2 class="font-display font-semibold text-lg text-white mb-3">Avis postés</h2>
    <?php if (empty($avis)): ?>
        <p class="text-text-dim text-sm">Aucun avis.</p>
    <?php else: ?>
        <div class="space-y-3">
            <?php foreach ($avis as $r): ?>
                <div class="bg-surface border border-border rounded-lg p-4">
                    <div class="flex items-start justify-between flex-wrap gap-2 mb-2">
                        <div>
                            <a href="/livre/<?= e($r->book_slug) ?>" target="_blank" class="text-white font-medium hover:text-accent transition-colors text-sm"><?= e($r->book_titre) ?></a>
                            <div class="flex items-center gap-2 mt-1">
                                <span class="text-base"><?= $stars((int) $r->note) ?></span>
                                <?php if ($r->approuve): ?>
                                    <span class="text-[10px] font-semibold px-2 py-0.5 rounded bg-emerald-500/20 text-emerald-400">Approuvé</span>
                                <?php else: ?>
                                    <span class="text-[10px] font-semibold px-2 py-0.5 rounded bg-amber-500/20 text-amber-400">En attente</span>
                                <?php endif; ?>
                                <span class="text-text-dim text-[11px]"><?= date('d/m/Y', strtotime($r->created_at)) ?></span>
                            </div>
                        </div>
                    </div>
                    <?php if (!empty($r->titre)): ?>
                        <p class="text-white text-sm font-semibold mt-1"><?= e($r->titre) ?></p>
                    <?php endif; ?>
                    <?php if (!empty($r->commentaire)): ?>
                        <p class="text-text-muted text-sm mt-1 line-clamp-3"><?= e($r->commentaire) ?></p>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</section>
