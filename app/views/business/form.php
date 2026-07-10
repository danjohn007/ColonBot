<?php
$isEdit     = $business !== null;
$pageTitle  = ($isEdit ? 'Editar: ' . $business['name'] : 'Nuevo Negocio') . ' – ' . APP_NAME;
$puntoReferenciaId = null;
foreach ($categories ?? [] as $cat) {
    if (($cat['slug'] ?? '') === 'punto-de-referencia') {
        $puntoReferenciaId = (int)$cat['id'];
        break;
    }
}
require APP_PATH . '/views/layout/head.php';
?>
<?php require APP_PATH . '/views/layout/navbar.php'; ?>

<main class="business-form-page max-w-7xl mx-auto px-4 py-8 mb-24">
  <div class="business-form-hero mb-6">
    <div class="flex flex-wrap items-start justify-between gap-4">
      <div class="min-w-0">
        <a href="<?= url('admin/negocio') ?>" class="inline-flex items-center gap-2 text-sm font-semibold text-blue-700 hover:text-blue-900 transition">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
          </svg>
          Volver a mis negocios
        </a>
        <h1 class="mt-3 text-3xl font-extrabold text-gray-950"><?= $isEdit ? 'Editar negocio' : 'Nuevo negocio' ?></h1>
        <p class="mt-2 text-sm text-gray-600 max-w-2xl">Completa la informacion que veran visitantes en el mapa, rutas turisticas y CristoBot.</p>
      </div>
      <div class="business-form-status">
        <span>Estado</span>
        <strong><?= e($business['status'] ?? 'Borrador') ?></strong>
      </div>
    </div>
  </div>

  <?php if ($isEdit): ?>
  <!-- Tab navigation -->
  <div class="flex border-b border-gray-200 mb-6">
    <button type="button" id="tab-btn-basic"
      class="tab-btn px-5 py-2.5 text-sm font-medium border-b-2 border-blue-600 text-blue-600 -mb-px"
      onclick="switchTab('basic')">
      📋 Información básica
    </button>
    <button type="button" id="scroll-btn-products"
      class="tab-btn px-5 py-2.5 text-sm font-medium border-b-2 border-transparent text-gray-500 hover:text-gray-700 hover:text-blue-600 -mb-px cursor-pointer"
      onclick="scrollToSection('section-services-products', this)">
      🛍️ Mis Productos
    </button>
    <button type="button" id="scroll-btn-services"
      class="tab-btn px-5 py-2.5 text-sm font-medium border-b-2 border-transparent text-gray-500 hover:text-gray-700 hover:text-blue-600 -mb-px cursor-pointer"
      onclick="scrollToSection('section-services', this)">
      📋 Mis Servicios
    </button>
  </div>
  <?php endif; ?>

  <!-- Tab: Información básica -->
  <div id="tab-basic" class="business-form-layout">
    <aside class="business-form-aside">
      <div class="business-form-guide">
        <p>Checklist</p>
        <a href="#section-basic">1. Informacion</a>
        <a href="#section-location">2. Ubicacion</a>
        <a href="#section-contact">3. Contacto</a>
        <a href="#section-amenities">4. Amenidades</a>
        <a href="#section-images">5. Imagenes</a>
        <?php if ($isEdit): ?>
        <a href="#section-services">6. Servicios</a>
        <?php endif; ?>
        <div class="business-form-tip">Los campos con * son necesarios para publicar correctamente el negocio.</div>
      </div>
    </aside>
    <form id="business-form" method="POST" action="<?= url($isEdit ? 'admin/negocio/' . $business['id'] : 'admin/negocio/crear') ?>"
      enctype="multipart/form-data" class="business-form-main space-y-6">
      <input type="hidden" name="_csrf" value="<?= e($csrf) ?>">

      <!-- Información básica -->
      <div id="section-basic" class="business-form-card space-y-4">
        <h2 class="font-semibold text-gray-900 border-b pb-2">📋 Información básica</h2>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <div class="sm:col-span-2">
            <label class="label">Nombre del negocio *</label>
            <input type="text" name="name" required value="<?= e($business['name'] ?? '') ?>"
              class="input" placeholder="Ej. Restaurante El Sabor">
          </div>

          <div class="sm:col-span-2">
            <label class="label">Categoría(s) * <span class="text-xs text-gray-400">(selecciona al menos una)</span></label>
            <div class="business-choice-grid mt-2">
              <?php foreach ($categories as $cat): ?>
              <label class="business-choice">
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

          <!-- Tipo de viaje -->
          <div class="sm:col-span-2">
            <label class="label">Tipo de viaje <span class="text-xs text-gray-400">(selecciona uno o varios)</span></label>
            <div class="business-choice-grid mt-2" id="trip-types-container">
              <?php $tripTypeOptions = ['familiar'=>'👨‍👩‍👧‍👦 Familiar', 'amigos'=>'🧑‍🤝‍🧑 Viaje de amigos', 'pareja'=>'💑 Pareja', 'adultos_mayores'=>'Adultos mayores', 'petfriendly'=>'🐾 Petfriendly']; ?>
              <?php foreach ($tripTypeOptions as $val => $label): ?>
              <label class="business-choice">
                <input type="checkbox" name="trip_types[]" value="<?= $val ?>"
                  <?= in_array($val, $businessTripTypes ?? [], true) ? 'checked' : '' ?>
                  class="w-4 h-4 rounded text-blue-600 trip-type-checkbox">
                <span class="text-sm text-gray-700"><?= $label ?></span>
              </label>
              <?php endforeach; ?>
              <label class="business-choice business-choice-all">
                <input type="checkbox" id="trip-type-all" value="todos"
                  class="w-4 h-4 rounded text-blue-600">
                <span class="text-sm font-medium text-blue-600">✅ Todos</span>
              </label>
            </div>
          </div>
          <script>
          document.addEventListener('DOMContentLoaded', function() {
            const allCheckbox = document.getElementById('trip-type-all');
            const typeCheckboxes = document.querySelectorAll('.trip-type-checkbox');
            const allValues = <?= json_encode(array_keys($tripTypeOptions)) ?>;

            function updateAllState() {
              const checked = Array.from(typeCheckboxes).filter(cb => cb.checked);
              allCheckbox.checked = checked.length === typeCheckboxes.length;
            }

            allCheckbox.addEventListener('change', function() {
              typeCheckboxes.forEach(cb => cb.checked = this.checked);
            });

            typeCheckboxes.forEach(cb => {
              cb.addEventListener('change', updateAllState);
            });

            updateAllState();
          });
          </script>

          <!-- Tipo de lugar -->
          <div class="sm:col-span-2">
            <label class="label">Tipo de lugar <span class="text-xs text-gray-400">(selecciona uno)</span></label>
            <div class="business-choice-grid business-choice-grid-wide mt-2">
