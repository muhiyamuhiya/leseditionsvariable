<?php
/**
 * Seed : auteurs de test + livres de test
 * Usage : php database/seeds/create_test_data.php
 */
require_once __DIR__ . '/../../bootstrap.php';

use App\Lib\Database;

echo "=== Seed : données de test ===" . PHP_EOL . PHP_EOL;

$db = Database::getInstance();

// --- Fonction utilitaire pour générer un slug ---
function slugify(string $text): string {
    $text = transliterator_transliterate('Any-Latin; Latin-ASCII; Lower()', $text);
    $text = preg_replace('/[^a-z0-9]+/', '-', $text);
    return trim($text, '-');
}

// =====================================================================
// AUTEURS
// =====================================================================
$auteurs = [
    ['prenom' => 'Amara',      'nom' => 'Mukendi',  'pays' => 'CD', 'bio' => "Romancier congolais, Amara Mukendi explore les fractures de l'identité africaine contemporaine à travers des récits intimes et universels."],
    ['prenom' => 'Fatou',      'nom' => 'Diallo',   'pays' => 'SN', 'bio' => "Essayiste sénégalaise, Fatou Diallo décortique les dynamiques économiques et sociales du continent avec une plume incisive."],
    ['prenom' => 'Samba',      'nom' => 'Ndiaye',   'pays' => 'SN', 'bio' => "Poète et conteur, Samba Ndiaye puise dans la tradition orale ouest-africaine pour créer une littérature à la croisée des mondes."],
    ['prenom' => 'Christelle', 'nom' => 'Mbala',    'pays' => 'CM', 'bio' => "Biographe camerounaise, Christelle Mbala donne la parole aux parcours extraordinaires de femmes africaines ordinaires."],
    ['prenom' => 'Jean-Paul',  'nom' => 'Lumumba',  'pays' => 'CD', 'bio' => "Héritier littéraire de Kinshasa, Jean-Paul Lumumba écrit des romans urbains qui capturent le pouls de la ville."],
];

$authorIds = [];
$passwordHash = password_hash('TestAuteur123!', PASSWORD_BCRYPT);

foreach ($auteurs as $a) {
    $email = strtolower($a['prenom'] . '.' . $a['nom']) . '@auteur.leseditionsvariable.com';
    $email = transliterator_transliterate('Any-Latin; Latin-ASCII; Lower()', $email);

    // Vérifier si existe déjà
    $existing = $db->fetch("SELECT id FROM users WHERE email = ?", [$email]);
    if ($existing) {
        echo "  Auteur {$a['prenom']} {$a['nom']} existe déjà (user #{$existing->id})" . PHP_EOL;
        $author = $db->fetch("SELECT id FROM authors WHERE user_id = ?", [$existing->id]);
        $authorIds[$a['prenom'] . ' ' . $a['nom']] = $author ? $author->id : null;
        continue;
    }

    // Créer le user
    $userId = $db->insert('users', [
        'email'         => $email,
        'password_hash' => $passwordHash,
        'prenom'        => $a['prenom'],
        'nom'           => $a['nom'],
        'role'          => 'auteur',
        'email_verifie' => 1,
        'pays'          => $a['pays'],
        'devise_preferee' => 'USD',
        'actif'         => 1,
        'accepte_cgu_at' => date('Y-m-d H:i:s'),
        'code_parrainage' => 'AUT' . strtoupper(bin2hex(random_bytes(3))),
    ]);

    $slug = slugify($a['prenom'] . ' ' . $a['nom']);

    // Créer l'auteur
    $authorId = $db->insert('authors', [
        'user_id'           => $userId,
        'slug'              => $slug,
        'nom_plume'         => $a['prenom'] . ' ' . $a['nom'],
        'biographie_courte' => $a['bio'],
        'pays_origine'      => $a['pays'],
        'statut_validation' => 'valide',
        'date_validation'   => date('Y-m-d H:i:s'),
        'contrat_signe'     => 1,
    ]);

    $authorIds[$a['prenom'] . ' ' . $a['nom']] = $authorId;
    echo "  Auteur créé : {$a['prenom']} {$a['nom']} (author #{$authorId})" . PHP_EOL;
}

echo PHP_EOL;

// =====================================================================
// LIVRES
// =====================================================================

