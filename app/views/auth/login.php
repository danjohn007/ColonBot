<?php
$pageTitle = 'Iniciar Sesión – ' . APP_NAME;
// Generate captcha numbers and store answer in session
$captchaA = random_int(1, 9);
$captchaB = random_int(1, 9);
$_SESSION['captcha_answer'] = $captchaA + $captchaB;
require APP_PATH . '/views/layout/head.php';
?>
<style>
  body { background: linear-gradient(135deg, #EFF6FF 0%, #EEF2FF 100%) !important; }
</style>
<div class="flex-1 flex items-center justify-center p-4">
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
      <p class="text-gray-500 text-sm mt-1">Panel de Administración</p>
    </div>

    <!-- Card -->
    <div class="bg-white rounded-2xl shadow-xl p-8">
      <?php $flash = flash(); if ($flash): ?>
      <div class="mb-4 p-3 rounded-lg text-sm font-medium
        <?= $flash['type'] === 'success' ? 'bg-green-50 text-green-700 border border-green-200' : 'bg-red-50 text-red-700 border border-red-200' ?>">
        <?= e($flash['msg']) ?>
      </div>
      <?php endif; ?>

      <form method="POST" action="<?= url('login') ?>" class="space-y-5">
        <input type="hidden" name="_csrf" value="<?= e($csrf) ?>">

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Correo electrónico</label>
          <input type="email" name="email" required autocomplete="email"
            class="w-full px-4 py-3 rounded-xl border border-gray-300 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition text-sm"
            placeholder="admin@ejemplo.mx">
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Contraseña</label>
          <div class="relative">
            <input type="password" name="password" id="pwd" required autocomplete="current-password"
              class="w-full px-4 py-3 rounded-xl border border-gray-300 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition text-sm pr-12"
              placeholder="••••••••">
            <button type="button" onclick="togglePwd()" class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600">
              <svg id="eye-icon" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
              </svg>
            </button>
          </div>
        </div>

        <!-- Math CAPTCHA -->
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">
            Verificación: ¿Cuánto es <?= $captchaA ?> + <?= $captchaB ?>?
          </label>
          <input type="number" name="captcha" required
            class="w-full px-4 py-3 rounded-xl border border-gray-300 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition text-sm"
            placeholder="Ingresa el resultado" min="0" max="18">
        </div>

        <button type="submit" class="w-full bg-blue-600 text-white py-3 rounded-xl font-semibold hover:bg-blue-700 transition shadow-sm">
          Ingresar al Panel
        </button>
      </form>

      <div class="mt-5 flex items-center justify-between text-xs text-gray-400">
        <a href="<?= url('mapa') ?>" class="hover:text-blue-600 transition">← Ver mapa turístico público</a>
        <button type="button"
          onclick="document.getElementById('forgot-msg').classList.toggle('hidden')"
          class="hover:text-blue-600 transition focus:outline-none">
          ¿Olvidaste tu Contraseña?
        </button>
      </div>
      <div id="forgot-msg" class="hidden mt-3 p-3 bg-blue-50 border border-blue-200 text-blue-700 text-xs rounded-xl" role="alert">
        Para restablecer tu contraseña, contacta al administrador del sistema.
      </div>
    </div>
  </div>
</div>

<script>
  function togglePwd() {
    const p = document.getElementById('pwd');
    p.type = p.type === 'password' ? 'text' : 'password';
  }
</script>
<?php require APP_PATH . '/views/layout/footer.php'; ?>
