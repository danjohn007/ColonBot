<?php
$pageTitle = 'Promociones – ' . APP_NAME;
require APP_PATH . '/views/layout/head.php';
?>
<?php require APP_PATH . '/views/layout/navbar.php'; ?>

<main class="max-w-6xl mx-auto px-4 py-8 mb-24">
  <div class="flex items-center justify-between mb-6">
    <h1 class="text-2xl font-bold text-gray-900">🎉 Promociones y Eventos</h1>
    <button onclick="openCreateModal()" class="bg-blue-600 text-white px-4 py-2 rounded-xl text-sm font-medium hover:bg-blue-700 transition">
      + Nueva Promoción/Evento
    </button>
  </div>

  <!-- Promotions list -->
  <div id="promotions-list" class="space-y-4">
    <?php if (empty($promotions)): ?>
    <div class="text-center py-16 bg-white rounded-2xl shadow-sm">
      <p class="text-gray-400 text-lg">No hay promociones creadas.</p>
      <button onclick="openCreateModal()" class="mt-4 bg-blue-600 text-white px-6 py-3 rounded-xl font-medium hover:bg-blue-700 transition">
        Crear primera Promoción/Evento
      </button>
    </div>
    <?php else: ?>
    <?php foreach ($promotions as $p): ?>
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
      <div class="flex items-start justify-between gap-4">
        <div class="flex-1">
          <div class="flex items-center gap-2 mb-2">
            <h3 class="font-bold text-gray-900"><?= e($p['title']) ?></h3>
            <span class="text-xs px-2 py-0.5 rounded-full font-medium <?= $p['status'] === 'active' ? 'bg-green-50 text-green-700' : ($p['status'] === 'pending' ? 'bg-yellow-50 text-yellow-700' : 'bg-gray-50 text-gray-500') ?>">
              <?= $p['status'] ?>
            </span>
            <span class="text-xs px-2 py-0.5 rounded-full font-medium bg-blue-50 text-blue-700"><?= $p['type'] ?></span>
          </div>
          <p class="text-sm text-gray-500 mb-2"><?= nl2br(e(mb_substr($p['description'] ?? '', 0, 200))) ?></p>
          <div class="flex flex-wrap gap-3 text-xs text-gray-400">
            <?php if ($p['price']): ?><span>💰 Precio: $<?= number_format((float)$p['price'], 2) ?></span><?php endif; ?>
            <?php if ($p['presale_price']): ?><span>🏷️ Preventa: $<?= number_format((float)$p['presale_price'], 2) ?></span><?php endif; ?>
            <?php if ($p['start_date']): ?><span>📅 Inicio: <?= date('d/m/Y', strtotime($p['start_date'])) ?></span><?php endif; ?>
            <?php if ($p['end_date']): ?><span>⏰ Fin: <?= date('d/m/Y', strtotime($p['end_date'])) ?></span><?php endif; ?>
            <span>🎯 Segmento: <?= e($p['target_segment']) ?></span>
          </div>
        </div>
        <div class="flex flex-col gap-2 shrink-0">
          <button onclick="editPromotion(<?= htmlspecialchars(json_encode($p), ENT_QUOTES) ?>)" class="text-xs px-3 py-1.5 bg-blue-50 text-blue-700 rounded-lg hover:bg-blue-100 transition">Editar</button>
          <button onclick="toggleStatus(<?= $p['id'] ?>, '<?= $p['status'] === 'active' ? 'inactive' : 'active' ?>')" class="text-xs px-3 py-1.5 <?= $p['status'] === 'active' ? 'bg-red-50 text-red-700 hover:bg-red-100' : 'bg-green-50 text-green-700 hover:bg-green-100' ?> rounded-lg transition">
            <?= $p['status'] === 'active' ? 'Desactivar' : 'Activar' ?>
          </button>
          <button onclick="sendPromotion(<?= $p['id'] ?>)" class="text-xs px-3 py-1.5 bg-purple-50 text-purple-700 rounded-lg hover:bg-purple-100 transition">Enviar</button>
        </div>
      </div>
      <?php if ($p['image']): ?>
      <img src="<?= imageUrl($p['image']) ?>" class="mt-3 h-20 w-20 object-cover rounded-lg">
      <?php endif; ?>
    </div>
    <?php endforeach; ?>
    <?php endif; ?>
  </div>
</main>

