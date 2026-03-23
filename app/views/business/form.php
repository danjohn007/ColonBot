<?php
$isEdit     = $business !== null;
$pageTitle  = ($isEdit ? 'Editar: ' . $business['name'] : 'Nuevo Negocio') . ' – ' . APP_NAME;
require APP_PATH . '/views/layout/head.php';
?>
<?php require APP_PATH . '/views/layout/navbar.php'; ?>

<main class="max-w-4xl mx-auto px-4 py-8 mb-24">
  <div class="flex items-center gap-3 mb-6">
    <a href="<?= url('admin/negocio') ?>" class="text-gray-500 hover:text-blue-600 transition">
      <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
      </svg>
    </a>
    <h1 class="text-2xl font-bold text-gray-900"><?= $isEdit ? 'Editar Negocio' : 'Nuevo Negocio' ?></h1>
  </div>

  <?php if ($isEdit): ?>
  <!-- Tab navigation -->
  <div class="flex border-b border-gray-200 mb-6">
    <button type="button" id="tab-btn-basic"
      class="tab-btn px-5 py-2.5 text-sm font-medium border-b-2 border-blue-600 text-blue-600 -mb-px"
      onclick="switchTab('basic')">
      📋 Información básica
    </button>
    <button type="button" id="tab-btn-products"
      class="tab-btn px-5 py-2.5 text-sm font-medium border-b-2 border-transparent text-gray-500 hover:text-gray-700 -mb-px"
      onclick="switchTab('products')">
      🛍️ Mis Productos
    </button>
  </div>
  <?php endif; ?>

  <!-- Tab: Información básica -->
  <div id="tab-basic">
    <form id="business-form" method="POST" action="<?= url($isEdit ? 'admin/negocio/' . $business['id'] : 'admin/negocio/crear') ?>"
      enctype="multipart/form-data" class="space-y-6">
      <input type="hidden" name="_csrf" value="<?= e($csrf) ?>">

      <!-- Información básica -->
      <div class="bg-white rounded-2xl shadow-sm p-6 space-y-4">
        <h2 class="font-semibold text-gray-900 border-b pb-2">📋 Información básica</h2>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <div class="sm:col-span-2">
            <label class="label">Nombre del negocio *</label>
            <input type="text" name="name" required value="<?= e($business['name'] ?? '') ?>"
              class="input" placeholder="Ej. Restaurante El Sabor">
          </div>

          <div class="sm:col-span-2">
            <label class="label">Categoría(s) * <span class="text-xs text-gray-400">(selecciona al menos una)</span></label>
            <div class="flex flex-wrap gap-3 mt-1">
              <?php foreach ($categories as $cat): ?>
              <label class="flex items-center gap-2 cursor-pointer select-none">
                <input type="checkbox" name="categories[]" value="<?= $cat['id'] ?>"
                  <?= in_array($cat['id'], $businessCategoryIds ?? [], false) ? 'checked' : '' ?>
                  class="w-4 h-4 rounded text-blue-600 category-checkbox">
                <span class="flex items-center gap-1 text-sm text-gray-700">
                  <span class="inline-block w-3 h-3 rounded-full flex-shrink-0" style="background-color: <?= e($cat['color']) ?>"></span>
                  <?= e($cat['name']) ?>
                </span>
              </label>
              <?php endforeach; ?>
            </div>
          </div>

          <div>
            <label class="label">Estado</label>
            <select name="status" class="input">
              <?php foreach (['draft'=>'Borrador','pending'=>'Pendiente de aprobación','published'=>'Publicado'] as $val => $label): ?>
              <option value="<?= $val ?>" <?= ($business['status'] ?? 'draft') === $val ? 'selected' : '' ?>><?= $label ?></option>
              <?php endforeach; ?>
            </select>
          </div>

          <div class="sm:col-span-2">
            <label class="label">Descripción</label>
            <textarea name="description" rows="4" class="input" placeholder="Describe el lugar..."><?= e($business['description'] ?? '') ?></textarea>
          </div>
        </div>
      </div>

      <!-- Ubicación -->
      <div class="bg-white rounded-2xl shadow-sm p-6 space-y-4">
        <h2 class="font-semibold text-gray-900 border-b pb-2">📍 Ubicación</h2>
        <div>
          <label class="label">Dirección</label>
          <input type="text" name="address" value="<?= e($business['address'] ?? '') ?>" class="input" placeholder="Calle, Col., Municipio, Estado">
        </div>
        <div class="grid grid-cols-2 gap-4">
          <div>
            <label class="label">Latitud</label>
            <input type="number" name="lat" step="0.0000001" id="lat-input" value="<?= e($business['lat'] ?? '') ?>" class="input" placeholder="20.2862">
          </div>
          <div>
            <label class="label">Longitud</label>
            <input type="number" name="lng" step="0.0000001" id="lng-input" value="<?= e($business['lng'] ?? '') ?>" class="input" placeholder="-99.7242">
          </div>
        </div>
        <p class="text-xs text-gray-400">Haz clic en el mapa para seleccionar la ubicación exacta</p>
        <div id="edit-map" class="w-full h-48 rounded-xl border border-gray-200 overflow-hidden"></div>
      </div>

      <!-- Contacto -->
      <div class="bg-white rounded-2xl shadow-sm p-6 space-y-4">
        <h2 class="font-semibold text-gray-900 border-b pb-2">📞 Contacto</h2>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <div>
            <label class="label">Teléfono</label>
            <input type="tel" name="phone" value="<?= e($business['phone'] ?? '') ?>" class="input" placeholder="+52 442 100 0000">
          </div>
          <div>
            <label class="label">WhatsApp</label>
            <input type="tel" name="whatsapp" value="<?= e($business['whatsapp'] ?? '') ?>" class="input" placeholder="521442100000">
          </div>
          <div>
            <label class="label">Correo</label>
            <input type="email" name="email" value="<?= e($business['email'] ?? '') ?>" class="input">
          </div>
          <div>
            <label class="label">Sitio web</label>
            <input type="url" name="website" value="<?= e($business['website'] ?? '') ?>" class="input" placeholder="https://...">
          </div>
          <div>
            <label class="label">Facebook</label>
            <input type="url" name="facebook" value="<?= e($business['facebook'] ?? '') ?>" class="input" placeholder="https://facebook.com/...">
          </div>
          <div>
            <label class="label">Instagram</label>
            <input type="url" name="instagram" value="<?= e($business['instagram'] ?? '') ?>" class="input" placeholder="https://instagram.com/...">
          </div>
          <div class="sm:col-span-2">
            <label class="label">Horarios (JSON)</label>
            <input type="text" name="schedule" value="<?= e($business['schedule'] ?? '') ?>" class="input font-mono text-sm"
              placeholder='{"lun-vie":"09:00-18:00","sab":"10:00-16:00"}'>
          </div>
        </div>
      </div>

      <!-- Amenidades -->
      <div class="bg-white rounded-2xl shadow-sm p-6">
        <h2 class="font-semibold text-gray-900 border-b pb-2 mb-4">🛎️ Amenidades</h2>
        <div class="flex flex-wrap gap-3">
          <?php foreach ($amenities as $a): ?>
          <label class="flex items-center gap-2 cursor-pointer">
            <input type="checkbox" name="amenities[]" value="<?= $a['id'] ?>"
              <?= in_array($a['id'], $businessAmenIds ?? [], false) ? 'checked' : '' ?>
              class="w-4 h-4 text-blue-600 rounded">
            <span class="text-sm text-gray-700"><?= e($a['name']) ?></span>
          </label>
          <?php endforeach; ?>
        </div>
      </div>

      <!-- Imágenes -->
      <div class="bg-white rounded-2xl shadow-sm p-6 space-y-4">
        <h2 class="font-semibold text-gray-900 border-b pb-2">🖼️ Imágenes</h2>
        <div>
          <label class="label">Imagen de portada</label>
          <input type="file" name="cover" accept="image/*" class="input">
          <?php if (!empty($business['cover_image'])): ?>
          <img src="<?= asset('uploads/' . $business['cover_image']) ?>" class="mt-2 h-24 rounded-lg object-cover">
          <?php endif; ?>
        </div>

        <?php if ($isEdit): ?>
        <!-- Galería existente -->
        <?php if (!empty($images)): ?>
        <div class="flex flex-wrap gap-2">
          <?php foreach ($images as $img): ?>
          <div class="relative group">
            <img src="<?= asset('uploads/' . $img['path']) ?>" class="h-20 w-20 object-cover rounded-lg">
            <button type="button" onclick="deleteImage(<?= $img['id'] ?>, this)"
              class="absolute top-1 right-1 bg-red-500 text-white rounded-full w-5 h-5 text-xs flex items-center justify-center opacity-0 group-hover:opacity-100 transition">✕</button>
          </div>
          <?php endforeach; ?>
        </div>
        <?php endif; ?>

        <!-- Subir más fotos -->
        <div>
          <label class="label">Agregar más fotos</label>
          <div class="flex gap-2">
            <input type="file" id="extra-image" accept="image/*" class="input flex-1">
            <button type="button" onclick="uploadExtra()"
              class="bg-blue-600 text-white px-4 py-2 rounded-xl text-sm font-medium hover:bg-blue-700 transition">
              Subir
            </button>
          </div>
          <div id="extra-gallery" class="flex flex-wrap gap-2 mt-2"></div>
        </div>
        <?php endif; ?>
      </div>

      <!-- Servicios -->
      <?php if ($isEdit): ?>
      <div class="bg-white rounded-2xl shadow-sm p-6">
        <h2 class="font-semibold text-gray-900 border-b pb-2 mb-4">📋 Servicios</h2>
        <div id="services-list" class="divide-y mb-4">
          <?php foreach ($services as $s): ?>
          <div class="py-2 flex justify-between items-center" id="svc-row-<?= $s['id'] ?>">
            <span class="text-sm text-gray-700">
              <?= e($s['name']) ?>
              <?= $s['price'] ? ' – ' . formatPrice((float)$s['price']) : '' ?>
              <?php if (!$s['active']): ?>
              <span class="text-xs text-gray-400">(inactivo)</span>
              <?php endif; ?>
            </span>
            <button type="button" onclick="removeService(<?= $business['id'] ?>, <?= $s['id'] ?>)"
              class="text-red-500 hover:text-red-700 text-xs ml-3 transition">Eliminar</button>
          </div>
          <?php endforeach; ?>
        </div>
        <details class="text-sm" id="add-service-details">
          <summary class="cursor-pointer text-blue-600 hover:underline">+ Agregar servicio</summary>
          <div class="mt-3 grid grid-cols-1 sm:grid-cols-3 gap-2 pt-2 border-t">
            <input type="text" id="svc-name" placeholder="Nombre" class="input text-sm">
            <input type="number" id="svc-price" placeholder="Precio (opcional)" class="input text-sm" step="0.01" min="0">
            <button type="button" onclick="addService(<?= $business['id'] ?>)"
              class="bg-green-500 text-white px-3 py-2 rounded-xl text-sm font-medium hover:bg-green-600 transition">
              Guardar
            </button>
          </div>
        </details>
      </div>
      <?php endif; ?>

      <div class="flex gap-3">
        <button type="submit" id="submit-btn"
          class="flex-1 bg-blue-600 text-white py-3 rounded-xl font-semibold hover:bg-blue-700 transition">
          <?= $isEdit ? 'Guardar cambios' : 'Crear negocio' ?>
        </button>
        <?php if ($isEdit): ?>
        <form method="POST" action="<?= url('admin/negocio/' . $business['id'] . '/eliminar') ?>" onsubmit="return confirm('¿Eliminar este negocio?')">
          <input type="hidden" name="_csrf" value="<?= e($csrf) ?>">
          <button type="submit" class="px-6 py-3 bg-red-50 text-red-600 rounded-xl font-medium hover:bg-red-100 transition">
            Eliminar
          </button>
        </form>
        <?php endif; ?>
      </div>
    </form>
  </div><!-- /tab-basic -->

  <?php if ($isEdit): ?>
  <!-- Tab: Mis Productos -->
  <div id="tab-products" class="hidden">
    <div class="bg-white rounded-2xl shadow-sm p-6">
      <div class="flex items-center justify-between border-b pb-3 mb-4">
        <h2 class="font-semibold text-gray-900">🛍️ Mis Productos</h2>
        <button type="button" onclick="openProductForm()"
          class="bg-blue-600 text-white px-4 py-2 rounded-xl text-sm font-medium hover:bg-blue-700 transition">
          + Agregar producto
        </button>
      </div>

      <!-- Products table -->
      <div id="products-table-wrap">
        <?php if (empty($products)): ?>
        <p class="text-sm text-gray-400 text-center py-8" id="no-products-msg">No hay productos registrados.</p>
        <?php else: ?>
        <div class="hidden" id="no-products-msg"></div>
        <?php endif; ?>
        <div class="overflow-x-auto">
          <table class="w-full text-sm" id="products-table" <?= empty($products) ? 'class="hidden"' : '' ?>>
            <thead>
              <tr class="text-left text-xs text-gray-500 uppercase tracking-wide border-b">
                <th class="pb-2 pr-4">Nombre</th>
                <th class="pb-2 pr-4">Descripción</th>
                <th class="pb-2 pr-4">Precio</th>
                <th class="pb-2 pr-4">Disponible</th>
                <th class="pb-2"></th>
              </tr>
            </thead>
            <tbody id="products-tbody">
              <?php foreach ($products as $p): ?>
              <tr class="border-b last:border-0" id="prod-row-<?= $p['id'] ?>">
                <td class="py-2 pr-4 font-medium text-gray-800"><?= e($p['name']) ?></td>
                <td class="py-2 pr-4 text-gray-500 max-w-xs truncate"><?= e($p['description'] ?? '') ?></td>
                <td class="py-2 pr-4"><?= $p['price'] !== null ? formatPrice((float)$p['price']) : '–' ?></td>
                <td class="py-2 pr-4">
                  <span class="<?= $p['available'] ? 'text-green-600' : 'text-gray-400' ?>">
                    <?= $p['available'] ? '✓ Sí' : '✗ No' ?>
                  </span>
                </td>
                <td class="py-2 text-right whitespace-nowrap">
                  <button type="button"
                    onclick="editProduct(<?= htmlspecialchars(json_encode($p), ENT_QUOTES) ?>)"
                    class="text-blue-600 hover:text-blue-800 mr-3 transition">Editar</button>
                  <button type="button"
                    onclick="removeProduct(<?= $business['id'] ?>, <?= $p['id'] ?>)"
                    class="text-red-500 hover:text-red-700 transition">Eliminar</button>
                </td>
              </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Product form (hidden by default) -->
      <div id="product-form-wrap" class="hidden mt-4 pt-4 border-t">
        <h3 class="text-sm font-semibold text-gray-800 mb-3" id="product-form-title">Nuevo producto</h3>
        <input type="hidden" id="prod-id" value="">
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
          <div class="sm:col-span-2">
            <label class="label">Nombre *</label>
            <input type="text" id="prod-name" class="input" placeholder="Ej. Vino tinto reserva">
          </div>
          <div class="sm:col-span-2">
            <label class="label">Descripción</label>
            <textarea id="prod-desc" rows="2" class="input" placeholder="Descripción del producto..."></textarea>
          </div>
          <div>
            <label class="label">Precio</label>
            <input type="number" id="prod-price" class="input" step="0.01" min="0" placeholder="0.00">
          </div>
          <div class="flex items-end gap-3">
            <label class="flex items-center gap-2 cursor-pointer mb-2.5">
              <input type="checkbox" id="prod-available" checked class="w-4 h-4 text-blue-600 rounded">
              <span class="text-sm text-gray-700">Disponible</span>
            </label>
          </div>
        </div>
        <div class="flex gap-2 mt-3">
          <button type="button" onclick="saveProduct(<?= $business['id'] ?>)"
            class="bg-green-500 text-white px-5 py-2 rounded-xl text-sm font-medium hover:bg-green-600 transition">
            Guardar producto
          </button>
          <button type="button" onclick="cancelProductForm()"
            class="px-5 py-2 rounded-xl text-sm font-medium text-gray-600 hover:bg-gray-100 transition">
            Cancelar
          </button>
        </div>
      </div>
    </div>
  </div><!-- /tab-products -->
  <?php endif; ?>

