<?php
$categories = [
    'compte' => [
        'titre' => 'Compte et inscription',
        'icon'  => '👤',
        'faqs'  => [
            ['Comment créer un compte ?', "Clique sur « Inscription » en haut à droite, renseigne ton prénom, nom, email et un mot de passe d'au moins 8 caractères. Tu reçois un email de confirmation : clique sur le lien pour activer ton compte. Tout est gratuit, l'inscription ne t'engage à rien."],
            ['J\'ai oublié mon mot de passe, que faire ?', "Va sur la page de connexion, clique sur « Mot de passe oublié », rentre ton email. Tu reçois un lien valable 1 heure pour définir un nouveau mot de passe. Si tu ne reçois rien, vérifie tes spams ou écris-nous."],
            ['Comment changer mon adresse email ?', "Pour des raisons de sécurité, le changement d'email se fait via le support. Écris-nous à contact@leseditionsvariable.com depuis l'ancienne adresse en précisant la nouvelle. On confirme sous 24h."],
            ['Comment supprimer mon compte ?', "Va dans « Mon compte » → « Mon profil » → section rouge « Zone de danger » → bouton « Demander la suppression ». Tu reçois un email de confirmation. En cliquant sur le lien dans l'email, tes données sont anonymisées définitivement. C'est irréversible."],
            ['Pourquoi je ne reçois pas l\'email de confirmation ?', "Dans 90% des cas, l'email est dans tes spams. Vérifie-y. Si rien, attends 5 minutes (les serveurs mail peuvent retarder). Si toujours rien, écris-nous à contact@leseditionsvariable.com avec ton email d'inscription."],
        ],
    ],
    'abonnement' => [
        'titre' => 'Abonnement',
        'icon'  => '⭐',
        'faqs'  => [
            ['Quelle est la différence entre Essentiel et Premium ?', "Essentiel donne accès à 80% du catalogue (tous les livres standards) à 3 USD/mois. Premium ajoute les livres exclusifs, le téléchargement PDF offline, un livre physique trimestriel livré en RDC, l'accès aux événements auteurs et le support prioritaire — pour 10 USD/mois."],
            ['Combien coûte l\'abonnement ?', "Essentiel Mensuel : 3 USD/mois. Essentiel Annuel : 30 USD/an (= 2 mois offerts). Premium Mensuel : 10 USD/mois. Premium Annuel : 100 USD/an (= 2 mois offerts)."],
            ['Comment annuler mon abonnement ?', "Va dans « Mon compte » → « Mon abonnement » → bouton rouge « Annuler ». Tu donnes une raison (optionnel) et tu confirmes. Tu gardes l'accès jusqu'à la fin de ta période payée. Tu peux réactiver à tout moment avant cette date."],
            ['Que se passe-t-il quand mon abonnement expire ?', "Tu perds l'accès aux livres lus via abonnement (ils restent visibles dans ta bibliothèque avec un badge gris « Renouvelle »). Tes livres achetés à l'unité, eux, restent accessibles à vie. Tes favoris et ta progression sont conservés."],
            ['Puis-je changer de plan en cours d\'année ?', "Oui, tu peux passer d'Essentiel à Premium à tout moment. Le changement prend effet immédiatement, on calcule un prorata sur la période restante. Pour passer de Premium à Essentiel, le changement prend effet à la fin de ta période payée actuelle."],
            ['Puis-je payer en Mobile Money ?', "Oui ! On accepte Airtel Money, Orange Money, MTN Mobile Money, M-Pesa, Wave et Moov Money. Au moment du paiement, choisis « Mobile Money » et suis les instructions. C'est pensé pour la RDC, le Sénégal, la Côte d'Ivoire et toute l'Afrique francophone."],
            ['Comment fonctionne la livraison du livre physique trimestriel (Premium) ?', "Une fois par trimestre, les abonnés Premium en RDC reçoivent un livre physique chez eux. Tu choisis le titre dans une sélection de 5 livres. Pour l'instant, la livraison physique est limitée à Kinshasa et grandes villes. On étendra progressivement."],
            ['Y a-t-il une période d\'essai ?', "Pas pour l'instant. Mais tu peux lire un extrait gratuit de 10 pages sur chaque livre, sans abonnement, pour te faire une idée du catalogue. L'extrait te donne le ton, le style, le début de l'histoire."],
        ],
    ],
    'lecture' => [
        'titre' => 'Lecture',
        'icon'  => '📖',
        'faqs'  => [
            ['Comment lire un livre ?', "Connecte-toi, va dans « Mon compte » → « Ma bibliothèque » (ou /catalogue pour découvrir), clique sur un livre, puis sur « Commencer la lecture ». La liseuse PDF.js s'ouvre en plein écran. Tu navigues avec les flèches, le clavier, ou en swipant sur mobile."],
            ['Puis-je lire hors-ligne ?', "Pour le moment, la lecture nécessite une connexion. Avec l'abonnement Premium, tu peux télécharger les livres en PDF protégé pour les lire offline dans n'importe quel lecteur PDF. Une app mobile native avec mode offline complet est en développement."],
            ['Sur combien d\'appareils puis-je lire ?', "Sans limite. Connecte-toi depuis ton téléphone, ta tablette, ton ordinateur. Ta progression est synchronisée automatiquement. Reprends ta lecture là où tu l'as laissée, peu importe l'appareil."],
            ['Puis-je télécharger les livres en PDF ?', "Avec l'abonnement Premium, oui. Le PDF est protégé contre la copie et le partage. Avec un achat unitaire, tu lis via la liseuse en ligne mais sans téléchargement direct (sauf option future)."],
            ['Comment fonctionne l\'extrait gratuit ?', "Sur chaque livre payant, tu as accès aux 10 premières pages gratuitement, dès que tu es connecté. L'extrait te permet de tester le style et l'univers avant d'acheter ou de t'abonner."],
            ['Pourquoi je ne peux pas copier le texte ?', "Pour protéger nos auteurs contre le piratage, la liseuse désactive le copier-coller, l'impression et l'enregistrement. C'est une mesure de protection minimum, comme sur Kindle ou Apple Books. Si tu veux citer un passage, contacte l'auteur directement."],
        ],
    ],
    'achat' => [
        'titre' => 'Achat à l\'unité',
        'icon'  => '🛒',
        'faqs'  => [
            ['Comment acheter un livre à l\'unité ?', "Sur la page d'un livre, clique sur « Acheter pour X $ ». Tu choisis ta méthode de paiement (Stripe carte ou Money Fusion Mobile Money), tu valides, et le livre apparaît immédiatement dans ta bibliothèque, à vie."],
            ['Quels modes de paiement acceptez-vous ?', "Carte bancaire (Visa, Mastercard, Amex) via Stripe pour le monde entier. Mobile Money (Airtel, Orange, MTN, M-Pesa, Wave, Moov) via Money Fusion pour l'Afrique francophone."],
            ['Est-ce que je garde le livre à vie si je l\'achète ?', "Oui. Un achat unitaire = accès à vie au livre, même si tu annules ton abonnement. Le livre reste dans ta bibliothèque pour toujours, lisible quand tu veux."],
            ['Puis-je obtenir un remboursement ?', "Pour les achats unitaires : non par défaut, comme tout produit numérique consommé immédiatement. Sauf erreur technique, double paiement ou problème vérifié — écris-nous, on traite au cas par cas. Pour l'abonnement : voir la rubrique annulation."],
            ['Que faire si mon paiement échoue ?', "Vérifie que ton solde est suffisant et que ta carte n'est pas expirée. Réessaie après quelques minutes. Si le problème persiste, change de méthode de paiement ou écris-nous avec le code d'erreur."],
        ],
    ],
    'auteurs' => [
        'titre' => 'Auteurs',
        'icon'  => '✍️',
        'faqs'  => [
            ['Comment publier mon livre chez Variable ?', "1) Crée un compte. 2) Va sur /auteur/candidater et remplis le formulaire (qui tu es, ton projet, un extrait). 3) On répond sous 7 jours. 4) Si validé, tu deviens auteur et tu peux soumettre ton manuscrit. 5) Après validation admin, ton livre est publié."],
            ['Combien je gagne par vente ?', "70% du prix de vente sur chaque achat unitaire (la plateforme garde 30% pour l'hébergement, les paiements et la promotion). Sur un livre vendu 9,99 USD, tu touches environ 6,99 USD avant frais de paiement. Pour les lectures via abonnement, on calcule un pool mensuel redistribué au prorata des pages réellement lues sur tes livres."],
            ['Quand suis-je payé ?', "Sur demande, dès que ton solde dépasse 10 USD. Tu lances le versement depuis /auteur/revenus, on traite sous quelques jours ouvrés. Mobile Money (RDC, Sénégal, etc.), virement bancaire/Wise pour la diaspora, ou PayPal/Stripe Connect."],
            ['Puis-je retirer mon livre ?', "Oui, à tout moment, depuis ton dashboard auteur. Le livre passe en « retiré » et n'est plus accessible aux nouveaux lecteurs. Les lecteurs qui l'ont déjà acheté gardent leur accès."],
            ['Quels formats acceptez-vous ?', "Pour le manuscrit : PDF (préféré), DOCX, ODT. Pour la couverture : JPEG/PNG/WebP, idéalement 1200×1800px (format portrait). Si tu n'as pas de couverture, on peut t'en faire une via notre service éditorial."],
            ['Que sont les services éditoriaux ?', "Une offre d'accompagnement payante : relecture/correction (75 $), mise en page pro (120 $), couverture personnalisée (150 $), coaching d'écriture (40 $/séance), pack complet (sur devis). Voir /services-editoriaux pour le détail."],
        ],
    ],
    'technique' => [
        'titre' => 'Technique',
        'icon'  => '⚙️',
        'faqs'  => [
            ['Quels navigateurs sont compatibles ?', "Chrome, Firefox, Safari, Edge — toutes versions récentes (2 dernières années). On supporte iOS 14+ et Android 8+. La liseuse PDF.js fonctionne partout, même sur des téléphones modestes."],
            ['L\'application mobile arrive-t-elle bientôt ?', "Oui. Une app native iOS et Android est en développement. Date de sortie estimée : fin 2026. Elle apportera le mode offline complet, les notifications push, et une expérience optimisée pour les téléphones africains."],
            ['Mes données sont-elles sécurisées ?', "Tout est chiffré en transit (HTTPS partout). Mots de passe hashés avec bcrypt. Données de paiement traitées par Stripe et Money Fusion (certifiés PCI-DSS), jamais stockées sur nos serveurs. Sauvegardes quotidiennes. Voir /confidentialite pour le détail."],
            ['Comment signaler un bug ?', "Écris-nous à contact@leseditionsvariable.com avec : ce que tu faisais, ce qui s'est passé, le navigateur/appareil. Une capture d'écran aide énormément. On répond sous 24h en semaine."],
        ],
    ],
];
$totalFaqs = 0;
foreach ($categories as $cat) $totalFaqs += count($cat['faqs']);
?>
<section class="py-12 sm:py-20" x-data="{ search: '' }">
    <div class="max-w-4xl mx-auto px-4 sm:px-6">

        <div class="text-center mb-12">
            <h1 class="font-display font-extrabold text-3xl sm:text-4xl md:text-5xl text-white mb-3">Centre d'aide</h1>
            <p class="text-text-muted text-base sm:text-lg">Toutes les réponses à tes questions. Si tu n'en trouves pas, on est là.</p>
        </div>

        <!-- Recherche -->
        <div class="mb-10 max-w-xl mx-auto">
            <div class="relative">
                <svg class="w-5 h-5 absolute left-4 top-1/2 -translate-y-1/2 text-text-dim" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z"/></svg>
                <input type="text" x-model="search" placeholder="Rechercher une question…"
                       class="w-full bg-surface-2 border border-border rounded-lg pl-12 pr-4 py-3 text-sm text-white outline-none focus:border-accent">
            </div>
            <p class="text-text-dim text-xs text-center mt-2"><?= $totalFaqs ?> questions disponibles</p>
        </div>

        <!-- Catégories -->
        <?php foreach ($categories as $catKey => $cat): ?>
            <div class="mb-10" x-data="{ open: null }">
                <h2 class="font-display font-bold text-xl text-white mb-4 flex items-center gap-2">
                    <span aria-hidden="true"><?= $cat['icon'] ?></span> <?= e($cat['titre']) ?>
                </h2>
                <div class="space-y-2">
                    <?php foreach ($cat['faqs'] as $i => [$q, $a]): ?>
                        <div class="bg-surface border border-border rounded-xl overflow-hidden"
                             x-show="search === '' || '<?= e(mb_strtolower($q . ' ' . $a)) ?>'.includes(search.toLowerCase())">
                            <button @click="open = open === <?= $i ?> ? null : <?= $i ?>" class="w-full px-5 py-4 text-left flex items-center justify-between hover:bg-surface-2 transition-colors gap-3">
                                <span class="text-white font-medium text-sm"><?= e($q) ?></span>
                                <svg class="w-4 h-4 text-text-dim transition-transform flex-shrink-0" :class="open === <?= $i ?> ? 'rotate-180' : ''" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5"/></svg>
                            </button>
                            <div x-show="open === <?= $i ?>" x-cloak class="px-5 pb-4 text-text-muted text-sm leading-relaxed"><?= e($a) ?></div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endforeach; ?>

        <!-- CTA contact -->
        <div class="bg-gradient-to-r from-accent/15 to-amber-600/15 border border-accent/30 rounded-xl p-7 text-center mt-12">
            <h2 class="font-display font-bold text-xl text-white mb-2">Tu ne trouves pas ?</h2>
            <p class="text-text-muted text-sm mb-5">Notre support répond en moyenne sous 24 heures en semaine.</p>
            <div class="flex flex-wrap gap-3 justify-center">
                <a href="/contact" class="btn-primary">Nous écrire</a>
                <a href="mailto:contact@leseditionsvariable.com" class="btn-secondary">Envoyer un email</a>
            </div>
        </div>

    </div>
</section>
