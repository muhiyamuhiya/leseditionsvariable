<?php $s = flash('admin_success'); if ($s): ?><div class="bg-emerald-500/10 border border-emerald-500/30 text-emerald-400 px-4 py-3 rounded-lg mb-6 text-sm"><?= e($s) ?></div><?php endif; ?>

<!-- ============================================================ -->
<!-- BANDEAU "À TRAITER"                                          -->
<!-- ============================================================ -->
<?php if ($alerts['total'] > 0 || $alerts['paiements_echoues'] > 0 || $alerts['abos_annules'] > 0): ?>
<div class="bg-gradient-to-r from-amber-500/15 to-rose-500/15 border border-amber-500/30 rounded-xl p-4 sm:p-5 mb-6">
    <h2 class="font-display font-bold text-lg sm:text-xl text-white mb-4 flex items-center gap-2">
        <span aria-hidden="true">🔔</span>
        À traiter
        <?php if ($alerts['total'] > 0): ?>
            <span class="bg-rose-500 text-white text-xs px-2 py-0.5 rounded-full"><?= $alerts['total'] ?></span>
        <?php endif; ?>
    </h2>

    <div class="grid grid-cols-2 md:grid-cols-3 gap-3">
        <?php if ($alerts['candidatures'] > 0): ?>
            <a href="/admin/candidatures" class="block bg-white/5 border border-white/10 rounded-lg p-3 hover:bg-white/10 transition-colors">
                <div class="flex items-center gap-2 mb-1">
                    <span class="text-xl" aria-hidden="true">📝</span>
                    <span class="text-2xl font-display font-bold text-amber-400"><?= $alerts['candidatures'] ?></span>
                </div>
                <p class="text-white text-sm">Candidature<?= $alerts['candidatures'] > 1 ? 's' : '' ?></p>
                <p class="text-text-dim text-xs">À examiner</p>
            </a>
        <?php endif; ?>

        <?php if (($alerts['brouillons'] ?? 0) > 0): ?>
            <a href="/admin/livres?statut=brouillon" class="block bg-amber-500/10 border border-amber-500/30 rounded-lg p-3 hover:bg-amber-500/15 transition-colors">
                <div class="flex items-center gap-2 mb-1">
                    <span class="text-xl" aria-hidden="true">📝</span>
                    <span class="text-2xl font-display font-bold text-amber-400"><?= $alerts['brouillons'] ?></span>
                </div>
                <p class="text-white text-sm">Brouillon<?= $alerts['brouillons'] > 1 ? 's' : '' ?> en attente</p>
                <p class="text-text-dim text-xs">À publier ou compléter</p>
            </a>
        <?php endif; ?>

        <?php if ($alerts['livres_revue'] > 0): ?>
            <a href="/admin/livres?statut=en_revue" class="block bg-white/5 border border-white/10 rounded-lg p-3 hover:bg-white/10 transition-colors">
                <div class="flex items-center gap-2 mb-1">
                    <span class="text-xl" aria-hidden="true">📖</span>
                    <span class="text-2xl font-display font-bold text-amber-400"><?= $alerts['livres_revue'] ?></span>
                </div>
                <p class="text-white text-sm">Livre<?= $alerts['livres_revue'] > 1 ? 's' : '' ?> en revue</p>
                <p class="text-text-dim text-xs">Soumis par auteurs</p>
            </a>
        <?php endif; ?>

        <?php if ($alerts['commandes_devis'] > 0): ?>
            <a href="/admin/services-editoriaux?statut=en_attente_devis" class="block bg-white/5 border border-white/10 rounded-lg p-3 hover:bg-white/10 transition-colors">
                <div class="flex items-center gap-2 mb-1">
                    <span class="text-xl" aria-hidden="true">💰</span>
                    <span class="text-2xl font-display font-bold text-amber-400"><?= $alerts['commandes_devis'] ?></span>
                </div>
                <p class="text-white text-sm">Devis à envoyer</p>
                <p class="text-text-dim text-xs">Commandes édito</p>
            </a>
        <?php endif; ?>

        <?php if ($alerts['commandes_livrer'] > 0): ?>
            <a href="/admin/services-editoriaux?statut=en_cours" class="block bg-white/5 border border-white/10 rounded-lg p-3 hover:bg-white/10 transition-colors">
                <div class="flex items-center gap-2 mb-1">
                    <span class="text-xl" aria-hidden="true">🚚</span>
                    <span class="text-2xl font-display font-bold text-blue-400"><?= $alerts['commandes_livrer'] ?></span>
                </div>
                <p class="text-white text-sm">Commande<?= $alerts['commandes_livrer'] > 1 ? 's' : '' ?> en cours</p>
                <p class="text-text-dim text-xs">À livrer</p>
            </a>
        <?php endif; ?>

        <?php if ($alerts['paiements_echoues'] > 0): ?>
            <a href="/admin/journal" class="block bg-white/5 border border-rose-500/30 rounded-lg p-3 hover:bg-white/10 transition-colors">
                <div class="flex items-center gap-2 mb-1">
                    <span class="text-xl" aria-hidden="true">⚠️</span>
                    <span class="text-2xl font-display font-bold text-rose-400"><?= $alerts['paiements_echoues'] ?></span>
                </div>
                <p class="text-white text-sm">Paiement<?= $alerts['paiements_echoues'] > 1 ? 's' : '' ?> échoué<?= $alerts['paiements_echoues'] > 1 ? 's' : '' ?></p>
                <p class="text-text-dim text-xs">7 derniers jours</p>
            </a>
        <?php endif; ?>

        <?php if ($alerts['abos_annules'] > 0): ?>
            <a href="/admin/abonnements" class="block bg-white/5 border border-white/10 rounded-lg p-3 hover:bg-white/10 transition-colors">
                <div class="flex items-center gap-2 mb-1">
                    <span class="text-xl" aria-hidden="true">💔</span>
                    <span class="text-2xl font-display font-bold text-text-muted"><?= $alerts['abos_annules'] ?></span>
                </div>
                <p class="text-white text-sm">Annulation<?= $alerts['abos_annules'] > 1 ? 's' : '' ?></p>
                <p class="text-text-dim text-xs">7 derniers jours</p>
            </a>
        <?php endif; ?>
    </div>
