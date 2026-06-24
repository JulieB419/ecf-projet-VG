<?php
declare(strict_types=1);


// Récupération de l'URL demandée (sans query string)
$uri  = $_SERVER['REQUEST_URI'] ?? '/';
$path = parse_url($uri, PHP_URL_PATH) ?? '/';

// Base du projet (auto-détection) :
// - en local: http://localhost/vite-gourmand/public => base = /vite-gourmand/public
// - en prod (docroot = /public): base = ''
$scriptName = $_SERVER['SCRIPT_NAME'] ?? '/index.php';
$projectBase = rtrim(str_replace('\\', '/', dirname($scriptName)), '/');
if ($projectBase === '/' || $projectBase === '.') {
    $projectBase = '';
}
if ($projectBase !== '' && strpos($path, $projectBase) === 0) {
    $path = substr($path, strlen($projectBase));
}

// Normalisation du chemin
$path = '/' . ltrim($path, '/');
if ($path !== '/') {
    $path = rtrim($path, '/');
}

require __DIR__ . '/../app/bootstrap.php';

use App\Core\Router;

$router = new Router();

// Routes publiques
$router->get('/', 'HomeController@index');
$router->get('/menus', 'MenuController@index');
$router->get('/menus/{id}', 'MenuController@show');
$router->get('/api/menus', 'MenuController@apiIndex');
$router->get('/api/menus/{id}', 'MenuController@apiShow');

// Auth
$router->get('/inscription', 'AuthController@registerForm');
$router->post('/inscription', 'AuthController@register');
$router->get('/connexion', 'AuthController@loginForm');
$router->post('/connexion', 'AuthController@login');
$router->post('/deconnexion', 'AuthController@logout');

$router->get('/mot-de-passe-oublie', 'AuthController@forgotForm');
$router->post('/mot-de-passe-oublie', 'AuthController@forgot');
$router->get('/reinitialiser-mot-de-passe', 'AuthController@resetForm');
$router->post('/reinitialiser-mot-de-passe', 'AuthController@reset');

// Commandes
$router->get('/commander/{menuId}', 'OrderController@createForm');
$router->post('/commander/{menuId}', 'OrderController@create');

// Client
$router->get('/profil', 'ProfileController@index');
$router->get('/profil/commandes/{id}', 'ProfileController@orderShow');
$router->post('/profil/commandes/{id}/annuler', 'ProfileController@cancelOrder');
$router->post('/profil/commandes/{id}/avis', 'ProfileController@submitReview');

// Employé
$router->get('/espace-employe', 'EmployeeController@index');
$router->get('/espace-employe/commandes/{id}', 'EmployeeController@orderShow');
$router->post('/espace-employe/commandes/{id}/statut', 'EmployeeController@updateStatus');
$router->post('/espace-employe/commandes/{id}/annuler', 'EmployeeController@cancelOrder');
$router->post('/espace-employe/avis/{id}/valider', 'EmployeeController@validateReview');
$router->post('/espace-employe/avis/{id}/refuser', 'EmployeeController@rejectReview');
$router->get('/espace-employe/horaires', 'OpeningHoursController@editForm');
$router->post('/espace-employe/horaires', 'OpeningHoursController@update');

// Admin
$router->get('/administration', 'AdminController@index');

// Admin - Legal pages CMS
$router->get('/administration/pages', 'AdminController@pagesForm');
$router->post('/administration/pages', 'AdminController@pagesSave');

/* Informations du traiteur (administration) */
$router->get('/administration/informations', 'AdminController@settingsForm');
$router->post('/administration/informations', 'AdminController@settingsSave');
$router->post('/administration/informations/horaires', 'AdminController@openingHoursSave');
// Alias rétro-compat (ancienne URL)
$router->get('/administration/settings', 'AdminController@settingsForm');
$router->post('/administration/settings', 'AdminController@settingsSave');
$router->get('/administration/employes', 'AdminController@employees');
$router->get('/administration/employes/nouveau', 'AdminController@employeeCreateForm');
$router->post('/administration/employes/nouveau', 'AdminController@employeeCreate');
$router->post('/administration/employes/{id}/desactiver', 'AdminController@employeeDisable');
$router->get('/administration/stats', 'AdminController@stats');
$router->post('/administration/stats/rebuild', 'AdminController@rebuildStats');

// Admin - Plats
$router->get('/administration/plats', 'AdminController@dishes');
$router->get('/administration/dishes', 'AdminController@dishes');
$router->get('/administration/plats/nouveau', 'AdminController@dishCreateForm');
$router->get('/administration/dishes/nouveau', 'AdminController@dishCreateForm');
$router->post('/administration/plats/nouveau', 'AdminController@dishCreate');
$router->post('/administration/dishes/nouveau', 'AdminController@dishCreate');
$router->get('/administration/plats/{id}/modifier', 'AdminController@dishEditForm');
$router->get('/administration/dishes/{id}/modifier', 'AdminController@dishEditForm');
$router->post('/administration/plats/{id}/modifier', 'AdminController@dishUpdate');
$router->post('/administration/dishes/{id}/modifier', 'AdminController@dishUpdate');
$router->post('/administration/plats/{id}/supprimer', 'AdminController@dishDelete');
$router->post('/administration/dishes/{id}/supprimer', 'AdminController@dishDelete');

// Admin - Menus
$router->get('/administration/menus', 'AdminController@menus');
$router->get('/administration/menus/nouveau', 'AdminController@menuCreateForm');
$router->post('/administration/menus/nouveau', 'AdminController@menuCreate');
$router->get('/administration/menus/{id}/modifier', 'AdminController@menuEditForm');
$router->post('/administration/menus/{id}/modifier', 'AdminController@menuUpdate');
$router->post('/administration/menus/{id}/toggle', 'AdminController@menuToggle');

// Contact & légal
$router->get('/contact', 'ContactController@form');
$router->post('/contact', 'ContactController@send');
$router->get('/mentions-legales', 'LegalController@legal');
$router->get('/cgv', 'LegalController@cgv');

// Dispatch final
$router->dispatch($path);
