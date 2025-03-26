<?php
// Démarrer la session pour gérer les variables de session
session_start();

// Inclure le fichier de connexion à la base de données
require 'db_connect.php';

// Activer l'affichage des erreurs pour le débogage
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// S'assurer que la connexion utilise l'encodage UTF-8 pour gérer les caractères spéciaux
$pdo->exec("SET NAMES utf8mb4");

// Vérifier si la requête est de type GET et si l'ID de la recette est présent
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['id'])) {
    // Récupérer et convertir l'ID de la recette en entier
    $recipeId = intval($_GET['id']);

    // Préparer et exécuter une requête SQL pour récupérer les informations de la recette
    $query = $pdo->prepare("SELECT * FROM recettes WHERE id_recettes = ?");
    $query->execute([$recipeId]);
    $recette = $query->fetch();

    // Si la recette n'est pas trouvée, afficher un message d'erreur et arrêter l'exécution
    if (!$recette) {
        echo "Recette non trouvée pour l'ID: " . $recipeId;
        die();
    }

    // Vérifier si l'image est déjà un chemin complet et si le fichier existe
    $imagePath = (!empty($recette['image']) && file_exists($recette['image'])) 
        ? htmlspecialchars($recette['image'], ENT_QUOTES, 'UTF-8') 
        : "image/default.png";

    // Décoder les entités HTML des ingrédients et les convertir en tableau
    $ingredientsList = htmlspecialchars_decode($recette['ingredients'] ?? '', ENT_QUOTES);
    $ingredientsArray = !empty($ingredientsList) ? explode("\n", trim($ingredientsList)) : [];

} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Si la requête est de type POST, cela signifie que le formulaire a été soumis
    $recipeId = intval($_POST['id']);
    $titre = $_POST['titre'];
    $description = $_POST['description'];
    $categorie = $_POST['categorie'];
    $ingredients = $_POST['ingredients'];
    $instructions = $_POST['instructions'];

    // Récupérer le chemin de l'ancienne image
    $query = $pdo->prepare("SELECT image FROM recettes WHERE id_recettes = ?");
    $query->execute([$recipeId]);
    $oldImage = $query->fetchColumn();

    // Gestion de l'image téléchargée
    if (!empty($_FILES['image']['name'])) {
        $targetDir = "image/"; // Dossier de stockage des images
        $imageFileName = time() . '_' . basename($_FILES['image']['name']); // Nom de fichier unique
        $targetFilePath = $targetDir . $imageFileName;

        // Déplacer le fichier téléchargé vers le dossier de stockage
        if (move_uploaded_file($_FILES['image']['tmp_name'], $targetFilePath)) {
            $imageToStore = $targetFilePath; // Chemin complet de l'image à stocker
        } else {
            echo "Erreur lors du téléchargement de l'image.";
            exit;
        }
    } else {
        $imageToStore = $oldImage; // Conserver l'ancienne image si aucune nouvelle n'est téléchargée
    }

    // Préparer et exécuter la requête SQL pour mettre à jour la recette
    $query = $pdo->prepare("UPDATE recettes SET titre = ?, description = ?, categorie = ?, ingredients = ?, instructions = ?, prix = ?, image = ? WHERE id_recettes = ?");
    $success = $query->execute([$titre, $description, $categorie, htmlspecialchars_decode($ingredients, ENT_QUOTES), htmlspecialchars_decode($instructions, ENT_QUOTES), $_POST['prix'], $imageToStore, $recipeId]);

    // Rediriger vers la page de gestion des recettes en cas de succès
    if ($success) {
        header("Location: gestion_recette.php");
        exit;
    } else {
        echo "Erreur lors de la mise à jour de la recette.";
    }
} else {
    // Si la requête n'est ni GET ni POST, afficher un message d'erreur
    die("Requête invalide.");
}
?>


<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modifier une Recette</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="headerfooter.css">
    <link rel="stylesheet" href="editrecipe.css">
</head>

