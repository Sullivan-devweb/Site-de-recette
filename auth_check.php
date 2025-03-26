<?php
// Démarre la session PHP pour permettre l'utilisation des variables de session
session_start();

// Vérifie si l'utilisateur est connecté en vérifiant la variable de session 'logged_in'
if (!isset($_SESSION['logged_in']) || !$_SESSION['logged_in']) {
    // Si l'utilisateur n'est pas connecté, redirige vers la page de connexion avec un message
    header("Location: connexion.php?message=Veuillez vous connecter.");
    exit(); // Arrête l'exécution du script
}

// Vérifie l'inactivité de l'utilisateur
if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity']) > 900) { 
    // 900 secondes = 15 minutes
    // Si l'utilisateur est inactif depuis plus de 15 minutes, détruire la session
    session_unset(); // Supprime toutes les variables de session
    session_destroy(); // Détruit la session

    // Redirige vers la page de connexion avec un message indiquant que la session a expiré
    header("Location: connexion.php?message=Session expirée. Veuillez vous reconnecter.&expired=1");
    exit(); // Arrête l'exécution du script
}

// Met à jour le temps de la dernière activité de l'utilisateur avec l'heure actuelle
$_SESSION['last_activity'] = time();
?>