<?php $isotipoOptions = ['restaurante'=>'🍽️ Restaurante', 'lugares_historicos'=>'🏛️ Lugares hist&oacute;ricos', 'viniedo'=>'🍇 Vi&ntilde;edo', 'hotel'=>'🏨 Hotel', 'paisaje_cerro'=>'⭐ Paisaje/cerro', 'lago_presa'=>'🌊 Lago/presa', 'lugar_compras'=>'🛍️ Lugar de compras', 'pena_bernal'=>'🏔️ Pe&ntilde;a de Bernal', 'aeropuerto'=>'✈️ Aeropuerto', 'zoologico_wameru'=>'🦁 Zool&oacute;gico Wamer&uacute;', 'arcos_queretaro'=>'🌉 Los Arcos de Quer&eacute;taro', 'estacion_tren'=>'🚂 Estaci&oacute;n del tren M&eacute;xico-Quer&eacute;taro', 'lugar_religioso'=>'⛪ Lugar religioso', 'apicultura'=>'🐝 Apicultura']; ?>
              <?php foreach ($isotipoOptions as $val => $label): ?>
              <label class="business-choice">
                <input type="radio" name="isotipo" value="<?= $val ?>"
                  <?= ($business['isotipo'] ?? '') === $val ? 'checked' : '' ?>
                  class="w-4 h-4 text-blue-600">
                <span class="text-sm text-gray-700"><?= $label ?></span>
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
      <div id="section-location" class="business-form-card space-y-4">
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
        <div id="edit-map" class="w-full h-80 rounded-xl border border-gray-200 overflow-hidden"></div>
      </div>

      <!-- Contacto -->
      <div id="section-contact" class="business-form-card space-y-4">
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
          <!-- Horarios estilo Google My Business -->
          <div class="sm:col-span-2">
            <label class="label">Horarios</label>
            <input type="hidden" name="schedule" id="schedule-json" value="<?= e($business['schedule'] ?? '') ?>">
            <div id="schedule-picker" class="space-y-2">
              <?php
              $days = ['lunes'=>'Lunes', 'martes'=>'Martes', 'miercoles'=>'Miércoles', 'jueves'=>'Jueves', 'viernes'=>'Viernes', 'sabado'=>'Sábado', 'domingo'=>'Domingo'];
              $dayKeys = ['lunes','martes','miercoles','jueves','viernes','sabado','domingo'];
              $savedSchedule = [];
              if (!empty($business['schedule'])) {
                $decoded = json_decode($business['schedule'], true);
                if (is_array($decoded)) $savedSchedule = $decoded;
              }
              foreach ($dayKeys as $dk):
                $dayData = $savedSchedule[$dk] ?? '';
                $isClosed = $dayData === 'closed' || $dayData === '';
                $rangeParts = $isClosed ? ['09:00', '18:00'] : explode('-', $dayData);
                $openTime = $rangeParts[0] ?? '09:00';
                $closeTime = $rangeParts[1] ?? '18:00';
              ?>
              <div class="schedule-row flex flex-wrap items-center gap-2 py-1.5" data-day="<?= $dk ?>">
                <span class="w-24 text-sm font-medium text-gray-700"><?= $days[$dk] ?></span>
                <label class="flex items-center gap-1.5 cursor-pointer">
                  <input type="checkbox" class="schedule-toggle w-4 h-4 text-blue-600 rounded" <?= !$isClosed ? 'checked' : '' ?>>
                  <span class="text-xs text-gray-500">Abierto</span>
                </label>
                <div class="schedule-times flex items-center gap-1 <?= $isClosed ? 'opacity-40' : '' ?>">
                  <input type="time" class="schedule-open input !min-h-0 !py-1.5 !px-2 w-28 text-xs" value="<?= $openTime ?>">
                  <span class="text-gray-400 text-xs">a</span>
                  <input type="time" class="schedule-close input !min-h-0 !py-1.5 !px-2 w-28 text-xs" value="<?= $closeTime ?>">
                </div>
                <span class="schedule-closed-text text-xs text-red-500 font-medium <?= !$isClosed ? 'hidden' : '' ?>">Cerrado</span>
              </div>
              <?php endforeach; ?>
            </div>
            <p class="text-xs text-gray-400 mt-1">Marca los días y ajusta los horarios de apertura y cierre.</p>
          </div>
          <div class="sm:col-span-2 rounded-xl border <?= ($business['is_open'] ?? 1) ? 'border-green-200 bg-green-50' : 'border-gray-200 bg-gray-50' ?> p-4">
            <input type="hidden" name="is_open" value="0">
            <label class="flex items-start justify-between gap-4 cursor-pointer">
              <span>
                <span class="block text-sm font-semibold <?= ($business['is_open'] ?? 1) ? 'text-green-700' : 'text-gray-700' ?>">
                  En línea en CristobalBot
                </span>
                <span class="block text-xs text-gray-500 mt-1">Activa este negocio para que aparezca como visible y disponible en el chatbot.</span>
              </span>
              <input type="checkbox" name="is_open" value="1"
                <?= ($business['is_open'] ?? 1) ? 'checked' : '' ?>
                class="mt-1 w-5 h-5 rounded text-green-600">
            </label>
            <div class="mt-3">
              <label class="label">Disponibilidad para mensajes</label>
              <select name="open_for_messaging" class="input">
                <option value="24hrs" <?= ($business['open_for_messaging'] ?? '24hrs') === '24hrs' ? 'selected' : '' ?>>24 horas</option>
                <option value="schedule" <?= ($business['open_for_messaging'] ?? '') === 'schedule' ? 'selected' : '' ?>>Segun horarios</option>
              </select>
            </div>
          </div>
        </div>
      </div>

      <!-- Amenidades -->
      <div id="section-amenities" class="business-form-card">
        <h2 class="font-semibold text-gray-900 border-b pb-2 mb-4">🛎️ Amenidades</h2>
        <div class="business-choice-grid mt-2" id="amenities-container">
          <?php foreach ($amenities as $a): ?>
          <label class="business-choice">
            <input type="checkbox" name="amenities[]" value="<?= $a['id'] ?>"
              <?= in_array($a['id'], $businessAmenIds ?? [], false) ? 'checked' : '' ?>
              class="w-4 h-4 text-blue-600 rounded">
            <span class="text-sm text-gray-700"><?= e($a['name']) ?></span>
          </label>
          <?php endforeach; ?>
        </div>
      </div>

      <!-- Imágenes -->
      <div id="section-images" class="business-form-card space-y-4">
        <h2 class="font-semibold text-gray-900 border-b pb-2">🖼️ Imágenes</h2>
        <div>
          <label class="label">Imagen de portada</label>
          <input type="file" name="cover" accept="image/*" class="input">
          <?php if (!empty($business['cover_image'])): ?>
          <img src="<?= imageUrl($business['cover_image']) ?>" class="mt-2 h-24 rounded-lg object-cover">
          <?php endif; ?>
        </div>

        <?php if ($isEdit): ?>
        <!-- Galería existente -->
        <?php if (!empty($images)): ?>
        <div class="flex flex-wrap gap-2">
          <?php foreach ($images as $img): ?>
          <div class="relative group">
            <img src="<?= imageUrl($img['path']) ?>" class="h-20 w-20 object-cover rounded-lg">
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

      <!-- Products section (inside basic tab) -->
      <?php if ($isEdit): ?>
      <div id="section-services-products" class="business-form-card">
        <h2 class="font-semibold text-gray-900 border-b pb-2 mb-4">🛍️ Mis Productos</h2>
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
        <div class="flex items-center justify-between mt-4 pt-4 border-t">
          <button type="button" onclick="openProductForm()"
            class="bg-blue-600 text-white px-4 py-2 rounded-xl text-sm font-medium hover:bg-blue-700 transition">
            + Agregar producto
          </button>
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

      <!-- Servicios -->
      <div id="section-services" class="business-form-card">
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

      <div class="business-form-actions">
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

  <?php endif; ?>

