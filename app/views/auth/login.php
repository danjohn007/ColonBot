<?php
$pageTitle = 'Iniciar Sesion - ' . APP_NAME;
$publicLoginPrefix = $routePrefix ?? '';
$extraHead = '<link rel="preconnect" href="https://fonts.googleapis.com">' . PHP_EOL
  . '  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>' . PHP_EOL
  . '  <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">' . PHP_EOL
  . '  <link rel="stylesheet" href="' . asset('css/landing-map.css') . '">';
require APP_PATH . '/views/layout/head.php';
?>

<main class="colon-auth-page">
  <div class="colon-auth-shell">
    <section class="colon-auth-copy">
      <img src="<?= asset('img/cristo-bot-nino.png') ?>" alt="Cristo Bot Colón" class="colon-auth-logo mb-6">
      <p class="colon-eyebrow">Panel de administración</p>
      <h1>Turismo en Colón</h1>
      <p>Administra a los negocios turísticos, promociones y eventos para los turistas de Colón, Querétaro</p>
    </section>

    <section class="colon-auth-card">
      <div class="colon-auth-panel">
        <h2><?= e(setting('site_name', APP_NAME)) ?></h2>
        <p class="text-sm mb-6">Ingresa con tus credenciales para continuar.</p>

        <?php $flash = flash(); if ($flash): ?>
        <div class="mb-4 p-3 rounded-xl text-sm font-medium
          <?= $flash['type'] === 'success' ? 'bg-orange-50 text-orange-700 border border-orange-200' : 'bg-red-50 text-red-700 border border-red-200' ?>">
          <?= e($flash['msg']) ?>
        </div>
        <?php endif; ?>

        <form method="POST" action="<?= url(($routePrefix ?? '') . 'login') ?>" class="space-y-5">
          <input type="hidden" name="_csrf" value="<?= e($csrf) ?>">

          <div>
            <label class="block text-sm font-semibold text-gray-700 mb-1">Correo electronico</label>
            <input type="email" name="email" required autocomplete="email"
              class="colon-public-input w-full px-4 py-3 rounded-xl border border-gray-300 focus:outline-none transition text-sm"
              placeholder="admin@ejemplo.mx">
          </div>

          <div>
            <label class="block text-sm font-semibold text-gray-700 mb-1">Contrasena</label>
            <div class="relative">
              <input type="password" name="password" id="pwd" required autocomplete="current-password"
                class="colon-public-input w-full px-4 py-3 rounded-xl border border-gray-300 focus:outline-none transition text-sm pr-12"
                placeholder="********">
              <button type="button" onclick="togglePwd()" class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-orange-700 transition" aria-label="Mostrar u ocultar contrasena">
                <svg id="eye-icon" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                </svg>
              </button>
            </div>
          </div>

          <div>
            <label class="block text-sm font-semibold text-gray-700 mb-1">
              Verificacion: cuanto es <strong><?= (int)$captchaA ?> + <?= (int)$captchaB ?></strong>?
            </label>
            <input type="number" name="captcha" required min="0" max="18"
              class="colon-public-input w-full px-4 py-3 rounded-xl border border-gray-300 focus:outline-none transition text-sm"
              placeholder="Resultado">
          </div>

          <button type="submit" class="colon-public-btn w-full">
            Ingresar al panel
          </button>
        </form>

        <div class="colon-user-login-banners" aria-label="Accesos para usuarios publicos">
          <a href="<?= url($publicLoginPrefix . 'registro/visitante') ?>" class="colon-login-banner colon-login-banner--visitor">
            <img src="<?= asset('img/cristo-bot-nino.png') ?>" alt="">
            <span class="colon-login-banner-overlay"></span>
            <span class="colon-login-banner-content">
              <small>Visitantes</small>
              <strong>Registrate como visitante y obten descuentos exclusivos</strong>
              <span>Califica y deja comentarios de atractivos turisticos.</span>
            </span>
          </a>

          <a href="<?= url($publicLoginPrefix . 'registro/prestador') ?>" class="colon-login-banner colon-login-banner--provider">
            <img src="<?= asset('img/landing/noche-restaurante.jpeg') ?>" alt="">
            <span class="colon-login-banner-overlay"></span>
            <span class="colon-login-banner-content">
              <small>Prestadores</small>
              <strong>Eres prestador de servicio?</strong>
              <span>Da de alta tu negocio y conecta con visitantes.</span>
            </span>
          </a>
        </div>

        <div class="flex items-center justify-between gap-4 mt-5">
          <a href="<?= url('olvide-contrasena') ?>" class="text-xs colon-public-link hover:underline transition">
            Olvide mi contrasena
          </a>
          <a href="<?= url('mapa') ?>" class="text-xs text-gray-400 hover:text-orange-700 transition">
            Ver mapa turistico
          </a>
        </div>
      </div>
    </section>
  </div>
</main>

<script>
  function togglePwd() {
    const p = document.getElementById('pwd');
    p.type = p.type === 'password' ? 'text' : 'password';
  }
</script>
<?php require APP_PATH . '/views/layout/footer.php'; ?>
