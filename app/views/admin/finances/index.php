<?php
/** @var array $stats     ca_lifetime, commission, revenus_auteurs_lifetime, deja_verse, en_attente, pool_dispo */
/** @var array $demandes  Demandes en attente (requested/en_cours) */
/** @var array $historique Versements traités (verse/refuse) */

$statusBadge = function (string $st): string {
    $map = [
        'requested' => 'bg-amber-500/20 text-amber-400',
        'en_cours'  => 'bg-amber-500/20 text-amber-400',
        'verse'     => 'bg-emerald-500/20 text-emerald-400',
        'refuse'    => 'bg-red-500/20 text-red-400',
        'echec'     => 'bg-red-500/20 text-red-400',
        'annule'    => 'bg-text-dim/20 text-text-dim',
    ];
    return $map[$st] ?? 'bg-text-dim/20 text-text-dim';
};
?>
<?php $s = flash('admin_success'); if ($s): ?><div class="bg-emerald-500/10 border border-emerald-500/30 text-emerald-400 px-4 py-3 rounded-lg mb-6 text-sm"><?= e($s) ?></div><?php endif; ?>
<?php $err = flash('error'); if ($err): ?><div class="bg-red-500/10 border border-red-500/30 text-red-400 px-4 py-3 rounded-lg mb-6 text-sm"><?= e($err) ?></div><?php endif; ?>

<!-- Stats globales -->
<div class="grid grid-cols-2 lg:grid-cols-3 gap-3 mb-6">
    <div class="bg-surface border border-border rounded-lg p-4">
        <p class="text-text-dim text-[10px] uppercase tracking-wider">CA lifetime</p>
        <p class="text-white text-xl font-display font-bold mt-1"><?= number_format($stats['ca_lifetime'], 2) ?> $</p>
    </div>
    <div class="bg-surface border border-border rounded-lg p-4">
        <p class="text-text-dim text-[10px] uppercase tracking-wider">Commission Variable</p>
        <p class="text-emerald-400 text-xl font-display font-bold mt-1"><?= number_format($stats['commission'], 2) ?> $</p>
    </div>
    <div class="bg-surface border border-border rounded-lg p-4">
        <p class="text-text-dim text-[10px] uppercase tracking-wider">Revenus auteurs (cumul)</p>
        <p class="text-white text-xl font-display font-bold mt-1"><?= number_format($stats['revenus_auteurs_lifetime'], 2) ?> $</p>
    </div>
    <div class="bg-surface border border-border rounded-lg p-4">
        <p class="text-text-dim text-[10px] uppercase tracking-wider">Déjà versé</p>
        <p class="text-emerald-400 text-xl font-display font-bold mt-1"><?= number_format($stats['deja_verse'], 2) ?> $</p>
    </div>
    <div class="bg-surface border border-border rounded-lg p-4 border-amber-500/30">
        <p class="text-text-dim text-[10px] uppercase tracking-wider">En attente de versement</p>
        <p class="text-amber-400 text-xl font-display font-bold mt-1"><?= number_format($stats['en_attente'], 2) ?> $</p>
    </div>
    <div class="bg-surface border border-border rounded-lg p-4">
        <p class="text-text-dim text-[10px] uppercase tracking-wider">Pool dispo (non demandé)</p>
        <p class="text-white text-xl font-display font-bold mt-1"><?= number_format($stats['pool_dispo'], 2) ?> $</p>
    </div>
</div>

