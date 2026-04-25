# Guide de déploiement — Les éditions Variable sur NitroHost cPanel

**Dernière révision** : 25 avril 2026

---

## Pré-requis

- Accès cPanel NitroHost (URL cPanel + identifiants)
- Domaine `leseditionsvariable.com` configuré, DNS pointant vers NitroHost
- Compte Resend opérationnel + clé API
- Compte Stripe (mode TEST pour la première mise en ligne)
- Compte Money Fusion (en attente — peut être configuré plus tard)
- Archive de déploiement : `leseditionsvariable_deployment.zip` (à créer en suivant **Étape 0**)

---

## Étape 0 — Préparer l'archive de déploiement

Sur ta machine locale, depuis le dossier projet :

```bash
cd /Users/muhiy/Documents/leseditionsvariable

# Construire un zip propre, sans le superflu
zip -r leseditionsvariable_deployment.zip . \
    -x ".git/*" "node_modules/*" "*.DS_Store" \
       "logs/*.log" "storage/editorial/uploads/*" "storage/editorial/deliveries/*" \
       ".env" "tests/*" "*.zip" \
       "AUDIT_REPORT.md" "DEPLOYMENT_GUIDE.md"
```

L'archive contient :
- `app/` (code applicatif PHP)
- `public_html/` (entry point + assets + .htaccess)
- `bootstrap.php`
- `vendor/` (dépendances Composer dont Stripe SDK)
- `database/schema_full.sql` + `database/migrations/` + `database/seeds/` + `database/cron/`
- `storage/` (avec sous-dossiers `.gitkeep`)
- `.env.production.template`

---

## Étape 1 — Créer la base de données

1. cPanel → **Bases de données** → **Bases de données MySQL**
2. **Créer nouvelle base** : nom suggéré `elesediti_variable` (le préfixe est ton identifiant cPanel)
3. **Créer nouvel utilisateur MySQL** avec mot de passe fort (16+ caractères, mix lettres/chiffres/symboles)
4. **Ajouter l'utilisateur à la base** → cocher **TOUS LES PRIVILÈGES**
5. **Noter** :
   - `DB_NAME` = `xxxxxxxx_variable`
   - `DB_USER` = `xxxxxxxx_variable_user`
   - `DB_PASSWORD` = `********`

---

## Étape 2 — Importer le schéma SQL

1. cPanel → **phpMyAdmin**
2. Sélectionner la base `xxxxxxxx_variable` créée à l'étape 1 (panneau gauche)
3. Onglet **Importer**
4. Choisir le fichier `database/schema_full.sql` (à extraire du zip ou uploader directement)
5. **Format** : SQL — laisser les options par défaut
6. Cliquer **Exécuter**
7. Vérifier que **20 tables** ont été créées : `users`, `books`, `authors`, `subscriptions`, `editorial_orders`, `notifications`, `newsletter_subscribers`, etc.

---

## Étape 3 — Upload du code via Gestionnaire de fichiers

### Option A — Tout dans `public_html/` (cPanel mutualisé classique)

C'est la voie standard NitroHost. Le `.htaccess` à la racine de `public_html/` protège les fichiers privés (`app/`, `database/`, `vendor/`, `bootstrap.php`, `.env`).

1. cPanel → **Fichiers** → **Gestionnaire de fichiers**
2. Naviguer vers `/home/USERNAME/public_html/`
3. Si le dossier contient des fichiers (`index.html`, `cgi-bin`, etc.) : sélectionner et **archiver** dans `_backup_initial_$(date).zip`, puis supprimer
4. **Upload** `leseditionsvariable_deployment.zip`
5. Clic droit sur le zip → **Extraire** → destination `/home/USERNAME/public_html/`
6. La structure finale doit être :

```
/home/USERNAME/public_html/
├── app/                    # code applicatif (PROTÉGÉ par .htaccess)
├── public_html/            # entry point — voir Note ci-dessous
│   ├── index.php
│   ├── .htaccess
│   └── assets/
├── bootstrap.php           # PROTÉGÉ
├── vendor/                 # PROTÉGÉ
├── database/               # PROTÉGÉ
├── storage/                # PROTÉGÉ (uploads runtime)
├── .env.production.template
└── (plus tard).env         # PROTÉGÉ — à créer manuellement
```

> **Note importante** : avec cette structure, le projet a un dossier `public_html/` à l'intérieur de `public_html/`. C'est inhabituel mais ça marche : le `.htaccess` de second niveau (dans le `public_html/` interne) catch les requêtes et les route via `index.php`.
>
> **Alternative plus propre (recommandée)** : voir Option B ci-dessous.

