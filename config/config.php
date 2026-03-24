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
define('ROOT_PATH',   dirname(__DIR__));
define('APP_PATH',    ROOT_PATH . '/app');
define('PUBLIC_PATH', ROOT_PATH . '/public');

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

// ─── Sesiones ──────────────────────────────────────────────────────────────
define('SESSION_NAME',     'colonbot_session');
define('SESSION_LIFETIME', 3600 * 8); // 8 horas

// ─── Subidas de archivos ───────────────────────────────────────────────────
define('UPLOAD_PATH',    ROOT_PATH . '/images');
define('UPLOAD_URL',     'https://colon.click/sistema/images');
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
    session_set_cookie_params(SESSION_LIFETIME);
    session_start();
}