<!-- Create/Edit Modal -->
<div id="promo-modal" class="fixed inset-0 z-50 hidden bg-black bg-opacity-40 flex items-center justify-center px-4">
  <div class="bg-white rounded-2xl shadow-2xl w-full max-w-2xl max-h-[90vh] overflow-y-auto p-6 relative">
    <button onclick="closeModal()" class="absolute top-4 right-4 text-gray-400 hover:text-gray-600">✕</button>
    <h2 class="text-lg font-bold text-gray-900 mb-4" id="modal-title">Nueva promoción</h2>
    <form onsubmit="savePromotion(event)" enctype="multipart/form-data" class="space-y-4">
      <input type="hidden" id="promo-id" value="">
      <input type="hidden" id="promo-business-id" name="business_id" value="<?= $businesses[0]['id'] ?? '' ?>">

      <div class="grid grid-cols-2 gap-4">
        <div class="col-span-2">
          <label class="label block text-sm font-medium text-gray-700 mb-1">Título *</label>
          <input type="text" id="promo-title" required class="input w-full px-4 py-2.5 border border-gray-300 rounded-xl text-sm">
        </div>
        <?php if (count($businesses) > 1): ?>
        <div class="col-span-2">
          <label class="label block text-sm font-medium text-gray-700 mb-1">Negocio</label>
          <select id="promo-business-select" class="input w-full px-4 py-2.5 border border-gray-300 rounded-xl text-sm">
            <?php foreach ($businesses as $b): ?>
            <option value="<?= $b['id'] ?>"><?= e($b['name']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <?php endif; ?>
        <div class="col-span-2">
          <label class="label block text-sm font-medium text-gray-700 mb-1">Descripción</label>
          <textarea id="promo-description" rows="3" class="input w-full px-4 py-2.5 border border-gray-300 rounded-xl text-sm"></textarea>
        </div>
        <div>
          <label class="label block text-sm font-medium text-gray-700 mb-1">Tipo</label>
          <select id="promo-type" class="input w-full px-4 py-2.5 border border-gray-300 rounded-xl text-sm">
            <option value="promocion">Promoción</option>
            <option value="evento">Evento</option>
          </select>
        </div>
        <div>
          <label class="label block text-sm font-medium text-gray-700 mb-1">Imagen</label>
          <input type="file" id="promo-image" accept="image/*" class="input w-full px-4 py-2 border border-gray-300 rounded-xl text-sm">
        </div>
        <div>
          <label class="label block text-sm font-medium text-gray-700 mb-1">Precio de lista</label>
          <input type="number" id="promo-price" step="0.01" min="0" class="input w-full px-4 py-2.5 border border-gray-300 rounded-xl text-sm">
        </div>
        <div>
          <label class="label block text-sm font-medium text-gray-700 mb-1">Precio preventa</label>
          <input type="number" id="promo-presale" step="0.01" min="0" class="input w-full px-4 py-2.5 border border-gray-300 rounded-xl text-sm">
        </div>
        <div>
          <label class="label block text-sm font-medium text-gray-700 mb-1">Fecha inicio</label>
          <input type="datetime-local" id="promo-start" class="input w-full px-4 py-2.5 border border-gray-300 rounded-xl text-sm">
        </div>
        <div>
          <label class="label block text-sm font-medium text-gray-700 mb-1">Fecha fin</label>
          <input type="datetime-local" id="promo-end" class="input w-full px-4 py-2.5 border border-gray-300 rounded-xl text-sm">
        </div>
        <div class="col-span-2">
          <label class="label block text-sm font-medium text-gray-700 mb-1">Segmento objetivo</label>
          <div class="grid grid-cols-2 gap-2">
            <label class="flex items-center gap-2 text-sm"><input type="checkbox" name="target_segment[]" value="prospectos_sin_historial" class="target-checkbox"> Prospectos sin historial</label>
            <label class="flex items-center gap-2 text-sm"><input type="checkbox" name="target_segment[]" value="prospectos_recurrentes" class="target-checkbox"> Prospectos recurrentes</label>
            <label class="flex items-center gap-2 text-sm"><input type="checkbox" name="target_segment[]" value="clientes" class="target-checkbox"> Clientes</label>
            <label class="flex items-center gap-2 text-sm"><input type="checkbox" name="target_segment[]" value="clientes_frecuentes" class="target-checkbox"> Clientes frecuentes</label>
            <label class="flex items-center gap-2 text-sm col-span-2 border-t pt-2"><input type="checkbox" id="target-all" value="todos" class="target-checkbox"> Todos</label>
          </div>
        </div>
        <div class="col-span-2">
          <label class="label block text-sm font-medium text-gray-700 mb-1">Condiciones</label>
          <textarea id="promo-conditions" rows="2" class="input w-full px-4 py-2.5 border border-gray-300 rounded-xl text-sm"></textarea>
        </div>
        <div class="col-span-2">
          <label class="label block text-sm font-medium text-gray-700 mb-1">URL pública (opcional)</label>
          <input type="url" id="promo-url" class="input w-full px-4 py-2.5 border border-gray-300 rounded-xl text-sm" placeholder="https://...">
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
    document.getElementById('modal-title').textContent = 'Nueva Promoción/Evento';
  document.getElementById('promo-id').value = '';
  document.getElementById('promo-title').value = '';
  document.getElementById('promo-description').value = '';
  document.getElementById('promo-price').value = '';
  document.getElementById('promo-presale').value = '';
  document.getElementById('promo-start').value = '';
  document.getElementById('promo-end').value = '';
  document.getElementById('promo-conditions').value = '';
  document.getElementById('promo-url').value = '';
  document.getElementById('promo-image').value = '';
  document.getElementById('promo-type').value = 'promocion';
  document.querySelectorAll('.target-checkbox').forEach(cb => cb.checked = false);
  document.getElementById('promo-modal').classList.remove('hidden');
}

