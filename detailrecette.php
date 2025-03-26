<?php
// Démarre la session PHP pour permettre l'utilisation des variables de session
session_start();

// Inclut le fichier de connexion à la base de données
require 'db_connect.php';

// Active l'affichage des erreurs pour le débogage
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Récupère l'ID de la recette depuis l'URL
$id_recettes = $_GET['id'] ?? null;

// Vérifie si l'ID de la recette est valide
if (!$id_recettes) {
    die("Recette non trouvée.");
}

// Récupère les détails de la recette depuis la base de données
$sql_recette = "SELECT * FROM recettes WHERE id_recettes = ?";
$stmt_recette = $pdo->prepare($sql_recette);
$stmt_recette->execute([$id_recettes]);
$recette = $stmt_recette->fetch(PDO::FETCH_ASSOC);

// Vérifie si la recette existe
if (!$recette) {
    die("Recette non trouvée.");
}

// Récupère les commentaires avec le prénom et l'ID de l'utilisateur
$sql_comments = "SELECT c.id_commentaire, c.commentaire, c.note, c.date_commentaire, c.id_utilisateur, u.prenom, u.image_profil
                 FROM commentaires c 
                 JOIN utilisateur u ON c.id_utilisateur = u.id_utilisateur 
                 WHERE c.id_recettes = ? 
                 ORDER BY c.date_commentaire DESC";
$stmt_comments = $pdo->prepare($sql_comments);
$stmt_comments->execute([$id_recettes]);
$comments = $stmt_comments->fetchAll(PDO::FETCH_ASSOC);

// Récupère l'auteur de la recette
$sql_auteur = "SELECT id_utilisateur, prenom FROM utilisateur WHERE id_utilisateur = ?";
$stmt_auteur = $pdo->prepare($sql_auteur);
$stmt_auteur->execute([$recette['id_utilisateur']]);
$auteur = $stmt_auteur->fetch(PDO::FETCH_ASSOC);

