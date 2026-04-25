<?php
$success = flash('success');
$error   = flash('error');
$icons = ['edit'=>'✏️','layout'=>'🧱','image'=>'🎨','message'=>'💬','package'=>'📦','plus'=>'➕'];
$statutLabels = [
    'en_attente_devis' => ['Devis en cours', 'bg-amber-500/20 text-amber-300'],
    'devis_envoye'     => ['Devis reçu', 'bg-blue-500/20 text-blue-300'],
    'accepte'          => ['Prêt à payer', 'bg-emerald-500/20 text-emerald-300'],
    'en_cours'         => ['En cours', 'bg-purple-500/20 text-purple-300'],
    'livre'            => ['Livré', 'bg-emerald-500/20 text-emerald-400'],
    'annule'           => ['Annulé', 'bg-rose-500/20 text-rose-400'],
    'rembourse'        => ['Remboursé', 'bg-rose-500/20 text-rose-400'],
];
[$lab, $cls] = $statutLabels[$order->statut] ?? [$order->statut, 'bg-surface-2 text-text-dim'];
?>
<?php if ($success): ?><div class="bg-emerald-500/10 border border-emerald-500/30 text-emerald-400 px-4 py-3 rounded-lg mb-4 text-sm"><?= e($success) ?></div><?php endif; ?>
<?php if ($error): ?><div class="bg-red-500/10 border border-red-500/30 text-red-400 px-4 py-3 rounded-lg mb-4 text-sm"><?= e($error) ?></div><?php endif; ?>

<div class="mb-4"><a href="/auteur/mes-commandes-editoriales" class="text-text-dim hover:text-accent text-xs">← Retour aux commandes</a></div>

<div class="bg-surface border border-border rounded-xl p-5 sm:p-7 mb-6">
    <div class="flex items-start gap-4 mb-5">
        <div class="text-3xl flex-shrink-0" aria-hidden="true"><?= $icons[$order->service_icon] ?? '📌' ?></div>
        <div class="flex-1">
            <h1 class="font-display font-bold text-xl sm:text-2xl text-white"><?= e($order->service_nom) ?></h1>
            <p class="text-text-muted text-sm mt-1"><?= e($order->titre_projet ?? '—') ?></p>
            <div class="flex items-center gap-2 mt-2">
                <span class="text-[11px] font-semibold px-2 py-0.5 rounded <?= $cls ?>"><?= e($lab) ?></span>
                <span class="text-text-dim text-xs">Créée le <?= date('d/m/Y', strtotime($order->created_at)) ?></span>
            </div>
        </div>
    </div>

    <?php if ($order->statut === 'en_attente_devis'): ?>
        <div class="bg-amber-500/10 border border-amber-500/30 rounded-lg p-4 mb-5">
            <p class="text-amber-300 font-medium">Devis en cours d'élaboration</p>
            <p class="text-text-muted text-sm mt-1">Notre équipe analyse ton projet et te revient sous 48h avec un devis personnalisé.</p>
        </div>
    <?php elseif ($order->statut === 'devis_envoye'): ?>
        <div class="bg-blue-500/10 border border-blue-500/30 rounded-lg p-4 mb-5">
            <p class="text-blue-300 font-medium">Devis : <?= number_format((float) $order->montant_propose, 2) ?>&nbsp;<?= e($order->devise) ?></p>
            <?php if (!empty($order->notes_admin)): ?>
                <p class="text-text-muted text-sm mt-2 whitespace-pre-line"><?= e($order->notes_admin) ?></p>
            <?php endif; ?>
            <form action="/admin/services-editoriaux/<?= (int) $order->id ?>/statut" method="POST" class="hidden"></form>
            <p class="text-text-dim text-xs mt-3">Pour accepter, clique sur « Accepter et payer ». Pour discuter du devis, contacte-nous.</p>
            <div class="mt-4 flex flex-wrap gap-3">
                <!-- Accept = transition to 'accepte' via paiement direct -->
                <form action="/admin/services-editoriaux/<?= (int) $order->id ?>/statut" method="POST" class="inline">
                    <?= csrf_field() ?>
                    <input type="hidden" name="statut" value="accepte">
                    <button type="submit" class="btn-primary text-sm">Accepter le devis</button>
                </form>
                <a href="mailto:contact@variablefly.com?subject=Devis commande %23<?= (int) $order->id ?>" class="btn-secondary text-sm">Discuter</a>
            </div>
        </div>
    <?php elseif ($order->statut === 'accepte'): ?>
        <div class="bg-emerald-500/10 border border-emerald-500/30 rounded-lg p-4 mb-5">
            <p class="text-emerald-400 font-medium">Prêt à payer</p>
            <p class="text-text-muted text-sm mt-1">Montant à régler : <strong class="text-white"><?= number_format((float) $order->montant_propose, 2) ?>&nbsp;<?= e($order->devise) ?></strong></p>
            <a href="/auteur/mes-commandes-editoriales/<?= (int) $order->id ?>/payer" class="btn-primary text-sm inline-block mt-3">Payer maintenant</a>
        </div>
    <?php elseif ($order->statut === 'en_cours'): ?>
        <div class="bg-purple-500/10 border border-purple-500/30 rounded-lg p-4 mb-5">
            <p class="text-purple-300 font-medium">Travail en cours</p>
            <p class="text-text-muted text-sm mt-1">Paiement reçu le <?= !empty($order->paye_at) ? date('d/m/Y', strtotime($order->paye_at)) : '—' ?>. Notre équipe travaille sur ton projet, on te notifiera dès la livraison.</p>
        </div>
    <?php elseif ($order->statut === 'livre'): ?>
        <div class="bg-emerald-500/10 border border-emerald-500/30 rounded-lg p-4 mb-5">
            <p class="text-emerald-400 font-medium">Commande livrée 🎉</p>
            <p class="text-text-muted text-sm mt-1">Livrée le <?= !empty($order->livre_at) ? date('d/m/Y', strtotime($order->livre_at)) : '—' ?>.</p>
            <?php if (!empty($order->fichier_livraison_url)): ?>
                <a href="/editorial/file/deliveries/<?= e(basename($order->fichier_livraison_url)) ?>" class="btn-primary text-sm inline-block mt-3">Télécharger ma livraison</a>
            <?php endif; ?>
        </div>
    <?php endif; ?>

    <div class="border-t border-border pt-5 space-y-4">
        <div>
            <p class="text-text-dim text-xs uppercase tracking-wider mb-1">Description du projet</p>
            <p class="text-text-muted text-sm whitespace-pre-line"><?= e($order->description_projet ?? '') ?></p>
        </div>
        <?php if (!empty($order->nombre_pages)): ?>
            <div>
                <p class="text-text-dim text-xs uppercase tracking-wider mb-1">Nombre de pages</p>
                <p class="text-white text-sm"><?= (int) $order->nombre_pages ?></p>
            </div>
        <?php endif; ?>
        <?php if (!empty($order->fichier_url)): ?>
            <div>
                <p class="text-text-dim text-xs uppercase tracking-wider mb-1">Fichier joint</p>
                <a href="/editorial/file/uploads/<?= e(basename($order->fichier_url)) ?>" class="text-accent hover:text-accent-hover text-sm">📎 Télécharger ton fichier</a>
            </div>
        <?php endif; ?>
    </div>
</div>
