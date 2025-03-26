# 🍽️ Site de Recette

## ✨ À propos
Le **Site de Recette** est une plateforme innovante conçue pour aider les étudiants à trouver et partager des recettes simples et abordables. Grâce à une interface intuitive et des fonctionnalités pratiques, cuisiner devient un jeu d'enfant !

🔗 Accédez à la plateforme : [Site de Recette](https://sitederecette.404cahorsfound.fr/)

## 🚀 Fonctionnalités principales
- 📝 **Gestion des recettes** : Création, personnalisation et mise à jour des recettes par les utilisateurs.
- 🔍 **Recherche avancée** : Filtres pour affiner les recherches par ingrédients, temps de préparation, etc.
- 💬 **Commentaires et évaluations** : Laisser des avis et des commentaires sur les recettes.
- 🔔 **Système de notifications** : Alertes pour les nouveaux commentaires, recettes ajoutées et mises à jour.
- 🌍 **Intégration API Geo API Gouv** : Utilisation de l'API Geo API du gouvernement français pour améliorer la précision de la localisation et les fonctionnalités de recherche géographique.

## 🛠️ Installation
Installez le Site de Recette en local en suivant ces étapes :

1. Clonez le dépôt :
   ```bash
   git clone https://github.com/Sullivan-devweb/site-de-recette.git
   cd site-de-recette
   ```

2. Installez les dépendances :
   ```bash
   npm install
   ```

3. Configurez l’environnement :
   ```bash
   cp .env.example .env
   # Puis modifiez le fichier .env avec vos paramètres, y compris votre clé API Geo API Gouv
   ```

4. Lancez l’application :
   ```bash
   npm start
   ```

📍 Une fois lancée, l’application est accessible sur [http://localhost:3000](http://localhost:3000).

## 🌍 Configuration de l'API Geo API Gouv
Pour utiliser l'API Geo API Gouv, vous devez obtenir une clé API et la configurer dans votre fichier `.env`.

1. Inscrivez-vous sur [API Geo API Gouv](https://geo.api.gouv.fr/) et obtenez une clé API.
2. Ajoutez votre clé API au fichier `.env` :
   ```plaintext
   GEO_API_GOUV_KEY=your_geo_api_gouv_key
   ```

## 🤝 Contribution
Nous accueillons avec plaisir toutes les contributions !

1. Forkez le projet.
2. Créez une branche dédiée :
   ```bash
   git checkout -b feature/nom-de-la-fonctionnalité
   ```
3. Ajoutez vos modifications et committez-les :
   ```bash
   git commit -m "Ajout : nouvelle fonctionnalité"
   ```
4. Poussez votre branche :
   ```bash
   git push origin feature/nom-de-la-fonctionnalité
   ```
5. Ouvrez une Pull Request et soumettez vos changements.

## 📜 Licence
Ce projet est sous licence MIT. Consultez le fichier [LICENSE](./LICENSE) pour plus d’informations.

## 👥 Équipe
- **Sullivan-devweb** - Développeur & Contributeur - [GitHub](https://github.com/Sullivan-devweb)
- **Faykkas** - Développeur & Contributeur - [GitHub](https://github.com/Faykkas)

## 🙏 Remerciements
Un immense merci à :
- La communauté open-source pour son soutien.
- Tous nos contributeurs passionnés.
- Nos précieux testeurs et utilisateurs pour leurs retours enrichissants.

💖 Site de Recette, là où chaque repas devient une aventure culinaire !
