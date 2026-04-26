-- =============================================================================
-- Migration 007 — Chat custom maison (bot mots-clés + admin)
-- =============================================================================

-- Conversations (utilisateurs connectés ET visiteurs anonymes via session_id)
CREATE TABLE IF NOT EXISTS chat_conversations (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED DEFAULT NULL,
    session_id VARCHAR(64) NOT NULL,
    visitor_email VARCHAR(255) DEFAULT NULL,
    visitor_name VARCHAR(100) DEFAULT NULL,
    statut ENUM('ouverte', 'en_attente_admin', 'repondue', 'archivee') NOT NULL DEFAULT 'ouverte',
    last_message_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    has_unread_for_admin TINYINT(1) NOT NULL DEFAULT 0,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_session (session_id),
    INDEX idx_user (user_id),
    INDEX idx_unread (has_unread_for_admin),
    INDEX idx_last_msg (last_message_at),
    INDEX idx_statut (statut),
    CONSTRAINT fk_chat_conv_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Messages (visiteur, user connecté, bot ou admin)
CREATE TABLE IF NOT EXISTS chat_messages (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    conversation_id INT UNSIGNED NOT NULL,
    sender_type ENUM('visiteur', 'user', 'bot', 'admin') NOT NULL,
    sender_user_id INT UNSIGNED DEFAULT NULL,
    content TEXT NOT NULL,
    is_bot_response TINYINT(1) NOT NULL DEFAULT 0,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_conv (conversation_id),
    INDEX idx_created (created_at),
    CONSTRAINT fk_chat_msg_conv FOREIGN KEY (conversation_id) REFERENCES chat_conversations(id) ON DELETE CASCADE,
    CONSTRAINT fk_chat_msg_user FOREIGN KEY (sender_user_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Réponses pré-écrites du bot (matching mots-clés)
CREATE TABLE IF NOT EXISTS chat_responses (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    keywords TEXT NOT NULL,
    question TEXT NOT NULL,
    answer TEXT NOT NULL,
    category VARCHAR(50) DEFAULT NULL,
    actif TINYINT(1) NOT NULL DEFAULT 1,
    times_used INT UNSIGNED NOT NULL DEFAULT 0,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_actif (actif),
    INDEX idx_category (category)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Ajout colonne source à newsletter_subscribers (pour tracer inscriptions via chat)
ALTER TABLE newsletter_subscribers
    ADD COLUMN source VARCHAR(50) NOT NULL DEFAULT 'website' AFTER prenom,
    ADD INDEX idx_source (source);

-- =============================================================================
-- SEED — 32 réponses initiales du bot
-- Convention : le PREMIER mot-clé de la liste vaut +2 points (priorité),
-- les suivants valent +1 chacun. Threshold pour matcher = score >= 2.
-- =============================================================================

INSERT INTO chat_responses (keywords, question, answer, category) VALUES
-- Salutations
('bonjour,salut,coucou,hello,bonsoir,hi,hey,yo', 'Salutation', 'Salut ! 👋 Comment puis-je t''aider aujourd''hui ?', 'salutations'),
('merci,thanks,thx,thank you', 'Remerciement', 'Avec plaisir ! 😊 N''hésite pas si tu as d''autres questions.', 'salutations'),
('au revoir,bye,a plus,a bientot,ciao', 'Au revoir', 'À bientôt ! On reste là si tu as d''autres questions. 👋', 'salutations'),
('aide,help,support,assistance,besoin aide', 'Demande d''aide', 'Bien sûr ! Dis-moi ce qui se passe et je t''oriente. Je peux répondre à plein de choses, sinon Angello prend le relais.', 'salutations'),
('qui es tu,qui est angello,equipe,fondateur,patron', 'Qui est Angello', 'Angello Muhiya est le fondateur de Variable. Si je ne sais pas répondre, c''est lui qui te répond directement (souvent en quelques minutes).', 'salutations'),
('contact,contacter,nous joindre,vous joindre,email contact', 'Contact', 'Tu peux nous écrire ici via le chat, ou par email à <a href="mailto:contact@leseditionsvariable.com">contact@leseditionsvariable.com</a>.', 'salutations'),
('horaires,heure ouverture,disponible quand,quand reponse', 'Horaires', 'Réponses humaines : <strong>lun-sam 8h-19h (Kinshasa)</strong>. Le bot répond 24/7 aux questions courantes.', 'salutations'),

-- Abonnements
('abonnement,combien,prix abonnement,coute abonnement,tarif abonnement,formule', 'Combien coûte l''abonnement', 'On a 3 formules :<br><strong>• Essentiel Mensuel</strong> — 3$/mois<br><strong>• Essentiel Annuel</strong> — 30$/an (économise 6$)<br><strong>• Premium</strong> — 8$/mois (livres premium inclus)<br><br>Plus de détails : <a href="/abonnement">Voir les abonnements</a>', 'abonnement'),
('annuler,resilier,arreter abonnement,stop abonnement,desabonner', 'Annuler abonnement', 'Tu peux annuler ton abonnement à tout moment depuis <a href="/mon-compte/abonnement">Mon compte → Abonnement</a>. L''accès reste actif jusqu''à la fin de la période payée.', 'abonnement'),
('difference essentiel premium,essentiel premium,quelle difference,quelle formule choisir,comparer formules', 'Différence Essentiel/Premium', 'L''<strong>Essentiel</strong> donne accès au catalogue standard. Le <strong>Premium</strong> ajoute les livres premium (inédits, gros titres) et les avant-premières.', 'abonnement'),
('renouvellement,renouvelle,paiement automatique,reabonnement', 'Renouvellement', 'Stripe se renouvelle automatiquement chaque mois/an. Money Fusion (mobile RDC) est manuel : tu reçois un rappel avant l''expiration.', 'abonnement'),
('changer formule,changer abonnement,upgrade,passer premium', 'Changer de formule', 'Tu peux changer de formule à tout moment depuis <a href="/mon-compte/abonnement">Mon compte → Abonnement</a>. Le prorata est calculé automatiquement.', 'abonnement'),

-- Achat unitaire
('acheter livre,achat livre,prix livre,combien coute livre', 'Comment acheter un livre', 'Va sur la fiche du livre, clique "Acheter", choisis Stripe (carte) ou Money Fusion (mobile RDC). Le livre apparaît dans ta bibliothèque immédiatement.', 'achat'),
('part auteur achat,combien touche auteur,royalties,redistribution achat', 'Part auteur sur achat', 'Sur chaque vente unitaire, l''auteur reçoit <strong>70%</strong> du prix (après frais de paiement). Le reste finance la plateforme et la promotion.', 'achat'),
('mode paiement,modes paiement,quel paiement,carte,mobile money,mpesa,airtel', 'Modes de paiement', 'On accepte :<br>• <strong>Stripe</strong> — cartes Visa/Mastercard (international)<br>• <strong>Money Fusion</strong> — Mobile Money (RDC : M-Pesa, Airtel, Orange)', 'achat'),
('rembourser,remboursement,refund,reprendre argent', 'Remboursement', 'Pour un livre acheté par erreur ou un problème technique, écris-nous ici. On regarde au cas par cas.', 'achat'),

-- Services éditoriaux
('relecture,correction,corriger texte,relire manuscrit', 'Service de relecture', 'On propose relecture, correction, mise en page et accompagnement éditorial. Tarifs : <a href="/services-editoriaux">Voir les services éditoriaux</a>', 'services'),
('publier livre,faire publier,vous publiez,editeur', 'Publier mon livre', 'Pour soumettre ton livre : candidate via <a href="/auteur/candidater">/auteur/candidater</a>. On revient vers toi sous 7-14 jours après lecture du manuscrit.', 'services'),
('mise en page,maquette,couverture,design livre', 'Mise en page', 'Mise en page intérieur + couverture : tarif selon le nombre de pages. Devis personnalisé : <a href="/services-editoriaux">Services éditoriaux</a>', 'services'),
('isbn,depot legal,enregistrement livre', 'ISBN / Dépôt légal', 'Oui on s''occupe de l''ISBN et du dépôt légal pour les livres qu''on publie. Inclus dans l''accompagnement éditorial.', 'services'),

-- Compte
('creer compte,inscription,inscrire,creation compte', 'Créer un compte', 'Crée ton compte ici : <a href="/inscription">/inscription</a>. C''est gratuit et ça prend 30 secondes.', 'compte'),
('supprimer compte,fermer compte,delete compte,effacer compte', 'Supprimer mon compte', 'Tu peux supprimer ton compte depuis <a href="/mon-compte/parametres">Mon compte → Paramètres</a>. La suppression est définitive après confirmation par email.', 'compte'),
('mot de passe oublie,perdu mot de passe,reinitialiser,reset password', 'Mot de passe oublié', 'Pas de panique : <a href="/mot-de-passe-oublie">Réinitialise ton mot de passe</a>. Tu recevras un lien par email.', 'compte'),
('changer email,modifier email,changer adresse,nouvel email', 'Changer mon email', 'Tu peux changer ton email depuis <a href="/mon-compte/parametres">Mon compte → Paramètres</a>. Une vérification par email te sera demandée.', 'compte'),

-- Lecture / Liseuse
('format,formats,quel format,epub,pdf,ebook', 'Formats supportés', 'Les livres sont lus directement dans la liseuse Variable (PDF.js sécurisé). Pas de téléchargement direct, lecture page par page.', 'lecture'),
('telecharger livre,telecharger,download,offline,hors ligne', 'Télécharger un livre', 'Pour des raisons de protection des auteurs, les livres ne sont pas téléchargeables. Tu peux les lire à volonté en ligne dans la liseuse.', 'lecture'),
('extrait,apercu,gratuit,echantillon,decouvrir livre', 'Extrait gratuit', 'Tous les livres ont un extrait gratuit (10 premières pages). Clique "Aperçu" sur la fiche du livre.', 'lecture'),
('partager livre,recommander livre,offrir livre', 'Partager un livre', 'Pour offrir un livre à quelqu''un, partage simplement le lien. Pour l''instant pas de système cadeau direct, mais c''est en réflexion.', 'lecture'),

-- Devenir auteur
('devenir auteur,etre auteur,je veux ecrire,publier mon roman', 'Devenir auteur', 'Candidate via <a href="/auteur/candidater">/auteur/candidater</a>. Soumets ton manuscrit, ta bio et un extrait. Réponse sous 7-14 jours.', 'auteur'),
('delai validation,combien temps reponse,quand reponse manuscrit', 'Délai de validation', 'Validation candidature : 7-14 jours. Mise en ligne après acceptation : 2-4 semaines (selon mise en page nécessaire).', 'auteur'),
('manuscrit refuse,refus,non accepte,non publie', 'Manuscrit refusé', 'Si on refuse, on t''envoie un retour détaillé. Tu peux soumettre un nouveau manuscrit ensuite. On encourage à retravailler et retenter.', 'auteur'),

-- Technique
('paiement echec,paiement echoue,carte refusee,probleme paiement,bug paiement', 'Paiement échoué', 'Si ton paiement échoue, vérifie ton solde et ressaye. Pour Money Fusion : confirme bien le SMS sur ton téléphone. Si ça persiste, écris-nous ici, on règle ça.', 'technique'),
('bug,erreur,probleme technique,marche pas,plante', 'Problème technique', 'Décris ton souci ici (page concernée + ce qui se passe), on regarde tout de suite.', 'technique'),
('email pas recu,pas recu email,email confirmation,verification email', 'Email pas reçu', 'Vérifie tes spams/courrier indésirable. Si toujours rien, dis-le-nous ici avec ton adresse, on relance.', 'technique');
