<?php $pageTitle = 'Recuperar Contraseña – ' . APP_NAME; require APP_PATH . '/views/layout/head.php'; ?>
<div class="flex-1 min-h-screen bg-gradient-to-br from-blue-50 to-indigo-100 flex items-center justify-center p-4">
  <div class="w-full max-w-md">
    <!-- Logo & Title -->
    <div class="text-center mb-8">
      <div class="inline-flex items-center justify-center w-16 h-16 bg-blue-600 rounded-2xl shadow-lg mb-4">
        <svg class="w-9 h-9 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
        </svg>
      </div>
      <h1 class="text-2xl font-bold text-gray-900"><?= e(setting('site_name', APP_NAME)) ?></h1>
      <p class="text-gray-500 text-sm mt-1">Recuperar Contraseña</p>
    </div>

    <!-- Card -->
    <div class="bg-white rounded-2xl shadow-xl p-8">
      <?php $flash = flash(); if ($flash): ?>
      <div class="mb-4 p-3 rounded-lg text-sm font-medium
        <?= $flash['type'] === 'success' ? 'bg-green-50 text-green-700 border border-green-200' : 'bg-red-50 text-red-700 border border-red-200' ?>">
        <?= e($flash['msg']) ?>
      </div>
      <?php endif; ?>

      <p class="text-sm text-gray-600 mb-5">
        Ingresa tu correo electrónico para solicitar ayuda con tu contraseña. El administrador del sistema te contactará para asistirte.
      </p>

      <form method="POST" action="<?= url('olvide-contrasena') ?>" class="space-y-5">
        <input type="hidden" name="_csrf" value="<?= e($csrf) ?>">

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Correo electrónico</label>
          <input type="email" name="email" required autocomplete="email"
            class="w-full px-4 py-3 rounded-xl border border-gray-300 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition text-sm"
            placeholder="admin@ejemplo.mx">
        </div>

        <button type="submit" class="w-full bg-blue-600 text-white py-3 rounded-xl font-semibold hover:bg-blue-700 transition shadow-sm">
          Enviar instrucciones
        </button>
      </form>

      <p class="text-center text-xs text-gray-400 mt-6">
        <a href="<?= url('login') ?>" class="hover:text-blue-600 transition">← Volver al inicio de sesión</a>
      </p>
    </div>
  </div>
</div>
<?php require APP_PATH . '/views/layout/footer.php'; ?>