</main>

<style>
  .business-form-page {
    color: #111827;
  }
  .business-form-hero {
    background: #fff;
    border: 1px solid #e5e7eb;
    border-radius: 18px;
    padding: 1.25rem;
    box-shadow: 0 12px 32px rgba(15, 23, 42, .06);
  }
  .business-form-status {
    min-width: 9rem;
    padding: .9rem 1rem;
    border-radius: 14px;
    background: #f8fafc;
    border: 1px solid #e5e7eb;
  }
  .business-form-status span {
    display: block;
    color: #6b7280;
    font-size: .72rem;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: .04em;
  }
  .business-form-status strong {
    display: block;
    margin-top: .2rem;
    color: #1d4ed8;
    font-size: .95rem;
  }
  .business-form-layout {
    display: grid;
    grid-template-columns: minmax(0, 1fr);
    gap: 1.25rem;
  }
  .business-form-aside {
    order: 2;
  }
  .business-form-main {
    order: 1;
    min-width: 0;
  }
  .business-form-guide {
    position: sticky;
    top: 5rem;
    background: #fff;
    border: 1px solid #e5e7eb;
    border-radius: 16px;
    padding: 1rem;
    box-shadow: 0 10px 28px rgba(15, 23, 42, .05);
  }
  .business-form-guide p {
    color: #6b7280;
    font-size: .72rem;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: .05em;
    margin-bottom: .75rem;
  }
  .business-form-guide a {
    display: block;
    padding: .65rem .75rem;
    border-radius: 10px;
    color: #374151;
    font-size: .86rem;
    font-weight: 700;
    text-decoration: none;
  }
  .business-form-guide a:hover {
    background: #eff6ff;
    color: #1d4ed8;
  }
  .business-form-tip {
    margin-top: 1rem;
    padding: .8rem;
    border-radius: 12px;
    background: #eff6ff;
    border: 1px solid #bfdbfe;
    color: #1e3a8a;
    font-size: .78rem;
    line-height: 1.4;
  }
  .business-form-card {
    background: #fff;
    border: 1px solid #e5e7eb;
    border-radius: 18px;
    padding: 1.25rem;
    box-shadow: 0 10px 28px rgba(15, 23, 42, .05);
    scroll-margin-top: 6rem;
  }
  .business-form-card h2 {
    display: flex;
    align-items: center;
    gap: .5rem;
    border-bottom: 1px solid #eef2f7;
    padding-bottom: .8rem;
    margin-bottom: 1rem;
    color: #111827;
  }
  .label {
    display: block;
    margin-bottom: .35rem;
    color: #374151;
    font-size: .86rem;
    font-weight: 800;
  }
  .input {
    width: 100%;
    min-height: 2.75rem;
    padding: .7rem .9rem;
    border: 1px solid #d1d5db;
    border-radius: 12px;
    background: #fff;
    color: #111827;
    font-size: .92rem;
    outline: none;
    transition: border-color .18s ease, box-shadow .18s ease, background .18s ease;
  }
  textarea.input {
    min-height: 7rem;
    resize: vertical;
  }
  .input:focus {
    border-color: #2563eb;
    box-shadow: 0 0 0 3px rgba(37, 99, 235, .14);
  }
  .business-choice-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(14rem, 1fr));
    gap: .65rem;
  }
  .business-choice-grid-wide {
    grid-template-columns: repeat(auto-fit, minmax(16rem, 1fr));
  }
  .business-choice {
    display: flex;
    align-items: center;
    gap: .65rem;
    min-height: 3rem;
    padding: .72rem .85rem;
    border: 1px solid #e5e7eb;
    border-radius: 12px;
    background: #fff;
    cursor: pointer;
    user-select: none;
    transition: border-color .18s ease, background .18s ease, box-shadow .18s ease;
  }
  .business-choice:hover {
    border-color: #93c5fd;
    background: #f8fbff;
  }
  .business-choice:has(input:checked) {
    border-color: #2563eb;
    background: #eff6ff;
    box-shadow: 0 0 0 2px rgba(37, 99, 235, .08);
  }
  .business-choice input {
    flex: 0 0 auto;
  }
  .business-choice-all {
    border-color: #bfdbfe;
    background: #f8fbff;
  }
  .business-form-actions {
    position: sticky;
    bottom: 1rem;
    z-index: 20;
    display: flex;
    gap: .75rem;
    padding: .75rem;
    border: 1px solid #e5e7eb;
    border-radius: 16px;
    background: rgba(255, 255, 255, .94);
    box-shadow: 0 18px 42px rgba(15, 23, 42, .16);
    backdrop-filter: blur(8px);
  }
  #edit-map {
    min-height: 20rem;
  }
  @media (min-width: 1024px) {
    .business-form-layout {
      grid-template-columns: minmax(0, 1fr) 18rem;
      align-items: start;
    }
    .business-form-aside {
      order: 2;
    }
    .business-form-main {
      order: 1;
    }
  }
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

