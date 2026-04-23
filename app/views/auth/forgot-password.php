<section class="min-h-[calc(100vh-160px)] flex items-center justify-center bg-gray-50 py-12 px-4">
    <div class="w-full max-w-md">
        <div class="bg-white rounded-2xl shadow-lg p-8">
            <h1 class="text-2xl font-bold text-center text-gray-800 mb-2">Mot de passe oublié</h1>
            <p class="text-center text-gray-500 mb-8">Entrez votre email pour recevoir un lien de réinitialisation</p>

            <?php
            $error = flash('error');
            $success = flash('success');
            ?>

            <?php if ($success): ?>
                <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg mb-6">
                    <?= e($success) ?>
                </div>
            <?php endif; ?>

            <?php if ($error): ?>
                <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg mb-6">
                    <?= e($error) ?>
                </div>
            <?php endif; ?>

            <form action="/mot-de-passe-oublie" method="POST" class="space-y-5">
                <?= csrf_field() ?>

                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Adresse email</label>
                    <input type="email" id="email" name="email"
                           required autocomplete="email"
                           class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none transition">
                </div>

                <button type="submit"
                        class="w-full bg-indigo-600 text-white py-2.5 px-4 rounded-lg font-medium hover:bg-indigo-700 focus:ring-4 focus:ring-indigo-200 transition">
                    Envoyer le lien
                </button>
            </form>

            <p class="mt-6 text-center text-sm text-gray-500">
                <a href="/connexion" class="text-indigo-600 font-medium hover:text-indigo-800">Retour à la connexion</a>
            </p>
        </div>
    </div>
</section>