</div>
<?php else: ?>
<div class="bg-emerald-500/10 border border-emerald-500/30 rounded-xl p-5 mb-6 text-center">
    <p class="text-emerald-300 font-medium">✨ Tout est à jour ! Aucune action requise pour l'instant.</p>
</div>
<?php endif; ?>

<!-- ============================================================ -->
<!-- STATS GÉNÉRALES                                              -->
<!-- ============================================================ -->
<div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4 mb-8">
    <div class="bg-surface border border-border rounded-xl p-4 sm:p-5">
        <p class="text-text-dim text-xs uppercase tracking-wider">CA du mois</p>
        <p class="font-display font-bold text-xl sm:text-2xl text-accent mt-1"><?= number_format((float) $stats['ca_mois'], 2) ?>&nbsp;$</p>
    </div>
    <div class="bg-surface border border-border rounded-xl p-4 sm:p-5">
        <p class="text-text-dim text-xs uppercase tracking-wider">Lecteurs</p>
        <p class="font-display font-bold text-xl sm:text-2xl text-white mt-1"><?= (int) $stats['lecteurs'] ?></p>
    </div>
    <div class="bg-surface border border-border rounded-xl p-4 sm:p-5">
        <p class="text-text-dim text-xs uppercase tracking-wider">Auteurs validés</p>
        <p class="font-display font-bold text-xl sm:text-2xl text-white mt-1"><?= (int) $stats['auteurs'] ?></p>
    </div>
    <div class="bg-surface border border-border rounded-xl p-4 sm:p-5">
        <p class="text-text-dim text-xs uppercase tracking-wider">Abonnés actifs</p>
        <p class="font-display font-bold text-xl sm:text-2xl text-white mt-1"><?= (int) $stats['abonnes'] ?></p>
    </div>
</div>

