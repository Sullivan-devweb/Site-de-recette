<?php
// Démarrage de la session pour accéder aux variables de session
session_start();

// Inclusion des classes PHPMailer nécessaires
require 'PHPMailer-6.9.3/src/PHPMailer.php';
require 'PHPMailer-6.9.3/src/SMTP.php';
require 'PHPMailer-6.9.3/src/Exception.php';

// Inclusion du fichier de connexion à la base de données
require_once 'db_connect.php';

// Chargement de la configuration SMTP depuis un fichier externe
$config = require 'config.php';

// Initialisation de la variable $message pour stocker les retours d'opération
$message = '';

// Vérification si l'utilisateur est connecté
if (isset($_SESSION['user_id'])) {
    // Récupération de l'ID de l'utilisateur depuis la session
    $user_id = $_SESSION['user_id'];

    try {
        // Récupération de l'email de l'utilisateur depuis la base de données
        $stmt = $pdo->prepare("SELECT email FROM utilisateur WHERE id_utilisateur = :id_utilisateur");
        $stmt->execute([':id_utilisateur' => $user_id]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        // Vérification si l'utilisateur a été trouvé
        if ($user) {
            // Récupération de l'email de l'utilisateur
            $email = $user['email'];

            // Génération d'un token unique et sécurisé
            $token = bin2hex(random_bytes(32));

            // Mise à jour de la base de données avec le token et sa date d'expiration (1 heure)
            $stmt = $pdo->prepare("UPDATE utilisateur SET token_reinitialisation = :token, token_expiration = NOW() + INTERVAL 1 HOUR WHERE id_utilisateur = :id_utilisateur");
            $stmt->execute([':token' => $token, ':id_utilisateur' => $user_id]);

            // Configuration de PHPMailer pour l'envoi d'email via SMTP
            $mail = new PHPMailer\PHPMailer\PHPMailer(true);
            $mail->isSMTP();
            $mail->Host = $config['smtp_host'];
            $mail->SMTPAuth = true;
            $mail->Username = $config['smtp_user'];
            $mail->Password = $config['smtp_pass'];
            $mail->SMTPSecure = $config['smtp_secure'];
            $mail->Port = $config['smtp_port'];

            // Configuration de l'email
            $mail->setFrom($config['smtp_user'], 'Support Votre Site');
            $mail->addAddress($email);
            $mail->isHTML(true);
            $mail->Subject = 'Réinitialisation de votre mot de passe';

            // Génération du lien de réinitialisation avec le token
            $resetLink = "https://sitederecette.404cahorsfound.fr/reinitialiser_mdp.php?token=" . $token;

            // Corps de l'email avec le lien de réinitialisation
            $mail->Body = "
                Bonjour,<br><br>
                Vous avez demandé à réinitialiser votre mot de passe. Cliquez sur le lien ci-dessous pour procéder :<br><br>
                <a href='" . $resetLink . "'>Réinitialiser votre mot de passe</a><br><br>
                Ce lien est valide pendant 1 heure.<br><br>
                Si vous n'êtes pas à l'origine de cette demande, veuillez ignorer cet email.<br><br>
                Cordialement,<br>
                L'équipe de support.
            ";

            // Envoi de l'email
            $mail->send();
            $message = "Un email de réinitialisation a été envoyé à votre adresse.";
        } else {
            $message = "Aucun utilisateur trouvé avec cet ID.";
        }
    } catch (PHPMailer\PHPMailer\Exception $e) {
        $message = "Erreur lors de l'envoi de l'email : " . $e->getMessage();
    } catch (PDOException $pdoEx) {
        $message = "Erreur de base de données : " . $pdoEx->getMessage();
    }
} else {
    // Si l'utilisateur n'est pas connecté, traitement du formulaire de réinitialisation par email
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['email'])) {
        // Récupération et sécurisation de l'email saisi
        $email = htmlspecialchars(trim($_POST['email']));

        try {
            // Vérification de l'existence de l'email dans la base de données
            $stmt = $pdo->prepare("SELECT id_utilisateur FROM utilisateur WHERE email = :email");
            $stmt->execute([':email' => $email]);

            // Si l'email existe, procéder à la réinitialisation
            if ($stmt->rowCount() > 0) {
                // Génération d'un token unique et sécurisé
                $token = bin2hex(random_bytes(32));

                // Mise à jour de la base de données avec le token et sa date d'expiration
                $stmt = $pdo->prepare("UPDATE utilisateur SET token_reinitialisation = :token, token_expiration = NOW() + INTERVAL 1 HOUR WHERE email = :email");
                $stmt->execute([':token' => $token, ':email' => $email]);

                // Configuration de PHPMailer pour l'envoi d'email
                $mail = new PHPMailer\PHPMailer\PHPMailer(true);
                $mail->isSMTP();
                $mail->Host = $config['smtp_host'];
                $mail->SMTPAuth = true;
                $mail->Username = $config['smtp_user'];
                $mail->Password = $config['smtp_pass'];
                $mail->SMTPSecure = $config['smtp_secure'];
                $mail->Port = $config['smtp_port'];

                // Configuration de l'email
                $mail->setFrom($config['smtp_user'], 'Support Votre Site');
                $mail->addAddress($email);
                $mail->isHTML(true);
                $mail->Subject = 'Réinitialisation de votre mot de passe';

                // Génération du lien de réinitialisation
                $resetLink = "https://sitederecette.404cahorsfound.fr/reinitialiser_mdp.php?token=" . $token;

                // Corps de l'email
                $mail->Body = "
                    Bonjour,<br><br>
                    Vous avez demandé à réinitialiser votre mot de passe. Cliquez sur le lien ci-dessous pour procéder :<br><br>
                    <a href='" . $resetLink . "'>Réinitialiser votre mot de passe</a><br><br>
                    Ce lien est valide pendant 1 heure.<br><br>
                    Si vous n'êtes pas à l'origine de cette demande, veuillez ignorer cet email.<br><br>
                    Cordialement,<br>
                    L'équipe de support.
                ";

                // Envoi de l'email
                $mail->send();
                $message = "Un email de réinitialisation a été envoyé à votre adresse.";
            } else {
                $message = "Cette adresse email n'est pas enregistrée.";
            }
        } catch (PHPMailer\PHPMailer\Exception $e) {
            $message = "Erreur lors de l'envoi de l'email : " . $e->getMessage();
        } catch (PDOException $pdoEx) {
            $message = "Erreur de base de données : " . $pdoEx->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Réinitialisation de mot de passe</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Police Rock Salt -->
    <link href="https://fonts.googleapis.com/css2?family=Rock+Salt&display=swap" rel="stylesheet">
    <!-- Votre CSS personnalisé -->
    <link rel="stylesheet" href="reset_password.css">
</head>
<body>
    <div class="container d-flex justify-content-center align-items-center" style="min-height: 100vh;">
        <div class="card shadow-lg p-4" style="background-color: #333; border-radius: 15px;">
            <h2 class="text-center mb-4" style="color: #ffcc00; text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.6);">Réinitialiser votre mot de passe</h2>
            <?php if (!isset($_SESSION['user_id'])): ?>
                <form method="POST" action="">
                    <div class="mb-3">
                        <label for="email" class="form-label" style="color: #ffcc00;">Adresse email</label>
                        <input type="email" id="email" name="email" class="form-control" placeholder="Entrez votre email" required>
                    </div>
                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary" style="background: linear-gradient(135deg, #ffcc00, #e6b800); border: none; color: black; font-weight: bold;">Envoyer le lien</button>
                    </div>
                </form>
            <?php endif; ?>
            <?php if ($message): ?>
                <div class="alert alert-info mt-3"><?php echo $message; ?></div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>