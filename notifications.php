<?php
// Démarrage de la session pour la gestion des variables de session
session_start();

// Activation de l'affichage des erreurs pour le débogage
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Inclusion du fichier de connexion à la base de données
require 'db_connect.php';

// Vérification si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    // Redirection vers la page de connexion si l'utilisateur n'est pas connecté
    header("Location: connexion_html.php");
    exit();
}

// Récupération de l'ID de l'utilisateur à partir de la session
$userId = $_SESSION['user_id'];

// Requête SQL pour récupérer les notifications de l'utilisateur avec les informations du commentaire associé
// Jointure avec les tables commentaires et utilisateur pour obtenir le prénom du commentateur et le contenu du commentaire
$sql = "SELECT n.id_notifications, n.message, n.date_envoi, n.lue, n.id_recettes, 
                uc.prenom AS commentateur, c.commentaire 
        FROM notifications n
        LEFT JOIN commentaires c ON n.id_recettes = c.id_recettes 
        LEFT JOIN utilisateur uc ON c.id_utilisateur = uc.id_utilisateur
        WHERE n.id_utilisateur = ? 
        ORDER BY n.date_envoi DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute([$userId]);
$notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Marquer les notifications comme lues après leur affichage
// Suppression des notifications pour l'utilisateur actuel
if (!empty($notifications)) {
    $updateSQL = "DELETE FROM notifications WHERE id_utilisateur = ?";
    $stmtUpdate = $pdo->prepare($updateSQL);
    $stmtUpdate->execute([$userId]);
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notifications</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="headerfooter.css" rel="stylesheet">
    <style>
        /* Style principal pour que le footer reste en bas de la page */
        body {
            display: flex;
            flex-direction: column;
            min-height: 100vh;
            background-color: #1E1E1E;
            color: #fff;
        }
        .content {
            flex: 1; // Permet au contenu de prendre tout l'espace disponible
        }
        .footer {
            background-color: #222;
            color: #bbb;
            text-align: center;
            padding: 10px 0;
        }
        
        /* Style des notifications */
        .notification-box {
            padding: 15px;
            border-radius: 10px;
            transition: 0.3s ease-in-out;
        }
        .notification-box:hover {
            transform: scale(1.02);
            box-shadow: 0 4px 8px rgba(255, 215, 0, 0.3);
        }
        .notif-icon {
            font-size: 1.5rem;
            margin-right: 10px;
        }
        .notif-unread {
            background-color: #212529;
            border-left: 5px solid #ffc107;
        }
        .notif-read {
            background-color: #343a40;
        }
        .btn-notif {
            margin-top: 10px;
            text-decoration: none;
        }
    </style>
</head>
<body>
    <?php include 'header.php'; ?>

    <main class="container content mt-4">
        <div class="container py-5">
            <h1 class="text-warning text-center">
                <i class="fas fa-bell"></i> Notifications
            </h1>
            <div class="mt-4">
                <?php if (!empty($notifications)): ?>
                    <?php foreach ($notifications as $notif): ?>
                        <div class="notification-box <?= $notif['lue'] == 0 ? 'notif-unread' : 'notif-read'; ?> mb-3 p-3">
                            <div class="d-flex align-items-center">
                                <i class="fas fa-comment-alt notif-icon text-warning"></i>
                                <div>
                                    <p class="mb-1"><strong><?= htmlspecialchars($notif['commentateur'] ?? 'Utilisateur inconnu'); ?></strong> a commenté votre recette :</p>
                                    <p class="text-white mb-1"><em><?= htmlspecialchars($notif['commentaire'] ?? $notif['message']); ?></em></p>
                                    <p class="text-white mb-1">Posté le <?= $notif['date_envoi']; ?></p>
                                </div>
                            </div>
                            <?php if (!empty($notif['id_recettes'])): ?>
                                <a href="detailrecette.php?id=<?= $notif['id_recettes']; ?>" class="btn btn-sm btn-warning btn-notif">
                                    <i class="fas fa-eye"></i> Voir la recette
                                </a>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p class="text-center text-light mt-4">Aucune notification pour le moment.</p>
                <?php endif; ?>
            </div>
        </div>
    </main>

    <footer class="footer">
        <?php include 'footer.php'; ?>
    </footer>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>