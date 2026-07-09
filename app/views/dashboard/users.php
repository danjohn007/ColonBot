<?php
$pageTitle = 'Usuarios - SuperAdmin';
require APP_PATH . '/views/layout/head.php';
?>
<?php require APP_PATH . '/views/layout/navbar.php'; ?>

<main class="max-w-6xl mx-auto px-4 py-8 mb-20">
  <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
    <div>
      <p class="text-xs font-bold uppercase tracking-wide text-blue-600">SuperAdmin</p>
      <h1 class="text-2xl font-bold text-gray-900">Gestion de usuarios</h1>
    </div>
    <button onclick="document.getElementById('modal-create').classList.remove('hidden')"
      class="bg-blue-600 text-white px-4 py-2.5 rounded-xl font-semibold hover:bg-blue-700 transition text-sm">
      + Nuevo usuario
    </button>
  </div>

  <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
    <div class="overflow-x-auto">
      <table class="w-full min-w-[760px] text-sm">
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
        <tbody class="divide-y divide-gray-100">
          <?php foreach ($users as $u): ?>
          <tr class="hover:bg-gray-50 transition">
            <td class="px-5 py-3 text-gray-400"><?= (int)$u['id'] ?></td>
            <td class="px-5 py-3 font-semibold text-gray-900"><?= e($u['name']) ?></td>
            <td class="px-5 py-3 text-gray-600"><?= e($u['email']) ?></td>
            <td class="px-5 py-3">
              <span class="px-2.5 py-1 rounded-full text-xs font-semibold
                <?= $u['role'] === 'superadmin' ? 'bg-purple-100 text-purple-700' : 'bg-blue-100 text-blue-700' ?>">
                <?= e($u['role']) ?>
              </span>
            </td>
            <td class="px-5 py-3">
              <span class="px-2.5 py-1 rounded-full text-xs font-semibold <?= $u['active'] ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' ?>">
                <?= $u['active'] ? 'Activo' : 'Inactivo' ?>
              </span>
            </td>
            <td class="px-5 py-3">
              <button onclick='openEditUser(<?= json_encode($u, JSON_HEX_APOS | JSON_HEX_QUOT) ?>)'
                class="text-blue-600 hover:underline text-xs font-semibold mr-3">Editar</button>
              <?php if ((int)$u['id'] !== (int)(currentUser()['id'] ?? 0)): ?>
              <form method="POST" action="<?= url('superadmin/usuarios/' . $u['id'] . '/eliminar') ?>"
                class="inline" onsubmit="return confirm('Eliminar usuario?')">
                <input type="hidden" name="_csrf" value="<?= e($csrf) ?>">
                <button type="submit" class="text-red-500 hover:underline text-xs font-semibold">Eliminar</button>
              </form>
              <?php endif; ?>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
</main>

<div id="modal-create" class="hidden fixed inset-0 bg-black/40 z-50 flex items-center justify-center p-4">
  <div class="bg-white rounded-2xl shadow-xl w-full max-w-2xl overflow-hidden">
    <div class="px-6 py-5 border-b border-gray-100 bg-gradient-to-r from-blue-50 to-indigo-50">
      <p class="text-xs font-bold uppercase tracking-wide text-blue-600">SuperAdmin</p>
      <h2 class="text-xl font-bold text-gray-900 mt-1">Nuevo usuario</h2>
      <p class="text-sm text-gray-500 mt-1">Alta de colaboradores, prestadores, visitantes o administradores.</p>
    </div>
    <form method="POST" action="<?= url('superadmin/usuarios/crear') ?>" class="p-6 space-y-5" onsubmit="return validateCreateUserPassword()">
      <input type="hidden" name="_csrf" value="<?= e($csrf) ?>">
      <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <label class="block">
          <span class="block text-sm font-semibold text-gray-700 mb-1">Nombre completo</span>
          <input type="text" name="name" required placeholder="Ej. Laura Morales" class="admin-user-input">
        </label>
        <label class="block">
          <span class="block text-sm font-semibold text-gray-700 mb-1">Correo electronico</span>
          <input type="email" name="email" required placeholder="usuario@correo.com" class="admin-user-input">
        </label>
        <label class="block">
          <span class="block text-sm font-semibold text-gray-700 mb-1">Contrasena</span>
          <span class="relative block">
            <input type="password" name="password" id="create-password" required minlength="8" autocomplete="new-password" placeholder="Minimo 8 caracteres" class="admin-user-input pr-12">
            <button type="button" class="password-eye" onclick="toggleUserPassword('create-password')" aria-label="Mostrar u ocultar contrasena">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
              </svg>
            </button>
          </span>
        </label>
        <label class="block">
          <span class="block text-sm font-semibold text-gray-700 mb-1">Confirmar contrasena</span>
          <span class="relative block">
            <input type="password" name="password_confirm" id="create-password-confirm" required minlength="8" autocomplete="new-password" placeholder="Repite la contrasena" class="admin-user-input pr-12">
            <button type="button" class="password-eye" onclick="toggleUserPassword('create-password-confirm')" aria-label="Mostrar u ocultar confirmacion">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
              </svg>
            </button>
          </span>
          <span id="create-password-error" class="hidden mt-1 text-xs font-semibold text-red-600">Las contrasenas no coinciden.</span>
        </label>
        <label class="block">
          <span class="block text-sm font-semibold text-gray-700 mb-1">Telefono</span>
          <input type="tel" name="phone" placeholder="Opcional" class="admin-user-input">
        </label>
        <label class="block">
          <span class="block text-sm font-semibold text-gray-700 mb-1">Rol</span>
          <select name="role" class="admin-user-input">
            <option value="colaborador_admin">Colaborador / Admin</option>
            <option value="superadmin">Super Administrador</option>
            <option value="prestador">Prestador de Servicio</option>
            <option value="visitor">Visitante</option>
          </select>
        </label>
      </div>
      <div class="flex flex-col sm:flex-row gap-2 pt-2">
        <button type="submit" class="flex-1 bg-blue-600 text-white py-3 rounded-xl font-semibold hover:bg-blue-700 transition">Crear usuario</button>
        <button type="button" onclick="document.getElementById('modal-create').classList.add('hidden')"
          class="px-5 py-3 bg-gray-100 text-gray-700 rounded-xl font-medium hover:bg-gray-200 transition">Cancelar</button>
      </div>
    </form>
  </div>
