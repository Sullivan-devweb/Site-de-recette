<?php
// Démarrage de la session pour la gestion des variables de session
session_start();

// Inclusion du fichier de connexion à la base de données
require_once 'db_connect.php';

// Vérification de la méthode de requête : doit être POST pour traiter les données du formulaire
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Nettoyage et sécurisation des données du formulaire pour prévenir les attaques XSS
    $nom = htmlspecialchars($_POST['nom']);
    $prenom = htmlspecialchars($_POST['prenom']);
    $email = htmlspecialchars($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $ville = htmlspecialchars($_POST['ville']);
    $ecole = htmlspecialchars($_POST['ecole']);
    $etudes = htmlspecialchars($_POST['etudes']);

    // Vérification de la correspondance des mots de passe
    if ($password !== $confirm_password) {
        echo "Les mots de passe ne correspondent pas.";
        exit(); // Arrêt du script en cas de non-correspondance
    }

    // Hachage du mot de passe avec l'algorithme bcrypt pour une sécurité renforcée
    $hashed_password = password_hash($password, PASSWORD_BCRYPT);

    // Préparation et exécution d'une requête pour vérifier si l'email existe déjà dans la base de données
    $stmt = $pdo->prepare("SELECT id_utilisateur FROM utilisateur WHERE email = :email");
    $stmt->execute([':email' => $email]);

    // Vérification du nombre de lignes retournées : si supérieur à 0, l'email est déjà utilisé
    if ($stmt->rowCount() > 0) {
        echo "Cet email est déjà utilisé.";
    } else {
        // Gestion de l'upload de l'image de profil
        $image_profil = null; // Initialisation à null par défaut

        // Vérification si un fichier image a été uploadé sans erreur
        if (isset($_FILES['image_profil']) && $_FILES['image_profil']['error'] === UPLOAD_ERR_OK) {
            $upload_dir = 'uploads/'; // Dossier de destination pour les uploads
            // Création du dossier s'il n'existe pas
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0755, true);
            }
            $image_name = basename($_FILES['image_profil']['name']); // Nom du fichier uploadé
            $image_path = $upload_dir . uniqid() . '_' . $image_name; // Chemin unique pour éviter les conflits de noms
            // Déplacement du fichier temporaire vers le dossier de destination
            if (move_uploaded_file($_FILES['image_profil']['tmp_name'], $image_path)) {
                $image_profil = $image_path; // Mise à jour du chemin de l'image
            } else {
                echo "Erreur lors de l'upload de l'image.";
                exit(); // Arrêt du script en cas d'erreur d'upload
            }
        }

        // Préparation et exécution de la requête d'insertion de l'utilisateur dans la base de données
        $stmt = $pdo->prepare("INSERT INTO utilisateur (nom, prenom, email, mot_depasse, ville, ecole, etudes, image_profil, date_inscription) 
                                VALUES (:nom, :prenom, :email, :password, :ville, :ecole, :etudes, :image_profil, CURDATE())");
        // Exécution de la requête avec les données du formulaire
        if ($stmt->execute([
            ':nom' => $nom,
            ':prenom' => $prenom,
            ':email' => $email,
            ':password' => $hashed_password,
            ':ville' => $ville,
            ':ecole' => $ecole,
            ':etudes' => $etudes,
            ':image_profil' => $image_profil
        ])) {
            // Redirection vers la page d'accueil après une inscription réussie
            header("Location: https://sitederecette.404cahorsfound.fr/connexion_html.php");
            exit(); // Arrêt du script après la redirection
        } else {
            echo "Erreur lors de l'inscription.";
        }
    }
}
?>