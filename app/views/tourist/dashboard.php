<?php
$pageTitle = 'Turista – ' . APP_NAME;
require APP_PATH . '/views/layout/head.php';
?>
<?php require APP_PATH . '/views/layout/navbar.php'; ?>

<main class="max-w-6xl mx-auto px-4 py-8 mb-24">
  <!-- Profile Section / Registration -->
  <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 mb-6">
    <?php if ($user): ?>
    <div class="flex items-center justify-between mb-4">
      <h1 class="text-2xl font-bold text-gray-900">👋 ¡Hola, <?= e($user['name']) ?>!</h1>
      <button onclick="toggleProfileForm()" class="text-sm text-blue-600 hover:underline">Editar perfil</button>
    </div>

    <form id="profile-form" method="POST" action="<?= url('turista/registrar') ?>" class="hidden space-y-4">
      <input type="hidden" name="_csrf" value="<?= e($csrf ?? '') ?>">
      <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div>
          <label class="label block text-sm font-medium text-gray-700 mb-1">Nombre</label>
          <input type="text" name="name" value="<?= e($user['name']) ?>" class="input w-full px-4 py-2.5 border border-gray-300 rounded-xl text-sm">
        </div>
        <div>
          <label class="label block text-sm font-medium text-gray-700 mb-1">WhatsApp</label>
          <input type="tel" name="whatsapp" value="<?= e($user['phone'] ?? '') ?>" class="input w-full px-4 py-2.5 border border-gray-300 rounded-xl text-sm" placeholder="521442100000">
        </div>
        <div>
          <label class="label block text-sm font-medium text-gray-700 mb-1">Email</label>
          <input type="email" name="email" value="<?= e($user['email']) ?>" class="input w-full px-4 py-2.5 border border-gray-300 rounded-xl text-sm">
        </div>
      </div>
      <button type="submit" class="bg-blue-600 text-white px-6 py-2.5 rounded-xl text-sm font-medium hover:bg-blue-700 transition">
        Guardar cambios
      </button>
    </form>
    <?php else: ?>
    <div class="text-center mb-4">
      <h1 class="text-2xl font-bold text-gray-900">👋 ¡Bienvenido a la Plataforma Turística de Colón!</h1>
      <p class="text-gray-500 mt-2">Explora los mejores lugares, valora servicios y contacta directamente con los prestadores.</p>
      <a href="<?= url('login') ?>" class="mt-4 inline-block bg-blue-600 text-white px-6 py-2.5 rounded-xl text-sm font-medium hover:bg-blue-700 transition">
        Iniciar sesión / Registrarse
      </a>
    </div>
    <?php endif; ?>
  </div>

  <!-- A) Lugares disponibles en el mapa -->
  <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 mb-6">
    <div class="flex items-center justify-between mb-4">
      <h2 class="font-bold text-gray-900 text-lg">🗺️ Lugares disponibles</h2>
      <a href="<?= url('mapa') ?>" class="text-sm text-blue-600 hover:underline">Ver en mapa →</a>
    </div>
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4" id="places-grid">
      <?php foreach ($topVisited as $b): ?>
      <div class="border border-gray-100 rounded-xl p-3 hover:bg-gray-50 transition">
        <a href="<?= url('lugar/' . $b['slug']) ?>">
          <img src="<?= $b['cover_image'] ? imageUrl($b['cover_image']) : asset('img/placeholder.svg') ?>" class="w-full h-28 object-cover rounded-lg mb-2">
          <p class="font-semibold text-gray-900 text-sm truncate"><?= e($b['name']) ?></p>
          <div class="flex items-center gap-1 text-yellow-400 text-xs">
            <?= str_repeat('★', max(1, min(5, round((float)$b['rating'])))) . str_repeat('☆', max(0, 5 - round((float)$b['rating']))) ?>
            <span class="text-gray-400 ml-1"><?= number_format((float)$b['rating'], 1) ?></span>
          </div>
          <span class="text-xs text-gray-400"><?= e($b['category_name'] ?? '') ?></span>
        </a>
        <!-- B) Calificar -->
        <button onclick="openReviewModal(<?= $b['id'] ?>, '<?= e($b['name']) ?>')" class="mt-2 w-full text-xs px-3 py-1.5 bg-yellow-50 text-yellow-700 rounded-lg hover:bg-yellow-100 transition">
          ⭐ Calificar
        </button>
      </div>
      <?php endforeach; ?>
    </div>
  </div>

  <!-- C) Historial de lugares visitados -->
  <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 mb-6">
    <h2 class="font-bold text-gray-900 text-lg mb-4">📍 Mi historial de lugares visitados</h2>
    <div id="visit-history">
      <?php if (!empty($visitHistory)): ?>
      <div class="space-y-2">
        <?php foreach ($visitHistory as $vh): ?>
        <a href="<?= url('lugar/' . $vh['slug']) ?>" class="flex items-center gap-3 p-3 border border-gray-100 rounded-xl hover:bg-gray-50 transition">
          <span class="text-lg">📍</span>
          <div class="flex-1 min-w-0">
            <p class="font-medium text-gray-900 text-sm truncate"><?= e($vh['name']) ?></p>
            <p class="text-xs text-gray-400">Visitado: <?= date('d/m/Y', strtotime($vh['visited_at'] ?? $vh['created_at'])) ?></p>
          </div>
        </a>
        <?php endforeach; ?>
      </div>
      <?php else: ?>
      <p class="text-sm text-gray-400 text-center py-4">Aún no has visitado ningún lugar. ¡Explora el mapa turístico!</p>
      <?php endif; ?>
    </div>
  </div>

  <!-- D) Notificaciones y promociones -->
  <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 mb-6">
    <h2 class="font-bold text-gray-900 text-lg mb-4">🔔 Notificaciones y promociones</h2>
    <div id="notifications-list">
      <?php if (!empty($notifications)): ?>
      <div class="space-y-3">
        <?php foreach ($notifications as $n): ?>
        <div class="flex items-start gap-3 p-3 border border-gray-100 rounded-xl <?= $n['read_at'] ? '' : 'bg-blue-50 border-blue-100' ?>">
          <span class="text-lg"><?= $n['type'] === 'promotion' ? '🏷️' : '📢' ?></span>
          <div class="flex-1 min-w-0">
            <p class="font-medium text-gray-900 text-sm"><?= e($n['title']) ?></p>
            <p class="text-xs text-gray-500"><?= e(mb_substr($n['message'] ?? '', 0, 200)) ?></p>
            <p class="text-xs text-gray-400 mt-1"><?= date('d/m/Y H:i', strtotime($n['created_at'])) ?></p>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
      <?php else: ?>
      <p class="text-sm text-gray-400 text-center py-4">No hay notificaciones nuevas.</p>
      <?php endif; ?>
    </div>
  </div>

  <!-- Active Promotions -->
  <?php if (!empty($activePromotions)): ?>
  <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
    <h2 class="font-bold text-gray-900 text-lg mb-4">🎉 Promociones activas</h2>
    <div class="space-y-3">
      <?php foreach ($activePromotions as $p): ?>
      <div class="flex items-start gap-4 p-4 border border-gray-100 rounded-xl">
        <?php if ($p['image']): ?>
        <img src="<?= imageUrl($p['image']) ?>" class="w-16 h-16 object-cover rounded-lg shrink-0">
        <?php endif; ?>
        <div class="flex-1 min-w-0">
          <h3 class="font-semibold text-gray-900"><?= e($p['title']) ?></h3>
          <p class="text-sm text-gray-500"><?= e(mb_substr($p['description'] ?? '', 0, 150)) ?></p>
          <?php if ($p['public_url']): ?>
          <a href="<?= e($p['public_url']) ?>" target="_blank" class="text-blue-600 text-sm hover:underline mt-1 inline-block">Más información →</a>
          <?php endif; ?>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
  <?php endif; ?>
