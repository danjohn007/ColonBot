<?php
$pageTitle = 'Categorías – SuperAdmin';
require APP_PATH . '/views/layout/head.php';
?>
<?php require APP_PATH . '/views/layout/navbar.php'; ?>
<main class="max-w-5xl mx-auto px-4 py-8 mb-20">
  <div class="flex items-center justify-between mb-6">
    <h1 class="text-2xl font-bold text-gray-900">🏷️ Categorías</h1>
    <button onclick="document.getElementById('modal-cat').classList.remove('hidden')"
      class="bg-blue-600 text-white px-4 py-2.5 rounded-xl font-semibold hover:bg-blue-700 transition text-sm">
      + Nueva Categoría
    </button>
  </div>

  <div class="admin-table-card bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
    <div class="overflow-x-auto">
    <table class="admin-readable-table w-full min-w-[860px] text-sm">
      <thead class="bg-gray-50 text-gray-500 text-xs uppercase tracking-wide">
        <tr>
          <th class="px-5 py-3 text-left">Color</th>
          <th class="px-5 py-3 text-left">Nombre</th>
          <th class="px-5 py-3 text-left">Slug</th>
          <th class="px-5 py-3 text-left">Ícono</th>
          <th class="px-5 py-3 text-left">Orden</th>
          <th class="px-5 py-3 text-left">Estado</th>
          <th class="px-5 py-3 text-left">Acciones</th>
        </tr>
      </thead>
      <tbody class="divide-y divide-gray-100">
        <?php foreach ($categories as $cat): ?>
        <tr class="hover:bg-gray-50 transition">
          <td class="px-5 py-3">
            <span class="inline-block w-6 h-6 rounded-full border border-white shadow" style="background: <?= e($cat['color']) ?>"></span>
          </td>
          <td class="px-5 py-3 font-medium text-gray-800"><?= e($cat['name']) ?></td>
          <td class="px-5 py-3 text-gray-500 font-mono text-xs"><?= e($cat['slug']) ?></td>
          <td class="px-5 py-3 text-gray-600"><?= e($cat['icon']) ?></td>
          <td class="px-5 py-3 text-gray-600"><?= $cat['sort_order'] ?></td>
          <td class="px-5 py-3 admin-table-actions">
            <span class="px-2 py-0.5 rounded-full text-xs <?= $cat['active'] ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500' ?>">
              <?= $cat['active'] ? 'Activa' : 'Inactiva' ?>
            </span>
          </td>
          <td class="px-5 py-3">
            <button type="button" onclick="openEditCat(<?= $cat['id'] ?>, '<?= e(str_replace("'", "\\'", $cat['name'])) ?>', '<?= e(str_replace("'", "\\'", $cat['icon'])) ?>', '<?= e($cat['color']) ?>', <?= (int)$cat['sort_order'] ?>, <?= (int)$cat['active'] ?>)"
              class="text-xs text-blue-500 hover:underline mr-3">Editar</button>
            <form method="POST" action="<?= url('superadmin/categorias/' . $cat['id'] . '/eliminar') ?>"
              class="inline" onsubmit="return confirm('¿Eliminar categoría?')">
              <input type="hidden" name="_csrf" value="<?= e($csrf) ?>">
              <button type="submit" class="text-xs text-red-500 hover:underline">Eliminar</button>
            </form>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
    </div>
  </div>
</main>

