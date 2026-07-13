<?php
/**
 * Configuración Global del Sistema
 * Plataforma Turística Interactiva – Municipio de Colón
 */

// ─── Detección automática de la URL base ───────────────────────────────────
if (!function_exists('app_url_path')) {
    function app_url_path(string $path): string
    {
        $path = trim(str_replace('\\', '/', rawurldecode($path)), '/');
        if ($path === '' || $path === '.') {
            return '';
        }
        return '/' . implode('/', array_map('rawurlencode', explode('/', $path)));
    }
}

$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$host     = $_SERVER['HTTP_HOST'] ?? 'localhost';
$script   = rawurldecode($_SERVER['SCRIPT_NAME'] ?? '/index.php');
if (!defined('BASE_URL')) {
    $baseUrlOverride = getenv('APP_BASE_URL') ?: '';
    if ($baseUrlOverride !== '') {
        define('BASE_URL', rtrim($baseUrlOverride, '/'));
    } else {
        $basePath = getenv('APP_BASE_PATH') ?: dirname($script);
        define('BASE_URL', $protocol . '://' . $host . app_url_path((string)$basePath));
    }
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
$currentImagesRoot = ROOT_PATH . '/images';
$parentImagesRoot  = dirname(ROOT_PATH) . '/images';
if (basename(ROOT_PATH) === 'landing' || is_dir($currentImagesRoot) || !is_dir($parentImagesRoot)) {
    define('IMAGES_ROOT', ROOT_PATH);
    define('IMAGES_URL',  rtrim(BASE_URL, '/'));
} else {
    $parentBasePath = rtrim(dirname((string)(parse_url(BASE_URL, PHP_URL_PATH) ?? '')), '/\\');
    $parentBaseUrl  = $protocol . '://' . $host . ($parentBasePath !== '' && $parentBasePath !== '.' ? '/' . trim($parentBasePath, '/\\') : '');
    define('IMAGES_ROOT', dirname(ROOT_PATH));
    define('IMAGES_URL',  rtrim($parentBaseUrl, '/'));
}

// ─── Base de datos ─────────────────────────────────────────────────────────
define('DB_HOST',     getenv('DB_HOST')     ?: 'localhost');
define('DB_NAME',     getenv('DB_NAME')     ?: 'colon_colonbotdb');
define('DB_USER',     getenv('DB_USER')     ?: 'colon_enolobot');
define('DB_PASS',     getenv('DB_PASS')     ?: '');
define('DB_CHARSET',  'utf8mb4');

// ─── Aplicación ────────────────────────────────────────────────────────────
define('APP_NAME',    'Plataforma Turística – Colón');
define('APP_VERSION', '1.0.0');
define('APP_ENV',     getenv('APP_ENV') ?: 'production'); // development | production

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
    error_reporting(0);
}

// ─── Iniciar sesión ────────────────────────────────────────────────────────
if (session_status() === PHP_SESSION_NONE) {
    session_name(SESSION_NAME);
    $sessionPath = app_url_path((string)(parse_url(BASE_URL, PHP_URL_PATH) ?? ''));
    $sessionPath = $sessionPath !== '' ? $sessionPath : '/';
    session_set_cookie_params([
        'lifetime' => SESSION_LIFETIME,
        'path' => $sessionPath,
        'secure' => (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off'),
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_start();
}
