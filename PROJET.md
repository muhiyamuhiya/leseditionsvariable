# Les Éditions Variable

## Objectif

Les Éditions Variable est une maison d'édition numérique fondée par Angello Luvungu Muhiya, basée à Kinshasa (RDC) et Trois-Rivières (Canada). La plateforme web **leseditionsvariable.com** a pour mission de rendre la littérature africaine francophone accessible au plus grand nombre, en offrant aux auteurs un espace de publication professionnel et aux lecteurs une expérience de lecture numérique moderne.

## Stack technique

Le projet repose sur une architecture PHP 8.2+ pure (sans framework) suivant un pattern MVC maison, couplée à une base de données MySQL 8. Le frontend utilise Tailwind CSS pour le design et Alpine.js pour l'interactivité. La liseuse intégrée s'appuie sur PDF.js. L'ensemble est conçu pour un hébergement cPanel classique (NitroHost) sans dépendance à des services cloud propriétaires.

## Cible et modèle économique

La plateforme cible les lecteurs d'Afrique francophone et de la diaspora (RDC en priorité, puis Sénégal, Côte d'Ivoire, Mali, Burkina, Cameroun, France, Belgique, Canada). Le modèle économique repose sur plusieurs sources de revenus combinées :

1. **Vente unitaire d'ebooks** : prix libre fixé par l'auteur. Variable prélève 20% de commission, l'auteur garde 80%.

2. **Abonnement lecteur** : 3 $/mois ou 30 $/an pour un accès illimité au catalogue. Un abonnement Premium à 8 $/mois inclut en plus un livre physique livré chaque trimestre en RDC.

3. **Pool de redistribution aux auteurs** : 50% des revenus d'abonnement sont redistribués aux auteurs chaque mois, au prorata des pages lues de leurs livres par les abonnés. Formule : revenu auteur = pool mensuel × (pages lues de ses livres / pages lues totales plateforme).

4. **Services payants aux auteurs** : mise en page, correction, conception de couverture, packs promotion (150 $ à 500 $ selon le pack).

Les paiements sont gérés via Money Fusion pour la RDC et les pays utilisant Mobile Money, et via Stripe pour les transactions internationales par carte. Les versements aux auteurs sont effectués trimestriellement avec un seuil minimum de 20 $ accumulés.
