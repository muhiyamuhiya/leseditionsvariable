<?php
/** @var string $tab            'achats' | 'abonnement' | 'historique' */
/** @var array  $achats          sales avec book_titre, slug, author_name */
/** @var ?object $subActuel      Abonnement courant */
/** @var array  $abonnements     Tous les abonnements (historique) */
/** @var array  $historique      Paiements unifiés (achats + abos) chronologique */
/** @var float  $totalAchats     Cumul USD achats unitaires */
/** @var float  $totalAbos       Cumul USD abonnements payés */
/** @var array  $progressions    Map [book_id => row reading_progress] */

$tabs = [
    'achats'      => ['label' => 'Achats uniques', 'count' => count($achats)],
    'abonnement'  => ['label' => 'Abonnement',     'count' => $subActuel ? 1 : 0],
    'historique'  => ['label' => 'Historique',     'count' => count($historique)],
];

$subTypeLabels = [
    'essentiel_mensuel' => 'Essentiel Mensuel',
    'essentiel_annuel'  => 'Essentiel Annuel',
    'premium_mensuel'   => 'Premium Mensuel',
    'premium_annuel'    => 'Premium Annuel',
];
?>

<section class="py-8 sm:py-12">
    <div class="max-w-[1000px] mx-auto px-4 sm:px-6">

        <!-- Header -->
        <div class="mb-6">
            <h1 class="font-display font-bold text-2xl sm:text-3xl text-white">Mes achats</h1>
            <p class="text-text-dim text-sm mt-1">Tous tes paiements à un seul endroit : achats uniques, abonnement, historique.</p>
        </div>

        <!-- Stats -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 mb-6">
            <div class="bg-surface border border-border rounded-lg p-4">
                <p class="text-text-dim text-[10px] uppercase tracking-wider">Achats unitaires</p>
                <p class="text-amber-400 text-2xl font-display font-bold mt-1"><?= number_format($totalAchats, 2) ?> $</p>
                <p class="text-text-dim text-[11px] mt-1"><?= count($achats) ?> livre<?= count($achats) > 1 ? 's' : '' ?> acheté<?= count($achats) > 1 ? 's' : '' ?></p>
            </div>
            <div class="bg-surface border border-border rounded-lg p-4">
                <p class="text-text-dim text-[10px] uppercase tracking-wider">Abonnement actuel</p>
                <?php if ($subActuel): ?>
                    <p class="text-emerald-400 text-base font-semibold mt-1"><?= e($subTypeLabels[$subActuel->type] ?? $subActuel->type) ?></p>
                    <p class="text-text-dim text-[11px] mt-1">Jusqu'au <?= date('d/m/Y', strtotime((string) $subActuel->date_fin)) ?></p>
                <?php else: ?>
                    <p class="text-text-muted text-base mt-1">Aucun</p>
                    <a href="/abonnement" class="text-accent text-[11px] hover:underline mt-1 inline-block">Voir les formules →</a>
                <?php endif; ?>
            </div>
            <div class="bg-surface border border-border rounded-lg p-4">
                <p class="text-text-dim text-[10px] uppercase tracking-wider">Total dépensé (lifetime)</p>
                <p class="text-white text-2xl font-display font-bold mt-1"><?= number_format($totalAchats + $totalAbos, 2) ?> $</p>
            </div>
        </div>

        <!-- Onglets -->
        <div class="border-b border-border mb-6">
            <nav class="flex gap-1">
                <?php foreach ($tabs as $key => $info): ?>
                    <a href="/mon-compte/achats?tab=<?= e($key) ?>"
                       class="px-4 py-2.5 text-sm font-medium border-b-2 -mb-[1px] transition-colors
                              <?= $tab === $key ? 'border-accent text-accent' : 'border-transparent text-text-muted hover:text-white' ?>">
                        <?= e($info['label']) ?>
                        <?php if ($info['count'] > 0): ?>
                            <span class="text-[11px] text-text-dim ml-1">(<?= $info['count'] ?>)</span>
                        <?php endif; ?>
                    </a>
                <?php endforeach; ?>
            </nav>
        </div>

        <!-- Onglet : Achats uniques -->
        <?php if ($tab === 'achats'): ?>
            <?php if (empty($achats)): ?>
                <div class="bg-surface border border-border rounded-lg p-10 text-center">
                    <p class="text-text-muted">Aucun achat unitaire pour l'instant.</p>
                    <a href="/catalogue" class="text-accent text-sm hover:underline mt-2 inline-block">Découvrir le catalogue →</a>
                </div>
            <?php else: ?>
                <div class="bg-surface border border-border rounded-lg overflow-hidden">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="bg-surface-2 border-b border-border text-text-dim text-[11px] uppercase tracking-wider text-left">
                            <th class="px-4 py-2.5">Livre</th>
                            <th class="px-4 py-2.5 hidden sm:table-cell">Date</th>
                            <th class="px-4 py-2.5">Prix</th>
                            <th class="px-4 py-2.5">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($achats as $a):
                            $prog = $progressions[(int) $a->book_id] ?? null;
                            $hasStarted = $prog && (int) $prog->derniere_page_lue > 1;
                        ?>
                        <tr class="border-b border-border/30 last:border-0 hover:bg-surface-2/50">
                            <td class="px-4 py-3">
                                <p class="text-white font-medium text-sm"><?= e($a->book_titre) ?></p>
                                <p class="text-text-dim text-xs mt-0.5">par <?= e($a->author_name) ?></p>
                            </td>
                            <td class="px-4 py-3 text-text-muted text-xs hidden sm:table-cell whitespace-nowrap"><?= date('d/m/Y', strtotime((string) ($a->date_paiement_confirme ?? $a->date_vente))) ?></td>
                            <td class="px-4 py-3 text-amber-400 font-medium whitespace-nowrap"><?= number_format((float) $a->prix_paye_usd, 2) ?> $</td>
                            <td class="px-4 py-3">
                                <a href="/lire/<?= e($a->book_slug) ?>" class="text-accent hover:text-accent-hover text-xs font-medium">
                                    <?= $hasStarted ? 'Continuer (p.' . (int) $prog->derniere_page_lue . ')' : 'Lire' ?>
                                </a>
                                <a href="/livre/<?= e($a->book_slug) ?>" class="text-text-muted hover:text-accent text-xs ml-2">Fiche</a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                </div>
                <p class="text-text-dim text-xs mt-3">Le reçu PDF de chaque achat t'a été envoyé par email au moment du paiement.</p>
            <?php endif; ?>

        <!-- Onglet : Abonnement -->
        <?php elseif ($tab === 'abonnement'): ?>
            <?php if (!$subActuel): ?>
                <div class="bg-surface border border-border rounded-lg p-10 text-center">
                    <p class="text-text-muted mb-3">Tu n'as pas d'abonnement en cours.</p>
                    <a href="/abonnement" class="btn-primary text-sm inline-block">Voir les formules</a>
                </div>
            <?php else: ?>
                <div class="bg-surface border border-border rounded-lg p-5 mb-4">
                    <div class="flex items-start justify-between gap-3 flex-wrap">
                        <div>
                            <p class="text-text-dim text-[10px] uppercase tracking-wider">Abonnement actuel</p>
                            <p class="text-white text-xl font-display font-bold mt-1"><?= e($subTypeLabels[$subActuel->type] ?? $subActuel->type) ?></p>
                            <p class="text-text-muted text-sm mt-2">
                                Du <?= date('d/m/Y', strtotime((string) $subActuel->date_debut)) ?>
                                au <?= date('d/m/Y', strtotime((string) $subActuel->date_fin)) ?>
                            </p>
                            <p class="text-amber-400 text-base font-semibold mt-3"><?= number_format((float) $subActuel->prix_paye, 2) ?> <?= e(strtoupper((string) $subActuel->devise)) ?></p>
                        </div>
                        <span class="text-[10px] font-bold uppercase tracking-wider px-2 py-1 rounded
                            <?= $subActuel->statut === 'actif' ? 'bg-emerald-500/20 text-emerald-400' : 'bg-amber-500/20 text-amber-400' ?>">
                            <?= e(ucfirst((string) $subActuel->statut)) ?>
                        </span>
                    </div>
                    <div class="flex gap-2 mt-4 flex-wrap">
                        <a href="/mon-compte/abonnement" class="btn-secondary text-sm">Gérer mon abonnement</a>
                        <?php if ($subActuel->statut === 'actif'): ?>
                            <a href="/mon-compte/abonnement/annuler" class="text-red-400 hover:text-red-300 text-xs px-3 py-2 transition-colors">Annuler</a>
                        <?php endif; ?>
                    </div>
                </div>

                <?php if (count($abonnements) > 1): ?>
                <div class="bg-surface border border-border rounded-lg overflow-hidden">
                    <div class="px-5 py-3 border-b border-border">
                        <p class="text-text-dim text-[11px] uppercase tracking-wider">Historique abonnements</p>
                    </div>
                    <table class="w-full text-sm">
                        <tbody>
                            <?php foreach ($abonnements as $sub): if ($sub->id === $subActuel->id) continue; ?>
                            <tr class="border-b border-border/30 last:border-0">
                                <td class="px-4 py-2 text-white text-xs"><?= e($subTypeLabels[$sub->type] ?? $sub->type) ?></td>
                                <td class="px-4 py-2 text-text-muted text-xs hidden sm:table-cell"><?= date('d/m/Y', strtotime((string) $sub->date_debut)) ?> → <?= date('d/m/Y', strtotime((string) $sub->date_fin)) ?></td>
                                <td class="px-4 py-2 text-amber-400 text-xs whitespace-nowrap"><?= number_format((float) $sub->prix_paye, 2) ?> $</td>
                                <td class="px-4 py-2 text-text-dim text-[11px]"><?= e(ucfirst((string) $sub->statut)) ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php endif; ?>
            <?php endif; ?>

        <!-- Onglet : Historique -->
        <?php elseif ($tab === 'historique'): ?>
            <?php if (empty($historique)): ?>
                <div class="bg-surface border border-border rounded-lg p-10 text-center">
                    <p class="text-text-muted">Aucun paiement pour l'instant.</p>
                </div>
            <?php else: ?>
                <div class="bg-surface border border-border rounded-lg overflow-hidden">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="bg-surface-2 border-b border-border text-text-dim text-[11px] uppercase tracking-wider text-left">
                            <th class="px-4 py-2.5">Date</th>
                            <th class="px-4 py-2.5">Type</th>
                            <th class="px-4 py-2.5">Détail</th>
                            <th class="px-4 py-2.5">Montant</th>
                            <th class="px-4 py-2.5 hidden md:table-cell">Méthode</th>
                            <th class="px-4 py-2.5 hidden lg:table-cell">Référence</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($historique as $h): ?>
                        <tr class="border-b border-border/30 last:border-0">
                            <td class="px-4 py-2 text-text-muted text-xs whitespace-nowrap"><?= date('d/m/Y', strtotime((string) $h->dt)) ?></td>
                            <td class="px-4 py-2">
                                <?php if ($h->kind === 'achat'): ?>
                                    <span class="text-[10px] font-medium px-2 py-0.5 rounded bg-amber-500/20 text-amber-400">Achat</span>
                                <?php else: ?>
                                    <span class="text-[10px] font-medium px-2 py-0.5 rounded bg-blue-500/20 text-blue-400">Abonnement</span>
                                <?php endif; ?>
                            </td>
                            <td class="px-4 py-2 text-white text-xs">
                                <?php if ($h->kind === 'achat'): ?>
                                    <a href="/livre/<?= e($h->slug) ?>" class="hover:text-accent"><?= e($h->label) ?></a>
                                <?php else: ?>
                                    <?= e($subTypeLabels[$h->label] ?? $h->label) ?>
                                <?php endif; ?>
                            </td>
                            <td class="px-4 py-2 text-amber-400 font-medium whitespace-nowrap"><?= number_format((float) $h->montant, 2) ?> <?= e(strtoupper((string) $h->devise)) ?></td>
                            <td class="px-4 py-2 text-text-muted text-xs hidden md:table-cell"><?= e($h->methode ?? '—') ?></td>
                            <td class="px-4 py-2 text-text-dim text-[11px] hidden lg:table-cell font-mono break-all max-w-[200px] truncate" title="<?= e($h->transaction_id ?? '') ?>"><?= e($h->transaction_id ?: '—') ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                </div>
            <?php endif; ?>
        <?php endif; ?>

    </div>
</section>
