<?php
declare(strict_types=1);

namespace App\Core;

class Router
{
    private array $routes = [
        'GET' => [],
        'POST' => [],
    ];

    public function get(string $path, string $action): void
    {
        $this->routes['GET'][] = [$this->normalize($path), $action];
    }

    public function post(string $path, string $action): void
    {
        $this->routes['POST'][] = [$this->normalize($path), $action];
    }

    /**
     * Trouve et exécute la route correspondant à l'URL.
     */
    public function dispatch(?string $path = null): void
    {
        $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

        // URL demandée (sans query string)
        $uri = $path ?? (parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?? '/');
        $uri = $this->normalize($uri);

        $routes = $this->routes[$method] ?? [];

        foreach ($routes as [$routePath, $action]) {
            $params = $this->match($routePath, $uri);
            if ($params !== null) {
                $this->runAction($action, $params);
                return;
            }
        }

        http_response_code(404);
        echo 'URL not found';
    }

    private function normalize(string $path): string
    {
        $path = '/' . ltrim($path, '/');

        if ($path !== '/') {
            $path = rtrim($path, '/');
        }

        return $path;
    }

    /**
     * Match une route type "/menus/{id}" avec "/menus/12"
     */
    private function match(string $routePath, string $uri): ?array
    {
        $pattern = preg_replace(
            '#\{([a-zA-Z_][a-zA-Z0-9_]*)\}#',
            '(?P<$1>[^/]+)',
            $routePath
        );

        $pattern = '#^' . $pattern . '$#';

        if (!preg_match($pattern, $uri, $matches)) {
            return null;
        }

        $params = [];
        foreach ($matches as $key => $value) {
            if (!is_int($key)) {
                $params[$key] = $value;
            }
        }

        return $params;
    }

    private function runAction(string $action, array $params): void
    {
        [$controllerName, $method] = explode('@', $action);

        $controllerClass = 'App\\Controllers\\' . $controllerName;

        if (!class_exists($controllerClass)) {
            http_response_code(500);
            echo "Controller introuvable : " . htmlspecialchars($controllerClass);
            return;
        }

        $controller = new $controllerClass();

        if (!method_exists($controller, $method)) {
            http_response_code(500);
            echo "Méthode introuvable : " . htmlspecialchars($method);
            return;
        }

        // Appel sécurisé (PHP 8+)
        // PHP 8 : call_user_func_array() interprète les clés associatives comme des paramètres nommés.
        // Or, dans ce projet, certains contrôleurs attendent un tableau $params (ex: array $params),
        // et d'autres attendent directement une valeur (ex: string $id).
        // On détecte le cas "array $params" pour garder le comportement historique.

        $ref = new \ReflectionMethod($controller, $method);
        $ps  = $ref->getParameters();

        if (count($ps) === 1) {
            $t = $ps[0]->getType();
            if ($t instanceof \ReflectionNamedType && $t->getName() === 'array') {
                $controller->{$method}($params);
                return;
            }
        }

        // Sinon : on passe les valeurs dans l'ordre (sans les clés)
        call_user_func_array([$controller, $method], array_values($params));
    }
}