<!-- ============================================================ -->
<!-- ÉLÉMENTS RÉCENTS À TRAITER                                   -->
<!-- ============================================================ -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-5 mb-6">
    <!-- Candidatures récentes -->
    <div class="bg-surface border border-border rounded-xl p-5">
        <div class="flex items-center justify-between mb-4">
            <h3 class="font-display font-semibold text-sm text-white">Candidatures récentes</h3>
            <?php if ($alerts['candidatures'] > 0): ?>
                <a href="/admin/candidatures" class="text-accent hover:text-accent-hover text-xs">Voir tout →</a>
            <?php endif; ?>
        </div>
        <?php if (empty($candidaturesRecentes)): ?>
            <p class="text-text-dim text-sm">Aucune candidature en attente.</p>
        <?php else: ?>
            <div class="space-y-2">
                <?php foreach ($candidaturesRecentes as $c): ?>
                    <a href="/admin/candidatures/<?= (int) $c->id ?>" class="flex items-center gap-3 p-2 rounded hover:bg-surface-2 transition-colors">
                        <?php if (!empty($c->avatar_url)): ?>
                            <img src="<?= e($c->avatar_url) ?>" alt="" class="w-8 h-8 rounded-full object-cover flex-shrink-0">
                        <?php else: ?>
                            <div class="w-8 h-8 rounded-full bg-accent text-black flex items-center justify-center text-xs font-bold flex-shrink-0"><?= e(mb_strtoupper(mb_substr($c->prenom, 0, 1))) ?></div>
                        <?php endif; ?>
                        <div class="flex-1 min-w-0">
                            <p class="text-white text-sm truncate"><?= e($c->prenom . ' ' . $c->nom) ?></p>
                            <p class="text-text-dim text-xs truncate"><?= e($c->email) ?></p>
                        </div>
                        <span class="text-text-dim text-xs flex-shrink-0"><?= date('d/m', strtotime($c->created_at)) ?></span>
                    </a>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <!-- Commandes éditoriales récentes -->
    <div class="bg-surface border border-border rounded-xl p-5">
        <div class="flex items-center justify-between mb-4">
            <h3 class="font-display font-semibold text-sm text-white">Commandes éditoriales</h3>
            <a href="/admin/services-editoriaux" class="text-accent hover:text-accent-hover text-xs">Voir tout →</a>
        </div>
        <?php if (empty($commandesRecentes)): ?>
            <p class="text-text-dim text-sm">Aucune commande à traiter.</p>
        <?php else: ?>
            <?php $serviceIcons = ['edit'=>'✏️','layout'=>'🧱','image'=>'🎨','message'=>'💬','package'=>'📦','plus'=>'➕']; ?>
            <div class="space-y-2">
                <?php foreach ($commandesRecentes as $c): ?>
                    <a href="/admin/services-editoriaux/<?= (int) $c->id ?>" class="flex items-center gap-3 p-2 rounded hover:bg-surface-2 transition-colors">
                        <span class="text-lg flex-shrink-0" aria-hidden="true"><?= $serviceIcons[$c->service_icon] ?? '📌' ?></span>
                        <div class="flex-1 min-w-0">
                            <p class="text-white text-sm truncate"><?= e($c->service_nom) ?></p>
                            <p class="text-text-dim text-xs truncate"><?= e($c->prenom . ' ' . $c->nom) ?> · <?= e($c->statut) ?></p>
                        </div>
                        <?php if (!empty($c->montant_propose)): ?>
                            <span class="text-accent text-xs font-semibold flex-shrink-0"><?= number_format((float) $c->montant_propose, 0) ?>&nbsp;<?= e($c->devise) ?></span>
                        <?php endif; ?>
                    </a>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- ============================================================ -->
<!-- TOP LIVRES + ACTIONS RAPIDES                                 -->
<!-- ============================================================ -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-5">
    <div class="bg-surface border border-border rounded-xl p-5">
        <h3 class="font-display font-semibold text-sm text-white mb-4">Top livres</h3>
        <?php if (empty($topLivres)): ?>
            <p class="text-text-dim text-sm">Aucune vente pour le moment.</p>
        <?php else: ?>
            <?php foreach ($topLivres as $i => $l): ?>
                <div class="flex items-center gap-3 py-2 <?= $i > 0 ? 'border-t border-border/50' : '' ?>">
                    <span class="text-text-dim text-xs w-5"><?= $i + 1 ?>.</span>
                    <a href="/livre/<?= e($l->slug) ?>" class="text-sm text-white hover:text-accent transition-colors truncate flex-grow"><?= e($l->titre) ?></a>
                    <span class="text-accent text-xs font-semibold"><?= (int) $l->total_ventes ?> ventes</span>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <div class="bg-surface border border-border rounded-xl p-5">
        <h3 class="font-display font-semibold text-sm text-white mb-4">Actions rapides</h3>
        <div class="space-y-2">
            <a href="/admin/livres/nouveau" class="flex items-center gap-3 px-3 py-2.5 bg-surface-2 rounded-lg text-sm text-text-muted hover:text-accent transition-colors">
                <svg class="w-4 h-4 text-accent" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
                Ajouter un livre
            </a>
            <a href="/admin/lecteurs" class="flex items-center gap-3 px-3 py-2.5 bg-surface-2 rounded-lg text-sm text-text-muted hover:text-accent transition-colors">
                <svg class="w-4 h-4 text-accent" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952"/></svg>
                Gérer les comptes
            </a>
            <a href="/" target="_blank" class="flex items-center gap-3 px-3 py-2.5 bg-surface-2 rounded-lg text-sm text-text-muted hover:text-accent transition-colors">
                <svg class="w-4 h-4 text-accent" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 6H5.25A2.25 2.25 0 003 8.25v10.5A2.25 2.25 0 005.25 21h10.5A2.25 2.25 0 0018 18.75V10.5m-10.5 6L21 3m0 0h-5.25M21 3v5.25"/></svg>
                Voir le site public
            </a>
        </div>
    </div>
</div>
