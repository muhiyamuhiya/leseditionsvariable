# Les Éditions Variable

Plateforme web de lecture numérique pour auteurs africains francophones.  
Développée par **Les Éditions Variable** — Kinshasa (RDC) / Trois-Rivières (Canada).

## Description

Les Éditions Variable est une plateforme complète permettant aux auteurs africains francophones de publier et vendre leurs ebooks, et aux lecteurs d'accéder à un catalogue riche via achat unitaire ou abonnement. La plateforme intègre une liseuse PDF en ligne, un système de paiement adapté à l'Afrique (Money Fusion) et à l'international (Stripe), ainsi que des tableaux de bord pour les auteurs et l'administration.

## Prérequis techniques

- **PHP** 8.2 ou supérieur
- **MySQL** 8.0 ou supérieur
- **Composer** (gestionnaire de dépendances PHP)
- **Node.js** 18+ et npm (pour la compilation Tailwind CSS)
- **Serveur web** Apache avec mod_rewrite activé (compatible cPanel)

## Installation

```bash
# 1. Cloner le dépôt
git clone https://github.com/votre-compte/leseditionsvariable.git
cd leseditionsvariable

# 2. Copier le fichier d'environnement
cp .env.example .env

# 3. Configurer les variables dans .env
#    - Connexion base de données
#    - Clés API (Stripe, Money Fusion)
#    - URL du site

# 4. Installer les dépendances PHP
composer install

# 5. Installer les dépendances frontend
npm install

# 6. Compiler les assets CSS
npm run build

# 7. Créer la base de données et exécuter les migrations
php migrations/migrate.php

# 8. Configurer le virtual host Apache vers /public
```

## Structure du projet

```
leseditionsvariable/
├── app/
│   ├── Controllers/       # Contrôleurs MVC
│   ├── Models/            # Modèles (accès base de données)
│   ├── Views/             # Templates PHP
│   ├── Middleware/         # Middleware (auth, CSRF, etc.)
│   └── Helpers/           # Fonctions utilitaires
├── config/                # Fichiers de configuration
├── public/                # Point d'entrée web (index.php, assets)
│   ├── css/
│   ├── js/
│   └── images/
├── storage/               # Fichiers uploadés (livres, couvertures)
│   ├── books/
│   ├── covers/
│   └── uploads/
├── migrations/            # Scripts de migration SQL
├── logs/                  # Journaux d'application
├── cache/                 # Fichiers de cache
├── .env.example           # Modèle de configuration
├── PROJET.md              # Résumé du projet
├── ROADMAP.md             # Plan de développement
└── README.md              # Ce fichier
```

## Licence

Projet propriétaire — Les Éditions Variable. Tous droits réservés.