</main>

<style>
  .label { @apply block text-sm font-medium text-gray-700 mb-1; }
  .input  { @apply w-full px-4 py-2.5 border border-gray-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition; }
</style>

<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
const CSRF = '<?= e($csrf) ?>';

function escHtml(str) {
  const d = document.createElement('div');
  d.textContent = String(str);
  return d.innerHTML;
}

// Category checkbox validation
document.getElementById('business-form').addEventListener('submit', function(e) {
  const checked = document.querySelectorAll('.category-checkbox:checked');
  if (checked.length === 0) {
    e.preventDefault();
    alert('Por favor selecciona al menos una categoría.');
  }
});

const defLat = parseFloat(document.getElementById('lat-input').value) || <?= setting('map_lat','20.2862') ?>;
const defLng = parseFloat(document.getElementById('lng-input').value) || <?= setting('map_lng','-99.7242') ?>;
const editMap = L.map('edit-map').setView([defLat, defLng], 15);
L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png').addTo(editMap);

let marker = L.marker([defLat, defLng], {draggable: true}).addTo(editMap);
marker.on('drag', e => updateCoords(e.target.getLatLng()));

// Pre-fill inputs with default coordinates for new businesses
if (!document.getElementById('lat-input').value) {
  document.getElementById('lat-input').value = defLat.toFixed(7);
  document.getElementById('lng-input').value = defLng.toFixed(7);
}

