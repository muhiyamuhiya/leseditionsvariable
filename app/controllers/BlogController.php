<?php
namespace App\Controllers;

/**
 * Blog statique — articles hardcodés (pas de DB, MVP).
 * Migration vers un CMS quand le volume justifie.
 */
class BlogController extends BaseController
{
    /**
     * Liste des articles. Les plus récents en premier.
     */
    private function articles(): array
    {
        return [
            'pourquoi-nous-avons-cree-variable' => [
                'slug'    => 'pourquoi-nous-avons-cree-variable',
                'title'   => 'Pourquoi nous avons créé Les éditions Variable',
                'author'  => 'Angello Luvungu Muhiya',
                'date'    => '2026-04-25',
                'excerpt' => "L'histoire de la maison d'édition née à Kinshasa qui veut changer la donne pour les auteurs africains.",
                'content' => <<<HTML
<p>J'ai grandi à Kikwit, dans une maison où on lisait. Ma mère gardait dans une malle quelques romans rapportés de Kinshasa. Mon père, lui, lisait le journal en buvant son café du matin. Mais les livres, les vrais, ceux qui te font rêver, étaient rares. Une bibliothèque municipale qui fermait souvent. Une seule librairie en ville qui vendait les manuels scolaires plus que les romans.</p>

<p>Quand j'ai commencé à écrire — d'abord des poèmes maladroits sur des feuilles arrachées, puis des nouvelles, puis ce premier livre qui s'appelle <em>« Je n'ai pas choisi ma naissance, mais j'ai choisi mon destin »</em> — j'ai compris une chose : je ne saurais jamais qui me lirait. L'édition traditionnelle, depuis Kinshasa, c'est presque impossible. Les grands éditeurs sont à Paris ou Dakar. Et même quand on y arrive, on touche 5 à 10% du prix de vente. Le reste va aux intermédiaires.</p>

<h2>Le déclic</h2>

<p>L'idée de Variable m'est venue à Trois-Rivières, au Québec, où je poursuis mes études en communication sociale. J'étais dans un café, je lisais un livre canadien sur ma tablette, j'ai pensé : <em>pourquoi un jeune de Kinshasa ne pourrait-il pas faire pareil ?</em> Pourquoi est-ce que la lecture serait un luxe, alors qu'on a tous un téléphone dans la poche ?</p>

<p>Et en parallèle, pourquoi est-ce qu'un auteur de Bukavu, de Yaoundé ou de Bamako devrait attendre trois ans qu'un éditeur parisien daigne le lire, pour ensuite toucher des miettes ?</p>

<h2>Ce qu'on construit</h2>

<p>Variable n'est pas une plateforme américaine traduite en français. C'est une plateforme pensée pour nous, par nous.</p>

<p>D'abord, le prix : 3 USD par mois pour la lecture illimitée. C'est moins qu'une bière en terrasse à Kinshasa. Moins qu'un café à Trois-Rivières. C'est délibérément pensé pour être accessible.</p>

<p>Ensuite, le paiement : Mobile Money en priorité. Carte bancaire pour la diaspora. Pas besoin d'un compte américain ou d'un PayPal compliqué.</p>

<p>Enfin, et c'est ce qui me tient le plus à cœur, la rémunération des auteurs. Chez Variable, l'auteur touche <strong>80% du prix de vente</strong>. C'est l'inverse exact de l'édition traditionnelle. On garde 20% pour faire fonctionner la maison. Pas plus. Pas de marketing tape-à-l'œil. Pas de bureaux à Paris.</p>

<h2>Et maintenant ?</h2>

<p>On vient de lancer. La plateforme est jeune. Quelques livres, quelques auteurs, beaucoup de chemin à faire. Mais l'ambition est claire : devenir, dans les cinq prochaines années, la plus grande bibliothèque numérique de littérature africaine francophone.</p>

<p>Si tu es lecteur, abonne-toi. Tu finances directement nos auteurs.</p>

<p>Si tu es auteur, candidate. On lit toutes les soumissions, sérieusement, en moins de sept jours.</p>

<p>Et si tu crois, comme moi, que la voix africaine mérite d'être entendue partout dans le monde — alors on est sur le même bateau. Bienvenue à bord.</p>
HTML
            ],

            'litterature-africaine-francophone-merite-mieux' => [
                'slug'    => 'litterature-africaine-francophone-merite-mieux',
                'title'   => '5 raisons pour lesquelles la littérature africaine francophone mérite mieux',
                'author'  => 'Rédaction Variable',
                'date'    => '2026-04-20',
                'excerpt' => "De Sembene Ousmane à Véronique Tadjo, en passant par les voix contemporaines de Kinshasa, Dakar et Abidjan, notre littérature a tout pour rayonner.",
                'content' => <<<HTML
<p>La littérature africaine francophone est l'un des secrets les mieux gardés du monde du livre. Voici cinq raisons pour lesquelles elle mérite mieux que le silence dans lequel elle est trop souvent cantonnée.</p>

<h2>1. Elle a une histoire profonde</h2>

<p>Sembene Ousmane, Mariama Bâ, Ahmadou Kourouma, Aminata Sow Fall, Sony Labou Tansi. Ce ne sont pas des noms de seconde zone. Ce sont des piliers de la littérature mondiale du XXᵉ siècle. Ils ont écrit sur la colonisation, l'indépendance, les dictatures, les femmes, l'amour — avec une force d'écriture qu'on n'enseigne quasiment jamais dans les écoles.</p>

<h2>2. Elle est plurielle</h2>

<p>Dire « littérature africaine » est aussi vague que dire « littérature européenne ». Entre un roman ivoirien, un essai congolais et un récit sénégalais, il y a un océan de différences. Variable veut montrer cette diversité, pas l'aplatir.</p>

<h2>3. Elle est contemporaine et vibrante</h2>

<p>Fiston Mwanza Mujila, Kossi Efoui, Léonora Miano, Felwine Sarr, Véronique Tadjo, Scholastique Mukasonga, Gauz, In Koli Jean Bofane. Une génération entière d'auteurs vivants écrit sur l'Afrique d'aujourd'hui : les villes, les migrations, les corps, les croyances, l'écologie. Ils sont primés à Paris et ailleurs, mais peu lus chez eux.</p>

<h2>4. Elle est urgente politiquement</h2>

<p>Quand un auteur congolais raconte la guerre dans l'Est, quand une autrice rwandaise revisite le génocide, quand un poète sénégalais interroge le franc CFA — ce ne sont pas des sujets « exotiques ». Ce sont les sujets brûlants du monde contemporain. La littérature africaine est en première ligne.</p>

<h2>5. Elle peine économiquement</h2>

<p>Voilà la raison la plus douloureuse. Les éditeurs africains n'ont pas les moyens de leurs voisins du Nord. Les librairies ferment. Les écoles n'achètent plus. Les auteurs gagnent peu. Et pourtant, la qualité est là. C'est précisément ce que Variable veut changer : <strong>connecter cette littérature à son lectorat</strong>, sur le continent et dans la diaspora, à un prix accessible.</p>

<p>La littérature africaine francophone mérite mieux. Elle mérite des lecteurs. Elle mérite une économie. Elle mérite la place qui lui revient dans le concert des littératures mondiales.</p>

<p>Variable est une réponse. Pas la seule. Mais une réponse construite avec l'amour qu'on doit à nos textes.</p>
HTML
            ],

            'comment-publier-premier-livre-afrique' => [
                'slug'    => 'comment-publier-premier-livre-afrique',
                'title'   => 'Comment publier ton premier livre quand tu vis en Afrique',
                'author'  => 'Angello Luvungu Muhiya',
                'date'    => '2026-04-15',
                'excerpt' => "Le guide pratique : choisir son sujet, structurer son manuscrit, trouver un éditeur, négocier ses droits.",
                'content' => <<<HTML
<p>On me pose souvent la question : <em>« Angello, je veux écrire un livre. Par où je commence ? »</em>. Voici ce que j'aurais aimé qu'on me dise il y a cinq ans.</p>

<h2>Étape 1 — Choisir ton sujet, vraiment</h2>

<p>Premier piège : vouloir écrire <em>« le grand roman africain ».</em> Trop vague. Choisis un angle. Un quartier de ta ville. Une famille. Une journée. Une émotion. Le grand vient toujours du petit.</p>

<p>Pose-toi la question : <strong>qu'est-ce que tu sais que personne d'autre ne sait ?</strong> Ce que tu vis, ce que tu as vu, ce que tu as appris en grandissant ici. C'est ça ton matériau. Pas ce que tu as lu chez Camus ou chez Adichie.</p>

<h2>Étape 2 — Écrire un peu chaque jour</h2>

<p>Je sais. C'est cliché. Mais c'est vrai. 500 mots par jour, c'est 15 000 mots par mois. C'est un quart de roman en 30 jours. Personne n'écrit un livre en une fois. Tout le monde écrit un livre par petits bouts.</p>

<p>Garde un cahier près de toi. Sur ton téléphone, dans une note. Quand une idée vient, écris-la. La mémoire est cruelle.</p>

<h2>Étape 3 — Faire relire avant tout le monde</h2>

<p>Trouve trois personnes de confiance qui aiment lire. Pas tes parents. Pas ton meilleur ami qui te dira « c'est génial ». Des lecteurs honnêtes. Donne-leur ton manuscrit, demande-leur de noter ce qui les a perdus, ce qui les a ennuyés, ce qui les a fait pleurer ou rire.</p>

<p>Réécris en fonction de leurs retours. Pas tous. Les bons.</p>

<h2>Étape 4 — Trouver un éditeur (ou pas)</h2>

<p>L'édition traditionnelle reste possible. Présence Africaine, Karthala, Harmattan, Sépia, et plus localement Mabiki en RDC, Présence Africaine au Sénégal, Vallesse en Côte d'Ivoire. Envoie un manuscrit propre, une lettre d'intention courte, et arme-toi de patience : 3 à 12 mois de réponse.</p>

<p>L'auto-publication numérique est une autre voie. Sans intermédiaire. Tu gardes 80% de tes ventes (sur Variable, par exemple). Tu publies en quelques semaines. C'est moins prestigieux peut-être, mais c'est plus juste, surtout quand on commence.</p>

<h2>Étape 5 — Comprendre tes droits</h2>

<p>Lis ton contrat. Vraiment. Ne signe rien sans avoir compris :</p>
<ul>
    <li>La <strong>cession des droits</strong> : pour combien de temps, sur quels territoires, sur quels formats ?</li>
    <li>Le <strong>pourcentage</strong> qui te revient sur chaque vente.</li>
    <li>La <strong>clause de sortie</strong> : peux-tu reprendre tes droits après quelques années ?</li>
</ul>

<p>Si tu ne comprends pas, demande à un avocat ou à un auteur expérimenté. Une mauvaise signature, c'est dix ans à le regretter.</p>

<h2>Étape 6 — Promouvoir, parce que personne ne le fera pour toi</h2>

<p>L'éditeur fait un peu. Mais c'est toi qui dois faire le plus. Réseaux sociaux, podcasts, écoles, libraires. Aller sur les salons quand tu peux. Demander à des journalistes locaux de te chroniquer. Inviter tes lecteurs à laisser un avis.</p>

<p>C'est long, c'est ingrat, mais c'est ce qui transforme un livre en succès.</p>

<h2>Le mot de la fin</h2>

<p>Publier un livre depuis l'Afrique, c'est dur. Mais c'est aussi un acte politique : tu ajoutes une voix dans un concert où les nôtres sont encore trop rares. Ne te décourage pas. Le monde a besoin de ce que tu as à dire.</p>
HTML
            ],

            'modele-80-20-remunere-mieux' => [
                'slug'    => 'modele-80-20-remunere-mieux',
                'title'   => 'Le modèle 80/20 : pourquoi Variable rémunère mieux que l\'édition traditionnelle',
                'author'  => 'Rédaction Variable',
                'date'    => '2026-04-10',
                'excerpt' => "On vous explique comment on calcule votre part, et pourquoi c'est 4 fois plus que les standards du marché.",
                'content' => <<<HTML
<p>Quand on dit aux auteurs qu'ils touchent 80% du prix de vente sur Variable, certains pensent qu'on plaisante. C'est tellement loin des standards du métier qu'ils n'osent pas y croire. Pourtant, c'est très simple.</p>

<h2>L'édition traditionnelle, en chiffres</h2>

<p>Sur un livre vendu en librairie à 20 €, voici ce qui se passe :</p>
<ul>
    <li>Le libraire prend 30 à 35%, soit environ 7 €.</li>
    <li>Le distributeur prend 15 à 20%, soit environ 4 €.</li>
    <li>L'éditeur garde 35 à 45%, soit environ 7 €.</li>
    <li>L'<strong>auteur touche 8 à 10%</strong>, soit environ <strong>1,80 €</strong>.</li>
</ul>

<p>Sur 100 livres vendus, l'auteur touche 180 €. Trois mois d'écriture, deux ans d'attente, et 180 €.</p>

<h2>Variable, en chiffres</h2>

<p>Sur un livre vendu 9,99 USD chez nous :</p>
<ul>
    <li>Frais de paiement (Stripe ou Money Fusion) : environ 4%, soit 0,40 USD.</li>
    <li><strong>Auteur</strong> : 80% net, soit environ <strong>7,67 USD</strong>.</li>
    <li>Variable : 16%, soit environ 1,60 USD pour faire tourner la plateforme.</li>
</ul>

<p>Sur 100 livres vendus, l'auteur touche 767 USD. Plus de 4 fois plus qu'en édition traditionnelle. Et il est payé chaque mois, sans attendre des relevés annuels obscurs.</p>

<h2>Comment c'est possible ?</h2>

<p>Trois raisons.</p>

<p><strong>D'abord, le numérique élimine les intermédiaires.</strong> Pas de libraire, pas de distributeur, pas de stock. Le livre voyage en quelques mégaoctets, du serveur au téléphone du lecteur.</p>

<p><strong>Ensuite, on a une structure légère.</strong> Pas de bureaux luxueux. Pas de marketing à coup de gros budgets. On est une équipe réduite qui fait avec les moyens du bord.</p>

<p><strong>Enfin, on assume un choix politique.</strong> On préfère un auteur bien payé qu'une marge qui gonfle. C'est ce qui fait, à terme, qu'on garde nos meilleures plumes et qu'on en attire d'autres.</p>

<h2>Et l'abonnement, alors ?</h2>

<p>Pour les livres lus via abonnement, le calcul est différent : on calcule chaque mois un pool basé sur les revenus d'abonnement, on en redistribue 50% aux auteurs, au prorata des pages effectivement lues. C'est transparent, vérifiable, expliqué dans nos conditions.</p>

<p>Tout est dans nos statistiques auteur, accessibles depuis ton dashboard. On ne te cache rien.</p>

<h2>Pourquoi on fait ça</h2>

<p>Parce qu'on pense que le métier d'auteur africain doit pouvoir nourrir son homme. Pas devenir riche, peut-être. Mais nourrir, oui. C'est la condition pour qu'une littérature existe vraiment, sur le long terme.</p>
HTML
            ],

            'lire-offline-astuces' => [
                'slug'    => 'lire-offline-astuces',
                'title'   => 'Lire en off-line : nos astuces pour profiter de Variable sans internet',
                'author'  => 'Rédaction Variable',
                'date'    => '2026-04-05',
                'excerpt' => "Téléchargement PDF, mode hors-ligne, lecture sans coupures : notre plateforme est pensée pour les réalités africaines.",
                'content' => <<<HTML
<p>À Kinshasa comme à Bamako, on connaît la vérité : l'internet, ça coupe. Une coupure de courant, un forfait épuisé, un opérateur capricieux. Et pourtant, on veut continuer à lire. Voici comment Variable s'adapte à ces réalités.</p>

<h2>1. Le mode lecture en streaming protégé</h2>

<p>Notre liseuse charge le livre page par page, en streaming. Tu n'as pas besoin de tout télécharger. Tu lis ce que tu lis. Si ta connexion s'interrompt, tu continues sur les pages déjà chargées.</p>

<h2>2. Le téléchargement PDF (Premium)</h2>

<p>Avec l'abonnement Premium (10 USD/mois), tu peux télécharger les livres en PDF protégé. Tu les ouvres ensuite dans n'importe quel lecteur PDF, même sans connexion. Idéal pour les longs trajets, les pannes de courant, ou simplement pour lire au calme dans un endroit sans réseau.</p>

<h2>3. La lecture sur l'ordinateur du bureau</h2>

<p>Beaucoup de nos lecteurs lisent au bureau pendant la pause de midi. La connexion est généralement meilleure qu'à la maison. Charge ton livre, lis tranquillement. Ta progression est sauvegardée automatiquement, tu peux reprendre le soir sur ton téléphone.</p>

<h2>4. L'astuce du WiFi public</h2>

<p>Beaucoup de cafés, restaurants, bibliothèques offrent du WiFi gratuit. Profites-en pour charger ton extrait gratuit ou commencer un livre. Une fois la liseuse ouverte, tu peux continuer même si le WiFi devient instable.</p>

<h2>5. Économiser ta data</h2>

<p>Notre liseuse est optimisée pour la consommation minimale. Une heure de lecture consomme environ 5 à 10 Mo. C'est moins qu'une seule chanson sur YouTube. Tu peux lire pendant des semaines avec un forfait modeste.</p>

<h2>6. Synchronisation automatique</h2>

<p>Quand tu reviens en ligne, ta progression de lecture, tes favoris et tes pages atteintes sont synchronisés automatiquement avec nos serveurs. Aucune perte. Tu peux lire à Kinshasa sur ton téléphone, finir le chapitre à Trois-Rivières sur ton ordinateur. Tout est synchro.</p>

<h2>Ce qu'on prépare</h2>

<p>Une application mobile native (iOS et Android) avec mode hors-ligne complet, pour 2026. Tu pourras télécharger 5 livres à l'avance, les emmener partout, lire pendant des semaines sans connexion. Le développement est en cours.</p>

<p>En attendant, la version web fait déjà l'essentiel. Et elle marche, même quand le réseau ne marche pas.</p>
HTML
            ],

            'mobile-money-priorite-paiement' => [
                'slug'    => 'mobile-money-priorite-paiement',
                'title'   => 'Mobile Money : pourquoi nous avons intégré ce mode de paiement en priorité',
                'author'  => 'Angello Luvungu Muhiya',
                'date'    => '2026-04-01',
                'excerpt' => "Airtel Money, Orange Money, MTN, M-Pesa : nous parlons votre langage de paiement.",
                'content' => <<<HTML
<p>Quand j'ai conçu Variable, une question m'est revenue dès le premier jour : <em>comment vont-ils payer ?</em></p>

<p>En Europe ou au Canada, c'est simple : carte bancaire, c'est fait. En Afrique francophone, la réalité est différente. Selon la Banque mondiale, moins de 30% de la population a un compte bancaire. Mais plus de 60% utilisent activement le Mobile Money. À Kinshasa, c'est même le mode de paiement majoritaire chez les jeunes adultes.</p>

<p>Si on voulait vraiment toucher notre public, il fallait intégrer ce mode de paiement <strong>avant</strong> la carte bancaire, pas après.</p>

<h2>Comment ça marche concrètement</h2>

<p>Tu choisis « Mobile Money » au moment du paiement. Tu rentres ton numéro de téléphone. Tu reçois un code par SMS ou un message dans l'app de ton opérateur. Tu valides. C'est payé.</p>

<p>Pas de carte. Pas d'IBAN. Pas de SWIFT. Juste ton téléphone et un code à quatre chiffres.</p>

<h2>Les opérateurs supportés</h2>

<p>Grâce à notre partenariat avec <strong>Money Fusion</strong>, nous acceptons :</p>
<ul>
    <li><strong>Airtel Money</strong> (RDC, Tchad, Niger, Madagascar…)</li>
    <li><strong>Orange Money</strong> (RDC, Côte d'Ivoire, Sénégal, Mali, Cameroun…)</li>
    <li><strong>MTN Mobile Money</strong> (Côte d'Ivoire, Cameroun, Bénin, Ouganda…)</li>
    <li><strong>M-Pesa</strong> (RDC, Kenya, Tanzanie, Ghana…)</li>
    <li><strong>Wave</strong> (Sénégal, Côte d'Ivoire)</li>
    <li><strong>Moov Money</strong> (Côte d'Ivoire, Bénin, Togo, Burkina Faso…)</li>
</ul>

<p>Et on ajoute des opérateurs au fur et à mesure que la demande arrive.</p>

<h2>Et la diaspora ?</h2>

<p>Pour la diaspora — Canada, France, Belgique, États-Unis — nous proposons aussi le paiement par carte bancaire via <strong>Stripe</strong>. Visa, Mastercard, Amex. C'est sécurisé, ça marche partout, c'est instantané.</p>

<p>Tu peux aussi être au Canada et payer un abonnement à un membre de ta famille en RDC : tu rentres son numéro Mobile Money, tu paies par carte, le système gère le change. C'est ce qu'on appelle le « pont diaspora ». De plus en plus utilisé.</p>

<h2>Pourquoi c'est important</h2>

<p>Beaucoup de plateformes occidentales nous font payer en dollars, sans alternative. C'est exclure de fait des millions d'utilisateurs. Et c'est dire, implicitement, <em>« on ne pense pas à vous ».</em></p>

<p>Variable a été conçue avec, dans la tête, l'image d'un étudiant à Goma, ou d'un enseignant à Bamako. Quelqu'un qui veut lire, qui peut se permettre 3 USD par mois, mais qui n'a pas de Visa. Pour ces lecteurs, Mobile Money n'est pas une option. C'est <em>la</em> condition.</p>

<p>Alors oui, on a fait le boulot d'intégrer ces paiements. Oui, c'est plus compliqué techniquement. Mais c'est la seule façon de rester fidèles à notre mission.</p>
HTML
            ],
        ];
    }

    public function index(): void
    {
        $articles = $this->articles();
        // Trier par date desc
        uasort($articles, fn($a, $b) => strcmp($b['date'], $a['date']));
        $this->view('pages/blog_index', [
            'titre'    => 'Le carnet Variable',
            'articles' => $articles,
        ]);
    }

    public function show(string $slug): void
    {
        $articles = $this->articles();
        if (!isset($articles[$slug])) {
            redirect('/blog');
            return;
        }
        $article = $articles[$slug];
        $autres = array_filter($articles, fn($a, $k) => $k !== $slug, ARRAY_FILTER_USE_BOTH);
        uasort($autres, fn($a, $b) => strcmp($b['date'], $a['date']));
        $autres = array_slice($autres, 0, 3);

        $this->view('pages/blog_show', [
            'titre'   => $article['title'],
            'article' => $article,
            'autres'  => $autres,
        ]);
    }
}
