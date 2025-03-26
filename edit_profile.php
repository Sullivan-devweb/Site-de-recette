<?php
// Active l'affichage des erreurs pour le débogage
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

// Démarre la session PHP pour permettre l'utilisation des variables de session
session_start();

// Inclut le fichier de connexion à la base de données
require_once 'db_connect.php';

// Vérifie si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    header("Location: connexion_html.php"); // Redirige vers la page de connexion
    exit(); // Arrête l'exécution du script
}

// Récupère l'ID de l'utilisateur connecté
$idUtilisateur = $_SESSION['user_id'];

// Récupère les informations de l'utilisateur, y compris l'image de profil
$query = $pdo->prepare("SELECT nom, prenom, email, ville, ecole, etudes, youtube, instagram, twitter, image_profil FROM utilisateur WHERE id_utilisateur = :id");
$query->bindValue(':id', $idUtilisateur, PDO::PARAM_INT);
$query->execute();
$utilisateur = $query->fetch(PDO::FETCH_ASSOC);

// Vérifie si l'utilisateur existe
if (!$utilisateur) {
    die("Erreur : utilisateur non trouvé."); // Affiche un message d'erreur et arrête le script
}

// Traitement du formulaire de mise à jour
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Récupère les données du formulaire
    $nom = $_POST['nom'] ?? '';
    $prenom = $_POST['prenom'] ?? '';
    $email = $_POST['email'] ?? '';
    $ville = $_POST['ville'] ?? '';
    $ecole = $_POST['ecole'] ?? '';
    $etudes = $_POST['etudes'] ?? '';
    $youtube = !empty($_POST['youtube']) ? $_POST['youtube'] : null;
    $instagram = !empty($_POST['instagram']) ? $_POST['instagram'] : null;
    $twitter = !empty($_POST['twitter']) ? $_POST['twitter'] : null;
    $imageProfil = $utilisateur['image_profil']; // Par défaut, garde l'ancienne image

    // Vérification et upload de la nouvelle image
    if (!empty($_FILES['image_profil']['name'])) {
        $uploadDir = 'uploads/'; // Dossier où stocker les images
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true); // Crée le dossier s'il n'existe pas
        }

        // Génère un nom de fichier unique pour éviter les conflits
        $fileName = uniqid() . '_' . basename($_FILES['image_profil']['name']);
        $uploadFile = $uploadDir . $fileName;
        $imageFileType = strtolower(pathinfo($uploadFile, PATHINFO_EXTENSION));

        // Vérifie le format et la taille de l'image
        if (in_array($imageFileType, ['jpg', 'jpeg', 'png', 'gif']) && $_FILES['image_profil']['size'] < 500000) {
            if (move_uploaded_file($_FILES['image_profil']['tmp_name'], $uploadFile)) {
                $imageProfil = $uploadFile; // Met à jour l'image si tout est OK

                // Supprime l'ancienne image si elle existe et n'est pas l'image par défaut
                if (!empty($utilisateur['image_profil']) && $utilisateur['image_profil'] !== 'default-avatar.png') {
                    unlink($utilisateur['image_profil']);
                }
            } else {
                echo "<p class='alert alert-danger'>Erreur lors de l'upload de l'image.</p>";
            }
        } else {
            echo "<p class='alert alert-danger'>Format ou taille de l'image non valide.</p>";
        }
    }

    // Mise à jour des informations de l'utilisateur dans la base de données
    $query = $pdo->prepare("UPDATE utilisateur SET nom = :nom, prenom = :prenom, email = :email, ville = :ville, ecole = :ecole, etudes = :etudes, youtube = :youtube, instagram = :instagram, twitter = :twitter, image_profil = :image_profil WHERE id_utilisateur = :id");

    $query->execute([
        ':nom' => $nom,
        ':prenom' => $prenom,
        ':email' => $email,
        ':ville' => $ville,
        ':ecole' => $ecole,
        ':etudes' => $etudes,
        ':youtube' => $youtube,
        ':instagram' => $instagram,
        ':twitter' => $twitter,
        ':image_profil' => $imageProfil,
        ':id' => $idUtilisateur
    ]);

    // Rafraîchit les données de l'utilisateur après la mise à jour
    $query = $pdo->prepare("SELECT image_profil FROM utilisateur WHERE id_utilisateur = :id");
    $query->bindValue(':id', $idUtilisateur, PDO::PARAM_INT);
    $query->execute();
    $utilisateur = $query->fetch(PDO::FETCH_ASSOC);

    echo "<p class='alert alert-success'>Profil mis à jour avec succès.</p>";
    header("Refresh:2; url=edit_profile.php"); // Redirige après 2 secondes
    exit();
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modifier le Profil</title>
    <!-- Intègre Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Intègre Font Awesome pour les icônes -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <!-- Lien vers les fichiers CSS personnalisés -->
    <link rel="stylesheet" href="index.css">
    <link rel="stylesheet" href="headerfooter.css">
