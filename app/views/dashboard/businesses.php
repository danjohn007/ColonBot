<?php
$pageTitle = 'Negocios – SuperAdmin';
$viewerRole = $user['role'] ?? '';
$validationMode = $validationMode ?? false;
$validationCandidatesCount = (int)($validationCandidatesCount ?? 0);
require APP_PATH . '/views/layout/head.php';
?>
<?php require APP_PATH . '/views/layout/navbar.php'; ?>
<main class="max-w-7xl mx-auto px-4 py-8 mb-20">
  <h1 class="text-2xl font-bold text-gray-900 mb-6">🏢 Gestión de Negocios</h1>

  <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-4">
    <div class="flex flex-wrap items-center gap-2">
      <?php if ($validationMode): ?>
      <a href="<?= url('superadmin/negocios') ?>" class="inline-flex items-center justify-center rounded-xl border border-gray-200 bg-white px-4 py-2.5 text-sm font-semibold text-gray-700 hover:bg-gray-50 transition">
        Ver todos los negocios
      </a>
      <span class="text-sm text-orange-700 font-semibold bg-orange-50 border border-orange-200 rounded-xl px-3 py-2">
        Mostrando candidatos para validar
      </span>
      <?php else: ?>
      <a href="<?= url('superadmin/negocios?validacion=1') ?>" class="inline-flex items-center justify-center rounded-xl bg-orange-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-orange-700 transition">
        Ver negocios disponibles para validar<?= $validationCandidatesCount > 0 ? ' (' . $validationCandidatesCount . ')' : '' ?>
      </a>
      <?php endif; ?>
    </div>
    <a href="<?= url('admin/negocio/crear') ?>" class="inline-flex items-center justify-center rounded-xl bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-blue-700 transition">
      + Nuevo negocio
    </a>
  </div>

  <div class="admin-table-card bg-white rounded-2xl shadow-sm border border-gray-100">
    <div class="overflow-x-auto overflow-y-visible">
    <table class="admin-readable-table min-w-[1120px] text-sm w-auto">
      <thead class="bg-gray-50 text-gray-500 text-xs uppercase tracking-wide">
        <tr>
          <th class="px-4 py-3 text-left">Nombre</th>
          <th class="px-4 py-3 text-left">Categoría</th>
          <th class="px-4 py-3 text-left">Propietario</th>
          <th class="px-4 py-3 text-left">Estado</th>
          <th class="px-4 py-3 text-left">Visitas</th>
          <th class="px-4 py-3 text-left">Validaci&oacute;n</th>
          <th class="px-4 py-3 text-left">Acciones</th>
        </tr>
      </thead>
      <tbody class="divide-y divide-gray-100">
        <?php foreach ($businesses as $b): ?>
        <?php
          $reviewsCount = (int)($b['reviews_count'] ?? 0);
          $reviewsAvg = (float)($b['reviews_avg'] ?? 0);
          $profileScore = (int)($b['verification_profile_score'] ?? 0);
          $lowReviewsCount = (int)($b['low_reviews_count'] ?? 0);
          $isTrusted = (int)($b['is_trusted'] ?? 0) === 1;
          $isSuggested = (int)($b['verification_suggested'] ?? 0) === 1;

          if ($isTrusted) {
              $validationLabel = 'Ya verificado';
              $validationClass = 'bg-green-100 text-green-700 border-green-200';
          } elseif (($b['status'] ?? '') !== 'published') {
              $validationLabel = 'Publicar antes de validar';
              $validationClass = 'bg-gray-100 text-gray-600 border-gray-200';
          } elseif ($reviewsCount < 3) {
              $validationLabel = 'Faltan rese&ntilde;as';
              $validationClass = 'bg-yellow-50 text-yellow-700 border-yellow-200';
          } elseif ($reviewsAvg < 4.3) {
              $validationLabel = 'Promedio insuficiente';
              $validationClass = 'bg-yellow-50 text-yellow-700 border-yellow-200';
          } elseif ($lowReviewsCount > 0) {
              $validationLabel = 'Revisar rese&ntilde;as bajas';
              $validationClass = 'bg-red-50 text-red-700 border-red-200';
          } elseif ($profileScore < 3) {
              $validationLabel = 'Completar perfil';
              $validationClass = 'bg-yellow-50 text-yellow-700 border-yellow-200';
          } else {
              $validationLabel = 'Sugerido para validar';
              $validationClass = 'bg-orange-100 text-orange-700 border-orange-200';
          }
        ?>
        <tr class="hover:bg-gray-50 transition">
          <td class="px-4 py-3 font-medium text-gray-800">
            <a href="<?= url('lugar/' . $b['slug']) ?>" class="hover:text-blue-600" target="_blank"><?= e($b['name']) ?></a>
          </td>
          <td class="px-4 py-3 text-gray-600"><?= e($b['category_name']) ?></td>
          <td class="px-4 py-3 text-gray-600"><?= e($b['owner_name']) ?></td>
          <td class="px-4 py-3">
            <span class="px-2 py-0.5 rounded-full text-xs font-medium
              <?= match($b['status']) {
                'published' => 'bg-green-100 text-green-700',
                'pending'   => 'bg-yellow-100 text-yellow-700',
                'rejected'  => 'bg-red-100 text-red-700',
                default     => 'bg-gray-100 text-gray-600',
              } ?>">
              <?= match($b['status']) { 'published'=>'Publicado','pending'=>'Pendiente','rejected'=>'Rechazado',default=>'Borrador' } ?>
            </span>
            <?php if ((int)($b['is_trusted'] ?? 0) === 1): ?>
            <span class="mt-1 inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-semibold bg-orange-100 text-orange-700 border border-orange-200">
              Verificado
            </span>
            <?php endif; ?>
          </td>
          <td class="px-4 py-3 text-gray-600"><?= number_format($b['visits']) ?></td>
          <td class="px-4 py-3">
            <span class="inline-flex items-center rounded-full border px-2 py-0.5 text-xs font-semibold <?= $validationClass ?>">
              <?= $validationLabel ?>
            </span>
            <div class="mt-1 text-xs text-gray-500 leading-relaxed">
              <?= number_format($reviewsAvg, 1) ?> estrellas &middot; <?= $reviewsCount ?> rese&ntilde;as &middot; Perfil <?= $profileScore ?>/4
            </div>
            <?php if ($isSuggested): ?>
            <div class="mt-1 text-xs font-medium text-orange-700">Listo para revisi&oacute;n manual.</div>
            <?php endif; ?>
          </td>
          <td class="px-4 py-3">
            <div class="admin-table-actions flex gap-1.5 flex-wrap">
              <a href="<?= url('admin/negocio/' . $b['id']) ?>"
                class="text-xs bg-blue-100 text-blue-700 px-2 py-1 rounded-lg hover:bg-blue-200 transition">✎ Editar</a>
              <?php if ($b['status'] !== 'published'): ?>
              <form method="POST" action="<?= url('superadmin/negocios/' . $b['id'] . '/aprobar') ?>" class="inline">
                <input type="hidden" name="_csrf" value="<?= e($csrf) ?>">
                <button type="submit" class="text-xs bg-green-100 text-green-700 px-2 py-1 rounded-lg hover:bg-green-200 transition">✓ Aprobar</button>
              </form>
              <?php endif; ?>
              <?php if ($b['status'] === 'published'): ?>
              <form method="POST" action="<?= url('superadmin/negocios/' . $b['id'] . '/rechazar') ?>" class="inline">
                <input type="hidden" name="_csrf" value="<?= e($csrf) ?>">
                <button type="submit" class="text-xs bg-yellow-100 text-yellow-700 px-2 py-1 rounded-lg hover:bg-yellow-200 transition">✗ Rechazar</button>
              </form>
              <?php endif; ?>
              <form method="POST" action="<?= url('superadmin/negocios/' . $b['id'] . '/confiable') ?>" class="inline">
                <input type="hidden" name="_csrf" value="<?= e($csrf) ?>">
                <?php if ($validationMode): ?>
                <input type="hidden" name="return_to" value="validation">
                <?php endif; ?>
                <?php if ((int)($b['is_trusted'] ?? 0) === 1): ?>
                <input type="hidden" name="trusted" value="0">
                <button type="submit" class="text-xs bg-gray-100 text-gray-700 px-2 py-1 rounded-lg hover:bg-gray-200 transition">Quitar confiable</button>
                <?php else: ?>
                <input type="hidden" name="trusted" value="1">
                <input type="hidden" name="trusted_note" value="Validado por Turismo Colon">
                <button type="submit" class="text-xs bg-orange-100 text-orange-700 px-2 py-1 rounded-lg hover:bg-orange-200 transition">Negocio confiable</button>
                <?php endif; ?>
              </form>
              <?php if ($viewerRole === 'superadmin'): ?>
              <form method="POST" action="<?= url('superadmin/negocios/' . $b['id'] . '/eliminar') ?>" class="inline"
                onsubmit="return confirm('¿Eliminar este negocio?')">
                <input type="hidden" name="_csrf" value="<?= e($csrf) ?>">
                <button type="submit" class="text-xs bg-red-100 text-red-600 px-2 py-1 rounded-lg hover:bg-red-200 transition">Eliminar</button>
              </form>
              <?php endif; ?>
            </div>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
    </div>
    <?php if (empty($businesses) && $validationMode): ?>
    <div class="p-12 text-center text-gray-400">No hay negocios que cumplan los criterios de validaci&oacute;n por ahora.</div>
    <?php elseif (empty($businesses)): ?>
    <div class="p-12 text-center text-gray-400">Sin negocios registrados aún.</div>
    <?php endif; ?>
  </div>
</main>
<style>
.admin-readable-table {
  border-collapse: separate;
  border-spacing: 0;
}
.admin-readable-table th {
  padding: .9rem 1rem;
  font-weight: 800;
  letter-spacing: .05em;
  white-space: nowrap;
}
.admin-readable-table td {
  padding: 1rem;
  line-height: 1.45;
  vertical-align: middle;
}
.admin-readable-table tbody tr:nth-child(even) {
  background: #fcfcfd;
}
.admin-readable-table tbody tr:hover {
  background: #eff6ff;
}
.admin-table-actions a,
.admin-table-actions button {
  display: inline-flex;
  align-items: center;
  min-height: 2rem;
  white-space: nowrap;
}
</style>
<?php require APP_PATH . '/views/layout/bottom_nav.php'; ?>
<?php require APP_PATH . '/views/layout/footer.php'; ?>
