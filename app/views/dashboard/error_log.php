<?php
$pageTitle = 'Registro de Errores';
require APP_PATH . '/views/layout/head.php';
?>
<?php require APP_PATH . '/views/layout/navbar.php'; ?>
<main class="max-w-7xl mx-auto px-4 py-8 mb-20">
  <h1 class="text-2xl font-bold text-gray-900 mb-6">🚨 Registro de Errores</h1>
  <div class="bg-white rounded-2xl shadow-sm overflow-x-auto">
    <table class="w-full text-sm">
      <thead class="bg-gray-50 text-gray-500 text-xs uppercase tracking-wide">
        <tr>
          <th class="px-4 py-3 text-left">Fecha</th>
          <th class="px-4 py-3 text-left">Nivel</th>
          <th class="px-4 py-3 text-left">Mensaje</th>
          <th class="px-4 py-3 text-left">Archivo</th>
          <th class="px-4 py-3 text-left">Línea</th>
        </tr>
      </thead>
      <tbody class="divide-y">
        <?php foreach ($logs as $log): ?>
        <tr class="hover:bg-gray-50 transition">
          <td class="px-4 py-2.5 text-gray-500 whitespace-nowrap"><?= date('d/m/y H:i', strtotime($log['created_at'])) ?></td>
          <td class="px-4 py-2.5">
            <span class="px-2 py-0.5 rounded-full text-xs font-medium <?= match($log['level']) {
              'critical' => 'bg-red-200 text-red-800',
              'error'    => 'bg-red-100 text-red-700',
              'warning'  => 'bg-yellow-100 text-yellow-700',
              default    => 'bg-gray-100 text-gray-600',
            } ?>">
              <?= e($log['level']) ?>
            </span>
          </td>
          <td class="px-4 py-2.5 text-gray-700 max-w-md truncate"><?= e($log['message']) ?></td>
          <td class="px-4 py-2.5 text-gray-400 text-xs"><?= e(basename($log['file'] ?? '')) ?></td>
          <td class="px-4 py-2.5 text-gray-400 text-xs"><?= $log['line'] ?></td>
        </tr>
        <?php endforeach; ?>
        <?php if (empty($logs)): ?>
        <tr><td colspan="5" class="px-4 py-10 text-center text-gray-400">✅ Sin errores registrados.</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</main>
<?php require APP_PATH . '/views/layout/bottom_nav.php'; ?>
<?php require APP_PATH . '/views/layout/footer.php'; ?>
