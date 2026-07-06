<?php
$pageTitle = e($promo['title']) . ' – ' . APP_NAME;
require APP_PATH . '/views/layout/head.php';
?>
<?php require APP_PATH . '/views/layout/navbar.php'; ?>

<main class="max-w-3xl mx-auto px-4 py-8 mb-20">
  <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
    <?php if ($promo['image']): ?>
    <img src="<?= imageUrl($promo['image']) ?>" alt="<?= e($promo['title']) ?>" class="w-full h-64 object-cover">
    <?php else: ?>
    <div class="w-full h-48 bg-gradient-to-br from-purple-100 to-blue-100 flex items-center justify-center text-6xl">
      🎉
    </div>
    <?php endif; ?>
    <div class="p-6">
      <div class="flex items-center gap-2 mb-3">
        <span class="text-xs px-2 py-0.5 rounded-full font-medium bg-green-50 text-green-700"><?= e($promo['type']) ?></span>
        <span class="text-xs text-gray-400"><?= $promo['start_date'] ? date('d/m/Y', strtotime($promo['start_date'])) : 'Vigente' ?></span>
      </div>
      <h1 class="text-2xl font-bold text-gray-900 mb-3"><?= e($promo['title']) ?></h1>
      <?php if ($promo['description']): ?>
      <p class="text-gray-600 mb-4" style="white-space: pre-wrap;"><?= e($promo['description']) ?></p>
      <?php endif; ?>
      <?php if ($promo['price'] || $promo['presale_price']): ?>
      <div class=\"grid grid-cols-2 gap-4 mb-4\">
        <?php if ($promo['price']): ?>
        <div class=\"bg-gray-50 rounded-lg p-3\">
          <p class=\"text-xs text-gray-500 font-semibold mb-1\">PRECIO DE LISTA</p>
          <p class=\"text-xl font-bold line-through text-gray-400\">$<?= number_format((float)$promo['price'], 2) ?></p>
        </div>
        <?php endif; ?>
        <?php if ($promo['presale_price']): ?>
        <div class=\"bg-green-50 rounded-lg p-3\">
          <p class=\"text-xs text-green-600 font-semibold mb-1\">PRECIO PROMOCIONAL</p>
          <p class=\"text-xl font-bold text-green-600\">$<?= number_format((float)$promo['presale_price'], 2) ?></p>
        </div>
        <?php endif; ?>
      </div>
      <?php endif; ?>
      <?php if ($promo['conditions']): ?>
      <div class="bg-gray-50 rounded-xl p-4 mb-4">
        <h3 class="font-semibold text-gray-900 text-sm mb-2">📋 Condiciones</h3>
        <p class="text-sm text-gray-600" style="white-space: pre-wrap;"><?= e($promo['conditions']) ?></p>
      </div>
      <?php endif; ?>
      <!-- Vigencia -->
      <?php if ($promo['start_date'] || $promo['end_date']): ?>
      <div class=\"bg-blue-50 rounded-xl p-4 mb-4\">
        <h3 class=\"font-semibold text-gray-900 text-sm mb-2\">⏳ Vigencia</h3>
        <div class=\"text-sm text-gray-600\">
          <?php if ($promo['start_date']): ?>
          <p>Inicia: <strong><?= date('d/m/Y', strtotime($promo['start_date'])) ?></strong></p>
          <?php endif; ?>
          <?php if ($promo['end_date']): ?>
          <p>Finaliza: <strong><?= date('d/m/Y', strtotime($promo['end_date'])) ?></strong></p>
          <?php endif; ?>
        </div>
      </div>
      <?php endif; ?>      
      <!-- Métricas -->
      <div class="flex gap-4 text-sm text-gray-500 border-t pt-4 mt-4">
        <span>👁️ Vistas: <?= $viewCount ?? 0 ?></span>
        <span>📋 Solicitudes: <?= $inquiryCount ?? 0 ?></span>
      </div>

      <!-- Formulario de solicitud -->
      <div class="mt-6 p-4 bg-blue-50 rounded-xl border border-blue-100">
        <h3 class="font-semibold text-gray-900 text-sm mb-3">📩 Solicitar información</h3>
        <form onsubmit="sendInquiry(event, <?= $promo['id'] ?>)" class="space-y-3">
          <div class="grid grid-cols-2 gap-3">
            <input type="text" id="inq-name" required placeholder="Nombre" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
            <input type="tel" id="inq-phone" placeholder="Teléfono" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
          </div>
          <input type="email" id="inq-email" placeholder="Email" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
          <textarea id="inq-message" rows="2" placeholder="Mensaje" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm"></textarea>
          <button type="submit" class="w-full bg-blue-600 text-white py-2 rounded-lg text-sm font-semibold hover:bg-blue-700 transition">
            Enviar solicitud
          </button>
        </form>
      </div>
    </div>
  </div>
</main>

<script>
const BASE_URL = '<?= BASE_URL ?>';
function sendInquiry(e, id) {
  e.preventDefault();
  const body = new URLSearchParams({
    name: document.getElementById('inq-name').value.trim(),
    phone: document.getElementById('inq-phone').value.trim(),
    email: document.getElementById('inq-email').value.trim(),
    message: document.getElementById('inq-message').value.trim(),
  });
  fetch(`${BASE_URL}/promocion/${id}/solicitar`, {
    method: 'POST',
    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
    body: body.toString(),
  })
  .then(r => r.json())
  .then(d => {
    if (d.ok) {
      alert(d.message || 'Solicitud enviada exitosamente');
      document.querySelector('#inq-name').value = '';
      document.querySelector('#inq-phone').value = '';
      document.querySelector('#inq-email').value = '';
      document.querySelector('#inq-message').value = '';
    } else {
      alert(d.error || 'Error al enviar');
    }
  });
}
</script>
<?php require APP_PATH . '/views/layout/footer.php'; ?>
</write_to_file>