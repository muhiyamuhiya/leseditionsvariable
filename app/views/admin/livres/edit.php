<form method="POST" action="/admin/livres/<?= $book->id ?>/editer" enctype="multipart/form-data" class="max-w-3xl space-y-6">
    <?= csrf_field() ?>

    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div class="sm:col-span-2">
            <label class="block text-xs text-text-dim uppercase tracking-wider mb-1">Titre *</label>
            <input type="text" name="titre" value="<?= e($book->titre) ?>" required class="w-full bg-surface border border-border rounded px-4 py-2.5 text-sm text-white outline-none focus:border-accent">
        </div>
        <div>
            <label class="block text-xs text-text-dim uppercase tracking-wider mb-1">Slug</label>
            <input type="text" name="slug" value="<?= e($book->slug) ?>" class="w-full bg-surface border border-border rounded px-4 py-2.5 text-sm text-white outline-none focus:border-accent">
        </div>
        <div>
            <label class="block text-xs text-text-dim uppercase tracking-wider mb-1">Sous-titre</label>
            <input type="text" name="sous_titre" value="<?= e($book->sous_titre ?? '') ?>" class="w-full bg-surface border border-border rounded px-4 py-2.5 text-sm text-white outline-none focus:border-accent">
        </div>
        <div>
            <label class="block text-xs text-text-dim uppercase tracking-wider mb-1">Auteur *</label>
            <select name="author_id" required class="w-full bg-surface border border-border rounded px-4 py-2.5 text-sm text-white outline-none focus:border-accent">
                <?php foreach ($authors as $a): ?>
                    <option value="<?= $a->id ?>" <?= $book->author_id == $a->id ? 'selected' : '' ?>><?= e($a->name) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div>
            <label class="block text-xs text-text-dim uppercase tracking-wider mb-1">Catégorie</label>
            <select name="category_id" class="w-full bg-surface border border-border rounded px-4 py-2.5 text-sm text-white outline-none focus:border-accent">
                <option value="">Aucune</option>
                <?php foreach ($categories as $c): ?>
                    <option value="<?= $c->id ?>" <?= $book->category_id == $c->id ? 'selected' : '' ?>><?= e($c->nom) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
    </div>

    <div>
        <label class="block text-xs text-text-dim uppercase tracking-wider mb-1">Description courte</label>
        <textarea name="description_courte" rows="2" maxlength="500" class="w-full bg-surface border border-border rounded px-4 py-2.5 text-sm text-white outline-none focus:border-accent resize-none"><?= e($book->description_courte ?? '') ?></textarea>
    </div>
    <div>
        <label class="block text-xs text-text-dim uppercase tracking-wider mb-1">Description longue</label>
        <textarea name="description_longue" rows="6" class="w-full bg-surface border border-border rounded px-4 py-2.5 text-sm text-white outline-none focus:border-accent resize-none"><?= e($book->description_longue ?? '') ?></textarea>
    </div>
    <div>
        <label class="block text-xs text-text-dim uppercase tracking-wider mb-1">Mots-clés</label>
        <input type="text" name="mots_cles" value="<?= e($book->mots_cles ?? '') ?>" class="w-full bg-surface border border-border rounded px-4 py-2.5 text-sm text-white outline-none focus:border-accent">
    </div>

    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
        <div><label class="block text-xs text-text-dim uppercase tracking-wider mb-1">ISBN</label><input type="text" name="isbn" value="<?= e($book->isbn ?? '') ?>" class="w-full bg-surface border border-border rounded px-3 py-2 text-sm text-white outline-none focus:border-accent"></div>
        <div><label class="block text-xs text-text-dim uppercase tracking-wider mb-1">Langue</label><input type="text" name="langue" value="<?= e($book->langue ?? 'fr') ?>" class="w-full bg-surface border border-border rounded px-3 py-2 text-sm text-white outline-none focus:border-accent"></div>
        <div><label class="block text-xs text-text-dim uppercase tracking-wider mb-1">Pages</label><input type="number" name="nombre_pages" value="<?= $book->nombre_pages ?? '' ?>" class="w-full bg-surface border border-border rounded px-3 py-2 text-sm text-white outline-none focus:border-accent"></div>
        <div><label class="block text-xs text-text-dim uppercase tracking-wider mb-1">Année</label><input type="number" name="annee_publication" value="<?= $book->annee_publication ?? '' ?>" class="w-full bg-surface border border-border rounded px-3 py-2 text-sm text-white outline-none focus:border-accent"></div>
    </div>

    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
        <div><label class="block text-xs text-text-dim uppercase tracking-wider mb-1">Prix USD *</label><input type="number" step="0.01" name="prix_unitaire_usd" value="<?= $book->prix_unitaire_usd ?>" required class="w-full bg-surface border border-border rounded px-3 py-2 text-sm text-white outline-none focus:border-accent"></div>
        <div><label class="block text-xs text-text-dim uppercase tracking-wider mb-1">Prix CDF</label><input type="number" step="0.01" name="prix_unitaire_cdf" value="<?= $book->prix_unitaire_cdf ?? '' ?>" class="w-full bg-surface border border-border rounded px-3 py-2 text-sm text-white outline-none focus:border-accent"></div>
        <div><label class="block text-xs text-text-dim uppercase tracking-wider mb-1">Prix EUR</label><input type="number" step="0.01" name="prix_unitaire_eur" value="<?= $book->prix_unitaire_eur ?? '' ?>" class="w-full bg-surface border border-border rounded px-3 py-2 text-sm text-white outline-none focus:border-accent"></div>
        <div><label class="block text-xs text-text-dim uppercase tracking-wider mb-1">Prix CAD</label><input type="number" step="0.01" name="prix_unitaire_cad" value="<?= $book->prix_unitaire_cad ?? '' ?>" class="w-full bg-surface border border-border rounded px-3 py-2 text-sm text-white outline-none focus:border-accent"></div>
    </div>

    <!-- Couverture -->
    <div>
        <label class="block text-xs text-text-dim uppercase tracking-wider mb-2">Couverture du livre</label>
        <?php if (!empty($book->couverture_url_web)): ?>
            <div class="mb-3"><img src="<?= e($book->couverture_url_web) ?>" alt="Couverture" class="h-32 rounded"></div>
        <?php endif; ?>
        <input type="file" name="couverture" accept="image/jpeg,image/png,image/webp"
               class="text-sm text-text-muted file:mr-3 file:py-2 file:px-4 file:rounded file:border-0 file:bg-surface-2 file:text-white file:font-medium file:cursor-pointer hover:file:bg-accent/20">
        <p class="text-text-dim text-xs mt-1">Format : JPEG, PNG ou WebP. Max 2 Mo. Recommandé : 600x900px.</p>
    </div>

    <div>
        <label class="block text-xs text-text-dim uppercase tracking-wider mb-2">Niveau d'accès aux abonnés</label>
        <?php
            // 3 cas dérivés des deux booléens
            $accessMode = 'achat_only';
            if (!empty($book->accessible_abonnement_essentiel) && !empty($book->accessible_abonnement_premium)) {
                $accessMode = 'essentiel_premium';
            } elseif (empty($book->accessible_abonnement_essentiel) && !empty($book->accessible_abonnement_premium)) {
                $accessMode = 'premium_only';
            }
        ?>
        <div class="space-y-2">
            <label class="flex items-start gap-2 text-sm text-text-muted cursor-pointer">
                <input type="radio" name="access_mode" value="essentiel_premium" <?= $accessMode === 'essentiel_premium' ? 'checked' : '' ?> class="accent-accent mt-0.5">
                <span><strong class="text-white">Inclus dans Essentiel + Premium</strong> <span class="text-text-dim">— par défaut</span></span>
            </label>
            <label class="flex items-start gap-2 text-sm text-text-muted cursor-pointer">
                <input type="radio" name="access_mode" value="premium_only" <?= $accessMode === 'premium_only' ? 'checked' : '' ?> class="accent-accent mt-0.5">
                <span><strong class="text-white">Inclus dans Premium uniquement</strong> <span class="text-text-dim">— exclusivité Premium</span></span>
            </label>
            <label class="flex items-start gap-2 text-sm text-text-muted cursor-pointer">
                <input type="radio" name="access_mode" value="achat_only" <?= $accessMode === 'achat_only' ? 'checked' : '' ?> class="accent-accent mt-0.5">
                <span><strong class="text-white">Achat unitaire uniquement</strong> <span class="text-text-dim">— jamais inclus dans un abonnement</span></span>
            </label>
        </div>
        <input type="hidden" name="accessible_abonnement_essentiel" value="0">
        <input type="hidden" name="accessible_abonnement_premium" value="0">
        <script>
        // Synchroniser les hidden inputs depuis le radio choisi
        document.querySelectorAll('input[name="access_mode"]').forEach(r => r.addEventListener('change', function() {
            const ess = document.querySelector('input[name="accessible_abonnement_essentiel"]');
            const prem = document.querySelector('input[name="accessible_abonnement_premium"]');
            ess.value = this.value === 'essentiel_premium' ? 1 : 0;
            prem.value = (this.value === 'essentiel_premium' || this.value === 'premium_only') ? 1 : 0;
        }));
        // Init au chargement
        (function init() {
            const checked = document.querySelector('input[name="access_mode"]:checked');
            if (checked) checked.dispatchEvent(new Event('change'));
        })();
        </script>
    </div>

    <div class="flex flex-wrap gap-6">
        <label class="flex items-center gap-2 text-sm text-text-muted cursor-pointer"><input type="checkbox" name="mis_en_avant" <?= $book->mis_en_avant ? 'checked' : '' ?> class="accent-accent"> Mis en avant</label>
        <label class="flex items-center gap-2 text-sm text-text-muted cursor-pointer"><input type="checkbox" name="nouveaute" <?= $book->nouveaute ? 'checked' : '' ?> class="accent-accent"> Nouveauté</label>
    </div>

    <div>
        <label class="block text-xs text-text-dim uppercase tracking-wider mb-2">Statut</label>
        <div class="flex flex-wrap gap-4">
            <?php foreach (['brouillon','en_revue','publie','retire'] as $st): ?>
                <label class="flex items-center gap-2 text-sm text-text-muted cursor-pointer">
                    <input type="radio" name="statut" value="<?= $st ?>" <?= $book->statut === $st ? 'checked' : '' ?> class="accent-accent">
                    <?= ucfirst(str_replace('_',' ',$st)) ?>
                </label>
            <?php endforeach; ?>
        </div>
    </div>

    <div class="flex gap-3 pt-4">
        <button type="submit" class="btn-primary">Enregistrer</button>
        <a href="/admin/livres" class="btn-secondary">Annuler</a>
    </div>
</form>
