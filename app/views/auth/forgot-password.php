<section class="min-h-[calc(100vh-4rem)] flex items-center justify-center py-12 px-4">
    <div class="w-full max-w-md">
        <div class="bg-surface rounded-xl border border-border p-8">
            <h1 class="text-2xl font-display font-700 text-center text-white mb-2">Mot de passe oublié</h1>
            <p class="text-center text-text-muted text-sm mb-8">Entrez votre email pour recevoir un lien de réinitialisation</p>

            <?php
            $error = flash('error');
            $success = flash('success');
            ?>

            <?php if ($success): ?>
                <div class="bg-emerald-500/10 border border-emerald-500/30 text-emerald-400 px-4 py-3 rounded-lg mb-6 text-sm">
                    <?= e($success) ?>
                </div>
            <?php endif; ?>

            <?php if ($error): ?>
                <div class="bg-red-500/10 border border-red-500/30 text-red-400 px-4 py-3 rounded-lg mb-6 text-sm">
                    <?= e($error) ?>
                </div>
            <?php endif; ?>

            <form action="/mot-de-passe-oublie" method="POST" class="space-y-5">
                <?= csrf_field() ?>

                <div>
                    <label for="email" class="block text-sm font-medium text-text-muted mb-1">Adresse email</label>
                    <input type="email" id="email" name="email"
                           required autocomplete="email"
                           class="w-full px-4 py-2.5 bg-surface-2 border border-border rounded-lg text-white focus:ring-2 focus:ring-accent focus:border-accent outline-none transition placeholder:text-text-dim">
                </div>

                <button type="submit" class="btn-primary w-full py-2.5">Envoyer le lien</button>
            </form>

            <p class="mt-6 text-center text-sm text-text-muted">
                <a href="/connexion" class="text-accent font-medium hover:text-accent-hover transition-colors">Retour à la connexion</a>
            </p>
        </div>
    </div>
</section>
