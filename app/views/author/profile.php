<?php $s = flash('author_success'); if ($s): ?><div class="bg-emerald-500/10 border border-emerald-500/30 text-emerald-400 px-4 py-3 rounded-lg mb-6 text-sm"><?= e($s) ?></div><?php endif; ?>

<div class="flex items-center gap-3 mb-6">
    <a href="/auteur/<?= e($author->slug) ?>" target="_blank" class="text-accent text-xs hover:text-accent-hover transition-colors">Voir mon profil public &rarr;</a>
</div>

<form method="POST" action="/auteur/profil" enctype="multipart/form-data" class="max-w-3xl space-y-6">
    <?= csrf_field() ?>
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div><label class="block text-xs text-text-dim uppercase tracking-wider mb-1">Nom de plume</label><input type="text" name="nom_plume" value="<?= e($author->nom_plume ?? '') ?>" class="w-full bg-surface border border-border rounded px-4 py-2.5 text-sm text-white outline-none focus:border-accent"></div>
        <div><label class="block text-xs text-text-dim uppercase tracking-wider mb-1">Pays d'origine</label><input type="text" name="pays_origine" value="<?= e($author->pays_origine ?? '') ?>" maxlength="2" class="w-full bg-surface border border-border rounded px-4 py-2.5 text-sm text-white outline-none focus:border-accent"></div>
        <div><label class="block text-xs text-text-dim uppercase tracking-wider mb-1">Ville</label><input type="text" name="ville_residence" value="<?= e($author->ville_residence ?? '') ?>" class="w-full bg-surface border border-border rounded px-4 py-2.5 text-sm text-white outline-none focus:border-accent"></div>
        <div><label class="block text-xs text-text-dim uppercase tracking-wider mb-1">Méthode versement</label><select name="methode_versement" class="w-full bg-surface border border-border rounded px-4 py-2.5 text-sm text-white outline-none focus:border-accent"><?php foreach (['mobile_money'=>'Mobile Money','paypal'=>'PayPal','banque'=>'Virement','stripe'=>'Stripe'] as $v=>$l): ?><option value="<?= $v ?>" <?= ($author->methode_versement ?? '') === $v ? 'selected' : '' ?>><?= $l ?></option><?php endforeach; ?></select></div>
    </div>
    <div><label class="block text-xs text-text-dim uppercase tracking-wider mb-1">Biographie courte</label><textarea name="biographie_courte" rows="3" maxlength="250" class="w-full bg-surface border border-border rounded px-4 py-2.5 text-sm text-white outline-none focus:border-accent resize-none"><?= e($author->biographie_courte ?? '') ?></textarea></div>
    <div><label class="block text-xs text-text-dim uppercase tracking-wider mb-1">Biographie longue</label><textarea name="biographie_longue" rows="6" class="w-full bg-surface border border-border rounded px-4 py-2.5 text-sm text-white outline-none focus:border-accent resize-none"><?= e($author->biographie_longue ?? '') ?></textarea></div>
    <div><label class="block text-xs text-text-dim uppercase tracking-wider mb-2">Photo</label>
        <?php $pUrl = author_photo_url($author); if ($pUrl): ?><div class="mb-3"><img src="<?= e($pUrl) ?>" class="w-20 h-20 rounded-full object-cover border border-border"></div><?php endif; ?>
        <input type="file" name="photo" accept="image/jpeg,image/png,image/webp" class="text-sm text-text-muted file:mr-3 file:py-2 file:px-4 file:rounded file:border-0 file:bg-surface-2 file:text-white file:font-medium file:cursor-pointer hover:file:bg-accent/20">
    </div>
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
        <input type="text" name="numero_mobile_money" value="<?= e($author->numero_mobile_money ?? '') ?>" placeholder="N° Mobile Money" class="bg-surface border border-border rounded px-3 py-2 text-sm text-white outline-none focus:border-accent placeholder:text-text-dim">
        <input type="email" name="email_paypal" value="<?= e($author->email_paypal ?? '') ?>" placeholder="Email PayPal" class="bg-surface border border-border rounded px-3 py-2 text-sm text-white outline-none focus:border-accent placeholder:text-text-dim">
    </div>
    <p class="text-text-dim text-[10px] uppercase tracking-wider">Réseaux sociaux</p>
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
        <input type="url" name="site_web" value="<?= e($author->site_web ?? '') ?>" placeholder="Site web" class="bg-surface border border-border rounded px-3 py-2 text-sm text-white outline-none focus:border-accent placeholder:text-text-dim">
        <input type="url" name="facebook_url" value="<?= e($author->facebook_url ?? '') ?>" placeholder="Facebook" class="bg-surface border border-border rounded px-3 py-2 text-sm text-white outline-none focus:border-accent placeholder:text-text-dim">
        <input type="url" name="instagram_url" value="<?= e($author->instagram_url ?? '') ?>" placeholder="Instagram" class="bg-surface border border-border rounded px-3 py-2 text-sm text-white outline-none focus:border-accent placeholder:text-text-dim">
        <input type="url" name="twitter_x_url" value="<?= e($author->twitter_x_url ?? '') ?>" placeholder="X / Twitter" class="bg-surface border border-border rounded px-3 py-2 text-sm text-white outline-none focus:border-accent placeholder:text-text-dim">
    </div>
    <div class="flex gap-3 pt-4"><button type="submit" class="btn-primary">Enregistrer</button></div>
</form>
