<?php
session_start(); // Démarre la session pour accéder aux variables de session
require 'db_connect.php'; // Inclut le fichier de connexion à la base de données

// Requête SQL pour récupérer les 3 recettes les plus récentes, triées par date d'ajout décroissante
$sql_recettes_recent = "
    SELECT DISTINCT id_recettes, image, titre, date_ajout 
    FROM recettes 
    ORDER BY date_ajout DESC 
    LIMIT 3";
$stmt_recettes_recent = $pdo->query($sql_recettes_recent);
$recettes_recent = $stmt_recettes_recent->fetchAll(PDO::FETCH_ASSOC);

// Requête SQL pour récupérer les 3 recettes les plus populaires, basées sur la moyenne des notes
// Utilise COALESCE pour gérer les cas où il n'y a pas de notes (moyenne_note = 0)
$sql_recettes_populaires = "
    SELECT r.id_recettes, r.image, r.titre, COALESCE(AVG(c.note), 0) AS moyenne_note
    FROM recettes r
    LEFT JOIN commentaires c ON r.id_recettes = c.id_recettes
    GROUP BY r.id_recettes, r.image, r.titre
    ORDER BY moyenne_note DESC
    LIMIT 3";
$stmt_recettes_populaires = $pdo->query($sql_recettes_populaires);
$recettes_populaires = $stmt_recettes_populaires->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Page d'accueil - Recettes</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Rock+Salt&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="accueil.css">
    <link rel="stylesheet" href="headerfooter.css">
</head>
<body>
    <?php include 'header.php'; ?>

    <main class="container my-5">
        <section class="popular-recipes mb-5">
            <h2 class="text-center mb-4">Recettes Populaires</h2>
            <div id="popularRecipesCarousel" class="carousel slide" data-bs-ride="carousel">
                <div class="carousel-inner">
                    <?php foreach ($recettes_populaires as $index => $recette): ?>
                        <div class="carousel-item <?php if ($index === 0) echo 'active'; ?>">
                            <a href="detailrecette.php?id=<?php echo htmlspecialchars($recette['id_recettes'], ENT_QUOTES, 'UTF-8'); ?>">
                                <?php
                                    $filePath = htmlspecialchars($recette['image'], ENT_QUOTES, 'UTF-8');
                                    $fileType = mime_content_type($filePath);

                                    if (strpos($fileType, "image") !== false): ?>
                                        <img src="<?php echo $filePath; ?>" 
                                             alt="<?php echo htmlspecialchars($recette['titre'], ENT_QUOTES, 'UTF-8'); ?>" 
                                             class="d-block w-100">
                                    <?php elseif (strpos($fileType, "video") !== false): ?>
                                        <video class="d-block w-100" autoplay muted loop>
                                            <source src="<?php echo $filePath; ?>" type="<?php echo $fileType; ?>">
                                            Votre navigateur ne supporte pas la lecture de cette vidéo.
                                        </video>
                                    <?php endif; ?>
                            </a>
                            <div class="carousel-caption">
                                <h3><?php echo htmlspecialchars($recette['titre'], ENT_QUOTES, 'UTF-8'); ?></h3>
                                <p>Note moyenne : <?php echo number_format($recette['moyenne_note'], 1); ?>/5</p>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                <button class="carousel-control-prev" type="button" data-bs-target="#popularRecipesCarousel" data-bs-slide="prev">
                    <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                    <span class="visually-hidden">Précédent</span>
                </button>
                <button class="carousel-control-next" type="button" data-bs-target="#popularRecipesCarousel" data-bs-slide="next">
                    <span class="carousel-control-next-icon" aria-hidden="true"></span>
                    <span class="visually-hidden">Suivant</span>
                </button>
                <div class="carousel-indicators">
                    <?php foreach ($recettes_populaires as $index => $recette): ?>
                        <button type="button" data-bs-target="#popularRecipesCarousel" data-bs-slide-to="<?php echo $index; ?>" class="<?php if ($index === 0) echo 'active'; ?>" aria-current="true" aria-label="Slide <?php echo $index + 1; ?>"></button>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>

        <section class="recent-recipes">
            <h2 class="text-center mb-4">Recettes Récentes</h2>
            <div class="row">
                <?php foreach ($recettes_recent as $recette): ?>
                    <div class="col-md-4 mb-4">
                        <div class="card h-100">
                            <?php
                                $filePath = htmlspecialchars($recette['image'], ENT_QUOTES, 'UTF-8');
                                $fileType = mime_content_type($filePath);

                                if (strpos($fileType, "image") !== false): ?>
                                    <img src="<?php echo $filePath; ?>" 
                                         alt="<?php echo htmlspecialchars($recette['titre'], ENT_QUOTES, 'UTF-8'); ?>" 
                                         class="card-img-top recipe-img-recent">
                                <?php elseif (strpos($fileType, "video") !== false): ?>
                                    <video class="card-img-top recipe-img-recent" autoplay muted loop>
                                        <source src="<?php echo $filePath; ?>" type="<?php echo $fileType; ?>">
                                        Votre navigateur ne supporte pas la lecture de cette vidéo.
                                    </video>
                                <?php endif; ?>
                            <div class="card-body">
                                <h3 class="card-title"><?php echo htmlspecialchars($recette['titre'], ENT_QUOTES, 'UTF-8'); ?></h3>
                                <a href="detailrecette.php?id=<?php echo htmlspecialchars($recette['id_recettes'], ENT_QUOTES, 'UTF-8'); ?>" 
                                   class="btn btn-primary">Voir la recette</a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </section>
    </main>

    <?php include 'footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="script.js"></script>
</body>
</html>