// Récupérer les IDs des catégories par slug
$cats = $db->fetchAll("SELECT id, slug FROM categories");
$catMap = [];
foreach ($cats as $c) {
    $catMap[$c->slug] = $c->id;
}

$livres = [
    [
        'titre' => 'Les rives du fleuve Congo',
        'auteur' => 'Amara Mukendi',
        'cat' => 'roman-fiction',
        'prix' => 9.99,
        'desc_courte' => "Un roman puissant sur trois générations de femmes entre Kinshasa et Bruxelles.",
        'desc_longue' => "Au bord du fleuve Congo, trois femmes portent le poids d'un héritage familial traversé par l'histoire tumultueuse de la RDC.\n\nMakala, la grand-mère, garde les secrets d'une époque révolue. Béatrice, sa fille, navigue entre deux continents. Et Sifa, la petite-fille, cherche sa voix dans un monde qui ne sait plus écouter.\n\nAmara Mukendi tisse avec finesse un récit qui transcende les frontières, porté par une écriture lumineuse et une profonde humanité.\n\nUn roman incontournable de la littérature congolaise contemporaine.",
        'pages' => 287,
        'avant' => 1,
    ],
    [
        'titre' => "L'Afrique qui entreprend",
        'auteur' => 'Fatou Diallo',
        'cat' => 'business-entrepreneuriat',
        'prix' => 12.99,
        'desc_courte' => "Vingt portraits d'entrepreneurs africains qui réinventent le continent.",
        'desc_longue' => "De Dakar à Nairobi, de Lagos à Kinshasa, une nouvelle génération d'entrepreneurs transforme l'Afrique.\n\nFatou Diallo est allée à leur rencontre. Vingt parcours, vingt visions, vingt manières de bâtir sur un continent en mouvement.\n\nCe livre n'est pas un manuel de business. C'est un témoignage vivant de ce que signifie entreprendre quand les infrastructures manquent mais que l'énergie abonde.\n\nUn essai essentiel pour comprendre l'économie africaine d'aujourd'hui et de demain.",
        'pages' => 234,
        'avant' => 1,
    ],
    [
        'titre' => 'Paroles de baobab',
        'auteur' => 'Samba Ndiaye',
        'cat' => 'poesie-theatre',
        'prix' => 7.99,
        'desc_courte' => "Recueil de poèmes qui mêle tradition orale et modernité africaine.",
        'desc_longue' => "Sous le baobab, les mots circulent de bouche en bouche depuis des siècles. Samba Ndiaye les recueille et les transforme en poèmes d'une beauté brute.\n\nEntre wolof et français, entre Dakar et les villages de Casamance, sa poésie dit l'Afrique telle qu'elle se vit : vibrante, contradictoire, toujours debout.\n\nUn recueil qui se lit à voix haute, comme on partage un repas.",
        'pages' => 156,
        'avant' => 1,
    ],
    [
        'titre' => 'Ma route, mon histoire',
        'auteur' => 'Christelle Mbala',
        'cat' => 'biographies-memoires',
        'prix' => 14.99,
        'desc_courte' => "Le parcours extraordinaire d'une femme camerounaise entre Douala, Paris et Montréal.",
        'desc_longue' => "Christelle Mbala raconte sans fard le parcours d'une femme camerounaise née dans un quartier populaire de Douala.\n\nDe son enfance marquée par la débrouillardise à ses études à Paris, de ses premiers pas professionnels à Montréal à son retour au Cameroun pour créer son entreprise.\n\nUne biographie qui inspire et qui montre que chaque route, même sinueuse, mène quelque part.\n\nUn livre pour tous ceux qui refusent de croire que leur lieu de naissance détermine leur destination.",
        'pages' => 312,
        'avant' => 1,
    ],
    [
        'titre' => "L'enfant de Kinshasa",
        'auteur' => 'Jean-Paul Lumumba',
        'cat' => 'roman-fiction',
        'prix' => 11.99,
        'desc_courte' => "Roman urbain sur la jeunesse congolaise entre rêves et réalité.",
        'desc_longue' => "Moïse a vingt ans et Kinshasa est son terrain de jeu. Entre les bars de Matonge et les embouteillages de Limete, il cherche sa place dans une ville qui ne dort jamais.\n\nJean-Paul Lumumba capture avec justesse le quotidien d'une jeunesse congolaise tiraillée entre tradition et modernité, entre rêves d'ailleurs et amour du pays.\n\nUn roman vif, drôle et touchant qui sent le Congo à chaque page.",
        'pages' => 248,
        'avant' => 0,
    ],
    [
        'titre' => 'Sous le baobab',
        'auteur' => 'Amara Mukendi',
        'cat' => 'roman-fiction',
        'prix' => 9.99,
        'desc_courte' => "Nouvelles sur la vie rurale congolaise, entre sagesse ancestrale et défis modernes.",
        'desc_longue' => "Sept nouvelles, sept villages, sept histoires qui disent l'Afrique rurale d'aujourd'hui.\n\nAmara Mukendi pose son regard tendre et lucide sur des personnages ordinaires confrontés à des choix extraordinaires.\n\nDu guérisseur qui doute de ses pouvoirs au jeune diplômé qui revient au village, ces récits dessinent une cartographie intime du Congo profond.",
        'pages' => 198,
        'avant' => 0,
    ],
    [
        'titre' => 'Femmes debout',
        'auteur' => 'Fatou Diallo',
        'cat' => 'essais-societe',
        'prix' => 13.99,
        'desc_courte' => "Essai sur les femmes qui transforment la société ouest-africaine.",
        'desc_longue' => "En Afrique de l'Ouest, les femmes portent l'économie, la famille et souvent la communauté entière sur leurs épaules.\n\nFatou Diallo leur rend hommage dans cet essai documenté et passionné. Des marchandes de Dantokpa aux entrepreneures tech de Dakar, elle montre comment les femmes africaines inventent chaque jour un modèle de société.\n\nUn livre nécessaire qui rappelle que le développement de l'Afrique passe par ses femmes.",
        'pages' => 276,
        'avant' => 0,
    ],
    [
        'titre' => 'Le sage de la savane',
        'auteur' => 'Samba Ndiaye',
        'cat' => 'roman-fiction',
        'prix' => 10.99,
        'desc_courte' => "Conte philosophique sur la transmission et la sagesse en Afrique.",
        'desc_longue' => "Dans un village sénégalais, le vieux Demba est le dernier à connaître les histoires anciennes. Quand un jeune citadin vient l'écouter, commence un dialogue entre deux mondes.\n\nSamba Ndiaye signe un conte philosophique lumineux sur la transmission, le temps qui passe et la nécessité de se souvenir.\n\nUn livre qui rappelle que les plus belles bibliothèques sont parfois des hommes.",
        'pages' => 184,
        'avant' => 0,
    ],
];

