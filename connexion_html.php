<!DOCTYPE html>
<html lang="fr">
<head>
    <!-- Définit le jeu de caractères UTF-8 pour prendre en charge les caractères spéciaux -->
    <meta charset="UTF-8">
    <!-- Assure que la page est responsive en s'adaptant à la largeur de l'appareil -->
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- Titre de la page affiché dans l'onglet du navigateur -->
    <title>Connexion</title>
    <!-- Intègre Bootstrap CSS pour le style -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Intègre la police Google Fonts "Rock Salt" -->
    <link href="https://fonts.googleapis.com/css2?family=Rock+Salt&display=swap" rel="stylesheet">
    <!-- Lien vers votre fichier CSS personnalisé -->
    <link rel="stylesheet" href="inscription_connexion.css">
</head>
<body>
    <!-- Section principale du formulaire de connexion -->
    <section class="login-form">
        <div class="container">
            <div class="row justify-content-center">
                <!-- Colonne centrée pour le formulaire -->
                <div class="col-md-6 col-lg-4">
                    <!-- Carte stylisée pour le formulaire -->
                    <div class="card shadow-lg p-4" style="background-color: #333; border-radius: 15px;">
                        <!-- Titre du formulaire -->
                        <h2 class="text-center mb-4" style="color: #ffcc00; text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.6);">Connectez-vous</h2>
                        <!-- Formulaire de connexion avec méthode POST -->
                        <form action="connexion.php" method="POST">
                            <!-- Champ pour l'email -->
                            <div class="mb-3">
                                <label for="email-login" class="form-label" style="color: #ffcc00;">Email</label>
                                <input type="email" id="email-login" name="email" class="form-control" required>
                            </div>
                            <!-- Champ pour le mot de passe -->
                            <div class="mb-3">
                                <label for="password-login" class="form-label" style="color: #ffcc00;">Mot de passe</label>
                                <input type="password" id="password-login" name="password" class="form-control" required>
                            </div>
                            <!-- Bouton de soumission du formulaire -->
                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary" style="background: linear-gradient(135deg, #ffcc00, #e6b800); border: none; color: black; font-weight: bold;">Se connecter</button>
                            </div>
                        </form>
                        <!-- Liens supplémentaires sous le formulaire -->
                        <div class="text-center mt-3">
                            <!-- Lien vers la page d'inscription -->
                            <p style="color: #ffcc00;">Pas encore de compte ? <a href="inscription_html.php" class="quick-link" style="color: #ffcc00; text-decoration: underline;">S'inscrire</a></p>
                            <!-- Lien vers la page de réinitialisation du mot de passe -->
                            <p><a href="reset_password.php" class="reset-link" style="color: #ffcc00; text-decoration: underline;">Mot de passe oublié ?</a></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Intègre Bootstrap JS pour les fonctionnalités interactives -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Lien vers votre fichier JavaScript personnalisé -->
    <script src="inscription_connexion.js"></script>
</body>
</html>