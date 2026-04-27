# Activation Money Fusion sur Variable

Ce document décrit la procédure pour activer les paiements **Mobile Money** (RDC, Sénégal, etc.) via Money Fusion sur la prod `leseditionsvariable.com`.

## État du code

L'intégration Money Fusion est **déjà codée** côté Variable (cf. audit du 27/04/2026) :

- `app/lib/PaymentConfig.php` — config + helpers `isMoneyFusionConfigured()`
- `app/controllers/PaymentController.php` — 5 méthodes : `payWithMoneyFusion`, `subscriptionMoneyFusion`, `payEditorialMoneyFusion`, `moneyFusionReturn`, `moneyFusionWebhook`
- `app/views/payment/choose-method.php` — sélecteur Stripe / MF
- `app/views/payment/moneyfusion-return.php` — page de retour utilisateur
- Routes existantes (cf. `public_html/index.php`)

**Sécurité** : le webhook MF re-query l'API authentique pour confirmer le statut (cf. commit `21a47ea`). Aucun fulfill ne se déclenche sur la base d'un payload POST seul.

## Checklist d'activation prod

### 1. Côté Money Fusion (dashboard marchand)

| Champ | Valeur |
|---|---|
| **URL de retour utilisateur** | `https://leseditionsvariable.com/paiement/moneyfusion/retour` |
| **URL de notification (webhook)** | `https://leseditionsvariable.com/webhook/moneyfusion` |
| **IP serveur à whitelister** | `95.217.84.98` (Hetzner — serveur cPanel NitroHost, vérifiée le 2026-04-27) |

> Si Money Fusion ne te demande pas d'IP : ignore ce champ. Si oui : la valeur est l'IP publique de ton hébergement cPanel. Pour la vérifier, en SSH : `curl -s https://api.ipify.org`

### 2. Côté Variable (`.env` prod)

Sur cPanel → Gestionnaire de fichiers → `.env`, remplir :

```
MONEYFUSION_API_URL=https://pay.moneyfusion.net/<NomMarchand>/<TokenMarchand>/pay/
MONEYFUSION_WEBHOOK_TOKEN=
```

- `MONEYFUSION_API_URL` : récupère-la dans le dashboard MF, section **"Mes liens de paiement"**. Format : `https://pay.moneyfusion.net/<NomMarchand>/<TokenMarchand>/pay/`
- `MONEYFUSION_WEBHOOK_TOKEN` : optionnel. Le webhook est déjà sécurisé par re-query de l'API MF, ce token n'est pas utilisé actuellement. Le garder vide.

### 3. Tester en bout-en-bout (paiement réel à 1 USD)

1. Sur la prod, **connecter un compte test** (lecteur)
2. Acheter un livre à **1 $** (créer un livre temporaire si nécessaire)
3. Choisir "**Mobile Money**" sur la page de paiement
4. Faire le paiement avec un compte M-Pesa / Airtel Money de test
5. Au retour, l'URL doit être `https://leseditionsvariable.com/paiement/moneyfusion/retour?tokenPay=...`
6. Vérifier en DB :
   ```sql
   SELECT id, statut, prix_paye_usd, methode_paiement, transaction_id
     FROM sales WHERE methode_paiement = 'money_fusion'
     ORDER BY id DESC LIMIT 1;
   -- statut doit être 'payee'
   
   SELECT id, source, date_ajout
     FROM user_books WHERE source = 'achat_unitaire'
     ORDER BY id DESC LIMIT 1;
   -- doit avoir une ligne pour le user/book
   ```
7. Vérifier que le **reçu PDF** est arrivé dans la boîte du compte test (et BCC dans la boîte admin)

### 4. Tester l'attaque (vérifier le fix sécurité)

Depuis n'importe où, lancer :

```bash
curl -X POST https://leseditionsvariable.com/webhook/moneyfusion \
  -H "Content-Type: application/json" \
  -d '{"tokenPay":"FAKE","statut":"paid","personal_Info":[{"userId":1,"bookId":1,"type":"book_purchase"}]}'
```

Réponse attendue : **HTTP 200** (pour ne pas divulguer aux attaquants si le token existe). En DB, **aucun row** dans `sales` ni `user_books`. Dans `logs/error.log` :

```
MF webhook REFUSÉ — verify failed pour token=FAKE | IP=... | UA=curl/...
```

Si tu vois cette ligne, le fix sécurité est bien actif.

## Logs & monitoring

- **Webhooks refusés** : `tail -f logs/error.log | grep "MF webhook"` — toute tentative d'attaque y apparaît avec IP + user-agent
- **Webhooks acceptés** : pas de log spécifique, mais visible dans `transactions_log` (provider='money_fusion', statut='reussi')
- **Pings de reconnaissance** : si tu vois beaucoup d'IPs différentes dans `logs/error.log`, ajouter du rate-limiting via `.htaccess` ou via fail2ban côté serveur

## Désactiver temporairement

Si tu veux désactiver Mobile Money sans toucher au code (ex: maintenance, problème côté MF) :

```
# .env prod
MONEYFUSION_API_URL=
```

→ `isMoneyFusionConfigured()` retourne `false`. Les boutons "Mobile Money" affichent "temporairement indisponible". Stripe continue normalement.

## Références

- **Helper de vérification** : `PaymentController::verifyMoneyFusionStatus()` re-query `https://www.pay.moneyfusion.net/paiementNotif/{token}` et retourne `null` si invalide.
- **Page de retour** : `PaymentController::moneyFusionReturn` utilise le même helper indirectement (logique dupliquée à factoriser un jour, hors scope actuel).
- **Sale insert** : `PaymentController::fulfillBookPurchase` est idempotent via `user_books` UNIQUE (user_id, book_id) — pas de double insert même si le webhook est appelé plusieurs fois (MF en envoie souvent 2-3 pour un même paiement).
