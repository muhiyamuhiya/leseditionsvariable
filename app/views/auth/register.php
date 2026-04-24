<section class="min-h-[calc(100vh-4rem)] flex items-center justify-center py-12 px-4">
    <div class="w-full max-w-lg">
        <div class="bg-surface rounded-xl border border-border p-8">
            <h1 class="text-2xl font-display font-700 text-center text-white mb-2">Créer un compte</h1>
            <p class="text-center text-text-muted text-sm mb-8">Rejoignez la communauté des lecteurs</p>

            <?php
            $error = flash('error');
            $errors = flash('errors');
            $redirectUrl = $_GET['redirect'] ?? '';
            ?>

            <?php if ($redirectUrl === '/auteur/candidater'): ?>
                <div class="bg-accent/10 border border-accent/30 rounded-lg p-4 mb-6 text-center">
                    <p class="text-accent font-medium text-sm">Inscris-toi pour postuler en tant qu'auteur</p>
                    <p class="text-text-dim text-xs mt-1">Après ton inscription, tu seras dirigé vers le formulaire de candidature.</p>
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

            <form action="/inscription" method="POST" class="space-y-5">
                <?= csrf_field() ?>
                <?php if ($redirectUrl): ?>
                    <input type="hidden" name="redirect" value="<?= e($redirectUrl) ?>">
                <?php endif; ?>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="prenom" class="block text-sm font-medium text-text-muted mb-1">Prénom</label>
                        <input type="text" id="prenom" name="prenom"
                               value="<?= e(flash('old_prenom') ?? '') ?>"
                               required minlength="2" maxlength="100"
                               class="w-full px-4 py-2.5 bg-surface-2 border border-border rounded-lg text-white focus:ring-2 focus:ring-accent focus:border-accent outline-none transition placeholder:text-text-dim">
                    </div>
                    <div>
                        <label for="nom" class="block text-sm font-medium text-text-muted mb-1">Nom</label>
                        <input type="text" id="nom" name="nom"
                               value="<?= e(flash('old_nom') ?? '') ?>"
                               required minlength="2" maxlength="100"
                               class="w-full px-4 py-2.5 bg-surface-2 border border-border rounded-lg text-white focus:ring-2 focus:ring-accent focus:border-accent outline-none transition placeholder:text-text-dim">
                    </div>
                </div>

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
                           required minlength="8" autocomplete="new-password"
                           class="w-full px-4 py-2.5 bg-surface-2 border border-border rounded-lg text-white focus:ring-2 focus:ring-accent focus:border-accent outline-none transition">
                    <p class="mt-1 text-xs text-text-dim">Minimum 8 caractères, au moins une lettre et un chiffre</p>
                </div>

                <div>
                    <label for="password_confirmation" class="block text-sm font-medium text-text-muted mb-1">Confirmer le mot de passe</label>
                    <input type="password" id="password_confirmation" name="password_confirmation"
                           required minlength="8" autocomplete="new-password"
                           class="w-full px-4 py-2.5 bg-surface-2 border border-border rounded-lg text-white focus:ring-2 focus:ring-accent focus:border-accent outline-none transition">
                </div>

                <div>
                    <label for="code_parrain" class="block text-sm font-medium text-text-muted mb-1">
                        Code de parrainage <span class="text-text-dim font-normal">(optionnel)</span>
                    </label>
                    <input type="text" id="code_parrain" name="code_parrain"
                           value="<?= e(flash('old_code_parrain') ?? ($refCode ?? '')) ?>"
                           maxlength="20"
                           class="w-full px-4 py-2.5 bg-surface-2 border border-border rounded-lg text-white focus:ring-2 focus:ring-accent focus:border-accent outline-none transition placeholder:text-text-dim">
                </div>

                <div class="space-y-3">
                    <label class="flex items-start gap-3 cursor-pointer">
                        <input type="checkbox" name="accepte_cgu" required
                               class="mt-0.5 h-4 w-4 rounded border-border bg-surface-2 text-accent focus:ring-accent">
                        <span class="text-sm text-text-muted">
                            J'accepte les <a href="/cgu" target="_blank" class="text-accent hover:text-accent-hover underline">conditions générales d'utilisation</a> *
                        </span>
                    </label>

                    <label class="flex items-start gap-3 cursor-pointer">
                        <input type="checkbox" name="accepte_newsletter"
                               <?= flash('old_newsletter') ? 'checked' : '' ?>
                               class="mt-0.5 h-4 w-4 rounded border-border bg-surface-2 text-accent focus:ring-accent">
                        <span class="text-sm text-text-muted">
                            Je souhaite recevoir les nouveautés et recommandations par email
                        </span>
                    </label>
                </div>

                <button type="submit" class="btn-primary w-full py-2.5">Créer mon compte</button>
            </form>

            <p class="mt-6 text-center text-sm text-text-muted">
                Déjà inscrit ?
                <a href="/connexion<?= $redirectUrl ? '?redirect=' . urlencode($redirectUrl) : '' ?>" class="text-accent font-medium hover:text-accent-hover transition-colors">Se connecter</a>
            </p>
        </div>
    </div>
</section>
