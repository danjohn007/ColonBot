<?php
$pageTitle = 'Eventos – ' . APP_NAME;
require APP_PATH . '/views/layout/head.php';
?>
<?php require APP_PATH . '/views/layout/navbar.php'; ?>

<main class="max-w-6xl mx-auto px-4 py-8 mb-24">
  <div class="flex items-center justify-between mb-6">
    <h1 class="text-2xl font-bold text-gray-900">🎉 Eventos</h1>
    <button onclick="openCreateModal()" class="bg-blue-600 text-white px-4 py-2 rounded-xl text-sm font-medium hover:bg-blue-700 transition">
      + Nuevo Evento
    </button>
  </div>

  <!-- Events list -->
  <div id="events-list" class="space-y-4">
    <?php if (empty($events)): ?>
    <div class="text-center py-16 bg-white rounded-2xl shadow-sm">
      <p class="text-gray-400 text-lg">No hay eventos creados.</p>
      <button onclick="openCreateModal()" class="mt-4 bg-blue-600 text-white px-6 py-3 rounded-xl font-medium hover:bg-blue-700 transition">
        Crear primer Evento
      </button>
    </div>
    <?php else: ?>
    <?php foreach ($events as $p): ?>
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
      <div class="flex items-start justify-between gap-4">
        <div class="flex-1">
          <div class="flex items-center gap-2 mb-2">
            <h3 class="font-bold text-gray-900"><?= e($p['title']) ?></h3>
            <span class="text-xs px-2 py-0.5 rounded-full font-medium <?= $p['status'] === 'active' ? 'bg-green-50 text-green-700' : ($p['status'] === 'pending' ? 'bg-yellow-50 text-yellow-700' : ($p['status'] === 'approved' ? 'bg-blue-50 text-blue-700' : 'bg-gray-50 text-gray-500')) ?>">
              <?= $p['status'] ?>
            </span>
            <?php if ($p['event_type'] === 'publico'): ?>
            <span class="text-xs px-2 py-0.5 rounded-full bg-purple-50 text-purple-700">Público</span>
            <?php else: ?>
            <span class="text-xs px-2 py-0.5 rounded-full bg-orange-50 text-orange-700">Privado</span>
            <?php endif; ?>
            <?php if ($p['bot_authorized']): ?>
            <span class="text-xs px-2 py-0.5 rounded-full bg-green-50 text-green-700">🤖 Bot OK</span>
            <?php endif; ?>
          </div>
          <p class="text-sm text-gray-500 mb-2"><?= nl2br(e(mb_substr($p['description'] ?? '', 0, 200))) ?></p>
          <div class="flex flex-wrap gap-3 text-xs text-gray-400">
            <?php if ($p['price']): ?><span>💰 Precio: $<?= number_format((float)$p['price'], 2) ?></span><?php endif; ?>
            <?php if ($p['presale_price']): ?><span>🏷️ Preventa: $<?= number_format((float)$p['presale_price'], 2) ?></span><?php endif; ?>
            <?php if ($p['capacity']): ?><span>👥 Aforo: <?= (int)$p['capacity'] ?></span><?php endif; ?>
            <?php if ($p['location']): ?><span>📍 <?= e($p['location']) ?></span><?php endif; ?>
            <?php if ($p['validity']): ?><span>⏳ Vigencia: <?= e($p['validity']) ?></span><?php endif; ?>
            <?php if ($p['start_date']): ?><span>📅 Inicio: <?= date('d/m/Y', strtotime($p['start_date'])) ?></span><?php endif; ?>
            <?php if ($p['end_date']): ?><span>⏰ Fin: <?= date('d/m/Y', strtotime($p['end_date'])) ?></span><?php endif; ?>
            <?php if ($p['public_url']): ?>
            <span>🔗 <a href="<?= e($p['public_url']) ?>" target="_blank" class="text-blue-600 hover:underline">URL pública</a></span>
            <?php endif; ?>
          </div>
          <?php if ($p['image']): ?>
          <img src="<?= imageUrl($p['image']) ?>" class="mt-3 h-20 w-20 object-cover rounded-lg">
          <?php endif; ?>
        </div>
        <div class="flex flex-col gap-2 shrink-0">
          <div class="flex flex-wrap gap-1">
            <?php if (in_array($user['role'], ['superadmin', 'colaborador_admin'])): ?>
              <?php if ($p['status'] === 'pending'): ?>
              <button onclick="approveEvent(<?= $p['id'] ?>)" class="text-xs px-3 py-1.5 bg-purple-50 text-purple-700 rounded-lg hover:bg-purple-100 transition">✅ Aprobar</button>
              <?php endif; ?>
              <?php if ($p['status'] === 'approved' && !$p['bot_authorized']): ?>
              <button onclick="authorizeBot(<?= $p['id'] ?>)" class="text-xs px-3 py-1.5 bg-indigo-50 text-indigo-700 rounded-lg hover:bg-indigo-100 transition">🤖 Publicar en Bot</button>
              <?php endif; ?>
              <!-- 1-click authorization for chatbot publication -->
              <?php if ($p['status'] === 'approved' && !$p['bot_authorized']): ?>
              <button onclick="authorizeBotPublish(<?= $p['id'] ?>)" class="text-xs px-3 py-1.5 bg-green-50 text-green-700 rounded-lg hover:bg-green-100 transition">📢 1 Click - Publicar</button>
              <?php endif; ?>
            <?php endif; ?>
            <button onclick="editEvent(<?= htmlspecialchars(json_encode($p), ENT_QUOTES) ?>)" class="text-xs px-3 py-1.5 bg-blue-50 text-blue-700 rounded-lg hover:bg-blue-100 transition">Editar</button>
            <button onclick="toggleStatus(<?= $p['id'] ?>, '<?= $p['status'] === 'active' ? 'inactive' : 'active' ?>')" class="text-xs px-3 py-1.5 <?= $p['status'] === 'active' ? 'bg-red-50 text-red-700 hover:bg-red-100' : 'bg-green-50 text-green-700 hover:bg-green-100' ?> rounded-lg transition">
              <?= $p['status'] === 'active' ? 'Desactivar' : 'Activar' ?>
            </button>
            <!-- Notify visitors button -->
            <?php if ($p['status'] === 'active' && $p['public_url']): ?>
            <button onclick="notifyVisitors(<?= $p['id'] ?>, '<?= e($p['title']) ?>')" class="text-xs px-3 py-1.5 bg-orange-50 text-orange-700 rounded-lg hover:bg-orange-100 transition">📣 Notificar Visitantes</button>
            <?php endif; ?>
          </div>
        </div>
      </div>
    </div>
    <?php endforeach; ?>
    <?php endif; ?>
  </div>
