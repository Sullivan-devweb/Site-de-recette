<?php
session_start();
include 'db_connect.php';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bienvenue sur Recettes Gourmandes</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="index.css">
    <link rel="stylesheet" href="headerfooter.css">
</head>
<body>
    <?php include 'header.php'; ?>

    <!-- Hero Section -->
    <section class="hero-section">
        <div class="container">
            <h1>Bienvenue sur Recettes Gourmandes</h1>
            <p>Découvrez des recettes savoureuses et faciles à réaliser pour épater vos proches !</p>
            <a href="listedesrecettes.php" class="btn btn-primary">Explorer les recettes</a>
        </div>
    </section>

    <!-- Feature Section -->
    <section class="feature-section">
        <div class="container text-center">
            <h2>Pourquoi choisir Recettes Gourmandes ?</h2>
            <div class="row">
                <div class="col-md-4">
                    <h3>Simple et Rapide</h3>
                    <p>Des recettes expliquées étape par étape pour gagner du temps en cuisine.</p>
                </div>
                <div class="col-md-4">
                    <h3>Pour Tous les Goûts</h3>
                    <p>Du plat familial au dessert gourmand, il y en a pour tout le monde.</p>
                </div>
                <div class="col-md-4">
                    <h3>Inspiration Quotidienne</h3>
                    <p>De nouvelles idées chaque jour pour ne jamais manquer d'inspiration.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Call to Action Section -->
    <section class="call-to-action-section text-center">
        <div class="container">
            <h2>Prêt à devenir un chef à la maison ?</h2>
            <p>Rejoignez notre communauté et accédez à des centaines de recettes exclusives.</p>
            <a href="inscription_html.php" class="btn btn-primary">Inscrivez-vous gratuitement</a>
        </div>
    </section>

    <!-- Footer -->
        
        <div class="text-center p-3 bg-dark text-white">
            © 2025 Recettes Gourmandes. Tous droits réservés.
        </div>
    </footer>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>