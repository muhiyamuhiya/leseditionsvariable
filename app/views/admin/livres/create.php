<form method="POST" action="/admin/livres/nouveau" enctype="multipart/form-data" class="max-w-3xl space-y-6">
    <?= csrf_field() ?>

    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div class="sm:col-span-2">
            <label class="block text-xs text-text-dim uppercase tracking-wider mb-1">Titre *</label>
            <input type="text" name="titre" required class="w-full bg-surface border border-border rounded px-4 py-2.5 text-sm text-white outline-none focus:border-accent">
        </div>
        <div>
            <label class="block text-xs text-text-dim uppercase tracking-wider mb-1">Slug (auto si vide)</label>
            <input type="text" name="slug" class="w-full bg-surface border border-border rounded px-4 py-2.5 text-sm text-white outline-none focus:border-accent">
        </div>
        <div>
            <label class="block text-xs text-text-dim uppercase tracking-wider mb-1">Sous-titre</label>
            <input type="text" name="sous_titre" class="w-full bg-surface border border-border rounded px-4 py-2.5 text-sm text-white outline-none focus:border-accent">
        </div>
        <div>
            <label class="block text-xs text-text-dim uppercase tracking-wider mb-1">Auteur *</label>
            <select name="author_id" required class="w-full bg-surface border border-border rounded px-4 py-2.5 text-sm text-white outline-none focus:border-accent">
                <option value="">Choisir...</option>
                <?php foreach ($authors as $a): ?><option value="<?= $a->id ?>"><?= e($a->name) ?></option><?php endforeach; ?>
            </select>
        </div>
        <div>
            <label class="block text-xs text-text-dim uppercase tracking-wider mb-1">Catégorie</label>
            <select name="category_id" class="w-full bg-surface border border-border rounded px-4 py-2.5 text-sm text-white outline-none focus:border-accent">
                <option value="">Aucune</option>
                <?php foreach ($categories as $c): ?><option value="<?= $c->id ?>"><?= e($c->nom) ?></option><?php endforeach; ?>
            </select>
        </div>
    </div>
    <div>
        <label class="block text-xs text-text-dim uppercase tracking-wider mb-1">Description courte</label>
        <textarea name="description_courte" rows="2" maxlength="500" class="w-full bg-surface border border-border rounded px-4 py-2.5 text-sm text-white outline-none focus:border-accent resize-none"></textarea>
    </div>
    <div>
        <label class="block text-xs text-text-dim uppercase tracking-wider mb-1">Description longue</label>
        <textarea name="description_longue" rows="6" class="w-full bg-surface border border-border rounded px-4 py-2.5 text-sm text-white outline-none focus:border-accent resize-none"></textarea>
    </div>
    <div class="grid grid-cols-2 gap-4">
        <div><label class="block text-xs text-text-dim uppercase tracking-wider mb-1">Prix USD *</label><input type="number" step="0.01" name="prix_unitaire_usd" value="9.99" required class="w-full bg-surface border border-border rounded px-3 py-2 text-sm text-white outline-none focus:border-accent"></div>
        <div>
            <label class="block text-xs text-text-dim uppercase tracking-wider mb-1">Statut</label>
            <select name="statut" class="w-full bg-surface border border-border rounded px-3 py-2 text-sm text-white outline-none focus:border-accent">
                <option value="brouillon">Brouillon</option><option value="en_revue">En revue</option><option value="publie">Publié</option>
            </select>
        </div>
    </div>

    <!-- Couverture -->
    <div>
        <label class="block text-xs text-text-dim uppercase tracking-wider mb-2">Couverture du livre</label>
        <input type="file" name="couverture" accept="image/jpeg,image/png,image/webp"
               class="text-sm text-text-muted file:mr-3 file:py-2 file:px-4 file:rounded file:border-0 file:bg-surface-2 file:text-white file:font-medium file:cursor-pointer hover:file:bg-accent/20">
        <p class="text-text-dim text-xs mt-1">Format : JPEG, PNG ou WebP. Max 2 Mo. Recommandé : 600x900px.</p>
    </div>

    <div class="flex flex-wrap gap-6">
        <label class="flex items-center gap-2 text-sm text-text-muted cursor-pointer"><input type="checkbox" name="accessible_abonnement_essentiel" value="1" checked class="accent-accent"> Accessible Essentiel</label>
        <label class="flex items-center gap-2 text-sm text-text-muted cursor-pointer"><input type="checkbox" name="accessible_abonnement_premium" value="1" checked class="accent-accent"> Accessible Premium</label>
    </div>
    <div class="flex gap-3 pt-4">
        <button type="submit" class="btn-primary">Créer le livre</button>
        <a href="/admin/livres" class="btn-secondary">Annuler</a>
    </div>
</form>
