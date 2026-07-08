<?php
$pageTitle = 'Eventos – Colaborador – ' . APP_NAME;
require APP_PATH . '/views/layout/head.php';
?>
<?php require APP_PATH . '/views/layout/navbar.php'; ?>

<main class="max-w-6xl mx-auto px-4 py-8 mb-24">
  <h1 class="text-2xl font-bold text-gray-900 mb-6">🎉 Gestión de Eventos y Promociones</h1>

  <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    <!-- Create Global Event -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
      <h2 class="font-semibold text-gray-900 mb-4">📢 Crear evento público</h2>
      <form method="POST" action="<?= url('colaborador/eventos/crear') ?>" enctype="multipart/form-data" class="space-y-4">
        <input type="hidden" name="_csrf" value="<?= e($csrf) ?>">
        <div>
          <label class="label block text-sm font-medium text-gray-700 mb-1">Título *</label>
          <input type="text" name="title" required class="input w-full px-4 py-2.5 border border-gray-300 rounded-xl text-sm">
        </div>
        <div>
          <label class="label block text-sm font-medium text-gray-700 mb-1">Descripción</label>
          <textarea name="description" rows="3" class="input w-full px-4 py-2.5 border border-gray-300 rounded-xl text-sm"></textarea>
        </div>
        <div class="grid grid-cols-2 gap-4">
          <div>
            <label class="label block text-sm font-medium text-gray-700 mb-1">Fecha inicio</label>
            <input type="datetime-local" name="start_date" class="input w-full px-4 py-2.5 border border-gray-300 rounded-xl text-sm">
          </div>
          <div>
            <label class="label block text-sm font-medium text-gray-700 mb-1">Fecha fin</label>
            <input type="datetime-local" name="end_date" class="input w-full px-4 py-2.5 border border-gray-300 rounded-xl text-sm">
          </div>
        </div>
        <div>
          <label class="label block text-sm font-medium text-gray-700 mb-1">Imagen</label>
          <input type="file" name="image" accept="image/*" class="input w-full px-4 py-2 border border-gray-300 rounded-xl text-sm">
        </div>
        <div>
          <label class="label block text-sm font-medium text-gray-700 mb-1">Condiciones / Info adicional</label>
          <textarea name="conditions" rows="2" class="input w-full px-4 py-2.5 border border-gray-300 rounded-xl text-sm"></textarea>
        </div>
        <div>
          <label class="label block text-sm font-medium text-gray-700 mb-1">URL pública</label>
          <input type="url" name="public_url" class="input w-full px-4 py-2.5 border border-gray-300 rounded-xl text-sm" placeholder="https://...">
        </div>
        <button type="submit" class="w-full bg-blue-600 text-white py-3 rounded-xl font-medium hover:bg-blue-700 transition">
          Crear evento público
        </button>
      </form>
    </div>

    <!-- Pending Approvals -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
      <h2 class="font-semibold text-gray-900 mb-4">⏳ Promociones pendientes de autorización</h2>
      <?php if (empty($pendingPromos)): ?>
      <p class="text-sm text-gray-400 text-center py-4">No hay promociones pendientes</p>
      <?php else: ?>
      <div class="space-y-3 max-h-96 overflow-y-auto">
        <?php foreach ($pendingPromos as $p): ?>
        <div class="p-4 border border-yellow-200 rounded-xl bg-yellow-50">
          <div class="flex items-start justify-between gap-3">
            <div>
              <h3 class="font-semibold text-gray-900 text-sm"><?= e($p['title']) ?></h3>
              <p class="text-xs text-gray-500 mt-1"><?= e($p['business_name'] ?? 'Global') ?> • Creado por: <?= e($p['creator_name'] ?? '') ?></p>
            </div>
            <div class="flex gap-2 shrink-0">
              <button onclick="approvePromotion(<?= $p['id'] ?>)" class="text-xs px-3 py-1.5 bg-green-500 text-white rounded-lg hover:bg-green-600 transition">Aprobar</button>
              <button onclick="rejectPromotion(<?= $p['id'] ?>)" class="text-xs px-3 py-1.5 bg-red-500 text-white rounded-lg hover:bg-red-600 transition">Rechazar</button>
            </div>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
      <?php endif; ?>
    </div>
  </div>

  <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mt-6">
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
      <h2 class="font-semibold text-gray-900 mb-4">Solicitudes de eventos de prestadores</h2>
      <?php if (empty($pendingEvents)): ?>
      <p class="text-sm text-gray-400 text-center py-4">No hay eventos pendientes</p>
      <?php else: ?>
      <div class="space-y-3 max-h-96 overflow-y-auto">
        <?php foreach ($pendingEvents as $event): ?>
        <div class="p-4 border border-yellow-200 rounded-xl bg-yellow-50">
          <div class="flex items-start justify-between gap-3">
            <div>
              <h3 class="font-semibold text-gray-900 text-sm"><?= e($event['title']) ?></h3>
              <p class="text-xs text-gray-500 mt-1"><?= e($event['business_name'] ?? 'Sin negocio') ?> - Creado por: <?= e($event['creator_name'] ?? '') ?></p>
            </div>
            <button onclick="approveEventRequest(<?= (int)$event['id'] ?>)" class="text-xs px-3 py-1.5 bg-green-500 text-white rounded-lg hover:bg-green-600 transition">Aprobar</button>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
      <?php endif; ?>
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
      <h2 class="font-semibold text-gray-900 mb-4">Listos para publicar en chatbot</h2>
      <?php if (empty($pendingBotEvents)): ?>
      <p class="text-sm text-gray-400 text-center py-4">No hay eventos listos para chatbot</p>
      <?php else: ?>
      <div class="space-y-3 max-h-96 overflow-y-auto">
        <?php foreach ($pendingBotEvents as $event): ?>
        <div class="p-4 border border-indigo-200 rounded-xl bg-indigo-50">
          <div class="flex items-start justify-between gap-3">
            <div>
              <h3 class="font-semibold text-gray-900 text-sm"><?= e($event['title']) ?></h3>
              <p class="text-xs text-gray-500 mt-1"><?= e($event['business_name'] ?? 'Evento publico') ?></p>
            </div>
            <button onclick="authorizeEventBot(<?= (int)$event['id'] ?>)" class="text-xs px-3 py-1.5 bg-indigo-500 text-white rounded-lg hover:bg-indigo-600 transition">Autorizar bot</button>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
      <?php endif; ?>
    </div>
  </div>

  <!-- Active events -->
  <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 mt-6">
    <h2 class="font-semibold text-gray-900 mb-4">✅ Eventos y promociones activas</h2>
    <?php if (empty($activePromos)): ?>
    <p class="text-sm text-gray-400 text-center py-4">No hay eventos activos</p>
    <?php else: ?>
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
      <?php foreach ($activePromos as $p): ?>
      <div class="border border-gray-100 rounded-xl p-4">
        <h3 class="font-semibold text-gray-900 text-sm"><?= e($p['title']) ?></h3>
        <p class="text-xs text-gray-500 mt-1"><?= e($p['business_name'] ?? 'Global') ?></p>
        <p class="text-xs text-gray-400 mt-2"><?= e(mb_substr($p['description'] ?? '', 0, 100)) ?></p>
      </div>
      <?php endforeach; ?>
    </div>
    <?php endif; ?>
  </div>
