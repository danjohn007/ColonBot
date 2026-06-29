<?php
$pageTitle = 'Mi Perfil – ' . APP_NAME;
require APP_PATH . '/views/layout/head.php';
?>
<?php require APP_PATH . '/views/layout/navbar.php'; ?>

<main class="max-w-4xl mx-auto px-4 py-8 mb-24">
  <h1 class="text-2xl font-bold text-gray-900 mb-6">👤 Mi Perfil</h1>

  <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
    <form method="POST" action="<?= url('mi-perfil/actualizar') ?>" class="space-y-4">
      <input type="hidden" name="_csrf" value="<?= e($csrf) ?>">

      <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Nombre</label>
          <input type="text" name="name" value="<?= e($user['name']) ?>" required class="w-full px-4 py-2.5 border border-gray-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
          <input type="email" name="email" value="<?= e($user['email']) ?>" class="w-full px-4 py-2.5 border border-gray-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Teléfono</label>
          <input type="tel" name="phone" value="<?= e($user['phone'] ?? '') ?>" class="w-full px-4 py-2.5 border border-gray-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Teléfono (opcional)">
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Nueva contraseña</label>
          <input type="password" name="password" class="w-full px-4 py-2.5 border border-gray-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Dejar vacío para no cambiar">
        </div>
      </div>

      <div class="pt-2">
        <button type="submit" class="bg-blue-600 text-white px-6 py-2.5 rounded-xl font-medium hover:bg-blue-700 transition">
          Guardar cambios
        </button>
      </div>
    </form>
  </div>

  <!-- Account info -->
  <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 mt-6">
    <h2 class="font-semibold text-gray-900 mb-3">Información de la cuenta</h2>
    <div class="space-y-2 text-sm">
      <p><span class="text-gray-500">Rol:</span> <span class="font-medium"><?= e($user['role']) ?></span></p>
    </div>
  </div>
</main>

<?php require APP_PATH . '/views/layout/bottom_nav.php'; ?>
<?php require APP_PATH . '/views/layout/footer.php'; ?>