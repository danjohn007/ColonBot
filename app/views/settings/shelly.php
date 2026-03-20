<?php
$pageTitle = 'Dispositivos Shelly Cloud';
require APP_PATH . '/views/layout/head.php';
?>
<?php require APP_PATH . '/views/layout/navbar.php'; ?>
<main class="max-w-5xl mx-auto px-4 py-8 mb-20">
  <div class="flex items-center gap-3 mb-6">
    <a href="<?= url('configuraciones') ?>" class="text-gray-400 hover:text-blue-600">← Config</a>
    <h1 class="text-2xl font-bold text-gray-900">💡 Dispositivos Shelly Cloud</h1>
  </div>

  <button onclick="document.getElementById('modal-shelly').classList.remove('hidden')"
    class="mb-6 bg-blue-600 text-white px-5 py-2.5 rounded-xl font-semibold hover:bg-blue-700 transition text-sm">
    + Agregar Dispositivo Shelly
  </button>

  <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
    <?php foreach ($devices as $d): ?>
    <div class="bg-white rounded-2xl shadow-sm p-5">
      <div class="flex items-start justify-between mb-3">
        <div>
          <h3 class="font-semibold text-gray-900"><?= e($d['name']) ?></h3>
          <p class="text-xs text-gray-500"><?= e($d['type']) ?> · <?= e($d['location'] ?? '-') ?></p>
        </div>
        <span class="px-2 py-0.5 text-xs rounded-full <?= $d['active'] ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500' ?>">
          <?= $d['active'] ? '🟢 Activo' : 'Inactivo' ?>
        </span>
      </div>
      <p class="text-xs font-mono text-gray-500 mb-3">ID: <?= e($d['device_id']) ?></p>
      <div class="flex gap-2 flex-wrap">
        <button onclick="shellyStatus(<?= $d['id'] ?>, this)"
          class="text-xs bg-blue-50 text-blue-700 px-3 py-1.5 rounded-lg hover:bg-blue-100 transition">
          📊 Estado
        </button>
        <button onclick="shellyToggle(<?= $d['id'] ?>, 0, 'toggle')"
          class="text-xs bg-yellow-50 text-yellow-700 px-3 py-1.5 rounded-lg hover:bg-yellow-100 transition">
          ⚡ Toggle
        </button>
        <form method="POST" action="<?= url('configuraciones/shelly/' . $d['id'] . '/eliminar') ?>" class="inline"
          onsubmit="return confirm('¿Eliminar dispositivo Shelly?')">
          <input type="hidden" name="_csrf" value="<?= e($csrf) ?>">
          <button type="submit" class="text-xs bg-red-50 text-red-600 px-3 py-1.5 rounded-lg hover:bg-red-100 transition">Eliminar</button>
        </form>
      </div>
      <div id="shelly-status-<?= $d['id'] ?>" class="mt-3 text-xs text-gray-500"></div>
    </div>
    <?php endforeach; ?>
    <?php if (empty($devices)): ?>
    <div class="col-span-3 bg-white rounded-2xl shadow-sm p-12 text-center text-gray-400">
      No hay dispositivos Shelly configurados.
    </div>
    <?php endif; ?>
  </div>
</main>

<!-- Modal -->
<div id="modal-shelly" class="hidden fixed inset-0 bg-black/40 z-50 flex items-center justify-center p-4">
  <div class="bg-white rounded-2xl shadow-xl w-full max-w-md p-6">
    <h2 class="text-lg font-bold mb-4">Agregar Dispositivo Shelly Cloud</h2>
    <form method="POST" action="<?= url('configuraciones/shelly/crear') ?>" class="space-y-3">
      <input type="hidden" name="_csrf" value="<?= e($csrf) ?>">
      <input type="text" name="name" required placeholder="Nombre (ej. Luz Entrada)" class="w-full input">
      <input type="text" name="device_id" required placeholder="Device ID de Shelly Cloud" class="w-full input font-mono text-sm">
      <input type="password" name="auth_key" required placeholder="Auth Key" class="w-full input font-mono text-sm">
      <input type="url" name="server_uri" value="https://shelly-41-eu.shelly.cloud" placeholder="Server URI" class="w-full input font-mono text-sm">
      <select name="type" class="w-full input">
        <option value="relay">Relay / Switch</option>
        <option value="plug">Plug S</option>
        <option value="dimmer">Dimmer</option>
        <option value="em">Energy Meter</option>
        <option value="sensor">Sensor</option>
      </select>
      <input type="text" name="location" placeholder="Ubicación (opcional)" class="w-full input">
      <div class="flex gap-2 pt-2">
        <button type="submit" class="flex-1 bg-blue-600 text-white py-2.5 rounded-xl font-semibold">Agregar</button>
        <button type="button" onclick="document.getElementById('modal-shelly').classList.add('hidden')"
          class="px-5 py-2.5 bg-gray-100 text-gray-700 rounded-xl font-medium">Cancelar</button>
      </div>
    </form>
  </div>
</div>

<style>.input { @apply px-4 py-2.5 border border-gray-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500; }</style>
<script>
function shellyStatus(id, btn) {
  fetch(`<?= BASE_URL ?>/api/shelly/${id}/estado`)
    .then(r => r.json())
    .then(d => {
      document.getElementById('shelly-status-' + id).textContent = JSON.stringify(d, null, 2);
    });
}

function shellyToggle(id, channel, turn) {
  const body = new URLSearchParams({ channel, turn });
  fetch(`<?= BASE_URL ?>/api/shelly/${id}/toggle`, { method: 'POST', body })
    .then(r => r.json())
    .then(d => {
      document.getElementById('shelly-status-' + id).textContent = 'Toggle: ' + JSON.stringify(d);
    });
}
</script>
<?php require APP_PATH . '/views/layout/bottom_nav.php'; ?>
<?php require APP_PATH . '/views/layout/footer.php'; ?>
