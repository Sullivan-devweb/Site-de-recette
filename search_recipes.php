<?php
session_start();
require 'db_connect.php'; // Inclut le fichier de connexion à la base de données

// Initialisation des conditions de la requête et des paramètres
$whereClauses = []; // Tableau pour stocker les conditions WHERE
$params = []; // Tableau associatif pour les paramètres de la requête

// Gestion de la recherche par titre
if (isset($_GET['search']) && !empty($_GET['search'])) {
    $search = '%' . $_GET['search'] . '%'; // Ajoute des wildcards pour une recherche partielle
    $whereClauses[] = "titre LIKE :search"; // Ajoute la condition WHERE pour le titre
    $params[':search'] = $search; // Ajoute le paramètre pour la recherche de titre
}

// Gestion de la recherche par prix
if (isset($_GET['prix']) && !empty($_GET['prix'])) {
    $prix = (float) $_GET['prix']; // Convertit le prix en nombre à virgule flottante
    $whereClauses[] = "prix <= :prix"; // Ajoute la condition WHERE pour le prix maximum
    $params[':prix'] = $prix; // Ajoute le paramètre pour le prix
}

// Gestion de la recherche par catégorie
if (isset($_GET['categorie']) && !empty($_GET['categorie'])) {
    $categorie = $_GET['categorie']; // Récupère la catégorie sélectionnée
    $whereClauses[] = "categorie = :categorie"; // Ajoute la condition WHERE pour la catégorie (correspondance exacte)
    $params[':categorie'] = $categorie; // Ajoute le paramètre pour la catégorie
}

// Construction de la requête SQL de base
$sql = "SELECT * FROM recettes"; // Sélectionne toutes les colonnes de la table recettes

// Ajout des conditions WHERE si elles existent
if (!empty($whereClauses)) {
    $sql .= " WHERE " . implode(' AND ', $whereClauses); // Combine les conditions avec AND
}

// Ajout de l'ordre de tri par date d'ajout décroissante
$sql .= " ORDER BY date_ajout DESC";

// Préparation et exécution de la requête SQL
try {
    $stmt = $pdo->prepare($sql); // Prépare la requête SQL
    $stmt->execute($params); // Exécute la requête avec les paramètres
    $recettes = $stmt->fetchAll(PDO::FETCH_ASSOC); // Récupère les résultats sous forme de tableau associatif

    // Affichage des résultats de la recherche
    if (!empty($recettes)) {
        foreach ($recettes as $recette) {
            $filePath = htmlspecialchars($recette['image'], ENT_QUOTES, 'UTF-8'); // Sécurise le chemin de l'image/vidéo
            $fileType = mime_content_type($filePath); // Détermine le type MIME du fichier

            // Affichage de chaque recette dans une carte Bootstrap
            echo "<div class='col-md-4'>
                    <div class='card shadow-lg bg-dark text-white border-0'>
                        <a href='detailrecette.php?id={$recette['id_recettes']}' class='text-decoration-none'>";

            // Affichage de l'image ou de la vidéo en fonction du type de fichier
            if (strpos($fileType, "image") !== false) {
                echo "<img src='" . $filePath . "' alt='" . htmlspecialchars($recette['titre'], ENT_QUOTES, 'UTF-8') . "' 
                         class='card-img-top img-fluid rounded' style='height: 250px; object-fit: cover;'>";
            } elseif (strpos($fileType, "video") !== false) {
                echo "<video class='card-img-top img-fluid rounded' autoplay muted loop playsinline style='height: 250px; object-fit: cover;'>
                        <source src='" . $filePath . "' type='" . $fileType . "'>
                        Votre navigateur ne supporte pas la lecture de cette vidéo.
                    </video>";
            }

            // Affichage des informations de la recette (titre, prix, catégorie)
            echo "<div class='card-body text-center'>
                    <h5 class='card-title text-warning'>" . htmlspecialchars($recette['titre'], ENT_QUOTES, 'UTF-8') . "</h5>
                    <h5 class='card-title text-warning'> " . number_format($recette['prix'], 2) . " €</h5>
                    <h5 class='card-title text-warning'> " . htmlspecialchars($recette['categorie'], ENT_QUOTES, 'UTF-8') . "</h5>
                </div>
            </a>
        </div>
    </div>";
        }
    } else {
        // Message si aucune recette ne correspond à la recherche
        echo "<div class='col-12 text-center text-warning'><p>Aucune recette ne correspond à votre recherche.</p></div>";
    }
} catch (PDOException $e) {
    // Gestion des erreurs de base de données
    echo "<p>Erreur de la requête : " . $e->getMessage() . "</p>";
}
?>