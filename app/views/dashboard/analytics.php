<?php
$pageTitle = 'Analítica – SuperAdmin';
require APP_PATH . '/views/layout/head.php';
?>
<?php require APP_PATH . '/views/layout/navbar.php'; ?>
<main class="max-w-7xl mx-auto px-4 py-8 mb-20">
  <h1 class="text-2xl font-bold text-gray-900 mb-6">📊 Analítica</h1>

  <!-- Summary cards -->
  <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-4 mb-8">
    <?php $cards = [
      ['Vistas mapa',       $summary['map_views'],         '🗺️'],
      ['Clicks WhatsApp',   $summary['whatsapp_clicks'],   '💬'],
      ['Indicaciones',      $summary['directions_clicks'], '🧭'],
      ['Sesiones chatbot',  $summary['chatbot_sessions'],  '🤖'],
      ['Total eventos',     $summary['total_events'],      '📊'],
    ];
    foreach ($cards as [$label, $val, $icon]):
    ?>
    <div class="bg-white rounded-2xl shadow-sm p-5 text-center">
      <div class="text-3xl mb-2"><?= $icon ?></div>
      <p class="text-2xl font-bold text-gray-900"><?= number_format($val) ?></p>
      <p class="text-xs text-gray-500"><?= $label ?></p>
    </div>
    <?php endforeach; ?>
  </div>

  <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    <!-- Chart -->
    <div class="bg-white rounded-2xl shadow-sm p-6">
      <h2 class="font-semibold text-gray-900 mb-4">Eventos diarios (30 días)</h2>
      <div id="chart-daily"></div>
    </div>

    <!-- Top Negocios -->
    <div class="bg-white rounded-2xl shadow-sm p-6">
      <h2 class="font-semibold text-gray-900 mb-4">Top 10 Negocios</h2>
      <?php foreach ($topBiz as $i => $b): ?>
      <div class="flex items-center gap-3 mb-3">
        <span class="text-xs font-bold text-gray-400 w-5"><?= $i+1 ?>.</span>
        <div class="flex-1">
          <p class="text-sm font-medium text-gray-800"><?= e($b['name']) ?></p>
          <div class="w-full bg-gray-100 rounded-full h-2 mt-1">
            <div class="bg-blue-500 h-2 rounded-full" style="width:<?= min(100, (int)$b['visits'] * 10) ?>%"></div>
          </div>
        </div>
        <span class="text-sm font-bold text-gray-700"><?= $b['visits'] ?></span>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</main>

<script>
<?php
$labels  = [];
$mapData = [];
$waData  = [];
$waDir   = [];
$dateGroups = [];
foreach ($dailyEvents as $row) {
    $dateGroups[$row['day']][$row['event']] = (int)$row['total'];
}
foreach ($dateGroups ?? [] as $day => $events) {
    $labels[]  = $day;
    $mapData[] = $events['map_view'] ?? 0;
    $waData[]  = $events['whatsapp_click'] ?? 0;
    $waDir[]   = $events['directions_click'] ?? 0;
}
?>
new ApexCharts(document.getElementById('chart-daily'), {
  series: [
    { name: 'Mapa', data: <?= json_encode($mapData) ?> },
    { name: 'WhatsApp', data: <?= json_encode($waData) ?> },
    { name: 'Cómo llegar', data: <?= json_encode($waDir) ?> },
  ],
  chart: { type: 'line', height: 280, toolbar: { show: false } },
  xaxis: { categories: <?= json_encode($labels) ?>, labels: { rotate: -45, style: { fontSize: '10px' } } },
  colors: ['#3B82F6','#10B981','#F59E0B'],
  stroke: { curve: 'smooth', width: 2 },
  dataLabels: { enabled: false },
  legend: { position: 'top' },
}).render();
</script>
<?php require APP_PATH . '/views/layout/bottom_nav.php'; ?>
<?php require APP_PATH . '/views/layout/footer.php'; ?>
