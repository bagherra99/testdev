## Recherche de commerces locaux

Application complète permettant de rechercher des commerces via l’API Foursquare, de stocker les résultats en base SQLite et de les afficher dans une interface React paginée.

### Prérequis
- PHP 8.1+
- SQLite (inclus avec PHP)
- Node.js 18+ / npm
- Clé API Foursquare Places (https://location.foursquare.com/places)

### Backend PHP
1. Exporter la clé API : `export FOURSQUARE_API_KEY="votre_cle"`.
2. Depuis la racine : `php -S localhost:8000 -t backend/public`.
3. L’API est accessible via `http://localhost:8000/api/search.php?keyword=restaurants&location=Paris`.
4. Chaque appel :
   - interroge Foursquare (50 résultats max),
   - filtre/normalise les données,
   - enregistre/actualise les commerces dans `backend/storage/businesses.sqlite`,
   - renvoie une réponse JSON paginée (10 éléments/page).

### Frontend React (Vite)
1. `cd frontend`
2. `npm install`
3. Créer un fichier `.env` si besoin : `VITE_API_BASE_URL=http://localhost:8000/api/search.php`.
4. `npm run dev` puis ouvrir `http://localhost:5173`.

L’interface propose :
- un formulaire (mot-clé + lieu),
- un affichage des résultats (nom, adresse, téléphone, site, photo),
- une pagination simple (Précédent/Suivant, 10 éléments par page),
- la gestion des erreurs de l’API.

### Tests rapides
- Lancer d’abord le serveur PHP, puis le front Vite.
- Rechercher « boulangeries » sur « Lyon » pour vérifier la chaîne complète.

### Personnalisation
- Modifier `backend/bootstrap.php` pour ajuster `per_page`, `max_results` ou le timeout HTTP.
- Adapter les styles dans `frontend/src/styles.css`.

