<?php
$pageTitle = 'Micrositio: ' . e($business['name']) . ' – ' . APP_NAME;
$isEdit = true;
require APP_PATH . '/views/layout/head.php';
?>
<?php require APP_PATH . '/views/layout/navbar.php'; ?>

<main class="max-w-6xl mx-auto px-4 py-8 mb-24">
  <!-- Header -->
  <div class="flex items-center justify-between mb-6">
    <div>
      <a href="<?= url('admin/micrositio') ?>" class="text-gray-500 hover:text-blue-600 text-sm">&larr; Volver al micrositio</a>
      <h1 class="text-2xl font-bold text-gray-900 mt-1">📊 <?= e($business['name']) ?></h1>
    </div>
    <div class="flex items-center gap-3">
      <!-- Toggle open/close -->
      <button id="toggle-open-btn" onclick="toggleOpen(<?= $business['id'] ?>)"
        class="flex items-center gap-2 px-4 py-2 rounded-xl text-sm font-medium transition <?= $business['is_open'] ? 'bg-green-50 text-green-700 border border-green-200' : 'bg-red-50 text-red-700 border border-red-200' ?>">
        <span id="open-status-dot" class="w-3 h-3 rounded-full <?= $business['is_open'] ? 'bg-green-500' : 'bg-red-500' ?>"></span>
        <span id="open-status-text"><?= $business['is_open'] ? 'Abierto' : 'Cerrado' ?></span>
      </button>
      <a href="<?= url('admin/negocio/' . $business['id']) ?>" class="px-4 py-2 bg-blue-600 text-white rounded-xl text-sm font-medium hover:bg-blue-700 transition">
        ✏️ Editar negocio
      </a>
    </div>
  </div>

    <!-- Stats Cards Row - Same as superadmin analytics -->
  <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4 mb-6">
    <div class="bg-white rounded-xl shadow-sm p-4 border border-gray-100">
      <p class="text-xs text-gray-500 uppercase font-semibold tracking-wide">🗺️ Vistas Mapa</p>
      <p class="text-2xl font-bold text-blue-600 mt-1" id="stat-map"><?= $mapViews ?></p>
    </div>
    <div class="bg-white rounded-xl shadow-sm p-4 border border-gray-100">
      <p class="text-xs text-gray-500 uppercase font-semibold tracking-wide">💬 Clicks WhatsApp</p>
      <p class="text-2xl font-bold text-green-600 mt-1" id="stat-wa"><?= $waClicks ?></p>
    </div>
    <div class="bg-white rounded-xl shadow-sm p-4 border border-gray-100">
      <p class="text-xs text-gray-500 uppercase font-semibold tracking-wide">🧭 Indicaciones</p>
      <p class="text-2xl font-bold text-orange-600 mt-1"><?php $dbDir = Database::getInstance(); echo (int)$dbDir->query("SELECT COUNT(*) FROM analytics WHERE business_id = ? AND event = 'directions_click'", [(int)$business['id']])->fetchColumn(); ?></p>
    </div>
    <div class="bg-white rounded-xl shadow-sm p-4 border border-gray-100">
      <p class="text-xs text-gray-500 uppercase font-semibold tracking-wide">🤖 Sesiones Chatbot</p>
      <p class="text-2xl font-bold text-purple-600 mt-1"><?php $dbBot = Database::getInstance(); echo (int)$dbBot->query("SELECT COUNT(*) FROM chatbot_sessions cs LEFT JOIN contacts c ON c.wa_id = cs.wa_id WHERE c.business_id = ?", [(int)$business['id']])->fetchColumn(); ?></p>
    </div>
    <div class="bg-white rounded-xl shadow-sm p-4 border border-gray-100">
      <p class="text-xs text-gray-500 uppercase font-semibold tracking-wide">🎉 Total eventos</p>
      <p class="text-2xl font-bold text-pink-600 mt-1"><?= count($events) ?></p>
    </div>
    <div class="bg-white rounded-xl shadow-sm p-4 border border-gray-100">
      <p class="text-xs text-gray-500 uppercase font-semibold tracking-wide">⭐ Calificación</p>
      <p class="text-2xl font-bold text-yellow-500 mt-1"><?= number_format($business['rating'], 1) ?></p>
    </div>
  </div>

  <!-- Charts Row -->
  <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
    <!-- Chart: Activity & Interactions -->
    <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
      <div class="flex items-center justify-between mb-4">
        <h2 class="font-semibold text-gray-900">📈 Actividad del negocio</h2>
        <select id="chart-period" onchange="loadChart(<?= $business['id'] ?>)"
          class="text-sm border border-gray-300 rounded-lg px-3 py-1.5 focus:outline-none focus:ring-2 focus:ring-blue-500">
          <option value="week">Por semana</option>
          <option value="month" selected>Por mes</option>
          <option value="year">Por año</option>
        </select>
      </div>
      <canvas id="activityChart" height="200"></canvas>
    </div>

    <!-- Contacts Chart -->
    <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
      <h2 class="font-semibold text-gray-900 mb-4">📊 Contactos CRM</h2>
      <canvas id="contactsChart" height="200"></canvas>
    </div>

    <!-- Top Negocios (same style as analytics page) -->
    <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
      <h2 class="font-semibold text-gray-900 mb-4">🏆 Visitas acumuladas</h2>
      <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
        <span class="text-sm font-medium text-gray-700">Vistas en mapa</span>
        <span class="text-lg font-bold text-blue-600"><?= $mapViews ?></span>
      </div>
      <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg mt-2">
        <span class="text-sm font-medium text-gray-700">Clicks WhatsApp</span>
        <span class="text-lg font-bold text-green-600"><?= $waClicks ?></span>
      </div>
      <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg mt-2">
        <span class="text-sm font-medium text-gray-700">Indicaciones</span>
        <span class="text-lg font-bold text-orange-600"><?php $dbDir2 = Database::getInstance(); echo (int)$dbDir2->query("SELECT COUNT(*) FROM analytics WHERE business_id = ? AND event = 'directions_click'", [(int)$business['id']])->fetchColumn(); ?></span>
      </div>
    </div>
  </div>

  <!-- Reseñas -->
  <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100 mb-6">
    <h2 class="font-semibold text-gray-900 mb-4">💬 Últimas reseñas</h2>
    <?php if (empty($reviews)): ?>
      <p class="text-gray-400 text-sm text-center py-4">Aún no hay reseñas</p>
    <?php else: ?>
      <div class="space-y-3 max-h-64 overflow-y-auto">
        <?php foreach (array_slice($reviews, 0, 5) as $r): ?>
        <div class="flex items-start gap-3 p-3 border border-gray-100 rounded-lg">
          <div class="w-8 h-8 rounded-full bg-blue-100 flex items-center justify-center text-blue-600 font-bold text-sm shrink-0">
            <?= mb_substr($r['user_name'], 0, 1) ?>
          </div>
          <div class="flex-1 min-w-0">
            <p class="font-medium text-gray-800 text-sm"><?= e($r['user_name']) ?></p>
            <div class="text-yellow-400 text-xs"><?= str_repeat('★', (int)$r['rating']) . str_repeat('☆', 5 - (int)$r['rating']) ?></div>
            <?php if ($r['comment']): ?>
            <p class="text-gray-500 text-xs mt-1"><?= e($r['comment']) ?></p>
            <?php endif; ?>
            <p class="text-gray-400 text-xs mt-1"><?= date('d/m/Y', strtotime($r['created_at'])) ?></p>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </div>

  <!-- Quick Links -->
  <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
    <a href="<?= url('admin/crm') ?>" class="bg-white rounded-xl shadow-sm p-6 border border-gray-100 hover:shadow-md transition text-center">
      <span class="text-3xl">📇</span>
      <h3 class="font-semibold text-gray-900 mt-2">CRM</h3>
      <p class="text-xs text-gray-400 mt-1">Administra tus contactos</p>
    </a>
    <a href="<?= url('admin/promociones') ?>" class="bg-white rounded-xl shadow-sm p-6 border border-gray-100 hover:shadow-md transition text-center">
      <span class="text-3xl">🎉</span>
      <h3 class="font-semibold text-gray-900 mt-2">Promociones</h3>
      <p class="text-xs text-gray-400 mt-1">Crea y envía promociones</p>
    </a>
    <a href="<?= url('admin/negocio/' . $business['id']) ?>" class="bg-white rounded-xl shadow-sm p-6 border border-gray-100 hover:shadow-md transition text-center">
      <span class="text-3xl">⚙️</span>
      <h3 class="font-semibold text-gray-900 mt-2">Configuración</h3>
      <p class="text-xs text-gray-400 mt-1">Edita tu negocio</p>
    </a>
  </div>
