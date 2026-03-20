<?php
$pageTitle = 'Dispositivos HikVision';
require APP_PATH . '/views/layout/head.php';
?>
<?php require APP_PATH . '/views/layout/navbar.php'; ?>
<main class="max-w-5xl mx-auto px-4 py-8 mb-20">
  <div class="flex items-center gap-3 mb-6">
    <a href="<?= url('configuraciones') ?>" class="text-gray-400 hover:text-blue-600">← Config</a>
    <h1 class="text-2xl font-bold text-gray-900">📹 Dispositivos HikVision</h1>
  </div>

  <button onclick="document.getElementById('modal-hik').classList.remove('hidden')"
    class="mb-6 bg-blue-600 text-white px-5 py-2.5 rounded-xl font-semibold hover:bg-blue-700 transition text-sm">
    + Agregar Dispositivo
  </button>

  <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
    <?php foreach ($devices as $d): ?>
    <div class="bg-white rounded-2xl shadow-sm p-5">
      <div class="flex items-start justify-between mb-3">
        <div>
          <h3 class="font-semibold text-gray-900"><?= e($d['name']) ?></h3>
          <p class="text-xs text-gray-500 mt-0.5"><?= e($d['location'] ?? 'Sin ubicación') ?></p>
        </div>
        <span class="px-2 py-0.5 text-xs rounded-full <?= $d['active'] ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500' ?>">
          <?= $d['active'] ? '🟢 Activo' : 'Inactivo' ?>
        </span>
      </div>
      <div class="grid grid-cols-2 gap-y-1 text-xs text-gray-600 mb-3">
        <span class="font-medium">Tipo:</span><span><?= e($d['type']) ?></span>
        <span class="font-medium">IP:</span><span class="font-mono"><?= e($d['ip']) ?>:<?= $d['port'] ?></span>
        <span class="font-medium">Usuario:</span><span><?= e($d['username']) ?></span>
      </div>
      <?php if ($d['stream_url']): ?>
      <a href="<?= e($d['stream_url']) ?>" target="_blank"
        class="text-xs text-blue-600 hover:underline block mb-3">🎥 Ver stream</a>
      <?php endif; ?>
      <button onclick="testHik(<?= $d['id'] ?>)"
        class="text-xs bg-teal-50 text-teal-700 px-3 py-1.5 rounded-lg hover:bg-teal-100 transition mr-2">
        🔌 Probar conexión
      </button>
      <form method="POST" action="<?= url('configuraciones/hikvision/' . $d['id'] . '/eliminar') ?>" class="inline"
        onsubmit="return confirm('¿Eliminar dispositivo?')">
        <input type="hidden" name="_csrf" value="<?= e($csrf) ?>">
        <button type="submit" class="text-xs bg-red-50 text-red-600 px-3 py-1.5 rounded-lg hover:bg-red-100 transition">Eliminar</button>
      </form>
    </div>
    <?php endforeach; ?>
    <?php if (empty($devices)): ?>
    <div class="col-span-2 bg-white rounded-2xl shadow-sm p-12 text-center text-gray-400">
      No hay dispositivos HikVision configurados.
    </div>
    <?php endif; ?>
  </div>
</main>

<!-- Modal -->
<div id="modal-hik" class="hidden fixed inset-0 bg-black/40 z-50 flex items-center justify-center p-4">
  <div class="bg-white rounded-2xl shadow-xl w-full max-w-md p-6 max-h-screen overflow-y-auto">
    <h2 class="text-lg font-bold mb-4">Agregar Cámara/NVR HikVision</h2>
    <form method="POST" action="<?= url('configuraciones/hikvision/crear') ?>" class="space-y-3">
      <input type="hidden" name="_csrf" value="<?= e($csrf) ?>">
      <input type="text" name="name" required placeholder="Nombre del dispositivo" class="w-full input">
      <div class="grid grid-cols-3 gap-2">
        <input type="text" name="ip" required placeholder="IP" class="col-span-2 input" pattern="^[\d\.]+$">
        <input type="number" name="port" placeholder="Puerto" value="80" class="input">
      </div>
      <div class="grid grid-cols-2 gap-2">
        <input type="text" name="username" value="admin" placeholder="Usuario" class="input">
        <input type="password" name="password" required placeholder="Contraseña" class="input">
      </div>
      <select name="type" class="w-full input">
        <option value="camera">Cámara IP</option>
        <option value="nvr">NVR</option>
        <option value="dvr">DVR</option>
      </select>
      <input type="url" name="stream_url" placeholder="URL Stream (opcional)" class="w-full input font-mono text-sm">
      <input type="text" name="location" placeholder="Ubicación física (opcional)" class="w-full input">
      <div class="flex gap-2 pt-2">
        <button type="submit" class="flex-1 bg-blue-600 text-white py-2.5 rounded-xl font-semibold">Agregar</button>
        <button type="button" onclick="document.getElementById('modal-hik').classList.add('hidden')"
          class="px-5 py-2.5 bg-gray-100 text-gray-700 rounded-xl font-medium">Cancelar</button>
      </div>
    </form>
  </div>
</div>

<style>.input { @apply px-4 py-2.5 border border-gray-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500; }</style>
<script>
function testHik(id) {
  fetch(`<?= BASE_URL ?>/api/hikvision/${id}/stream`)
    .then(r => r.json())
    .then(d => alert(d.stream_url ? '✅ Stream URL: ' + d.stream_url : '❌ Error: ' + JSON.stringify(d)));
}
</script>
<?php require APP_PATH . '/views/layout/bottom_nav.php'; ?>
<?php require APP_PATH . '/views/layout/footer.php'; ?>
