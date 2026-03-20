<?php
$pageTitle = 'Configuraciones Globales';
require APP_PATH . '/views/layout/head.php';
?>
<?php require APP_PATH . '/views/layout/navbar.php'; ?>
<main class="max-w-5xl mx-auto px-4 py-8 mb-24">
  <h1 class="text-2xl font-bold text-gray-900 mb-2">⚙️ Configuraciones Globales</h1>
  <p class="text-gray-500 text-sm mb-8">Configura el comportamiento y apariencia del sistema.</p>

  <!-- Settings tabs -->
  <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
    <!-- Sidebar nav -->
    <nav class="lg:col-span-1">
      <ul class="space-y-1 bg-white rounded-2xl shadow-sm p-3 sticky top-20">
        <?php $sections = [
          ['general',  '🏠 General',          '#sec-general'],
          ['theme',    '🎨 Colores y Estilos', '#sec-theme'],
          ['map',      '🗺️ Mapa',             '#sec-map'],
          ['chatbot',  '🤖 ChatBot WhatsApp',  '#sec-chatbot'],
          ['payments', '💳 PayPal',            '#sec-payments'],
          ['qr',       '📱 API QR Codes',      '#sec-qr'],
          ['gps',      '📡 GPS Tracker',       '#sec-gps'],
        ];
        foreach ($sections as [$grp, $label, $anchor]):
        ?>
        <li>
          <a href="<?= $anchor ?>" class="flex items-center gap-2 px-3 py-2 rounded-xl text-sm text-gray-700 hover:bg-blue-50 hover:text-blue-700 transition font-medium">
            <?= $label ?>
          </a>
        </li>
        <?php endforeach; ?>
        <li><hr class="my-2 border-gray-100"></li>
        <li>
          <a href="<?= url('configuraciones/hikvision') ?>" class="flex items-center gap-2 px-3 py-2 rounded-xl text-sm text-gray-700 hover:bg-blue-50 hover:text-blue-700 transition font-medium">
            📹 HikVision
          </a>
        </li>
        <li>
          <a href="<?= url('configuraciones/shelly') ?>" class="flex items-center gap-2 px-3 py-2 rounded-xl text-sm text-gray-700 hover:bg-blue-50 hover:text-blue-700 transition font-medium">
            💡 Shelly Cloud
          </a>
        </li>
        <li>
          <a href="<?= url('configuraciones/gps') ?>" class="flex items-center gap-2 px-3 py-2 rounded-xl text-sm text-gray-700 hover:bg-blue-50 hover:text-blue-700 transition font-medium">
            📡 GPS Trackers
          </a>
        </li>
        <li>
          <a href="<?= url('superadmin/bitacora') ?>" class="flex items-center gap-2 px-3 py-2 rounded-xl text-sm text-gray-700 hover:bg-blue-50 hover:text-blue-700 transition font-medium">
            📋 Bitácora
          </a>
        </li>
        <li>
          <a href="<?= url('superadmin/errores') ?>" class="flex items-center gap-2 px-3 py-2 rounded-xl text-sm text-gray-700 hover:bg-blue-50 hover:text-blue-700 transition font-medium">
            🚨 Errores
          </a>
        </li>
      </ul>
    </nav>

    <!-- Settings forms -->
    <div class="lg:col-span-3 space-y-8">

      <?php
      $g = $groups;
      $v = fn(string $k, string $grp='general') => e($g[$grp][$k] ?? '');
      ?>

      <!-- General -->
      <section id="sec-general" class="bg-white rounded-2xl shadow-sm p-6">
        <h2 class="font-semibold text-gray-900 mb-4 border-b pb-2">🏠 General</h2>
        <form method="POST" action="<?= url('configuraciones/guardar') ?>" enctype="multipart/form-data" class="space-y-4">
          <input type="hidden" name="_csrf" value="<?= e($csrf) ?>">
          <input type="hidden" name="group" value="general">
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div class="sm:col-span-2">
              <label class="label">Nombre del sitio</label>
              <input type="text" name="site_name" value="<?= $v('site_name') ?>" class="input">
            </div>
            <div class="sm:col-span-2">
              <label class="label">Eslogan</label>
              <input type="text" name="site_tagline" value="<?= $v('site_tagline') ?>" class="input">
            </div>
            <div>
              <label class="label">Correo principal del sistema</label>
              <input type="email" name="contact_email" value="<?= $v('contact_email') ?>" class="input">
            </div>
            <div>
              <label class="label">Teléfono de contacto 1</label>
              <input type="tel" name="contact_phone" value="<?= $v('contact_phone') ?>" class="input">
            </div>
            <div>
              <label class="label">Teléfono de contacto 2</label>
              <input type="tel" name="contact_phone2" value="<?= $v('contact_phone2') ?>" class="input">
            </div>
            <div>
              <label class="label">Horario de atención</label>
              <input type="text" name="schedule" value="<?= $v('schedule') ?>" class="input" placeholder="Lun-Vie 09:00-18:00">
            </div>
          </div>
          <button type="submit" class="btn-primary">Guardar configuración general</button>
        </form>
      </section>

      <!-- Theme / Colors -->
      <section id="sec-theme" class="bg-white rounded-2xl shadow-sm p-6">
        <h2 class="font-semibold text-gray-900 mb-4 border-b pb-2">🎨 Colores del Sistema</h2>
        <form method="POST" action="<?= url('configuraciones/guardar') ?>" class="space-y-4">
          <input type="hidden" name="_csrf" value="<?= e($csrf) ?>">
          <input type="hidden" name="group" value="theme">
          <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <?php foreach (['color_primary'=>'Color Primario','color_secondary'=>'Color Secundario','color_accent'=>'Color de Acento'] as $key=>$label): ?>
            <div>
              <label class="label"><?= $label ?></label>
              <div class="flex gap-2 items-center">
                <input type="color" name="<?= $key ?>" value="<?= e($g['theme'][$key] ?? '#3B82F6') ?>"
                  class="w-12 h-10 rounded-lg border border-gray-300 cursor-pointer">
                <input type="text" value="<?= e($g['theme'][$key] ?? '#3B82F6') ?>"
                  oninput="this.previousElementSibling.value=this.value"
                  class="flex-1 input font-mono text-sm">
              </div>
            </div>
            <?php endforeach; ?>
          </div>
          <button type="submit" class="btn-primary">Guardar colores</button>
        </form>
      </section>

      <!-- Map -->
      <section id="sec-map" class="bg-white rounded-2xl shadow-sm p-6">
        <h2 class="font-semibold text-gray-900 mb-4 border-b pb-2">🗺️ Configuración del Mapa</h2>
        <form method="POST" action="<?= url('configuraciones/guardar') ?>" class="space-y-4">
          <input type="hidden" name="_csrf" value="<?= e($csrf) ?>">
          <input type="hidden" name="group" value="map">
          <div class="grid grid-cols-3 gap-4">
            <div>
              <label class="label">Latitud central</label>
              <input type="number" name="map_lat" step="0.0001" value="<?= e($g['map']['map_lat'] ?? '20.2862') ?>" class="input">
            </div>
            <div>
              <label class="label">Longitud central</label>
              <input type="number" name="map_lng" step="0.0001" value="<?= e($g['map']['map_lng'] ?? '-99.7242') ?>" class="input">
            </div>
            <div>
              <label class="label">Zoom inicial</label>
              <input type="number" name="map_zoom" min="1" max="20" value="<?= e($g['map']['map_zoom'] ?? '13') ?>" class="input">
            </div>
          </div>
          <button type="submit" class="btn-primary">Guardar configuración de mapa</button>
        </form>
      </section>

      <!-- ChatBot -->
      <section id="sec-chatbot" class="bg-white rounded-2xl shadow-sm p-6">
        <h2 class="font-semibold text-gray-900 mb-4 border-b pb-2">🤖 Configuración ChatBot WhatsApp</h2>
        <form method="POST" action="<?= url('configuraciones/guardar') ?>" class="space-y-4">
          <input type="hidden" name="_csrf" value="<?= e($csrf) ?>">
          <input type="hidden" name="group" value="chatbot">
          <div>
            <label class="label">Token de acceso (Meta/WhatsApp Business API)</label>
            <input type="password" name="wa_token" value="<?= $v('wa_token','chatbot') ?>" class="input font-mono text-sm" placeholder="Bearer token de la API de Meta">
          </div>
          <div>
            <label class="label">Phone Number ID</label>
            <input type="text" name="wa_phone_id" value="<?= $v('wa_phone_id','chatbot') ?>" class="input font-mono text-sm">
          </div>
          <div>
            <label class="label">Verify Token (para el webhook)</label>
            <input type="text" name="wa_verify_token" value="<?= $v('wa_verify_token','chatbot') ?>" class="input font-mono text-sm">
          </div>
          <div>
            <label class="label">Versión de API de Meta (ej. v19.0)</label>
            <input type="text" name="wa_api_version" value="<?= $v('wa_api_version','chatbot') ?>" class="input font-mono text-sm" placeholder="v19.0">
          </div>
          <div class="bg-blue-50 p-4 rounded-xl text-sm text-blue-700">
            <p class="font-semibold mb-1">URL del Webhook para Meta:</p>
            <code class="font-mono break-all"><?= url('chatbot/webhook') ?></code>
          </div>
          <button type="submit" class="btn-primary">Guardar configuración chatbot</button>
        </form>
      </section>

      <!-- PayPal -->
      <section id="sec-payments" class="bg-white rounded-2xl shadow-sm p-6">
        <h2 class="font-semibold text-gray-900 mb-4 border-b pb-2">💳 PayPal</h2>
        <form method="POST" action="<?= url('configuraciones/guardar') ?>" class="space-y-4">
          <input type="hidden" name="_csrf" value="<?= e($csrf) ?>">
          <input type="hidden" name="group" value="payments">
          <div>
            <label class="label">Client ID de PayPal</label>
            <input type="text" name="paypal_client_id" value="<?= $v('paypal_client_id','payments') ?>" class="input font-mono text-sm" placeholder="AXxx...">
          </div>
          <div>
            <label class="label">Modo</label>
            <select name="paypal_mode" class="input">
              <option value="sandbox" <?= ($g['payments']['paypal_mode'] ?? '') === 'sandbox' ? 'selected' : '' ?>>Sandbox (pruebas)</option>
              <option value="live" <?= ($g['payments']['paypal_mode'] ?? '') === 'live' ? 'selected' : '' ?>>Live (producción)</option>
            </select>
          </div>
          <button type="submit" class="btn-primary">Guardar configuración PayPal</button>
        </form>
      </section>

      <!-- QR API -->
      <section id="sec-qr" class="bg-white rounded-2xl shadow-sm p-6">
        <h2 class="font-semibold text-gray-900 mb-4 border-b pb-2">📱 API de QR Codes Masivos</h2>
        <form method="POST" action="<?= url('configuraciones/guardar') ?>" class="space-y-4">
          <input type="hidden" name="_csrf" value="<?= e($csrf) ?>">
          <input type="hidden" name="group" value="qr">
          <div>
            <label class="label">API Key para generación de QR</label>
            <input type="password" name="qr_api_key" value="<?= $v('qr_api_key','qr') ?>" class="input font-mono text-sm" placeholder="Tu API key del proveedor QR">
          </div>
          <p class="text-xs text-gray-400">Compatible con servicios como api.qr-code-generator.com o goqr.me</p>
          <button type="submit" class="btn-primary">Guardar</button>
        </form>
      </section>

      <!-- GPS -->
      <section id="sec-gps" class="bg-white rounded-2xl shadow-sm p-6">
        <h2 class="font-semibold text-gray-900 mb-4 border-b pb-2">📡 GPS Tracker API</h2>
        <form method="POST" action="<?= url('configuraciones/guardar') ?>" class="space-y-4">
          <input type="hidden" name="_csrf" value="<?= e($csrf) ?>">
          <input type="hidden" name="group" value="gps">
          <div>
            <label class="label">URL del servidor GPS</label>
            <input type="url" name="gps_api_url" value="<?= $v('gps_api_url','gps') ?>" class="input" placeholder="https://...">
          </div>
          <div>
            <label class="label">API Key del servidor GPS</label>
            <input type="password" name="gps_api_key" value="<?= $v('gps_api_key','gps') ?>" class="input font-mono text-sm">
          </div>
          <p class="text-xs text-gray-400">Endpoint para recibir posición desde trackers: <code class="font-mono"><?= url('api/gps/actualizar') ?></code></p>
          <button type="submit" class="btn-primary">Guardar</button>
        </form>
      </section>

    </div>
  </div>
</main>

<style>
  .label    { @apply block text-sm font-medium text-gray-700 mb-1; }
  .input    { @apply w-full px-4 py-2.5 border border-gray-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition; }
  .btn-primary { @apply bg-blue-600 text-white px-5 py-2.5 rounded-xl font-semibold hover:bg-blue-700 transition text-sm; }
</style>

<?php require APP_PATH . '/views/layout/bottom_nav.php'; ?>
<?php require APP_PATH . '/views/layout/footer.php'; ?>