editMap.on('click', e => {
  marker.setLatLng(e.latlng);
  updateCoords(e.latlng);
});

function updateCoords(latlng) {
  document.getElementById('lat-input').value = latlng.lat.toFixed(7);
  document.getElementById('lng-input').value = latlng.lng.toFixed(7);
}

<?php if ($isEdit): ?>
// ── Tab switching ────────────────────────────────────────────────────────────
function switchTab(tab) {
  document.getElementById('tab-basic').classList.toggle('hidden', tab !== 'basic');
  document.getElementById('tab-products').classList.toggle('hidden', tab !== 'products');
  document.getElementById('tab-btn-basic').classList.toggle('border-blue-600', tab === 'basic');
  document.getElementById('tab-btn-basic').classList.toggle('text-blue-600', tab === 'basic');
  document.getElementById('tab-btn-basic').classList.toggle('border-transparent', tab !== 'basic');
  document.getElementById('tab-btn-basic').classList.toggle('text-gray-500', tab !== 'basic');
  document.getElementById('tab-btn-products').classList.toggle('border-blue-600', tab === 'products');
  document.getElementById('tab-btn-products').classList.toggle('text-blue-600', tab === 'products');
  document.getElementById('tab-btn-products').classList.toggle('border-transparent', tab !== 'products');
  document.getElementById('tab-btn-products').classList.toggle('text-gray-500', tab !== 'products');
  // Invalidate the map when switching back to basic tab so it renders correctly
  if (tab === 'basic') { setTimeout(() => editMap.invalidateSize(), 50); }
}

