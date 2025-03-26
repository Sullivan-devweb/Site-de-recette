<?php
// Démarre la session PHP pour permettre l'utilisation des variables de session
session_start();

// Inclut le fichier de connexion à la base de données
require 'db_connect.php';

// Vérifie si l'ID de l'utilisateur est passé dans l'URL et s'il est valide
if (!isset($_GET['id']) || empty($_GET['id']) || !is_numeric($_GET['id'])) {
    die("⚠️ Utilisateur non trouvé."); // Affiche un message d'erreur et arrête le script
}

// Récupère et sécurise l'ID de l'utilisateur depuis l'URL
$userId = intval($_GET['id']);

// Récupère les informations de l'utilisateur depuis la base de données
$stmt = $pdo->prepare("SELECT * FROM utilisateur WHERE id_utilisateur = ?");
$stmt->execute([$userId]); // Exécute la requête avec l'ID de l'utilisateur
$user = $stmt->fetch(PDO::FETCH_ASSOC); // Récupère le résultat sous forme de tableau associatif

// Vérifie si l'utilisateur existe
if (!$user) {
    die("⚠️ Utilisateur non trouvé."); // Affiche un message d'erreur et arrête le script
}

// Gestion de l'image de profil
$imageProfil = (!empty($user['image_profil']) && file_exists($user['image_profil'])) 
    ? htmlspecialchars($user['image_profil'], ENT_QUOTES, 'UTF-8') // Utilise l'image de profil si elle existe
    : "image/default-profile.png"; // Sinon, utilise une image par défaut
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <!-- Définit le jeu de caractères UTF-8 pour prendre en charge les caractères spéciaux -->
    <meta charset="UTF-8">
    <!-- Assure que la page est responsive en s'adaptant à la largeur de l'appareil -->
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- Titre de la page avec le prénom de l'utilisateur -->
    <title>Profil de <?= htmlspecialchars($user['prenom']); ?></title>
    <!-- Intègre Bootstrap CSS pour le style -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Intègre Font Awesome pour les icônes -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <!-- Lien vers le fichier CSS personnalisé pour le header et le footer -->
    <link rel="stylesheet" href="headerfooter.css">
    <!-- Styles CSS personnalisés pour cette page -->
    <style>
        /* Structure pour forcer le footer en bas de la page */
        html, body {
            height: 100%;
            display: flex;
            flex-direction: column;
            background-color: #1E1E1E;
            color: white;
        }

        .container {
            flex: 1; /* Permet au contenu de prendre tout l'espace disponible */
        }

        footer {
            background: #222;
            color: white;
            text-align: center;
            padding: 10px 0;
            margin-top: auto;
            width: 100%;
            position: relative;
            bottom: 0;
        }

        /* Conteneur du profil */
        .profile-container {
            max-width: 600px;
            margin: auto;
            background: #444;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
            text-align: center; /* Centrage du contenu */
        }

        /* En-tête du profil (image + nom) */
        .profile-header {
            display: flex;
            align-items: center;
            gap: 20px;
            margin-bottom: 15px;
            justify-content: center; /* Centrage horizontal */
        }

        /* Image de profil */
        .profile-image {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            object-fit: cover;
            border: 3px solid #FFC107;
        }

        /* Nom de l'utilisateur */
        .profile-name {
            font-size: 1.8rem;
            font-weight: bold;
            color: #FFC107;
            margin-bottom: 10px;
        }

        /* Icônes des réseaux sociaux */
        .social-icons {
            display: flex;
            justify-content: center; /* Centrage des icônes */
            gap: 15px;
            margin-top: 10px;
        }

        .social-icons a {
            font-size: 24px;
            color: white; /* Couleur originale des icônes */
            transition: transform 0.3s, color 0.3s;
        }

        .social-icons a:hover {
            transform: scale(1.2);
            color: #FFC107;
        }

        /* Éléments d'information (ville, école, études) */
        .info-item {
            background: #333;
            padding: 12px;
            margin: 10px 0;
            border-radius: 5px;
            font-size: 1.1rem;
            font-weight: bold;
            color: #FFC107;
        }
    </style>
</head>
<body>

<!-- Inclusion du header -->
<?php include 'header.php'; ?>

<!-- Contenu principal -->
<div class="container py-5">
    <div class="profile-container">
        
        <!-- En-tête : Image + Nom -->
        <div class="profile-header">
            <img src="<?= $imageProfil; ?>" alt="Photo de profil" class="profile-image">
            <div>
                <p class="profile-name"><?= htmlspecialchars($user['prenom']); ?></p>
                
                <!-- Icônes Réseaux Sociaux -->
                <div class="social-icons">
                    <?php if (!empty($user['twitter'])): ?>
                        <a href="<?= htmlspecialchars($user['twitter']); ?>" target="_blank">
                            <i class="fab fa-x-twitter"></i> <!-- Icône X (Twitter) -->
                        </a>
                    <?php endif; ?>
                    <?php if (!empty($user['instagram'])): ?>
                        <a href="<?= htmlspecialchars($user['instagram']); ?>" target="_blank">
                            <i class="fab fa-instagram"></i> <!-- Icône Instagram -->
                        </a>
                    <?php endif; ?>
                    <?php if (!empty($user['youtube'])): ?>
                        <a href="<?= htmlspecialchars($user['youtube']); ?>" target="_blank">
                            <i class="fab fa-youtube"></i> <!-- Icône YouTube -->
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Informations utilisateur (Centrées) -->
        <div class="info-item">📍 <strong>Ville :</strong> <?= !empty($user['ville']) ? htmlspecialchars($user['ville']) : 'Non renseigné'; ?></div>
        <div class="info-item">🎓 <strong>École :</strong> <?= !empty($user['ecole']) ? htmlspecialchars($user['ecole']) : 'Non renseigné'; ?></div>
        <div class="info-item">📖 <strong>Études :</strong> <?= !empty($user['etudes']) ? htmlspecialchars($user['etudes']) : 'Non renseigné'; ?></div>

        <!-- Bouton retour -->
        <div class="text-center mt-4">
            <a href="javascript:history.back()" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Retour
            </a>
        </div>
    </div>
</div>

<!-- Inclusion du footer -->
<?php include 'footer.php'; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>