// ── Punto de referencia: bloquear Tipo de viaje y Amenidades ──────────────────
document.addEventListener('DOMContentLoaded', function() {
  function togglePuntoReferencia() {
    const puntoReferenciaId = '<?= $puntoReferenciaId !== null ? (int)$puntoReferenciaId : '' ?>';
    const puntoRef = puntoReferenciaId ? document.querySelector(`.category-checkbox[value="${puntoReferenciaId}"]`) : null;
    if (!puntoRef) return;
    
    const tripTypesContainer = document.getElementById('trip-types-container');
    
    function updateDisabledState() {
      const isChecked = puntoRef.checked;
      
      // Bloquear/desbloquear Tipo de viaje
      if (tripTypesContainer) {
        tripTypesContainer.querySelectorAll('input[type="checkbox"]').forEach(cb => {
          cb.disabled = isChecked;
          if (isChecked) cb.checked = false;
        });
        tripTypesContainer.style.opacity = isChecked ? '0.5' : '1';
      }
      
      // Bloquear/desbloquear Amenidades
      document.querySelectorAll('input[name="amenities[]"]').forEach(cb => {
        cb.disabled = isChecked;
        if (isChecked) cb.checked = false;
      });
      document.querySelectorAll('input[name="amenities[]"]').forEach(cb => {
        cb.closest('label').style.opacity = isChecked ? '0.5' : '1';
      });
    }
    
    puntoRef.addEventListener('change', updateDisabledState);
    updateDisabledState();
  }
  
  togglePuntoReferencia();
});