// ── Image upload ─────────────────────────────────────────────────────────────
function uploadExtra() {
  const file = document.getElementById('extra-image').files[0];
  if (!file) return;
  const fd = new FormData();
  fd.append('image', file);
  fd.append('business_id', <?= $business['id'] ?>);
  fd.append('_csrf', CSRF);
  fetch('<?= url('admin/upload') ?>', { method: 'POST', body: fd })
    .then(r => r.json())
    .then(d => {
      if (d.path) {
        const div = document.getElementById('extra-gallery');
        div.innerHTML += `<img src="${d.path}" class="h-20 w-20 object-cover rounded-lg">`;
      }
    });
}

function deleteImage(id, btn) {
  if (!confirm('¿Eliminar imagen?')) return;
  btn.closest('.relative').remove();
}

// ── Services ─────────────────────────────────────────────────────────────────
function addService(businessId) {
  const name  = document.getElementById('svc-name').value.trim();
  const price = document.getElementById('svc-price').value;
  if (!name) { alert('Escribe el nombre del servicio.'); return; }

  const body = new URLSearchParams({ _csrf: CSRF, name, price });
  fetch('<?= url('admin/negocio/' . $business['id'] . '/servicio') ?>', {
    method: 'POST',
    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
    body: body.toString()
  })
  .then(r => r.json())
  .then(d => {
    if (d.ok) {
      renderServices(d.services);
      document.getElementById('svc-name').value  = '';
      document.getElementById('svc-price').value = '';
      document.getElementById('add-service-details').removeAttribute('open');
    } else {
      alert(d.error || 'Error al guardar.');
    }
  });
}

