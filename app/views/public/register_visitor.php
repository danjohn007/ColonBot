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
      <img src="<?= asset('img/cristo-bot-nino-small.png') ?>" alt="Cristo Bot Col&oacute;n" class="colon-auth-logo mb-6" width="160" height="160" decoding="async">
      <p class="colon-eyebrow">Visitantes</p>
      <h1>Explora Col&oacute;n mejor</h1>
      <p>Guarda lugares, descubre beneficios y participa con comentarios que ayudan a otros viajeros a vivir una ruta m&aacute;s completa.</p>
    </section>

    <section class="colon-auth-card">
      <?php $f = flash(); if ($f): ?>
      <div class="mb-4 p-3 rounded-xl text-sm <?= $f['type'] === 'success' ? 'bg-orange-50 text-orange-700 border border-orange-200' : 'bg-red-50 text-red-700 border border-red-200' ?>">
        <?= e($f['msg']) ?>
      </div>
      <?php endif; ?>

      <div class="colon-auth-panel">
        <h2>Iniciar sesi&oacute;n</h2>
        <p class="text-sm mb-6">Accede a descuentos, favoritos y funcionalidades para visitantes.</p>

        <form method="POST" action="<?= url(($routePrefix ?? '') . 'registro/visitante/iniciar-sesion') ?>" class="space-y-4">
          <input type="hidden" name="_csrf" value="<?= e($csrf) ?>">
          <input type="hidden" name="return_to" value="<?= e($returnTo ?? '') ?>">
          <div>
            <label class="block text-sm font-semibold text-gray-700 mb-1">Email</label>
            <input type="email" name="email" required class="colon-public-input w-full px-4 py-3 border border-gray-300 rounded-xl text-sm focus:outline-none transition">
          </div>
          <div>
            <label class="block text-sm font-semibold text-gray-700 mb-1">Contrase&ntilde;a</label>
            <div class="relative">
              <input type="password" name="password" id="visitor-login-password" required class="colon-public-input w-full px-4 py-3 pr-12 border border-gray-300 rounded-xl text-sm focus:outline-none transition">
              <button type="button" onclick="toggleVisitorPassword('visitor-login-password')" class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-orange-700 transition" aria-label="Mostrar u ocultar contrase&ntilde;a">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                </svg>
              </button>
            </div>
          </div>
          <button type="submit" class="colon-public-btn w-full">
            Iniciar sesi&oacute;n
          </button>
        </form>
      </div>

      <div class="colon-form-divider">o</div>

      <div class="colon-auth-panel">
        <h2>Registro para visitantes</h2>
        <p class="text-sm mb-6">Reg&iacute;strate para obtener descuentos exclusivos, calificar y dejar comentarios.</p>

        <form method="POST" action="<?= url(($routePrefix ?? '') . 'registro/visitante/guardar') ?>" class="space-y-4">
          <input type="hidden" name="_csrf" value="<?= e($csrf) ?>">
          <input type="hidden" name="return_to" value="<?= e($returnTo ?? '') ?>">
          <div>
            <label class="block text-sm font-semibold text-gray-700 mb-1">Nombre completo *</label>
            <input type="text" name="name" required class="colon-public-input w-full px-4 py-3 border border-gray-300 rounded-xl text-sm focus:outline-none transition">
          </div>
          <div>
            <label class="block text-sm font-semibold text-gray-700 mb-1">Email *</label>
            <input type="email" name="email" required class="colon-public-input w-full px-4 py-3 border border-gray-300 rounded-xl text-sm focus:outline-none transition">
            <p class="text-xs text-gray-400 mt-1">Te enviaremos un c&oacute;digo de confirmaci&oacute;n.</p>
          </div>
          <div>
            <label class="block text-sm font-semibold text-gray-700 mb-1">Contrase&ntilde;a *</label>
            <div class="relative">
              <input type="password" name="password" id="visitor-register-password" required minlength="8" autocomplete="new-password" class="colon-public-input w-full px-4 py-3 pr-12 border border-gray-300 rounded-xl text-sm focus:outline-none transition">
              <button type="button" onclick="toggleVisitorPassword('visitor-register-password')" class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-orange-700 transition" aria-label="Mostrar u ocultar contrase&ntilde;a">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                </svg>
              </button>
            </div>
          </div>
          <div>
            <label class="block text-sm font-semibold text-gray-700 mb-1">Repetir contrase&ntilde;a *</label>
            <div class="relative">
              <input type="password" name="password_confirm" id="visitor-register-password-confirm" required minlength="8" autocomplete="new-password" class="colon-public-input w-full px-4 py-3 pr-12 border border-gray-300 rounded-xl text-sm focus:outline-none transition">
              <button type="button" onclick="toggleVisitorPassword('visitor-register-password-confirm')" class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-orange-700 transition" aria-label="Mostrar u ocultar contrase&ntilde;a">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                </svg>
              </button>
            </div>
          </div>
          <button type="submit" class="colon-public-btn w-full">
            Registrarse
          </button>
        </form>
      </div>
    </section>
  </div>
</main>

<script>
function toggleVisitorPassword(id) {
  const input = document.getElementById(id);
  if (!input) return;
  input.type = input.type === 'password' ? 'text' : 'password';
}
</script>

<?php require APP_PATH . '/views/layout/footer.php'; ?>
