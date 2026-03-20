<?php
$pageTitle = 'GPS Trackers';
require APP_PATH . '/views/layout/head.php';
?>
<?php require APP_PATH . '/views/layout/navbar.php'; ?>
<main class="max-w-5xl mx-auto px-4 py-8 mb-20">
  <div class="flex items-center gap-3 mb-6">
    <a href="<?= url('configuraciones') ?>" class="text-gray-400 hover:text-blue-600">← Config</a>
    <h1 class="text-2xl font-bold text-gray-900">📡 GPS Trackers</h1>
  </div>

  <button onclick="document.getElementById('modal-gps').classList.remove('hidden')"
    class="mb-6 bg-blue-600 text-white px-5 py-2.5 rounded-xl font-semibold hover:bg-blue-700 transition text-sm">
    + Agregar GPS Tracker
  </button>

  <div class="bg-white rounded-2xl shadow-sm overflow-x-auto">
    <table class="w-full text-sm">
      <thead class="bg-gray-50 text-gray-500 text-xs uppercase tracking-wide">
        <tr>
          <th class="px-4 py-3 text-left">Nombre</th>
          <th class="px-4 py-3 text-left">IMEI</th>
          <th class="px-4 py-3 text-left">Proveedor</th>
          <th class="px-4 py-3 text-left">Última posición</th>
          <th class="px-4 py-3 text-left">Última señal</th>
          <th class="px-4 py-3 text-left">Acciones</th>
        </tr>
      </thead>
      <tbody class="divide-y">
        <?php foreach ($trackers as $t): ?>
        <tr class="hover:bg-gray-50 transition">
          <td class="px-4 py-3 font-medium text-gray-800"><?= e($t['name']) ?></td>
          <td class="px-4 py-3 font-mono text-xs text-gray-600"><?= e($t['imei']) ?></td>
          <td class="px-4 py-3 text-gray-600"><?= e($t['provider'] ?? '-') ?></td>
          <td class="px-4 py-3 text-gray-600">
            <?php if ($t['last_lat'] && $t['last_lng']): ?>
            <a href="https://www.google.com/maps?q=<?= $t['last_lat'] ?>,<?= $t['last_lng'] ?>" target="_blank"
              class="text-blue-600 hover:underline text-xs">
              📍 <?= round($t['last_lat'], 4) ?>, <?= round($t['last_lng'], 4) ?>
            </a>
            <?php else: ?>
            <span class="text-gray-400 text-xs">Sin datos</span>
            <?php endif; ?>
          </td>
          <td class="px-4 py-3 text-gray-400 text-xs">
            <?= $t['last_seen'] ? date('d/m/y H:i', strtotime($t['last_seen'])) : 'Nunca' ?>
          </td>
          <td class="px-4 py-3">
            <form method="POST" action="<?= url('configuraciones/gps/' . $t['id'] . '/eliminar') ?>" class="inline"
              onsubmit="return confirm('¿Eliminar GPS tracker?')">
              <input type="hidden" name="_csrf" value="<?= e($csrf) ?>">
              <button type="submit" class="text-xs bg-red-50 text-red-600 px-3 py-1.5 rounded-lg hover:bg-red-100 transition">Eliminar</button>
            </form>
          </td>
        </tr>
        <?php endforeach; ?>
        <?php if (empty($trackers)): ?>
        <tr><td colspan="6" class="px-4 py-10 text-center text-gray-400">Sin GPS trackers configurados.</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>

  <div class="mt-6 bg-blue-50 rounded-2xl p-4 text-sm text-blue-700">
    <p class="font-semibold mb-1">📡 Endpoint para actualización de posición GPS:</p>
    <code class="font-mono break-all text-xs"><?= url('api/gps/actualizar') ?></code>
    <p class="mt-2 text-xs">Parámetros POST: <code class="font-mono">imei</code>, <code class="font-mono">lat</code>, <code class="font-mono">lng</code>, <code class="font-mono">api_key</code></p>
  </div>
</main>

<!-- Modal -->
<div id="modal-gps" class="hidden fixed inset-0 bg-black/40 z-50 flex items-center justify-center p-4">
  <div class="bg-white rounded-2xl shadow-xl w-full max-w-sm p-6">
    <h2 class="text-lg font-bold mb-4">Agregar GPS Tracker</h2>
    <form method="POST" action="<?= url('configuraciones/gps/crear') ?>" class="space-y-3">
      <input type="hidden" name="_csrf" value="<?= e($csrf) ?>">
      <input type="text" name="name" required placeholder="Nombre (ej. Unidad 01)" class="w-full input">
      <input type="text" name="imei" required placeholder="IMEI del dispositivo" class="w-full input font-mono text-sm" maxlength="20">
      <input type="text" name="provider" placeholder="Proveedor (ej. Telcel, AT&T)" class="w-full input">
      <input type="password" name="api_key" placeholder="API Key (opcional)" class="w-full input font-mono text-sm">
      <div class="flex gap-2 pt-2">
        <button type="submit" class="flex-1 bg-blue-600 text-white py-2.5 rounded-xl font-semibold">Agregar</button>
        <button type="button" onclick="document.getElementById('modal-gps').classList.add('hidden')"
          class="px-5 py-2.5 bg-gray-100 text-gray-700 rounded-xl font-medium">Cancelar</button>
      </div>
    </form>
  </div>
</div>

<style>.input { @apply px-4 py-2.5 border border-gray-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500; }</style>
<?php require APP_PATH . '/views/layout/bottom_nav.php'; ?>
<?php require APP_PATH . '/views/layout/footer.php'; ?>
