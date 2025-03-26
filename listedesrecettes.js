document.addEventListener('DOMContentLoaded', function () {
    // Sélectionne l'élément input de recherche par son ID
    let searchInput = document.getElementById('search-input');
    // Sélectionne l'élément select pour le filtre de catégorie par son ID
    let categorieFilter = document.getElementById('categorie-filter');
    // Sélectionne l'élément input pour le filtre de prix par son ID
    let prixFilter = document.getElementById('prix-filter');
    // Sélectionne le bouton de recherche par son ID
    let searchButton = document.getElementById('search-button');
    // Sélectionne le conteneur des recettes par son sélecteur CSS
    let recipeContainer = document.querySelector('#recipe-container');

    // Fonction pour mettre à jour l'affichage des recettes en fonction des filtres sélectionnés
    function updateRecipes() {
        // Récupère la valeur de l'input de recherche et supprime les espaces inutiles
        const searchQuery = searchInput.value.trim();
        // Récupère la valeur sélectionnée dans le filtre de catégorie
        const categorieQuery = categorieFilter.value;
        // Récupère la valeur entrée dans le filtre de prix
        const prixQuery = prixFilter.value;

        // Construit l'URL pour la requête fetch en encodant les paramètres pour éviter les problèmes de caractères spéciaux
        let url = `search_recipes.php?search=${encodeURIComponent(searchQuery)}&categorie=${encodeURIComponent(categorieQuery)}&prix=${encodeURIComponent(prixQuery)}`;

        // Effectue une requête fetch à l'URL construite
        fetch(url)
            .then(response => response.text()) // Convertit la réponse en texte
            .then(html => {
                recipeContainer.innerHTML = html; // Met à jour le contenu du conteneur des recettes avec le HTML reçu
            })
            .catch(error => console.error('Erreur lors de la recherche dynamique :', error)); // Gère les erreurs de la requête fetch
    }

    // Ajoute des écouteurs d'événements pour déclencher la mise à jour des recettes
    // Le bouton de recherche déclenche la mise à jour lors du clic
    searchButton.addEventListener('click', updateRecipes);
    // L'input de recherche déclenche la mise à jour à chaque changement de valeur
    searchInput.addEventListener('input', updateRecipes);
    // Le filtre de catégorie déclenche la mise à jour lors du changement de sélection
    categorieFilter.addEventListener('change', updateRecipes);
    // Le filtre de prix déclenche la mise à jour à chaque changement de valeur
    prixFilter.addEventListener('input', updateRecipes);
});