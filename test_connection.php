<?php
/**
 * Test de Conexión y URL Base
 * Plataforma Turística Interactiva – Municipio de Colón
 *
 * Accede a este archivo directamente para verificar la instalación:
 *   http://tu-servidor/test_connection.php
 */

// ─── Cargar config ────────────────────────────────────────────────────────
define('ROOT_PATH', __DIR__);
require_once __DIR__ . '/config/config.php';

$results = [];

// ─── 1. PHP Version ───────────────────────────────────────────────────────
$phpOk = version_compare(PHP_VERSION, '7.4.0', '>=');
$results[] = [
    'test'    => 'PHP Version',
    'value'   => PHP_VERSION,
    'ok'      => $phpOk,
    'detail'  => $phpOk ? 'PHP >= 7.4 ✓' : 'Se requiere PHP >= 7.4',
];

// ─── 2. Extensiones requeridas ────────────────────────────────────────────
$required_exts = ['pdo', 'pdo_mysql', 'mbstring', 'json', 'curl', 'fileinfo'];
foreach ($required_exts as $ext) {
    $loaded = extension_loaded($ext);
    $results[] = [
        'test'   => "Extensión: {$ext}",
        'value'  => $loaded ? 'Habilitada' : 'No encontrada',
        'ok'     => $loaded,
        'detail' => $loaded ? 'OK' : "Por favor habilita la extensión {$ext} en php.ini",
    ];
}

// ─── 3. URL Base ─────────────────────────────────────────────────────────
$results[] = [
    'test'   => 'URL Base detectada',
    'value'  => BASE_URL,
    'ok'     => true,
    'detail' => 'Calculada automáticamente desde SERVER vars',
];

// ─── 4. Conexión a Base de Datos ──────────────────────────────────────────
$dbOk     = false;
$dbDetail = '';
try {
    require_once __DIR__ . '/config/database.php';
    $pdo     = Database::getInstance();
    $version = $pdo->query('SELECT VERSION()')->fetchColumn();
    $dbOk    = true;
    $dbDetail = "MySQL {$version}";
} catch (Throwable $e) {
    $dbDetail = 'Error: ' . $e->getMessage();
}
$results[] = [
    'test'   => 'Conexión MySQL',
    'value'  => $dbOk ? 'Conectado' : 'Fallo',
    'ok'     => $dbOk,
    'detail' => $dbDetail,
];

// ─── 5. Tablas existentes ─────────────────────────────────────────────────
if ($dbOk) {
    $tables = ['users','categories','businesses','amenities','settings','analytics'];
    foreach ($tables as $table) {
        try {
            $exists = $pdo->query("SHOW TABLES LIKE '{$table}'")->fetchColumn() !== false;
            $results[] = [
                'test'   => "Tabla: {$table}",
                'value'  => $exists ? 'Existe' : 'No existe',
                'ok'     => $exists,
                'detail' => $exists ? 'OK' : "Ejecuta database/schema.sql",
            ];
        } catch (Throwable) {
            $results[] = ['test'=>"Tabla: {$table}",'value'=>'Error','ok'=>false,'detail'=>''];
        }
    }
}

// ─── 6. Directorio de uploads ────────────────────────────────────────────
$uploadDir = __DIR__ . '/public/uploads';
$uploadOk  = is_dir($uploadDir) && is_writable($uploadDir);
if (!is_dir($uploadDir)) {
    @mkdir($uploadDir, 0755, true);
    $uploadOk = is_dir($uploadDir) && is_writable($uploadDir);
}
$results[] = [
    'test'   => 'Directorio uploads',
    'value'  => $uploadDir,
    'ok'     => $uploadOk,
    'detail' => $uploadOk ? 'Escribible ✓' : 'Sin permisos de escritura – ejecuta: chmod 755 public/uploads',
];

// ─── 7. mod_rewrite ──────────────────────────────────────────────────────
$htOk = file_exists(__DIR__ . '/.htaccess');
$results[] = [
    'test'   => '.htaccess presente',
    'value'  => $htOk ? 'Sí' : 'No',
    'ok'     => $htOk,
    'detail' => $htOk ? 'URL rewriting configurado' : 'Falta .htaccess, cópialo desde el repositorio',
];

// ─── Render ───────────────────────────────────────────────────────────────
$allOk = !in_array(false, array_column($results, 'ok'), true);
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Test de Conexión – Plataforma Turística Colón</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="min-h-screen bg-gray-50 p-6">
  <div class="max-w-2xl mx-auto">
    <div class="text-center mb-8">
      <div class="text-5xl mb-3">🗺️</div>
      <h1 class="text-2xl font-bold text-gray-900">Plataforma Turística – Municipio de Colón</h1>
      <p class="text-gray-500 mt-1">Diagnóstico del sistema</p>
    </div>

    <!-- Status banner -->
    <div class="mb-6 p-4 rounded-2xl text-center font-semibold <?= $allOk ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' ?>">
      <?= $allOk ? '✅ Sistema listo para funcionar' : '⚠️ Hay elementos que requieren atención' ?>
    </div>

    <div class="bg-white rounded-2xl shadow-sm overflow-hidden">
      <table class="w-full text-sm">
        <thead class="bg-gray-50 text-gray-500 text-xs uppercase tracking-wide">
          <tr>
            <th class="px-5 py-3 text-left">Prueba</th>
            <th class="px-5 py-3 text-left">Resultado</th>
            <th class="px-5 py-3 text-left">Estado</th>
          </tr>
        </thead>
        <tbody class="divide-y">
          <?php foreach ($results as $r): ?>
          <tr class="<?= $r['ok'] ? 'hover:bg-green-50' : 'bg-red-50' ?> transition">
            <td class="px-5 py-3 font-medium text-gray-800"><?= htmlspecialchars($r['test']) ?></td>
            <td class="px-5 py-3 text-gray-600 font-mono text-xs break-all"><?= htmlspecialchars($r['value']) ?></td>
            <td class="px-5 py-3">
              <?php if ($r['ok']): ?>
                <span class="text-green-600 font-semibold">✓ OK</span>
              <?php else: ?>
                <span class="text-red-600 font-semibold">✗ Error</span>
                <br><span class="text-xs text-red-400"><?= htmlspecialchars($r['detail']) ?></span>
              <?php endif; ?>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>

    <?php if ($allOk): ?>
    <div class="mt-6 text-center">
      <a href="<?= BASE_URL ?>" class="inline-block bg-blue-600 text-white px-6 py-3 rounded-xl font-semibold hover:bg-blue-700 transition">
        🚀 Ir a la plataforma
      </a>
    </div>
    <?php endif; ?>

    <div class="mt-8 bg-blue-50 rounded-2xl p-5 text-sm text-blue-800">
      <h3 class="font-semibold mb-2">Credenciales de acceso inicial:</h3>
      <ul class="space-y-1">
        <li><strong>SuperAdmin:</strong> superadmin@colonbot.mx / Admin@2024</li>
        <li><strong>Admin:</strong> admin.mirador@colonbot.mx / Admin@2024</li>
      </ul>
      <p class="mt-3 text-xs text-blue-600">⚠️ Cambia estas contraseñas después del primer acceso.</p>
      <p class="mt-2 text-xs text-blue-500">🗑️ Elimina este archivo después de verificar la instalación.</p>
    </div>
  </div>
</body>
</html>
