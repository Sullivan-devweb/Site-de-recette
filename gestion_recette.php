<?php
// Démarrer la session pour gérer les variables de session
session_start();

// Inclure le fichier de connexion à la base de données
include 'db_connect.php';

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    // Rediriger vers la page de connexion si l'utilisateur n'est pas connecté
    header("Location: connexion_html.php");
    exit();
}

// Vérifier si l'ID de l'utilisateur est défini dans la session
$utilisateurValide = isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
$idUtilisateur = $utilisateurValide ? $_SESSION['user_id'] : null;

// Fonction pour récupérer les recettes de l'utilisateur
function getRecettes($idUtilisateur) {
    global $pdo;
    // Si l'ID de l'utilisateur n'est pas défini, retourner un tableau vide
    if ($idUtilisateur === null) {
        return [];
    }
    // Requête SQL pour récupérer les recettes de l'utilisateur, triées par date d'ajout décroissante
    $sql = "SELECT * FROM recettes WHERE id_utilisateur = :id_utilisateur ORDER BY date_ajout DESC";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(":id_utilisateur", $idUtilisateur, PDO::PARAM_INT);
    $stmt->execute();
    // Retourner les résultats sous forme de tableau associatif
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Récupérer les recettes de l'utilisateur
$recettes = getRecettes($idUtilisateur);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mes Recettes</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="gestion_recette.css">
    <link rel="stylesheet" href="headerfooter.css">
</head>
    <?php include 'header.php'; ?>

    <main class="container py-4">
        <h1 class="text-center">Bienvenue dans votre espace recettes</h1>
        <div class="text-center my-3">
            <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#recipeModal">
                <i class="fas fa-plus"></i> Ajouter une Recette
            </button>
        </div>
        <div class="row">
    <?php if (!empty($recettes)): ?>
        <?php foreach ($recettes as $recette): ?>
            <div class="col-md-4 mb-4">
                <div class="card bg-secondary text-light shadow">
                    <?php if (!empty($recette['image'])): ?>
                        <?php $fileType = mime_content_type($recette['image']); ?>
                        <?php if (strpos($fileType, "image") !== false): ?>
                            <img src="<?= htmlspecialchars($recette['image']) ?>" class="card-img-top" alt="Image de la recette">
                        <?php elseif (strpos($fileType, "video") !== false): ?>
                            <video class="card-img-top" controls>
                                <source src="<?= htmlspecialchars($recette['image']) ?>" type="<?= $fileType ?>">
                                Votre navigateur ne supporte pas la lecture de cette vidéo.
                            </video>
                        <?php endif; ?>
                    <?php endif; ?>

                    <div class="card-body">
                        <h5 class="card-title"><?= htmlspecialchars($recette['titre']) ?></h5>
                        <p class="card-text"><?= htmlspecialchars($recette['description']) ?></p>
                        <p class="card-text"><strong>Prix :</strong> <?= number_format($recette['prix'], 2) ?> €</p>
                        <div class="d-flex justify-content-between">
                            <button class="btn btn-warning" onclick="editRecipe(<?= $recette['id_recettes'] ?>)">
                                <i class="fas fa-edit"></i> Modifier
                            </button>
                            <button class="btn btn-danger" onclick="deleteRecipe(<?= $recette['id_recettes'] ?>)">
                                <i class="fas fa-trash"></i> Supprimer
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <p class="text-center">Aucune recette disponible. Ajoutez-en une !</p>
    <?php endif; ?>
</div>
    </main>

    <div class="modal fade" id="recipeModal" tabindex="-1" aria-labelledby="recipeModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg"> <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Ajouter / Modifier une Recette</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
           <div class="modal-body">
    <form id="recipeForm" action="save_recette.php" method="post" enctype="multipart/form-data">
        <input type="hidden" id="recipeId" name="id">
        
        <div class="mb-3">
            <label class="form-label">Titre</label>
            <input type="text" class="form-control" id="titre" name="titre" required>
        </div>
            <div class="mb-3">
            <label class="form-label">Prix (en €)</label>
            <input type="number" class="form-control" id="prix" name="prix" step="0.01" min="0" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Média (Image ou Vidéo)</label>
            <input type="file" class="form-control" id="media" name="media" accept="image/*,video/*" required>
            <div id="media-preview" class="mt-2"></div>
        </div>

        <div class="mb-3">
            <label class="form-label">Catégorie</label>
            <select class="form-control" id="categorie" name="categorie" required>
                <option value="">Sélectionnez une catégorie</option>
                <option value="Entrée">Entrée</option>
                <option value="Plat">Plat</option>
                <option value="Dessert">Dessert</option>
            </select>
        </div>
        
        <div class="mb-3">
            <label class="form-label">Description</label>
            <textarea class="form-control" id="description" name="description" rows="3" required></textarea>
        </div>

        <div class="mb-3">
            <label class="form-label">Ingrédients</label>
            <div class="input-group">
                <input type="text" id="ingredient-input" class="form-control" placeholder="Ajouter un ingrédient">
                <button type="button" class="btn btn-success" onclick="addIngredient()">Ajouter</button>
            </div>
            <ul id="ingredients-list" class="list-group mt-2"></ul>
            <input type="hidden" id="ingredients" name="ingredients">
        </div>

        <div class="mb-3">
            <label class="form-label">Instructions</label>
            <textarea class="form-control" id="instructions" name="instructions" rows="4" required></textarea>
        </div>

        <button type="submit" class="btn btn-primary">Enregistrer</button>
    </form>
</div>
        </div>
    </div>
    </div>

    <?php include 'footer.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="gestion_recette.js"></script>
</body>
</html>