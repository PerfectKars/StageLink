<?php
declare(strict_types=1);
namespace App\Core;

class Router
{
    private array $routes = [];

    public function get(string $path, array $handler): void  { $this->routes['GET'][$path]  = $handler; }
    public function post(string $path, array $handler): void { $this->routes['POST'][$path] = $handler; }

    public function dispatch(string $uri, string $method): void
    {
        $uri    = rtrim(parse_url($uri, PHP_URL_PATH), '/') ?: '/';
        $method = strtoupper($method);

        if (isset($this->routes[$method][$uri])) {
            $this->call($this->routes[$method][$uri], []);
            return;
        }

        foreach ($this->routes[$method] ?? [] as $route => $handler) {
            $pattern = '#^' . preg_replace('/:[a-zA-Z_]+/', '([^/]+)', $route) . '$#';
            if (preg_match($pattern, $uri, $matches)) {
                array_shift($matches);
                $this->call($handler, $matches);
                return;
            }
        }

        http_response_code(404);
        $controller = new \App\Controllers\ErrorController();
        $controller->notFound();
    }

    private function call(array $handler, array $params): void
    {
        [$controllerClass, $method] = $handler;
        $fullClass = 'App\\Controllers\\' . $controllerClass;
        if (!class_exists($fullClass)) throw new \RuntimeException("Contrôleur $fullClass introuvable.");
        $controller = new $fullClass();
        if (!method_exists($controller, $method)) throw new \RuntimeException("Méthode $method introuvable.");
        call_user_func_array([$controller, $method], $params);
    }
}
