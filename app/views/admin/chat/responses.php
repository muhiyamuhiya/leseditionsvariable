<?php
/** @var array $responses */
/** @var ?object $editing */

$flashSuccess = flash('success');
$flashError   = flash('error');

$totalActive = 0;
$totalUsage = 0;
foreach ($responses as $r) {
    if ($r->actif) $totalActive++;
    $totalUsage += (int) $r->times_used;
}

// Categories distinctes pour le select
$categories = [];
foreach ($responses as $r) {
    if ($r->category && !in_array($r->category, $categories, true)) {
        $categories[] = $r->category;
    }
}
sort($categories);
?>
<div class="flex items-center gap-3 mb-4">
    <a href="/admin/chat" class="text-text-dim hover:text-white text-sm">← Retour au chat</a>
    <h2 class="font-display text-xl text-white">Réponses du bot</h2>
</div>

<div class="grid grid-cols-2 md:grid-cols-4 gap-3 mb-4">
    <div class="bg-surface border border-border rounded-lg p-3">
        <p class="text-xs text-text-dim">Total</p>
        <p class="text-2xl font-display font-bold text-white"><?= count($responses) ?></p>
    </div>
    <div class="bg-surface border border-border rounded-lg p-3">
        <p class="text-xs text-text-dim">Actives</p>
        <p class="text-2xl font-display font-bold text-emerald-400"><?= $totalActive ?></p>
    </div>
    <div class="bg-surface border border-border rounded-lg p-3">
        <p class="text-xs text-text-dim">Désactivées</p>
        <p class="text-2xl font-display font-bold text-red-400"><?= count($responses) - $totalActive ?></p>
    </div>
    <div class="bg-surface border border-border rounded-lg p-3">
        <p class="text-xs text-text-dim">Total déclenchements</p>
        <p class="text-2xl font-display font-bold text-accent"><?= number_format($totalUsage, 0, ',', ' ') ?></p>
    </div>
</div>

<?php if ($flashSuccess): ?>
    <div class="mb-3 bg-emerald-500/10 border border-emerald-500/30 text-emerald-300 text-sm px-3 py-2 rounded-lg"><?= e($flashSuccess) ?></div>
<?php endif; ?>
<?php if ($flashError): ?>
    <div class="mb-3 bg-red-500/10 border border-red-500/30 text-red-300 text-sm px-3 py-2 rounded-lg"><?= e($flashError) ?></div>
<?php endif; ?>

