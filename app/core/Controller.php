<?php
/**
 * Clase base de Controladores
 */
abstract class Controller
{
    protected function view(string $view, array $data = []): void
    {
        extract($data);
        $viewPath = APP_PATH . '/views/' . str_replace('.', '/', $view) . '.php';
        if (!file_exists($viewPath)) {
            http_response_code(500);
            die('View not found: ' . htmlspecialchars($view));
        }
        require $viewPath;
    }

    protected function json(mixed $data, int $code = 200): void
    {
        http_response_code($code);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }

    protected function redirect(string $path = ''): void
    {
        header('Location: ' . BASE_URL . '/' . ltrim($path, '/'));
        exit;
    }

    protected function currentRouteUri(): string
    {
        $requestPath = trim((string)(parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH) ?? ''), '/');
        $basePath = trim((string)(parse_url(BASE_URL, PHP_URL_PATH) ?? ''), '/');

        if ($basePath !== '' && ($requestPath === $basePath || strpos($requestPath, $basePath . '/') === 0)) {
            $requestPath = trim(substr($requestPath, strlen($basePath)), '/');
        }

        return $requestPath;
    }

    protected function pathForCurrentPrefix(string $path, string $prefix = 'landing'): string
    {
        $path = ltrim($path, '/');
        $routeUri = $this->currentRouteUri();
        $hasPrefix = $routeUri === $prefix || strpos($routeUri, $prefix . '/') === 0;

        return $hasPrefix ? $prefix . '/' . $path : $path;
    }

    protected function redirectForCurrentPrefix(string $path, string $prefix = 'landing'): void
    {
        $this->redirect($this->pathForCurrentPrefix($path, $prefix));
    }

    protected function requireAuth(string $role = 'admin'): void
    {
        if (!isset($_SESSION['user'])) {
            $this->redirect('login');
        }
        $userRole = $this->normalizeRole($_SESSION['user']['role'] ?? '');
        $allowed  = match ($role) {
            'superadmin'        => ['superadmin'],
            'admin'             => ['colaborador_admin', 'superadmin'],
            'colaborador_admin' => ['colaborador_admin', 'superadmin'],
            'prestador'         => ['prestador', 'colaborador_admin', 'superadmin'],
            'colaborador'       => ['colaborador_admin', 'superadmin'],
            'visitor'           => ['visitor', 'colaborador_admin', 'superadmin'],
            default             => ['visitor', 'colaborador_admin', 'superadmin', 'prestador'],
        };
        if (!in_array($userRole, $allowed, true)) {
            http_response_code(403);
            die('Acceso no autorizado');
        }
    }

    protected function normalizeRole(string $role): string
    {
        return match ($role) {
            'admin_colaborador', 'colaborador', 'admin' => 'colaborador_admin',
            'turista' => 'visitor',
            default => $role,
        };
    }

    protected function isPost(): bool
    {
        return $_SERVER['REQUEST_METHOD'] === 'POST';
    }

    protected function input(string $key, mixed $default = null): mixed
    {
        return $_POST[$key] ?? $_GET[$key] ?? $default;
    }

    protected function flash(string $type, string $msg): void
    {
        $_SESSION['flash'] = ['type' => $type, 'msg' => $msg];
    }

    protected function csrf(): string
    {
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }

    protected function verifyCsrf(): void
    {
        $token = $_POST['_csrf'] ?? '';
        if (!hash_equals($_SESSION['csrf_token'] ?? '', $token)) {
            http_response_code(419);
            die('CSRF token mismatch');
        }
    }

    protected function logAction(string $action, string $model = '', int $modelId = 0, string $detail = ''): void
    {
        $db     = Database::getInstance();
        $userId = $_SESSION['user']['id'] ?? null;
        $ip     = $_SERVER['REMOTE_ADDR'] ?? null;
        $stmt   = $db->prepare(
            'INSERT INTO action_log (user_id, action, model, model_id, detail, ip) VALUES (?,?,?,?,?,?)'
        );
        $stmt->execute([$userId, $action, $model ?: null, $modelId ?: null, $detail ?: null, $ip]);
    }
}
