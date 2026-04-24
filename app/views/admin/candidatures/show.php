<?php $s = flash('admin_success'); if ($s): ?><div class="bg-emerald-500/10 border border-emerald-500/30 text-emerald-400 px-4 py-3 rounded-lg mb-6 text-sm"><?= e($s) ?></div><?php endif; ?>

<a href="/admin/candidatures" class="text-text-dim text-xs hover:text-accent transition-colors mb-4 inline-block">&larr; Retour aux candidatures</a>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <!-- Colonne gauche : photo + infos de base -->
    <div class="lg:col-span-1">
        <div class="bg-surface border border-border rounded-xl p-5 text-center">
            <?php $pUrl = author_photo_url($author); ?>
            <?php if ($pUrl): ?>
                <img src="<?= e($pUrl) ?>" class="w-32 h-32 rounded-full object-cover mx-auto border-2 border-accent mb-4">
            <?php else: ?>
                <div class="w-32 h-32 rounded-full bg-gradient-to-br from-accent to-amber-700 flex items-center justify-center text-4xl font-display font-bold text-black mx-auto mb-4"><?= e(author_initials($author)) ?></div>
            <?php endif; ?>
            <h2 class="font-display font-bold text-xl text-white"><?= e($author->prenom . ' ' . $author->nom) ?></h2>
            <?php if ($author->nom_plume): ?><p class="text-text-muted text-sm mt-1">Nom de plume : <?= e($author->nom_plume) ?></p><?php endif; ?>
            <p class="text-text-dim text-xs mt-2"><?= e($author->email) ?></p>
            <?php if ($author->telephone): ?><p class="text-text-dim text-xs"><?= e($author->telephone) ?></p><?php endif; ?>
            <p class="text-text-dim text-xs mt-2"><?= e(implode(' · ', array_filter([$author->ville_residence ?? '', $author->pays_origine ?? '']))) ?></p>
            <p class="text-text-dim text-xs mt-2">Inscrit le <?= date('d/m/Y', strtotime($author->user_created_at)) ?></p>
            <p class="text-text-dim text-xs">Candidature le <?= date('d/m/Y', strtotime($author->created_at)) ?></p>

            <?php $vc = ['valide'=>'bg-emerald-500/20 text-emerald-400','en_attente'=>'bg-accent/20 text-accent','refuse'=>'bg-red-500/20 text-red-400','suspendu'=>'bg-text-dim/20 text-text-dim']; ?>
            <div class="mt-4">
                <span class="text-xs font-medium px-3 py-1 rounded-full <?= $vc[$author->statut_validation] ?? '' ?>"><?= ucfirst(str_replace('_',' ',$author->statut_validation)) ?></span>
            </div>
        </div>
    </div>

    <!-- Colonne droite : détails -->
    <div class="lg:col-span-2 space-y-6">
        <div class="bg-surface border border-border rounded-xl p-5">
            <h3 class="text-white font-semibold text-sm mb-3">Biographie courte</h3>
            <p class="text-text-muted text-sm leading-relaxed"><?= e($author->biographie_courte ?? 'Non renseignée') ?></p>
        </div>

        <?php if ($author->biographie_longue): ?>
        <div class="bg-surface border border-border rounded-xl p-5">
            <h3 class="text-white font-semibold text-sm mb-3">Biographie longue</h3>
            <div class="text-text-muted text-sm leading-relaxed whitespace-pre-line"><?= e($author->biographie_longue) ?></div>
        </div>
        <?php endif; ?>

        <div class="bg-surface border border-border rounded-xl p-5">
            <h3 class="text-white font-semibold text-sm mb-3">Réseaux sociaux</h3>
            <?php $links = array_filter([$author->site_web ?? null, $author->facebook_url ?? null, $author->instagram_url ?? null, $author->twitter_x_url ?? null, $author->linkedin_url ?? null]); ?>
            <?php if ($links): ?>
                <div class="space-y-2">
                    <?php foreach ($links as $link): ?>
                        <a href="<?= e($link) ?>" target="_blank" class="text-accent text-sm hover:text-accent-hover block truncate"><?= e($link) ?></a>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <p class="text-text-dim text-sm">Aucun lien fourni</p>
            <?php endif; ?>
        </div>

        <div class="bg-surface border border-border rounded-xl p-5">
            <h3 class="text-white font-semibold text-sm mb-3">Versement</h3>
            <p class="text-text-muted text-sm">Méthode : <strong class="text-white"><?= e(ucfirst(str_replace('_',' ',$author->methode_versement ?? '-'))) ?></strong></p>
            <?php if ($author->numero_mobile_money): ?><p class="text-text-dim text-sm mt-1">Mobile Money : <?= e($author->numero_mobile_money) ?></p><?php endif; ?>
            <?php if ($author->email_paypal): ?><p class="text-text-dim text-sm mt-1">PayPal : <?= e($author->email_paypal) ?></p><?php endif; ?>
        </div>

        <?php if (!empty($livresEnRevue)): ?>
        <div class="bg-surface border border-border rounded-xl p-5">
            <h3 class="text-white font-semibold text-sm mb-3">Livres soumis (<?= count($livresEnRevue) ?>)</h3>
            <?php foreach ($livresEnRevue as $l): ?>
                <a href="/admin/livres/<?= $l->id ?>/apercu" class="block text-accent text-sm hover:text-accent-hover mb-1"><?= e($l->titre) ?> (<?= $l->statut ?>)</a>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>

        <!-- Actions -->
        <?php if ($author->statut_validation === 'en_attente'): ?>
        <div class="flex flex-wrap gap-3 pt-4">
            <form method="POST" action="/admin/candidatures/<?= $author->id ?>/valider"><?= csrf_field() ?><button class="bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-medium px-6 py-2.5 rounded transition-colors">Valider la candidature</button></form>
            <form method="POST" action="/admin/candidatures/<?= $author->id ?>/refuser" onsubmit="this.querySelector('[name=motif]').value=prompt('Motif du refus :') || ''; return this.querySelector('[name=motif]').value !== '';">
                <?= csrf_field() ?><input type="hidden" name="motif" value="">
                <button class="bg-red-600/20 hover:bg-red-600/40 text-red-400 text-sm font-medium px-6 py-2.5 rounded transition-colors">Refuser</button>
            </form>
        </div>
        <?php elseif ($author->statut_validation === 'valide'): ?>
        <div class="flex gap-3 pt-4">
            <a href="/auteur/<?= e($author->slug) ?>" target="_blank" class="btn-secondary text-sm">Voir le profil public</a>
        </div>
        <?php elseif ($author->statut_validation === 'refuse' && $author->notes_admin): ?>
        <div class="bg-red-500/10 border border-red-500/30 rounded-lg p-4 mt-4">
            <p class="text-red-400 text-sm font-medium">Motif de refus :</p>
            <p class="text-text-muted text-sm mt-1"><?= e($author->notes_admin) ?></p>
        </div>
        <?php endif; ?>
    </div>
</div>