$bookCount = 0;
foreach ($livres as $i => $l) {
    $slug = slugify($l['titre']);

    // Vérifier si le livre existe déjà
    $existing = $db->fetch("SELECT id FROM books WHERE slug = ?", [$slug]);
    if ($existing) {
        echo "  Livre \"{$l['titre']}\" existe déjà (#{$existing->id})" . PHP_EOL;
        continue;
    }

    $authorId = $authorIds[$l['auteur']] ?? null;
    if (!$authorId) {
        echo "  ERREUR : auteur \"{$l['auteur']}\" non trouvé, livre ignoré" . PHP_EOL;
        continue;
    }

    $catId = $catMap[$l['cat']] ?? null;
    $daysAgo = rand(1, 90);
    $pubDate = date('Y-m-d H:i:s', strtotime("-{$daysAgo} days"));

    $bookId = $db->insert('books', [
        'author_id'             => $authorId,
        'category_id'           => $catId,
        'titre'                 => $l['titre'],
        'slug'                  => $slug,
        'description_courte'    => $l['desc_courte'],
        'description_longue'    => $l['desc_longue'],
        'nombre_pages'          => $l['pages'],
        'prix_unitaire_usd'     => $l['prix'],
        'statut'                => 'publie',
        'date_publication'      => $pubDate,
        'accessible_abonnement' => 1,
        'mis_en_avant'          => $l['avant'],
        'editeur'               => 'Les éditions Variable',
        'langue'                => 'fr',
        'annee_publication'     => date('Y'),
    ]);

    echo "  Livre créé : \"{$l['titre']}\" (#{$bookId})" . PHP_EOL;
    $bookCount++;
}

echo PHP_EOL . "=== Terminé : {$bookCount} livres créés ===" . PHP_EOL;