// Ajoute un commentaire si l'utilisateur est connecté et si le formulaire est soumis
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SESSION['user_id'])) {
    $commentaire = trim($_POST['commentaire']);
    $note = (int) $_POST['note'];
    $id_utilisateur = $_SESSION['user_id'];

    // Vérifie que le commentaire et la note sont valides
    if (!empty($commentaire) && $note >= 1 && $note <= 5) {
        $sql_insert = "INSERT INTO commentaires (id_recettes, id_utilisateur, commentaire, note, date_commentaire) VALUES (?, ?, ?, ?, NOW())";
        $stmt_insert = $pdo->prepare($sql_insert);
        $stmt_insert->execute([$id_recettes, $id_utilisateur, $commentaire, $note]);

        // Vérifie si le commentaire a bien été inséré
        if ($stmt_insert->rowCount() > 0) {
            // Récupère les informations de l'auteur de la recette
            $stmt_auteur = $pdo->prepare("SELECT u.id_utilisateur, u.prenom FROM utilisateur u JOIN recettes r ON u.id_utilisateur = r.id_utilisateur WHERE r.id_recettes = ?");
            $stmt_auteur->execute([$id_recettes]);
            $auteur = $stmt_auteur->fetch(PDO::FETCH_ASSOC);

            // Envoie une notification à l'auteur de la recette si ce n'est pas l'utilisateur actuel
            if ($auteur && $auteur['id_utilisateur'] != $id_utilisateur) {
                $id_auteur = $auteur['id_utilisateur'];
                
                // Récupère le prénom de l'utilisateur qui a commenté
                $stmt_prenom = $pdo->prepare("SELECT prenom FROM utilisateur WHERE id_utilisateur = ?");
                $stmt_prenom->execute([$id_utilisateur]);
                $user_prenom = $stmt_prenom->fetch(PDO::FETCH_ASSOC)['prenom'] ?? 'Utilisateur inconnu';
                
                // Construit le message avec un lien vers la recette
                $message = "$user_prenom a commenté votre recette. <a href='detailrecette.php?id=$id_recettes'>Voir</a>";

                // Insère la notification dans la base de données
                $stmt_notif = $pdo->prepare("INSERT INTO notifications (id_utilisateur, message, id_recettes, date_envoi, lue) VALUES (?, ?, ?, NOW(), 0)");
                $stmt_notif->execute([$id_auteur, $message, $id_recettes]);
            }
        }

        // Redirige vers la même page pour éviter la soumission multiple du formulaire
        header("Location: detailrecette.php?id=$id_recettes");
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Détail de la Recette</title>
    <!-- Intègre Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Intègre Font Awesome pour les icônes -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <!-- Lien vers le fichier CSS personnalisé pour le header et le footer -->
    <link rel="stylesheet" href="headerfooter.css">
    <!-- Lien vers le fichier CSS personnalisé pour cette page -->
    <link rel="stylesheet" href="detailrecette.css">
</head>
<body>

<!-- Inclusion du header -->
<?php include 'header.php'; ?>

<main class="container mt-4">
    <div class="card shadow-lg p-4 bg-dark text-white recipe-card">
        <div class="card-header text-center">
            <h1><?php echo htmlspecialchars_decode($recette['titre'], ENT_QUOTES); ?></h1>
            <div class="mt-3">
                <p class="h4 text-warning">Prix : <?php echo number_format($recette['prix'], 2, ',', ' ') . ' €'; ?></p>
            </div>
        </div>

        <div class="row g-4 mt-3">
            <div class="col-md-6 text-center">
                <?php
                $filePath = htmlspecialchars($recette['image'], ENT_QUOTES, 'UTF-8');
                $fileType = mime_content_type($filePath);

                if (strpos($fileType, "image") !== false): 
                ?>
                    <img src="<?php echo $filePath; ?>" 
                         alt="<?php echo htmlspecialchars_decode($recette['titre'], ENT_QUOTES); ?>" 
                         class="img-fluid rounded recipe-image"/>
                <?php elseif (strpos($fileType, "video") !== false): ?>
                    <video class="img-fluid rounded recipe-image" controls autoplay muted>
                        <source src="<?php echo $filePath; ?>" type="<?php echo $fileType; ?>">
                        Votre navigateur ne supporte pas la lecture de cette vidéo.
                    </video>
                <?php else: ?>
                    <p class="text-muted">Aucune image ou vidéo disponible.</p>
                <?php endif; ?>
            </div>

            <div class="col-md-6">
                <h2 class="text-warning">Ingrédients</h2>
                <ul class="list-group list-group-flush recipe-ingredients-list">
                    <?php foreach (explode("\n", $recette['ingredients']) as $ingredient): ?>
                        <li class="list-group-item bg-dark text-white"> <?php echo htmlspecialchars_decode($ingredient, ENT_QUOTES); ?> </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>

        <div class="mt-4 recipe-description">
            <h2 class="text-warning">Description</h2>
            <p class="text-center"><?php echo nl2br(htmlspecialchars_decode($recette['description'], ENT_QUOTES)); ?></p>
        </div>

        <div class="mt-4 recipe-instructions">
            <h2 class="text-warning">Instructions</h2>
            <p><?php echo nl2br(htmlspecialchars_decode($recette['instructions'], ENT_QUOTES)); ?></p>
        </div>
        
        <?php if ($auteur): ?>
            <div class="mt-4 text-center">
                <p class="text-white">Recette écrite par 
                    <a href="detail_profil.php?id=<?= $auteur['id_utilisateur']; ?>" class="text-warning fw-bold">
                        <?= htmlspecialchars($auteur['prenom'], ENT_QUOTES, 'UTF-8'); ?>
                    </a>
                </p>
            </div>
        <?php endif; ?>

        <div class="mt-5 comment-container">
            <h2 class="text-warning">Commentaires</h2>
            <?php foreach ($comments as $comment): ?>
                <div class="border p-4 mb-3 rounded bg-dark comment-box shadow-lg d-flex align-items-start">
                    <!-- Image de profil -->
                    <div class="me-3">
                        <?php 
                        $imageProfil = (!empty($comment['image_profil']) && file_exists($comment['image_profil'])) 
                            ? htmlspecialchars($comment['image_profil'], ENT_QUOTES, 'UTF-8') 
                            : "image/default-profile.png"; 
                        ?>
                        <a href="detail_profil.php?id=<?= $comment['id_utilisateur']; ?>">
                            <img src="<?= $imageProfil; ?>" alt="Photo de profil" class="rounded-circle" width="50" height="50">
                        </a>
                    </div>

                    <!-- Détails du commentaire -->
                    <div>
                        <p class="fw-bold text-warning mb-1">
                            <a href="detail_profil.php?id=<?= $comment['id_utilisateur']; ?>" class="text-warning text-decoration-none">
                                <?= htmlspecialchars($comment['prenom'], ENT_QUOTES, 'UTF-8'); ?>
                            </a>
                        </p>
                        <p class="text-white"> 
                            <?= nl2br(htmlspecialchars_decode($comment['commentaire'], ENT_QUOTES)); ?>
                        </p>
                        <p class="text-warning">Note:
                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                <?= $i <= $comment['note'] ? '★' : '☆'; ?>
                            <?php endfor; ?>
                        </p>
                        <p class="text-muted small">Posté le <?= $comment['date_commentaire']; ?></p>

                        <!-- Afficher Modifier/Supprimer uniquement pour l'auteur du commentaire -->
                        <?php if (isset($_SESSION['user_id']) && $_SESSION['user_id'] == $comment['id_utilisateur']): ?>
                            <div class="d-flex gap-2">
                                <a href="edit_comment.php?id=<?= $comment['id_commentaire']; ?>" class="btn btn-sm btn-warning">Modifier</a>
                                <a href="delete_comment.php?id=<?= $comment['id_commentaire']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Êtes-vous sûr de vouloir supprimer ce commentaire ?');">Supprimer</a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <?php if (isset($_SESSION['user_id'])): ?>
            <form method="POST" class="mt-4 comment-form">
                <div class="mb-3">
                    <textarea name="commentaire" class="form-control" placeholder="Laissez un commentaire..." rows="3" required></textarea>
                </div>
                <div class="mb-3">
                    <label class="form-label">Notez cette recette :</label>
                    <select name="note" class="form-select" required>
                        <option value="1">1 ★</option>
                        <option value="2">2 ★★</option>
                        <option value="3">3 ★★★</option>
                        <option value="4">4 ★★★★</option>
                        <option value="5">5 ★★★★★</option>
                    </select>
                </div>
                <button type="submit" class="btn btn-warning w-100">Envoyer le commentaire</button>
            </form>
        <?php else: ?>
            <p class="text-center mt-3">Connectez-vous pour laisser un commentaire.</p>
        <?php endif; ?>

        <div class="text-center mt-4">
            <a href="listedesrecettes.php" class="btn btn-secondary">Retour à la liste des recettes</a>
        </div>
    </div>
</main>

<!-- Inclusion du footer -->
<?php include 'footer.php'; ?>

<!-- Intègre Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>