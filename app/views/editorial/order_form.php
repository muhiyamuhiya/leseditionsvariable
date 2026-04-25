<?php $error = flash('error'); ?>
<div class="mb-4"><a href="/auteur/services-editoriaux/<?= e($service->slug) ?>" class="text-text-dim hover:text-accent text-xs">← Retour</a></div>

<div class="max-w-[700px]">
    <h1 class="font-display font-bold text-2xl sm:text-3xl text-white mb-1">Commander : <?= e($service->nom) ?></h1>
    <p class="text-text-muted text-sm mb-6">
        <?php if ($service->sur_devis): ?>
            Décris ton projet en détail. On t'envoie un devis personnalisé sous 48h.
        <?php else: ?>
            Prix fixe : <strong class="text-accent"><?= number_format((float) $service->prix_usd, 2) ?>&nbsp;$</strong>. Paiement après création de la commande.
        <?php endif; ?>
    </p>

    <?php if ($error): ?><div class="bg-red-500/10 border border-red-500/30 text-red-400 px-4 py-3 rounded-lg mb-4 text-sm"><?= e($error) ?></div><?php endif; ?>

    <form action="/auteur/services-editoriaux/<?= e($service->slug) ?>/commander" method="POST" enctype="multipart/form-data" class="space-y-5">
        <?= csrf_field() ?>

        <div>
            <label class="block text-xs text-text-dim uppercase tracking-wider mb-2">Titre du projet *</label>
            <input type="text" name="titre_projet" required maxlength="300"
                   placeholder="Ex : Mon premier roman, recueil de poèmes…"
                   class="w-full bg-surface-2 border border-border rounded px-4 py-3 text-sm text-white outline-none focus:border-accent">
        </div>

        <div>
            <label class="block text-xs text-text-dim uppercase tracking-wider mb-2">Description du besoin *</label>
            <textarea name="description_projet" required minlength="20" rows="6" maxlength="3000"
                      placeholder="Décris ton manuscrit, ton public cible, ton objectif et toute info utile pour notre équipe."
                      class="w-full bg-surface-2 border border-border rounded px-4 py-3 text-sm text-white outline-none focus:border-accent resize-none"></textarea>
            <p class="text-text-dim text-xs mt-1">Min 20 caractères.</p>
        </div>

        <div>
            <label class="block text-xs text-text-dim uppercase tracking-wider mb-2">Nombre de pages (optionnel)</label>
            <input type="number" name="nombre_pages" min="0" max="2000"
                   class="w-32 bg-surface-2 border border-border rounded px-4 py-2.5 text-sm text-white outline-none focus:border-accent">
        </div>

        <div>
            <label class="block text-xs text-text-dim uppercase tracking-wider mb-2">Manuscrit (optionnel mais recommandé)</label>
            <input type="file" name="fichier" accept="application/pdf,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document,application/zip"
                   class="text-sm text-text-muted file:mr-3 file:py-2 file:px-4 file:rounded file:border-0 file:bg-surface-2 file:text-white file:font-medium file:cursor-pointer hover:file:bg-accent/20">
            <p class="text-text-dim text-xs mt-1">PDF, DOCX ou ZIP. Max 50 Mo. Aide notre équipe à mieux te répondre.</p>
        </div>

        <div class="flex flex-wrap gap-3 pt-2">
            <button type="submit" class="btn-primary">Envoyer ma commande</button>
            <a href="/auteur/services-editoriaux/<?= e($service->slug) ?>" class="btn-secondary">Annuler</a>
        </div>
    </form>
</div>
