<?php $s = flash('admin_success'); if ($s): ?><div class="bg-emerald-500/10 border border-emerald-500/30 text-emerald-400 px-4 py-3 rounded-lg mb-6 text-sm"><?= e($s) ?></div><?php endif; ?>

<div class="flex items-center gap-3 mb-6">
    <?php $pUrl = author_photo_url($author); ?>
    <?php if ($pUrl): ?>
        <img src="<?= e($pUrl) ?>" class="w-14 h-14 rounded-full object-cover border-2 border-accent">
    <?php else: ?>
        <div class="w-14 h-14 rounded-full bg-gradient-to-br from-accent to-amber-700 flex items-center justify-center text-xl font-display font-bold text-black"><?= e(author_initials($author)) ?></div>
    <?php endif; ?>
    <div>
        <p class="text-white font-medium"><?= e($author->prenom . ' ' . $author->nom) ?></p>
        <p class="text-text-dim text-xs"><?= e($author->email) ?></p>
    </div>
    <a href="/auteur/<?= e($author->slug) ?>" target="_blank" class="ml-auto text-accent text-xs hover:text-accent-hover transition-colors">Voir le profil public &rarr;</a>
</div>

<form method="POST" action="/admin/auteurs/<?= $author->id ?>/editer" enctype="multipart/form-data" class="max-w-3xl space-y-6">
    <?= csrf_field() ?>

    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div>
            <label class="block text-xs text-text-dim uppercase tracking-wider mb-1">Nom de plume</label>
            <input type="text" name="nom_plume" value="<?= e($author->nom_plume ?? '') ?>" class="w-full bg-surface border border-border rounded px-4 py-2.5 text-sm text-white outline-none focus:border-accent">
        </div>
        <div>
            <label class="block text-xs text-text-dim uppercase tracking-wider mb-1">Pays d'origine</label>
            <input type="text" name="pays_origine" value="<?= e($author->pays_origine ?? '') ?>" maxlength="2" class="w-full bg-surface border border-border rounded px-4 py-2.5 text-sm text-white outline-none focus:border-accent">
        </div>
        <div>
            <label class="block text-xs text-text-dim uppercase tracking-wider mb-1">Ville de résidence</label>
            <input type="text" name="ville_residence" value="<?= e($author->ville_residence ?? '') ?>" class="w-full bg-surface border border-border rounded px-4 py-2.5 text-sm text-white outline-none focus:border-accent">
        </div>
        <div>
            <label class="block text-xs text-text-dim uppercase tracking-wider mb-1">Méthode de versement</label>
            <select name="methode_versement" class="w-full bg-surface border border-border rounded px-4 py-2.5 text-sm text-white outline-none focus:border-accent">
                <?php foreach (['mobile_money'=>'Mobile Money','banque'=>'Virement bancaire','paypal'=>'PayPal','stripe'=>'Stripe'] as $v=>$l): ?>
                    <option value="<?= $v ?>" <?= ($author->methode_versement ?? '') === $v ? 'selected' : '' ?>><?= $l ?></option>
                <?php endforeach; ?>
            </select>
        </div>
    </div>

    <div>
        <label class="block text-xs text-text-dim uppercase tracking-wider mb-1">Biographie courte</label>
        <textarea name="biographie_courte" rows="3" class="w-full bg-surface border border-border rounded px-4 py-2.5 text-sm text-white outline-none focus:border-accent resize-none"><?= e($author->biographie_courte ?? '') ?></textarea>
    </div>
    <div>
        <label class="block text-xs text-text-dim uppercase tracking-wider mb-1">Biographie longue</label>
        <textarea name="biographie_longue" rows="6" class="w-full bg-surface border border-border rounded px-4 py-2.5 text-sm text-white outline-none focus:border-accent resize-none"><?= e($author->biographie_longue ?? '') ?></textarea>
    </div>

    <!-- Photo -->
    <div>
        <label class="block text-xs text-text-dim uppercase tracking-wider mb-2">Photo de l'auteur</label>
        <?php if ($pUrl): ?>
            <div class="mb-3"><img src="<?= e($pUrl) ?>" class="w-24 h-24 rounded-full object-cover border border-border"></div>
        <?php endif; ?>
        <input type="file" name="photo" accept="image/jpeg,image/png,image/webp"
               class="text-sm text-text-muted file:mr-3 file:py-2 file:px-4 file:rounded file:border-0 file:bg-surface-2 file:text-white file:font-medium file:cursor-pointer hover:file:bg-accent/20">
        <p class="text-text-dim text-xs mt-1">Carré recommandé (500x500px), max 2 Mo.</p>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div><label class="block text-xs text-text-dim uppercase tracking-wider mb-1">N° Mobile Money</label><input type="text" name="numero_mobile_money" value="<?= e($author->numero_mobile_money ?? '') ?>" class="w-full bg-surface border border-border rounded px-4 py-2.5 text-sm text-white outline-none focus:border-accent"></div>
        <div><label class="block text-xs text-text-dim uppercase tracking-wider mb-1">Email PayPal</label><input type="email" name="email_paypal" value="<?= e($author->email_paypal ?? '') ?>" class="w-full bg-surface border border-border rounded px-4 py-2.5 text-sm text-white outline-none focus:border-accent"></div>
    </div>

    <p class="text-text-dim text-[10px] uppercase tracking-wider pt-2">Réseaux sociaux</p>
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div><label class="block text-xs text-text-dim mb-1">Site web</label><input type="url" name="site_web" value="<?= e($author->site_web ?? '') ?>" class="w-full bg-surface border border-border rounded px-3 py-2 text-sm text-white outline-none focus:border-accent"></div>
        <div><label class="block text-xs text-text-dim mb-1">Facebook</label><input type="url" name="facebook_url" value="<?= e($author->facebook_url ?? '') ?>" class="w-full bg-surface border border-border rounded px-3 py-2 text-sm text-white outline-none focus:border-accent"></div>
        <div><label class="block text-xs text-text-dim mb-1">Instagram</label><input type="url" name="instagram_url" value="<?= e($author->instagram_url ?? '') ?>" class="w-full bg-surface border border-border rounded px-3 py-2 text-sm text-white outline-none focus:border-accent"></div>
        <div><label class="block text-xs text-text-dim mb-1">X / Twitter</label><input type="url" name="twitter_x_url" value="<?= e($author->twitter_x_url ?? '') ?>" class="w-full bg-surface border border-border rounded px-3 py-2 text-sm text-white outline-none focus:border-accent"></div>
        <div><label class="block text-xs text-text-dim mb-1">LinkedIn</label><input type="url" name="linkedin_url" value="<?= e($author->linkedin_url ?? '') ?>" class="w-full bg-surface border border-border rounded px-3 py-2 text-sm text-white outline-none focus:border-accent"></div>
    </div>

    <div class="flex gap-3 pt-4">
        <button type="submit" class="btn-primary">Enregistrer</button>
        <a href="/admin/auteurs" class="btn-secondary">Annuler</a>
    </div>
</form>
