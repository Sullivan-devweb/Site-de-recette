<?php
// Démarre la session PHP pour permettre l'utilisation des variables de session
session_start();

// Inclut le fichier de connexion à la base de données
require 'db_connect.php';

// Vérifie si l'utilisateur est connecté en vérifiant la présence de 'user_id' dans la session
if (!isset($_SESSION['user_id'])) {
    // Si l'utilisateur n'est pas connecté, redirige vers la page de connexion
    header("Location: connexion_html.php");
    exit(); // Arrête l'exécution du script
}

// Vérifie si l'ID du commentaire est présent dans l'URL
if (!isset($_GET['id'])) {
    // Si l'ID du commentaire est manquant, affiche un message d'erreur et arrête le script
    die("ID du commentaire manquant.");
}

// Récupère et sécurise l'ID du commentaire depuis l'URL
$id_commentaire = intval($_GET['id']);
// Récupère l'ID de l'utilisateur connecté depuis la session
$user_id = $_SESSION['user_id'];

// Vérifie que le commentaire appartient bien à l'utilisateur connecté
$stmt = $pdo->prepare("SELECT id_utilisateur FROM commentaires WHERE id_commentaire = ?");
$stmt->execute([$id_commentaire]); // Exécute la requête avec l'ID du commentaire
$comment = $stmt->fetch(PDO::FETCH_ASSOC); // Récupère le résultat sous forme de tableau associatif

// Si le commentaire n'existe pas ou n'appartient pas à l'utilisateur connecté
if (!$comment || $comment['id_utilisateur'] != $user_id) {
    // Affiche un message d'erreur et arrête le script
    die("Vous n'avez pas le droit de supprimer ce commentaire.");
}

// Supprime le commentaire de la base de données
$stmt_delete = $pdo->prepare("DELETE FROM commentaires WHERE id_commentaire = ?");
$stmt_delete->execute([$id_commentaire]); // Exécute la requête de suppression

// Redirige l'utilisateur vers la page précédente
header("Location: " . $_SERVER['HTTP_REFERER']);
exit(); // Arrête l'exécution du script
?>