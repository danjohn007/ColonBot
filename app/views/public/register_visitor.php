<?php
$pageTitle = 'Registro Visitante - CristobalBot';
$extraHead = '<link rel="preconnect" href="https://fonts.googleapis.com">' . PHP_EOL
  . '  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>' . PHP_EOL
  . '  <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">' . PHP_EOL
  . '  <link rel="stylesheet" href="' . asset('css/landing-map.css') . '">';
require APP_PATH . '/views/layout/head.php';
?>

<main class="colon-auth-page">
  <div class="colon-auth-shell">
    <section class="colon-auth-copy">
      <img src="<?= asset('img/cristo-bot-nino.png') ?>" alt="Cristo Bot Colon" class="colon-auth-logo mb-6">
      <p class="colon-eyebrow">Visitantes</p>
      <h1>Explora Colon mejor</h1>
      <p>Guarda lugares, descubre beneficios y participa con comentarios que ayudan a otros viajeros a vivir una ruta mas completa.</p>
    </section>

    <section class="colon-auth-card">
      <?php $f = flash(); if ($f): ?>
      <div class="mb-4 p-3 rounded-xl text-sm <?= $f['type'] === 'success' ? 'bg-orange-50 text-orange-700 border border-orange-200' : 'bg-red-50 text-red-700 border border-red-200' ?>">
        <?= e($f['msg']) ?>
      </div>
      <?php endif; ?>

      <div class="colon-auth-panel">
        <h2>Iniciar sesion</h2>
        <p class="text-sm mb-6">Accede a descuentos, favoritos y funcionalidades para visitantes.</p>

        <form method="POST" action="<?= url('registro/visitante/iniciar-sesion') ?>" class="space-y-4">
          <input type="hidden" name="_csrf" value="<?= e($csrf) ?>">
          <div>
            <label class="block text-sm font-semibold text-gray-700 mb-1">Email</label>
            <input type="email" name="email" required class="colon-public-input w-full px-4 py-3 border border-gray-300 rounded-xl text-sm focus:outline-none transition">
          </div>
          <div>
            <label class="block text-sm font-semibold text-gray-700 mb-1">Contrasena</label>
            <input type="password" name="password" required class="colon-public-input w-full px-4 py-3 border border-gray-300 rounded-xl text-sm focus:outline-none transition">
          </div>
          <button type="submit" class="colon-public-btn w-full">
            Iniciar sesion
          </button>
        </form>
      </div>

      <div class="colon-form-divider">o</div>

      <div class="colon-auth-panel">
        <h2>Registro para visitantes</h2>
        <p class="text-sm mb-6">Registrate para obtener descuentos exclusivos, calificar y dejar comentarios.</p>

        <form method="POST" action="<?= url('registro/visitante/guardar') ?>" class="space-y-4">
          <input type="hidden" name="_csrf" value="<?= e($csrf) ?>">
          <div>
            <label class="block text-sm font-semibold text-gray-700 mb-1">Nombre completo *</label>
            <input type="text" name="name" required class="colon-public-input w-full px-4 py-3 border border-gray-300 rounded-xl text-sm focus:outline-none transition">
          </div>
          <div>
            <label class="block text-sm font-semibold text-gray-700 mb-1">Email *</label>
            <input type="email" name="email" required class="colon-public-input w-full px-4 py-3 border border-gray-300 rounded-xl text-sm focus:outline-none transition">
            <p class="text-xs text-gray-400 mt-1">Te enviaremos un codigo de confirmacion.</p>
          </div>
          <button type="submit" class="colon-public-btn w-full">
            Registrarse
          </button>
        </form>
      </div>
    </section>
  </div>
</main>

<?php require APP_PATH . '/views/layout/footer.php'; ?>