<!-- Demandes en attente -->
<div class="bg-surface border border-border rounded-lg overflow-hidden mb-6">
    <div class="px-5 py-4 border-b border-border flex items-center justify-between">
        <h2 class="text-white font-display font-semibold">Demandes à traiter (<?= count($demandes) ?>)</h2>
        <a href="/admin/versements" class="text-text-dim text-xs hover:text-accent">Vue legacy →</a>
    </div>

    <?php if (empty($demandes)): ?>
        <p class="text-text-muted text-sm p-5">Aucune demande en attente.</p>
    <?php else: ?>
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-surface-2 border-b border-border text-text-dim text-[11px] uppercase tracking-wider text-left">
                    <th class="px-4 py-2.5">Auteur</th>
                    <th class="px-4 py-2.5">Demandé le</th>
                    <th class="px-4 py-2.5">Montant</th>
                    <th class="px-4 py-2.5">Méthode + Compte</th>
                    <th class="px-4 py-2.5">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($demandes as $d): ?>
                <tr class="border-b border-border/30 last:border-0">
                    <td class="px-4 py-3">
                        <p class="text-white text-sm font-medium"><?= e($d->author_name) ?></p>
                        <?php if (!empty($d->user_email)): ?><p class="text-text-dim text-[11px]"><?= e($d->user_email) ?></p><?php endif; ?>
                    </td>
                    <td class="px-4 py-3 text-text-muted text-xs"><?= $d->requested_at ? date('d/m/Y H:i', strtotime((string) $d->requested_at)) : date('d/m/Y', strtotime((string) $d->created_at)) ?></td>
                    <td class="px-4 py-3 text-amber-400 font-medium whitespace-nowrap"><?= number_format((float) $d->total_a_verser, 2) ?> $</td>
                    <td class="px-4 py-3 text-text-muted text-xs">
                        <p><strong class="text-white"><?= e(str_replace('_', ' ', (string) ($d->requested_method ?? '—'))) ?></strong></p>
                        <p class="font-mono text-[11px] mt-0.5 break-all"><?= e($d->requested_account_snapshot ?? '—') ?></p>
                    </td>
                    <td class="px-4 py-3">
                        <div class="flex flex-col gap-1">
                            <form method="POST" action="/admin/finances/<?= (int) $d->id ?>/traiter" onsubmit="this.querySelector('[name=reference]').value=prompt('Référence du paiement (transaction MM, n° virement, etc.) :') || ''; return this.querySelector('[name=reference]').value !== '';">
                                <?= csrf_field() ?><input type="hidden" name="reference" value="">
                                <button class="text-emerald-400 hover:text-emerald-300 text-xs font-medium">✓ Marquer payé</button>
                            </form>
                            <form method="POST" action="/admin/finances/<?= (int) $d->id ?>/refuser" onsubmit="this.querySelector('[name=reason]').value=prompt('Raison du refus (sera envoyée à l\'auteur) :') || ''; return this.querySelector('[name=reason]').value !== '';">
                                <?= csrf_field() ?><input type="hidden" name="reason" value="">
                                <button class="text-red-400 hover:text-red-300 text-xs">✗ Refuser</button>
                            </form>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>

<!-- Historique -->
<div class="bg-surface border border-border rounded-lg overflow-hidden">
    <div class="px-5 py-4 border-b border-border">
        <h2 class="text-white font-display font-semibold">Historique (50 derniers)</h2>
    </div>
    <?php if (empty($historique)): ?>
        <p class="text-text-muted text-sm p-5">Aucun versement traité pour l'instant.</p>
    <?php else: ?>
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-surface-2 border-b border-border text-text-dim text-[11px] uppercase tracking-wider text-left">
                    <th class="px-4 py-2.5">Auteur</th>
                    <th class="px-4 py-2.5">Montant</th>
                    <th class="px-4 py-2.5 hidden sm:table-cell">Méthode</th>
                    <th class="px-4 py-2.5">Statut</th>
                    <th class="px-4 py-2.5 hidden md:table-cell">Date</th>
                    <th class="px-4 py-2.5 hidden lg:table-cell">Référence / Motif</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($historique as $h): ?>
                <tr class="border-b border-border/30 last:border-0">
                    <td class="px-4 py-2 text-white text-xs"><?= e($h->author_name) ?></td>
                    <td class="px-4 py-2 text-amber-400 font-medium whitespace-nowrap"><?= number_format((float) $h->total_a_verser, 2) ?> $</td>
                    <td class="px-4 py-2 text-text-muted hidden sm:table-cell text-xs"><?= e(str_replace('_', ' ', (string) ($h->requested_method ?? $h->methode_versement ?? '—'))) ?></td>
                    <td class="px-4 py-2"><span class="text-[11px] font-medium px-2 py-1 rounded <?= $statusBadge($h->statut) ?>"><?= ucfirst(str_replace('_',' ', (string) $h->statut)) ?></span></td>
                    <td class="px-4 py-2 text-text-dim hidden md:table-cell text-xs"><?= $h->date_versement ? date('d/m/Y', strtotime((string) $h->date_versement)) : ($h->updated_at ? date('d/m/Y', strtotime((string) $h->updated_at)) : '—') ?></td>
                    <td class="px-4 py-2 text-text-dim hidden lg:table-cell text-[11px] font-mono">
                        <?= e($h->reference_versement ?: $h->rejection_reason ?: '—') ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>
