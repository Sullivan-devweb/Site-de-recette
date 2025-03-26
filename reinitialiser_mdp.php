<?php
// Inclusion du fichier de connexion à la base de données
require_once 'db_connect.php';

// Initialisation de la variable $reset_successful pour suivre le succès de la réinitialisation
$reset_successful = false; // Par défaut, la réinitialisation est considérée comme un échec

// Vérification de la présence du token dans l'URL
if (isset($_GET['token'])) {
    // Récupération du token depuis l'URL
    $token = $_GET['token'];

    // Vérification si le formulaire de réinitialisation a été soumis (méthode POST)
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        // Récupération des nouveaux mots de passe depuis le formulaire
        $newPassword = $_POST['new-password'];
        $confirmPassword = $_POST['confirm-password'];

        // Validation de la correspondance des mots de passe
        if ($newPassword !== $confirmPassword) {
            // Affichage d'un message d'erreur si les mots de passe ne correspondent pas
            echo "<p>Les mots de passe ne correspondent pas.</p>";
        } else {
            // Hachage du nouveau mot de passe pour des raisons de sécurité
            $hashedPassword = password_hash($newPassword, PASSWORD_BCRYPT);

            // Préparation et exécution d'une requête SQL pour vérifier le token et sa validité (non expiré)
            $stmt = $pdo->prepare("SELECT * FROM utilisateur WHERE token_reinitialisation = :token AND token_expiration > NOW()");
            $stmt->execute(['token' => $token]);
            $user = $stmt->fetch();

            // Vérification si un utilisateur avec le token valide a été trouvé
            if ($user) {
                // Préparation et exécution d'une requête SQL pour mettre à jour le mot de passe de l'utilisateur
                // et supprimer le token de réinitialisation pour empêcher sa réutilisation
                $updateStmt = $pdo->prepare("UPDATE utilisateur SET mot_depasse = :password, token_reinitialisation = NULL, token_expiration = NULL WHERE token_reinitialisation = :token");
                $updateStmt->execute(['password' => $hashedPassword, 'token' => $token]);

                // Indication que la réinitialisation a réussi
                $reset_successful = true;
            } else {
                // Indication que le token est invalide ou expiré
                $reset_successful = false;
            }
        }
    }
} else {
    // Indication que le token est manquant dans l'URL
    $reset_successful = 'token_missing';
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Réinitialiser le mot de passe</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Rock+Salt&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="reinitialiser_mdp.css">
</head>
<body>
    <div class="container d-flex justify-content-center align-items-center" style="min-height: 100vh;">
        <div class="card shadow-lg p-4" style="background-color: #333; border-radius: 15px;">
            <h2 class="text-center mb-4" style="color: #ffcc00; text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.6);">Réinitialiser votre mot de passe</h2>
            <form action="reinitialiser_mdp.php?token=<?php echo htmlspecialchars($token); ?>" method="POST">
                <div class="mb-3">
                    <label for="new-password" class="form-label" style="color: #ffcc00;">Nouveau mot de passe</label>
                    <input type="password" id="new-password" name="new-password" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label for="confirm-password" class="form-label" style="color: #ffcc00;">Confirmer le mot de passe</label>
                    <input type="password" id="confirm-password" name="confirm-password" class="form-control" required>
                </div>
                <div class="d-grid">
                    <button type="submit" class="btn btn-primary" style="background: linear-gradient(135deg, #ffcc00, #e6b800); border: none; color: black; font-weight: bold;">Réinitialiser le mot de passe</button>
                </div>
            </form>
            <div class="php-message mt-3">
                <?php
                // Affichage des messages de succès ou d'erreur en fonction du résultat de la réinitialisation
                if ($reset_successful === true) {
                    echo "<p class='alert alert-success'>Votre mot de passe a été réinitialisé avec succès.</p>";
                    echo '<p><a href="connexion_html.php" class="alert-link" style="color: #ffcc00;">Cliquez ici pour vous connecter</a></p>';
                } elseif ($reset_successful === false) {
                    echo "<p class='alert alert-danger'>Token invalide ou expiré.</p>";
                } elseif ($reset_successful === 'token_missing') {
                    echo "<p class='alert alert-danger'>Token manquant.</p>";
                }
                ?>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>