// Initialisation de la liste des ingrédients
let ingredients = [];

// Vérifier si on est sur la page editRecipe.php pour charger les ingrédients existants
document.addEventListener("DOMContentLoaded", function () {
    const hiddenInput = document.getElementById("ingredients");
    if (hiddenInput && hiddenInput.value.trim() !== "") {
        ingredients = hiddenInput.value.split("\n").filter(Boolean);
        updateIngredientsList();
    }
});

/* 🔹 Fonction pour afficher le modal Bootstrap */
function openModal() {
    const modal = new bootstrap.Modal(document.getElementById("recipeModal"));
    modal.show();
}

/* 🔹 Fonction pour fermer le modal Bootstrap */
function closeModal() {
    const modal = bootstrap.Modal.getInstance(document.getElementById("recipeModal"));
    if (modal) modal.hide();
}

/* 🔹 Fonction pour ajouter un ingrédient */
function addIngredient() {
    const input = document.getElementById("ingredient-input");
    const ingredientValue = input?.value.trim();

    if (!ingredientValue) {
        alert("Veuillez entrer un ingrédient valide.");
        return;
    }

    if (ingredients.includes(ingredientValue)) {
        alert("Cet ingrédient est déjà ajouté.");
        return;
    }

    // Ajoute l'ingrédient à la liste et met à jour
    ingredients.push(ingredientValue);
    updateIngredientsList();
    input.value = ""; // Réinitialise l'input
}

/* 🔹 Fonction pour mettre à jour la liste des ingrédients */
function updateIngredientsList() {
    const ingredientsList = document.getElementById("ingredients-list");
    const hiddenInput = document.getElementById("ingredients");

    if (!ingredientsList || !hiddenInput) {
        console.error("Erreur : éléments introuvables pour gérer la liste des ingrédients.");
        return;
    }

    // Réinitialise la liste affichée
    ingredientsList.innerHTML = "";

    ingredients.forEach((ingredient, index) => {
        const li = document.createElement("li");
        li.className = "list-group-item d-flex justify-content-between align-items-center bg-dark text-light";
        li.textContent = ingredient;

        // Bouton de suppression
        const deleteButton = document.createElement("button");
        deleteButton.innerHTML = '<i class="fas fa-trash"></i>';
        deleteButton.className = "btn btn-sm btn-danger";
        deleteButton.onclick = () => removeIngredient(index);

        li.appendChild(deleteButton);
        ingredientsList.appendChild(li);
    });

    // Met à jour le champ caché pour le formulaire
    hiddenInput.value = ingredients.join("\n");
}

/* 🔹 Fonction pour supprimer un ingrédient de la liste */
function removeIngredient(index) {
    ingredients.splice(index, 1);
    updateIngredientsList();
}

/* 🔹 Fonction pour ouvrir la page d'édition */
function editRecipe(recipeId) {
    if (recipeId) {
        window.location.href = `editRecipe.php?id=${recipeId}`;
    } else {
        console.error("Erreur : ID de la recette invalide.");
    }
}

function deleteRecipe(recipeId) {
    if (!recipeId || isNaN(recipeId)) {
        console.error("Erreur : ID de la recette invalide.", recipeId);
        return;
    }

    if (confirm("Êtes-vous sûr de vouloir supprimer cette recette ?")) {
        console.log("Envoi de la requête DELETE pour l'ID:", recipeId); // Debug

        fetch("deleteRecipe.php", {
            method: "POST",
            headers: {
                "Content-Type": "application/json"
            },
            body: JSON.stringify({ id: recipeId })
        })
        .then(response => {
            console.log("Réponse brute du serveur:", response); // Debug
            return response.text(); // <-- Change `.json()` en `.text()`
        })
        .then(data => {
            console.log("Réponse reçue:", data); // Debug
            try {
                let jsonData = JSON.parse(data); // Vérifie si c'est bien du JSON
                if (jsonData.success) {
                    alert("Recette supprimée avec succès.");
                    location.reload();
                } else {
                    alert("Erreur : " + jsonData.message);
                }
            } catch (error) {
                console.error("Réponse non JSON :", data); // Debug
                alert("Erreur serveur : " + data);
            }
        })
        .catch(error => {
            console.error("Erreur lors de la suppression :", error);
            alert("Une erreur est survenue lors de la suppression.");
        });
    }
}

