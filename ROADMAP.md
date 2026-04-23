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
- Page détail d'un livre (description, aperçu, avis)
- Panier d'achat et tunnel de commande
- Intégration Money Fusion (paiement mobile RDC)
- Intégration Stripe (paiement carte internationale)
- Bibliothèque personnelle du lecteur (livres achetés)
- Liseuse PDF.js intégrée avec protection du contenu
- Système de téléchargement sécurisé

## Phase 3 — Système d'abonnement (Semaines 5-6)

- Définition des formules d'abonnement (mensuel, annuel)
- Page de présentation des offres
- Gestion des paiements récurrents (Stripe + Money Fusion)
- Accès conditionnel au catalogue selon l'abonnement
- Gestion du renouvellement et de l'expiration
- Historique de facturation pour l'abonné

## Phase 4 — Dashboard auteur (Semaines 7-8)

- Tableau de bord auteur (statistiques de ventes, revenus)
- Soumission de manuscrit (upload PDF, métadonnées)
- Gestion du profil auteur (bio, photo, réseaux sociaux)
- Suivi des ventes et des paiements
- Page publique de l'auteur
- Système de notification (nouveau commentaire, vente)

## Phase 5 — Dashboard admin (Semaines 9-10)

- Tableau de bord administrateur (KPI, revenus, utilisateurs)
- Gestion des livres (validation, publication, retrait)
- Gestion des utilisateurs (lecteurs, auteurs, modération)
- Gestion des paiements et reversements aux auteurs
- Gestion du catalogue et des catégories
- Paramètres de la plateforme (commissions, CGU, emails)

## Phase 6 — SEO, sécurité, mise en production (Semaines 11-12)

- Optimisation SEO (balises meta, sitemap, URLs propres, données structurées)
- Audit de sécurité (CSRF, XSS, injection SQL, rate limiting)
- Mise en place HTTPS et headers de sécurité
- Optimisation des performances (cache, compression, lazy loading)
- Tests fonctionnels et corrections de bugs
- Déploiement sur NitroHost (configuration cPanel, DNS, SSL)
- Documentation finale et formation
