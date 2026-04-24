<section class="py-10 sm:py-16">
    <div class="max-w-2xl mx-auto px-4 sm:px-6">
        <h1 class="font-display font-extrabold text-3xl text-white mb-2">Devenir auteur</h1>
        <p class="text-text-muted mb-8">Remplis ce formulaire pour soumettre ta candidature. Nous l'examinons sous 5 jours ouvrés.</p>

        <?php if ($author && $author->statut_validation === 'en_attente'): ?>
            <div class="bg-accent/10 border border-accent/30 text-accent px-4 py-3 rounded-lg mb-6 text-sm">Ta candidature est en cours d'examen. Tu peux la mettre à jour ci-dessous.</div>
        <?php endif; ?>

        <form method="POST" action="/auteur/candidater" enctype="multipart/form-data" class="space-y-5">
            <?= csrf_field() ?>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div><label class="block text-xs text-text-dim uppercase tracking-wider mb-1">Nom de plume (optionnel)</label><input type="text" name="nom_plume" value="<?= e($author->nom_plume ?? '') ?>" class="w-full bg-surface border border-border rounded px-4 py-2.5 text-sm text-white outline-none focus:border-accent placeholder:text-text-dim" placeholder="Si tu publies sous un pseudonyme"></div>
                <div><label class="block text-xs text-text-dim uppercase tracking-wider mb-1">Slug URL</label><input type="text" name="slug" value="<?= e($author->slug ?? '') ?>" class="w-full bg-surface border border-border rounded px-4 py-2.5 text-sm text-white outline-none focus:border-accent placeholder:text-text-dim" placeholder="auto-généré si vide"></div>
            </div>
            <div><label class="block text-xs text-text-dim uppercase tracking-wider mb-1">Biographie courte *</label><textarea name="biographie_courte" rows="2" maxlength="250" required class="w-full bg-surface border border-border rounded px-4 py-2.5 text-sm text-white outline-none focus:border-accent resize-none"><?= e($author->biographie_courte ?? '') ?></textarea><p class="text-text-dim text-xs mt-1">250 caractères max</p></div>
            <div><label class="block text-xs text-text-dim uppercase tracking-wider mb-1">Biographie longue</label><textarea name="biographie_longue" rows="5" maxlength="2000" class="w-full bg-surface border border-border rounded px-4 py-2.5 text-sm text-white outline-none focus:border-accent resize-none"><?= e($author->biographie_longue ?? '') ?></textarea></div>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div><label class="block text-xs text-text-dim uppercase tracking-wider mb-1">Pays d'origine</label>
                    <select name="pays_origine" class="w-full bg-surface border border-border rounded px-4 py-2.5 text-sm text-white outline-none focus:border-accent">
                        <option value="">Choisir...</option>
                        <?php foreach (['CD'=>'RD Congo','SN'=>'Sénégal','CI'=>"Côte d'Ivoire",'CM'=>'Cameroun','ML'=>'Mali','BF'=>'Burkina Faso','GN'=>'Guinée','BJ'=>'Bénin','TG'=>'Togo','NE'=>'Niger','GA'=>'Gabon','CG'=>'Congo-Brazza','TD'=>'Tchad','MG'=>'Madagascar','FR'=>'France','BE'=>'Belgique','CA'=>'Canada','CH'=>'Suisse'] as $code=>$nom): ?>
                            <option value="<?= $code ?>" <?= ($author->pays_origine ?? '') === $code ? 'selected' : '' ?>><?= $nom ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div><label class="block text-xs text-text-dim uppercase tracking-wider mb-1">Ville de résidence</label><input type="text" name="ville_residence" value="<?= e($author->ville_residence ?? '') ?>" class="w-full bg-surface border border-border rounded px-4 py-2.5 text-sm text-white outline-none focus:border-accent"></div>
            </div>
            <div><label class="block text-xs text-text-dim uppercase tracking-wider mb-2">Photo (recommandée)</label><input type="file" name="photo" accept="image/jpeg,image/png,image/webp" class="text-sm text-text-muted file:mr-3 file:py-2 file:px-4 file:rounded file:border-0 file:bg-surface-2 file:text-white file:font-medium file:cursor-pointer hover:file:bg-accent/20"></div>
            <p class="text-text-dim text-[10px] uppercase tracking-wider pt-2">Réseaux sociaux (optionnels)</p>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <input type="url" name="site_web" value="<?= e($author->site_web ?? '') ?>" placeholder="Site web" class="bg-surface border border-border rounded px-3 py-2 text-sm text-white outline-none focus:border-accent placeholder:text-text-dim">
                <input type="url" name="facebook_url" value="<?= e($author->facebook_url ?? '') ?>" placeholder="Facebook" class="bg-surface border border-border rounded px-3 py-2 text-sm text-white outline-none focus:border-accent placeholder:text-text-dim">
                <input type="url" name="instagram_url" value="<?= e($author->instagram_url ?? '') ?>" placeholder="Instagram" class="bg-surface border border-border rounded px-3 py-2 text-sm text-white outline-none focus:border-accent placeholder:text-text-dim">
                <input type="url" name="twitter_x_url" value="<?= e($author->twitter_x_url ?? '') ?>" placeholder="X / Twitter" class="bg-surface border border-border rounded px-3 py-2 text-sm text-white outline-none focus:border-accent placeholder:text-text-dim">
            </div>
            <div><label class="block text-xs text-text-dim uppercase tracking-wider mb-2">Méthode de versement préférée</label>
                <div class="flex flex-wrap gap-4">
                    <?php foreach (['mobile_money'=>'Mobile Money','paypal'=>'PayPal','banque'=>'Virement bancaire','stripe'=>'Stripe'] as $v=>$l): ?>
                        <label class="flex items-center gap-2 text-sm text-text-muted cursor-pointer"><input type="radio" name="methode_versement" value="<?= $v ?>" <?= ($author->methode_versement ?? 'mobile_money') === $v ? 'checked' : '' ?> class="accent-accent"> <?= $l ?></label>
                    <?php endforeach; ?>
                </div>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <input type="text" name="numero_mobile_money" value="<?= e($author->numero_mobile_money ?? '') ?>" placeholder="N° Mobile Money" class="bg-surface border border-border rounded px-3 py-2 text-sm text-white outline-none focus:border-accent placeholder:text-text-dim">
                <input type="email" name="email_paypal" value="<?= e($author->email_paypal ?? '') ?>" placeholder="Email PayPal" class="bg-surface border border-border rounded px-3 py-2 text-sm text-white outline-none focus:border-accent placeholder:text-text-dim">
            </div>
            <label class="flex items-start gap-3 cursor-pointer pt-2"><input type="checkbox" required class="mt-0.5 accent-accent"><span class="text-sm text-text-muted">J'accepte les <a href="/cgu" target="_blank" class="text-accent underline">conditions d'utilisation</a> pour les auteurs *</span></label>
            <button type="submit" class="btn-primary w-full sm:w-auto text-base py-3 px-8">Soumettre ma candidature</button>
        </form>
    </div>
</section>