</head>
<body>
    <!-- Inclusion du header -->
    <?php include 'header.php'; ?>

    <section class="edit-profile-section">
        <div class="container">
            <h1>Modifier le Profil</h1>

            <!-- Formulaire de mise à jour du profil -->
            <form method="POST" action="edit_profile.php" enctype="multipart/form-data">
                <!-- Affichage de la photo de profil -->
                <div class="mb-3 text-center">
                    <img id="previewImage" src="<?php echo !empty($utilisateur['image_profil']) ? htmlspecialchars($utilisateur['image_profil']) : 'default-avatar.png'; ?>" alt="Photo de profil" class="rounded-circle" width="150" height="150" style="object-fit: cover;">
                    <label for="image_profil" class="btn btn-secondary mt-2">Changer l'image</label>
                    <input type="file" class="form-control d-none" id="image_profil" name="image_profil" accept="image/*">
                </div>

                <!-- Champ pour le nom -->
                <div class="mb-3">
                    <label for="nom" class="form-label">Nom</label>
                    <input type="text" class="form-control" id="nom" name="nom" value="<?php echo isset($utilisateur['nom']) ? htmlspecialchars($utilisateur['nom']) : ''; ?>" required>
                </div>

                <!-- Champ pour le prénom -->
                <div class="mb-3">
                    <label for="prenom" class="form-label">Prénom</label>
                    <input type="text" class="form-control" id="prenom" name="prenom" value="<?php echo isset($utilisateur['prenom']) ? htmlspecialchars($utilisateur['prenom']) : ''; ?>" required>
                </div>

                <!-- Champ pour l'email -->
                <div class="mb-3">
                    <label for="email" class="form-label">Email</label>
                    <input type="email" class="form-control" id="email" name="email" value="<?php echo isset($utilisateur['email']) ? htmlspecialchars($utilisateur['email']) : ''; ?>" required>
                </div>

                <!-- Champ pour la ville avec autocomplétion -->
                <div class="mb-3">
                    <label for="ville" class="form-label">Ville</label>
                    <input type="text" class="form-control" id="ville" name="ville" value="<?php echo isset($utilisateur['ville']) ? htmlspecialchars($utilisateur['ville']) : ''; ?>" required>
                    <div id="ville-suggestions" class="suggestions"></div>
                </div>

                <!-- Champ pour l'école -->
                <div class="mb-3">
                    <label for="ecole" class="form-label">École</label>
                    <input type="text" class="form-control" id="ecole" name="ecole" value="<?php echo isset($utilisateur['ecole']) ? htmlspecialchars($utilisateur['ecole']) : ''; ?>" required>
                </div>

                <!-- Champ pour les études -->
                <div class="mb-3">
                    <label for="etudes" class="form-label">Études</label>
                    <input type="text" class="form-control" id="etudes" name="etudes" value="<?php echo isset($utilisateur['etudes']) ? htmlspecialchars($utilisateur['etudes']) : ''; ?>" required>
                </div>

                <!-- Champ pour le lien YouTube -->
                <div class="mb-3">
                    <label for="youtube" class="form-label">Lien YouTube</label>
                    <input type="url" class="form-control" id="youtube" name="youtube" value="<?php echo isset($utilisateur['youtube']) ? htmlspecialchars($utilisateur['youtube']) : ''; ?>">
                </div>

                <!-- Champ pour le lien Instagram -->
                <div class="mb-3">
                    <label for="instagram" class="form-label">Lien Instagram</label>
                    <input type="url" class="form-control" id="instagram" name="instagram" value="<?php echo isset($utilisateur['instagram']) ? htmlspecialchars($utilisateur['instagram']) : ''; ?>">
                </div>

                <!-- Champ pour le lien Twitter -->
                <div class="mb-3">
                    <label for="twitter" class="form-label">Lien Twitter</label>
                    <input type="url" class="form-control" id="twitter" name="twitter" value="<?php echo isset($utilisateur['twitter']) ? htmlspecialchars($utilisateur['twitter']) : ''; ?>">
                </div>

                <!-- Bouton de soumission du formulaire -->
                <button type="submit" class="btn btn-primary">Mettre à jour</button>
            </form>
        </div>
    </section>

    <!-- Inclusion du footer -->
    <?php include 'footer.php'; ?>

    <!-- Script pour la prévisualisation de l'image -->
    <script>
        const imageInput = document.getElementById('image_profil');
        const previewImage = document.getElementById('previewImage');

        imageInput.addEventListener('change', function(event) {
            const file = event.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    previewImage.src = e.target.result;
                };
                reader.readAsDataURL(file);
            }
        });
    </script>

    <!-- Script pour l'autocomplétion des villes avec l'API Gouv -->
    <script>
        const villeInput = document.getElementById("ville");
        const villeSuggestions = document.getElementById("ville-suggestions");

        villeInput.addEventListener("input", async (e) => {
            const query = e.target.value;
            if (query.length >= 3) {
                const response = await fetch(`https://geo.api.gouv.fr/communes?nom=${query}&fields=nom&boost=population&limit=5`);
                const data = await response.json();
                villeSuggestions.innerHTML = data.map(ville => `<div>${ville.nom}</div>`).join("");
                villeSuggestions.style.display = "block";
            } else {
                villeSuggestions.style.display = "none";
            }
        });

        villeSuggestions.addEventListener("click", (e) => {
            if (e.target.tagName === "DIV") {
                villeInput.value = e.target.textContent;
                villeSuggestions.style.display = "none";
            }
        });
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>