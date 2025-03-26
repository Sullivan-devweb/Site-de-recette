<?php
session_start();  // Démarre la session
session_unset();  // Supprime toutes les variables de session
session_destroy();  // Détruit la session

// Redirige l'utilisateur vers la page d'accueil (index.php)
header("Location: index.php");
exit();
?>
