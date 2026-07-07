<?php
/**
 * Configuración Global del Sistema
 * Plataforma Turística Interactiva – Municipio de Colón
 */

// ─── Detección automática de la URL base ───────────────────────────────────
if (!defined('BASE_URL')) {
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $host     = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $script   = $_SERVER['SCRIPT_NAME'] ?? '/index.php';
    $basePath = rtrim(dirname($script), '/\\');
    define('BASE_URL', $protocol . '://' . $host . $basePath);
}

// ─── Rutas del sistema ─────────────────────────────────────────────────────
if (!defined('ROOT_PATH')) {
    define('ROOT_PATH', dirname(__DIR__));
}
if (!defined('APP_PATH')) {
    define('APP_PATH', ROOT_PATH . '/app');
}
if (!defined('PUBLIC_PATH')) {
    define('PUBLIC_PATH', ROOT_PATH . '/public');
}

// ─── Detección de carpeta compartida de imágenes ──────────────────────────
// Si estamos en /sistema/2/ o /sistema/1/, usar /sistema/images/
// Si estamos en /sistema/ directamente, usar /sistema/images/
$imageBasePath = dirname(ROOT_PATH); // Sube un nivel (de /2/ a /sistema/)
$imageBaseUrl  = $protocol . '://' . $host . '/' . trim(dirname(dirname($script)), '/\\');
if (basename(ROOT_PATH) !== 'sistema') {
    // Estamos en una subcarpeta (1, 2, etc.)
    define('IMAGES_ROOT', $imageBasePath);
    define('IMAGES_URL',  rtrim($imageBaseUrl, '/'));
} else {
    // Estamos directamente en /sistema/
    define('IMAGES_ROOT', ROOT_PATH);
    define('IMAGES_URL',  rtrim($protocol . '://' . $host . '/' . trim(dirname($script), '/\\'), '/'));
}

// ─── Base de datos ─────────────────────────────────────────────────────────
define('DB_HOST',     getenv('DB_HOST')     ?: 'localhost');
define('DB_NAME',     getenv('DB_NAME')     ?: 'colon_colonbotdb');
define('DB_USER',     getenv('DB_USER')     ?: 'colon_enolobot');
define('DB_PASS',     getenv('DB_PASS')     ?: 'B@#O,lv&uA2*');
define('DB_CHARSET',  'utf8mb4');

// ─── Aplicación ────────────────────────────────────────────────────────────
define('APP_NAME',    'Plataforma Turística – Colón');
define('APP_VERSION', '1.0.0');
define('APP_ENV',     getenv('APP_ENV') ?: 'production'); // development | production

// ─── Registro explícito de errores para diagnosticar HTTP 500 ─────────────
ini_set('log_errors', '1');
set_error_handler(function (int $severity, string $message, string $file, int $line): bool {
    if (!(error_reporting() & $severity)) {
        return false;
    }

    $url = ($_SERVER['REQUEST_METHOD'] ?? 'CLI') . ' ' . ($_SERVER['REQUEST_URI'] ?? '');
    error_log("[ColonBot PHP error] {$message} in {$file}:{$line} | {$url}");
    return false;
});

set_exception_handler(function (Throwable $e): void {
    $url = ($_SERVER['REQUEST_METHOD'] ?? 'CLI') . ' ' . ($_SERVER['REQUEST_URI'] ?? '');
    error_log('[ColonBot uncaught exception] ' . get_class($e) . ': ' . $e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine() . " | {$url}\n" . $e->getTraceAsString());
    http_response_code(500);
    if (defined('APP_ENV') && APP_ENV === 'development') {
        echo '<pre>' . htmlspecialchars((string)$e, ENT_QUOTES, 'UTF-8') . '</pre>';
    }
});

register_shutdown_function(function (): void {
    $error = error_get_last();
    if (!$error) {
        return;
    }

    $fatalTypes = [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR, E_USER_ERROR];
    if (!in_array($error['type'], $fatalTypes, true)) {
        return;
    }

    $url = ($_SERVER['REQUEST_METHOD'] ?? 'CLI') . ' ' . ($_SERVER['REQUEST_URI'] ?? '');
    error_log("[ColonBot fatal error] {$error['message']} in {$error['file']}:{$error['line']} | {$url}");
});

// ─── Sesiones ──────────────────────────────────────────────────────────────
define('SESSION_NAME',     'colonbot_session');
define('SESSION_LIFETIME', 3600 * 8); // 8 horas

// ─── Subidas de archivos ───────────────────────────────────────────────────
define('UPLOAD_PATH',    IMAGES_ROOT . '/images');
define('UPLOAD_URL',     IMAGES_URL . '/images');
define('MAX_FILE_SIZE',  5 * 1024 * 1024); // 5 MB
define('ALLOWED_IMG_EXT', ['jpg', 'jpeg', 'png', 'gif', 'webp']);

// ─── Zona horaria ──────────────────────────────────────────────────────────
date_default_timezone_set('America/Mexico_City');

// ─── Errores (en producción se ocultan) ────────────────────────────────────
if (APP_ENV === 'development') {
    ini_set('display_errors', 1);
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', 0);
    error_reporting(E_ALL);
}

// ─── Iniciar sesión ────────────────────────────────────────────────────────
if (session_status() === PHP_SESSION_NONE) {
    session_name(SESSION_NAME);
    session_set_cookie_params(SESSION_LIFETIME);
    session_start();
}