</main>

<script>
const CSRF = '<?= e($csrf) ?>';
const BASE_URL = '<?= BASE_URL ?>';

function approvePromotion(id) {
  if (!confirm('¿Aprobar esta promoción?')) return;
  const body = new URLSearchParams({ _csrf: CSRF });
  fetch(`${BASE_URL}/colaborador/eventos/${id}/aprobar`, {
    method: 'POST',
    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
    body: body.toString(),
  })
  .then(r => r.json())
  .then(d => { if (d.ok) location.reload(); });
}

function rejectPromotion(id) {
  if (!confirm('¿Rechazar esta promoción?')) return;
  const body = new URLSearchParams({ _csrf: CSRF, status: 'inactive' });
  fetch(`${BASE_URL}/admin/promociones/${id}/toggle`, {
    method: 'POST',
    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
    body: body.toString(),
  })
  .then(r => r.json())
  .then(d => { if (d.ok) location.reload(); });
}

function approveEventRequest(id) {
  if (!confirm('Aprobar este evento de prestador?')) return;
  const body = new URLSearchParams({ _csrf: CSRF });
  fetch(`${BASE_URL}/admin/eventos/${id}/aprobar`, {
    method: 'POST',
    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
    body: body.toString(),
  })
  .then(r => r.json())
  .then(d => { if (d.ok) location.reload(); else alert(d.error || 'Error al aprobar'); });
}

function authorizeEventBot(id) {
  if (!confirm('Publicar este evento en el chatbot?')) return;
  const body = new URLSearchParams({ _csrf: CSRF });
  fetch(`${BASE_URL}/admin/eventos/${id}/autorizar-bot`, {
    method: 'POST',
    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
    body: body.toString(),
  })
  .then(r => r.json())
  .then(d => { if (d.ok) location.reload(); else alert(d.error || 'Error al autorizar'); });
}
</script>
<style>
  .label { @apply block text-sm font-medium text-gray-700 mb-1; }
  .input  { @apply w-full px-4 py-2.5 border border-gray-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition; }
</style>
<?php require APP_PATH . '/views/layout/footer.php'; ?>
