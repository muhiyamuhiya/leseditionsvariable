<?php
/**
 * Seed : livre d'Angello Luvungu Muhiya
 * Usage : php database/seeds/add_angello_book.php
 */
require_once __DIR__ . '/../../bootstrap.php';

use App\Lib\Database;
use App\Lib\PDFProcessor;

echo "=== Ajout du livre d'Angello ===" . PHP_EOL . PHP_EOL;

$db = Database::getInstance();
$slug = 'je-nai-pas-choisi-ma-naissance';

// Vérifier si le livre existe déjà
$existing = $db->fetch("SELECT id FROM books WHERE slug = ?", [$slug]);
if ($existing) {
    echo "Le livre existe déjà (ID #{$existing->id}), skip." . PHP_EOL;
    exit(0);
}

// =====================================================================
// AUTEUR : vérifier/créer l'entrée authors pour l'admin
// =====================================================================
$adminUser = $db->fetch("SELECT id FROM users WHERE email = 'contact@variablefly.com'");
if (!$adminUser) {
    echo "ERREUR : utilisateur admin introuvable." . PHP_EOL;
    exit(1);
}

$authorEntry = $db->fetch("SELECT id FROM authors WHERE user_id = ?", [$adminUser->id]);

if (!$authorEntry) {
    $authorId = $db->insert('authors', [
        'user_id'            => $adminUser->id,
        'slug'               => 'angello-luvungu-muhiya',
        'nom_plume'          => null,
        'biographie_courte'  => "PDG des Éditions Variable, Angello Luvungu Muhiya est né à Kikwit (RDC). Social communicateur de formation, il partage son parcours entre Kinshasa, Trois-Rivières et les terres fertiles de la diaspora africaine.",
        'biographie_longue'  => "Angello Luvungu Muhiya est né à Kikwit, dans la province du Kwilu en République démocratique du Congo. Très tôt marqué par les réalités sociales de son pays, il s'engage dans le travail communautaire et la communication sociale.\n\nFondateur de l'ONG VariableFly, il oeuvre pour l'insertion professionnelle et l'autonomisation de la jeunesse africaine. Son parcours l'a mené de Kinshasa à Trois-Rivières au Canada, où il pose les bases des Éditions Variable.\n\nConvaincu que la littérature africaine francophone mérite une plateforme à la hauteur de ses ambitions, il crée Les éditions Variable en 2024 pour offrir aux auteurs du continent une rémunération juste et aux lecteurs une expérience de lecture moderne.\n\n« Je n'ai pas choisi ma naissance, mais j'ai choisi mon destin » est son premier ouvrage, un témoignage personnel sur la résilience et la construction de soi.",
        'pays_origine'       => 'CD',
        'ville_residence'    => 'Trois-Rivières',
        'statut_validation'  => 'valide',
        'date_validation'    => date('Y-m-d H:i:s'),
        'valide_par_admin_id'=> $adminUser->id,
        'contrat_signe'      => 1,
    ]);
    echo "  Auteur créé : Angello Luvungu Muhiya (author #{$authorId})" . PHP_EOL;
} else {
    $authorId = $authorEntry->id;
    echo "  Auteur existant (author #{$authorId})" . PHP_EOL;
}

// Mettre à jour le rôle user en 'auteur' si encore 'admin'
$db->update('users', ['role' => 'admin'], 'id = ?', [$adminUser->id]);

// =====================================================================
// CATÉGORIE
// =====================================================================
$cat = $db->fetch("SELECT id FROM categories WHERE slug = 'biographies-memoires'");
$catId = $cat ? $cat->id : null;

// =====================================================================
// LIVRE
// =====================================================================
$bookId = $db->insert('books', [
    'author_id'             => $authorId,
    'category_id'           => $catId,
    'titre'                 => "Je n'ai pas choisi ma naissance, mais j'ai choisi mon destin",
    'slug'                  => $slug,
    'isbn'                  => '978-99951-11-09-4',
    'description_courte'    => "Un témoignage puissant sur la construction de soi à travers les épreuves, de Kikwit à Trois-Rivières.",
    'description_longue'    => "Né à Kikwit, au coeur de la République démocratique du Congo, Angello Luvungu Muhiya n'a pas eu le choix de son point de départ. Mais il a eu celui de sa direction.\n\nCe récit autobiographique retrace avec une sincérité désarmante le parcours d'un jeune Congolais confronté aux réalités les plus dures de son pays — la pauvreté, l'instabilité, le manque de perspectives — et la manière dont il a transformé chaque obstacle en tremplin.\n\nDe Kikwit à Kinshasa, de Kinshasa à Trois-Rivières, ce livre est une méditation sur l'identité, l'exil choisi, la transmission et la responsabilité. C'est aussi un hommage à tous ceux qui, partout en Afrique et dans la diaspora, refusent de laisser leur lieu de naissance définir leur destination.\n\nUn texte sobre, direct, habité par une voix qui ne demande pas la permission d'exister.",
    'mots_cles'             => 'autobiographie, Congo, RDC, résilience, diaspora, Kikwit, Trois-Rivières, parcours, identité africaine',
    'langue'                => 'fr',
    'nombre_pages'          => 102,
    'annee_publication'     => 2026,
    'editeur'               => 'Les éditions Variable',
    'fichier_complet_path'  => 'storage/books/je-nai-pas-choisi-ma-naissance.pdf',
    'fichier_extrait_path'  => 'storage/extracts/je-nai-pas-choisi-ma-naissance-extrait.pdf',
    'prix_unitaire_usd'     => 9.99,
    'prix_unitaire_cdf'     => 27972,
    'prix_unitaire_eur'     => 9.49,
    'prix_unitaire_cad'     => 13.99,
    'accessible_abonnement' => 1,
    'statut'                => 'publie',
    'date_publication'      => date('Y-m-d H:i:s'),
    'mis_en_avant'          => 1,
    'nouveaute'             => 1,
]);

echo "  Livre créé : \"Je n'ai pas choisi ma naissance...\" (#{$bookId})" . PHP_EOL;

// =====================================================================
// EXTRAIT PDF
// =====================================================================
echo PHP_EOL . "Génération de l'extrait PDF..." . PHP_EOL;
PDFProcessor::generateExtract(
    BASE_PATH . '/storage/books/je-nai-pas-choisi-ma-naissance.pdf',
    BASE_PATH . '/storage/extracts/je-nai-pas-choisi-ma-naissance-extrait.pdf',
    10
);

echo PHP_EOL . "=== Terminé ===" . PHP_EOL;
