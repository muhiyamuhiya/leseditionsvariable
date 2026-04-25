<?php
$success = flash('success');
$error = flash('error');
?>

<?php if ($success): ?>
    <div class="bg-emerald-500/10 border border-emerald-500/30 text-emerald-400 px-4 py-3 rounded-lg mb-6 text-sm"><?= e($success) ?></div>
<?php endif; ?>
<?php if ($error): ?>
    <div class="bg-red-500/10 border border-red-500/30 text-red-400 px-4 py-3 rounded-lg mb-6 text-sm"><?= e($error) ?></div>
<?php endif; ?>

<section class="py-8 sm:py-12">
    <div class="max-w-[800px] mx-auto px-4 sm:px-6">

        <h1 class="font-display font-extrabold text-2xl sm:text-3xl text-white mb-8">Mon profil</h1>

        <form action="/mon-compte/profil" method="POST" enctype="multipart/form-data" class="space-y-6">
            <?= csrf_field() ?>

            <!-- Photo -->
            <div class="bg-surface border border-border rounded-xl p-5">
                <h3 class="text-white font-display font-semibold text-base mb-4">Photo de profil</h3>
                <div class="flex items-center gap-5">
                    <?php if (!empty($user->avatar_url)): ?>
                        <img src="<?= e($user->avatar_url) ?>" alt="Photo" class="w-20 h-20 rounded-full object-cover border-2 border-accent">
                    <?php else: ?>
                        <div class="w-20 h-20 rounded-full bg-accent flex items-center justify-center text-black text-2xl font-bold font-display"><?= e(mb_strtoupper(mb_substr($user->prenom, 0, 1))) ?></div>
                    <?php endif; ?>
                    <div>
                        <input type="file" name="photo" accept="image/jpeg,image/png,image/webp"
                               class="text-sm text-text-muted file:mr-3 file:py-2 file:px-4 file:rounded file:border-0 file:bg-surface-2 file:text-white file:font-medium file:cursor-pointer hover:file:bg-accent/20">
                        <p class="text-text-dim text-xs mt-1">JPEG / PNG / WebP, max 2 Mo</p>
                    </div>
                </div>
            </div>

            <!-- Infos -->
            <div class="bg-surface border border-border rounded-xl p-5">
                <h3 class="text-white font-display font-semibold text-base mb-4">Informations personnelles</h3>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs text-text-dim uppercase tracking-wider mb-1">Prénom *</label>
                        <input type="text" name="prenom" value="<?= e($user->prenom) ?>" required
                               class="w-full bg-surface-2 border border-border rounded px-4 py-2.5 text-sm text-white outline-none focus:border-accent">
                    </div>
                    <div>
                        <label class="block text-xs text-text-dim uppercase tracking-wider mb-1">Nom *</label>
                        <input type="text" name="nom" value="<?= e($user->nom) ?>" required
                               class="w-full bg-surface-2 border border-border rounded px-4 py-2.5 text-sm text-white outline-none focus:border-accent">
                    </div>
                    <div>
                        <label class="block text-xs text-text-dim uppercase tracking-wider mb-1">Email</label>
                        <input type="email" value="<?= e($user->email) ?>" disabled
                               class="w-full bg-surface-2 border border-border rounded px-4 py-2.5 text-sm text-text-dim cursor-not-allowed">
                        <p class="text-text-dim text-xs mt-1">Contacte le support pour changer ton email.</p>
                    </div>
                    <div>
                        <label class="block text-xs text-text-dim uppercase tracking-wider mb-1">Téléphone</label>
                        <input type="tel" name="telephone" value="<?= e($user->telephone ?? '') ?>" placeholder="+243..."
                               class="w-full bg-surface-2 border border-border rounded px-4 py-2.5 text-sm text-white outline-none focus:border-accent placeholder:text-text-dim">
                    </div>
                    <div>
                        <label class="block text-xs text-text-dim uppercase tracking-wider mb-1">Pays</label>
                        <select name="pays" class="w-full bg-surface-2 border border-border rounded px-4 py-2.5 text-sm text-white outline-none focus:border-accent">
                            <?php foreach (['CD'=>'Congo (RDC)','CA'=>'Canada','FR'=>'France','BE'=>'Belgique','SN'=>'Sénégal','CI'=>"Côte d'Ivoire",'CM'=>'Cameroun','BJ'=>'Bénin','TG'=>'Togo','BF'=>'Burkina Faso','ML'=>'Mali','NE'=>'Niger','GA'=>'Gabon','CG'=>'Congo Brazza','CH'=>'Suisse','MA'=>'Maroc'] as $code => $nom): ?>
                                <option value="<?= $code ?>" <?= ($user->pays ?? '') === $code ? 'selected' : '' ?>><?= e($nom) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs text-text-dim uppercase tracking-wider mb-1">Devise préférée</label>
                        <select name="devise_preferee" class="w-full bg-surface-2 border border-border rounded px-4 py-2.5 text-sm text-white outline-none focus:border-accent">
                            <?php foreach (['USD'=>'USD ($)','CDF'=>'CDF (Fc)','EUR'=>'EUR (€)','CAD'=>'CAD ($)','XOF'=>'XOF (CFA)'] as $code => $nom): ?>
                                <option value="<?= $code ?>" <?= ($user->devise_preferee ?? 'USD') === $code ? 'selected' : '' ?>><?= $nom ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Préférences -->
            <div class="bg-surface border border-border rounded-xl p-5">
                <h3 class="text-white font-display font-semibold text-base mb-4">Préférences</h3>
                <label class="flex items-center gap-3 cursor-pointer">
                    <input type="checkbox" name="accepte_newsletter" <?= $user->accepte_newsletter ? 'checked' : '' ?> class="accent-accent w-4 h-4">
                    <span class="text-text-muted text-sm">Recevoir la newsletter mensuelle</span>
                </label>
            </div>

            <div class="flex gap-3">
                <button type="submit" class="btn-primary">Enregistrer</button>
                <a href="/mon-compte" class="btn-secondary">Annuler</a>
            </div>
        </form>

        <!-- Mot de passe -->
        <form action="/mon-compte/password" method="POST" class="mt-10">
            <?= csrf_field() ?>
            <div class="bg-surface border border-border rounded-xl p-5">
                <h3 class="text-white font-display font-semibold text-base mb-4">Changer le mot de passe</h3>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs text-text-dim uppercase tracking-wider mb-1">Mot de passe actuel</label>
                        <input type="password" name="current_password" required
                               class="w-full bg-surface-2 border border-border rounded px-4 py-2.5 text-sm text-white outline-none focus:border-accent">
                    </div>
                    <div>
                        <label class="block text-xs text-text-dim uppercase tracking-wider mb-1">Nouveau mot de passe</label>
                        <input type="password" name="new_password" required minlength="8"
                               class="w-full bg-surface-2 border border-border rounded px-4 py-2.5 text-sm text-white outline-none focus:border-accent">
                        <p class="text-text-dim text-xs mt-1">Minimum 8 caractères</p>
                    </div>
                </div>
                <button type="submit" class="btn-secondary mt-4 text-sm">Changer le mot de passe</button>
            </div>
        </form>

        <!-- Zone de danger : suppression de compte (RGPD) -->
        <div class="mt-12 border-2 border-rose-500/40 rounded-xl p-5 sm:p-6 bg-rose-500/5">
            <h3 class="text-rose-400 font-display font-semibold text-base mb-2">Zone de danger</h3>
            <p class="text-text-muted text-sm mb-2">Supprimer mon compte. Cette action est <strong class="text-white">définitive</strong>. Tu perdras :</p>
            <ul class="text-text-muted text-sm space-y-1 mb-5 ml-5 list-disc">
                <li>Tes livres achetés</li>
                <li>Ta progression de lecture</li>
                <li>Tes avis et favoris</li>
                <li>Tes versements en attente (si tu es auteur)</li>
            </ul>
            <form action="/mon-compte/supprimer-demande" method="POST"
                  onsubmit="return confirm('Un email de confirmation va être envoyé à ton adresse. Continuer ?');">
                <?= csrf_field() ?>
                <button type="submit" class="bg-rose-600 hover:bg-rose-700 text-white text-sm font-semibold px-5 py-2.5 rounded transition-colors">
                    Demander la suppression de mon compte
                </button>
            </form>
        </div>

    </div>
</section>