</main>

<!-- Review Modal -->
<div id="review-modal" class="fixed inset-0 z-50 hidden bg-black bg-opacity-40 flex items-center justify-center px-4">
  <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md max-h-[90vh] overflow-y-auto p-6 relative">
    <button onclick="closeReviewModal()" class="absolute top-4 right-4 text-gray-400 hover:text-gray-600">✕</button>
    <h2 class="text-lg font-bold text-gray-900 mb-1">⭐ Calificar negocio</h2>
    <p class="text-sm text-gray-500 mb-4" id="review-business-name"></p>
    <form onsubmit="submitReview(event)" class="space-y-4">
      <input type="hidden" id="review-business-id" value="">
      <div>
        <label class="label block text-sm font-medium text-gray-700 mb-1">Tu calificación</label>
        <div class="flex gap-1 text-2xl" id="star-rating">
          <span onclick="setRating(1)" class="cursor-pointer text-gray-300 hover:text-yellow-400 transition star" data-star="1">★</span>
          <span onclick="setRating(2)" class="cursor-pointer text-gray-300 hover:text-yellow-400 transition star" data-star="2">★</span>
          <span onclick="setRating(3)" class="cursor-pointer text-gray-300 hover:text-yellow-400 transition star" data-star="3">★</span>
          <span onclick="setRating(4)" class="cursor-pointer text-gray-300 hover:text-yellow-400 transition star" data-star="4">★</span>
          <span onclick="setRating(5)" class="cursor-pointer text-gray-300 hover:text-yellow-400 transition star" data-star="5">★</span>
        </div>
        <input type="hidden" id="review-rating" value="5">
      </div>
      <div>
        <label class="label block text-sm font-medium text-gray-700 mb-1">Comentario</label>
        <textarea id="review-comment" rows="3" class="input w-full px-4 py-2.5 border border-gray-300 rounded-xl text-sm" placeholder="Escribe tu opinión sobre este lugar..."></textarea>
      </div>
      <button type="submit" class="w-full bg-blue-600 text-white py-3 rounded-xl font-medium hover:bg-blue-700 transition">
        Enviar valoración
      </button>
    </form>
  </div>
