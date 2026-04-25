<?php $error = flash('error'); ?>
<section class="py-12 sm:py-16">
    <div class="max-w-[560px] mx-auto px-4 sm:px-6">

        <div class="bg-surface border-2 border-rose-500/40 rounded-xl p-6 sm:p-8">
            <div class="w-14 h-14 rounded-full bg-rose-500/15 flex items-center justify-center mb-5">
                <svg class="w-7 h-7 text-rose-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z"/>
                </svg>
            </div>

            <h1 class="font-display font-bold text-2xl text-white mb-2">Confirmation de suppression définitive</h1>
            <p class="text-text-muted text-sm mb-6">Bonjour <?= e($prenom) ?>. Cette action est <strong class="text-rose-400">irréversible</strong>. En confirmant, tu perdras :</p>

            <ul class="space-y-2 mb-6 text-sm text-text-muted">
                <li class="flex gap-2"><span class="text-rose-400">•</span> L'accès à tes livres achetés</li>
                <li class="flex gap-2"><span class="text-rose-400">•</span> Toute ta progression de lecture</li>
                <li class="flex gap-2"><span class="text-rose-400">•</span> Tes avis et favoris</li>
                <li class="flex gap-2"><span class="text-rose-400">•</span> Tes versements en attente (si tu es auteur)</li>
            </ul>

            <?php if ($error): ?>
                <div class="bg-red-500/10 border border-red-500/30 text-red-400 px-4 py-3 rounded-lg mb-4 text-sm"><?= e($error) ?></div>
            <?php endif; ?>

            <form action="/supprimer-compte/confirmer/<?= e($token) ?>" method="POST" class="space-y-4">
                <?= csrf_field() ?>
                <div>
                    <label class="block text-xs text-text-dim uppercase tracking-wider mb-2">Tape <strong class="text-white">SUPPRIMER</strong> pour confirmer</label>
                    <input type="text" name="confirmation" required autocomplete="off"
                           class="w-full bg-surface-2 border border-border rounded px-4 py-2.5 text-sm text-white outline-none focus:border-rose-500 uppercase">
                </div>

                <div class="flex flex-wrap gap-3 pt-2">
                    <button type="submit" class="bg-rose-600 hover:bg-rose-700 text-white text-sm font-semibold px-5 py-2.5 rounded transition-colors">Supprimer définitivement</button>
                    <a href="/mon-compte" class="btn-secondary text-sm">Annuler</a>
                </div>
            </form>
        </div>
    </div>
</section>
