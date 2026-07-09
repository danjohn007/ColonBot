<?php
$pageTitle = 'Inicio - ' . APP_NAME;
$routePrefix = routePrefix();
$leadCount = (int)($summary['lead_count'] ?? 0);
$convertedLeads = (int)($summary['converted_leads'] ?? 0);
$conversionRate = (float)($summary['conversion_rate'] ?? 0);
$campaignResponseRate = (float)($summary['campaign_response_rate'] ?? 0);
$weeklySales = (float)($summary['weekly_sales'] ?? 0);
$monthlySales = (float)($summary['monthly_sales'] ?? 0);
$topLovemarks = $summary['top_lovemarks'] ?? [];
$businessRows = $summary['business_rows'] ?? [];
$campaignRows = $summary['campaign_rows'] ?? [];
$salesByWeek = $summary['sales_by_week'] ?? [];
$salesByMonth = $summary['sales_by_month'] ?? [];
$contactsByMonth = $summary['contacts_by_month'] ?? [];
require APP_PATH . '/views/layout/head.php';
?>
<?php require APP_PATH . '/views/layout/navbar.php'; ?>

<main class="max-w-7xl mx-auto px-4 py-8 mb-24">
  <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4 mb-6">
    <div>
      <h1 class="text-2xl font-bold text-gray-900">Inicio</h1>
      <p class="text-sm text-gray-500 mt-1">Resumen de leads, conversion, respuesta comercial, Lovemarks y ventas.</p>
    </div>
    <div class="flex flex-wrap gap-2">
      <a href="<?= url($routePrefix . 'admin/crm') ?>" class="px-4 py-2 rounded-xl bg-blue-600 text-white text-sm font-semibold hover:bg-blue-700 transition">Abrir CRM</a>
      <a href="<?= url($routePrefix . 'admin/promociones') ?>" class="px-4 py-2 rounded-xl bg-white text-gray-700 border border-gray-200 text-sm font-semibold hover:bg-blue-50 transition">Promociones</a>
      <a href="<?= url($routePrefix . 'admin') ?>" class="px-4 py-2 rounded-xl bg-white text-gray-700 border border-gray-200 text-sm font-semibold hover:bg-blue-50 transition">Mis negocios</a>
    </div>
  </div>

  <?php if (empty($businesses)): ?>
  <section class="bg-white rounded-xl shadow-sm p-10 border border-gray-100 text-center">
    <h2 class="text-xl font-semibold text-gray-800 mb-2">Aun no tienes negocios registrados</h2>
    <p class="text-gray-500 mb-6">Crea tu primer negocio para comenzar a medir leads, conversion y ventas.</p>
    <a href="<?= url($routePrefix . 'admin/negocio/crear') ?>" class="inline-flex items-center justify-center bg-blue-600 text-white px-6 py-3 rounded-xl font-semibold hover:bg-blue-700 transition">Crear negocio</a>
  </section>
  <?php else: ?>

  <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4 mb-6">
    <section class="bg-white rounded-xl shadow-sm p-5 border border-gray-100">
      <p class="text-xs text-gray-500 uppercase font-semibold tracking-wide">Leads vs conversion</p>
      <div class="flex items-end justify-between gap-3 mt-3">
        <p class="text-3xl font-bold text-blue-600"><?= number_format($leadCount) ?></p>
        <p class="text-xl font-bold text-green-600"><?= number_format($conversionRate, 1) ?>%</p>
      </div>
      <p class="text-xs text-gray-400 mt-2"><?= number_format($convertedLeads) ?> leads convertidos</p>
    </section>

    <section class="bg-white rounded-xl shadow-sm p-5 border border-gray-100">
      <p class="text-xs text-gray-500 uppercase font-semibold tracking-wide">Respuesta eventos/promos</p>
      <p class="text-3xl font-bold text-orange-600 mt-3"><?= number_format($campaignResponseRate, 1) ?>%</p>
      <p class="text-xs text-gray-400 mt-2">
        <?= (int)($summary['campaign_inquiries'] ?? 0) ?> consultas de <?= (int)($summary['campaign_views'] ?? 0) ?> vistas
      </p>
    </section>

    <section class="bg-white rounded-xl shadow-sm p-5 border border-gray-100">
      <p class="text-xs text-gray-500 uppercase font-semibold tracking-wide">Top Lovemarks</p>
      <p class="text-3xl font-bold text-pink-600 mt-3"><?= count($topLovemarks) ?></p>
      <p class="text-xs text-gray-400 mt-2">Clientes frecuentes con mas compras</p>
    </section>

    <section class="bg-white rounded-xl shadow-sm p-5 border border-gray-100">
      <p class="text-xs text-gray-500 uppercase font-semibold tracking-wide">Ventas semana / mes</p>
      <div class="flex items-end justify-between gap-3 mt-3">
        <p class="text-2xl font-bold text-emerald-600">$<?= number_format($weeklySales, 2) ?></p>
        <p class="text-xl font-bold text-indigo-600">$<?= number_format($monthlySales, 2) ?></p>
      </div>
      <p class="text-xs text-gray-400 mt-2">Ultimos 7 dias y ultimo mes</p>
    </section>
  </div>

  <div class="grid grid-cols-1 xl:grid-cols-2 gap-6 mb-6">
    <section class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
      <div class="flex items-center justify-between gap-3 mb-4">
        <h2 class="font-semibold text-gray-900">Leads y conversion por mes</h2>
        <span class="text-xs px-3 py-1 rounded-full bg-blue-50 text-blue-700 font-semibold">6 meses</span>
      </div>
      <canvas id="providerContactsChart" height="180"></canvas>
    </section>

    <section class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
      <div class="flex items-center justify-between gap-3 mb-4">
        <h2 class="font-semibold text-gray-900">Monto de ventas</h2>
        <span class="text-xs px-3 py-1 rounded-full bg-emerald-50 text-emerald-700 font-semibold">Semana y mes</span>
      </div>
      <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <canvas id="providerWeeklySalesChart" height="180"></canvas>
        <canvas id="providerMonthlySalesChart" height="180"></canvas>
      </div>
    </section>
  </div>

  <div class="grid grid-cols-1 xl:grid-cols-3 gap-6 mb-6">
    <section class="bg-white rounded-xl shadow-sm p-6 border border-gray-100 xl:col-span-2">
      <h2 class="font-semibold text-gray-900 mb-4">Leads vs conversion por negocio</h2>
      <div class="overflow-x-auto">
        <table class="w-full text-sm min-w-[620px]">
          <thead>
            <tr class="text-left text-xs text-gray-500 uppercase tracking-wide border-b">
              <th class="py-3 pr-4">Negocio</th>
              <th class="py-3 pr-4">Leads</th>
              <th class="py-3 pr-4">Convertidos</th>
              <th class="py-3 pr-4">Conversion</th>
              <th class="py-3">Ventas</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-100">
            <?php foreach ($businessRows as $row): ?>
            <tr>
              <td class="py-3 pr-4 font-medium text-gray-900"><?= e($row['name']) ?></td>
              <td class="py-3 pr-4"><?= (int)$row['leads'] ?></td>
              <td class="py-3 pr-4"><?= (int)$row['converted'] ?></td>
              <td class="py-3 pr-4 font-semibold text-green-600"><?= number_format((float)$row['conversion_rate'], 1) ?>%</td>
              <td class="py-3 font-semibold text-gray-900">$<?= number_format((float)$row['sales'], 2) ?></td>
            </tr>
            <?php endforeach; ?>
            <?php if (empty($businessRows)): ?>
            <tr><td colspan="5" class="py-6 text-center text-gray-400">Sin leads registrados</td></tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </section>

    <section class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
      <h2 class="font-semibold text-gray-900 mb-4">Top Lovemarks</h2>
      <div class="space-y-3 max-h-96 overflow-y-auto">
        <?php foreach ($topLovemarks as $lovemark): ?>
        <div class="border border-gray-100 rounded-lg p-3">
          <div class="flex items-start justify-between gap-3">
            <div class="min-w-0">
              <p class="font-semibold text-sm text-gray-900 truncate"><?= e($lovemark['name']) ?></p>
              <p class="text-xs text-gray-500 truncate"><?= e($lovemark['business_name'] ?? '') ?></p>
            </div>
            <span class="text-sm font-bold text-pink-600 whitespace-nowrap">$<?= number_format((float)$lovemark['total_spent'], 2) ?></span>
          </div>
          <p class="text-xs text-gray-400 mt-2"><?= (int)$lovemark['purchases'] ?> compras</p>
        </div>
        <?php endforeach; ?>
        <?php if (empty($topLovemarks)): ?>
        <p class="text-sm text-gray-400 text-center py-6">Aun no hay Lovemarks registrados</p>
        <?php endif; ?>
      </div>
    </section>
  </div>

  <div class="grid grid-cols-1 xl:grid-cols-2 gap-6">
    <section class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
      <h2 class="font-semibold text-gray-900 mb-4">% de respuesta a eventos y promociones</h2>
      <div class="overflow-x-auto">
        <table class="w-full text-sm min-w-[620px]">
          <thead>
            <tr class="text-left text-xs text-gray-500 uppercase tracking-wide border-b">
              <th class="py-3 pr-4">Campana</th>
              <th class="py-3 pr-4">Tipo</th>
              <th class="py-3 pr-4">Vistas</th>
              <th class="py-3 pr-4">Consultas</th>
              <th class="py-3">Respuesta</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-100">
            <?php foreach ($campaignRows as $row): ?>
            <tr>
              <td class="py-3 pr-4">
                <p class="font-medium text-gray-900"><?= e($row['title']) ?></p>
                <p class="text-xs text-gray-400"><?= e($row['business_name']) ?></p>
              </td>
              <td class="py-3 pr-4 text-gray-600"><?= e($row['type']) ?></td>
              <td class="py-3 pr-4"><?= (int)$row['views'] ?></td>
              <td class="py-3 pr-4"><?= (int)$row['inquiries'] ?></td>
              <td class="py-3 font-semibold text-orange-600"><?= number_format((float)$row['response_rate'], 1) ?>%</td>
            </tr>
            <?php endforeach; ?>
            <?php if (empty($campaignRows)): ?>
            <tr><td colspan="5" class="py-6 text-center text-gray-400">Sin eventos o promociones con respuesta</td></tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </section>

    <section class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
      <h2 class="font-semibold text-gray-900 mb-4">Ventas recientes</h2>
      <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
          <p class="text-xs text-gray-500 uppercase font-semibold mb-3">Por semana</p>
          <div class="space-y-2 max-h-72 overflow-y-auto">
            <?php foreach ($salesByWeek as $row): ?>
            <div class="flex items-center justify-between gap-3 border-b border-gray-100 pb-2 text-sm">
              <span class="text-gray-600"><?= e($row['label']) ?></span>
              <strong class="text-gray-900">$<?= number_format((float)$row['total'], 2) ?></strong>
            </div>
            <?php endforeach; ?>
            <?php if (empty($salesByWeek)): ?>
            <p class="text-sm text-gray-400">Sin ventas semanales</p>
            <?php endif; ?>
          </div>
        </div>
        <div>
          <p class="text-xs text-gray-500 uppercase font-semibold mb-3">Por mes</p>
          <div class="space-y-2 max-h-72 overflow-y-auto">
            <?php foreach ($salesByMonth as $row): ?>
            <div class="flex items-center justify-between gap-3 border-b border-gray-100 pb-2 text-sm">
              <span class="text-gray-600"><?= e($row['label']) ?></span>
              <strong class="text-gray-900">$<?= number_format((float)$row['total'], 2) ?></strong>
            </div>
            <?php endforeach; ?>
            <?php if (empty($salesByMonth)): ?>
            <p class="text-sm text-gray-400">Sin ventas mensuales</p>
            <?php endif; ?>
          </div>
        </div>
      </div>
    </section>
  </div>
  <?php endif; ?>
