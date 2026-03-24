<?php
/**
 * Funciones de ayuda globales
 */

function url(string $path = ''): string
{
    return BASE_URL . '/' . ltrim($path, '/');
}

function asset(string $path): string
{
    return '/assets/' . ltrim($path, '/');
}

function imageUrl(string $filename): string
{
    if (empty($filename)) return '';
    return UPLOAD_URL . '/' . ltrim($filename, '/');
}

function e(mixed $val): string
{
    return htmlspecialchars((string)$val, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function flash(): ?array
{
    $flash = $_SESSION['flash'] ?? null;
    unset($_SESSION['flash']);
    return $flash;
}

function isLoggedIn(): bool
{
    return isset($_SESSION['user']);
}

function currentUser(): ?array
{
    return $_SESSION['user'] ?? null;
}

function hasRole(string $role): bool
{
    $user = currentUser();
    if (!$user) return false;
    return match ($role) {
        'superadmin' => $user['role'] === 'superadmin',
        'admin'      => in_array($user['role'], ['admin', 'superadmin'], true),
        default      => true,
    };
}

function setting(string $key, string $default = ''): string
{
    static $cache = null;
    if ($cache === null) {
        try {
            $db   = Database::getInstance();
            $rows = $db->query('SELECT `key`, `value` FROM settings')->fetchAll(PDO::FETCH_KEY_PAIR);
            $cache = $rows;
        } catch (Throwable) {
            $cache = [];
        }
    }
    return $cache[$key] ?? $default;
}

function slugify(string $text): string
{
    $text = mb_strtolower($text, 'UTF-8');
    $text = strtr($text, [
        'á'=>'a','é'=>'e','í'=>'i','ó'=>'o','ú'=>'u',
        'ä'=>'a','ë'=>'e','ï'=>'i','ö'=>'o','ü'=>'u',
        'ñ'=>'n','ç'=>'c','ß'=>'ss',
    ]);
    $text = preg_replace('/[^a-z0-9\s-]/', '', $text);
    $text = preg_replace('/[\s-]+/', '-', trim($text));
    return substr($text, 0, 180);
}

function formatPrice(float $price): string
{
    return '$' . number_format($price, 2, '.', ',');
}

function timeAgo(\DateTimeInterface $dt): string
{
    $diff = time() - $dt->getTimestamp();
    if ($diff < 60)      return 'hace unos segundos';
    if ($diff < 3600)    return 'hace ' . floor($diff/60) . ' min';
    if ($diff < 86400)   return 'hace ' . floor($diff/3600) . ' h';
    if ($diff < 604800)  return 'hace ' . floor($diff/86400) . ' días';
    return $dt->format('d/m/Y');
}

function logError(string $msg, string $file = '', int $line = 0, string $level = 'error'): void
{
    try {
        $db = Database::getInstance();
        $db->prepare(
            'INSERT INTO error_log (level, message, file, line) VALUES (?,?,?,?)'
        )->execute([$level, $msg, $file, $line]);
    } catch (Throwable) {
        error_log($msg);
    }
}

function waLink(string $phone, string $msg = ''): string
{
    $phone = preg_replace('/\D/', '', $phone);
    $msg   = urlencode($msg);
    return "https://wa.me/{$phone}" . ($msg ? "?text={$msg}" : '');
}

function stars(float $rating): string
{
    $full  = (int)floor($rating);
    $html  = '';
    for ($i = 1; $i <= 5; $i++) {
        $html .= $i <= $full
            ? '<svg class="w-4 h-4 text-yellow-400 inline" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>'
            : '<svg class="w-4 h-4 text-gray-300 inline" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>';
    }
    return $html;
}
