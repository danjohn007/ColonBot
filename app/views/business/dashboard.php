<?php
$pageTitle = 'Mi Panel – ' . APP_NAME;
require APP_PATH . '/views/layout/head.php';
?>
<?php require APP_PATH . '/views/layout/navbar.php'; ?>

<main class="max-w-6xl mx-auto px-4 py-8 mb-20">
  <div class="flex items-center justify-between mb-6">
    <div>
      <h1 class="text-2xl font-bold text-gray-900">Mi Panel</h1>
      <p class="text-gray-500 text-sm mt-0.5">Hola, <?= e($user['name']) ?> 👋</p>
    </div>
    <a href="<?= url('admin/negocio/crear') ?>"
      class="flex items-center gap-2 bg-blue-600 text-white px-4 py-2.5 rounded-xl font-semibold hover:bg-blue-700 transition text-sm">
      + Agregar Negocio
    </a>
  </div>

  <?php if (empty($businesses)): ?>
  <div class="bg-white rounded-2xl shadow-sm p-12 text-center">
    <div class="text-6xl mb-4">🏢</div>
    <h2 class="text-xl font-semibold text-gray-700 mb-2">Aún no tienes negocios registrados</h2>
    <p class="text-gray-500 mb-6">Crea tu primer perfil de negocio para aparecer en el mapa turístico.</p>
    <a href="<?= url('admin/negocio/crear') ?>" class="inline-block bg-blue-600 text-white px-6 py-3 rounded-xl font-semibold hover:bg-blue-700 transition">
      Crear mi primer negocio
    </a>
  </div>
  <?php else: ?>
  <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
    <?php foreach ($businesses as $b): ?>
    <div class="bg-white rounded-2xl shadow-sm hover:shadow-md transition overflow-hidden flex flex-col">
      <?php if ($b['cover_image']): ?>
      <img src="<?= imageUrl($b['cover_image']) ?>" alt="<?= e($b['name']) ?>" class="w-full h-40 object-cover">
      <?php else: ?>
      <div class="w-full h-40 bg-gradient-to-br from-blue-50 to-indigo-100 flex items-center justify-center text-4xl">🏢</div>
      <?php endif; ?>
      <div class="p-5 flex-1 flex flex-col">
        <div class="flex items-start justify-between mb-2">
          <h3 class="font-semibold text-gray-900"><?= e($b['name']) ?></h3>
          <span class="text-xs px-2 py-0.5 rounded-full shrink-0
            <?= match($b['status']) {
              'published' => 'bg-green-100 text-green-700',
              'pending'   => 'bg-yellow-100 text-yellow-700',
              'rejected'  => 'bg-red-100 text-red-700',
              default     => 'bg-gray-100 text-gray-600',
            } ?>">
            <?= match($b['status']) { 'published'=>'Publicado','pending'=>'Pendiente','rejected'=>'Rechazado',default=>'Borrador' } ?>
          </span>
        </div>
        <p class="text-xs text-gray-500 mb-1"><?= e($b['category_name']) ?></p>
        <p class="text-sm text-gray-600 line-clamp-2 flex-1"><?= e($b['description']) ?></p>
        <div class="flex gap-2 mt-4">
          <a href="<?= url('admin/negocio/' . $b['id']) ?>"
            class="flex-1 text-center text-sm bg-blue-50 text-blue-700 py-2 rounded-lg hover:bg-blue-100 transition font-medium">
            Editar
          </a>
          <?php if ($b['status'] === 'published'): ?>
          <a href="<?= url('lugar/' . $b['slug']) ?>" target="_blank"
            class="flex-1 text-center text-sm bg-gray-50 text-gray-700 py-2 rounded-lg hover:bg-gray-100 transition font-medium">
            Ver
          </a>
          <?php endif; ?>
        </div>
      </div>
    </div>
    <?php endforeach; ?>
  </div>
  <?php endif; ?>
</main>

<?php require APP_PATH . '/views/layout/bottom_nav.php'; ?>
<?php require APP_PATH . '/views/layout/footer.php'; ?>