document.addEventListener("DOMContentLoaded", function () {
    const form = document.getElementById("recipeForm");
    const modalBody = document.querySelector(".modal-body");

    form.addEventListener("submit", function (event) {
        let isValid = true;
        let errorMessages = [];

        // Supprime l'ancien message d'erreur en haut du modal
        const existingErrorBox = document.getElementById("error-box");
        if (existingErrorBox) {
            existingErrorBox.remove();
        }

        // Vérifier le titre
        const titre = document.getElementById("titre");
        if (titre.value.trim() === "") {
            errorMessages.push("⚠️ Le titre est obligatoire.");
            isValid = false;
        }

        // Vérifier la description
        const description = document.getElementById("description");
        if (description.value.trim() === "") {
            errorMessages.push("⚠️ La description est obligatoire.");
            isValid = false;
        }

        // Vérifier la catégorie
        const categorie = document.getElementById("categorie");
        if (categorie.value === "") {
            errorMessages.push("⚠️ Veuillez sélectionner une catégorie.");
            isValid = false;
        }

        // Vérifier les ingrédients
        const ingredients = document.getElementById("ingredients");
        if (ingredients.value.trim() === "") {
            errorMessages.push("⚠️ Ajoutez au moins un ingrédient.");
            isValid = false;
        }

        // Vérifier les instructions
        const instructions = document.getElementById("instructions");
        if (instructions.value.trim() === "") {
            errorMessages.push("⚠️ Les instructions sont obligatoires.");
            isValid = false;
        }

        // Vérifier l'image (si c'est un ajout et non une modification)
        const imageInput = document.getElementById("image");
        if (!imageInput.files.length && !document.getElementById("recipeId").value) {
            errorMessages.push("⚠️ Veuillez ajouter une image.");
            isValid = false;
        }

        // Si des erreurs existent, les afficher en haut du modal
        if (!isValid) {
            event.preventDefault();
            displayErrorMessages(errorMessages);
        }
    });

    function displayErrorMessages(messages) {
        const errorBox = document.createElement("div");
        errorBox.id = "error-box";
        errorBox.className = "alert alert-danger";
        errorBox.innerHTML = "<strong>Erreurs à corriger :</strong><ul>" +
            messages.map(msg => `<li>${msg}</li>`).join("") +
            "</ul>";

        // Ajouter l'erreur au début du modal
        modalBody.insertBefore(errorBox, modalBody.firstChild);
    }
});

document.addEventListener("DOMContentLoaded", function () {
    const mediaInput = document.getElementById("media");
    const mediaPreview = document.getElementById("media-preview");

    mediaInput.addEventListener("change", function () {
        const file = this.files[0];

        if (!file) {
            mediaPreview.innerHTML = "";
            return;
        }

        const fileURL = URL.createObjectURL(file);
        const fileType = file.type.split("/")[0]; // Vérifie si c'est une image ou une vidéo

        mediaPreview.innerHTML = ""; // Réinitialiser l'aperçu

        if (fileType === "image") {
            const img = document.createElement("img");
            img.src = fileURL;
            img.alt = "Aperçu de l'image";
            img.className = "img-fluid rounded mt-2";
            img.style.maxWidth = "100%";
            mediaPreview.appendChild(img);
        } else if (fileType === "video") {
            const video = document.createElement("video");
            video.src = fileURL;
            video.controls = true;
            video.className = "img-fluid rounded mt-2";
            video.style.maxWidth = "100%";
            mediaPreview.appendChild(video);
        } else {
            mediaPreview.innerHTML = "<p class='text-warning'>Format non supporté.</p>";
        }
    });
});





