<section class="min-h-[60vh] flex items-center justify-center px-4">
    <div class="text-center max-w-md">
        <div class="w-20 h-20 rounded-full bg-accent/10 flex items-center justify-center mx-auto mb-6">
            <svg class="w-10 h-10 text-accent" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        </div>
        <h1 class="font-display font-bold text-2xl text-white mb-3">Candidature en cours d'examen</h1>
        <p class="text-text-muted mb-6">Nous reviendrons vers toi sous 5 jours ouvrés à <strong class="text-white"><?= e(App\Lib\Auth::user()->email ?? '') ?></strong>.</p>
        <div class="flex flex-col sm:flex-row gap-3 justify-center">
            <a href="/auteur/candidater" class="btn-secondary text-sm">Mettre à jour ma candidature</a>
            <a href="/catalogue" class="btn-primary text-sm">Explorer le catalogue</a>
        </div>
    </div>
</section>
