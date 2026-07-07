<?php
$pageTitle = 'Visitante - ' . APP_NAME;
$phone = $user['phone'] ?? '';
require APP_PATH . '/views/layout/head.php';
?>
<?php require APP_PATH . '/views/layout/navbar.php'; ?>

<main class="max-w-6xl mx-auto px-4 py-8 mb-24">
  <section class="bg-gradient-to-br from-orange-600 to-orange-500 rounded-2xl shadow-sm p-6 mb-6 text-white">
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
      <div>
        <p class="text-sm font-semibold uppercase tracking-wide text-orange-100">Panel de visitante</p>
        <h1 class="text-2xl md:text-3xl font-bold mt-1">Hola, <?= e($user['name']) ?></h1>
        <p class="text-orange-50 mt-2 max-w-2xl">Explora lugares, guarda tu historial de visitas, comenta negocios y revisa promociones o eventos disponibles.</p>
      </div>
      <div class="flex flex-wrap gap-2">
        <a href="<?= url('mapa') ?>" class="inline-flex items-center justify-center px-4 py-2 rounded-xl bg-white text-orange-700 text-sm font-semibold hover:bg-orange-50 transition">Ver mapa</a>
        <a href="<?= url('admin/notificaciones') ?>" class="inline-flex items-center justify-center px-4 py-2 rounded-xl bg-orange-700/40 text-white text-sm font-semibold hover:bg-orange-700 transition">Notificaciones</a>
      </div>
    </div>
  </section>

  <section class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 mb-6">
    <div class="flex items-center justify-between gap-4 mb-4">
      <h2 class="font-bold text-gray-900 text-lg">Mi perfil</h2>
      <button onclick="toggleProfileForm()" class="text-sm text-orange-600 font-semibold hover:underline">Editar</button>
    </div>
    <form id="profile-form" method="POST" action="<?= url('turista/registrar') ?>" class="hidden space-y-4">
      <input type="hidden" name="_csrf" value="<?= e($csrf ?? '') ?>">
      <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <label class="block">
          <span class="text-sm font-medium text-gray-700 mb-1 block">Nombre</span>
          <input type="text" name="name" value="<?= e($user['name']) ?>" class="w-full px-4 py-2.5 border border-gray-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-orange-500">
        </label>
        <label class="block">
          <span class="text-sm font-medium text-gray-700 mb-1 block">WhatsApp</span>
          <input type="tel" name="whatsapp" value="<?= e($phone) ?>" class="w-full px-4 py-2.5 border border-gray-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-orange-500" placeholder="5214421000000">
        </label>
        <label class="block">
          <span class="text-sm font-medium text-gray-700 mb-1 block">Email</span>
          <input type="email" name="email" value="<?= e($user['email']) ?>" class="w-full px-4 py-2.5 border border-gray-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-orange-500">
        </label>
      </div>
      <button type="submit" class="bg-orange-600 text-white px-6 py-2.5 rounded-xl text-sm font-semibold hover:bg-orange-700 transition">Guardar cambios</button>
    </form>
  </section>

  <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
    <section class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
      <h2 class="font-bold text-gray-900 text-lg mb-4">Promociones activas</h2>
      <div class="space-y-3">
        <?php if (empty($activePromotions)): ?>
        <p class="text-sm text-gray-500">Aún no hay promociones activas.</p>
        <?php endif; ?>
        <?php foreach (array_slice($activePromotions, 0, 6) as $p): ?>
        <a href="<?= e($p['public_url'] ?: url('promocion/' . (int)$p['id'])) ?>" class="flex gap-3 p-3 border border-gray-100 rounded-xl hover:bg-orange-50 transition">
          <img src="<?= $p['image'] ? imageUrl($p['image']) : asset('img/placeholder.svg') ?>" alt="" class="w-16 h-16 object-cover rounded-lg shrink-0">
          <div class="min-w-0">
            <p class="font-semibold text-gray-900 text-sm truncate"><?= e($p['title']) ?></p>
            <p class="text-xs text-gray-500 line-clamp-2"><?= e($p['description'] ?? '') ?></p>
            <?php if (!empty($p['business_name'])): ?><p class="text-xs text-orange-600 mt-1"><?= e($p['business_name']) ?></p><?php endif; ?>
          </div>
        </a>
        <?php endforeach; ?>
      </div>
    </section>

    <section class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
      <h2 class="font-bold text-gray-900 text-lg mb-4">Eventos publicados</h2>
      <div class="space-y-3">
        <?php if (empty($activeEvents)): ?>
        <p class="text-sm text-gray-500">Aún no hay eventos publicados.</p>
        <?php endif; ?>
        <?php foreach (array_slice($activeEvents, 0, 6) as $event): ?>
        <a href="<?= url('evento/' . (int)$event['id'] . '/' . slugify($event['title'])) ?>" class="flex gap-3 p-3 border border-gray-100 rounded-xl hover:bg-orange-50 transition">
          <img src="<?= $event['image'] ? imageUrl($event['image']) : asset('img/placeholder.svg') ?>" alt="" class="w-16 h-16 object-cover rounded-lg shrink-0">
          <div class="min-w-0">
            <p class="font-semibold text-gray-900 text-sm truncate"><?= e($event['title']) ?></p>
            <p class="text-xs text-gray-500 line-clamp-2"><?= e($event['description'] ?? '') ?></p>
            <?php if (!empty($event['start_date'])): ?><p class="text-xs text-orange-600 mt-1"><?= e(date('d/m/Y H:i', strtotime($event['start_date']))) ?></p><?php endif; ?>
          </div>
        </a>
        <?php endforeach; ?>
      </div>
    </section>
  </div>

  <section class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 mb-6">
    <h2 class="font-bold text-gray-900 text-lg mb-4">Lugares visitados</h2>
    <?php if (empty($visitedPlaces)): ?>
    <p class="text-sm text-gray-500">Tu historial aparecerá cuando abras el detalle de un lugar desde el mapa.</p>
    <?php else: ?>
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
      <?php foreach ($visitedPlaces as $b): ?>
      <a href="<?= url('lugar/' . $b['slug']) ?>" class="p-3 border border-gray-100 rounded-xl hover:bg-gray-50 transition">
        <div class="flex items-center gap-3">
          <img src="<?= $b['cover_image'] ? imageUrl($b['cover_image']) : asset('img/placeholder.svg') ?>" alt="" class="w-14 h-14 object-cover rounded-lg shrink-0">
          <div class="min-w-0">
            <p class="font-semibold text-gray-900 text-sm truncate"><?= e($b['name']) ?></p>
            <p class="text-xs text-gray-500"><?= e($b['category_name']) ?></p>
          </div>
        </div>
        <p class="text-xs text-orange-600 mt-3">Última visita: <?= e(date('d/m/Y H:i', strtotime($b['last_visited_at']))) ?></p>
      </a>
      <?php endforeach; ?>
    </div>
    <?php endif; ?>
  </section>

  <section class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 mb-6">
    <h2 class="font-bold text-gray-900 text-lg mb-4">Mis comentarios</h2>
    <?php if (empty($myReviews)): ?>
    <p class="text-sm text-gray-500">Aún no has calificado negocios.</p>
    <?php else: ?>
    <div class="space-y-3">
      <?php foreach ($myReviews as $review): ?>
      <a href="<?= url('lugar/' . $review['business_slug']) ?>#valoraciones" class="block p-4 border border-gray-100 rounded-xl hover:bg-gray-50 transition">
        <div class="flex items-center justify-between gap-3">
          <p class="font-semibold text-gray-900 text-sm"><?= e($review['business_name']) ?></p>
          <span class="text-yellow-400 text-sm"><?= str_repeat('★', (int)$review['rating']) . str_repeat('☆', 5 - (int)$review['rating']) ?></span>
        </div>
        <?php if (!empty($review['comment'])): ?><p class="text-sm text-gray-600 mt-2"><?= e($review['comment']) ?></p><?php endif; ?>
        <p class="text-xs text-gray-400 mt-2"><?= e(date('d/m/Y', strtotime($review['created_at']))) ?></p>
      </a>
      <?php endforeach; ?>
    </div>
    <?php endif; ?>
  </section>

  <section class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
    <h2 class="font-bold text-gray-900 text-lg mb-4">Lugares más visitados</h2>
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
      <?php foreach (array_slice($topVisited, 0, 6) as $b): ?>
      <a href="<?= url('lugar/' . $b['slug']) ?>" class="flex items-center gap-3 p-3 border border-gray-100 rounded-xl hover:bg-gray-50 transition">
        <img src="<?= $b['cover_image'] ? imageUrl($b['cover_image']) : asset('img/placeholder.svg') ?>" alt="" class="w-14 h-14 object-cover rounded-lg shrink-0">
        <div class="min-w-0">
          <p class="font-semibold text-gray-900 text-sm truncate"><?= e($b['name']) ?></p>
          <p class="text-xs text-gray-500"><?= e($b['category_name']) ?> · <?= number_format((float)$b['rating'], 1) ?>/5</p>
        </div>
      </a>
      <?php endforeach; ?>
    </div>
  </section>
</main>

<script>
function toggleProfileForm() {
  document.getElementById('profile-form')?.classList.toggle('hidden');
}
</script>

<?php require APP_PATH . '/views/layout/bottom_nav.php'; ?>
<?php require APP_PATH . '/views/layout/footer.php'; ?>
