<?php
session_start();
require 'db_connect.php';

// Vérification si le formulaire a été soumis
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Récupération et nettoyage des données du formulaire
    $titre = $_POST['titre'] ?? null;
    $description = $_POST['description'] ?? null;
    $ingredients = $_POST['ingredients'] ?? null;
    $instructions = $_POST['instructions'] ?? null;
    $categorie = $_POST['categorie'] ?? null;
    $prix = floatval($_POST['prix'] ?? 0); // Récupération du prix et conversion en float
    $id_utilisateur = $_SESSION['user_id'] ?? null;

    $mediaPath = null; // Initialisation du chemin du fichier média

    // Gestion du fichier média téléchargé
    if (!empty($_FILES['media']['name'])) {
        $uploadDir = 'uploads/'; // Dossier de destination pour les fichiers téléchargés
        $fileName = time() . '_' . basename($_FILES['media']['name']); // Nom unique du fichier
        $targetPath = $uploadDir . $fileName; // Chemin complet du fichier de destination

        // Création du dossier de destination si inexistant
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true); // Création récursive du dossier
        }

        // Vérification du type de fichier autorisé
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'video/mp4', 'video/webm'];
        if (in_array($_FILES['media']['type'], $allowedTypes)) {
            // Déplacement du fichier téléchargé vers le dossier de destination
            if (move_uploaded_file($_FILES['media']['tmp_name'], $targetPath)) {
                $mediaPath = $targetPath; // Mise à jour du chemin du fichier média
            } else {
                // Gestion de l'erreur de téléchargement
                die("Erreur lors du téléchargement du fichier.");
            }
        } else {
            // Gestion du format de fichier non pris en charge
            die("Format de fichier non pris en charge.");
        }
    }

    // Préparation et exécution de la requête SQL pour insérer la recette dans la base de données
    $sql = "INSERT INTO recettes (titre, description, ingredients, instructions, categorie, date_ajout, id_utilisateur, image, prix) 
            VALUES (?, ?, ?, ?, ?, NOW(), ?, ?, ?)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$titre, $description, $ingredients, $instructions, $categorie, $id_utilisateur, $mediaPath, $prix]);

    // Redirection vers la liste des recettes après l'ajout
    header('Location: listedesrecettes.php');
    exit();
}
?>