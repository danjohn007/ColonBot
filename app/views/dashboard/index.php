<?php
$pageTitle = 'Dashboard SuperAdmin – ' . APP_NAME;
require APP_PATH . '/views/layout/head.php';
?>
<?php require APP_PATH . '/views/layout/navbar.php'; ?>

<main class="max-w-7xl mx-auto px-4 py-8 mb-20">
  <div class="mb-6">
    <h1 class="text-2xl font-bold text-gray-900">Dashboard SuperAdmin</h1>
    <p class="text-gray-500 text-sm mt-0.5">Vista general del sistema</p>
  </div>

  <!-- Stats cards -->
  <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-4 mb-8">
    <?php $cards = [
      ['label'=>'Negocios','value'=>$bizCount,'icon'=>'🏢','color'=>'blue'],
      ['label'=>'Usuarios','value'=>$userCount,'icon'=>'👥','color'=>'purple'],
      ['label'=>'Vistas mapa','value'=>$summary['map_views'],'icon'=>'🗺️','color'=>'teal'],
      ['label'=>'Clicks WhatsApp','value'=>$summary['whatsapp_clicks'],'icon'=>'💬','color'=>'green'],
      ['label'=>'Sesiones chatbot','value'=>$summary['chatbot_sessions'],'icon'=>'🤖','color'=>'orange'],
    ];
    foreach ($cards as $c):
    ?>
    <div class="bg-white rounded-2xl shadow-sm p-5 flex items-center gap-4">
      <div class="text-3xl"><?= $c['icon'] ?></div>
      <div>
        <p class="text-2xl font-bold text-gray-900"><?= number_format($c['value']) ?></p>
        <p class="text-xs text-gray-500"><?= $c['label'] ?></p>
      </div>
    </div>
    <?php endforeach; ?>
  </div>

  <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
    <!-- Chart: Daily Events -->
    <div class="bg-white rounded-2xl shadow-sm p-6">
      <h2 class="font-semibold text-gray-900 mb-4">📈 Eventos diarios (últimos 30 días)</h2>
      <div id="chart-events"></div>
    </div>

    <!-- Top negocios -->
    <div class="bg-white rounded-2xl shadow-sm p-6">
      <h2 class="font-semibold text-gray-900 mb-4">🏆 Top Negocios visitados</h2>
      <?php if ($topBiz): ?>
      <div class="space-y-3">
        <?php foreach ($topBiz as $i => $b): ?>
        <div class="flex items-center gap-3">
          <span class="w-6 h-6 rounded-full bg-blue-100 text-blue-700 text-xs font-bold flex items-center justify-center"><?= $i+1 ?></span>
          <div class="flex-1 min-w-0">
            <p class="text-sm font-medium text-gray-800 truncate"><?= e($b['name']) ?></p>
            <div class="w-full bg-gray-100 rounded-full h-1.5 mt-1">
              <div class="bg-blue-500 h-1.5 rounded-full" style="width:<?= min(100, (int)$b['visits'] * 10) ?>%"></div>
            </div>
          </div>
          <span class="text-sm font-semibold text-gray-700 shrink-0"><?= number_format($b['visits']) ?></span>
        </div>
        <?php endforeach; ?>
      </div>
      <?php else: ?>
      <p class="text-gray-400 text-sm">Sin datos aún</p>
      <?php endif; ?>
    </div>
  </div>

  <!-- Quick links -->
  <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-4">
    <?php $links = [
      ['Gestión de Usuarios',   'superadmin/usuarios',   '👥'],
      ['Gestión de Negocios',   'superadmin/negocios',   '🏢'],
      ['Categorías',            'superadmin/categorias', '🏷️'],
      ['Analítica',             'superadmin/analitica',  '📊'],
      ['Bitácora de Acciones',  'superadmin/bitacora',   '📋'],
      ['Registro de Errores',   'superadmin/errores',    '🚨'],
      ['Configuraciones',       'configuraciones',        '⚙️'],
      ['Ver Mapa Público',      'mapa',                  '🗺️'],
    ];
    foreach ($links as [$label, $path, $icon]):
    ?>
    <a href="<?= url($path) ?>"
      class="bg-white rounded-2xl shadow-sm p-5 flex flex-col items-center gap-3 hover:shadow-md hover:-translate-y-0.5 transition-all text-center group">
      <span class="text-3xl group-hover:scale-110 transition-transform"><?= $icon ?></span>
      <span class="text-sm font-medium text-gray-700"><?= $label ?></span>
    </a>
    <?php endforeach; ?>
  </div>
</main>

<?php require APP_PATH . '/views/layout/bottom_nav.php'; ?>
<script>
<?php
// Prepare chart data
$labels  = [];
$mapData = [];
$waData  = [];
$dateGroups = [];
foreach ($dailyEvents as $row) {
    $dateGroups[$row['day']][$row['event']] = (int)$row['total'];
}
foreach ($dateGroups as $day => $events) {
    $labels[]  = $day;
    $mapData[] = $events['map_view'] ?? 0;
    $waData[]  = $events['whatsapp_click'] ?? 0;
}
?>
const options = {
  series: [
    { name: 'Vistas mapa', data: <?= json_encode($mapData) ?> },
    { name: 'WhatsApp', data: <?= json_encode($waData) ?> },
  ],
  chart: { type: 'area', height: 220, toolbar: { show: false }, sparkline: { enabled: false } },
  xaxis: { categories: <?= json_encode($labels) ?>, labels: { show: <?= count($labels) > 0 ? 'true' : 'false' ?>, rotate: -45, style: { fontSize: '10px' } } },
  colors: ['#3B82F6', '#10B981'],
  stroke: { curve: 'smooth', width: 2 },
  fill: { type: 'gradient', gradient: { opacityFrom: 0.4, opacityTo: 0 } },
  dataLabels: { enabled: false },
  tooltip: { x: { format: 'dd MMM' } },
  legend: { position: 'top' },
};
new ApexCharts(document.getElementById('chart-events'), options).render();
</script>
<?php require APP_PATH . '/views/layout/footer.php'; ?>
