<?php
$topLimit = (int)($_GET['top'] ?? 20);
if (!in_array($topLimit, [20, 50, 100], true)) {
  $topLimit = 20;
}

$monthName = function($month) {
  $names = [
    1 => 'Enero', 2 => 'Febrero', 3 => 'Marzo', 4 => 'Abril',
    5 => 'Mayo', 6 => 'Junio', 7 => 'Julio', 8 => 'Agosto',
    9 => 'Septiembre', 10 => 'Octubre', 11 => 'Noviembre', 12 => 'Diciembre',
  ];
  return $names[(int)$month] ?? (string)$month;
};

$tripLabel = function($type) {
  $labels = [
    'familiar' => 'Familias',
    'pareja' => 'Pareja',
    'amigos' => 'Grupos de amigos',
    'adultos_mayores' => 'Adultos mayores',
    'petfriendly' => 'Petfriendly',
  ];
  return $labels[$type] ?? ucfirst((string)$type);
};

$providerActionBase = url(routePrefix() . 'colaborador/negocios');
?>

<style>
.admin-tourism-dashboard table {
  border-collapse: separate;
  border-spacing: 0;
  min-width: 680px;
}
.admin-tourism-dashboard thead {
  position: sticky;
  top: 0;
  z-index: 1;
  background: #f9fafb;
}
.admin-tourism-dashboard th {
  padding: .85rem 1rem;
  color: #6b7280;
  font-size: .72rem;
  font-weight: 800;
  letter-spacing: .05em;
  text-transform: uppercase;
  white-space: nowrap;
}
.admin-tourism-dashboard td {
  padding: .9rem 1rem;
  color: #374151;
  line-height: 1.45;
  vertical-align: middle;
}
.admin-tourism-dashboard tbody tr:nth-child(even) {
  background: #fcfcfd;
}
.admin-tourism-dashboard tbody tr:hover {
  background: #eff6ff;
}
.admin-tourism-dashboard td:first-child,
.admin-tourism-dashboard th:first-child {
  padding-left: 1.1rem;
}
.admin-tourism-dashboard .overflow-x-auto {
  border-radius: .85rem;
}
.admin-provider-actions {
  display: flex;
  flex-wrap: wrap;
  gap: .4rem;
  min-width: 21rem;
}
.provider-action {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  min-height: 2rem;
  border-radius: .65rem;
  padding: .45rem .7rem;
  font-size: .75rem;
  font-weight: 800;
  transition: background-color .2s ease, color .2s ease, opacity .2s ease;
  white-space: nowrap;
}
.provider-action:disabled {
  cursor: not-allowed;
  opacity: .45;
}
.provider-action-whatsapp {
  background: #ecfdf5;
  color: #047857;
}
.provider-action-email {
  background: #eff6ff;
  color: #1d4ed8;
}
.provider-action-reset {
  background: #fff7ed;
  color: #c2410c;
}
.provider-action-whatsapp:not(:disabled):hover {
  background: #d1fae5;
}
.provider-action-email:not(:disabled):hover {
  background: #dbeafe;
}
.provider-action-reset:hover {
  background: #fed7aa;
}
</style>

