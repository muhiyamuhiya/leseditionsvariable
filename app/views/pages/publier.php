<?php
$user = App\Lib\Auth::user();
$author = App\Lib\Auth::getAuthorRecord();
?>
<section class="py-10 sm:py-16">
    <div class="max-w-[800px] mx-auto px-4 sm:px-6">
        <h1 class="font-display font-extrabold text-3xl sm:text-4xl text-white mb-2">Publier chez Variable</h1>
        <p class="text-text-muted text-lg mb-8">Tu es auteur ? Publie chez ceux qui te liront vraiment.</p>

        <!-- Alerte statut si connecté -->
        <?php if ($user && $author && $author->statut_validation === 'en_attente'): ?>
            <div class="bg-accent/10 border border-accent/30 rounded-lg p-5 mb-8 text-center">
                <p class="text-accent font-medium">Ta candidature est en cours d'examen. Nous reviendrons vers toi sous 5 jours ouvrés.</p>
                <a href="/auteur/candidater" class="text-accent/70 text-sm hover:text-accent mt-2 inline-block">Mettre à jour ma candidature</a>
            </div>
        <?php endif; ?>

        <div class="text-text-muted text-[15px] leading-[1.8] space-y-5">
            <h2 class="font-display font-bold text-xl text-white">Nos engagements</h2>
            <ul class="space-y-3">
                <li class="flex items-start gap-3"><svg class="w-5 h-5 text-accent flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/></svg><span><strong class="text-white">80% de chaque vente</strong> te revient directement</span></li>
                <li class="flex items-start gap-3"><svg class="w-5 h-5 text-accent flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/></svg><span>Tes <strong class="text-white">droits d'auteur restent à toi</strong></span></li>
                <li class="flex items-start gap-3"><svg class="w-5 h-5 text-accent flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/></svg><span>Accès au <strong class="text-white">pool de redistribution</strong> des abonnements</span></li>
                <li class="flex items-start gap-3"><svg class="w-5 h-5 text-accent flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/></svg><span>Services professionnels : correction, mise en page, couverture</span></li>
            </ul>

            <h2 class="font-display font-bold text-xl text-white pt-4">Le processus</h2>
            <div class="bg-surface border border-border rounded-lg p-6 space-y-4">
                <div class="flex gap-4"><span class="text-accent font-bold">01</span><p>Tu remplis le formulaire de candidature en ligne</p></div>
                <div class="flex gap-4"><span class="text-accent font-bold">02</span><p>Nous examinons ta candidature sous 5 jours ouvrés</p></div>
                <div class="flex gap-4"><span class="text-accent font-bold">03</span><p>Une fois validé, tu soumets ton manuscrit PDF depuis ton espace auteur</p></div>
                <div class="flex gap-4"><span class="text-accent font-bold">04</span><p>Ton livre est publié et disponible sur la plateforme</p></div>
            </div>

            <!-- CTA dynamique -->
            <div class="text-center pt-6">
                <?php if (!$user): ?>
                    <a href="/connexion?redirect=/auteur/candidater" class="btn-primary text-base px-8 py-3">Soumettre mon manuscrit</a>
                    <p class="text-text-dim text-xs mt-3">Tu n'as pas encore de compte ? <a href="/inscription" class="text-accent hover:text-accent-hover">Inscris-toi</a> d'abord.</p>
                <?php elseif ($user->role === 'lecteur'): ?>
                    <a href="/auteur/candidater" class="btn-primary text-base px-8 py-3">Postuler maintenant</a>
                <?php elseif ($user->role === 'admin'): ?>
                    <a href="/auteur" class="btn-primary text-base px-8 py-3">Accéder à l'espace auteur</a>
                <?php elseif ($author && $author->statut_validation === 'valide'): ?>
                    <a href="/auteur/livres/nouveau" class="btn-primary text-base px-8 py-3">Ajouter un nouveau livre</a>
                <?php elseif ($author && $author->statut_validation === 'refuse'): ?>
                    <a href="/auteur/candidater" class="btn-primary text-base px-8 py-3">Compléter ma candidature</a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>
