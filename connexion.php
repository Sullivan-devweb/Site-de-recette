<?php
// Démarre la session PHP pour permettre l'utilisation des variables de session
session_start();

// Inclut le fichier de connexion à la base de données
require_once 'db_connect.php';

// Vérifie si la méthode de requête est POST (formulaire soumis)
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Récupère et sécurise l'email saisi par l'utilisateur
    $email = htmlspecialchars($_POST['email']);
    // Récupère le mot de passe saisi par l'utilisateur (non modifié pour vérification)
    $password = $_POST['password'];

    // Prépare la requête SQL pour récupérer les informations de l'utilisateur
    $stmt = $pdo->prepare("SELECT id_utilisateur, nom, prenom, mot_depasse FROM utilisateur WHERE email = :email");
    // Exécute la requête en remplaçant le paramètre :email par la valeur de $email
    $stmt->execute([':email' => $email]);
    // Récupère le résultat de la requête sous forme de tableau associatif
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    // Vérifie si l'utilisateur existe et si le mot de passe correspond
    if ($user && password_verify($password, $user['mot_depasse'])) {
        // Si les informations sont correctes, stocke les données dans la session
        $_SESSION['logged_in'] = true; // Indique que l'utilisateur est connecté
        $_SESSION['user_id'] = $user['id_utilisateur']; // Stocke l'ID de l'utilisateur
        $_SESSION['user_name'] = $user['nom']; // Stocke le nom de l'utilisateur
        $_SESSION['user_prenom'] = $user['prenom']; // Stocke le prénom de l'utilisateur
        $_SESSION['last_activity'] = time(); // Enregistre le temps de la dernière activité

        // Redirige l'utilisateur vers la page d'accueil avec un paramètre de succès
        header("Location: accueil.php?success=1");
        exit(); // Arrête l'exécution du script
    } else {
        // Si les informations sont incorrectes, affiche un message d'erreur
        echo "Email ou mot de passe incorrect.";
    }
}
?>