<main class="admin-tourism-dashboard max-w-7xl mx-auto px-4 py-8 mb-24">
  <div class="flex flex-wrap items-center justify-between gap-3 mb-6">
    <div>
      <h1 class="text-2xl font-bold text-gray-900"><?= e($dashboardTitle ?? 'Dashboard turistico') ?></h1>
      <p class="text-sm text-gray-500 mt-1"><?= e($dashboardSubtitle ?? 'Sitios, rankings, prestadores, visitas, rutas y estacionalidad.') ?></p>
    </div>
    <div class="flex gap-2">
      <?php foreach ([20, 50, 100] as $limit): ?>
      <a href="?top=<?= $limit ?>" class="px-3 py-2 rounded-lg text-sm font-semibold <?= $topLimit === $limit ? 'bg-blue-600 text-white' : 'bg-white text-gray-600 border border-gray-200 hover:bg-blue-50' ?>">
        Top <?= $limit ?>
      </a>
      <?php endforeach; ?>
    </div>
  </div>

  <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
    <div class="bg-white rounded-xl shadow-sm p-4 border border-gray-100">
      <p class="text-xs text-gray-500 uppercase font-semibold">Sitios mas visitados</p>
      <p class="text-2xl font-bold text-blue-600 mt-1"><?= count($topSites ?? []) ?></p>
    </div>
    <div class="bg-white rounded-xl shadow-sm p-4 border border-gray-100">
      <p class="text-xs text-gray-500 uppercase font-semibold">Rutas visitadas</p>
      <p class="text-2xl font-bold text-green-600 mt-1"><?= count($topRoutes ?? []) ?></p>
    </div>
    <div class="bg-white rounded-xl shadow-sm p-4 border border-gray-100">
      <p class="text-xs text-gray-500 uppercase font-semibold">Prestadores nuevos</p>
      <p class="text-2xl font-bold text-purple-600 mt-1"><?= count($newProviders ?? []) ?></p>
    </div>
    <div class="bg-white rounded-xl shadow-sm p-4 border border-gray-100">
      <p class="text-xs text-gray-500 uppercase font-semibold">Vistas mapa</p>
      <p class="text-2xl font-bold text-orange-600 mt-1"><?= number_format((int)($summary['map_views'] ?? 0)) ?></p>
    </div>
  </div>

  <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
    <section class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
      <h2 class="font-semibold text-gray-900 mb-4">Sitios mas visitados</h2>
      <div class="overflow-x-auto max-h-96 overflow-y-auto">
        <table class="w-full text-sm">
          <thead>
            <tr class="text-left text-xs text-gray-500 uppercase tracking-wide border-b">
              <th class="pb-2 pr-4">#</th>
              <th class="pb-2 pr-4">Sitio</th>
              <th class="pb-2 pr-4">Categoria</th>
              <th class="pb-2 pr-4">Visitas</th>
              <th class="pb-2">Rating</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach (array_slice($topSites ?? [], 0, $topLimit) as $i => $site): ?>
            <tr class="border-b last:border-0">
              <td class="py-2 pr-4 text-gray-400"><?= $i + 1 ?></td>
              <td class="py-2 pr-4 font-medium"><?= e($site['name']) ?></td>
              <td class="py-2 pr-4 text-gray-500"><?= e($site['category'] ?? '') ?></td>
              <td class="py-2 pr-4"><?= (int)($site['tracked_visits'] ?? $site['visits'] ?? 0) ?></td>
              <td class="py-2"><?= number_format((float)($site['rating'] ?? 0), 1) ?></td>
            </tr>
            <?php endforeach; ?>
            <?php if (empty($topSites)): ?>
            <tr><td colspan="5" class="py-6 text-center text-gray-400">Sin visitas registradas</td></tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </section>

    <section class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
      <h2 class="font-semibold text-gray-900 mb-4">Ultimos sitios super rankeados</h2>
      <div class="space-y-3 max-h-96 overflow-y-auto">
        <?php foreach ($recentTopReviews ?? [] as $review): ?>
        <div class="border border-gray-100 rounded-lg p-3">
          <div class="flex items-center justify-between gap-3">
            <p class="font-semibold text-sm text-gray-900"><?= e($review['business_name']) ?></p>
            <span class="text-yellow-500 text-sm"><?= str_repeat('*', (int)$review['rating']) ?></span>
          </div>
          <p class="text-xs text-gray-500"><?= e($review['category_name']) ?> - <?= date('d/m/Y', strtotime($review['created_at'])) ?></p>
          <?php if (!empty($review['comment'])): ?>
          <p class="text-sm text-gray-600 mt-2"><?= e($review['comment']) ?></p>
          <?php endif; ?>
        </div>
        <?php endforeach; ?>
        <?php if (empty($recentTopReviews)): ?>
        <p class="text-sm text-gray-400 text-center py-4">Sin resenas destacadas recientes</p>
        <?php endif; ?>
      </div>
    </section>
  </div>

  <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
    <section class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
      <h2 class="font-semibold text-gray-900 mb-4">Mejores lugares rankeados por categoria</h2>
      <div class="overflow-x-auto max-h-96 overflow-y-auto">
        <table class="w-full text-sm">
          <thead>
            <tr class="text-left text-xs text-gray-500 uppercase tracking-wide border-b">
              <th class="pb-2 pr-4">Rank</th>
              <th class="pb-2 pr-4">Categoria</th>
              <th class="pb-2 pr-4">Negocio</th>
              <th class="pb-2 pr-4">Rating</th>
              <th class="pb-2">Visitas</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach (array_filter($topByCategory ?? [], fn($t) => (int)$t['rn'] <= $topLimit) as $t): ?>
            <tr class="border-b last:border-0">
              <td class="py-2 pr-4 text-gray-400"><?= (int)$t['rn'] ?></td>
              <td class="py-2 pr-4 text-gray-500"><?= e($t['category']) ?></td>
              <td class="py-2 pr-4 font-medium"><?= e($t['name']) ?></td>
              <td class="py-2 pr-4"><?= number_format((float)$t['rating'], 1) ?></td>
              <td class="py-2"><?= (int)$t['visits'] ?></td>
            </tr>
            <?php endforeach; ?>
            <?php if (empty($topByCategory)): ?>
            <tr><td colspan="5" class="py-6 text-center text-gray-400">Sin ranking registrado</td></tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </section>

    <section class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
      <h2 class="font-semibold text-gray-900 mb-4">Nuevos prestadores registrados</h2>
      <div class="space-y-3 max-h-96 overflow-y-auto">
        <?php foreach (array_slice($newProviders ?? [], 0, $topLimit) as $provider): ?>
        <div class="border border-gray-100 rounded-lg p-3">
          <p class="font-semibold text-sm text-gray-900"><?= e($provider['name']) ?></p>
          <p class="text-xs text-gray-500"><?= e($provider['category_name'] ?? $provider['category'] ?? '') ?> - <?= e($provider['owner_name'] ?? '') ?></p>
          <p class="text-xs text-gray-400 mt-1"><?= date('d/m/Y', strtotime($provider['created_at'])) ?> - <?= e($provider['status'] ?? '') ?></p>
        </div>
        <?php endforeach; ?>
        <?php if (empty($newProviders)): ?>
        <p class="text-sm text-gray-400 text-center py-4">Sin registros recientes</p>
        <?php endif; ?>
      </div>
    </section>
  </div>

  <section class="bg-white rounded-xl shadow-sm p-6 border border-gray-100 mb-6">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-4">
      <div>
        <h2 class="font-semibold text-gray-900">Directorio de prestadores</h2>
        <p class="text-sm text-gray-500 mt-1">Contacto directo por WhatsApp o correo, y restablecimiento de valoraciones.</p>
      </div>
      <span class="text-xs px-3 py-1 rounded-full bg-blue-50 text-blue-700 font-semibold"><?= count($providers ?? []) ?> registros</span>
    </div>
    <div class="overflow-x-auto max-h-[30rem] overflow-y-auto">
      <table class="w-full text-sm">
        <thead>
          <tr class="text-left text-xs text-gray-500 uppercase tracking-wide border-b">
            <th class="pb-2 pr-4">Prestador</th>
            <th class="pb-2 pr-4">Responsable</th>
            <th class="pb-2 pr-4">WhatsApp</th>
            <th class="pb-2 pr-4">Correo</th>
            <th class="pb-2 pr-4">Valoracion</th>
            <th class="pb-2">Acciones</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($providers ?? [] as $provider): ?>
          <?php
            $providerEmail = $provider['email'] ?: ($provider['owner_email'] ?? '');
            $providerWhatsapp = $provider['whatsapp'] ?: ($provider['phone'] ?? '');
          ?>
          <tr class="border-b last:border-0">
            <td class="py-2 pr-4">
              <a href="<?= url('lugar/' . $provider['slug']) ?>" target="_blank" class="font-semibold text-gray-900 hover:text-blue-700"><?= e($provider['name']) ?></a>
              <p class="text-xs text-gray-500 mt-1"><?= e($provider['status'] ?? '') ?></p>
            </td>
            <td class="py-2 pr-4 text-gray-600">
              <?= e($provider['owner_name'] ?? 'Sin responsable') ?>
            </td>
            <td class="py-2 pr-4 text-gray-600">
              <?= $providerWhatsapp ? e($providerWhatsapp) : '<span class="text-gray-400">Sin WhatsApp</span>' ?>
            </td>
            <td class="py-2 pr-4 text-gray-600">
              <?= $providerEmail ? e($providerEmail) : '<span class="text-gray-400">Sin correo</span>' ?>
            </td>
            <td class="py-2 pr-4">
              <span class="inline-flex items-center rounded-full bg-yellow-50 px-2.5 py-1 text-xs font-semibold text-yellow-700">
                <?= number_format((float)($provider['rating'] ?? 0), 1) ?>/5
              </span>
            </td>
            <td class="py-2">
              <div class="admin-provider-actions">
                <button type="button" data-provider-id="<?= (int)$provider['id'] ?>" data-channel="whatsapp" data-contact-url="<?= $providerWhatsapp ? e(waLink($providerWhatsapp)) : '' ?>" onclick="contactProviderFromDashboard(this)" class="provider-action provider-action-whatsapp" <?= $providerWhatsapp ? '' : 'disabled' ?>>WhatsApp</button>
                <button type="button" data-provider-id="<?= (int)$provider['id'] ?>" data-channel="email" data-contact-email="<?= e($providerEmail) ?>" onclick="contactProviderFromDashboard(this)" class="provider-action provider-action-email" <?= $providerEmail ? '' : 'disabled' ?>>Correo</button>
                <button type="button" data-provider-id="<?= (int)$provider['id'] ?>" onclick="resetProviderRatingFromDashboard(this)" class="provider-action provider-action-reset">Restablecer valoracion</button>
              </div>
            </td>
          </tr>
          <?php endforeach; ?>
          <?php if (empty($providers)): ?>
          <tr><td colspan="6" class="py-6 text-center text-gray-400">Sin prestadores registrados</td></tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </section>

  <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
    <section class="bg-white rounded-xl shadow-sm p-6 border border-gray-100 lg:col-span-2">
      <h2 class="font-semibold text-gray-900 mb-4">Registro de visitas por prestador</h2>
      <div class="overflow-x-auto max-h-96 overflow-y-auto">
        <table class="w-full text-sm">
          <thead>
            <tr class="text-left text-xs text-gray-500 uppercase tracking-wide border-b">
              <th class="pb-2 pr-4">Prestador</th>
              <th class="pb-2 pr-4">Dia</th>
              <th class="pb-2 pr-4">Semana</th>
              <th class="pb-2 pr-4">Mes</th>
              <th class="pb-2">Total</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($providerVisits ?? [] as $row): ?>
            <tr class="border-b last:border-0">
              <td class="py-2 pr-4 font-medium"><?= e($row['name']) ?></td>
              <td class="py-2 pr-4"><?= (int)$row['today'] ?></td>
              <td class="py-2 pr-4"><?= (int)$row['this_week'] ?></td>
              <td class="py-2 pr-4"><?= (int)$row['this_month'] ?></td>
              <td class="py-2"><?= (int)$row['total'] ?></td>
            </tr>
            <?php endforeach; ?>
            <?php if (empty($providerVisits)): ?>
            <tr><td colspan="5" class="py-6 text-center text-gray-400">Sin visitas por prestador</td></tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </section>

    <section class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
      <h2 class="font-semibold text-gray-900 mb-4">Rutas mas visitadas</h2>
      <div class="space-y-3 max-h-96 overflow-y-auto">
        <?php foreach ($topRoutes ?? [] as $route): ?>
        <div class="p-4 border border-gray-100 rounded-xl">
          <div class="flex items-center justify-between gap-3">
            <h3 class="font-semibold text-gray-900"><?= e($tripLabel($route['trip_type'])) ?></h3>
            <span class="text-xs px-2 py-1 rounded-full bg-blue-50 text-blue-700 font-medium"><?= (int)$route['total_businesses'] ?> negocios</span>
          </div>
          <div class="flex flex-wrap items-center gap-4 mt-2 text-xs text-gray-500">
            <span><?= (int)$route['total_visits'] ?> visitas</span>
            <span><?= number_format((float)$route['avg_rating'], 1) ?> rating</span>
          </div>
        </div>
        <?php endforeach; ?>
        <?php if (empty($topRoutes)): ?>
        <p class="text-sm text-gray-400 text-center py-4">Sin datos de rutas</p>
        <?php endif; ?>
      </div>
    </section>
  </div>

  <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
    <section class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
      <h2 class="font-semibold text-gray-900 mb-4">Visitas por dia</h2>
      <div class="space-y-2 max-h-80 overflow-y-auto">
        <?php foreach ($dailyVisits ?? [] as $row): ?>
        <div class="flex justify-between text-sm border-b border-gray-100 pb-2">
          <span><?= e($row['period']) ?></span>
          <strong><?= (int)$row['total'] ?></strong>
        </div>
        <?php endforeach; ?>
      </div>
    </section>

    <section class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
      <h2 class="font-semibold text-gray-900 mb-4">Visitas por semana</h2>
      <div class="space-y-2 max-h-80 overflow-y-auto">
        <?php foreach ($weeklyVisits ?? [] as $row): ?>
        <div class="flex justify-between text-sm border-b border-gray-100 pb-2">
          <span><?= (int)$row['year'] ?> - Semana <?= (int)$row['week'] ?></span>
          <strong><?= (int)$row['total'] ?></strong>
        </div>
        <?php endforeach; ?>
      </div>
    </section>

    <section class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
      <h2 class="font-semibold text-gray-900 mb-4">Visitas por mes</h2>
      <div class="space-y-2 max-h-80 overflow-y-auto">
        <?php foreach ($monthlyVisits ?? [] as $row): ?>
        <div class="flex justify-between text-sm border-b border-gray-100 pb-2">
          <span><?= e($monthName($row['month'])) ?> <?= (int)$row['year'] ?></span>
          <strong><?= (int)$row['total'] ?></strong>
        </div>
        <?php endforeach; ?>
      </div>
    </section>
  </div>

  <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
    <section class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
      <h2 class="font-semibold text-gray-900 mb-4">Comparativo de estacionalidad por mes y semana</h2>
      <div class="overflow-x-auto max-h-80 overflow-y-auto">
        <table class="w-full text-sm">
          <thead>
            <tr class="text-left text-xs text-gray-500 uppercase tracking-wide border-b">
              <th class="pb-2 pr-4">Mes</th>
              <th class="pb-2 pr-4">Semana</th>
              <th class="pb-2">Visitas</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($seasonalData ?? [] as $row): ?>
            <tr class="border-b last:border-0">
              <td class="py-2 pr-4"><?= e($monthName($row['month'])) ?></td>
              <td class="py-2 pr-4 text-gray-500">Semana <?= (int)$row['week'] ?></td>
              <td class="py-2"><?= (int)$row['total'] ?></td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </section>

    <section class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
      <h2 class="font-semibold text-gray-900 mb-4">Comparativo por evento/promocion y mes</h2>
      <div class="overflow-x-auto max-h-80 overflow-y-auto">
        <table class="w-full text-sm">
          <thead>
            <tr class="text-left text-xs text-gray-500 uppercase tracking-wide border-b">
              <th class="pb-2 pr-4">Mes</th>
              <th class="pb-2 pr-4">Tipo</th>
              <th class="pb-2">Total</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($eventSeasonality ?? [] as $row): ?>
            <tr class="border-b last:border-0">
              <td class="py-2 pr-4"><?= e($monthName($row['month'])) ?></td>
              <td class="py-2 pr-4 text-gray-500"><?= e($row['source']) ?></td>
              <td class="py-2"><?= (int)$row['total'] ?></td>
            </tr>
            <?php endforeach; ?>
            <?php if (empty($eventSeasonality)): ?>
            <tr><td colspan="3" class="py-6 text-center text-gray-400">Sin eventos/promociones con fecha</td></tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </section>
  </div>

  <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    <section class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
      <h2 class="font-semibold text-gray-900 mb-4">Rutas vs tipo de turismo</h2>
      <div class="overflow-x-auto max-h-96 overflow-y-auto">
        <table class="w-full text-sm">
          <thead>
            <tr class="text-left text-xs text-gray-500 uppercase tracking-wide border-b">
              <th class="pb-2 pr-4">Tipo</th>
              <th class="pb-2 pr-4">Categoria/ruta</th>
              <th class="pb-2 pr-4">Visitas</th>
              <th class="pb-2">Rating</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($routesVsTourism ?? [] as $row): ?>
            <tr class="border-b last:border-0">
              <td class="py-2 pr-4"><?= e($tripLabel($row['trip_type'])) ?></td>
              <td class="py-2 pr-4 text-gray-500"><?= e($row['category_name']) ?></td>
              <td class="py-2 pr-4"><?= (int)$row['total_visits'] ?></td>
              <td class="py-2"><?= number_format((float)$row['avg_rating'], 1) ?></td>
            </tr>
            <?php endforeach; ?>
            <?php if (empty($routesVsTourism)): ?>
            <tr><td colspan="4" class="py-6 text-center text-gray-400">Sin cruce de rutas y turismo</td></tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </section>

    <section class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
      <h2 class="font-semibold text-gray-900 mb-4">Sitios mas visitados por familias, pareja, adultos mayores y amigos</h2>
      <div class="overflow-x-auto max-h-96 overflow-y-auto">
        <table class="w-full text-sm">
          <thead>
            <tr class="text-left text-xs text-gray-500 uppercase tracking-wide border-b">
              <th class="pb-2 pr-4">Tipo</th>
              <th class="pb-2 pr-4">Sitio</th>
              <th class="pb-2 pr-4">Categoria</th>
              <th class="pb-2">Visitas</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($topSitesByTripType ?? [] as $row): ?>
            <tr class="border-b last:border-0">
              <td class="py-2 pr-4"><?= e($tripLabel($row['trip_type'])) ?></td>
              <td class="py-2 pr-4 font-medium"><?= e($row['name']) ?></td>
              <td class="py-2 pr-4 text-gray-500"><?= e($row['category_name']) ?></td>
              <td class="py-2"><?= (int)($row['tracked_visits'] ?? $row['visits'] ?? 0) ?></td>
            </tr>
            <?php endforeach; ?>
            <?php if (empty($topSitesByTripType)): ?>
            <tr><td colspan="4" class="py-6 text-center text-gray-400">Sin clasificacion por tipo de visitante</td></tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </section>
  </div>
