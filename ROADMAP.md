# Roadmap — Les Éditions Variable

Plan de développement sur 12 semaines (6 phases).

---

## Phase 1 — Socle technique, authentification, homepage (Semaines 1-2)

- Mise en place de l'architecture MVC (routeur, contrôleurs, vues, modèles)
- Configuration de la connexion MySQL et migrations de base
- Système d'authentification complet (inscription, connexion, mot de passe oublié)
- Gestion des sessions et rôles (lecteur, auteur, admin)
- Page d'accueil avec design responsive (Tailwind CSS)
- Layout principal (header, footer, navigation)
- Configuration de l'environnement (.env, autoload, helpers)

## Phase 2 — Page livre, achat, liseuse (Semaines 3-4)

- Page catalogue avec filtres (genre, auteur, prix, popularité)
- Page détail d'un livre (description, aperçu gratuit 10 pages, avis)
- Panier d'achat et tunnel de commande
- Intégration Money Fusion (paiement mobile RDC)
- Intégration Stripe (paiement carte internationale)
- Bibliothèque personnelle du lecteur (livres achetés)
- Liseuse PDF.js intégrée avec protection du contenu (anti-copie, watermark)
- Streaming sécurisé page par page (pas de téléchargement direct)

## Phase 3 — Système d'abonnement et pool de redistribution (Semaines 5-6)

- Définition des 3 formules d'abonnement (Essentiel Mensuel 3$, Essentiel Annuel 30$, Premium 8$/mois)
- Page de présentation des offres avec comparateur
- Paiements récurrents Stripe (auto-renouvellement)
- Paiements manuels Money Fusion mensuels (rappels avant expiration)
- Accès conditionnel au catalogue selon l'abonnement actif
- **Tracking précis des pages lues par abonné** avec règles anti-abus (min 10s par page, min 70% du livre pour validation)
- **Calcul automatique du pool de redistribution** (cron mensuel le 5 du mois) : total abonnements - frais paiement - 50% = pool auteurs
- **Redistribution au prorata des pages lues** avec stockage dans author_payouts pour chaque auteur
- Page /mon-compte/abonnement (statut, factures, changer de plan, annuler)
- Emails automatiques (paiement réussi, échec, bientôt expirer)
- Historique de facturation téléchargeable PDF

## Phase 4 — Dashboard auteur (Semaines 7-8)

- Tableau de bord auteur (statistiques de ventes, revenus, pages lues)
- Soumission de manuscrit (upload PDF, métadonnées, génération automatique extrait 10 pages)
- Gestion du profil auteur public (bio, photo, réseaux sociaux)
- Suivi des ventes et des versements trimestriels
- Page publique /auteur/:slug
- Système de notifications (vente, nouveau lecteur, versement effectué)
- Achat de packs de promotion (150$, 300$, 500$)

## Phase 5 — Dashboard admin (Semaines 9-10)

- Tableau de bord administrateur (KPI, CA, abonnés actifs, top livres/auteurs)
- Validation des candidatures auteurs (générer contrat PDF auto)
- Gestion des livres (valider, publier, retirer, mettre en avant)
- Gestion des utilisateurs (lecteurs, auteurs, modération)
- Gestion des versements trimestriels aux auteurs
- Visualisation du pool mensuel et historique des redistributions
- Paramètres de la plateforme (prix abonnements, commissions, taux conversion devises)

## Phase 6 — SEO, sécurité, mise en production (Semaines 11-12)

- Optimisation SEO (balises meta, sitemap.xml, URLs propres, JSON-LD Schema.org)
- Audit de sécurité complet (CSRF, XSS, injection SQL, rate limiting, headers HTTP)
- Mise en place HTTPS et configuration .htaccess
- Optimisation des performances (cache fichier, compression Gzip, images WebP, lazy loading)
- Pages légales (mentions, CGU, CGV, politique confidentialité RGPD)
- Tests fonctionnels complets et corrections de bugs
- Déploiement sur NitroHost (configuration cPanel, DNS, SSL Let's Encrypt)
- Configuration des cron jobs (calcul pool, versements, sitemap, backups)
- Documentation finale (README, guide admin, guide auteur)