</main>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
const CSRF = '<?= e($csrf) ?>';
const BASE_URL = '<?= BASE_URL ?>';

// ── Toggle Open/Close ──────────────────────────────────────────────
function toggleOpen(businessId) {
  const btn = document.getElementById('toggle-open-btn');
  const dot = document.getElementById('open-status-dot');
  const txt = document.getElementById('open-status-text');

  const body = new URLSearchParams({ _csrf: CSRF });
  fetch(`${BASE_URL}/admin/micrositio/${businessId}/toggle`, {
    method: 'POST',
    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
    body: body.toString(),
  })
  .then(r => r.json())
  .then(d => {
    if (d.ok) {
      const isOpen = d.is_open;
      dot.className = `w-3 h-3 rounded-full ${isOpen ? 'bg-green-500' : 'bg-red-500'}`;
      txt.textContent = isOpen ? 'Abierto' : 'Cerrado';
      btn.className = `flex items-center gap-2 px-4 py-2 rounded-xl text-sm font-medium transition ${isOpen ? 'bg-green-50 text-green-700 border border-green-200' : 'bg-red-50 text-red-700 border border-red-200'}`;
    }
  });
}

// ── Activity Chart ─────────────────────────────────────────────────
function loadChart(businessId) {
  const period = document.getElementById('chart-period').value;
  fetch(`${BASE_URL}/admin/micrositio/${businessId}/graficas?period=${period}`)
    .then(r => r.json())
    .then(data => {
      renderActivityChart(data);
    });
}

