<?php
$pageTitle = e($business['name']) . ' – ' . APP_NAME;
require APP_PATH . '/views/layout/head.php';
?>
<?php require APP_PATH . '/views/layout/navbar.php'; ?>

<main class="max-w-5xl mx-auto px-4 py-8 mb-20">
  <!-- Breadcrumb -->
  <nav class="text-sm text-gray-500 mb-4">
    <a href="<?= url('mapa') ?>" class="hover:text-blue-600">← Regresar al mapa</a>
  </nav>

  <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
    <!-- Main content -->
    <div class="lg:col-span-2 space-y-6">
      <!-- Gallery -->
      <?php if ($images): ?>
      <div class="grid grid-cols-2 sm:grid-cols-3 gap-2 rounded-2xl overflow-hidden">
        <?php foreach (array_slice($images, 0, 5) as $i => $img): ?>
        <img src="<?= $i === 0 ? asset('uploads/' . $img['path']) : asset('uploads/' . $img['path']) ?>"
          alt="<?= e($img['caption'] ?: $business['name']) ?>"
          class="<?= $i === 0 ? 'col-span-2 row-span-2 sm:col-span-2 sm:row-span-2' : '' ?> w-full h-48 object-cover">
        <?php endforeach; ?>
      </div>
      <?php else: ?>
      <div class="w-full h-64 bg-gradient-to-br from-blue-100 to-indigo-200 rounded-2xl flex items-center justify-center text-blue-400 text-6xl">
        🗺️
      </div>
      <?php endif; ?>

      <!-- Info -->
      <div class="bg-white rounded-2xl shadow-sm p-6 space-y-4">
        <div class="flex items-start justify-between gap-4">
          <div>
            <span class="text-xs font-semibold px-3 py-1 rounded-full text-white" style="background: <?= e($business['category_color']) ?>">
              <?= e($business['category_name']) ?>
            </span>
            <h1 class="text-2xl font-bold text-gray-900 mt-2"><?= e($business['name']) ?></h1>
          </div>
          <div class="text-right shrink-0">
            <?= stars((float)$business['rating']) ?>
            <p class="text-sm text-gray-500"><?= number_format((float)$business['rating'], 1) ?> / 5.0</p>
          </div>
        </div>

        <?php if ($business['description']): ?>
        <p class="text-gray-600 leading-relaxed"><?= nl2br(e($business['description'])) ?></p>
        <?php endif; ?>

        <?php if ($business['address']): ?>
        <div class="flex items-start gap-2 text-sm text-gray-600">
          <svg class="w-4 h-4 text-gray-400 mt-0.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
          </svg>
          <?= e($business['address']) ?>
        </div>
        <?php endif; ?>

        <?php
        $schedule = $business['schedule'] ? json_decode($business['schedule'], true) : [];
        if ($schedule):
        ?>
        <div class="text-sm text-gray-600">
          <p class="font-medium mb-1 flex items-center gap-1"><svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>Horarios</p>
          <ul class="space-y-1 ml-5">
            <?php foreach ($schedule as $days => $hours): ?>
            <li><span class="font-medium capitalize"><?= e($days) ?>:</span> <?= e($hours) ?></li>
            <?php endforeach; ?>
          </ul>
        </div>
        <?php endif; ?>
      </div>

      <!-- Amenidades -->
      <?php if ($amenities): ?>
      <div id="amenidades" class="bg-white rounded-2xl shadow-sm p-6">
        <h2 class="font-semibold text-gray-900 mb-3">🛎️ Amenidades</h2>
        <div class="flex flex-wrap gap-2">
          <?php foreach ($amenities as $a): ?>
          <span class="inline-flex items-center gap-1.5 text-sm px-3 py-1.5 bg-blue-50 text-blue-700 rounded-full">
            ✓ <?= e($a['name']) ?>
          </span>
          <?php endforeach; ?>
        </div>
      </div>
      <?php endif; ?>

      <!-- Servicios -->
      <?php if ($services): ?>
      <div id="servicios" class="bg-white rounded-2xl shadow-sm p-6">
        <h2 class="font-semibold text-gray-900 mb-4">📋 Servicios</h2>
        <div class="divide-y">
          <?php foreach ($services as $s): ?>
          <div class="py-3 flex justify-between items-start">
            <div>
              <p class="font-medium text-gray-800"><?= e($s['name']) ?></p>
              <?php if ($s['description']): ?><p class="text-sm text-gray-500 mt-0.5"><?= e($s['description']) ?></p><?php endif; ?>
            </div>
            <?php if ($s['price']): ?>
            <span class="text-blue-600 font-semibold text-sm shrink-0 ml-4"><?= formatPrice((float)$s['price']) ?></span>
            <?php endif; ?>
          </div>
          <?php endforeach; ?>
        </div>
      </div>
      <?php endif; ?>

      <!-- Productos -->
      <?php if ($products): ?>
      <div id="productos" class="bg-white rounded-2xl shadow-sm p-6">
        <h2 class="font-semibold text-gray-900 mb-4">🛍️ Productos</h2>
        <div class="grid grid-cols-2 sm:grid-cols-3 gap-4">
          <?php foreach ($products as $p): ?>
          <div class="border border-gray-100 rounded-xl overflow-hidden hover:shadow-md transition">
            <?php if ($p['image']): ?>
            <img src="<?= asset('uploads/' . $p['image']) ?>" alt="<?= e($p['name']) ?>" class="w-full h-32 object-cover">
            <?php else: ?>
            <div class="w-full h-32 bg-gray-100 flex items-center justify-center text-3xl">🛍️</div>
            <?php endif; ?>
            <div class="p-3">
              <p class="font-medium text-sm text-gray-800"><?= e($p['name']) ?></p>
              <?php if ($p['price']): ?>
              <p class="text-blue-600 font-bold text-sm mt-1"><?= formatPrice((float)$p['price']) ?></p>
              <?php endif; ?>
            </div>
          </div>
          <?php endforeach; ?>
        </div>
      </div>
      <?php endif; ?>

      <!-- Eventos -->
      <?php if (!empty($events)): ?>
      <div id="eventos" class="bg-white rounded-2xl shadow-sm p-6">
        <h2 class="font-semibold text-gray-900 mb-4">🎉 Eventos</h2>
        <div class="divide-y">
          <?php foreach ($events as $ev): ?>
          <div class="py-3 flex justify-between items-start">
            <div>
              <p class="font-medium text-gray-800"><?= e($ev['name']) ?></p>
              <?php if ($ev['description']): ?><p class="text-sm text-gray-500 mt-0.5"><?= e($ev['description']) ?></p><?php endif; ?>
              <?php if ($ev['date']): ?><p class="text-xs text-gray-400 mt-1">📅 <?= e(date('d/m/Y H:i', strtotime($ev['date']))) ?></p><?php endif; ?>
            </div>
            <?php if ($ev['price']): ?>
            <span class="text-blue-600 font-semibold text-sm shrink-0 ml-4"><?= formatPrice((float)$ev['price']) ?></span>
            <?php endif; ?>
          </div>
          <?php endforeach; ?>
        </div>
      </div>
      <?php endif; ?>
    </div>

    <!-- Sidebar Contact -->
    <div class="space-y-4 sticky top-20">
      <!-- CTA -->
      <div class="bg-white rounded-2xl shadow-sm p-5 space-y-3">
        <h3 class="font-semibold text-gray-900">Contactar</h3>

        <?php if ($business['whatsapp']): ?>
        <a href="<?= e(waLink($business['whatsapp'], 'Hola, vi tu perfil en Colón Turismo 🗺️')) ?>"
          target="_blank"
          onclick="trackContact('whatsapp_click', <?= (int)$business['id'] ?>)"
          class="flex items-center justify-center gap-2 w-full bg-green-500 hover:bg-green-600 text-white py-3 rounded-xl font-semibold transition">
          <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
          Contactar por WhatsApp
        </a>
        <?php endif; ?>

        <?php if ($business['lat'] && $business['lng']): ?>
        <a href="https://www.google.com/maps/dir/?api=1&destination=<?= (float)$business['lat'] ?>,<?= (float)$business['lng'] ?>"
          target="_blank"
          onclick="trackContact('directions_click', <?= (int)$business['id'] ?>)"
          class="flex items-center justify-center gap-2 w-full bg-orange-500 hover:bg-orange-600 text-white py-3 rounded-xl font-semibold transition">
          🗺️ Cómo llegar (Google Maps)
        </a>
        <a href="waze://?ll=<?= (float)$business['lat'] ?>,<?= (float)$business['lng'] ?>&navigate=yes"
          class="flex items-center justify-center gap-2 w-full border-2 border-blue-600 text-blue-600 py-3 rounded-xl font-semibold hover:bg-blue-50 transition text-sm">
          Abrir en Waze
        </a>
        <?php endif; ?>

        <?php if ($business['phone']): ?>
        <a href="tel:<?= e($business['phone']) ?>"
          class="flex items-center justify-center gap-2 w-full border border-gray-300 text-gray-700 py-3 rounded-xl font-medium hover:bg-gray-50 transition text-sm">
          📞 <?= e($business['phone']) ?>
        </a>
        <?php endif; ?>

        <!-- Social -->
        <?php if ($business['facebook'] || $business['instagram'] || $business['website']): ?>
        <div class="pt-3 border-t space-y-2">
          <?php if ($business['website']): ?>
          <a href="<?= e($business['website']) ?>" target="_blank" rel="noopener"
            class="flex items-center gap-2 text-sm text-blue-600 hover:underline">🌐 Sitio web</a>
          <?php endif; ?>
          <?php if ($business['facebook']): ?>
          <a href="<?= e($business['facebook']) ?>" target="_blank" rel="noopener"
            class="flex items-center gap-2 text-sm text-blue-600 hover:underline">📘 Facebook</a>
          <?php endif; ?>
          <?php if ($business['instagram']): ?>
          <a href="<?= e($business['instagram']) ?>" target="_blank" rel="noopener"
            class="flex items-center gap-2 text-sm text-blue-600 hover:underline">📸 Instagram</a>
          <?php endif; ?>
        </div>
        <?php endif; ?>
      </div>

      <!-- Mini Map -->
      <?php if ($business['lat'] && $business['lng']): ?>
      <div class="bg-white rounded-2xl shadow-sm p-4">
        <h3 class="font-semibold text-gray-900 mb-3">Ubicación</h3>
        <div id="mini-map" class="w-full h-48 rounded-xl overflow-hidden"></div>
      </div>
      <?php endif; ?>
    </div>
  </div>
</main>

<?php require APP_PATH . '/views/layout/bottom_nav.php'; ?>
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
<?php if ($business['lat'] && $business['lng']): ?>
const miniMap = L.map('mini-map', { zoomControl: false, scrollWheelZoom: false })
  .setView([<?= (float)$business['lat'] ?>, <?= (float)$business['lng'] ?>], 15);
L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png').addTo(miniMap);
L.marker([<?= (float)$business['lat'] ?>, <?= (float)$business['lng'] ?>])
  .addTo(miniMap)
  .bindPopup('<?= addslashes(e($business['name'])) ?>').openPopup();
<?php endif; ?>

function trackContact(event, businessId) {
  fetch('<?= BASE_URL ?>/api/analitica', {
    method: 'POST',
    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
    body: `event=${event}&business_id=${businessId}`,
  });
}
</script>
<?php require APP_PATH . '/views/layout/footer.php'; ?>
