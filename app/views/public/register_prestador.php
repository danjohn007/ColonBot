<?php
$pageTitle = 'Registro Prestador – CristobalBot';
require APP_PATH . '/views/layout/head.php';
?>
<?php require APP_PATH . '/views/layout/navbar.php'; ?>

<main class="max-w-lg mx-auto px-4 py-8 mb-24">
  <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
    <h1 class="text-2xl font-bold text-gray-900 mb-2">📝 Registro para Prestadores de Servicio</h1>
    <p class="text-sm text-gray-500 mb-6">Da de alta tu negocio en nuestra plataforma turística.</p>

    <form method="POST" action="<?= url('registro/prestador/guardar') ?>" class="space-y-4">
      <input type="hidden" name="_csrf" value="<?= e($csrf) ?>">
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Nombre completo *</label>
        <input type="text" name="name" required class="w-full px-4 py-2.5 border border-gray-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition">
      </div>
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Nombre del negocio</label>
        <input type="text" name="business_name" class="w-full px-4 py-2.5 border border-gray-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition">
        <p class="text-xs text-gray-400 mt-1">Opcional, lo podrás configurar después</p>
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
</write_to_file>