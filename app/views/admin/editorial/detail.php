<?php
$success = flash('admin_success');
$error = flash('admin_error');
$icons = ['edit'=>'✏️','layout'=>'🧱','image'=>'🎨','message'=>'💬','package'=>'📦','plus'=>'➕'];
$statutLabels = [
    'en_attente_devis' => ['Devis en cours', 'bg-amber-500/20 text-amber-300'],
    'devis_envoye'     => ['Devis envoyé', 'bg-blue-500/20 text-blue-300'],
    'accepte'          => ['Accepté (à payer)', 'bg-emerald-500/20 text-emerald-300'],
    'en_cours'         => ['En cours', 'bg-purple-500/20 text-purple-300'],
    'livre'            => ['Livré', 'bg-emerald-500/20 text-emerald-400'],
    'annule'           => ['Annulé', 'bg-rose-500/20 text-rose-400'],
    'rembourse'        => ['Remboursé', 'bg-rose-500/20 text-rose-400'],
];
[$lab, $cls] = $statutLabels[$order->statut] ?? [$order->statut, 'bg-surface-2 text-text-dim'];
?>
<?php if ($success): ?><div class="bg-emerald-500/10 border border-emerald-500/30 text-emerald-400 px-4 py-3 rounded-lg mb-4 text-sm"><?= e($success) ?></div><?php endif; ?>
<?php if ($error): ?><div class="bg-red-500/10 border border-red-500/30 text-red-400 px-4 py-3 rounded-lg mb-4 text-sm"><?= e($error) ?></div><?php endif; ?>

<div class="mb-4"><a href="/admin/services-editoriaux" class="text-text-dim hover:text-accent text-xs">← Retour aux commandes</a></div>

<div class="bg-surface border border-border rounded-xl p-5 sm:p-7 mb-6">
    <div class="flex items-start gap-4 mb-4 flex-wrap">
        <div class="text-3xl flex-shrink-0"><?= $icons[$order->service_icon] ?? '📌' ?></div>
        <div class="flex-1">
            <h1 class="font-display font-bold text-xl sm:text-2xl text-white">Commande #<?= (int) $order->id ?> — <?= e($order->service_nom) ?></h1>
            <p class="text-text-muted text-sm mt-1"><?= e($order->titre_projet ?? '—') ?></p>
            <div class="flex items-center gap-2 mt-2">
                <span class="text-[11px] font-semibold px-2 py-0.5 rounded <?= $cls ?>"><?= e($lab) ?></span>
                <span class="text-text-dim text-xs">Créée le <?= date('d/m/Y H:i', strtotime($order->created_at)) ?></span>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-5 pt-5 border-t border-border">
        <div>
            <p class="text-text-dim text-xs uppercase tracking-wider mb-1">Auteur</p>
            <a href="/admin/lecteurs/<?= (int) $order->u_id ?>" class="text-accent hover:text-accent-hover"><?= e($order->prenom . ' ' . $order->nom) ?></a>
            <p class="text-text-dim text-xs mt-0.5"><?= e($order->email) ?></p>
        </div>
        <div>
            <p class="text-text-dim text-xs uppercase tracking-wider mb-1">Montant</p>
            <p class="text-accent font-display font-bold text-lg"><?= !empty($order->montant_propose) ? number_format((float) $order->montant_propose, 2) . ' ' . e($order->devise) : 'Sur devis' ?></p>
        </div>
        <?php if (!empty($order->paye_at)): ?>
            <div><p class="text-text-dim text-xs uppercase tracking-wider mb-1">Payé le</p><p class="text-emerald-400 text-sm"><?= date('d/m/Y H:i', strtotime($order->paye_at)) ?></p></div>
        <?php endif; ?>
        <?php if (!empty($order->livre_at)): ?>
            <div><p class="text-text-dim text-xs uppercase tracking-wider mb-1">Livré le</p><p class="text-emerald-400 text-sm"><?= date('d/m/Y H:i', strtotime($order->livre_at)) ?></p></div>
        <?php endif; ?>
    </div>

    <div class="space-y-4 pt-5 border-t border-border">
        <div>
            <p class="text-text-dim text-xs uppercase tracking-wider mb-1">Description</p>
            <p class="text-text-muted text-sm whitespace-pre-line"><?= e($order->description_projet ?? '—') ?></p>
        </div>
        <?php if (!empty($order->nombre_pages)): ?>
            <div><p class="text-text-dim text-xs uppercase tracking-wider mb-1">Pages</p><p class="text-white text-sm"><?= (int) $order->nombre_pages ?></p></div>
        <?php endif; ?>
        <?php if (!empty($order->fichier_url)): ?>
            <div>
                <p class="text-text-dim text-xs uppercase tracking-wider mb-1">Fichier joint par l'auteur</p>
                <a href="/editorial/file/uploads/<?= e(basename($order->fichier_url)) ?>" class="text-accent hover:text-accent-hover text-sm">📎 Télécharger</a>
            </div>
        <?php endif; ?>
        <?php if (!empty($order->notes_admin)): ?>
            <div><p class="text-text-dim text-xs uppercase tracking-wider mb-1">Notes admin</p><p class="text-text-muted text-sm whitespace-pre-line"><?= e($order->notes_admin) ?></p></div>
        <?php endif; ?>
    </div>
