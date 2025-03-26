<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inscription</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Police Rock Salt -->
    <link href="https://fonts.googleapis.com/css2?family=Rock+Salt&display=swap" rel="stylesheet">
    <!-- Votre CSS personnalisé -->
    <link rel="stylesheet" href="inscription_connexion.css">
    <style>
        .required::after {
            content: " *";
            color: red;
        }
    </style>
</head>
<body>
    <section class="signup-form">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-md-8 col-lg-6">
                    <div class="card shadow-lg p-4" style="background-color: #333; border-radius: 15px;">
                        <h2 class="text-center mb-4" style="color: #ffcc00; text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.6);">Créez un compte</h2>
                          <!-- Message d'information -->
                        <p class="text-center mb-3" style="color: white;">
                            <span style="color: red;">*</span> Champ obligatoire
                        </p>
                        <form action="inscription.php" method="POST" enctype="multipart/form-data">
                            <!-- Nom et Prénom -->
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="nom" class="form-label required" style="color: #ffcc00;">Nom</label>
                                    <input type="text" id="nom" name="nom" class="form-control" required>
                                </div>
                                <div class="col-md-6">
                                    <label for="prenom" class="form-label required" style="color: #ffcc00;">Prénom</label>
                                    <input type="text" id="prenom" name="prenom" class="form-control" required>
                                </div>
                            </div>

                            <!-- Email -->
                            <div class="mb-3">
                                <label for="email" class="form-label required" style="color: #ffcc00;">Email</label>
                                <input type="email" id="email" name="email" class="form-control" required>
                            </div>

                            <!-- Mot de passe -->
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="password" class="form-label required" style="color: #ffcc00;">Mot de passe</label>
                                    <input type="password" id="password" name="password" class="form-control" required>
                                </div>
                                <div class="col-md-6">
                                    <label for="confirm_password" class="form-label required" style="color: #ffcc00;">Confirmez le mot de passe</label>
                                    <input type="password" id="confirm_password" name="confirm_password" class="form-control" required>
                                </div>
                            </div>

                            <!-- Ville, École, Études -->
                            <div class="mb-3">
                                <label for="ville" class="form-label required" style="color: #ffcc00;">Ville</label>
                                <input type="text" id="ville" name="ville" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label for="ecole" class="form-label" style="color: #ffcc00;">École</label>
                                <input type="text" id="ecole" name="ecole" class="form-control">
                            </div>
                            <div class="mb-3">
                                <label for="etudes" class="form-label" style="color: #ffcc00;">Études Post-Bac</label>
                                <input type="text" id="etudes" name="etudes" class="form-control">
                            </div>

                            <!-- Image de profil -->
                            <div class="mb-3">
                                <label for="image_profil" class="form-label required" style="color: #ffcc00;">Image de profil</label>
                                <input type="file" id="image_profil" name="image_profil" class="form-control" accept="image/*" required>
                                <div id="image-preview" class="mt-2">
                                    <img id="preview" src="#" alt="Prévisualisation de l'image" style="display: none; max-width: 100px; max-height: 100px; border-radius: 8px;">
                                </div>
                            </div>

                            <!-- Bouton d'inscription -->
                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary" style="background: linear-gradient(135deg, #ffcc00, #e6b800); border: none; color: black; font-weight: bold;">S'inscrire</button>
                            </div>
                        </form>

                        <!-- Lien de connexion -->
                        <div class="text-center mt-3">
                            <p style="color: #ffcc00;">Vous avez déjà un compte ? <a href="connexion_html.php" class="quick-link" style="color: #ffcc00; text-decoration: underline;">Se connecter</a></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Votre JavaScript personnalisé -->
    <script src="inscription_connexion.js"></script>
</body>
</html>