function removeService(businessId, sid) {
  if (!confirm('¿Eliminar este servicio?')) return;
  const body = new URLSearchParams({ _csrf: CSRF });
  fetch(`<?= url('admin/negocio/' . $business['id']) ?>/servicio/${sid}/eliminar`, {
    method: 'POST',
    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
    body: body.toString()
  })
  .then(r => r.json())
  .then(d => {
    if (d.ok) {
      const row = document.getElementById('svc-row-' + sid);
      if (row) row.remove();
    }
  });
}

function renderServices(services) {
  const list = document.getElementById('services-list');
  list.innerHTML = '';
  services.forEach(s => {
    const priceText = s.price ? ` – $${parseFloat(s.price).toFixed(2)}` : '';
    const activeText = s.active === 0 ? ' <span class="text-xs text-gray-400">(inactivo)</span>' : '';
    const bid = <?= $business['id'] ?>;
    list.innerHTML += `<div class="py-2 flex justify-between items-center" id="svc-row-${s.id}">
      <span class="text-sm text-gray-700">${escHtml(s.name)}${priceText}${activeText}</span>
      <button type="button" onclick="removeService(${bid}, ${s.id})"
        class="text-red-500 hover:text-red-700 text-xs ml-3 transition">Eliminar</button>
    </div>`;
  });
}

