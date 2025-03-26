document.addEventListener("DOMContentLoaded", () => {
    // Sélectionne l'élément du carousel par son ID
    const carouselElement = document.getElementById("popularRecipesCarousel");

    // Initialise le carousel Bootstrap avec des options spécifiques
    const carousel = new bootstrap.Carousel(carouselElement, {
        interval: 5000, // Définit l'intervalle de temps entre chaque slide à 5000 millisecondes (5 secondes)
        ride: 'carousel' // Active le comportement de carousel
    });
});