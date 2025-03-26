<?php
// Activer le mode debug pour afficher toutes les erreurs PHP
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Définir le type de contenu de la réponse comme JSON
header('Content-Type: application/json');

// Inclure le fichier de connexion à la base de données
require 'db_connect.php';

// Vérifier si la méthode de requête est POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Récupérer les données JSON envoyées dans le corps de la requête
    $input = json_decode(file_get_contents('php://input'), true);
    // Récupérer et sécuriser l'ID de la recette
    $recipeId = intval($input['id'] ?? 0);

    // Vérifier si l'ID de la recette est valide
    if ($recipeId > 0) {
        // Préparer la requête SQL pour supprimer la recette
        $query = $pdo->prepare("DELETE FROM recettes WHERE id_recettes = ?");
        // Exécuter la requête avec l'ID de la recette
        $success = $query->execute([$recipeId]);

        // Vérifier si la suppression a réussi
        if ($success) {
            // Retourner une réponse JSON indiquant le succès
            echo json_encode(['success' => true]);
        } else {
            // Retourner une réponse JSON indiquant une erreur lors de la suppression
            echo json_encode(['success' => false, 'message' => "Erreur lors de la suppression."]);
        }
    } else {
        // Retourner une réponse JSON indiquant que l'ID est invalide
        echo json_encode(['success' => false, 'message' => "ID invalide."]);
    }
} else {
    // Retourner une réponse JSON indiquant que la méthode HTTP n'est pas autorisée
    http_response_code(405); // Méthode non autorisée
    echo json_encode(['success' => false, 'message' => "Méthode non autorisée."]);
}
?>