<style>
/* Importation de la police */
@import url('https://fonts.googleapis.com/css2?family=Rock+Salt&family=Roboto:wght@400;600&display=swap');

/* Styles globaux */
body {
    font-family: 'Roboto', sans-serif;
    background-color: #2C2F33 !important;
    color: white !important;
}

/* Titre principal */
h1, .card-header h2 {
    text-align: center;
    font-family: 'Rock Salt', cursive;
    text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.6);
    margin-top: 20px;
}

/* Bouton principal */
.btn-primary, .btn-success {
    font-weight: bold;
    padding: 12px 20px;
    border-radius: 8px;
    transition: transform 0.3s ease, background-color 0.3s ease;
    background-color: #FFC107 !important; /* Jaune */
    border-color: #FFC107 !important;
    color: black !important;
}

.btn-primary:hover, .btn-success:hover {
    background-color: #E0A800 !important; /* Jaune foncé */
    border-color: #E0A800 !important;
    transform: translateY(-2px);
}

/* Cartes */
.card {
    background-color: #444 !important;
    border-radius: 10px;
    text-align: center;
    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
    transition: transform 0.3s ease;
}

.card:hover {
    transform: scale(1.03);
}

/* Image de la recette */
.card img, .modal img {
    width: 100%;
    height: 200px;
    object-fit: cover;
    border-radius: 10px 10px 0 0;
}

/* Texte dans la carte */
.card-title {
    font-size: 1.2rem;
    font-weight: bold;
}

.card-text {
    font-size: 0.9rem;
    color: #ccc;
}

/* Boutons Modifier & Supprimer */
.btn-warning, .btn-danger {
    font-size: 0.9rem;
    font-weight: bold;
    border-radius: 5px;
    transition: transform 0.3s ease;
}

.btn-warning {
    background-color: #FF9800 !important;
    border-color: #FF9800 !important;
}

.btn-warning:hover {
    background-color: #E68900 !important;
}

.btn-danger {
    background-color: #f44336 !important;
    border-color: #f44336 !important;
}

.btn-danger:hover {
    background-color: #e53935 !important;
}

/* Modal Bootstrap */
.modal-content {
    background-color: #444 !important;
    color: white !important;
    border-radius: 12px;
}

.modal-header {
    border-bottom: 1px solid rgba(255, 255, 255, 0.2);
}

/* Style des champs de formulaire */
.form-control, .form-select {
    background-color: #222 !important;
    color: white !important;
    border: none !important;
    border-radius: 6px;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
}

.form-control:focus, .form-select:focus {
    background-color: #333 !important;
}

/* Bouton de fermeture du modal */
.btn-close {
    background-color: #f44336 !important;
    color: white !important;
    border-radius: 50%;
    transition: background-color 0.3s ease;
}

.btn-close:hover {
    background-color: #e53935 !important;
}

/* Liste des ingrédients */
.ingredients-list {
    list-style: none;
    padding: 0;
}

