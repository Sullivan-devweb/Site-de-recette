<?php
// Démarrage de la session pour accéder aux variables de session
session_start();
// Inclusion du fichier de connexion à la base de données
require 'db_connect.php';

// Requête SQL pour récupérer toutes les recettes, en joignant la table utilisateur pour obtenir le prénom de l'auteur
// Les recettes sont triées par date d'ajout décroissante
$sql_recettes = "SELECT r.*, u.id_utilisateur, u.prenom 
                  FROM recettes r 
                  JOIN utilisateur u ON r.id_utilisateur = u.id_utilisateur
                  ORDER BY r.date_ajout DESC";
// Exécution de la requête SQL
$stmt_recettes = $pdo->query($sql_recettes);
// Récupération de toutes les lignes de résultat sous forme de tableau associatif
$recettes = $stmt_recettes->fetchAll(PDO::FETCH_ASSOC);

?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Liste des Recettes</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="headerfooter.css">
    <link rel="stylesheet" href="listedesrecettes.css">
</head>
<body>

<?php include 'header.php'; ?>

<main class="container mt-4">
    <div class="text-center mb-4">
        <h1 class="text-warning">Liste des Recettes</h1>
        
        <div class="row justify-content-center">
            <div class="col-md-6">
                <input type="search" id="search-input" class="form-control mb-2" placeholder="Rechercher une recette..." autocomplete="off">
            </div>
            <div class="col-md-2">
                <select id="categorie-filter" class="form-select mb-2">
                    <option value="">Toutes les catégories</option>
                    <option value="Entrée">Entrée</option>
                    <option value="Plat">Plat</option>
                    <option value="Dessert">Dessert</option>
                </select>
            </div>
            <div class="col-md-2">
                <input type="number" id="prix-filter" class="form-control mb-2" placeholder="Prix max (€)" min="0" step="0.01">
            </div>
            <div class="col-md-2">
                <button id="search-button" class="btn btn-warning w-100">Rechercher</button>
            </div>
        </div>
    </div>

    <div class="row g-4" id="recipe-container">
    <?php foreach ($recettes as $recette): ?>
        <div class="col-md-4">
            <div class="card shadow-lg bg-dark text-white border-0">
                <a href="detailrecette.php?id=<?php echo $recette['id_recettes']; ?>" class="text-decoration-none">
                    <?php 
                        // Récupération du chemin de l'image ou de la vidéo de la recette
                        $filePath = htmlspecialchars($recette['image'], ENT_QUOTES, 'UTF-8');
                        // Détermination du type MIME du fichier pour afficher l'élément approprié (image ou vidéo)
                        $fileType = mime_content_type($filePath);
                        
                        // Affichage de l'image si le fichier est une image
                        if (strpos($fileType, "image") !== false):
                    ?>
                        <img src="<?php echo $filePath; ?>" 
                             alt="<?php echo htmlspecialchars_decode(html_entity_decode($recette['titre'], ENT_QUOTES, 'UTF-8')); ?>" 
                             class="card-img-top img-fluid rounded"
                             style="height: 250px; object-fit: cover;">
                    
                    <?php // Affichage de la vidéo si le fichier est une vidéo
                    elseif (strpos($fileType, "video") !== false): ?>
                        <video class="card-img-top img-fluid rounded" autoplay muted loop playsinline style="height: 250px; object-fit: cover;">
                            <source src="<?php echo $filePath; ?>" type="<?php echo $fileType; ?>">
                            Votre navigateur ne supporte pas la lecture de cette vidéo.
                        </video>
                    
                    <?php endif; ?>

                    <div class="card-body">
                        <h5 class="card-title text-warning">
                            <?php echo htmlspecialchars_decode(html_entity_decode($recette['titre'], ENT_QUOTES, 'UTF-8')); ?>
                        </h5>

                        <div class="text-end">
                            <p class="text-warning small mb-0">
                                Recette écrite par 
                                <a href="detail_profil.php?id=<?= $recette['id_utilisateur']; ?>" class="fw-bold text-decoration-none">
                                    <?= htmlspecialchars($recette['prenom'], ENT_QUOTES, 'UTF-8'); ?>
                                </a>
                            </p>
                        </div>

                    </div>
                </a>
            </div>
        </div>
    <?php endforeach; ?>
</div>

</main>

<?php include 'footer.php'; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="listedesrecettes.js"></script>
</body>
</html>