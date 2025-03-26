document.addEventListener("DOMContentLoaded", () => {
    // Sélectionne l'élément input de type fichier pour l'image de profil
    const imageInput = document.getElementById("image_profil");
    // Sélectionne l'élément img où l'aperçu de l'image sera affiché
    const imagePreview = document.getElementById("preview");

    // Ajoute un écouteur d'événement 'change' à l'input de fichier
    imageInput.addEventListener("change", (e) => {
        // Récupère le premier fichier sélectionné
        const file = e.target.files[0];
        // Vérifie si un fichier a été sélectionné
        if (file) {
            // Crée un nouvel objet FileReader pour lire le contenu du fichier
            const reader = new FileReader();
            // Définit une fonction à exécuter lorsque la lecture du fichier est terminée
            reader.onload = (e) => {
                // Met à jour la source de l'image de prévisualisation avec les données lues
                imagePreview.src = e.target.result;
                // Affiche l'image de prévisualisation
                imagePreview.style.display = "block";
            };
            // Démarre la lecture du fichier en tant qu'URL de données
            reader.readAsDataURL(file);
        }
    });

    // Sélectionne l'élément input pour la ville
    const villeInput = document.getElementById("ville");
    // Sélectionne l'élément div où les suggestions de ville seront affichées
    const villeSuggestions = document.getElementById("ville-suggestions");

    // Ajoute un écouteur d'événement 'input' à l'input de la ville
    villeInput.addEventListener("input", async (e) => {
        // Récupère la valeur entrée par l'utilisateur
        const query = e.target.value;
        // Vérifie si la valeur entrée a au moins 3 caractères
        if (query.length >= 3) {
            // Effectue une requête fetch à l'API Gouv pour obtenir les suggestions de villes
            const response = await fetch(`https://geo.api.gouv.fr/communes?nom=${query}&fields=nom&boost=population&limit=5`);
            // Convertit la réponse en JSON
            const data = await response.json();
            // Met à jour le contenu de l'élément des suggestions avec les noms des villes
            villeSuggestions.innerHTML = data.map(ville => `<div>${ville.nom}</div>`).join("");
            // Affiche l'élément des suggestions
            villeSuggestions.style.display = "block";
        } else {
            // Cache l'élément des suggestions si la valeur entrée a moins de 3 caractères
            villeSuggestions.style.display = "none";
        }
    });

    // Ajoute un écouteur d'événement 'click' à l'élément des suggestions de ville
    villeSuggestions.addEventListener("click", (e) => {
        // Vérifie si l'élément cliqué est un élément div (une suggestion de ville)
        if (e.target.tagName === "DIV") {
            // Met à jour la valeur de l'input de la ville avec le texte de la suggestion cliquée
            villeInput.value = e.target.textContent;
            // Cache l'élément des suggestions
            villeSuggestions.style.display = "none";
        }
    });

    // Sélectionne le formulaire
    document.querySelector('form').addEventListener('submit', function (event) {
        // Sélectionne les éléments input pour le mot de passe et la confirmation du mot de passe
        const password = document.getElementById('password').value;
        const confirmPassword = document.getElementById('confirm_password').value;

        // Vérifie si les mots de passe ne correspondent pas
        if (password !== confirmPassword) {
            // Empêche l'envoi du formulaire
            event.preventDefault();
            // Affiche une alerte informant l'utilisateur de la non-correspondance des mots de passe
            alert("Les mots de passe ne correspondent pas.");
        }
    });
});