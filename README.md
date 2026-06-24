Vite Gourmand – Application Web

Projet réalisé dans le cadre d'un dossier ECF

Vite Gourmand est une application web développée dans le cadre du TP ECF Développeur Web & Web Mobile.

L’objectif est de permettre :

- Aux clients de consulter les menus et passer commande
- Aux employés de gérer les commandes
- Aux administrateurs de piloter le site et consulter les statistiques

Le projet repose sur une architecture MVC personnalisée en PHP 8.

CHOIX TECHNIQUES

Back-end

- PHP 8
- Architecture MVC personnalisée
- PDO (requêtes préparées)
- PHPMailer
- Sessions sécurisées
- MySQL (BDD principale)
- MongoDB (statistiques)

Front-end

- HTML5 sémantique
- CSS3
- Bootstrap 5
- JavaScript (Vanilla)
- Approche mobile-first

BASES DE DONNEES
1- MySQL (données métier) : utilisateurs, menus, plats, commandes, avis, horaires
2 - MongoDB (statistiques) : agrégation du chiffre d’affaires, nombre de commandes par menu, filtres temporels

Comment installer le projet ? 

1 - cloner le projet : git clone <url-du-repo>

2 - configuration de l'environnement : copier le fichier .env.exemple en .env et copier les informations suivantes : 
   - Accès base MySQL
   - Accès MongoDB
   - Paramètres SMTP
   - Configuration URL
ATTENTION : Le fichier .env ne doit jamais être versionné.

3 - Import de la base de données
   - Créer une base MySQL
   - Importer schema.sql puis seed.sql

4 - BDD MongoDB (optionnel pour le site, obligatoire pour admin statistique) 
      - Information : les évènements sont enregsitrés lors du passage d'une commande aux statuts acceptée et terminée ce qui génère un document dans la collection oerder_event
      - Il y a 2 méthodes d'installation 
            - Installation locale : configurer dans .env 

MONGO_URI=mongodb://127.0.0.1:27017
MONGO_DB=vite_gourmand
MONGO_COLLECTION=order_events


            - Docker 
docker run -d --name mongo-vg -p 27017:27017 mongo:7

5 - Lancement : configurer Apache pour pointer vers le dossier public/ ou www/.

Accès de démonstration : Les identifiants de démonstration sont fournis séparément dans le document rendu sur la plateforme d’examen.

Sécurité :

- Mots de passe hashés (bcrypt)
- Requêtes préparées PDO
- Protection contre injections SQL
- Gestion des rôles (client / employé / admin)
- Variables sensibles isolées dans .env

Déploiement :
Le projet est déployé sur un hébergement mutualisé compatible PHP 8.

Auteur : 

Projet réalisé dans le cadre de l’ECF Développeur Web & Web Mobile par Julie B. 

## Documents
Les documents demandés (MCD, diagrammes UML, use case, séquence, maquettes, manuels) sont disponibles dans le dossier `documents/`.