### Option B — Document Root pointant directement sur `public_html/` interne (recommandée)

Si NitroHost permet de configurer le DocumentRoot :

1. cPanel → **Domaines** → **Domaines complémentaires** ou **Sous-domaines**
2. Pour `leseditionsvariable.com`, modifier le **Document Root** vers `/home/USERNAME/public_html/leseditionsvariable/public_html`
3. Upload le zip dans `/home/USERNAME/public_html/leseditionsvariable/` au lieu de `public_html/` directement
4. La structure devient :

```
/home/USERNAME/public_html/leseditionsvariable/
├── app/                    # à l'extérieur du DocumentRoot — sécurité maximale
├── public_html/            # <- DocumentRoot (publique)
│   ├── index.php
│   ├── .htaccess
│   └── assets/
├── bootstrap.php
├── vendor/
├── database/
├── storage/
└── .env
```

Avec Option B, les fichiers sensibles (`.env`, `app/`, `vendor/`) sont **physiquement inaccessibles via HTTP**. C'est la meilleure pratique. Si tu peux configurer le DocumentRoot, fais-le.

---

## Étape 4 — Configurer `.env`

1. Gestionnaire de fichiers → naviguer vers la racine du projet (où se trouve `.env.production.template`)
2. **Copier** `.env.production.template` → renommer la copie en `.env`
3. Éditer `.env` (clic droit → **Éditer**) et remplir :

```ini
APP_ENV=production
APP_DEBUG=false
APP_URL=https://leseditionsvariable.com
APP_URL_PUBLIC=https://leseditionsvariable.com

DB_HOST=localhost
DB_PORT=3306
DB_NAME=xxxxxxxx_variable          # depuis Étape 1
DB_USER=xxxxxxxx_variable_user     # depuis Étape 1
DB_PASSWORD=ton_password_fort      # depuis Étape 1

# Stripe — TEST keys depuis dashboard.stripe.com (toggle en haut)
STRIPE_PUBLIC_KEY=pk_test_...
STRIPE_SECRET_KEY=sk_test_...
STRIPE_WEBHOOK_SECRET=whsec_...    # à configurer après Étape 6

# Money Fusion — laisser vide pour l'instant si non validé
MONEYFUSION_API_URL=
MONEYFUSION_WEBHOOK_TOKEN=

# Resend — depuis resend.com/api-keys
RESEND_API_KEY=re_...
MAIL_FROM_EMAIL=contact@leseditionsvariable.com
MAIL_FROM_NAME="Les éditions Variable"
```

