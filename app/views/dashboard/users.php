<?php
$pageTitle = 'Usuarios – SuperAdmin';
require APP_PATH . '/views/layout/head.php';
?>
<?php require APP_PATH . '/views/layout/navbar.php'; ?>
<main class="max-w-6xl mx-auto px-4 py-8 mb-20">
  <div class="flex items-center justify-between mb-6">
    <h1 class="text-2xl font-bold text-gray-900">👥 Gestión de Usuarios</h1>
    <button onclick="document.getElementById('modal-create').classList.remove('hidden')"
      class="bg-blue-600 text-white px-4 py-2.5 rounded-xl font-semibold hover:bg-blue-700 transition text-sm">
      + Nuevo Usuario
    </button>
  </div>

  <div class="bg-white rounded-2xl shadow-sm overflow-hidden">
    <table class="w-full text-sm">
      <thead class="bg-gray-50 text-gray-500 text-xs uppercase tracking-wide">
        <tr>
          <th class="px-5 py-3 text-left">#</th>
          <th class="px-5 py-3 text-left">Nombre</th>
          <th class="px-5 py-3 text-left">Correo</th>
          <th class="px-5 py-3 text-left">Rol</th>
          <th class="px-5 py-3 text-left">Estado</th>
          <th class="px-5 py-3 text-left">Acciones</th>
        </tr>
      </thead>
      <tbody class="divide-y">
        <?php foreach ($users as $u): ?>
        <tr class="hover:bg-gray-50 transition">
          <td class="px-5 py-3 text-gray-400"><?= $u['id'] ?></td>
          <td class="px-5 py-3 font-medium text-gray-800"><?= e($u['name']) ?></td>
          <td class="px-5 py-3 text-gray-600"><?= e($u['email']) ?></td>
          <td class="px-5 py-3">
            <span class="px-2 py-0.5 rounded-full text-xs font-medium
              <?= $u['role'] === 'superadmin' ? 'bg-purple-100 text-purple-700' : 'bg-blue-100 text-blue-700' ?>">
              <?= $u['role'] ?>
            </span>
          </td>
          <td class="px-5 py-3">
            <span class="px-2 py-0.5 rounded-full text-xs <?= $u['active'] ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' ?>">
              <?= $u['active'] ? 'Activo' : 'Inactivo' ?>
            </span>
          </td>
          <td class="px-5 py-3">
            <button onclick='openEditUser(<?= json_encode($u) ?>)'
              class="text-blue-600 hover:underline text-xs mr-3">Editar</button>
            <?php if ($u['id'] !== (currentUser()['id'] ?? 0)): ?>
            <form method="POST" action="<?= url('superadmin/usuarios/' . $u['id'] . '/eliminar') ?>"
              class="inline" onsubmit="return confirm('¿Eliminar usuario?')">
              <input type="hidden" name="_csrf" value="<?= e($csrf) ?>">
              <button type="submit" class="text-red-500 hover:underline text-xs">Eliminar</button>
            </form>
            <?php endif; ?>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</main>

<!-- Modal Create -->
<div id="modal-create" class="hidden fixed inset-0 bg-black/40 z-50 flex items-center justify-center p-4">
  <div class="bg-white rounded-2xl shadow-xl w-full max-w-md p-6">
    <h2 class="text-lg font-bold mb-4">Nuevo Usuario</h2>
    <form method="POST" action="<?= url('superadmin/usuarios/crear') ?>" class="space-y-3">
      <input type="hidden" name="_csrf" value="<?= e($csrf) ?>">
      <input type="text" name="name" required placeholder="Nombre completo" class="w-full input">
      <input type="email" name="email" required placeholder="Correo electrónico" class="w-full input">
      <input type="password" name="password" required placeholder="Contraseña (mín. 8 chars)" minlength="8" class="w-full input">
      <input type="tel" name="phone" placeholder="Teléfono (opcional)" class="w-full input">
      <select name="role" class="w-full input">
        <option value="admin">Admin de Negocio</option>
        <option value="superadmin">Super Administrador</option>
      </select>
      <div class="flex gap-2 pt-2">
        <button type="submit" class="flex-1 bg-blue-600 text-white py-2.5 rounded-xl font-semibold hover:bg-blue-700 transition">Crear</button>
        <button type="button" onclick="document.getElementById('modal-create').classList.add('hidden')"
          class="px-5 py-2.5 bg-gray-100 text-gray-700 rounded-xl font-medium hover:bg-gray-200 transition">Cancelar</button>
      </div>
    </form>
  </div>
</div>

<!-- Modal Edit -->
<div id="modal-edit" class="hidden fixed inset-0 bg-black/40 z-50 flex items-center justify-center p-4">
  <div class="bg-white rounded-2xl shadow-xl w-full max-w-md p-6">
    <h2 class="text-lg font-bold mb-4">Editar Usuario</h2>
    <form id="edit-form" method="POST" class="space-y-3">
      <input type="hidden" name="_csrf" value="<?= e($csrf) ?>">
      <input type="text" name="name" id="edit-name" required placeholder="Nombre" class="w-full input">
      <input type="password" name="password" placeholder="Nueva contraseña (dejar vacío para no cambiar)" class="w-full input">
      <select name="role" id="edit-role" class="w-full input">
        <option value="admin">Admin de Negocio</option>
        <option value="superadmin">Super Administrador</option>
      </select>
      <label class="flex items-center gap-2 text-sm">
        <input type="checkbox" name="active" id="edit-active" value="1" class="rounded">
        Activo
      </label>
      <div class="flex gap-2 pt-2">
        <button type="submit" class="flex-1 bg-blue-600 text-white py-2.5 rounded-xl font-semibold">Guardar</button>
        <button type="button" onclick="document.getElementById('modal-edit').classList.add('hidden')"
          class="px-5 py-2.5 bg-gray-100 text-gray-700 rounded-xl font-medium">Cancelar</button>
      </div>
    </form>
  </div>
</div>

<style>.input { @apply px-4 py-2.5 border border-gray-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500; }</style>
<script>
function openEditUser(u) {
  document.getElementById('edit-name').value = u.name;
  document.getElementById('edit-role').value = u.role;
  document.getElementById('edit-active').checked = u.active == 1;
  document.getElementById('edit-form').action = '<?= url('superadmin/usuarios/') ?>' + u.id;
  document.getElementById('modal-edit').classList.remove('hidden');
}
</script>
<?php require APP_PATH . '/views/layout/bottom_nav.php'; ?>
<?php require APP_PATH . '/views/layout/footer.php'; ?>
