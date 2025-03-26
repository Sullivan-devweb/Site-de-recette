<?php

$host = "127.0.0.1";
$dbname = "u501368352_culinaireDB";
$password = "DewPec46";
$username = "u501368352_SulliSam";

try {
    // Création de la connexion PDO
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);

    // Définir le mode de gestion des erreurs
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Supprimer ou commenter cette ligne pour ne pas afficher "Connexion réussie" à chaque fois
    // echo "Connexion réussie à la base de données!";
} catch (PDOException $e) {
    echo "Échec de la connexion : " . $e->getMessage();
}

?>