// ── Schedule picker: sync visual controls to hidden JSON input ──────────────
function syncSchedule() {
  const result = {};
  document.querySelectorAll('.schedule-row').forEach(row => {
    const day = row.dataset.day;
    const isOpen = row.querySelector('.schedule-toggle').checked;
    if (isOpen) {
      const openVal = row.querySelector('.schedule-open').value || '09:00';
      const closeVal = row.querySelector('.schedule-close').value || '18:00';
      result[day] = openVal + '-' + closeVal;
    } else {
      result[day] = 'closed';
    }
  });
  document.getElementById('schedule-json').value = JSON.stringify(result);
}

// Set up schedule toggle listeners
document.addEventListener('DOMContentLoaded', function() {
  document.querySelectorAll('.schedule-toggle').forEach(toggle => {
    toggle.addEventListener('change', function() {
      const row = this.closest('.schedule-row');
      const timesDiv = row.querySelector('.schedule-times');
      const closedText = row.querySelector('.schedule-closed-text');
      if (this.checked) {
        timesDiv.classList.remove('opacity-40');
        closedText.classList.add('hidden');
      } else {
        timesDiv.classList.add('opacity-40');
        closedText.classList.remove('hidden');
      }
      syncSchedule();
    });
  });
  document.querySelectorAll('.schedule-open, .schedule-close').forEach(input => {
    input.addEventListener('change', syncSchedule);
  });
  // Initial sync
  syncSchedule();
});

