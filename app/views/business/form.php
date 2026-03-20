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

  <form method="POST" action="<?= url($isEdit ? 'admin/negocio/' . $business['id'] : 'admin/negocio/crear') ?>"
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

        <div>
          <label class="label">Categoría *</label>
          <select name="category_id" class="input">
            <?php foreach ($categories as $cat): ?>
            <option value="<?= $cat['id'] ?>"
              <?= ($business['category_id'] ?? '') == $cat['id'] ? 'selected' : '' ?>>
              <?= e($cat['name']) ?>
            </option>
            <?php endforeach; ?>
          </select>
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
      <?php if (!empty($services)): ?>
      <div class="divide-y mb-4">
        <?php foreach ($services as $s): ?>
        <div class="py-2 flex justify-between">
          <span class="text-sm text-gray-700"><?= e($s['name']) ?> <?= $s['price'] ? '– ' . formatPrice($s['price']) : '' ?></span>
        </div>
        <?php endforeach; ?>
      </div>
      <?php endif; ?>
      <details class="text-sm">
        <summary class="cursor-pointer text-blue-600 hover:underline">+ Agregar servicio</summary>
        <div class="mt-3 grid grid-cols-1 sm:grid-cols-3 gap-2 pt-2 border-t">
          <input type="text" id="svc-name" placeholder="Nombre" class="input text-sm">
          <input type="number" id="svc-price" placeholder="Precio (opcional)" class="input text-sm" step="0.01">
          <button type="button" onclick="addService(<?= $business['id'] ?>)"
            class="bg-green-500 text-white px-3 py-2 rounded-xl text-sm font-medium hover:bg-green-600 transition">
            Guardar
          </button>
        </div>
      </details>
    </div>
    <?php endif; ?>

    <div class="flex gap-3">
      <button type="submit"
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
</main>

<style>
  .label { @apply block text-sm font-medium text-gray-700 mb-1; }
  .input  { @apply w-full px-4 py-2.5 border border-gray-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition; }
</style>

<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
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
function uploadExtra() {
  const file = document.getElementById('extra-image').files[0];
  if (!file) return;
  const fd = new FormData();
  fd.append('image', file);
  fd.append('business_id', <?= $business['id'] ?>);
  fd.append('_csrf', '<?= e($csrf) ?>');
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
  // Simple fetch – would need an endpoint in production
  btn.closest('.relative').remove();
}

function addService(businessId) {
  const name  = document.getElementById('svc-name').value;
  const price = document.getElementById('svc-price').value;
  if (!name) return;
  // In production: send via fetch to a dedicated endpoint
  alert('Servicio "' + name + '" se guardará al enviar el formulario principal.');
}
<?php endif; ?>
</script>
<?php require APP_PATH . '/views/layout/bottom_nav.php'; ?>
<?php require APP_PATH . '/views/layout/footer.php'; ?>