</main>

<!-- Create/Edit Modal: "Crear evento" -->
<div id="event-modal" class="fixed inset-0 z-50 hidden bg-black bg-opacity-40 flex items-center justify-center px-4">
  <div class="bg-white rounded-2xl shadow-2xl w-full max-w-2xl max-h-[90vh] overflow-y-auto p-6 relative">
    <button onclick="closeModal()" class="absolute top-4 right-4 text-gray-400 hover:text-gray-600">✕</button>
    <h2 class="text-lg font-bold text-gray-900 mb-4" id="modal-title">Crear evento</h2>
    <form onsubmit="saveEvent(event)" enctype="multipart/form-data" class="space-y-4">
      <input type="hidden" id="event-id" value="">
      <input type="hidden" id="event-business-id" name="business_id" value="<?= $businesses[0]['id'] ?? '' ?>">

      <div class="grid grid-cols-2 gap-4">
        <div class="col-span-2">
          <label class="label block text-sm font-medium text-gray-700 mb-1">Título *</label>
          <input type="text" id="event-title" required class="input w-full px-4 py-2.5 border border-gray-300 rounded-xl text-sm" placeholder="Nombre del evento">
        </div>
        <?php if (count($businesses) > 1): ?>
        <div class="col-span-2">
          <label class="label block text-sm font-medium text-gray-700 mb-1">Negocio</label>
          <select id="event-business-select" class="input w-full px-4 py-2.5 border border-gray-300 rounded-xl text-sm">
            <option value="">-- Evento público (sin negocio) --</option>
            <?php foreach ($businesses as $b): ?>
            <option value="<?= $b['id'] ?>"><?= e($b['name']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <?php endif; ?>
        <div class="col-span-2">
          <label class="label block text-sm font-medium text-gray-700 mb-1">Descripción del evento</label>
          <textarea id="event-description" rows="3" class="input w-full px-4 py-2.5 border border-gray-300 rounded-xl text-sm" placeholder="Descripción del evento..."></textarea>
        </div>
        <div>
          <label class="label block text-sm font-medium text-gray-700 mb-1">Imagen</label>
          <input type="file" id="event-image" accept="image/*" class="input w-full px-4 py-2 border border-gray-300 rounded-xl text-sm">
        </div>
        <div>
          <label class="label block text-sm font-medium text-gray-700 mb-1">Precio</label>
          <input type="number" id="event-price" step="0.01" min="0" class="input w-full px-4 py-2.5 border border-gray-300 rounded-xl text-sm" placeholder="0.00">
        </div>
        <div>
          <label class="label block text-sm font-medium text-gray-700 mb-1">Precio de preventa</label>
          <input type="number" id="event-presale-price" step="0.01" min="0" class="input w-full px-4 py-2.5 border border-gray-300 rounded-xl text-sm" placeholder="0.00">
        </div>
        <div>
          <label class="label block text-sm font-medium text-gray-700 mb-1">Aforo de la sede</label>
          <input type="number" id="event-capacity" min="0" class="input w-full px-4 py-2.5 border border-gray-300 rounded-xl text-sm" placeholder="Ej: 100">
        </div>
        <div class="col-span-2">
          <label class="label block text-sm font-medium text-gray-700 mb-1">Ubicación del evento</label>
          <input type="text" id="event-location" class="input w-full px-4 py-2.5 border border-gray-300 rounded-xl text-sm" placeholder="Dirección o lugar del evento">
        </div>
        <div>
          <label class="label block text-sm font-medium text-gray-700 mb-1">Vigencia</label>
          <input type="text" id="event-validity" class="input w-full px-4 py-2.5 border border-gray-300 rounded-xl text-sm" placeholder="Ej: 30 días">
        </div>
        <div>
          <label class="label block text-sm font-medium text-gray-700 mb-1">Fecha inicio</label>
          <input type="datetime-local" id="event-start" class="input w-full px-4 py-2.5 border border-gray-300 rounded-xl text-sm">
        </div>
        <div>
          <label class="label block text-sm font-medium text-gray-700 mb-1">Fecha fin</label>
          <input type="datetime-local" id="event-end" class="input w-full px-4 py-2.5 border border-gray-300 rounded-xl text-sm">
        </div>
        <div>
          <label class="label block text-sm font-medium text-gray-700 mb-1">Inicio preventa</label>
          <input type="datetime-local" id="event-presale-start" class="input w-full px-4 py-2.5 border border-gray-300 rounded-xl text-sm">
        </div>
        <div>
          <label class="label block text-sm font-medium text-gray-700 mb-1">Fin preventa</label>
          <input type="datetime-local" id="event-presale-end" class="input w-full px-4 py-2.5 border border-gray-300 rounded-xl text-sm">
        </div>
        <div class="col-span-2">
          <label class="label block text-sm font-medium text-gray-700 mb-1">Condiciones / Restricciones</label>
          <textarea id="event-conditions" rows="2" class="input w-full px-4 py-2.5 border border-gray-300 rounded-xl text-sm" placeholder="Condiciones del evento..."></textarea>
        </div>
      </div>
      <button type="submit" class="w-full bg-blue-600 text-white py-3 rounded-xl font-medium hover:bg-blue-700 transition">
        Guardar
      </button>
    </form>
  </div>
</div>

<script>
const CSRF = '<?= e($csrf) ?>';
const BASE_URL = '<?= BASE_URL ?>';

function openCreateModal() {
  document.getElementById('modal-title').textContent = 'Crear evento';
  document.getElementById('event-id').value = '';
  document.getElementById('event-title').value = '';
  document.getElementById('event-description').value = '';
  document.getElementById('event-price').value = '';
  document.getElementById('event-presale-price').value = '';
  document.getElementById('event-capacity').value = '';
  document.getElementById('event-location').value = '';
  document.getElementById('event-validity').value = '';
  document.getElementById('event-start').value = '';
  document.getElementById('event-end').value = '';
  document.getElementById('event-presale-start').value = '';
  document.getElementById('event-presale-end').value = '';
  document.getElementById('event-conditions').value = '';
  document.getElementById('event-image').value = '';
  document.getElementById('event-modal').classList.remove('hidden');
}

function editEvent(p) {
  document.getElementById('modal-title').textContent = 'Editar Evento';
  document.getElementById('event-id').value = p.id;
  document.getElementById('event-title').value = p.title;
  document.getElementById('event-description').value = p.description || '';
  document.getElementById('event-price').value = p.price || '';
  document.getElementById('event-presale-price').value = p.presale_price || '';
  document.getElementById('event-capacity').value = p.capacity || '';
  document.getElementById('event-location').value = p.location || '';
  document.getElementById('event-validity').value = p.validity || '';
  document.getElementById('event-start').value = p.start_date ? p.start_date.replace(' ', 'T').substring(0, 16) : '';
  document.getElementById('event-end').value = p.end_date ? p.end_date.replace(' ', 'T').substring(0, 16) : '';
  document.getElementById('event-presale-start').value = p.presale_start ? p.presale_start.replace(' ', 'T').substring(0, 16) : '';
  document.getElementById('event-presale-end').value = p.presale_end ? p.presale_end.replace(' ', 'T').substring(0, 16) : '';
  document.getElementById('event-conditions').value = p.conditions || '';
  document.getElementById('event-modal').classList.remove('hidden');
}

function closeModal() {
  document.getElementById('event-modal').classList.add('hidden');
}

function saveEvent(e) {
  e.preventDefault();
  const id = document.getElementById('event-id').value;
  const isEdit = !!id;

  const fd = new FormData();
  fd.append('_csrf', CSRF);
  fd.append('business_id', document.getElementById('event-business-id')?.value || document.getElementById('event-business-select')?.value || '');
  fd.append('title', document.getElementById('event-title').value.trim());
  fd.append('description', document.getElementById('event-description').value.trim());
  fd.append('price', document.getElementById('event-price').value);
  fd.append('presale_price', document.getElementById('event-presale-price').value);
  fd.append('capacity', document.getElementById('event-capacity').value);
  fd.append('location', document.getElementById('event-location').value.trim());
  fd.append('validity', document.getElementById('event-validity').value.trim());
  fd.append('start_date', document.getElementById('event-start').value);
  fd.append('end_date', document.getElementById('event-end').value);
  fd.append('presale_start', document.getElementById('event-presale-start').value);
  fd.append('presale_end', document.getElementById('event-presale-end').value);
  fd.append('conditions', document.getElementById('event-conditions').value.trim());

  const img = document.getElementById('event-image');
  if (img.files[0]) fd.append('image', img.files[0]);

  const url = isEdit ? `${BASE_URL}/admin/eventos/${id}/editar` : `${BASE_URL}/admin/eventos/crear`;
  fetch(url, { method: 'POST', body: fd })
    .then(r => r.json())
    .then(d => {
      if (d.ok) {
        closeModal();
        location.reload();
      } else {
        alert(d.error || 'Error al guardar');
      }
    });
}

function toggleStatus(id, status) {
  if (!confirm('¿Cambiar estado?')) return;
  const body = new URLSearchParams({ _csrf: CSRF, status });
  fetch(`${BASE_URL}/admin/eventos/${id}/toggle`, {
    method: 'POST',
    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
    body: body.toString(),
  })
  .then(r => r.json())
  .then(d => { if (d.ok) location.reload(); });
}

function approveEvent(id) {
  if (!confirm('¿Aprobar este evento?')) return;
  const body = new URLSearchParams({ _csrf: CSRF });
  fetch(`${BASE_URL}/admin/eventos/${id}/aprobar`, {
    method: 'POST',
    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
    body: body.toString(),
  })
  .then(r => r.json())
  .then(d => { if (d.ok) location.reload(); });
}

function authorizeBot(id) {
  if (!confirm('¿Autorizar la publicación de este evento en el chatbot? Esta acción enviará el evento al chatbot.')) return;
  const body = new URLSearchParams({ _csrf: CSRF });
  fetch(`${BASE_URL}/admin/eventos/${id}/autorizar-bot`, {
    method: 'POST',
    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
    body: body.toString(),
  })
  .then(r => r.json())
  .then(d => { if (d.ok) location.reload(); });
}

/** 1-click authorization + publish to chatbot */
function authorizeBotPublish(id) {
  if (!confirm('¿Autorizar y publicar este evento en el chatbot con 1 clic?')) return;
  const body = new URLSearchParams({ _csrf: CSRF });
  fetch(`${BASE_URL}/admin/eventos/${id}/autorizar-bot`, {
    method: 'POST',
    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
    body: body.toString(),
  })
  .then(r => r.json())
  .then(d => {
    if (d.ok) {
      alert('✅ Evento autorizado y publicado en el chatbot exitosamente.');
      location.reload();
    } else {
      alert(d.error || 'Error al autorizar');
    }
  });
}

/** Notify all visitor users about a new event */
function notifyVisitors(eventId, title) {
  if (!confirm(`¿Notificar a todos los visitantes sobre el evento: "${title}"?`)) return;
  const body = new URLSearchParams({ _csrf: CSRF, event_id: eventId });
  fetch(`${BASE_URL}/admin/eventos/${eventId}/notificar`, {
    method: 'POST',
    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
    body: body.toString(),
  })
  .then(r => r.json())
  .then(d => {
    if (d.ok) {
      alert(`✅ Notificación enviada a ${d.count || 0} visitante(s).`);
    } else {
      alert(d.error || 'Error al notificar');
    }
  });
}
</script>

<style>
  .label { @apply block text-sm font-medium text-gray-700 mb-1; }
  .input  { @apply w-full px-4 py-2.5 border border-gray-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition; }
</style>
<?php require APP_PATH . '/views/layout/bottom_nav.php'; ?>
<?php require APP_PATH . '/views/layout/footer.php'; ?>
