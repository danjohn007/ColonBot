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
          <input type="tel" name="whatsapp" value="<?= e($profile['whatsapp'] ?? '') ?>" class="input w-full px-4 py-2.5 border border-gray-300 rounded-xl text-sm" placeholder="521442100000">
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

  <!-- Top Visited & Recommended -->
  <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 mb-6">
    <h2 class="font-bold text-gray-900 text-lg mb-4">🏆 Lugares más visitados y recomendados</h2>
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
      <?php foreach ($topVisited as $b): ?>
      <a href="<?= url('lugar/' . $b['slug']) ?>" class="flex items-center gap-3 p-3 border border-gray-100 rounded-xl hover:bg-gray-50 transition">
        <img src="<?= $b['cover_image'] ? imageUrl($b['cover_image']) : asset('img/placeholder.svg') ?>" class="w-14 h-14 object-cover rounded-lg shrink-0">
        <div class="min-w-0">
          <p class="font-semibold text-gray-900 text-sm truncate"><?= e($b['name']) ?></p>
          <div class="flex items-center gap-1 text-yellow-400 text-xs">
            <?= str_repeat('★', max(1, min(5, round((float)$b['rating'])))) . str_repeat('☆', max(0, 5 - round((float)$b['rating']))) ?>
            <span class="text-gray-400 ml-1"><?= number_format((float)$b['rating'], 1) ?></span>
          </div>
        </div>
      </a>
      <?php endforeach; ?>
    </div>
  </div>

  <!-- Emergency Numbers -->
  <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 mb-6">
    <h2 class="font-bold text-gray-900 text-lg mb-4">🆘 Números de emergencia</h2>
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
      <?php foreach ($emergencyNumbers as $e): ?>
      <a href="tel:<?= e($e['phone']) ?>" class="flex items-center gap-3 p-3 border border-red-100 rounded-xl hover:bg-red-50 transition">
        <span class="w-10 h-10 rounded-full bg-red-100 flex items-center justify-center text-red-600 text-lg">📞</span>
        <div>
          <p class="font-semibold text-gray-900 text-sm"><?= e($e['name']) ?></p>
          <p class="text-red-600 text-sm font-medium"><?= e($e['phone']) ?></p>
          <?php if ($e['description']): ?>
          <p class="text-gray-400 text-xs"><?= e($e['description']) ?></p>
          <?php endif; ?>
        </div>
      </a>
      <?php endforeach; ?>
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

<script>
function toggleProfileForm() {
  const form = document.getElementById('profile-form');
  form.classList.toggle('hidden');
}
</script>

<style>
  .label { @apply block text-sm font-medium text-gray-700 mb-1; }
  .input  { @apply w-full px-4 py-2.5 border border-gray-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition; }
</style>
<?php require APP_PATH . '/views/layout/bottom_nav.php'; ?>
<?php require APP_PATH . '/views/layout/footer.php'; ?>