<?php
$pageTitle = 'Negocios – SuperAdmin';
require APP_PATH . '/views/layout/head.php';
?>
<?php require APP_PATH . '/views/layout/navbar.php'; ?>
<main class="max-w-7xl mx-auto px-4 py-8 mb-20">
  <h1 class="text-2xl font-bold text-gray-900 mb-6">🏢 Gestión de Negocios</h1>

  <div class="bg-white rounded-2xl shadow-sm overflow-x-auto">
    <table class="w-full text-sm">
      <thead class="bg-gray-50 text-gray-500 text-xs uppercase tracking-wide">
        <tr>
          <th class="px-4 py-3 text-left">Nombre</th>
          <th class="px-4 py-3 text-left">Categoría</th>
          <th class="px-4 py-3 text-left">Propietario</th>
          <th class="px-4 py-3 text-left">Estado</th>
          <th class="px-4 py-3 text-left">Visitas</th>
          <th class="px-4 py-3 text-left">Acciones</th>
        </tr>
      </thead>
      <tbody class="divide-y">
        <?php foreach ($businesses as $b): ?>
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
          </td>
          <td class="px-4 py-3 text-gray-600"><?= number_format($b['visits']) ?></td>
          <td class="px-4 py-3">
            <div class="flex gap-1.5 flex-wrap">
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
              <form method="POST" action="<?= url('superadmin/negocios/' . $b['id'] . '/eliminar') ?>" class="inline"
                onsubmit="return confirm('¿Eliminar este negocio?')">
                <input type="hidden" name="_csrf" value="<?= e($csrf) ?>">
                <button type="submit" class="text-xs bg-red-100 text-red-600 px-2 py-1 rounded-lg hover:bg-red-200 transition">Eliminar</button>
              </form>
            </div>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
    <?php if (empty($businesses)): ?>
    <div class="p-12 text-center text-gray-400">Sin negocios registrados aún.</div>
    <?php endif; ?>
  </div>
</main>
<?php require APP_PATH . '/views/layout/bottom_nav.php'; ?>
<?php require APP_PATH . '/views/layout/footer.php'; ?>
