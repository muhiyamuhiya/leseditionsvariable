<section class="min-h-[60vh] flex items-center justify-center px-4">
    <div class="text-center max-w-md">
        <div class="w-20 h-20 rounded-full bg-red-500/10 flex items-center justify-center mx-auto mb-6">
            <svg class="w-10 h-10 text-red-400" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z"/></svg>
        </div>
        <h1 class="font-display font-bold text-2xl text-white mb-3">Candidature non retenue</h1>
        <?php if (!empty($author->notes_admin)): ?>
            <div class="bg-surface border border-border rounded-lg p-4 mb-6 text-left"><p class="text-text-dim text-xs uppercase mb-1">Motif :</p><p class="text-text-muted text-sm"><?= e($author->notes_admin) ?></p></div>
        <?php endif; ?>
        <p class="text-text-muted text-sm mb-6">Si tu souhaites postuler à nouveau, contacte-nous à <a href="mailto:contact@leseditionsvariable.com" class="text-accent">contact@leseditionsvariable.com</a>.</p>
        <a href="/catalogue" class="btn-primary text-sm">Explorer le catalogue</a>
    </div>
</section>
