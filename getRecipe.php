<?php
// Démarrer la session pour accéder aux variables de session
session_start();

// Inclure le fichier de connexion à la base de données
include 'db_connect.php';

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_id']) || empty($_SESSION['user_id'])) {
    // Si l'utilisateur n'est pas connecté, renvoyer une réponse JSON avec un message d'erreur
    echo json_encode(['success' => false, 'message' => 'Utilisateur non connecté']);
    exit; // Arrêter l'exécution du script
}

// Récupérer l'ID de l'utilisateur à partir de la session
$idUtilisateur = $_SESSION['user_id'];

// Vérifier si l'ID de la recette est passé via la requête GET
if (!isset($_GET['id'])) {
    // Si l'ID de la recette est manquant, renvoyer une réponse JSON avec un message d'erreur
    echo json_encode(['success' => false, 'message' => 'ID de recette manquant']);
    exit; // Arrêter l'exécution du script
}

// Convertir l'ID de la recette en entier pour des raisons de sécurité
$idRecette = (int) $_GET['id'];

// Préparer une requête SQL pour récupérer les détails de la recette
// en s'assurant que la recette appartient à l'utilisateur connecté
$sql = "SELECT * FROM recettes WHERE id_recettes = :id_recette AND id_utilisateur = :id_utilisateur";
$stmt = $pdo->prepare($sql);

// Lier les paramètres à la requête SQL
$stmt->bindParam(':id_recette', $idRecette, PDO::PARAM_INT);
$stmt->bindParam(':id_utilisateur', $idUtilisateur, PDO::PARAM_INT);

// Exécuter la requête SQL
$stmt->execute();

// Récupérer les résultats de la requête sous forme de tableau associatif
$recette = $stmt->fetch(PDO::FETCH_ASSOC);

// Vérifier si une recette a été trouvée
if ($recette) {
    // Si une recette est trouvée, renvoyer une réponse JSON avec les détails de la recette
    echo json_encode(['success' => true, 'recipe' => $recette]);
} else {
    // Si aucune recette n'est trouvée, renvoyer une réponse JSON avec un message d'erreur
    echo json_encode(['success' => false, 'message' => 'Recette non trouvée']);
}
?>