</main>

<script>
const PROVIDER_ACTION_BASE = '<?= e($providerActionBase) ?>';
const PROVIDER_CSRF = '<?= e($csrf ?? '') ?>';

function setProviderButtonBusy(button, busy) {
  if (!button) return;
  button.disabled = busy;
  button.dataset.originalText = button.dataset.originalText || button.textContent;
  button.textContent = busy ? 'Procesando...' : button.dataset.originalText;
}

function contactProviderFromDashboard(button) {
  const id = button?.dataset.providerId;
  const channel = button?.dataset.channel || 'contact';
  if (!id) return;

  if (channel === 'email') {
    const email = button.dataset.contactEmail || '';
    if (!email) {
      alert('Este prestador no tiene correo registrado.');
      return;
    }
    const subject = 'Contacto de Direccion de Turismo';
    const gmailUrl = `https://mail.google.com/mail/?view=cm&fs=1&to=${encodeURIComponent(email)}&su=${encodeURIComponent(subject)}`;
    if (navigator.clipboard) {
      navigator.clipboard.writeText(email).catch(() => {});
    }
    window.open(gmailUrl, '_blank', 'noopener');
    logProviderContact(id, channel);
    return;
  }

  const link = button.dataset.contactUrl || '';
  if (!link) {
    alert('Este prestador no tiene WhatsApp registrado.');
    return;
  }
  window.open(link, '_blank', 'noopener');
  logProviderContact(id, channel);
}

function logProviderContact(id, channel) {
  fetch(`${PROVIDER_ACTION_BASE}/${id}/contactar?channel=${encodeURIComponent(channel)}`)
    .catch(() => {});
}

function resetProviderRatingFromDashboard(button) {
  const id = button?.dataset.providerId;
  if (!id || !confirm('Restablecer la valoracion de este prestador? Esta accion eliminara sus resenas.')) return;

  setProviderButtonBusy(button, true);
  fetch(`${PROVIDER_ACTION_BASE}/${id}/reestablecer-valoraciones`, {
    method: 'POST',
    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
    body: new URLSearchParams({ _csrf: PROVIDER_CSRF }),
  })
    .then(response => response.json())
    .then(data => {
      if (!data.ok) throw new Error(data.error || 'No se pudo restablecer la valoracion.');
      alert('Valoracion restablecida correctamente.');
      window.location.reload();
    })
    .catch(error => {
      alert(error.message || 'No se pudo restablecer la valoracion.');
      setProviderButtonBusy(button, false);
    });
}
</script>
