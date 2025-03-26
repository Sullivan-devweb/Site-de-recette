<?php
session_start();
require_once 'db_connect.php';

// Définir le type de réponse en JSON pour les communications avec JavaScript
header('Content-Type: application/json');

// Vérifier si la requête est une requête POST
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    try {
        // Récupérer les données JSON envoyées par la requête
        $input = file_get_contents('php://input');
        $data = json_decode($input, true);

        // Vérifier si le décodage JSON a réussi
        if ($data === null) {
            error_log("❌ Erreur JSON : Données invalides -> " . $input);
            throw new Exception("Erreur de décodage des données JSON.");
        }

        // Vérifier si l'utilisateur est connecté
        if (!isset($_SESSION['user_id'])) {
            error_log("❌ Erreur : Utilisateur non connecté.");
            throw new Exception("Vous devez être connecté pour commenter.");
        }

        // Vérifier si tous les champs nécessaires sont présents
        if (!isset($data['id_recette'], $data['commentaire'], $data['note'])) {
            throw new Exception("Tous les champs sont obligatoires.");
        }

        // Récupérer et valider les données du commentaire
        $id_recette = intval($data['id_recette']);
        $commentaire = trim($data['commentaire']);
        $note = intval($data['note']);
        $user_id = $_SESSION['user_id'];

        // Validation de l'ID de la recette
        if ($id_recette <= 0) {
            throw new Exception("L'ID de la recette est invalide.");
        }

        // Validation de la note (doit être entre 1 et 5)
        if ($note < 1 || $note > 5) {
            throw new Exception("La note doit être comprise entre 1 et 5.");
        }

        // Validation du commentaire (ne peut pas être vide)
        if (empty($commentaire)) {
            throw new Exception("Le commentaire ne peut pas être vide.");
        }

        // Récupération du prénom de l'utilisateur à partir de la base de données
        $stmt_user = $pdo->prepare("SELECT prenom FROM utilisateur WHERE id_utilisateur = ?");
        $stmt_user->execute([$user_id]);
        $user_data = $stmt_user->fetch(PDO::FETCH_ASSOC);

        // Vérification si l'utilisateur a été trouvé
        if (!$user_data) {
            throw new Exception("Utilisateur introuvable.");
        }

        // Sécurisation du prénom récupéré
        $prenom = htmlspecialchars($user_data['prenom'], ENT_QUOTES, 'UTF-8');

        // Préparation et exécution de la requête SQL pour insérer le commentaire
        $stmt = $pdo->prepare("INSERT INTO commentaires (id_recettes, id_utilisateur, commentaire, note, date_commentaire, vu)
                               VALUES (?, ?, ?, ?, NOW(), 0)");
        $stmt->execute([$id_recette, $user_id, $commentaire, $note]);

        // Vérification si l'insertion a réussi
        if ($stmt->rowCount() === 0) {
            throw new Exception("Impossible d'ajouter le commentaire.");
        }

        // Récupération de l'auteur de la recette pour la notification
        $stmt_recette_auteur = $pdo->prepare("SELECT id_utilisateur FROM recettes WHERE id_recettes = ?");
        $stmt_recette_auteur->execute([$id_recette]);
        $recette_auteur = $stmt_recette_auteur->fetch(PDO::FETCH_ASSOC);

        // Vérification si l'auteur de la recette est différent de l'utilisateur commentant
        if ($recette_auteur && $recette_auteur['id_utilisateur'] != $user_id) {
            $id_auteur = $recette_auteur['id_utilisateur'];
            $message = "$prenom a commenté votre recette : \"$commentaire\"";

            // Vérification de l'existence d'une notification similaire récente pour éviter les doublons
            $stmt_check_notif = $pdo->prepare("SELECT id_notifications FROM notifications 
                                               WHERE id_utilisateur = ? AND id_recettes = ? 
                                               AND date_envoi >= NOW() - INTERVAL 5 MINUTE");
            $stmt_check_notif->execute([$id_auteur, $id_recette]);

            // Si aucune notification similaire n'existe, insérer la notification
            if ($stmt_check_notif->rowCount() == 0) {
                $stmt_notif = $pdo->prepare("INSERT INTO notifications (id_utilisateur, message, date_envoi, lue) VALUES (?, ?, NOW(), 0)");
                $stmt_notif->execute([$id_auteur, $message]);

                // Log de succès ou d'erreur de l'ajout de la notification
                if ($stmt_notif->rowCount() > 0) {
                    error_log("✅ Notification ajoutée pour l'utilisateur ID : $id_auteur");
                } else {
                    error_log("❌ Erreur lors de l'ajout de la notification.");
                }
            } else {
                error_log("ℹ️ Notification déjà existante, pas besoin d'ajouter.");
            }
        } else {
            error_log("ℹ️ L'auteur de la recette a commenté lui-même, pas de notification.");
        }

        // Préparation de la réponse JSON pour le client
        echo json_encode([
            'success' => true,
            'prenom' => $prenom,
            'commentaire' => htmlspecialchars($commentaire, ENT_QUOTES, 'UTF-8'),
            'note' => $note,
            'date_commentaire' => date('Y-m-d H:i:s')
        ]);

    } catch (Exception $e) {
        // Gestion des erreurs et envoi d'une réponse JSON d'erreur
        error_log("❌ Erreur lors du traitement : " . $e->getMessage());
        echo json_encode(['error' => $e->getMessage()]);
    }
} else {
    // Gestion des requêtes autres que POST
    error_log("❌ Erreur : Méthode non autorisée -> " . $_SERVER["REQUEST_METHOD"]);
    echo json_encode(['error' => 'Méthode invalide.']);
}
?>