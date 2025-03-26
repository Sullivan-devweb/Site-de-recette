<?php
// Démarre la session PHP pour permettre l'utilisation des variables de session
session_start();

// Inclut le fichier de connexion à la base de données
require 'db_connect.php';

// Vérifie si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    header("Location: connexion_html.php"); // Redirige vers la page de connexion
    exit(); // Arrête l'exécution du script
}

// Vérifie si l'ID du commentaire est présent dans l'URL
if (!isset($_GET['id'])) {
    die("ID du commentaire manquant."); // Affiche un message d'erreur et arrête le script
}

// Récupère et sécurise l'ID du commentaire depuis l'URL
$id_commentaire = intval($_GET['id']);
// Récupère l'ID de l'utilisateur connecté depuis la session
$user_id = $_SESSION['user_id'];

// Vérifie que le commentaire appartient bien à l'utilisateur et récupère l'ID de la recette
$stmt = $pdo->prepare("SELECT commentaire, note, id_recettes FROM commentaires WHERE id_commentaire = ? AND id_utilisateur = ?");
$stmt->execute([$id_commentaire, $user_id]); // Exécute la requête avec l'ID du commentaire et l'ID de l'utilisateur
$comment = $stmt->fetch(PDO::FETCH_ASSOC); // Récupère le résultat sous forme de tableau associatif

// Vérifie si le commentaire existe
if (!$comment) {
    die("Vous n'avez pas le droit de modifier ce commentaire."); // Affiche un message d'erreur et arrête le script
}

// Récupère l'ID de la recette associée au commentaire
$id_recette = $comment['id_recettes'];

// Mettre à jour le commentaire si le formulaire est soumis
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Récupère et nettoie le nouveau commentaire
    $nouveau_commentaire = trim($_POST['commentaire']);
    // Récupère et convertit la nouvelle note en entier
    $nouvelle_note = intval($_POST['note']);

    // Vérifie que le commentaire et la note sont valides
    if (!empty($nouveau_commentaire) && $nouvelle_note >= 1 && $nouvelle_note <= 5) {
        // Prépare la requête SQL pour mettre à jour le commentaire
        $stmt_update = $pdo->prepare("UPDATE commentaires SET commentaire = ?, note = ?, date_commentaire = NOW() WHERE id_commentaire = ?");
        // Exécute la requête avec les nouvelles valeurs
        $stmt_update->execute([$nouveau_commentaire, $nouvelle_note, $id_commentaire]);

        // Redirige vers la page de détail de la recette après la modification
        header("Location: detailrecette.php?id=" . $id_recette);
        exit(); // Arrête l'exécution du script
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modifier Commentaire</title>
    <!-- Intègre Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-dark text-light">
    <div class="container py-5">
        <!-- Titre de la page -->
        <h1 class="text-warning">Modifier votre commentaire</h1>

        <!-- Formulaire de modification du commentaire -->
        <form method="POST">
            <div class="mb-3">
                <label class="form-label">Votre commentaire :</label>
                <!-- Champ de texte pour le commentaire -->
                <textarea name="commentaire" class="form-control" rows="3" required><?= htmlspecialchars($comment['commentaire'], ENT_QUOTES, 'UTF-8'); ?></textarea>
            </div>
            <div class="mb-3">
                <label class="form-label">Votre note :</label>
                <!-- Sélecteur de note -->
                <select name="note" class="form-select" required>
                    <option value="1" <?= $comment['note'] == 1 ? 'selected' : ''; ?>>1 ★</option>
                    <option value="2" <?= $comment['note'] == 2 ? 'selected' : ''; ?>>2 ★★</option>
                    <option value="3" <?= $comment['note'] == 3 ? 'selected' : ''; ?>>3 ★★★</option>
                    <option value="4" <?= $comment['note'] == 4 ? 'selected' : ''; ?>>4 ★★★★</option>
                    <option value="5" <?= $comment['note'] == 5 ? 'selected' : ''; ?>>5 ★★★★★</option>
                </select>
            </div>

            <!-- Champ caché pour transmettre l'ID de la recette -->
            <input type="hidden" name="recette_id" value="<?= $id_recette; ?>">

            <!-- Bouton de soumission du formulaire -->
            <button type="submit" class="btn btn-warning w-100">Mettre à jour</button>
        </form>

        <!-- Bouton de retour vers la page de détail de la recette -->
        <a href="detailrecette.php?id=<?= $id_recette; ?>" class="btn btn-secondary mt-3">Retour</a>
    </div>
</body>
</html>