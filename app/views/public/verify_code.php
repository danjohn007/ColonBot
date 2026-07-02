<?php
$pageTitle = 'Verificar Código – CristobalBot';
require APP_PATH . '/views/layout/head.php';
?>
<?php require APP_PATH . '/views/layout/navbar.php'; ?>

<main class="max-w-lg mx-auto px-4 py-8 mb-24">
  <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
    <h1 class="text-2xl font-bold text-gray-900 mb-2">🔐 Verificar Código</h1>
    <p class="text-sm text-gray-500 mb-6">Hemos enviado un código de verificación a <strong><?= e($email) ?></strong>. Ingresa el código para completar tu registro.</p>

    <!-- Email verification -->
    <div class="mb-6 p-4 border border-blue-200 rounded-xl bg-blue-50">
      <h2 class="font-semibold text-gray-900 mb-2">📧 Verificación por Email</h2>
      <p class="text-xs text-gray-500 mb-3">Revisa tu bandeja de entrada y spam, ingresa el código de 6 dígitos.</p>
      <form method="POST" action="<?= url('registro/verificar/codigo') ?>" class="flex gap-2">
        <input type="hidden" name="_csrf" value="<?= e($csrf) ?>">
        <input type="hidden" name="method" value="email">
        <input type="text" name="code" required maxlength="6" pattern="[0-9]{6}" placeholder="000000"
          class="flex-1 px-4 py-2.5 border border-gray-300 rounded-xl text-sm text-center text-lg font-bold tracking-widest focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition">
        <button type="submit" class="px-4 py-2.5 bg-blue-600 text-white rounded-xl text-sm font-medium hover:bg-blue-700 transition">
          Verificar
        </button>
      </form>
    </div>

    <p class="text-xs text-gray-400 text-center">¿No recibiste el código? <a href="#" onclick="resendCode(event)" class="text-blue-600 hover:underline">Reenviar código</a></p>
  </div>
</main>

<script>
const CSRF = '<?= e($csrf) ?>';
const BASE_URL = '<?= BASE_URL ?>';

function resendCode(e) {
  e.preventDefault();
  if (!confirm('¿Reenviar código de verificación?')) return;

  const body = new URLSearchParams({ _csrf: CSRF, method: 'email' });
  fetch(`${BASE_URL}/registro/reenviar-codigo`, {
    method: 'POST',
    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
    body: body.toString(),
  })
  .then(r => r.json())
  .then(d => {
    if (d.ok) {
      alert('Código reenviado. Revisa tu email y WhatsApp.');
    } else {
      alert(d.error || 'Error al reenviar');
    }
  });
}
</script>

<?php require APP_PATH . '/views/layout/footer.php'; ?>
</write_to_file>