// ── Products ─────────────────────────────────────────────────────────────────
function openProductForm() {
  document.getElementById('product-form-title').textContent = 'Nuevo producto';
  document.getElementById('prod-id').value        = '';
  document.getElementById('prod-name').value      = '';
  document.getElementById('prod-desc').value      = '';
  document.getElementById('prod-price').value     = '';
  document.getElementById('prod-available').checked = true;
  document.getElementById('product-form-wrap').classList.remove('hidden');
  document.getElementById('prod-name').focus();
}

function editProduct(p) {
  document.getElementById('product-form-title').textContent = 'Editar producto';
  document.getElementById('prod-id').value        = p.id;
  document.getElementById('prod-name').value      = p.name;
  document.getElementById('prod-desc').value      = p.description || '';
  document.getElementById('prod-price').value     = p.price || '';
  document.getElementById('prod-available').checked = p.available === 1;
  document.getElementById('product-form-wrap').classList.remove('hidden');
  document.getElementById('prod-name').focus();
}

function cancelProductForm() {
  document.getElementById('product-form-wrap').classList.add('hidden');
}

function saveProduct(businessId) {
  const name      = document.getElementById('prod-name').value.trim();
  const desc      = document.getElementById('prod-desc').value.trim();
  const price     = document.getElementById('prod-price').value;
  const available = document.getElementById('prod-available').checked ? 1 : 0;
  const pid       = document.getElementById('prod-id').value;

  if (!name) { alert('Escribe el nombre del producto.'); return; }

  const body = new URLSearchParams({ _csrf: CSRF, name, description: desc, price, available, product_id: pid });
  fetch('<?= url('admin/negocio/' . $business['id'] . '/producto') ?>', {
    method: 'POST',
    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
    body: body.toString()
  })
  .then(r => r.json())
  .then(d => {
    if (d.ok) {
      renderProducts(d.products);
      cancelProductForm();
    } else {
      alert(d.error || 'Error al guardar.');
    }
  });
}

