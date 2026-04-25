<?php
$success = flash('newsletter_success');
$error = flash('newsletter_error');
?>
<section class="py-12 sm:py-20">
    <div class="max-w-3xl mx-auto px-4 sm:px-6">

        <div class="text-center mb-14">
            <div class="text-5xl mb-4" aria-hidden="true">✉️</div>
            <h1 class="font-display font-extrabold text-3xl sm:text-4xl md:text-5xl text-white mb-3">Notre lettre mensuelle</h1>
            <p class="text-text-muted text-base sm:text-lg">Une fois par mois. Les nouveautés, les coulisses, les conseils. Jamais de spam, promis.</p>
        </div>

        <!-- Pourquoi -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-10">
            <div class="bg-surface border border-border rounded-xl p-5">
                <div class="text-2xl mb-2" aria-hidden="true">🆕</div>
                <h3 class="font-display font-semibold text-white text-sm mb-1">Nouveautés en avant-première</h3>
                <p class="text-text-muted text-xs">Tu sauras avant tout le monde quels livres arrivent.</p>
            </div>
            <div class="bg-surface border border-border rounded-xl p-5">
                <div class="text-2xl mb-2" aria-hidden="true">💡</div>
                <h3 class="font-display font-semibold text-white text-sm mb-1">Conseils d'auteurs</h3>
                <p class="text-text-muted text-xs">Comment ils écrivent, s'organisent, ce qu'ils lisent.</p>
            </div>
            <div class="bg-surface border border-border rounded-xl p-5">
                <div class="text-2xl mb-2" aria-hidden="true">🎁</div>
                <h3 class="font-display font-semibold text-white text-sm mb-1">Réductions exclusives</h3>
                <p class="text-text-muted text-xs">Codes promo réservés aux abonnés newsletter.</p>
            </div>
        </div>

        <!-- Formulaire -->
        <div class="bg-gradient-to-br from-accent/15 to-amber-600/10 border border-accent/30 rounded-xl p-7 sm:p-10 mb-10">
            <h2 class="font-display font-bold text-xl text-white mb-4 text-center">Rejoins la liste</h2>

            <?php if ($success): ?>
                <div class="bg-emerald-500/10 border border-emerald-500/30 text-emerald-400 px-4 py-3 rounded-lg mb-4 text-sm text-center"><?= e($success) ?></div>
            <?php endif; ?>
            <?php if ($error): ?>
                <div class="bg-red-500/10 border border-red-500/30 text-red-400 px-4 py-3 rounded-lg mb-4 text-sm text-center"><?= e($error) ?></div>
            <?php endif; ?>

            <form action="/newsletter/subscribe" method="POST" class="max-w-md mx-auto space-y-3">
                <?= csrf_field() ?>
                <input type="text" name="prenom" placeholder="Ton prénom (optionnel)"
                       class="w-full bg-surface-2 border border-border rounded px-4 py-3 text-sm text-white outline-none focus:border-accent">
                <input type="email" name="email" required placeholder="ton@email.com"
                       class="w-full bg-surface-2 border border-border rounded px-4 py-3 text-sm text-white outline-none focus:border-accent">
                <button type="submit" class="btn-primary w-full">Je m'abonne</button>
            </form>
            <p class="text-text-dim text-xs text-center mt-3">Tu peux te désinscrire à tout moment depuis le lien en bas de chaque email.</p>
        </div>

        <!-- Aperçu -->
        <div class="bg-surface border border-border rounded-xl p-6 sm:p-8">
            <p class="text-text-dim text-xs uppercase tracking-wider mb-2">Édition d'avril 2026</p>
            <h2 class="font-display font-semibold text-white text-lg mb-3">Au sommaire de la prochaine édition</h2>
            <ul class="space-y-2 text-text-muted text-sm">
                <li class="flex gap-2"><span class="text-accent">•</span> Les premiers livres publiés sur Variable</li>
                <li class="flex gap-2"><span class="text-accent">•</span> Interview du fondateur dans un média congolais</li>
                <li class="flex gap-2"><span class="text-accent">•</span> Tutoriel : poster ton premier avis sur la plateforme</li>
                <li class="flex gap-2"><span class="text-accent">•</span> Code promo abonnement annuel pour nos abonnés newsletter</li>
            </ul>
            <p class="text-text-dim text-xs mt-5">À paraître fin avril. Inscris-toi pour la recevoir.</p>
        </div>

    </div>
</section>
