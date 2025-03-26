<?php
// Vérifie si l'utilisateur est connecté (si l'ID de l'utilisateur est présent dans la session)
if (isset($_SESSION['user_id'])) {
    // Récupère l'ID de l'utilisateur à partir de la session
    $id_utilisateur = $_SESSION['user_id'];
    
    // Prépare et exécute une requête SQL pour compter les notifications non lues de l'utilisateur
    $stmt_notif_count = $pdo->prepare("SELECT COUNT(*) AS non_lues FROM notifications WHERE id_utilisateur = ? AND lue = 0");
    $stmt_notif_count->execute([$id_utilisateur]);
    
    // Récupère le nombre de notifications non lues ou définit à 0 si aucune notification n'est trouvée
    $notif_count = $stmt_notif_count->fetch(PDO::FETCH_ASSOC)['non_lues'] ?? 0;

    // Prépare et exécute une requête SQL pour récupérer l'image de profil de l'utilisateur
    $stmt_image_profil = $pdo->prepare("SELECT image_profil FROM utilisateur WHERE id_utilisateur = ?");
    $stmt_image_profil->execute([$id_utilisateur]);
    
    // Récupère le chemin de l'image de profil ou définit à 'default-avatar.png' si aucune image n'est trouvée
    $image_profil = $stmt_image_profil->fetch(PDO::FETCH_ASSOC)['image_profil'] ?? 'default-avatar.png';
}
?>

<header>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                <img src="image/logo.png" alt="Logo Recettes" height="100">
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                
                <?php if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true): ?>
                    <ul class="navbar-nav me-auto">
                        <li class="nav-item"><a class="nav-link" href="accueil.php">Accueil</a></li>
                        <li class="nav-item"><a class="nav-link" href="gestion_recette.php">Mes recettes</a></li>
                        <li class="nav-item"><a class="nav-link" href="listedesrecettes.php">Listes des Recettes</a></li>
                    </ul>

                    <div class="d-flex align-items-center me-3">
                        <a href="edit_profile.php" class="text-decoration-none">
                            <img src="<?= htmlspecialchars($image_profil); ?>" alt="Photo de profil" class="rounded-circle me-2" width="40" height="40" style="object-fit: cover;">
                        </a>
                        <div class="navbar-text text-warning">
                            Bonjour <strong><?= htmlspecialchars($_SESSION['user_prenom']); ?></strong>
                        </div>
                    </div>

                    <div class="me-3 position-relative">
                        <a href="notifications.php" class="text-decoration-none">
                            <i class="fas fa-bell text-light"></i>
                            <?php if ($notif_count > 0): ?>
                                <span class="badge bg-danger position-absolute top-0 start-100 translate-middle">
                                    <?= $notif_count; ?>
                                </span>
                            <?php endif; ?>
                        </a>
                    </div>

                    <a href="logout.php" class="btn btn-outline-warning">Déconnexion</a>
                
                <?php else: ?>
                    <a href="inscription_html.php" class="btn btn-warning me-2">Inscription</a>
                    <a href="connexion_html.php" class="btn btn-outline-light">Connexion</a>
                <?php endif; ?>

            </div>
        </div>
    </nav>
</header>