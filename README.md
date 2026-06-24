# Vite Gourmand – Application web traiteur

Projet réalisé dans le cadre du titre professionnel **Développeur Web et Web Mobile**.

## Présentation
Vite Gourmand est une application web de gestion de commandes pour un service traiteur.

Elle permet :
- aux **clients** de consulter les menus et passer commande ;
- aux **employés** de suivre les commandes et les avis ;
- aux **administrateurs** de gérer les contenus, les menus, les plats, les employés et les statistiques.

Le projet repose sur une **architecture MVC personnalisée en PHP 8**.

## Choix techniques
### Back-end
- PHP 8
- Architecture MVC personnalisée
- PDO avec requêtes préparées
- Sessions sécurisées
- PHPMailer
- MySQL / MariaDB pour les données métier
- MongoDB pour les statistiques

### Front-end
- HTML5 sémantique
- CSS3
- Bootstrap 5
- JavaScript vanilla
- Approche mobile-first

## Bases de données
### MySQL / MariaDB
Base principale contenant :
- utilisateurs
- menus
- plats
- commandes
- avis
- horaires
- contenus CMS

### MongoDB
Base utilisée pour les **statistiques d'administration** :
- chiffre d'affaires
- nombre de commandes
- agrégation par menu
- filtres par dates
- exclusion ou inclusion des commandes annulées

Quand MongoDB n'est pas disponible, l'application utilise un **fallback local JSONL** pour conserver les événements statistiques.

## Structure du projet
```text
app/
  Controllers/
  Core/
  Models/
  Services/
  Views/
public/
  assets/
database/
  schema.sql
  seed.sql
documents/
storage/
```

## Installation
### 1. Cloner le dépôt
```bash
git clone <url-du-repo>
```

### 2. Configurer l'environnement
Copier `.env.example` vers `.env`, puis renseigner :
- accès MySQL / MariaDB
- accès MongoDB
- paramètres SMTP

Le fichier `.env` ne doit jamais être versionné.

### 3. Importer la base SQL
- créer une base MySQL / MariaDB
- importer `database/schema.sql`
- importer `database/seed.sql`

### 4. Configurer MongoDB (optionnel pour le site, utile pour les stats)
Exemple local :
```env
MONGO_URI=mongodb://127.0.0.1:27017
MONGO_DB=vite_gourmand
MONGO_STATS_COLLECTION=order_stats
```

Exemple Docker :
```bash
docker run -d --name mongo-vg -p 27017:27017 mongo:7
```

### 5. Lancer l'application
Configurer Apache pour pointer vers le dossier `public/`.

En local, un serveur PHP simple peut aussi être utilisé :
```bash
php -S localhost:8000 -t public
```

## Fonctionnement des statistiques
- un événement statistique est enregistré lors de la création d'une commande ;
- une commande annulée est marquée comme annulée dans la source de stats ;
- l'administration peut filtrer les données et inclure ou non les annulations.

## Sécurité
- mots de passe hashés avec `password_hash()`
- requêtes préparées PDO
- protection CSRF sur les formulaires sensibles
- gestion des rôles : client / employé / admin
- variables sensibles isolées dans `.env`

## Déploiement
Le projet est prévu pour un hébergement mutualisé compatible PHP 8 avec un dossier public dédié.

## Documents
Les documents demandés (diagrammes, maquettes, manuels, document technique) sont disponibles dans le dossier `documents/`.

## Auteur
Projet réalisé par **Julie B.** dans le cadre de l'ECF / titre professionnel DWWM.