4. Sauvegarder
5. **Permissions** : `.env` doit être à `644` (lecture pour l'utilisateur Apache, pas exécutable)

---

## Étape 5 — Permissions des dossiers

Via Gestionnaire de fichiers ou SSH :

```bash
# Dossiers en écriture pour le serveur web
chmod 755 storage storage/users storage/covers storage/books storage/extracts storage/editorial storage/editorial/uploads storage/editorial/deliveries
chmod 755 logs

# Fichiers .env et bootstrap : lecture seule
chmod 644 .env bootstrap.php

# Dossiers de code : lecture seule pour le web
chmod -R 755 app public_html vendor database
```

---

## Étape 6 — Configurer le webhook Stripe

1. Connecte-toi à [dashboard.stripe.com](https://dashboard.stripe.com)
2. **Mode TEST activé** (toggle en haut à gauche)
3. **Developers** → **Webhooks** → **Add endpoint**
4. URL : `https://leseditionsvariable.com/webhook/stripe`
5. Events à écouter :
   - `checkout.session.completed`
   - `payment_intent.succeeded`
   - `payment_intent.payment_failed`
6. Cliquer **Add endpoint**
7. Récupérer le **Signing secret** (`whsec_...`)
8. Le coller dans `.env` → `STRIPE_WEBHOOK_SECRET=...`

---

## Étape 7 — Configurer Resend

1. Connecte-toi à [resend.com](https://resend.com)
2. **Domains** → **Add Domain** → `leseditionsvariable.com`
3. Ajouter les enregistrements DNS chez ton registrar :
   - **SPF** (TXT) : `v=spf1 include:amazonses.com ~all`
   - **DKIM** (3 enregistrements CNAME fournis par Resend)
   - **MX** : conserver tes MX existants si tu reçois déjà des emails sur ce domaine
4. **Vérifier le domaine** dans Resend (peut prendre 5-30 min après propagation DNS)
5. **API Keys** → **Create API Key** → permission `Sending access`
6. Copier la clé `re_...` dans `.env` → `RESEND_API_KEY=...`

---

## Étape 8 — Tests fonctionnels

1. **Page d'accueil** : ouvrir https://leseditionsvariable.com → doit charger sans erreur
2. **Inscription** : créer un compte test → tu dois recevoir un email de vérification (vérifier avec un email réel, pas un alias)
3. **Connexion** : confirmer l'email puis se connecter
4. **Catalogue** : `/catalogue` doit afficher les livres importés
5. **Achat** : utiliser une carte Stripe TEST `4242 4242 4242 4242`, expiration future, CVC quelconque → vérifier que la commande passe en `reussi` dans `transactions_log`
6. **Webhook Stripe** : vérifier dans `dashboard.stripe.com → Webhooks → leseditionsvariable.com/webhook/stripe → Logs` que les events sont bien reçus avec status `200`
7. **Page admin** (`/admin`) : se connecter avec le compte admin (créé via `database/seeds/create_admin.php` ou manuellement en DB) et vérifier que tout charge

---

## Étape 9 — Activer HTTPS forcé

Une fois le site fonctionnel en HTTP (et que tu as un certificat SSL Let's Encrypt actif via cPanel) :

1. Éditer `public_html/.htaccess`
2. Décommenter les lignes :
```apache
RewriteCond %{HTTPS} off
RewriteRule ^(.*)$ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]
```
3. Sauvegarder. Tous les `http://` redirigent maintenant en 301 vers `https://`

---

## Étape 10 — Cron (optionnel pour MVP)

Pour activer la notification d'expiration d'abonnement :

1. cPanel → **Cron Jobs** → **Standard**
2. Ajouter une tâche **quotidienne à 9h** :
```
/usr/local/bin/php /home/USERNAME/public_html/database/cron/notify_expiring_subscriptions.php
```

---

## Sécurité finale — checklist avant ouverture publique

- [ ] `.env` permissions `644` (pas `755`, pas `777`)
- [ ] HTTPS forcé activé
- [ ] `APP_DEBUG=false` dans `.env`
- [ ] `.env` n'apparaît pas dans `https://leseditionsvariable.com/.env` (test : doit retourner 403 ou 404)
- [ ] `bootstrap.php` n'apparaît pas en HTTP (idem)
- [ ] `app/`, `vendor/`, `database/`, `storage/` non accessibles directement
- [ ] Webhook Stripe configuré et reçoit bien les events (vérifier les logs Stripe)
- [ ] Resend domaine vérifié et un email de test reçu
- [ ] Compte admin créé et connecté avec succès
- [ ] Couvertures de livres compressées (8 MB → 1 MB déjà fait avec `compress_images.php`)

---

## Bascule mode LIVE Stripe (à faire plus tard)

Quand tu es prêt à prendre des vrais paiements :

1. Stripe dashboard → **Activer le compte** (vérification d'identité, RIB, etc.)
2. Récupérer les clés `pk_live_...` et `sk_live_...` (toggle en haut)
3. Créer un nouveau webhook en mode LIVE pointant vers la même URL `/webhook/stripe`
4. Récupérer le nouveau `whsec_...` LIVE
5. Mettre à jour `.env` :
   - `STRIPE_PUBLIC_KEY` → `pk_live_...`
   - `STRIPE_SECRET_KEY` → `sk_live_...`
   - `STRIPE_WEBHOOK_SECRET` → `whsec_...` (LIVE)
6. **Faire un achat test** avec une vraie carte (le rembourser ensuite via Stripe dashboard)

---

## Activation Money Fusion (à faire plus tard)

Quand Money Fusion valide ton compte marchand :

1. Récupérer `MONEYFUSION_API_URL` et `MONEYFUSION_WEBHOOK_TOKEN` depuis ton dashboard MF
2. Mettre à jour `.env` avec ces valeurs
3. Configurer dans le dashboard MF :
   - `callback_url` : `https://leseditionsvariable.com/paiement/moneyfusion/retour`
   - `webhook_url` : `https://leseditionsvariable.com/webhook/moneyfusion`
4. Test avec un montant minimal (1 USD ou équivalent CDF)

---

## Support et debug

- **Logs erreurs PHP** : cPanel → **Métriques** → **Erreurs**
- **Logs application** : `/home/USERNAME/public_html/logs/`
- **Logs Resend** : dashboard.resend.com → **Logs**
- **Logs Stripe** : dashboard.stripe.com → **Developers** → **Logs**

En cas de problème : activer temporairement `APP_DEBUG=true` dans `.env` (et **bien le repasser à `false` après**), ou consulter `error_log` côté cPanel.

---

*Bonne mise en ligne ! 🚀*
