<?php
/**
 * Enrutador MVC simple
 */
class Router
{
    private array $routes = [];

    public function get(string $path, string $controller, string $method): void
    {
        $this->routes[] = ['GET', $path, $controller, $method];
    }

    public function post(string $path, string $controller, string $method): void
    {
        $this->routes[] = ['POST', $path, $controller, $method];
    }

    public function dispatch(): void
    {
        $httpMethod = $_SERVER['REQUEST_METHOD'];
        $uri        = $this->getUri();

        foreach ($this->routes as [$routeMethod, $routePath, $controller, $action]) {
            if ($httpMethod !== $routeMethod) {
                continue;
            }
            $params = $this->match($routePath, $uri);
            if ($params !== null) {
                $ctrl = new $controller();
                call_user_func_array([$ctrl, $action], $params);
                return;
            }
        }

        // 404
        http_response_code(404);
        require APP_PATH . '/views/errors/404.php';
    }

    private function getUri(): string
    {
        $scriptDir  = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\');
        $requestUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        if ($scriptDir !== '' && strpos($requestUri, $scriptDir) === 0) {
            $requestUri = substr($requestUri, strlen($scriptDir));
        }
        return trim($requestUri, '/');
    }

    private function match(string $routePath, string $uri): ?array
    {
        // Split on {param} tokens, quote static parts, then reassemble
        $parts   = preg_split('/(\{[a-z_]+\})/', $routePath, -1, PREG_SPLIT_DELIM_CAPTURE);
        $pattern = '';
        foreach ($parts as $part) {
            $pattern .= preg_match('/^\{[a-z_]+\}$/', $part)
                ? '([^/]+)'
                : preg_quote($part, '#');
        }
        if (preg_match('#^' . $pattern . '$#', $uri, $matches)) {
            array_shift($matches);
            return $matches;
        }
        return null;
    }
}
