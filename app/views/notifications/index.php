<?php
$pageTitle = 'Notificaciones – ' . APP_NAME;
require APP_PATH . '/views/layout/head.php';
?>
<?php require APP_PATH . '/views/layout/navbar.php'; ?>

<main class="max-w-4xl mx-auto px-4 py-8 mb-24">
  <div class="flex items-center justify-between mb-6">
    <div class="flex items-center gap-3">
      <a href="<?= url('admin') ?>" class="text-gray-500 hover:text-blue-600 transition">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
        </svg>
      </a>
      <h1 class="text-2xl font-bold text-gray-900">🔔 Notificaciones</h1>
      <?php if ($unread > 0): ?>
      <span class="bg-red-500 text-white text-xs font-bold px-2 py-0.5 rounded-full"><?= $unread ?></span>
      <?php endif; ?>
    </div>
    <?php if ($unread > 0): ?>
    <form method="POST" action="<?= url('admin/notificaciones/leer-todas') ?>">
      <input type="hidden" name="_csrf" value="<?= e($csrf) ?>">
      <button type="submit"
        class="text-sm text-blue-600 hover:text-blue-800 font-medium transition">
        ✓ Marcar todas como leídas
      </button>
    </form>
    <?php endif; ?>
  </div>

  <?php if (empty($notifications)): ?>
  <div class="bg-white rounded-2xl shadow-sm p-12 text-center">
    <p class="text-4xl mb-3">🔔</p>
    <p class="text-gray-500 text-sm">No tienes notificaciones por el momento.</p>
  </div>
  <?php else: ?>
  <div class="space-y-3">
    <?php foreach ($notifications as $n): ?>
    <?php $isUnread = $n['read_at'] === null; ?>
    <div class="bg-white rounded-2xl shadow-sm p-5 flex gap-4 <?= $isUnread ? 'border-l-4 border-blue-500' : '' ?>"
         id="notif-<?= $n['id'] ?>">
      <div class="flex-shrink-0 mt-0.5 text-2xl">
        <?= match($n['type']) {
          'contact' => '📩',
          'review'  => '⭐',
          'status'  => '📋',
          default   => '🔔',
        } ?>
      </div>
      <div class="flex-1 min-w-0">
        <div class="flex items-start justify-between gap-2">
          <div>
            <p class="text-sm font-semibold text-gray-900 <?= $isUnread ? '' : 'font-normal' ?>">
              <?= e($n['title']) ?>
            </p>
            <?php if ($n['business_name']): ?>
            <p class="text-xs text-blue-600 mt-0.5"><?= e($n['business_name']) ?></p>
            <?php endif; ?>
          </div>
          <div class="flex items-center gap-2 flex-shrink-0">
            <span class="text-xs text-gray-400">
              <?php
                $dt = new DateTime($n['created_at']);
                echo timeAgo($dt);
              ?>
            </span>
            <?php if ($isUnread): ?>
            <button type="button"
              onclick="markRead(<?= $n['id'] ?>)"
              class="text-xs text-blue-600 hover:text-blue-800 transition whitespace-nowrap">
              Marcar leída
            </button>
            <?php else: ?>
            <span class="text-xs text-gray-400">Leída</span>
            <?php endif; ?>
          </div>
        </div>
        <?php if (!empty($n['message'])): ?>
        <p class="text-sm text-gray-600 mt-1"><?= e($n['message']) ?></p>
        <?php endif; ?>
      </div>
    </div>
    <?php endforeach; ?>
  </div>
  <?php endif; ?>
</main>

<script>
const csrf = '<?= e($csrf) ?>';

function markRead(id) {
  fetch('<?= url('admin/notificaciones') ?>/' + id + '/leer', {
    method: 'POST',
    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
    body: '_csrf=' + encodeURIComponent(csrf)
  })
  .then(r => r.json())
  .then(d => {
    if (d.ok) {
      const el = document.getElementById('notif-' + id);
      if (el) {
        el.classList.remove('border-l-4', 'border-blue-500');
        const btn = el.querySelector('button[onclick]');
        if (btn) {
          const span = document.createElement('span');
          span.className = 'text-xs text-gray-400';
          span.textContent = 'Leída';
          btn.replaceWith(span);
        }
      }
    }
  });
}
</script>

<?php require APP_PATH . '/views/layout/bottom_nav.php'; ?>
<?php require APP_PATH . '/views/layout/footer.php'; ?>