</div>

<!-- Actions admin contextuelles -->
<?php if ($order->statut === 'en_attente_devis'): ?>
    <div class="bg-surface border border-border rounded-xl p-5 sm:p-7 mb-6">
        <h2 class="font-display font-semibold text-lg text-white mb-4">Envoyer un devis</h2>
        <form action="/admin/services-editoriaux/<?= (int) $order->id ?>/devis" method="POST" class="space-y-4">
            <?= csrf_field() ?>
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                <div class="sm:col-span-2">
                    <label class="block text-xs text-text-dim uppercase tracking-wider mb-1">Montant</label>
                    <input type="number" name="montant_propose" step="0.01" min="0" required class="w-full bg-surface-2 border border-border rounded px-3 py-2.5 text-sm text-white outline-none focus:border-accent">
                </div>
                <div>
                    <label class="block text-xs text-text-dim uppercase tracking-wider mb-1">Devise</label>
                    <select name="devise" class="w-full bg-surface-2 border border-border rounded px-3 py-2.5 text-sm text-white outline-none focus:border-accent">
                        <option>USD</option><option>EUR</option><option>CDF</option><option>CAD</option>
                    </select>
                </div>
            </div>
            <div>
                <label class="block text-xs text-text-dim uppercase tracking-wider mb-1">Notes pour l'auteur (optionnel)</label>
                <textarea name="notes_admin" rows="3" class="w-full bg-surface-2 border border-border rounded px-3 py-2.5 text-sm text-white outline-none focus:border-accent resize-none"></textarea>
            </div>
            <button type="submit" class="btn-primary">Envoyer le devis</button>
        </form>
    </div>
<?php elseif (in_array($order->statut, ['accepte','en_cours'], true)): ?>
    <div class="bg-surface border border-border rounded-xl p-5 sm:p-7 mb-6">
        <h2 class="font-display font-semibold text-lg text-white mb-4">Livraison</h2>
        <form action="/admin/services-editoriaux/<?= (int) $order->id ?>/livraison" method="POST" enctype="multipart/form-data" class="space-y-4">
            <?= csrf_field() ?>
            <input type="file" name="delivery" required accept="application/pdf,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document,application/zip,image/jpeg,image/png" class="text-sm text-text-muted file:mr-3 file:py-2 file:px-4 file:rounded file:border-0 file:bg-surface-2 file:text-white file:font-medium file:cursor-pointer hover:file:bg-accent/20">
            <p class="text-text-dim text-xs">PDF, DOCX, ZIP, JPG ou PNG. Max 100 Mo.</p>
            <button type="submit" class="btn-primary">Marquer comme livré + envoyer fichier</button>
        </form>
    </div>
<?php endif; ?>

<!-- Actions générales — changer statut -->
<div class="bg-surface border border-border rounded-xl p-5 sm:p-7">
    <h2 class="font-display font-semibold text-base text-white mb-3">Changer le statut</h2>
    <form action="/admin/services-editoriaux/<?= (int) $order->id ?>/statut" method="POST" class="flex flex-wrap gap-3 items-end">
        <?= csrf_field() ?>
        <div>
            <label class="block text-xs text-text-dim uppercase tracking-wider mb-1">Nouveau statut</label>
            <select name="statut" class="bg-surface-2 border border-border rounded px-3 py-2 text-sm text-white outline-none focus:border-accent">
                <?php foreach (['en_attente_devis','devis_envoye','accepte','en_cours','livre','annule','rembourse'] as $s): ?>
                    <option value="<?= $s ?>" <?= $order->statut === $s ? 'selected' : '' ?>><?= e($s) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <button type="submit" class="btn-secondary text-sm">Mettre à jour</button>
    </form>
</div>