</div>

<div id="modal-edit" class="hidden fixed inset-0 bg-black/40 z-50 flex items-center justify-center p-4">
  <div class="bg-white rounded-2xl shadow-xl w-full max-w-md p-6">
    <h2 class="text-lg font-bold mb-4">Editar usuario</h2>
    <form id="edit-form" method="POST" class="space-y-3">
      <input type="hidden" name="_csrf" value="<?= e($csrf) ?>">
      <input type="text" name="name" id="edit-name" required placeholder="Nombre" class="admin-user-input">
      <span class="relative block">
        <input type="password" name="password" id="edit-password" autocomplete="new-password" placeholder="Nueva contrasena (dejar vacio para no cambiar)" class="admin-user-input pr-12">
        <button type="button" class="password-eye" onclick="toggleUserPassword('edit-password')" aria-label="Mostrar u ocultar contrasena">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
          </svg>
        </button>
      </span>
      <select name="role" id="edit-role" class="admin-user-input">
        <option value="colaborador_admin">Colaborador / Admin</option>
        <option value="superadmin">Super Administrador</option>
        <option value="prestador">Prestador de Servicio</option>
        <option value="visitor">Visitante</option>
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

<style>
.admin-user-input {
  width: 100%;
  border: 1px solid #d1d5db;
  border-radius: .75rem;
  padding: .75rem 1rem;
  color: #111827;
  font-size: .875rem;
  outline: none;
  transition: border-color .2s ease, box-shadow .2s ease;
}
.admin-user-input:focus {
  border-color: #2563eb;
  box-shadow: 0 0 0 3px rgba(37, 99, 235, .16);
}
.password-eye {
  position: absolute;
  right: .8rem;
  top: 50%;
  transform: translateY(-50%);
  color: #6b7280;
  padding: .25rem;
}
.password-eye:hover {
  color: #2563eb;
}
</style>

<script>
function toggleUserPassword(id) {
  const input = document.getElementById(id);
  if (!input) return;
  input.type = input.type === 'password' ? 'text' : 'password';
}

function validateCreateUserPassword() {
  const password = document.getElementById('create-password');
  const confirm = document.getElementById('create-password-confirm');
  const error = document.getElementById('create-password-error');
  const matches = password && confirm && password.value === confirm.value;
  if (error) error.classList.toggle('hidden', matches);
  if (!matches && confirm) confirm.focus();
  return matches;
}

function openEditUser(u) {
  document.getElementById('edit-name').value = u.name;
  document.getElementById('edit-password').value = '';
  document.getElementById('edit-role').value = u.role;
  document.getElementById('edit-active').checked = u.active == 1;
  document.getElementById('edit-form').action = '<?= url('superadmin/usuarios/') ?>' + u.id;
  document.getElementById('modal-edit').classList.remove('hidden');
}
</script>

<?php require APP_PATH . '/views/layout/bottom_nav.php'; ?>
<?php require APP_PATH . '/views/layout/footer.php'; ?>
