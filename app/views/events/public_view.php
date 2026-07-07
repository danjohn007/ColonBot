<?php
$pageTitle = ($event['title'] ?? 'Evento') . ' – ' . APP_NAME;
$ogImage = $event['image'] ? imageUrl($event['image']) : '';
require APP_PATH . '/views/layout/head.php';
?>
<style>
  body { background: #f9fafb; }
  .event-hero {
    background: linear-gradient(135deg, #1e3a5f 0%, #2d5a87 50%, #1a2744 100%);
    color: white;
    padding: 3rem 1rem;
    text-align: center;
  }
  .event-card {
    background: white;
    border-radius: 1rem;
    box-shadow: 0 1px 3px rgba(0,0,0,0.1);
    overflow: hidden;
  }
  .whatsapp-btn {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    background: #25D366;
    color: white;
    padding: 0.75rem 1.5rem;
    border-radius: 0.75rem;
    font-weight: 600;
    font-size: 1rem;
    transition: all 0.2s;
    text-decoration: none;
  }
  .whatsapp-btn:hover { background: #1DA851; transform: translateY(-1px); }
</style>

<div class="event-hero">
  <div class="max-w-4xl mx-auto">
    <?php if ($event['image']): ?>
    <img src="<?= imageUrl($event['image']) ?>" alt="<?= e($event['title']) ?>" class="w-48 h-48 object-cover rounded-2xl mx-auto mb-6 shadow-lg">
    <?php endif; ?>
    <h1 class="text-3xl md:text-4xl font-bold mb-3"><?= e($event['title']) ?></h1>
    <?php if ($event['start_date']): ?>
    <p class="text-lg opacity-90">📅 <?= date('d/m/Y H:i', strtotime($event['start_date'])) ?></p>
    <?php endif; ?>
    <?php if ($event['location']): ?>
    <p class="text-lg opacity-90">📍 <?= e($event['location']) ?></p>
    <?php endif; ?>
  </div>
</div>

<main class="max-w-4xl mx-auto px-4 py-8 mb-24">
  <!-- Description -->
  <?php if ($event['description']): ?>
  <div class="event-card p-6 mb-6">
    <h2 class="font-bold text-gray-900 text-lg mb-3">📝 Descripción</h2>
    <p class="text-gray-700 leading-relaxed"><?= nl2br(e($event['description'])) ?></p>
  </div>
  <?php endif; ?>

  <!-- Pricing -->
  <div class="event-card p-6 mb-6">
    <h2 class="font-bold text-gray-900 text-lg mb-3">💰 Precios</h2>
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
      <?php if ($event['price']): ?>
      <div class="bg-blue-50 rounded-xl p-4 text-center">
        <p class="text-sm text-gray-500">Precio general</p>
        <p class="text-2xl font-bold text-blue-700">$<?= number_format((float)$event['price'], 2) ?></p>
      </div>
      <?php endif; ?>
      <?php if ($event['presale_price']): ?>
      <div class="bg-green-50 rounded-xl p-4 text-center">
        <p class="text-sm text-gray-500">Preventa</p>
        <p class="text-2xl font-bold text-green-700">$<?= number_format((float)$event['presale_price'], 2) ?></p>
      </div>
      <?php endif; ?>
    </div>
    <?php if ($event['presale_start'] && $event['presale_end']): ?>
    <p class="text-xs text-gray-400 mt-3 text-center">
      Preventa vigente del <?= date('d/m/Y', strtotime($event['presale_start'])) ?> al <?= date('d/m/Y', strtotime($event['presale_end'])) ?>
    </p>
    <?php endif; ?>
    <?php if ($event['capacity']): ?>
    <p class="text-xs text-gray-400 mt-2 text-center">� Aforo: <?= (int)$event['capacity'] ?> personas</p>
    <?php endif; ?>
    <?php if ($event['validity']): ?>
    <p class="text-xs text-gray-400 mt-2 text-center">⏳ Vigencia: <?= e($event['validity']) ?></p>
    <?php endif; ?>
  </div>

  <!-- Business Info -->
  <?php if ($business): ?>
  <div class="event-card p-6 mb-6">
    <h2 class="font-bold text-gray-900 text-lg mb-3">🏪 Datos del negocio</h2>
    <div class="space-y-2">
      <p class="text-gray-800"><strong>Nombre:</strong> <?= e($business['name']) ?></p>
      <?php if ($business['address']): ?>
      <p class="text-gray-600"><strong>Dirección:</strong> <?= e($business['address']) ?></p>
      <?php endif; ?>
      <?php if ($business['whatsapp']): ?>
      <p class="text-gray-600"><strong>WhatsApp:</strong> <a href="https://wa.me/<?= preg_replace('/\D/', '', $business['whatsapp']) ?>" target="_blank" class="text-green-600 hover:underline"><?= e($business['whatsapp']) ?></a></p>
      <?php endif; ?>
      <?php if ($business['lat'] && $business['lng']): ?>
      <p class="text-gray-600"><strong>Ubicación en mapa:</strong> <a href="https://www.google.com/maps?q=<?= $business['lat'] ?>,<?= $business['lng'] ?>" target="_blank" class="text-blue-600 hover:underline">Ver en Google Maps →</a></p>
      <?php endif; ?>
    </div>
  </div>
  <?php endif; ?>

  <!-- WhatsApp Button -->
  <?php if ($business && $business['whatsapp']): ?>
  <div class="text-center">
    <a href="https://wa.me/<?= preg_replace('/\D/', '', $business['whatsapp']) ?>?text=<?= urlencode('Hola, me interesa el evento: ' . $event['title']) ?>" target="_blank" class="whatsapp-btn">
      <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
      Contactar por WhatsApp
    </a>
  </div>
  <?php endif; ?>
</main>
<?php require APP_PATH . '/views/layout/footer.php'; ?>
</final_file_content>

IMPORTANT: For any future changes to this file, use the final_file_content shown above as your reference. This content reflects the current state of the file, including any auto-formatting (e.g., if you used single quotes but the formatter converted them to double quotes). Always base your search_files operations on this final version to ensure accuracy.