function removeProduct(businessId, pid) {
  if (!confirm('¿Eliminar este producto?')) return;
  const body = new URLSearchParams({ _csrf: CSRF });
  fetch(`<?= url('admin/negocio/' . $business['id']) ?>/producto/${pid}/eliminar`, {
    method: 'POST',
    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
    body: body.toString()
  })
  .then(r => r.json())
  .then(d => {
    if (d.ok) {
      const row = document.getElementById('prod-row-' + pid);
      if (row) row.remove();
      toggleNoProductsMsg();
    }
  });
}

function toggleNoProductsMsg() {
  const tbody = document.getElementById('products-tbody');
  const msg   = document.getElementById('no-products-msg');
  const table = document.getElementById('products-table');
  if (tbody && tbody.children.length === 0) {
    msg.textContent = 'No hay productos registrados.';
    msg.classList.remove('hidden');
    if (table) table.classList.add('hidden');
  }
}

function renderProducts(products) {
  const tbody = document.getElementById('products-tbody');
  const msg   = document.getElementById('no-products-msg');
  const table = document.getElementById('products-table');
  if (!tbody) return;

  tbody.innerHTML = '';
  if (products.length === 0) {
    msg.textContent = 'No hay productos registrados.';
    msg.classList.remove('hidden');
    if (table) table.classList.add('hidden');
    return;
  }
  msg.classList.add('hidden');
  if (table) table.classList.remove('hidden');

  const bid = <?= $business['id'] ?>;
  products.forEach(p => {
    const priceText = p.price !== null ? '$' + parseFloat(p.price).toFixed(2) : '–';
    const availText = p.available === 1
      ? '<span class="text-green-600">✓ Sí</span>'
      : '<span class="text-gray-400">✗ No</span>';
    tbody.innerHTML += `<tr class="border-b last:border-0" id="prod-row-${p.id}">
      <td class="py-2 pr-4 font-medium text-gray-800">${escHtml(p.name)}</td>
      <td class="py-2 pr-4 text-gray-500 max-w-xs truncate">${escHtml(p.description || '')}</td>
      <td class="py-2 pr-4">${priceText}</td>
      <td class="py-2 pr-4">${availText}</td>
      <td class="py-2 text-right whitespace-nowrap">
        <button type="button" onclick='editProduct(${JSON.stringify(p).replace(/'/g, "\\'")})'
          class="text-blue-600 hover:text-blue-800 mr-3 transition">Editar</button>
        <button type="button" onclick="removeProduct(${bid}, ${p.id})"
          class="text-red-500 hover:text-red-700 transition">Eliminar</button>
      </td>
    </tr>`;
  });
}
<?php endif; ?>
</script>
<?php require APP_PATH . '/views/layout/bottom_nav.php'; ?>
<?php require APP_PATH . '/views/layout/footer.php'; ?>

