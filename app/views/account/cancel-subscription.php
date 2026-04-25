<section class="py-8 sm:py-12">
    <div class="max-w-[640px] mx-auto px-4 sm:px-6">

        <div class="bg-surface border border-border rounded-xl p-6 sm:p-8">
            <h1 class="font-display font-bold text-2xl text-white mb-2">On est tristes de te voir partir, <?= e($user->prenom) ?></h1>
            <p class="text-text-muted text-sm mb-6">Avant d'annuler, voici ce que tu vas perdre à la fin de ta période payée :</p>

            <ul class="space-y-2 mb-7 text-sm text-text-muted">
                <li class="flex gap-2"><span class="text-accent">•</span> Accès illimité au catalogue</li>
                <li class="flex gap-2"><span class="text-accent">•</span> Sauvegarde de ta progression de lecture</li>
                <li class="flex gap-2"><span class="text-accent">•</span> Accès aux nouveautés dès leur sortie</li>
                <li class="flex gap-2"><span class="text-accent">•</span> Lecture multi-appareils</li>
            </ul>

            <p class="text-text-dim text-xs mb-6">Bon à savoir : ton accès reste actif jusqu'au <strong class="text-white"><?= date('d/m/Y', strtotime($sub->date_fin)) ?></strong>. Tu pourras réactiver à tout moment avant cette date.</p>

            <form action="/mon-compte/abonnement/annuler" method="POST" class="space-y-5">
                <?= csrf_field() ?>

                <div>
                    <label class="block text-xs text-text-dim uppercase tracking-wider mb-2">Pourquoi annules-tu ?</label>
                    <select name="motif" required
                            class="w-full bg-surface-2 border border-border rounded px-4 py-2.5 text-sm text-white outline-none focus:border-accent">
                        <option value="">— choisir une raison —</option>
                        <option value="trop_cher">Trop cher pour moi</option>
                        <option value="pas_le_temps">Je n'ai pas eu le temps de lire</option>
                        <option value="catalogue">Le catalogue ne me convient pas</option>
                        <option value="alternative">J'ai trouvé une alternative</option>
                        <option value="technique">Problème technique</option>
                        <option value="autre">Autre</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs text-text-dim uppercase tracking-wider mb-2">Précisions (facultatif)</label>
                    <textarea name="raison" rows="3" maxlength="500"
                              placeholder="Aide-nous à nous améliorer…"
                              class="w-full bg-surface-2 border border-border rounded px-4 py-2.5 text-sm text-white outline-none focus:border-accent resize-none"></textarea>
                </div>

                <div class="flex flex-wrap gap-3 pt-2">
                    <a href="/mon-compte/abonnement" class="btn-primary">Garder mon abonnement</a>
                    <button type="submit" class="text-rose-400 hover:text-rose-300 text-sm py-2.5 px-4 transition-colors">Oui, annuler définitivement</button>
                </div>
            </form>
        </div>
    </div>
</section>
