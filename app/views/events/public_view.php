<?php
$returnTo = trim((string)($_GET['return_to'] ?? ''));
$viewer = currentUser();
$backUrl = preg_match('/^(landing\/)?turista(\/perfil)?$/', $returnTo)
  ? url($returnTo)
  : url(routePrefix() . (($viewer['role'] ?? '') === 'visitor' ? 'turista' : 'mapa'));
$pageTitle = e($event['title']) . ' – ' . APP_NAME;
require APP_PATH . '/views/layout/head.php';
?>
<?php require APP_PATH . '/views/layout/navbar.php'; ?>

<main class="max-w-3xl mx-auto px-4 py-8 mb-20">
  <div class="mb-4">
    <a href="<?= e($backUrl) ?>" class="inline-flex items-center gap-2 rounded-xl bg-white px-4 py-2 text-sm font-bold text-gray-700 border border-gray-200 shadow-sm hover:bg-gray-50 transition">
      <span aria-hidden="true">&larr;</span>
      Volver
    </a>
  </div>

  <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
    <?php if ($event['image']): ?>
    <img src="<?= imageUrl($event['image']) ?>" alt="<?= e($event['title']) ?>" class="w-full h-64 object-cover">
    <?php else: ?>
    <div class="w-full h-48 bg-gradient-to-br from-purple-100 to-blue-100 flex items-center justify-center text-6xl">
      🎉
    </div>
    <?php endif; ?>
    <div class="p-6">
      <div class="flex items-center gap-2 mb-3">
        <span class="text-xs px-2 py-0.5 rounded-full font-medium bg-green-50 text-green-700">Evento</span>
        <span class="text-xs text-gray-400"><?= $event['start_date'] ? date('d/m/Y', strtotime($event['start_date'])) : 'Vigente' ?></span>
      </div>
      <h1 class="text-2xl font-bold text-gray-900 mb-3"><?= e($event['title']) ?></h1>
      <?php if ($event['description']): ?>
      <p class="text-gray-600 mb-4" style="white-space: pre-wrap;"><?= e($event['description']) ?></p>
      <?php endif; ?>
      <?php if ($event['price']): ?>
      <p class="text-2xl font-bold text-blue-600 mb-4">$<?= number_format((float)$event['price'], 2) ?></p>
      <?php endif; ?>
      <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-sm text-gray-600 mb-4">
        <?php if (!empty($event['presale_price'])): ?>
        <p><strong>Preventa:</strong> $<?= number_format((float)$event['presale_price'], 2) ?></p>
        <?php endif; ?>
        <?php if (!empty($event['validity'])): ?>
        <p><strong>Vigencia:</strong> <?= e($event['validity']) ?></p>
        <?php endif; ?>
        <?php if (!empty($event['location'])): ?>
        <p><strong>Ubicacion:</strong> <?= e($event['location']) ?></p>
        <?php endif; ?>
        <?php if (!empty($event['whatsapp'])): ?>
        <p><strong>WhatsApp info:</strong>
          <a href="<?= e(waLink($event['whatsapp'], 'Hola, me interesa el evento: ' . $event['title'])) ?>" target="_blank" class="text-green-600 hover:underline">
            <?= e($event['whatsapp']) ?>
          </a>
        </p>
        <?php endif; ?>
        <?php if (!empty($event['capacity'])): ?>
        <p><strong>Aforo:</strong> <?= (int)$event['capacity'] ?></p>
        <?php endif; ?>
        <?php if (!empty($event['end_date'])): ?>
        <p><strong>Fecha fin:</strong> <?= date('d/m/Y H:i', strtotime($event['end_date'])) ?></p>
        <?php endif; ?>
      </div>
      <?php if ($event['conditions']): ?>
      <div class="bg-gray-50 rounded-xl p-4 mb-4">
        <h3 class="font-semibold text-gray-900 text-sm mb-2">📋 Restricciones del Evento</h3>
        <p class="text-sm text-gray-600" style="white-space: pre-wrap;"><?= e($event['conditions']) ?></p>
      </div>
      <?php endif; ?>
      
      <!-- Business Info -->
      <?php if ($business): ?>
      <div class="border-t pt-4 mt-4">
        <h3 class="font-semibold text-gray-900 text-sm mb-3">📍 Datos del Negocio</h3>
        <div class="space-y-2 text-sm text-gray-600">
          <p><strong>Nombre:</strong> <?= e($business['name']) ?></p>
          <?php if ($business['address']): ?>
          <p><strong>Dirección:</strong> <?= e($business['address']) ?></p>
          <?php endif; ?>
          <?php if ($business['whatsapp']): ?>
          <p><strong>WhatsApp:</strong> 
            <a href="https://wa.me/<?= e(preg_replace('/\D/', '', $business['whatsapp'])) ?>" target="_blank" class="text-green-600 hover:underline">
              <?= e($business['whatsapp']) ?>
            </a>
          </p>
          <?php endif; ?>
          <?php if ($business['lat'] && $business['lng']): ?>
          <p><strong>Ubicación:</strong> 
            <a href="https://www.google.com/maps/dir/?api=1&destination=<?= $business['lat'] ?>,<?= $business['lng'] ?>" target="_blank" class="text-blue-600 hover:underline">
              Ver en Google Maps
            </a>
          </p>
          <?php elseif (!empty($business['google_maps_link'])): ?>
          <p><strong>Ubicacion:</strong>
            <a href="<?= e($business['google_maps_link']) ?>" target="_blank" class="text-blue-600 hover:underline">
              Ver en Google Maps
            </a>
          </p>
          <?php endif; ?>
        </div>
        <?php $eventWhatsapp = $event['whatsapp'] ?: ($business['whatsapp'] ?? ''); ?>
        <?php if ($eventWhatsapp): ?>
        <a href="<?= e(waLink($eventWhatsapp, 'Hola, me interesa el evento: ' . $event['title'])) ?>" target="_blank"
          class="mt-4 inline-flex items-center gap-2 bg-green-500 text-white px-6 py-3 rounded-xl text-sm font-semibold hover:bg-green-600 transition">
          💬 Contactar por WhatsApp
        </a>
        <?php endif; ?>
      </div>
      <?php elseif (!empty($event['whatsapp'])): ?>
      <div class="border-t pt-4 mt-4">
        <a href="<?= e(waLink($event['whatsapp'], 'Hola, me interesa el evento: ' . $event['title'])) ?>" target="_blank"
          class="inline-flex items-center gap-2 bg-green-500 text-white px-6 py-3 rounded-xl text-sm font-semibold hover:bg-green-600 transition">
          💬 Contactar por WhatsApp
        </a>
      </div>
      <?php endif; ?>
    </div>
  </div>
</main>

<?php require APP_PATH . '/views/layout/footer.php'; ?>
