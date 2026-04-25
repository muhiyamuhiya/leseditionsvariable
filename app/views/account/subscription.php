<?php
$success = flash('success');
$error = flash('error');
?>
<?php if ($success): ?>
    <div class="bg-emerald-500/10 border border-emerald-500/30 text-emerald-400 px-4 py-3 rounded-lg mt-6 mx-4 sm:mx-6 max-w-[800px] sm:mx-auto text-sm"><?= e($success) ?></div>
<?php endif; ?>
<?php if ($error): ?>
    <div class="bg-red-500/10 border border-red-500/30 text-red-400 px-4 py-3 rounded-lg mt-6 mx-4 sm:mx-6 max-w-[800px] sm:mx-auto text-sm"><?= e($error) ?></div>
<?php endif; ?>

<section class="py-8 sm:py-12">
    <div class="max-w-[800px] mx-auto px-4 sm:px-6">
        <h1 class="font-display font-extrabold text-2xl sm:text-3xl text-white mb-8">Mon abonnement</h1>

        <?php if (!$sub): ?>
            <div class="bg-surface border border-border rounded-xl p-8 text-center">
                <p class="text-text-muted mb-5">Tu n'as pas d'abonnement actif.</p>
                <a href="/abonnement" class="btn-primary">Découvrir les offres</a>
            </div>
        <?php else: ?>
            <?php
                $joursRestants = max(0, (int) ((strtotime($sub->date_fin) - time()) / 86400));
                $isCancelled = $sub->statut === 'annule';
            ?>
            <div class="bg-surface border border-border rounded-xl p-6 sm:p-8">

                <div class="flex flex-wrap items-start justify-between gap-3 mb-5">
                    <div>
                        <span class="inline-block bg-accent/20 text-accent text-xs font-bold px-2.5 py-1 rounded uppercase tracking-wider"><?= e($planLabel) ?></span>
                        <?php if ($tier === 'premium'): ?>
                            <span class="inline-block bg-gradient-to-r from-amber-400/20 to-amber-600/20 text-amber-400 text-xs font-bold px-2.5 py-1 rounded ml-1 uppercase tracking-wider">Premium</span>
                        <?php endif; ?>
                    </div>
                    <?php if ($isCancelled): ?>
                        <span class="text-rose-400 text-xs font-medium">Annulé — accès actif jusqu'à expiration</span>
                    <?php endif; ?>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
                    <div>
                        <p class="text-text-dim text-xs uppercase tracking-wider mb-1">Date de début</p>
                        <p class="text-white text-sm"><?= date('d/m/Y', strtotime($sub->date_debut)) ?></p>
                    </div>
                    <div>
                        <p class="text-text-dim text-xs uppercase tracking-wider mb-1">Expiration</p>
                        <p class="text-white text-sm"><?= date('d/m/Y', strtotime($sub->date_fin)) ?></p>
                    </div>
                    <div>
                        <p class="text-text-dim text-xs uppercase tracking-wider mb-1">Compte à rebours</p>
                        <p class="text-accent text-sm font-semibold"><?= $joursRestants ?> jour<?= $joursRestants > 1 ? 's' : '' ?></p>
                    </div>
                </div>

                <?php if ($isCancelled): ?>
                    <form action="/mon-compte/abonnement/reactiver" method="POST">
                        <?= csrf_field() ?>
                        <button type="submit" class="btn-primary text-sm">Réactiver mon abonnement</button>
                    </form>
                <?php else: ?>
                    <div class="flex flex-wrap gap-3">
                        <a href="/abonnement" class="btn-secondary text-sm">Changer de plan</a>
                        <a href="/mon-compte/abonnement/annuler" class="text-rose-400 hover:text-rose-300 text-sm py-2.5 px-3 transition-colors">Annuler mon abonnement</a>
                    </div>
                <?php endif; ?>
            </div>

        <?php endif; ?>
    </div>
</section>
