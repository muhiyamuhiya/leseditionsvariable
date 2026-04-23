<section class="min-h-[calc(100vh-4rem)] flex items-center justify-center py-12 px-4">
    <div class="w-full max-w-md">
        <div class="bg-surface rounded-xl border border-border p-8">
            <h1 class="text-2xl font-display font-700 text-center text-white mb-2">Connexion</h1>
            <p class="text-center text-text-muted text-sm mb-8">Accédez à votre espace lecture</p>

            <?php
            $error = flash('error');
            $success = flash('success');
            $errors = flash('errors');
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

            <?php if ($errors): ?>
                <div class="bg-red-500/10 border border-red-500/30 text-red-400 px-4 py-3 rounded-lg mb-6 text-sm">
                    <ul class="list-disc list-inside space-y-1">
                        <?php foreach ($errors as $err): ?>
                            <li><?= e($err) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <form action="/connexion" method="POST" class="space-y-5">
                <?= csrf_field() ?>

                <div>
                    <label for="email" class="block text-sm font-medium text-text-muted mb-1">Adresse email</label>
                    <input type="email" id="email" name="email"
                           value="<?= e(flash('old_email') ?? '') ?>"
                           required autocomplete="email"
                           class="w-full px-4 py-2.5 bg-surface-2 border border-border rounded-lg text-white focus:ring-2 focus:ring-accent focus:border-accent outline-none transition placeholder:text-text-dim">
                </div>

                <div>
                    <label for="password" class="block text-sm font-medium text-text-muted mb-1">Mot de passe</label>
                    <input type="password" id="password" name="password"
                           required autocomplete="current-password"
                           class="w-full px-4 py-2.5 bg-surface-2 border border-border rounded-lg text-white focus:ring-2 focus:ring-accent focus:border-accent outline-none transition">
                </div>

                <div class="flex items-center justify-end">
                    <a href="/mot-de-passe-oublie" class="text-sm text-accent hover:text-accent-hover transition-colors">
                        Mot de passe oublié ?
                    </a>
                </div>

                <button type="submit" class="btn-primary w-full py-2.5">Se connecter</button>
            </form>

            <p class="mt-6 text-center text-sm text-text-muted">
                Pas encore inscrit ?
                <a href="/inscription" class="text-accent font-medium hover:text-accent-hover transition-colors">Créer un compte</a>
            </p>
        </div>
    </div>
</section>