<?php if ($isEdit): ?>
// ── Smooth scroll to section ────────────────────────────────────────────────
function scrollToSection(sectionId, btn) {
  // First make sure we're on the basic tab
  document.getElementById('tab-basic').classList.remove('hidden');

  // Update tabs visual state - make basic the active tab
  const tabBtn = document.getElementById('tab-btn-basic');
  if (tabBtn) {
    tabBtn.classList.add('border-blue-600', 'text-blue-600');
    tabBtn.classList.remove('border-transparent', 'text-gray-500');
  }

  // Invalidate map if needed
  setTimeout(() => { if (typeof editMap !== 'undefined') editMap.invalidateSize(); }, 50);

  // Scroll to the target section after a brief delay for the map to render
  setTimeout(() => {
    const target = document.getElementById(sectionId);
    if (target) {
      target.scrollIntoView({ behavior: 'smooth', block: 'start' });
      // Add a flash highlight effect
      target.style.transition = 'box-shadow 0.3s ease';
      target.style.boxShadow = '0 0 0 3px rgba(37, 99, 235, 0.3)';
      setTimeout(() => { target.style.boxShadow = ''; }, 1500);
    }
  }, 100);
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
  const body = new URLSearchParams({ _csrf: CSRF });
  fetch(`<?= url('admin/imagen/') ?>${id}/eliminar`, {
    method: 'POST',
    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
    body: body.toString()
  })
  .then(r => {
    if (!r.ok) throw new Error('HTTP ' + r.status);
    return r.json();
  })
  .then(d => {
    if (d.ok) {
      btn.closest('.relative').remove();
    } else {
      alert(d.error || 'Error al eliminar la imagen.');
    }
  })
  .catch(() => alert('Error al eliminar la imagen.'));
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
