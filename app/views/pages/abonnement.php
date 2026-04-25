<?php
$user = \App\Lib\Auth::user();
$activeSub = $user ? \App\Models\Subscription::getActive($user->id) : null;
$activeType = $activeSub ? $activeSub->type : null;
?>
<section class="py-10 sm:py-16">
    <div class="max-w-[1100px] mx-auto px-4 sm:px-6">

        <div class="text-center mb-12">
            <h1 class="font-display font-extrabold text-3xl sm:text-4xl md:text-5xl text-white">Lis sans limite.</h1>
            <p class="text-text-muted text-base sm:text-lg mt-3 max-w-lg mx-auto">Trois formules. Un seul objectif : te donner accès à toute la littérature africaine francophone.</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-5 sm:gap-6">

            <!-- Essentiel Mensuel -->
            <div class="bg-surface border border-border rounded-xl p-6 sm:p-8 flex flex-col">
                <h3 class="font-display font-bold text-xl text-white">Essentiel</h3>
                <p class="text-text-dim text-xs uppercase tracking-wider mt-1">Mensuel</p>
                <div class="mt-5 mb-6">
                    <span class="font-display font-extrabold text-5xl text-white">3</span>
                    <span class="text-text-muted text-lg ml-1">$ / mois</span>
                </div>
                <ul class="space-y-3 text-sm text-text-muted flex-grow">
                    <li class="flex items-center gap-2"><svg class="w-4 h-4 text-accent flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/></svg>Accès à 80% du catalogue</li>
                    <li class="flex items-center gap-2"><svg class="w-4 h-4 text-accent flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/></svg>Tous les livres standards</li>
                    <li class="flex items-center gap-2"><svg class="w-4 h-4 text-accent flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/></svg>Lecture multi-appareils</li>
                    <li class="flex items-center gap-2"><svg class="w-4 h-4 text-accent flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/></svg>Annulation à tout moment</li>
                </ul>
                <?php if ($activeType === 'essentiel_mensuel'): ?>
                    <span class="block w-full mt-8 py-3 text-center bg-surface-2 text-text-dim text-sm rounded">Plan actuel</span>
                <?php else: ?>
                    <a href="/abonnement/souscrire/essentiel_mensuel" class="btn-secondary w-full mt-8 py-3 text-center block">Choisir ce plan</a>
                <?php endif; ?>
            </div>

            <!-- Essentiel Annuel — meilleure offre -->
            <div class="bg-surface border-2 border-accent rounded-xl p-6 sm:p-8 flex flex-col relative">
                <span class="absolute -top-3 left-1/2 -translate-x-1/2 bg-accent text-black text-[11px] font-bold px-3 py-1 rounded-full uppercase tracking-wider">Meilleure offre</span>
                <h3 class="font-display font-bold text-xl text-white">Essentiel</h3>
                <p class="text-text-dim text-xs uppercase tracking-wider mt-1">Annuel</p>
                <div class="mt-5 mb-1">
                    <span class="font-display font-extrabold text-5xl text-white">30</span>
                    <span class="text-text-muted text-lg ml-1">$ / an</span>
                </div>
                <p class="text-emerald-400 text-xs font-medium mb-5">≈ 2,50 $ / mois — 2 mois offerts</p>
                <ul class="space-y-3 text-sm text-text-muted flex-grow">
                    <li class="flex items-center gap-2"><svg class="w-4 h-4 text-accent flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/></svg>Tout Essentiel Mensuel</li>
                    <li class="flex items-center gap-2"><svg class="w-4 h-4 text-accent flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/></svg>2 mois offerts</li>
                    <li class="flex items-center gap-2"><svg class="w-4 h-4 text-accent flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/></svg>Accès prioritaire aux nouveautés</li>
                </ul>
                <?php if ($activeType === 'essentiel_annuel'): ?>
                    <span class="block w-full mt-8 py-3 text-center bg-surface-2 text-text-dim text-sm rounded">Plan actuel</span>
                <?php else: ?>
                    <a href="/abonnement/souscrire/essentiel_annuel" class="btn-primary w-full mt-8 py-3 text-center block">Choisir ce plan</a>
                <?php endif; ?>
            </div>

            <!-- Premium Mensuel -->
            <div class="bg-surface border border-border rounded-xl p-6 sm:p-8 flex flex-col relative">
                <span class="absolute -top-3 left-1/2 -translate-x-1/2 bg-gradient-to-r from-amber-400 to-amber-600 text-black text-[11px] font-bold px-3 py-1 rounded-full uppercase tracking-wider">Total</span>
                <h3 class="font-display font-bold text-xl text-white">Premium</h3>
                <p class="text-text-dim text-xs uppercase tracking-wider mt-1">Mensuel</p>
                <div class="mt-5 mb-6">
                    <span class="font-display font-extrabold text-5xl text-white">10</span>
                    <span class="text-text-muted text-lg ml-1">$ / mois</span>
                </div>
                <ul class="space-y-3 text-sm text-text-muted flex-grow">
                    <li class="flex items-center gap-2"><svg class="w-4 h-4 text-accent flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/></svg>100% du catalogue (incl. exclusivités)</li>
                    <li class="flex items-center gap-2"><svg class="w-4 h-4 text-accent flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/></svg>Téléchargement PDF offline</li>
                    <li class="flex items-center gap-2"><svg class="w-4 h-4 text-accent flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/></svg>1 livre physique trimestriel (RDC)</li>
                    <li class="flex items-center gap-2"><svg class="w-4 h-4 text-accent flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/></svg>Événements auteurs en ligne</li>
                    <li class="flex items-center gap-2"><svg class="w-4 h-4 text-accent flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/></svg>Support prioritaire</li>
                </ul>
                <?php if ($activeType === 'premium_mensuel'): ?>
                    <span class="block w-full mt-8 py-3 text-center bg-surface-2 text-text-dim text-sm rounded">Plan actuel</span>
                <?php else: ?>
                    <a href="/abonnement/souscrire/premium_mensuel" class="btn-secondary w-full mt-8 py-3 text-center block">Choisir ce plan</a>
                <?php endif; ?>
            </div>
        </div>

        <!-- Premium Annuel — discret en bas -->
        <div class="mt-8 bg-surface-2 border border-border rounded-xl p-5 flex flex-wrap items-center justify-between gap-4">
            <div>
                <span class="text-white font-semibold">Premium Annuel</span>
                <span class="text-text-dim text-sm ml-2">100 $ / an — 2 mois offerts par rapport au mensuel</span>
            </div>
            <?php if ($activeType === 'premium_annuel'): ?>
                <span class="text-text-dim text-sm">Plan actuel</span>
            <?php else: ?>
                <a href="/abonnement/souscrire/premium_annuel" class="text-accent text-sm hover:text-accent-hover">Choisir ce plan →</a>
            <?php endif; ?>
        </div>

        <!-- Comparateur Essentiel vs Premium -->
        <div class="mt-12 bg-surface border border-border rounded-xl overflow-hidden">
            <div class="px-5 sm:px-7 py-4 border-b border-border">
                <h2 class="font-display font-semibold text-lg text-white">Comparer Essentiel et Premium</h2>
            </div>
            <div class="overflow-x-auto">
            <table class="w-full text-sm min-w-[480px]">
                <thead>
                    <tr class="text-text-dim border-b border-border">
                        <th class="text-left px-5 sm:px-7 py-3 font-medium">Fonctionnalité</th>
                        <th class="text-center px-3 py-3 font-medium">Essentiel</th>
                        <th class="text-center px-3 py-3 font-medium text-accent">Premium</th>
                    </tr>
                </thead>
                <tbody class="text-text-muted">
                    <?php
                    $check = '<svg class="inline w-4 h-4 text-emerald-400" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/></svg>';
                    $cross = '<svg class="inline w-4 h-4 text-text-dim" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>';
                    $rows = [
                        ['Accès aux livres standards', $check, $check],
                        ['Accès aux livres exclusifs', $cross, $check],
                        ['Téléchargement PDF offline', $cross, $check],
                        ['Livre physique trimestriel', $cross, $check],
                        ['Lecture multi-appareils',    $check, $check],
                        ['Annulation à tout moment',   $check, $check],
                    ];
                    foreach ($rows as [$label, $ess, $prem]):
                    ?>
                    <tr class="border-b border-border last:border-0">
                        <td class="px-5 sm:px-7 py-3"><?= e($label) ?></td>
                        <td class="text-center px-3 py-3"><?= $ess ?></td>
                        <td class="text-center px-3 py-3"><?= $prem ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            </div>
        </div>

        <p class="text-text-dim text-center text-xs mt-8">Annule quand tu veux. Sans engagement. Paiement par Mobile Money ou carte bancaire.</p>
    </div>
</section>
