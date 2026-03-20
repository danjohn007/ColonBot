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

    protected function requireAuth(string $role = 'admin'): void
    {
        if (!isset($_SESSION['user'])) {
            $this->redirect('login');
        }
        $userRole = $_SESSION['user']['role'] ?? '';
        $allowed  = match ($role) {
            'superadmin' => ['superadmin'],
            'admin'      => ['admin', 'superadmin'],
            default      => ['visitor', 'admin', 'superadmin'],
        };
        if (!in_array($userRole, $allowed, true)) {
            http_response_code(403);
            die('Acceso no autorizado');
        }
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

    protected function saveUpload(array $file): ?string
    {
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, ALLOWED_IMG_EXT, true)) return null;
        if ($file['size'] > MAX_FILE_SIZE) return null;

        $filename = bin2hex(random_bytes(16)) . '.' . $ext;
        $dest     = UPLOAD_PATH . '/' . $filename;

        if (!is_dir(UPLOAD_PATH)) {
            mkdir(UPLOAD_PATH, 0755, true);
        }

        return move_uploaded_file($file['tmp_name'], $dest) ? $filename : null;
    }
}