<!-- Modal Nueva Categoría -->
<div id="modal-cat" class="hidden fixed inset-0 bg-black/40 z-50 flex items-center justify-center p-4">
  <div class="bg-white rounded-2xl shadow-xl w-full max-w-sm p-6">
    <h2 class="text-lg font-bold mb-4">Nueva Categoría</h2>
    <form method="POST" action="<?= url('superadmin/categorias/crear') ?>" class="space-y-3">
      <input type="hidden" name="_csrf" value="<?= e($csrf) ?>">
      <input type="text" name="name" required placeholder="Nombre" class="w-full px-4 py-2.5 border border-gray-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
      <input type="text" name="icon" placeholder="Ícono (ej. map-pin, wine)" class="w-full px-4 py-2.5 border border-gray-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
      <div class="flex gap-3 items-center">
        <label class="text-sm text-gray-700">Color:</label>
        <input type="color" name="color" value="#3B82F6" class="w-10 h-10 rounded-lg border border-gray-300 cursor-pointer">
      </div>
      <input type="number" name="sort_order" placeholder="Orden (0, 1, 2...)" value="0" class="w-full px-4 py-2.5 border border-gray-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
      <div class="flex gap-2 pt-2">
        <button type="submit" class="flex-1 bg-blue-600 text-white py-2.5 rounded-xl font-semibold">Crear</button>
        <button type="button" onclick="document.getElementById('modal-cat').classList.add('hidden')"
          class="px-5 py-2.5 bg-gray-100 text-gray-700 rounded-xl font-medium">Cancelar</button>
      </div>
    </form>
  </div>
</div>

<!-- Modal Editar Categoría -->
<div id="modal-edit-cat" class="hidden fixed inset-0 bg-black/40 z-50 flex items-center justify-center p-4">
  <div class="bg-white rounded-2xl shadow-xl w-full max-w-sm p-6">
    <h2 class="text-lg font-bold mb-4">Editar Categoría</h2>
    <form method="POST" action="" id="edit-cat-form" class="space-y-3">
      <input type="hidden" name="_csrf" value="<?= e($csrf) ?>">
      <input type="text" id="edit-cat-name" name="name" required placeholder="Nombre" class="w-full px-4 py-2.5 border border-gray-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
      <input type="text" id="edit-cat-icon" name="icon" placeholder="Ícono (ej. map-pin, wine)" class="w-full px-4 py-2.5 border border-gray-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
      <div class="flex gap-3 items-center">
        <label class="text-sm text-gray-700">Color:</label>
        <input type="color" id="edit-cat-color" name="color" value="#3B82F6" class="w-10 h-10 rounded-lg border border-gray-300 cursor-pointer">
      </div>
      <input type="number" id="edit-cat-order" name="sort_order" placeholder="Orden (0, 1, 2...)" value="0" class="w-full px-4 py-2.5 border border-gray-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
      <div class="flex items-center gap-2">
        <input type="checkbox" id="edit-cat-active" name="active" value="1" class="w-4 h-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500">
        <label for="edit-cat-active" class="text-sm text-gray-700">Activa</label>
      </div>
      <div class="flex gap-2 pt-2">
        <button type="submit" class="flex-1 bg-blue-600 text-white py-2.5 rounded-xl font-semibold">Guardar</button>
        <button type="button" onclick="document.getElementById('modal-edit-cat').classList.add('hidden')"
          class="px-5 py-2.5 bg-gray-100 text-gray-700 rounded-xl font-medium">Cancelar</button>
      </div>
    </form>
  </div>
</div>

<script>
function openEditCat(id, name, icon, color, sortOrder, active) {
  document.getElementById('edit-cat-form').action = '<?= BASE_URL ?>/superadmin/categorias/' + id;
  document.getElementById('edit-cat-name').value = name;
  document.getElementById('edit-cat-icon').value = icon;
  document.getElementById('edit-cat-color').value = color;
  document.getElementById('edit-cat-order').value = sortOrder;
  document.getElementById('edit-cat-active').checked = active === 1;
  document.getElementById('modal-edit-cat').classList.remove('hidden');
}
</script>

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
.admin-table-actions button {
  display: inline-flex;
  align-items: center;
  min-height: 2rem;
  white-space: nowrap;
}
</style>

<?php require APP_PATH . '/views/layout/bottom_nav.php'; ?>
<?php require APP_PATH . '/views/layout/footer.php'; ?>
