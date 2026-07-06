<?php
$pageTitle = 'Registro Visitante – CristobalBot';
require APP_PATH . '/views/layout/head.php';
?>

<main class="max-w-lg mx-auto px-4 py-8 mb-24">
  <!-- Login Tab -->
  <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 mb-6">
    <h1 class="text-2xl font-bold text-gray-900 mb-2">🔑 Iniciar Sesión</h1>
    <p class="text-sm text-gray-500 mb-6">¿Ya tienes una cuenta? Inicia sesión para acceder a descuentos y funcionalidades.</p>

    <?php $f = flash(); if ($f): ?>
    <div class="mb-4 p-3 rounded-xl text-sm <?= $f['type'] === 'success' ? 'bg-green-50 text-green-700 border border-green-200' : 'bg-red-50 text-red-700 border border-red-200' ?>">
      <?= e($f['msg']) ?>
    </div>
    <?php endif; ?>

    <form method="POST" action="<?= url('registro/visitante/iniciar-sesion') ?>" class="space-y-4">
      <input type="hidden" name="_csrf" value="<?= e($csrf) ?>">
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
        <input type="email" name="email" required class="w-full px-4 py-2.5 border border-gray-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition">
      </div>
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Contraseña</label>
        <input type="password" name="password" required class="w-full px-4 py-2.5 border border-gray-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition">
      </div>
      <button type="submit" class="w-full bg-green-600 text-white py-3 rounded-xl font-medium hover:bg-green-700 transition">
        Iniciar Sesión
      </button>
    </form>
  </div>

  <div class="relative flex items-center mb-6">
    <div class="flex-1 border-t border-gray-200"></div>
    <span class="px-3 text-sm text-gray-400">o</span>
    <div class="flex-1 border-t border-gray-200"></div>
  </div>

  <!-- Register Tab -->
  <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
    <h1 class="text-2xl font-bold text-gray-900 mb-2">📝 Registro para Visitantes</h1>
    <p class="text-sm text-gray-500 mb-6">Regístrate y obtén descuentos exclusivos, califica y deja comentarios de nuestros atractivos turísticos.</p>

    <form method="POST" action="<?= url('registro/visitante/guardar') ?>" class="space-y-4">
      <input type="hidden" name="_csrf" value="<?= e($csrf) ?>">
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Nombre completo *</label>
        <input type="text" name="name" required class="w-full px-4 py-2.5 border border-gray-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition">
      </div>
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Email *</label>
        <input type="email" name="email" required class="w-full px-4 py-2.5 border border-gray-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition">
        <p class="text-xs text-gray-400 mt-1">Te enviaremos un código de confirmación</p>
      </div>
      <button type="submit" class="w-full bg-blue-600 text-white py-3 rounded-xl font-medium hover:bg-blue-700 transition">
        Registrarse
      </button>
    </form>
  </div>
</main>

<?php require APP_PATH . '/views/layout/footer.php'; ?>