let activityChartInstance = null;
function renderActivityChart(data) {
  const ctx = document.getElementById('activityChart').getContext('2d');
  if (activityChartInstance) activityChartInstance.destroy();

  activityChartInstance = new Chart(ctx, {
    type: 'line',
    data: {
      labels: data.map(d => d.label),
      datasets: [
        {
          label: 'Vistas en mapa',
          data: data.map(d => parseInt(d.map_views)),
          borderColor: '#10B981',
          backgroundColor: 'rgba(16, 185, 129, 0.1)',
          fill: true,
          tension: 0.4,
        },
        {
          label: 'WhatsApp clicks',
          data: data.map(d => parseInt(d.wa_clicks)),
          borderColor: '#3B82F6',
          backgroundColor: 'rgba(59, 130, 246, 0.1)',
          fill: true,
          tension: 0.4,
        },
      ],
    },
    options: {
      responsive: true,
      plugins: {
        legend: { position: 'top', labels: { boxWidth: 12, padding: 12 } },
      },
      scales: {
        y: { beginAtZero: true, ticks: { precision: 0 } },
      },
    },
  });
}

// ── Contacts Chart ─────────────────────────────────────────────────
const contactsData = <?= json_encode($chartData) ?>;
let contactsChartInstance = null;

function renderContactsChart() {
  const ctx = document.getElementById('contactsChart').getContext('2d');
  if (contactsChartInstance) contactsChartInstance.destroy();

  contactsChartInstance = new Chart(ctx, {
    type: 'bar',
    data: {
      labels: contactsData.map(d => d.label),
      datasets: [
        {
          label: 'Prospectos',
          data: contactsData.map(d => parseInt(d.prospectos)),
          backgroundColor: '#8B5CF6',
        },
        {
          label: 'Clientes',
          data: contactsData.map(d => parseInt(d.clientes)),
          backgroundColor: '#F59E0B',
        },
        {
          label: 'Lovemarks',
          data: contactsData.map(d => parseInt(d.lovemarks)),
          backgroundColor: '#EC4899',
        },
      ],
    },
    options: {
      responsive: true,
      plugins: {
        legend: { position: 'top', labels: { boxWidth: 12, padding: 12 } },
      },
      scales: {
        y: { beginAtZero: true, ticks: { precision: 0 } },
      },
    },
  });
}

// Init
loadChart(<?= $business['id'] ?>);
renderContactsChart();
</script>
<?php require APP_PATH . '/views/layout/bottom_nav.php'; ?>
<?php require APP_PATH . '/views/layout/footer.php'; ?>