</main>

<?php if (!empty($businesses)): ?>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
const contactsByMonth = <?= json_encode($contactsByMonth, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;
const salesByWeek = <?= json_encode($salesByWeek, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;
const salesByMonth = <?= json_encode($salesByMonth, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;

function numeric(value) {
  return Number.parseFloat(value || 0);
}

function renderProviderContactsChart() {
  const canvas = document.getElementById('providerContactsChart');
  if (!canvas || typeof Chart === 'undefined') return;

  new Chart(canvas.getContext('2d'), {
    type: 'bar',
    data: {
      labels: contactsByMonth.map(row => row.label),
      datasets: [
        {
          label: 'Leads',
          data: contactsByMonth.map(row => numeric(row.total)),
          backgroundColor: '#2563eb',
          borderRadius: 6,
        },
        {
          label: 'Convertidos',
          data: contactsByMonth.map(row => numeric(row.converted)),
          backgroundColor: '#16a34a',
          borderRadius: 6,
        },
      ],
    },
    options: {
      responsive: true,
      plugins: { legend: { position: 'top', labels: { boxWidth: 12 } } },
      scales: { y: { beginAtZero: true, ticks: { precision: 0 } } },
    },
  });
}

function renderProviderSalesChart() {
  const weeklyCanvas = document.getElementById('providerWeeklySalesChart');
  const monthlyCanvas = document.getElementById('providerMonthlySalesChart');
  if (typeof Chart === 'undefined') return;

  if (weeklyCanvas) {
    new Chart(weeklyCanvas.getContext('2d'), {
      type: 'line',
      data: {
        labels: salesByWeek.map(row => row.label),
        datasets: [
          {
            label: 'Ventas por semana',
            data: salesByWeek.map(row => numeric(row.total)),
            borderColor: '#059669',
            backgroundColor: 'rgba(5, 150, 105, 0.12)',
            fill: true,
            tension: 0.35,
          },
        ],
      },
      options: {
        responsive: true,
        plugins: { legend: { position: 'top', labels: { boxWidth: 12 } } },
        scales: { y: { beginAtZero: true } },
      },
    });
  }

  if (monthlyCanvas) {
    new Chart(monthlyCanvas.getContext('2d'), {
    type: 'line',
    data: {
      labels: salesByMonth.map(row => row.label),
      datasets: [
        {
          label: 'Ventas por mes',
          data: salesByMonth.map(row => numeric(row.total)),
          borderColor: '#4f46e5',
          backgroundColor: 'rgba(79, 70, 229, 0.12)',
          fill: true,
          tension: 0.35,
        },
      ],
    },
    options: {
      responsive: true,
      plugins: { legend: { position: 'top', labels: { boxWidth: 12 } } },
      scales: { y: { beginAtZero: true } },
    },
  });
  }
}

renderProviderContactsChart();
renderProviderSalesChart();
</script>
<?php endif; ?>

<?php require APP_PATH . '/views/layout/bottom_nav.php'; ?>
<?php require APP_PATH . '/views/layout/footer.php'; ?>