</div>

<script>
const CSRF = '<?= e($csrf ?? '') ?>';
const BASE_URL = '<?= BASE_URL ?>';

function toggleProfileForm() {
  const form = document.getElementById('profile-form');
  form.classList.toggle('hidden');
}

let selectedRating = 5;

function setRating(val) {
  selectedRating = val;
  document.getElementById('review-rating').value = val;
  document.querySelectorAll('.star').forEach(s => {
    const starVal = parseInt(s.dataset.star);
    s.classList.toggle('text-yellow-400', starVal <= val);
    s.classList.toggle('text-gray-300', starVal > val);
  });
}

function openReviewModal(businessId, businessName) {
  document.getElementById('review-business-id').value = businessId;
  document.getElementById('review-business-name').textContent = businessName;
  document.getElementById('review-comment').value = '';
  setRating(5);
  document.getElementById('review-modal').classList.remove('hidden');
}

function closeReviewModal() {
  document.getElementById('review-modal').classList.add('hidden');
}

function submitReview(e) {
  e.preventDefault();
  const businessId = document.getElementById('review-business-id').value;
  const rating = document.getElementById('review-rating').value;
  const comment = document.getElementById('review-comment').value.trim();

  const body = new URLSearchParams({
    _csrf: CSRF,
    business_id: businessId,
    rating,
    comment
  });

  fetch(`${BASE_URL}/turista/valorar`, {
    method: 'POST',
    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
    body: body.toString(),
  })
  .then(r => r.json())
  .then(d => {
    if (d.ok || d.success) {
      closeReviewModal();
      alert('¡Gracias por tu valoración!');
    } else {
      alert(d.error || 'Error al enviar valoración');
    }
  })
  .catch(() => alert('Error al enviar valoración'));
}
</script>

<style>
  .label { @apply block text-sm font-medium text-gray-700 mb-1; }
  .input  { @apply w-full px-4 py-2.5 border border-gray-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition; }
</style>
<?php require APP_PATH . '/views/layout/bottom_nav.php'; ?>
<?php require APP_PATH . '/views/layout/footer.php'; ?>