.ingredients-list li {
    background-color: #333;
    padding: 8px 15px;
    margin: 8px 0;
    border-radius: 5px;
    font-size: 15px;
    line-height: 1.5;
    color: white;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

/* Boutons de suppression des ingrédients */
.ingredients-list li button {
    width: 16px;
    height: 16px;
    background-color: black;
    color: white;
    border: none;
    border-radius: 50%;
    font-size: 12px;
    cursor: pointer;
    transition: background-color 0.3s ease;
}

.ingredients-list li button:hover {
    background-color: red;
}


</style>
<body class="bg-dark text-light">
    <div class="container py-5">
        <div class="card bg-secondary text-light shadow-lg mx-auto" style="max-width: 600px;">
            <div class="card-header text-center">
                <h2>Modifier la Recette</h2>
            </div>
            <div class="card-body">
                <!-- Affichage de l'image actuelle -->
                <div class="text-center mb-3">
    <?php if (!empty($imagePath) && file_exists($imagePath)): ?>
        <?php $fileType = mime_content_type($imagePath); ?>
        
        <?php if (strpos($fileType, "image") !== false): ?>
            <img src="<?= htmlspecialchars($imagePath, ENT_QUOTES, 'UTF-8') ?>" 
                 class="img-fluid rounded shadow" 
                 alt="Image de la recette" 
                 style="max-width: 300px; height: auto;">
        
        <?php elseif (strpos($fileType, "video") !== false): ?>
            <video class="img-fluid rounded shadow" controls style="max-width: 300px; height: auto;">
                <source src="<?= htmlspecialchars($imagePath, ENT_QUOTES, 'UTF-8') ?>" type="<?= $fileType ?>">
                Votre navigateur ne supporte pas la lecture de cette vidéo.
            </video>
        <?php endif; ?>
    <?php else: ?>
        <p class="text-warning">Aucune image ou vidéo disponible.</p>
    <?php endif; ?>
</div>


                <form action="editRecipe.php" method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="id" value="<?= htmlspecialchars($recette['id_recettes'], ENT_QUOTES, 'UTF-8') ?>">

                    <div class="mb-3">
                        <label for="titre" class="form-label">Titre :</label>
                        <input type="text" name="titre" id="titre" class="form-control" value="<?= htmlspecialchars($recette['titre'], ENT_QUOTES, 'UTF-8') ?>" required maxlength="100">
                    </div>
                    
                    <div class="mb-3">
                        <label for="prix" class="form-label">Prix (€) :</label>
                        <input type="number" name="prix" id="prix" class="form-control" step="0.01" min="0" 
                            value="<?= htmlspecialchars($recette['prix'], ENT_QUOTES, 'UTF-8') ?>" required>
                    </div>


                    <div class="mb-3">
                        <label for="description" class="form-label">Description :</label>
                        <textarea name="description" id="description" class="form-control" required rows="3"><?= htmlspecialchars_decode($recette['description'], ENT_QUOTES) ?></textarea>
                    </div>

                    <div class="mb-3">
                        <label for="categorie" class="form-label">Catégorie :</label>
                        <select name="categorie" id="categorie" class="form-select" required>
                            <option value="Entrée" <?= ($recette['categorie'] == 'Entrée' ? 'selected' : '') ?>>Entrée</option>
                            <option value="Plat" <?= ($recette['categorie'] == 'Plat' ? 'selected' : '') ?>>Plat</option>
                            <option value="Dessert" <?= ($recette['categorie'] == 'Dessert' ? 'selected' : '') ?>>Dessert</option>
                        </select>
                    </div>

                    <!-- Image -->
                    <div class="mb-3">
                        <label for="image" class="form-label">Changer l'image :</label>
                        <input type="file" name="image" id="image" class="form-control" accept="image/*">
                    </div>

                    <div class="mb-3">
                        <label for="ingredient-input" class="form-label">Ingrédients :</label>
                        <div class="input-group">
                            <input type="text" id="ingredient-input" class="form-control" placeholder="Ajouter un ingrédient">
                            <button type="button" class="btn btn-success" onclick="addIngredient()">
                                <i class="fas fa-plus"></i>
                            </button>
                        </div>
                        <ul id="ingredients-list" class="list-group mt-2">
                            <?php foreach ($ingredientsArray as $ingredient): ?>
                                <li class="list-group-item d-flex justify-content-between align-items-center bg-dark text-light">
                                    <?= htmlspecialchars($ingredient, ENT_QUOTES, 'UTF-8') ?>
                                    <button type="button" class="btn btn-sm btn-danger" onclick="removeIngredient(this)">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                        <input type="hidden" id="ingredients" name="ingredients" value="<?= htmlspecialchars($ingredientsList, ENT_QUOTES, 'UTF-8') ?>">
                    </div>

                    <div class="mb-3">
                        <label for="instructions" class="form-label">Instructions :</label>
                        <textarea id="instructions" name="instructions" class="form-control" rows="4" required><?= htmlspecialchars_decode($recette['instructions'], ENT_QUOTES) ?></textarea>
                    </div>

                    <button type="submit" class="btn btn-primary w-100">Enregistrer</button>
                    <a href="gestion_recette.php" class="btn btn-secondary w-100 mt-2">
    <i class="fas fa-arrow-left"></i> Retour
</a>

                </form>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        // Liste des ingrédients
        let ingredients = document.getElementById("ingredients").value.split("\n").filter(Boolean);

        // Fonction pour ajouter un ingrédient
        function addIngredient() {
            const ingredientInput = document.getElementById('ingredient-input');
            const ingredientValue = ingredientInput.value.trim();

            if (!ingredientValue) {
                alert("Veuillez entrer un ingrédient valide.");
                return;
            }

            if (ingredients.includes(ingredientValue)) {
                alert("Cet ingrédient a déjà été ajouté.");
                return;
            }

            // Ajouter à la liste
            ingredients.push(ingredientValue);
            updateIngredientsList();
            ingredientInput.value = ''; // Réinitialise le champ
        }

        // Fonction pour supprimer un ingrédient
        function removeIngredient(button) {
            const ingredientItem = button.parentElement;
            const ingredientValue = ingredientItem.firstChild.textContent.trim();

            // Supprimer l'ingrédient de la liste
            ingredients = ingredients.filter(ing => ing !== ingredientValue);
            updateIngredientsList();
        }

        // Fonction pour mettre à jour l'affichage des ingrédients
        function updateIngredientsList() {
            const ingredientsList = document.getElementById('ingredients-list');
            const hiddenInput = document.getElementById('ingredients');

            // Vider la liste
            ingredientsList.innerHTML = '';

            // Ajouter chaque ingrédient à la liste
            ingredients.forEach(ingredient => {
                const li = document.createElement('li');
                li.className = 'list-group-item d-flex justify-content-between align-items-center bg-dark text-light';
                li.textContent = ingredient;

                // Bouton de suppression
                const deleteButton = document.createElement('button');
                deleteButton.innerHTML = '<i class="fas fa-trash"></i>';
                deleteButton.className = 'btn btn-sm btn-danger';
                deleteButton.onclick = function () {
                    removeIngredient(deleteButton);
                };

                li.appendChild(deleteButton);
                ingredientsList.appendChild(li);
            });

            // Mettre à jour le champ caché
            hiddenInput.value = ingredients.join("\n");
        }

        // Initialiser la liste des ingrédients au chargement de la page
        document.addEventListener("DOMContentLoaded", updateIngredientsList);
        
        
        document.addEventListener("DOMContentLoaded", function () {
    const mediaInput = document.getElementById("image"); // Champ input pour le fichier
    const mediaPreview = document.querySelector(".text-center.mb-3"); // Conteneur de prévisualisation

    mediaInput.addEventListener("change", function () {
        const file = this.files[0];

        if (!file) {
            mediaPreview.innerHTML = "<p class='text-warning'>Aucun fichier sélectionné.</p>";
            return;
        }

        const fileURL = URL.createObjectURL(file);
        const fileType = file.type.split("/")[0]; // Vérifie si c'est une image ou une vidéo

        mediaPreview.innerHTML = ""; // Réinitialiser l'aperçu

        if (fileType === "image") {
            const img = document.createElement("img");
            img.src = fileURL;
            img.alt = "Aperçu de l'image";
            img.className = "img-fluid rounded shadow mt-2";
            img.style.maxWidth = "300px";
            mediaPreview.appendChild(img);
        } else if (fileType === "video") {
            const video = document.createElement("video");
            video.src = fileURL;
            video.controls = true;
            video.className = "img-fluid rounded shadow mt-2";
            video.style.maxWidth = "300px";
            mediaPreview.appendChild(video);
        } else {
            mediaPreview.innerHTML = "<p class='text-danger'>Format non supporté.</p>";
        }
    });
});

    </script>
</body>
</html>

