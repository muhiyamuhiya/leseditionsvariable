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
            <label class="block text-xs text-text-dim uppercase tracking-wider mb-1 flex items-center justify-between">
                <span>Auteur du livre *</span>
                <button type="button" id="btn-open-author-modal" class="text-accent hover:underline text-xs normal-case tracking-normal">+ Créer un nouvel auteur</button>
            </label>
            <select name="author_id" id="author-select" required class="w-full bg-surface border border-border rounded px-4 py-2.5 text-sm text-white outline-none focus:border-accent">
                <option value="">Choisir...</option>
                <?php foreach ($authors as $a): ?>
                    <option value="<?= $a->id ?>"><?= e($a->name) ?><?= !empty($a->is_classic) ? ' — classique' : '' ?></option>
                <?php endforeach; ?>
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
        <input type="file" name="couverture" accept="image/jpeg,image/png,image/webp,image/heic,image/heif"
               class="text-sm text-text-muted file:mr-3 file:py-2 file:px-4 file:rounded file:border-0 file:bg-surface-2 file:text-white file:font-medium file:cursor-pointer hover:file:bg-accent/20">
        <p class="text-text-dim text-xs mt-1">Format : JPEG, PNG, WebP ou HEIC. Max 5 Mo. Recommandé : 600x900px.</p>
    </div>

    <!-- Manuscrit PDF -->
    <div>
        <label class="block text-xs text-text-dim uppercase tracking-wider mb-2">Manuscrit PDF *</label>
        <input type="file" name="manuscrit" accept="application/pdf" required
               class="text-sm text-text-muted file:mr-3 file:py-2 file:px-4 file:rounded file:border-0 file:bg-surface-2 file:text-white file:font-medium file:cursor-pointer hover:file:bg-accent/20">
        <p class="text-text-dim text-xs mt-1">PDF uniquement, max 50 Mo. Un extrait de 10 pages sera généré automatiquement.</p>
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

<!-- Modal création nouvel auteur -->
<div id="author-modal" class="fixed inset-0 bg-black/70 z-50 hidden items-center justify-center p-4">
    <div class="bg-surface border border-border rounded-xl max-w-md w-full max-h-[90vh] overflow-auto">
        <div class="px-5 py-4 border-b border-border flex items-center justify-between">
            <h3 class="text-white font-display text-lg font-semibold">Nouvel auteur</h3>
            <button type="button" id="btn-close-author-modal" class="text-text-dim hover:text-white text-xl leading-none">&times;</button>
        </div>
        <form id="author-form" enctype="multipart/form-data" class="p-5 space-y-4">
            <input type="hidden" name="_csrf_token" value="<?= e(csrf_token()) ?>">
            <div>
                <label class="block text-xs text-text-dim uppercase tracking-wider mb-1">Nom de plume *</label>
                <input type="text" name="nom_plume" required class="w-full bg-surface-2 border border-border rounded px-3 py-2 text-sm text-white outline-none focus:border-accent" placeholder="Ex: Émile Zola">
            </div>
            <div>
                <label class="block text-xs text-text-dim uppercase tracking-wider mb-1">Biographie courte</label>
                <textarea name="biographie_courte" rows="3" maxlength="500" class="w-full bg-surface-2 border border-border rounded px-3 py-2 text-sm text-white outline-none focus:border-accent resize-none"></textarea>
            </div>
            <div>
                <label class="block text-xs text-text-dim uppercase tracking-wider mb-1">Pays d'origine (code ISO 2 lettres)</label>
                <input type="text" name="pays_origine" maxlength="2" class="w-full bg-surface-2 border border-border rounded px-3 py-2 text-sm text-white outline-none focus:border-accent uppercase" placeholder="FR">
            </div>
            <div>
                <label class="block text-xs text-text-dim uppercase tracking-wider mb-1">Photo</label>
                <input type="file" name="photo_auteur" accept="image/jpeg,image/png,image/webp" class="text-sm text-text-muted file:mr-2 file:py-1.5 file:px-3 file:rounded file:border-0 file:bg-surface file:text-white file:cursor-pointer file:text-xs">
                <p class="text-text-dim text-xs mt-1">JPEG/PNG/WebP, max 2 Mo.</p>
            </div>
            <label class="flex items-center gap-2 text-sm text-text-muted cursor-pointer">
                <input type="checkbox" name="is_classic" value="1" class="accent-accent">
                <span>Auteur classique (Zola, Hugo... pas de compte user)</span>
            </label>
            <p id="author-form-error" class="text-red-400 text-xs hidden"></p>
            <div class="flex gap-2 pt-2">
                <button type="submit" class="btn-primary text-sm" id="btn-submit-author">Créer l'auteur</button>
                <button type="button" id="btn-cancel-author-modal" class="btn-secondary text-sm">Annuler</button>
            </div>
        </form>
    </div>
</div>

<script>
(function () {
    const modal     = document.getElementById('author-modal');
    const openBtn   = document.getElementById('btn-open-author-modal');
    const closeBtn  = document.getElementById('btn-close-author-modal');
    const cancelBtn = document.getElementById('btn-cancel-author-modal');
    const form      = document.getElementById('author-form');
    const errEl     = document.getElementById('author-form-error');
    const submitBtn = document.getElementById('btn-submit-author');
    const select    = document.getElementById('author-select');

    function show() { modal.classList.remove('hidden'); modal.classList.add('flex'); }
    function hide() { modal.classList.add('hidden'); modal.classList.remove('flex'); errEl.classList.add('hidden'); errEl.textContent = ''; form.reset(); }

    openBtn.addEventListener('click', show);
    closeBtn.addEventListener('click', hide);
    cancelBtn.addEventListener('click', hide);
    modal.addEventListener('click', (e) => { if (e.target === modal) hide(); });

    form.addEventListener('submit', async (e) => {
        e.preventDefault();
        errEl.classList.add('hidden');
        submitBtn.disabled = true;
        submitBtn.textContent = 'Envoi...';

        try {
            const fd = new FormData(form);
            const res = await fetch('/admin/auteurs/ajax-create', {
                method: 'POST',
                body: fd,
                headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' },
                credentials: 'same-origin',
            });

            // Lire le texte d'abord pour pouvoir afficher un message clair même
            // si la réponse n'est pas du JSON (ex: page d'erreur HTML serveur)
            const text = await res.text();
            let data;
            try {
                data = JSON.parse(text);
            } catch {
                throw new Error('Réponse invalide du serveur (HTTP ' + res.status + '). Détail : ' + text.substring(0, 150));
            }

            if (!res.ok || data.success === false) {
                errEl.textContent = data.error || ('Erreur serveur (HTTP ' + res.status + ').');
                errEl.classList.remove('hidden');
                return;
            }

            // Injection du nouvel auteur dans le dropdown et sélection
            const opt = document.createElement('option');
            opt.value = data.id;
            opt.textContent = data.name + (data.is_classic ? ' — classique' : '');
            opt.selected = true;
            select.appendChild(opt);
            hide();
        } catch (err) {
            errEl.textContent = err.message || 'Erreur réseau.';
            errEl.classList.remove('hidden');
        } finally {
            submitBtn.disabled = false;
            submitBtn.textContent = "Créer l'auteur";
        }
    });
})();
</script>
