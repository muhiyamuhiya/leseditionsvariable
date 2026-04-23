<section class="min-h-[calc(100vh-4rem)] flex items-center justify-center py-12 px-4">
    <div class="w-full max-w-md">
        <div class="bg-surface rounded-xl border border-border p-8">
            <h1 class="text-2xl font-display font-700 text-center text-white mb-2">Nouveau mot de passe</h1>
            <p class="text-center text-text-muted text-sm mb-8">Choisissez votre nouveau mot de passe</p>

            <?php
            $error = flash('error');
            $errors = flash('errors');
            ?>

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

            <form action="/reset-password/<?= e($token) ?>" method="POST" class="space-y-5">
                <?= csrf_field() ?>

                <div>
                    <label for="password" class="block text-sm font-medium text-text-muted mb-1">Nouveau mot de passe</label>
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

                <button type="submit" class="btn-primary w-full py-2.5">Changer le mot de passe</button>
            </form>
        </div>
    </div>
</section>