<!-- Formulaire création/édition -->
<section class="bg-surface border border-border rounded-xl p-4 mb-6">
    <h3 class="font-display text-base text-white mb-3">
        <?= $editing ? 'Modifier la réponse #' . (int) $editing->id : 'Ajouter une réponse' ?>
    </h3>
    <form action="<?= $editing ? '/admin/chat/responses/' . (int) $editing->id : '/admin/chat/responses' ?>" method="POST" class="space-y-3">
        <?= csrf_field() ?>

        <div>
            <label class="block text-xs text-text-muted mb-1">Mots-clés <span class="text-red-400">*</span></label>
            <input type="text" name="keywords" required
                   value="<?= e($editing->keywords ?? '') ?>"
                   placeholder="abonnement,combien,prix abonnement,coute"
                   class="w-full bg-bg border border-border rounded-lg px-3 py-2 text-sm text-white placeholder-text-dim focus:border-accent outline-none">
            <p class="text-[10px] text-text-dim mt-1">Séparés par des virgules. Le PREMIER mot-clé compte +2 pts (priorité), les autres +1 pt. Score minimum pour matcher : 2.</p>
        </div>

        <div>
            <label class="block text-xs text-text-muted mb-1">Question type <span class="text-red-400">*</span></label>
            <input type="text" name="question" required
                   value="<?= e($editing->question ?? '') ?>"
                   placeholder="Combien coûte l'abonnement"
                   class="w-full bg-bg border border-border rounded-lg px-3 py-2 text-sm text-white placeholder-text-dim focus:border-accent outline-none">
            <p class="text-[10px] text-text-dim mt-1">Pour ta référence (n'apparaît pas côté visiteur).</p>
        </div>

        <div>
            <label class="block text-xs text-text-muted mb-1">Réponse du bot <span class="text-red-400">*</span></label>
            <textarea name="answer" required rows="4"
                      placeholder="On a 3 formules : &lt;strong&gt;Essentiel&lt;/strong&gt; ..."
                      class="w-full bg-bg border border-border rounded-lg px-3 py-2 text-sm text-white placeholder-text-dim focus:border-accent outline-none resize-y font-mono"><?= e($editing->answer ?? '') ?></textarea>
            <p class="text-[10px] text-text-dim mt-1">HTML simple autorisé : &lt;a href&gt;, &lt;strong&gt;, &lt;br&gt;.</p>
        </div>

        <div>
            <label class="block text-xs text-text-muted mb-1">Catégorie</label>
            <input type="text" name="category" list="vc-categories"
                   value="<?= e($editing->category ?? '') ?>"
                   placeholder="abonnement, achat, services, compte, lecture, auteur, technique, salutations…"
                   class="w-full bg-bg border border-border rounded-lg px-3 py-2 text-sm text-white placeholder-text-dim focus:border-accent outline-none">
            <datalist id="vc-categories">
                <?php foreach ($categories as $cat): ?>
                    <option value="<?= e($cat) ?>"></option>
                <?php endforeach; ?>
            </datalist>
        </div>

        <div class="flex items-center justify-end gap-2 pt-1">
            <?php if ($editing): ?>
                <a href="/admin/chat/responses" class="px-4 py-2 text-sm rounded-lg bg-surface-2 border border-border text-text-muted hover:text-white transition-colors">Annuler</a>
            <?php endif; ?>
            <button type="submit" class="px-4 py-2 bg-accent text-black text-sm font-semibold rounded-lg hover:bg-accent-hover transition-colors">
                <?= $editing ? 'Mettre à jour' : 'Ajouter' ?>
            </button>
        </div>
    </form>
</section>

<!-- Liste -->
<section class="bg-surface border border-border rounded-xl overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-surface-2 text-text-dim text-xs uppercase tracking-wide">
                <tr>
                    <th class="text-left px-3 py-2.5">#</th>
                    <th class="text-left px-3 py-2.5">Catégorie</th>
                    <th class="text-left px-3 py-2.5">Question / Mots-clés</th>
                    <th class="text-right px-3 py-2.5">Usage</th>
                    <th class="text-center px-3 py-2.5">Actif</th>
                    <th class="text-right px-3 py-2.5">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($responses)): ?>
                    <tr><td colspan="6" class="text-center text-text-dim py-6">Aucune réponse pour l'instant.</td></tr>
                <?php endif; ?>
                <?php foreach ($responses as $r): ?>
                    <tr class="border-t border-border <?= $r->actif ? '' : 'opacity-60' ?>">
                        <td class="px-3 py-2.5 text-text-dim text-xs"><?= (int) $r->id ?></td>
                        <td class="px-3 py-2.5">
                            <?php if ($r->category): ?>
                                <span class="text-[10px] uppercase tracking-wide font-bold bg-surface-2 text-text-muted px-1.5 py-0.5 rounded"><?= e($r->category) ?></span>
                            <?php else: ?>
                                <span class="text-text-dim text-xs">—</span>
                            <?php endif; ?>
                        </td>
                        <td class="px-3 py-2.5 max-w-md">
                            <p class="text-white text-sm truncate"><?= e($r->question) ?></p>
                            <p class="text-text-dim text-[11px] mt-0.5 truncate font-mono"><?= e($r->keywords) ?></p>
                        </td>
                        <td class="px-3 py-2.5 text-right text-text-muted text-xs"><?= (int) $r->times_used ?></td>
                        <td class="px-3 py-2.5 text-center">
                            <form action="/admin/chat/responses/<?= (int) $r->id ?>" method="POST" class="inline">
                                <?= csrf_field() ?>
                                <input type="hidden" name="toggle_actif" value="1">
                                <button type="submit"
                                        title="<?= $r->actif ? 'Désactiver' : 'Réactiver' ?>"
                                        class="w-9 h-5 rounded-full relative transition-colors <?= $r->actif ? 'bg-emerald-500' : 'bg-text-dim/40' ?>">
                                    <span class="absolute top-0.5 w-4 h-4 rounded-full bg-white transition-all <?= $r->actif ? 'left-[18px]' : 'left-0.5' ?>"></span>
                                </button>
                            </form>
                        </td>
                        <td class="px-3 py-2.5 text-right space-x-2 whitespace-nowrap">
                            <a href="/admin/chat/responses?edit=<?= (int) $r->id ?>" class="text-xs text-accent hover:underline">Éditer</a>
                            <form action="/admin/chat/responses/<?= (int) $r->id ?>/supprimer" method="POST" class="inline" onsubmit="return confirm('Supprimer définitivement cette réponse ?')">
                                <?= csrf_field() ?>
                                <button type="submit" class="text-xs text-red-400 hover:underline">Supprimer</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</section>