function editPromotion(p) {
    document.getElementById('modal-title').textContent = 'Editar Promoción/Evento';
  document.getElementById('promo-id').value = p.id;
  document.getElementById('promo-title').value = p.title;
  document.getElementById('promo-description').value = p.description || '';
  document.getElementById('promo-price').value = p.price || '';
  document.getElementById('promo-presale').value = p.presale_price || '';
  document.getElementById('promo-start').value = p.start_date ? p.start_date.replace(' ', 'T').substring(0, 16) : '';
  document.getElementById('promo-end').value = p.end_date ? p.end_date.replace(' ', 'T').substring(0, 16) : '';
  document.getElementById('promo-conditions').value = p.conditions || '';
  document.getElementById('promo-url').value = p.public_url || '';
  document.getElementById('promo-type').value = p.type;
  document.querySelectorAll('.target-checkbox').forEach(cb => {
    cb.checked = p.target_segment && p.target_segment.split(',').includes(cb.value);
  });
  document.getElementById('promo-modal').classList.remove('hidden');
}

function closeModal() {
  document.getElementById('promo-modal').classList.add('hidden');
}

function savePromotion(e) {
  e.preventDefault();
  const id = document.getElementById('promo-id').value;
  const isEdit = !!id;

  const fd = new FormData();
  fd.append('_csrf', CSRF);
  fd.append('business_id', document.getElementById('promo-business-id')?.value || document.getElementById('promo-business-select')?.value || '');
  fd.append('title', document.getElementById('promo-title').value.trim());
  fd.append('description', document.getElementById('promo-description').value.trim());
  fd.append('price', document.getElementById('promo-price').value);
  fd.append('presale_price', document.getElementById('promo-presale').value);
  fd.append('start_date', document.getElementById('promo-start').value);
  fd.append('end_date', document.getElementById('promo-end').value);
  fd.append('conditions', document.getElementById('promo-conditions').value.trim());
  fd.append('public_url', document.getElementById('promo-url').value.trim());
  fd.append('type', document.getElementById('promo-type').value);

  const targets = Array.from(document.querySelectorAll('.target-checkbox:checked')).map(cb => cb.value);
  targets.forEach(t => fd.append('target_segment[]', t));

  const img = document.getElementById('promo-image');
  if (img.files[0]) fd.append('image', img.files[0]);

  const url = isEdit ? `${BASE_URL}/admin/promociones/${id}/editar` : `${BASE_URL}/admin/promociones/crear`;
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
  fetch(`${BASE_URL}/admin/promociones/${id}/toggle`, {
    method: 'POST',
    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
    body: body.toString(),
  })
  .then(r => r.json())
  .then(d => { if (d.ok) location.reload(); });
}

function sendPromotion(id) {
  if (!confirm('¿Enviar esta promoción a los contactos segmentados?')) return;
  const body = new URLSearchParams({ _csrf: CSRF, via: 'whatsapp' });
  fetch(`${BASE_URL}/admin/promociones/${id}/enviar`, {
    method: 'POST',
    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
    body: body.toString(),
  })
  .then(r => r.json())
  .then(d => {
    if (d.ok && d.links) {
      alert(`Enlace generado para ${d.links.length} contactos. Se abrirá WhatsApp en una nueva ventana.`);
      if (d.links.length > 0) {
        window.open(d.links[0].url, '_blank');
      }
    } else {
      alert('Error al enviar');
    }
  });
}

document.getElementById('target-all')?.addEventListener('change', function() {
  document.querySelectorAll('.target-checkbox').forEach(cb => {
    if (cb.id !== 'target-all') cb.checked = this.checked;
  });
});
</script>

<style>
  .label { @apply block text-sm font-medium text-gray-700 mb-1; }
  .input  { @apply w-full px-4 py-2.5 border border-gray-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition; }
</style>
<?php require APP_PATH . '/views/layout/bottom_nav.php'; ?>
<?php require APP_PATH . '/views/layout/footer.php'; ?>