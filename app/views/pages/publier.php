<?php
$user = App\Lib\Auth::user();
$author = App\Lib\Auth::getAuthorRecord();
?>
<section class="py-12 sm:py-20">
    <div class="max-w-4xl mx-auto px-4 sm:px-6">

        <!-- Hero -->
        <div class="text-center mb-14">
            <h1 class="font-display font-extrabold text-3xl sm:text-4xl md:text-5xl text-white leading-tight mb-4">
                Publier ton livre chez Variable
            </h1>
            <p class="text-text-muted text-base sm:text-lg max-w-2xl mx-auto">
                Pour les auteurs africains et de la diaspora qui veulent toucher leur public sans intermédiaire.
            </p>
        </div>

        <!-- Pourquoi Variable -->
        <h2 class="font-display font-bold text-2xl text-white mb-5 text-center">Pourquoi Variable</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-12">
            <div class="bg-surface border border-border rounded-xl p-6">
                <div class="text-3xl mb-3" aria-hidden="true">💰</div>
                <h3 class="font-display font-semibold text-white mb-2">70% pour toi</h3>
                <p class="text-text-muted text-sm">Sur chaque vente, tu reçois 70% du prix. La plateforme garde 30% pour l'hébergement, les paiements et la promotion. Plus avantageux que l'édition traditionnelle.</p>
            </div>
            <div class="bg-surface border border-border rounded-xl p-6">
                <div class="text-3xl mb-3" aria-hidden="true">🎁</div>
                <h3 class="font-display font-semibold text-white mb-2">Pas d'avance, pas de frais cachés</h3>
                <p class="text-text-muted text-sm">Tu ne paies rien pour publier. Tu es payé dès la première vente.</p>
            </div>
            <div class="bg-surface border border-border rounded-xl p-6">
                <div class="text-3xl mb-3" aria-hidden="true">📚</div>
                <h3 class="font-display font-semibold text-white mb-2">Public engagé</h3>
                <p class="text-text-muted text-sm">Notre lectorat aime la littérature africaine. Ils cherchent des auteurs comme toi.</p>
            </div>
            <div class="bg-surface border border-border rounded-xl p-6">
                <div class="text-3xl mb-3" aria-hidden="true">✏️</div>
                <h3 class="font-display font-semibold text-white mb-2">Service éditorial pro</h3>
                <p class="text-text-muted text-sm">Si tu veux, on t'accompagne. Relecture, mise en page, couverture. Tarifs accessibles.</p>
            </div>
        </div>

        <!-- Comment ça marche -->
        <h2 class="font-display font-bold text-2xl text-white mb-5 text-center">Comment ça marche</h2>
        <div class="bg-surface border border-border rounded-xl p-7 sm:p-10 mb-12">
            <ol class="space-y-6">
                <li class="flex gap-4">
                    <span class="flex-shrink-0 w-8 h-8 rounded-full bg-accent text-black font-display font-bold flex items-center justify-center text-sm">1</span>
                    <div>
                        <p class="text-white font-semibold">Tu candidates</p>
                        <p class="text-text-muted text-sm">Tu nous parles de toi et de ton projet via le formulaire de candidature.</p>
                    </div>
                </li>
                <li class="flex gap-4">
                    <span class="flex-shrink-0 w-8 h-8 rounded-full bg-accent text-black font-display font-bold flex items-center justify-center text-sm">2</span>
                    <div>
                        <p class="text-white font-semibold">Nous validons</p>
                        <p class="text-text-muted text-sm">Sous 7 jours, on te répond. Validation, demande d'infos complémentaires, ou refus argumenté.</p>
                    </div>
                </li>
                <li class="flex gap-4">
                    <span class="flex-shrink-0 w-8 h-8 rounded-full bg-accent text-black font-display font-bold flex items-center justify-center text-sm">3</span>
                    <div>
                        <p class="text-white font-semibold">Tu soumets ton manuscrit</p>
                        <p class="text-text-muted text-sm">PDF, DOCX, peu importe. Tu le déposes depuis ton dashboard auteur.</p>
                    </div>
                </li>
                <li class="flex gap-4">
                    <span class="flex-shrink-0 w-8 h-8 rounded-full bg-accent text-black font-display font-bold flex items-center justify-center text-sm">4</span>
                    <div>
                        <p class="text-white font-semibold">On publie</p>
                        <p class="text-text-muted text-sm">Mise en ligne sur la plateforme. Vérification qualité avant publication.</p>
                    </div>
                </li>
                <li class="flex gap-4">
                    <span class="flex-shrink-0 w-8 h-8 rounded-full bg-accent text-black font-display font-bold flex items-center justify-center text-sm">5</span>
                    <div>
                        <p class="text-white font-semibold">Tu encaisses</p>
                        <p class="text-text-muted text-sm">Versements mensuels dès 20 USD de revenus, par Mobile Money ou virement.</p>
                    </div>
                </li>
            </ol>
        </div>

        <!-- Services éditoriaux -->
        <h2 class="font-display font-bold text-2xl text-white mb-5 text-center">Nos services éditoriaux</h2>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-3 mb-6">
            <div class="bg-surface border border-border rounded-xl p-4 text-center">
                <p class="text-2xl mb-1">✏️</p>
                <p class="text-white text-sm font-semibold">Relecture</p>
                <p class="text-accent text-sm font-bold mt-1">75 $</p>
            </div>
            <div class="bg-surface border border-border rounded-xl p-4 text-center">
                <p class="text-2xl mb-1">🧱</p>
                <p class="text-white text-sm font-semibold">Mise en page</p>
                <p class="text-accent text-sm font-bold mt-1">120 $</p>
            </div>
            <div class="bg-surface border border-border rounded-xl p-4 text-center">
                <p class="text-2xl mb-1">🎨</p>
                <p class="text-white text-sm font-semibold">Couverture</p>
                <p class="text-accent text-sm font-bold mt-1">150 $</p>
            </div>
            <div class="bg-surface border border-border rounded-xl p-4 text-center">
                <p class="text-2xl mb-1">📦</p>
                <p class="text-white text-sm font-semibold">Pack complet</p>
                <p class="text-accent text-sm font-bold mt-1">Sur devis</p>
            </div>
        </div>
        <div class="text-center mb-12">
            <a href="/services-editoriaux" class="text-accent hover:text-accent-hover text-sm">Découvrir tous les services →</a>
        </div>

        <!-- Témoignages placeholder -->
        <div class="bg-surface border border-border rounded-xl p-7 mb-12 text-center">
            <p class="text-text-dim text-sm">Bientôt, les histoires de nos premiers auteurs…</p>
            <p class="text-text-dim text-xs mt-1">Témoignages à venir</p>
        </div>

        <!-- FAQ auteur -->
        <h2 class="font-display font-bold text-2xl text-white mb-5 text-center">Questions fréquentes</h2>
        <div class="space-y-3 mb-12" x-data="{ open: null }">
            <?php
            $faqs = [
                ['Combien de temps prend la validation ?', 'Sous 7 jours ouvrables, on te répond. Si la candidature est complète et claire, on traite en 2-3 jours.'],
                ['Puis-je publier un livre déjà publié ailleurs ?', 'Oui, à condition que tes droits soient libres ou que ton contrat précédent autorise une exploitation numérique sur d\'autres plateformes. Vérifie ton contrat avant de candidater.'],
                ['Comment sont protégés mes droits d\'auteur ?', 'Tu gardes la propriété intellectuelle de ton œuvre. Variable obtient une licence d\'exploitation non exclusive sur la version numérique. Tu peux retirer ton livre à tout moment.'],
                ['Quand suis-je payé ?', 'Versements mensuels, dès 20 USD de revenus accumulés. Le 5 du mois suivant. Mobile Money, virement bancaire ou compte Stripe.'],
                ['Puis-je retirer mon livre ?', 'Oui, à tout moment, depuis ton dashboard auteur. Le livre passe en statut « retiré » et n\'est plus accessible aux nouveaux lecteurs.'],
                ['Quels formats acceptez-vous ?', 'PDF, DOCX, ODT pour le manuscrit. JPEG/PNG/WebP pour la couverture (1200×1800px recommandé).'],
            ];
            foreach ($faqs as $i => [$q, $a]):
            ?>
                <div class="bg-surface border border-border rounded-xl overflow-hidden">
                    <button @click="open = open === <?= $i ?> ? null : <?= $i ?>" class="w-full px-5 py-4 text-left flex items-center justify-between hover:bg-surface-2 transition-colors">
                        <span class="text-white font-medium text-sm"><?= e($q) ?></span>
                        <svg class="w-4 h-4 text-text-dim transition-transform" :class="open === <?= $i ?> ? 'rotate-180' : ''" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5"/></svg>
                    </button>
                    <div x-show="open === <?= $i ?>" x-cloak class="px-5 pb-4 text-text-muted text-sm"><?= e($a) ?></div>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- CTA final -->
        <div class="bg-gradient-to-r from-accent/20 to-amber-600/20 border border-accent/30 rounded-xl p-7 text-center">
            <h2 class="font-display font-bold text-2xl text-white mb-3">Prêt à partager ta voix avec le monde ?</h2>
            <div class="flex flex-wrap gap-3 justify-center mt-5">
                <?php if (!$user): ?>
                    <a href="/inscription" class="btn-primary">Créer mon compte</a>
                    <a href="/contact" class="btn-secondary">Discuter avec nous</a>
                <?php elseif (!$author): ?>
                    <a href="/auteur/candidater" class="btn-primary">Candidater maintenant</a>
                    <a href="/contact" class="btn-secondary">Discuter avec nous</a>
                <?php else: ?>
                    <a href="/auteur" class="btn-primary">Mon dashboard auteur</a>
                    <a href="/auteur/livres/nouveau" class="btn-secondary">Soumettre un nouveau livre</a>
                <?php endif; ?>
            </div>
        </div>

    </div>
</section>
