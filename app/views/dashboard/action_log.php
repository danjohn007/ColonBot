<?php
$pageTitle = 'Bitácora de Acciones';
require APP_PATH . '/views/layout/head.php';
?>
<?php require APP_PATH . '/views/layout/navbar.php'; ?>
<main class="max-w-7xl mx-auto px-4 py-8 mb-20">
  <h1 class="text-2xl font-bold text-gray-900 mb-6">📋 Bitácora de Acciones</h1>
  <div class="bg-white rounded-2xl shadow-sm overflow-x-auto">
    <table class="w-full text-sm">
      <thead class="bg-gray-50 text-gray-500 text-xs uppercase tracking-wide">
        <tr>
          <th class="px-4 py-3 text-left">Fecha</th>
          <th class="px-4 py-3 text-left">Usuario</th>
          <th class="px-4 py-3 text-left">Acción</th>
          <th class="px-4 py-3 text-left">Modelo</th>
          <th class="px-4 py-3 text-left">IP</th>
          <th class="px-4 py-3 text-left">Detalle</th>
        </tr>
      </thead>
      <tbody class="divide-y">
        <?php foreach ($logs as $log): ?>
        <tr class="hover:bg-gray-50 transition">
          <td class="px-4 py-2.5 text-gray-500 whitespace-nowrap"><?= date('d/m/y H:i', strtotime($log['created_at'])) ?></td>
          <td class="px-4 py-2.5 text-gray-700"><?= e($log['user_name'] ?? 'Sistema') ?></td>
          <td class="px-4 py-2.5 font-mono text-xs text-blue-700"><?= e($log['action']) ?></td>
          <td class="px-4 py-2.5 text-gray-500 text-xs"><?= e($log['model'] ?? '') ?> <?= $log['model_id'] ? '#'.$log['model_id'] : '' ?></td>
          <td class="px-4 py-2.5 text-gray-400 text-xs"><?= e($log['ip'] ?? '') ?></td>
          <td class="px-4 py-2.5 text-gray-500 text-xs max-w-xs truncate"><?= e($log['detail'] ?? '') ?></td>
        </tr>
        <?php endforeach; ?>
        <?php if (empty($logs)): ?>
        <tr><td colspan="6" class="px-4 py-10 text-center text-gray-400">Sin acciones registradas.</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</main>
<?php require APP_PATH . '/views/layout/bottom_nav.php'; ?>
<?php require APP_PATH . '/views/layout/footer.php'; ?>
