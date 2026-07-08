<?php
$pageTitle = 'Mi Perfil Visitante - ' . APP_NAME;
$prefix = $routePrefix ?? '';
$phone = $user['phone'] ?? '';
require APP_PATH . '/views/layout/head.php';
?>
<?php require APP_PATH . '/views/layout/navbar.php'; ?>

<main class="max-w-4xl mx-auto px-4 py-6 md:py-8 mb-24">
  <div class="mb-5">
    <a href="<?= url($prefix . 'turista') ?>" class="inline-flex items-center gap-2 text-sm font-bold text-orange-700 hover:text-orange-800">
      <span aria-hidden="true">&larr;</span>
      Volver
    </a>
  </div>

  <section class="rounded-2xl overflow-hidden shadow-sm border border-orange-100 bg-white mb-6">
    <div class="bg-gradient-to-r from-orange-600 to-amber-500 px-6 py-7 text-white">
      <p class="text-xs font-bold uppercase tracking-[.22em] text-orange-100">Visitante</p>
      <h1 class="text-2xl md:text-4xl font-extrabold mt-2 text-white">Mi perfil visitante</h1>
      <p class="text-orange-50 mt-2 max-w-2xl text-sm">Actualiza tus datos para recibir avisos, beneficios y una experiencia mas personalizada.</p>
    </div>

    <form method="POST" action="<?= url($prefix . 'turista/registrar') ?>" class="p-6 space-y-5">
      <input type="hidden" name="_csrf" value="<?= e($csrf ?? '') ?>">
      <input type="hidden" name="return_to" value="<?= e($prefix . 'turista/perfil') ?>">

      <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <label class="block">
          <span class="text-sm font-semibold text-gray-700 mb-1 block">Nombre</span>
          <input type="text" name="name" value="<?= e($user['name']) ?>" required class="w-full px-4 py-3 border border-gray-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-orange-500">
        </label>

        <label class="block">
          <span class="text-sm font-semibold text-gray-700 mb-1 block">Email</span>
          <input type="email" name="email" value="<?= e($user['email']) ?>" required class="w-full px-4 py-3 border border-gray-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-orange-500">
        </label>

        <label class="block md:col-span-2">
          <span class="text-sm font-semibold text-gray-700 mb-1 block">WhatsApp</span>
          <input type="tel" name="whatsapp" value="<?= e($phone) ?>" class="w-full px-4 py-3 border border-gray-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-orange-500" placeholder="5214421000000">
        </label>
      </div>

      <div class="flex flex-col sm:flex-row gap-3 pt-2">
        <button type="submit" class="inline-flex items-center justify-center px-6 py-3 rounded-xl bg-orange-600 text-white text-sm font-bold hover:bg-orange-700 transition">
          Guardar cambios
        </button>
        <a href="<?= url($prefix . 'turista') ?>" class="inline-flex items-center justify-center px-6 py-3 rounded-xl border border-gray-200 text-gray-600 text-sm font-bold hover:bg-gray-50 transition">
          Cancelar
        </a>
      </div>
    </form>
  </section>

  <section class="grid grid-cols-1 sm:grid-cols-3 gap-4">
    <div class="bg-white rounded-xl border border-gray-100 p-4 shadow-sm">
      <p class="text-xs font-bold uppercase text-gray-400">Cuenta</p>
      <p class="font-bold text-gray-900 mt-1"><?= e($user['role'] ?? 'visitor') ?></p>
    </div>
    <div class="bg-white rounded-xl border border-gray-100 p-4 shadow-sm">
      <p class="text-xs font-bold uppercase text-gray-400">Correo</p>
      <p class="font-bold text-gray-900 mt-1 break-words"><?= e($user['email'] ?? '') ?></p>
    </div>
    <div class="bg-white rounded-xl border border-gray-100 p-4 shadow-sm">
      <p class="text-xs font-bold uppercase text-gray-400">WhatsApp</p>
      <p class="font-bold text-gray-900 mt-1"><?= $phone ? e($phone) : 'Sin capturar' ?></p>
    </div>
  </section>
</main>

<?php require APP_PATH . '/views/layout/bottom_nav.php'; ?>
<?php require APP_PATH . '/views/layout/footer.php'; ?>
