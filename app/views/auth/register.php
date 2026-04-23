<section class="min-h-[calc(100vh-160px)] flex items-center justify-center bg-gray-50 py-12 px-4">
    <div class="w-full max-w-lg">
        <div class="bg-white rounded-2xl shadow-lg p-8">
            <h1 class="text-2xl font-bold text-center text-gray-800 mb-2">Créer un compte</h1>
            <p class="text-center text-gray-500 mb-8">Rejoignez la communauté des lecteurs</p>

            <?php
            $error = flash('error');
            $errors = flash('errors');
            ?>

            <?php if ($error): ?>
                <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg mb-6">
                    <?= e($error) ?>
                </div>
            <?php endif; ?>

            <?php if ($errors): ?>
                <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg mb-6">
                    <ul class="list-disc list-inside space-y-1">
                        <?php foreach ($errors as $err): ?>
                            <li><?= e($err) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <form action="/inscription" method="POST" class="space-y-5">
                <?= csrf_field() ?>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="prenom" class="block text-sm font-medium text-gray-700 mb-1">Prénom</label>
                        <input type="text" id="prenom" name="prenom"
                               value="<?= e(flash('old_prenom') ?? '') ?>"
                               required minlength="2" maxlength="100"
                               class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none transition">
                    </div>
                    <div>
                        <label for="nom" class="block text-sm font-medium text-gray-700 mb-1">Nom</label>
                        <input type="text" id="nom" name="nom"
                               value="<?= e(flash('old_nom') ?? '') ?>"
                               required minlength="2" maxlength="100"
                               class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none transition">
                    </div>
                </div>

                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Adresse email</label>
                    <input type="email" id="email" name="email"
                           value="<?= e(flash('old_email') ?? '') ?>"
                           required autocomplete="email"
                           class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none transition">
                </div>

                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700 mb-1">Mot de passe</label>
                    <input type="password" id="password" name="password"
                           required minlength="8" autocomplete="new-password"
                           class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none transition">
                    <p class="mt-1 text-xs text-gray-400">Minimum 8 caractères, au moins une lettre et un chiffre</p>
                </div>

                <div>
                    <label for="password_confirmation" class="block text-sm font-medium text-gray-700 mb-1">Confirmer le mot de passe</label>
                    <input type="password" id="password_confirmation" name="password_confirmation"
                           required minlength="8" autocomplete="new-password"
                           class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none transition">
                </div>

                <div>
                    <label for="code_parrain" class="block text-sm font-medium text-gray-700 mb-1">
                        Code de parrainage <span class="text-gray-400 font-normal">(optionnel)</span>
                    </label>
                    <input type="text" id="code_parrain" name="code_parrain"
                           value="<?= e(flash('old_code_parrain') ?? ($refCode ?? '')) ?>"
                           maxlength="20"
                           class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none transition">
                </div>

                <div class="space-y-3">
                    <label class="flex items-start gap-3 cursor-pointer">
                        <input type="checkbox" name="accepte_cgu" required
                               class="mt-0.5 h-4 w-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500">
                        <span class="text-sm text-gray-600">
                            J'accepte les <a href="/cgu" target="_blank" class="text-indigo-600 hover:text-indigo-800 underline">conditions générales d'utilisation</a> *
                        </span>
                    </label>

                    <label class="flex items-start gap-3 cursor-pointer">
                        <input type="checkbox" name="accepte_newsletter"
                               <?= flash('old_newsletter') ? 'checked' : '' ?>
                               class="mt-0.5 h-4 w-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500">
                        <span class="text-sm text-gray-600">
                            Je souhaite recevoir les nouveautés et recommandations par email
                        </span>
                    </label>
                </div>

                <button type="submit"
                        class="w-full bg-indigo-600 text-white py-2.5 px-4 rounded-lg font-medium hover:bg-indigo-700 focus:ring-4 focus:ring-indigo-200 transition">
                    Créer mon compte
                </button>
            </form>

            <p class="mt-6 text-center text-sm text-gray-500">
                Déjà inscrit ?
                <a href="/connexion" class="text-indigo-600 font-medium hover:text-indigo-800">Se connecter</a>
            </p>
        </div>
    </div>
</section>
